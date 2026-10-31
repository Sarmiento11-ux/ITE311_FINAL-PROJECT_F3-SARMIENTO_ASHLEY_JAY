<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Ceramics & Pottery',
                'slug'        => 'ceramics-pottery',
                'description' => 'Wheel-thrown mugs, planters, bowls, and artisanal vases crafted from Philippine stoneware and terracotta clay.',
                'image'       => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
            [
                'name'        => 'Woven Goods & Bags',
                'slug'        => 'woven-goods-bags',
                'description' => 'Handwoven totes, clutches, and baskets handcrafted from native abaca, tikog, and pandan leaves.',
                'image'       => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
            [
                'name'        => 'Artisan Jewelry',
                'slug'        => 'artisan-jewelry',
                'description' => 'Authentic freshwater pearl bracelets, wire-wrapped earrings, and brass accessories handcrafted by local artisans.',
                'image'       => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
            [
                'name'        => 'Home Decor & Candles',
                'slug'        => 'home-decor-candles',
                'description' => 'Hand-poured soy wax candles, macrame wall hangings, and ambient home accents for warm spaces.',
                'image'       => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
            [
                'name'        => 'Wooden Crafts',
                'slug'        => 'wooden-crafts',
                'description' => 'Solid acacia serving boards, hand-carved kitchenware, and coasters made from sustainable hardwood.',
                'image'       => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
            [
                'name'        => 'Baskets & Storage',
                'slug'        => 'baskets-storage',
                'description' => 'Traditional rattan baskets and functional handcrafted organizers woven by Filipino artisans.',
                'image'       => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=800&q=80',
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
