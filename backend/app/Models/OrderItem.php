<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'seller_id',
        'product_name',
        'unit_price',
        'price',
        'quantity',
        'subtotal',
        'status',
        'tracking_number',
        'commission_rate',
        'platform_fee',
        'seller_payout',
        'escrow_status',
        'escrow_released_at',
    ];

    protected $casts = [
        'unit_price'         => 'decimal:2',
        'price'              => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'quantity'           => 'integer',
        'commission_rate'    => 'decimal:2',
        'platform_fee'       => 'decimal:2',
        'seller_payout'      => 'decimal:2',
        'escrow_released_at' => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * An order item belongs to an order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * An order item belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * An order item is fulfilled by a seller (User).
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
