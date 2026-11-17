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
     * Comprehensive seeders for customers and sellers with complete profile data
     * across different Philippine geographic zones (Luzon, Visayas, Mindanao, Local).
     */
    public function run(): void
    {
        // =========================================================================
        // 1. CUSTOMERS (Various Philippine Delivery Addresses for Zone Testing)
        // =========================================================================

        // Customer 1: Metro Manila (Luzon NCR)
        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name'                => 'Maria Clara Santos',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'phone'               => '+63 917 123 4567',
                'address'             => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                'avatar'              => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );

        // Customer 2: Provincial Luzon (Pampanga)
        User::updateOrCreate(
            ['email' => 'juan@example.com'],
            [
                'name'                => 'Juan Dela Cruz',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'phone'               => '+63 915 234 5678',
                'address'             => 'Block 12 Lot 5 Greenview Subdivision, San Fernando, Pampanga',
                'avatar'              => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );

        // Customer 3: Visayas (Cebu City)
        User::updateOrCreate(
            ['email' => 'carlos.visayas@example.com'],
            [
                'name'                => 'Carlos Miguel Tan',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'phone'               => '+63 928 345 6789',
                'address'             => '24 Mango Avenue, Lahug, Cebu City, Cebu',
                'avatar'              => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );

        // Customer 4: Mindanao (Davao City)
        User::updateOrCreate(
            ['email' => 'fatima.mindanao@example.com'],
            [
                'name'                => 'Fatima Marohom',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'phone'               => '+63 939 456 7890',
                'address'             => '88 Roxas Avenue, Poblacion, Davao City, Davao del Sur',
                'avatar'              => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );

        // Customer 5: Local Mindoro (Ashley Jay Sarmiento)
        User::updateOrCreate(
            ['email' => 'sarmiento@gmail.com'],
            [
                'name'                => 'Ashley Jay Sarmiento',
                'password'            => Hash::make('password'),
                'role'                => 'customer',
                'phone'               => '+63 917 888 9999',
                'address'             => 'Sitio Ilaya, Macatoc, Victoria, Oriental Mindoro',
                'avatar'              => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );

        // =========================================================================
        // 2. SELLERS (Artisans with Distinct Zones, Subscription Tiers, & Profiles)
        // =========================================================================

        // Seller 1: Luzon North (Baguio City) — Pro Tier (3% commission)
        $seller1 = User::updateOrCreate(
            ['email' => 'artisan@example.com'],
            [
                'name'                => 'Elena Bautista',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '+63 920 987 6543',
                'address'             => '14 Session Road, Baguio City, Benguet',
                'avatar'              => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'pro',
                'subscription_status' => 'active',
                'commission_rate'     => 3.00,
                'listing_limit'       => null,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller1->id],
            [
                'shop_name'                => 'Cordillera Weaves',
                'shop_slug'                => 'cordillera-weaves',
                'shop_description'         => 'Preserving indigenous Cordillera weaving traditions through contemporary fashion and sustainable handmade home accessories.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Baguio City, Philippines',
                'address'                  => '14 Session Road, Baguio City, Benguet',
                'contact_number'           => '+63 920 987 6543',
                'is_verified'              => true,
                'is_active'                => true,
                'verification_status'      => 'approved',
                'verified_at'              => now(),
                'verification_reviewed_at' => now(),
            ]
        );

        // Seller 2: Luzon South (Calamba, Laguna) — Basic Tier (5% commission)
        $seller2 = User::updateOrCreate(
            ['email' => 'clayworks@example.com'],
            [
                'name'                => 'Marco Gomez',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '+63 918 555 1234',
                'address'             => 'Brgy. Pansol, Calamba, Laguna',
                'avatar'              => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'commission_rate'     => 5.00,
                'listing_limit'       => 10,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller2->id],
            [
                'shop_name'                => 'Studio Clayworks',
                'shop_slug'                => 'studio-clayworks',
                'shop_description'         => 'Modern stoneware pottery crafted from native Laguna clay. Fired with mineral glazes inspired by Philippine landscapes.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Calamba, Laguna, Philippines',
                'address'                  => 'Brgy. Pansol, Calamba, Laguna',
                'contact_number'           => '+63 918 555 1234',
                'is_verified'              => true,
                'is_active'                => true,
                'verification_status'      => 'approved',
                'verified_at'              => now(),
                'verification_reviewed_at' => now(),
            ]
        );

        // Seller 3: Visayas (Cebu City) — Starter Tier (8% commission)
        $seller3 = User::updateOrCreate(
            ['email' => 'mateo.cebu@example.com'],
            [
                'name'                => 'Mateo Alvarez',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '+63 922 456 7890',
                'address'             => 'Sitio Kawayan, Mandaue City, Cebu',
                'avatar'              => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'commission_rate'     => 8.00,
                'listing_limit'       => 5,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller3->id],
            [
                'shop_name'                => 'Sugbo Rattan & Bamboo Crafts',
                'shop_slug'                => 'sugbo-rattan-crafts',
                'shop_description'         => 'Authentic Cebuano hand-woven rattan baskets, bamboo planters, and functional accent crafts crafted by master Visayan artisans.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Cebu City, Visayas, Philippines',
                'address'                  => 'Sitio Kawayan, Mandaue City, Cebu',
                'contact_number'           => '+63 922 456 7890',
                'is_verified'              => true,
                'is_active'                => true,
                'verification_status'      => 'approved',
                'verified_at'              => now(),
                'verification_reviewed_at' => now(),
            ]
        );

        // Seller 4: Mindoro / Local (Victoria, Oriental Mindoro) — Pro Tier (3% commission)
        $seller4 = User::updateOrCreate(
            ['email' => 'reshally@gmail.com'],
            [
                'name'                => 'Reshally P. Rubion',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '09368288551',
                'address'             => 'Murangan, Victoria, Oriental Mindoro',
                'avatar'              => 'https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'pro',
                'subscription_status' => 'active',
                'commission_rate'     => 3.00,
                'listing_limit'       => null,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller4->id],
            [
                'shop_name'                => 'Mindoro Heritage & Agri-Crafts',
                'shop_slug'                => 'mindoro-heritage-crafts',
                'shop_description'         => 'Pure Mindoro Calamansi concentrate syrups, organic wild honey, and indigenous Mangyan woven handicrafts.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Victoria, Oriental Mindoro, Philippines',
                'address'                  => 'Murangan, Victoria, Oriental Mindoro',
                'contact_number'           => '09368288551',
                'is_verified'              => true,
                'is_active'                => true,
                'verification_status'      => 'approved',
                'verified_at'              => now(),
                'verification_reviewed_at' => now(),
            ]
        );

        // Seller 5: Mindanao (Davao City) — Pro Tier (3% commission)
        $seller5 = User::updateOrCreate(
            ['email' => 'amina.mindanao@example.com'],
            [
                'name'                => 'Datu Bai Amina',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '+63 945 678 1234',
                'address'             => 'Matina Crossing, Davao City, Davao del Sur',
                'avatar'              => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'pro',
                'subscription_status' => 'active',
                'commission_rate'     => 3.00,
                'listing_limit'       => null,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller5->id],
            [
                'shop_name'                => 'Mindanao Yakan & Inabal Tapestries',
                'shop_slug'                => 'mindanao-yakan-inabal',
                'shop_description'         => 'Hand-loomed Inabal abaca cloth and vibrant Yakan geometric tapestries woven by cultural preservation communities in Mindanao.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Davao City, Mindanao, Philippines',
                'address'                  => 'Matina Crossing, Davao City, Davao del Sur',
                'contact_number'           => '+63 945 678 1234',
                'is_verified'              => true,
                'is_active'                => true,
                'verification_status'      => 'approved',
                'verified_at'              => now(),
                'verification_reviewed_at' => now(),
            ]
        );

        // Seller 6: Palawan (Pending Verification — for testing Admin Verification workflows)
        $seller6 = User::updateOrCreate(
            ['email' => 'sarah.pending@example.com'],
            [
                'name'                => 'Sarah Jenkins',
                'password'            => Hash::make('password'),
                'role'                => 'seller',
                'phone'               => '+63 999 111 2233',
                'address'             => 'Rizal Avenue, Puerto Princesa, Palawan',
                'avatar'              => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'commission_rate'     => 8.00,
                'listing_limit'       => 5,
                'is_active'           => true,
            ]
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $seller6->id],
            [
                'shop_name'                => 'Palawan Pearl & Shellworks',
                'shop_slug'                => 'palawan-pearl-shellworks',
                'shop_description'         => 'Handcrafted accessories and home accents made from upcycled mother-of-pearl and natural Palawan sea glass.',
                'shop_logo'                => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=200&q=80',
                'location'                 => 'Puerto Princesa, Palawan, Philippines',
                'address'                  => 'Rizal Avenue, Puerto Princesa, Palawan',
                'contact_number'           => '+63 999 111 2233',
                'is_verified'              => false,
                'is_active'                => true,
                'verification_status'      => 'pending',
                'verified_at'              => null,
                'verification_reviewed_at' => null,
            ]
        );

        // =========================================================================
        // 3. ADMINISTRATOR (Central System Control)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'                => 'Admin CraftMarket',
                'password'            => Hash::make('admin123'),
                'role'                => 'admin',
                'phone'               => '+63 900 000 0000',
                'address'             => 'CraftMarket HQ, Ayala Avenue, Makati City, Metro Manila',
                'avatar'              => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
                'subscription_plan'   => 'basic',
                'subscription_status' => 'active',
                'is_active'           => true,
            ]
        );
    }
}
