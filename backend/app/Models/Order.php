<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'shipping_fee',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'contact_number',
        'notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'shipping_fee'  => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'confirmed_at'  => 'datetime',
        'shipped_at'    => 'datetime',
        'delivered_at'  => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * An order belongs to a customer (User).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An order can have many order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
