<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * Local sandbox simulation is a developer-only fallback. It must never be
     * reachable on production, even if the env flag is accidentally left on.
     */
    private function simulationAllowed(): bool
    {
        return app()->environment('local', 'testing')
            && (bool) config('services.paymongo.simulation_enabled');
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

        // Pre-flight: fail fast with an actionable message when the gateway is
        // not configured, instead of sending an empty key to PayMongo.
        $secretKey = (string) config('services.paymongo.secret_key');
        $isConfigured = $secretKey !== '' && !str_contains($secretKey, 'REPLACE_WITH');

        if (!$isConfigured) {
            if ($this->simulationAllowed()) {
                return $this->createSimulatedCheckoutSession($order, $user);
            }

            return response()->json([
                'success' => false,
                'message' => 'Online payment is temporarily unavailable. Please choose Cash on Delivery or try again later.',
                'data'    => ['configured' => false],
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
                    if ($response->status() === 401) {
                        $errorMsg = 'PayMongo rejected the configured secret key. Verify PAYMONGO_SECRET_KEY in backend/.env, then run "php artisan config:clear".';
                    }
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
     * Create a local-only simulated checkout session.
     *
     * Used on developer machines when no PayMongo sandbox key is configured so
     * the full order → payment → escrow flow can still be demonstrated. No
     * request reaches PayMongo and no money moves.
     */
    private function createSimulatedCheckoutSession(Order $order, $user)
    {
        $sessionId = 'cs_sandbox_' . Str::uuid();

        $payment = DB::transaction(function () use ($order, $user, $sessionId) {
            // Only one pending payment attempt per order (mirrors the real flow).
            Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            $payment = Payment::create([
                'order_id'            => $order->id,
                'user_id'             => $user->id,
                'payment_method'      => 'sandbox',
                'payment_gateway'     => 'paymongo_sandbox',
                'checkout_session_id' => $sessionId,
                'checkout_url'        => rtrim((string) config('services.paymongo.frontend_url'), '/')
                    . '/payment/checkout?order_id=' . $order->id,
                'amount'              => $order->total_amount,
                'status'              => 'pending',
            ]);

            $order->update(['payment_method' => 'PayMongo Online Payment']);

            return $payment;
        });

        Log::info('[PayMongo Sandbox] Simulated checkout created for order ' . $order->order_number);

        return response()->json([
            'success' => true,
            'mode'    => 'sandbox',
            'message' => 'Checkout session created. Redirecting to payment page.',
            'data'    => [
                'payment_id'   => $payment->id,
                'checkout_url' => $payment->checkout_url,
                'session_id'   => $sessionId,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * POST /api/payments/simulate
     *
     * Local-only settlement endpoint for a simulated checkout session. Applies
     * the same payment → order → escrow transitions as the PayMongo webhook so
     * the rest of the system (seller dashboards, payouts) behaves identically.
     */
    public function simulatePayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'outcome'  => 'sometimes|in:paid,cancelled',
        ]);

        if (!$this->simulationAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'Online payment is not available on this server. Please choose Cash on Delivery or try again later.',
            ], 403);
        }

        $user  = $request->user();
        $order = Order::find($validated['order_id']);

        if (!$order || $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or access denied.',
            ], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
            ], 422);
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('payment_gateway', 'paymongo_sandbox')
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'No pending checkout session found for this order. Start checkout again from My Orders.',
            ], 422);
        }

        // ── Cancel branch ───────────────────────────────────────────────────
        if (($validated['outcome'] ?? 'paid') === 'cancelled') {
            $payment->update(['status' => 'cancelled']);

            Log::info('[PayMongo Sandbox] Simulated checkout cancelled for order ' . $order->order_number);

            return response()->json([
                'success' => true,
                'mode'    => 'sandbox',
                'message' => 'Payment was cancelled. The order is still unpaid.',
                'data'    => [
                    'order_id'       => $order->id,
                    'order_number'   => $order->order_number,
                    'payment_status' => $order->fresh()->payment_status,
                ],
            ]);
        }

        // ── Paid branch (mirrors WebhookController::handleCheckoutPaid) ─────
        $reference = 'SIMULATED-' . strtoupper(Str::random(10));

        DB::transaction(function () use ($payment, $order, $reference) {
            $payment->update([
                'status'            => 'paid',
                'paid_at'           => now(),
                'payment_reference' => $reference,
                'payment_method'    => 'sandbox',
            ]);

            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
                'confirmed_at'   => now(),
                'escrow_status'  => 'held',
            ]);

            $order->items()->update(['escrow_status' => 'held']);
        });

        Log::info('[PayMongo Sandbox] Order ' . $order->order_number . ' marked as PAID (simulated)');

        // NotificationService swallows mail failures, so a missing SMTP server
        // can never break the simulated settlement.
        NotificationService::sendPaymentSuccess(
            $order->fresh(),
            (float) $payment->amount,
            'PayMongo Online Payment',
            $reference
        );

        return response()->json([
            'success' => true,
            'mode'    => 'sandbox',
            'message' => 'Payment successful.',
            'data'    => [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'payment_status' => 'paid',
                'payment'        => [
                    'id'                => $payment->id,
                    'status'            => 'paid',
                    'payment_gateway'   => 'paymongo_sandbox',
                    'payment_reference' => $reference,
                ],
            ],
        ]);
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

        // If we have a pending PayMongo session, refresh its status from the API.
        // Simulated sessions never exist on PayMongo, so they are skipped.
        if ($payment && $payment->status === 'pending'
            && $payment->checkout_session_id
            && $payment->payment_gateway !== 'paymongo_sandbox') {
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
                    'payment_gateway'  => $payment->payment_gateway,
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

                // Send payment success email receipt to customer
                NotificationService::sendPaymentSuccess($payment->order, (float) $payment->amount, $payment->payment_method ?? 'PayMongo', $paymentIntentId);

            } elseif (in_array($pmStatus, ['cancelled', 'expired'])) {
                $payment->markAsCancelled();
            }
        } catch (\Exception $e) {
            Log::warning('[PayMongo] Could not refresh session status: ' . $e->getMessage());
        }
    }
}
