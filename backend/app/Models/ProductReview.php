<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'order_item_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────

    /**
     * The customer who wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The product being reviewed.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The qualifying order item (confirms purchase).
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
