<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * POST /api/webhooks/paymongo
     *
     * Receives and processes PayMongo webhook events.
     *
     * PayMongo signs webhooks with HMAC-SHA256 using your webhook secret.
     * Signature is in the `paymongo-signature` header as:
     *   t=<timestamp>,te=<test_sig>,li=<live_sig>
     *
     * Docs: https://developers.paymongo.com/docs/webhooks
     */
    public function handlePayMongo(Request $request)
    {
        // ── 1. Signature verification ──────────────────────────────────────────
        $webhookSecret = config('services.paymongo.webhook_secret');
        $signatureHeader = $request->header('paymongo-signature');

        if ($webhookSecret && $webhookSecret !== 'whsec_REPLACE_WITH_YOUR_WEBHOOK_SECRET') {
            if (!$signatureHeader) {
                Log::warning('[Webhook] Missing PayMongo signature header.');
                return response()->json(['error' => 'Missing signature'], 400);
            }

            if (!$this->verifySignature($signatureHeader, $request->getContent(), $webhookSecret)) {
                Log::warning('[Webhook] PayMongo signature verification failed.');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        // ── 2. Parse event ─────────────────────────────────────────────────────
        $payload   = $request->json()->all();
        $eventType = $payload['data']['attributes']['type'] ?? null;
        $eventData = $payload['data']['attributes']['data'] ?? null;

        Log::info('[Webhook] PayMongo event received: ' . $eventType);

        // ── 3. Handle specific events ──────────────────────────────────────────
        try {
            match ($eventType) {
                'checkout_session.payment.paid'     => $this->handleCheckoutPaid($eventData),
                'payment_intent.payment.paid'       => $this->handlePaymentIntentPaid($eventData),
                'checkout_session.expired'          => $this->handleCheckoutExpired($eventData),
                default                             => Log::info('[Webhook] Unhandled event type: ' . $eventType),
            };
        } catch (\Exception $e) {
            Log::error('[Webhook] Error processing event: ' . $e->getMessage());
            // Return 200 so PayMongo doesn't keep retrying a permanent error
            return response()->json(['received' => true, 'error' => $e->getMessage()]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Verify PayMongo webhook signature.
     * PayMongo signature format: t=<ts>,te=<test_sig>,li=<live_sig>
     */
    private function verifySignature(string $header, string $body, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        $testSig   = $parts['te'] ?? null;
        $liveSig   = $parts['li'] ?? null;

        if (!$timestamp) return false;

        $signedPayload  = $timestamp . '.' . $body;
        $expectedSig    = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expectedSig, $testSig ?? '') || hash_equals($expectedSig, $liveSig ?? '');
    }

    /**
     * Handle checkout_session.payment.paid
     * Fired when customer completes payment in the hosted checkout page.
     */
    private function handleCheckoutPaid(array $data): void
    {
        $sessionId = $data['id'] ?? null;
        if (!$sessionId) return;

        DB::transaction(function () use ($data, $sessionId) {
            $payment = Payment::where('checkout_session_id', $sessionId)->first();
            if (!$payment) {
                Log::warning('[Webhook] No payment record for session: ' . $sessionId);
                return;
            }

            // Idempotency: skip if already marked paid
            if ($payment->status === 'paid') {
                Log::info('[Webhook] Payment already marked paid. Skipping: ' . $sessionId);
                return;
            }

            $paymentIntentId  = $data['attributes']['payment_intent']['id'] ?? $sessionId;
            $paymentMethodType = $data['attributes']['payment_intent']['attributes']['payment_method_allowed'][0]
                                ?? $data['attributes']['payment_method_used']
                                ?? 'online';

            // Update payment record
            $payment->update([
                'status'             => 'paid',
                'paid_at'            => now(),
                'payment_reference'  => $paymentIntentId,
                'payment_method'     => $paymentMethodType,
            ]);

            // Update order — payment is captured into escrow until delivery.
            $order = $payment->order;
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
                'confirmed_at'   => now(),
                'escrow_status'  => 'held',
            ]);
            $order->items()->update(['escrow_status' => 'held']);

            Log::info('[Webhook] Order ' . ($order->order_number ?? $order->id) . ' marked as PAID via ' . $paymentMethodType);

            // Send payment confirmation email receipt
            NotificationService::sendPaymentSuccess($order, (float) $payment->amount, $paymentMethodType, $paymentIntentId);
        });
    }

    /**
     * Handle payment_intent.payment.paid
     * Fired when a payment intent succeeds (alternative event path).
     */
    private function handlePaymentIntentPaid(array $data): void
    {
        $paymentIntentId = $data['id'] ?? null;
        if (!$paymentIntentId) return;

        DB::transaction(function () use ($data, $paymentIntentId) {
            $payment = Payment::where('payment_reference', $paymentIntentId)->first();
            if (!$payment || $payment->status === 'paid') return;

            $payment->update([
                'status'    => 'paid',
                'paid_at'   => now(),
            ]);

            $order = $payment->order;
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'confirmed',
                    'confirmed_at'   => now(),
                    'escrow_status'  => 'held',
                ]);
                $order->items()->update(['escrow_status' => 'held']);
            }

            // Send payment confirmation email receipt
            NotificationService::sendPaymentSuccess($payment->order, (float) $payment->amount, $payment->payment_method ?? 'online', $paymentIntentId);
        });
    }

    /**
     * Handle checkout_session.expired
     * Fired when a checkout session expires without payment.
     */
    private function handleCheckoutExpired(array $data): void
    {
        $sessionId = $data['id'] ?? null;
        if (!$sessionId) return;

        $payment = Payment::where('checkout_session_id', $sessionId)
            ->where('status', 'pending')
            ->first();

        if ($payment) {
            $payment->update(['status' => 'cancelled']);
            Log::info('[Webhook] Checkout session expired: ' . $sessionId);
        }
    }
}
