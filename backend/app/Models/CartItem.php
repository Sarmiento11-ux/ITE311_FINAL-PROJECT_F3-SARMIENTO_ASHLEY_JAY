<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * The customer who owns this cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The product in the cart.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
