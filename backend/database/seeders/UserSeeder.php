<?php

namespace Database\Seeders;

use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Customer Demo User
        $customer = User::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name'     => 'Maria Clara Santos',
                'password' => Hash::make('password'),
                'role'     => 'customer',
                'phone'    => '+63 917 123 4567',
                'address'  => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                'avatar'   => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
            ]
        );

        // 2. Seller Demo User
        $seller = User::updateOrCreate(
            ['email' => 'artisan@example.com'],
            [
                'name'     => 'Elena Bautista',
                'password' => Hash::make('password'),
                'role'     => 'seller',
                'phone'    => '+63 920 987 6543',
                'address'  => '14 Session Road, Baguio City, Benguet',
                'avatar'   => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller->id],
            [
                'shop_name'        => 'Cordillera Weaves',
                'shop_slug'        => 'cordillera-weaves',
                'shop_description' => 'Preserving indigenous Cordillera weaving traditions through contemporary fashion and sustainable handmade home accessories.',
                'shop_logo'        => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=200&q=80',
                'location'         => 'Baguio City, Philippines',
                'contact_number'   => '+63 920 987 6543',
                'is_verified'      => true,
                'is_active'        => true,
            ]
        );

        // 3. Second Seller User
        $seller2 = User::updateOrCreate(
            ['email' => 'clayworks@example.com'],
            [
                'name'     => 'Marco Gomez',
                'password' => Hash::make('password'),
                'role'     => 'seller',
                'phone'    => '+63 918 555 1234',
                'address'  => 'Brgy. Pansol, Calamba, Laguna',
                'avatar'   => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller2->id],
            [
                'shop_name'        => 'Studio Clayworks',
                'shop_slug'        => 'studio-clayworks',
                'shop_description' => 'Modern stoneware pottery crafted from native Laguna clay. Fired with mineral glazes inspired by Philippine landscapes.',
                'shop_logo'        => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=200&q=80',
                'location'         => 'Calamba, Laguna, Philippines',
                'contact_number'   => '+63 918 555 1234',
                'is_verified'      => true,
                'is_active'        => true,
            ]
        );

        // 4. Admin User
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin CraftMarket',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'phone'    => '+63 900 000 0000',
                'address'  => 'CraftMarket HQ, Makati City, Metro Manila',
                'avatar'   => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
            ]
        );
    }
}
