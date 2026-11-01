<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * PayMongo API base URL.
     */
    private function baseUrl(): string
    {
        return config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
    }

    /**
     * Base64-encoded secret key for PayMongo Basic Auth.
     * PayMongo uses: Authorization: Basic base64(secret_key:)
     */
    private function authHeader(): string
    {
        return 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':');
    }

    /**
     * POST /api/payments/checkout-session
     *
     * Creates a PayMongo checkout session for an existing order.
     * Returns the hosted checkout URL for the customer to complete payment.
     *
     * Only the authenticated customer who owns the order can initiate payment.
     */
    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $user  = $request->user();
        $order = Order::with('items.product')->find($validated['order_id']);

        // Authorization: only the order owner can pay
        if (!$order || $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied.',
            ], 403);
        }

        // Guard: already paid
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($order, $user) {
                // Cancel any previously pending payment record for this order
                Payment::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);

                // Build line items for PayMongo
                $lineItems = $order->items->map(function ($item) {
                    return [
                        'currency'     => 'PHP',
                        'amount'       => (int) round(($item->unit_price ?? $item->price) * 100), // centavos
                        'name'         => $item->product_name,
                        'quantity'     => $item->quantity,
                        'description'  => 'Handmade craft from CraftMarket',
                    ];
                })->toArray();

                // Add shipping fee as a line item if applicable
                if ((float) $order->shipping_fee > 0) {
                    $lineItems[] = [
                        'currency'    => 'PHP',
                        'amount'      => (int) round((float) $order->shipping_fee * 100),
                        'name'        => 'Shipping & Handling',
                        'quantity'    => 1,
                        'description' => 'Nationwide delivery fee',
                    ];
                }

                $successUrl = config('services.paymongo.success_url') . '&order_id=' . $order->id;
                $cancelUrl  = config('services.paymongo.cancel_url')  . '&order_id=' . $order->id;

                // Create PayMongo checkout session via API
                $response = Http::withHeaders([
                    'Authorization' => $this->authHeader(),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])->post($this->baseUrl() . '/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name'  => $order->user->name,
                                'email' => $order->user->email,
                                'phone' => $order->contact_number,
                            ],
                            'send_email_receipt' => false,
                            'show_description'   => true,
                            'show_line_items'    => true,
                            'line_items'         => $lineItems,
                            'payment_method_types' => ['gcash', 'card', 'paymaya', 'billease', 'dob', 'grab_pay'],
                            'description'        => 'CraftMarket Order ' . $order->order_number,
                            'reference_number'   => $order->order_number,
                            'success_url'        => $successUrl,
                            'cancel_url'         => $cancelUrl,
                        ],
                    ],
                ]);

                if (!$response->successful()) {
                    $errorBody = $response->json();
                    $errorMsg  = $errorBody['errors'][0]['detail'] ?? 'PayMongo API error. Please try again.';
                    throw new \Exception($errorMsg);
                }

                $sessionData = $response->json('data');
                $sessionId   = $sessionData['id'];
                $checkoutUrl = $sessionData['attributes']['checkout_url'];

                // Persist payment record
                $payment = Payment::create([
                    'order_id'            => $order->id,
                    'user_id'             => $user->id,
                    'payment_method'      => 'online',        // refined on webhook
                    'payment_gateway'     => 'paymongo',
                    'checkout_session_id' => $sessionId,
                    'checkout_url'        => $checkoutUrl,
                    'amount'              => $order->total_amount,
                    'status'              => 'pending',
                ]);

                // Mark the order's payment method as online/PayMongo
                $order->update(['payment_method' => 'PayMongo Online Payment']);

                return [
                    'payment_id'   => $payment->id,
                    'checkout_url' => $checkoutUrl,
                    'session_id'   => $sessionId,
                    'order_number' => $order->order_number,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Checkout session created. Redirecting to payment page.',
                'data'    => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('[PayMongo] Checkout session creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }

    /**
     * GET /api/payments/status/{orderId}
     *
     * Retrieve the current payment status for an order.
     * The frontend polls this after returning from PayMongo to verify payment.
     * Never trust what the frontend says — always read from our database.
     */
    public function getPaymentStatus(Request $request, $orderId)
    {
        $user  = $request->user();
        $order = Order::with('latestPayment')->find($orderId);

        if (!$order || $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied.',
            ], 403);
        }

        $payment = $order->latestPayment;

        // If we have a pending PayMongo session, refresh its status from the API
        if ($payment && $payment->status === 'pending' && $payment->checkout_session_id) {
            $this->refreshCheckoutSessionStatus($payment);
            $payment->refresh();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id'         => $order->id,
                'order_number'     => $order->order_number,
                'order_status'     => $order->status,
                'payment_status'   => $order->fresh()->payment_status,
                'payment'          => $payment ? [
                    'id'               => $payment->id,
                    'status'           => $payment->status,
                    'payment_method'   => $payment->payment_method,
                    'amount'           => $payment->amount,
                    'paid_at'          => $payment->paid_at,
                    'payment_reference' => $payment->payment_reference,
                ] : null,
            ],
        ]);
    }

    /**
     * Refresh a pending checkout session's status by querying PayMongo API.
     * Used when customer returns from hosted checkout page.
     */
    private function refreshCheckoutSessionStatus(Payment $payment): void
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authHeader(),
                'Accept'        => 'application/json',
            ])->get($this->baseUrl() . '/checkout_sessions/' . $payment->checkout_session_id);

            if (!$response->successful()) return;

            $session    = $response->json('data.attributes');
            $pmStatus   = $session['payment_intent']['attributes']['status'] ?? $session['status'] ?? null;

            if ($pmStatus === 'succeeded' || $pmStatus === 'paid') {
                $paymentIntentId = $session['payment_intent']['id'] ?? $payment->checkout_session_id;
                $payment->markAsPaid($paymentIntentId);
                // Confirm the order
                $payment->order()->update(['status' => 'confirmed', 'confirmed_at' => now()]);

            } elseif (in_array($pmStatus, ['cancelled', 'expired'])) {
                $payment->markAsCancelled();
            }
        } catch (\Exception $e) {
            Log::warning('[PayMongo] Could not refresh session status: ' . $e->getMessage());
        }
    }
}
