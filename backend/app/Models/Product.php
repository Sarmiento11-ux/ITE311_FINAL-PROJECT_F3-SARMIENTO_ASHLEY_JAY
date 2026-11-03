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

    protected $appends = [
        'image_url',
        'raw_image_path',
        'avg_rating',
        'review_count',
    ];

    // ─── Accessors & Mutators ──────────────────────────────────────

    /**
     * Get the accessible public URL for the product image.
     * Handles external URLs (e.g. Unsplash), local storage paths (products/...), and null values.
     */
    public function getImageAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url('storage/' . $value);
    }

    /**
     * Get the fallback-safe image URL (defaults to CraftMarket craft placeholder).
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image ?? url('images/craft-placeholder.svg');
    }

    /**
     * Get the raw storage path as stored in MySQL (e.g. "products/abc.jpg" or external URL).
     */
    public function getRawImagePathAttribute(): ?string
    {
        return $this->attributes['image'] ?? null;
    }

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

    /**
     * Average star rating for this product.
     */
    public function getAvgRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Total number of reviews for this product.
     */
    public function getReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * A product can have many reviews.
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * A product can be in many customers' wishlists.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
