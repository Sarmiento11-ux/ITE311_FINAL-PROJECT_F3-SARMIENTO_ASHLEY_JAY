<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'images',
        'status',
        'is_featured',
        'material',
        'dimensions',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'images'      => 'array',
        'is_featured' => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * A product belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A product belongs to a seller (User).
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * A product can appear in many order items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
