<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',        // 'customer', 'seller', 'admin'
        'is_active',   // boolean active/deactivated flag
        'phone',
        'address',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────

    /**
     * A user can have one seller profile.
     */
    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }

    /**
     * A user (customer) can have many orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * A seller can have many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * A customer can have many cart items.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * A customer can have many wishlist items.
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
