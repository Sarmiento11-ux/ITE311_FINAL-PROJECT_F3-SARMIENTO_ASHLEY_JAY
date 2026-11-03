<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * The customer who owns this wishlist item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The saved product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
