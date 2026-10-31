<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_slug',
        'shop_description',
        'shop_logo',
        'shop_banner',
        'location',
        'address',
        'contact_number',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active'   => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * A seller profile belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
