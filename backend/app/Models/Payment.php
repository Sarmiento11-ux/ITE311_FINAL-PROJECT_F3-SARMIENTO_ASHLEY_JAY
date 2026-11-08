<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'checkout_session_id',
        'checkout_url',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * A payment belongs to an order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * A payment belongs to the customer who made it.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ───────────────────────────────────────────────────

    /**
     * Mark payment as paid and update related order.
     */
    public function markAsPaid(string $reference = null): void
    {
        $this->update([
            'status'             => 'paid',
            'paid_at'            => now(),
            'payment_reference'  => $reference ?? $this->payment_reference,
        ]);

        // Also update the order's payment_status and move funds into escrow
        $order = $this->order;

        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'escrow_status'  => 'held',
            ]);

            $order->items()->update(['escrow_status' => 'held']);
        }
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Mark payment as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
