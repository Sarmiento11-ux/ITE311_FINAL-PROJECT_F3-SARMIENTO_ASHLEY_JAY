<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $elena = User::where('email', 'artisan@example.com')->first();
        $marco = User::where('email', 'clayworks@example.com')->first();

        $catPottery = Category::where('slug', 'ceramics-pottery')->first();
        $catWoven   = Category::where('slug', 'woven-goods-bags')->first();
        $catJewelry = Category::where('slug', 'artisan-jewelry')->first();
        $catDecor   = Category::where('slug', 'home-decor-candles')->first();
        $catWood    = Category::where('slug', 'wooden-crafts')->first();
        $catBasket  = Category::where('slug', 'baskets-storage')->first();

        $products = [
            [
                'seller_id'   => $marco?->id ?? 1,
                'category_id' => $catPottery?->id ?? 1,
                'name'        => 'Earthy Terracotta Coffee Mug',
                'slug'        => 'earthy-terracotta-coffee-mug',
                'description' => "Hand-thrown on a traditional potter's wheel using locally sourced Laguna stoneware clay. Features a natural unglazed earthy exterior combined with a food-safe glazed interior.",
                'price'       => 420.00,
                'stock'       => 8,
                'image'       => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => '100% Native Clay, Non-Toxic Mineral Glaze',
                'dimensions'  => '350ml capacity • 9.0cm height x 8.5cm diameter',
            ],
            [
                'seller_id'   => $elena?->id ?? 1,
                'category_id' => $catWoven?->id ?? 2,
                'name'        => 'Handwoven Abaca Market Tote',
                'slug'        => 'handwoven-abaca-market-tote',
                'description' => 'Tightly handwoven from sun-dried natural abaca fibers by women artisan weavers. Features durable vegetable-tanned genuine leather shoulder straps.',
                'price'       => 780.00,
                'stock'       => 12,
                'image'       => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => '100% Natural Abaca Fiber, Veg-Tanned Leather, Brass Rivets',
                'dimensions'  => '38cm width x 32cm height x 12cm base gusset',
            ],
            [
                'seller_id'   => $elena?->id ?? 1,
                'category_id' => $catJewelry?->id ?? 3,
                'name'        => 'Palawan Freshwater Pearl Bracelet',
                'slug'        => 'palawan-freshwater-pearl-bracelet',
                'description' => 'Ethically harvested cultured freshwater pearls from Palawan, hand-strung on durable silk cord with a 14k gold-filled lobster clasp.',
                'price'       => 350.00,
                'stock'       => 15,
                'image'       => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => 'Genuine Freshwater Pearls, 14k Gold-Filled Clasp, Silk Cord',
                'dimensions'  => '17.5cm length with 3cm extender chain',
            ],
            [
                'seller_id'   => $elena?->id ?? 1,
                'category_id' => $catDecor?->id ?? 4,
                'name'        => 'Scented Soy Wax Candle in Coconut Shell',
                'slug'        => 'scented-soy-wax-candle-coconut-shell',
                'description' => 'Hand-poured 100% natural soy wax infused with sampaguita and vanilla essential oils inside a reclaimed polished Philippine coconut shell.',
                'price'       => 290.00,
                'stock'       => 20,
                'image'       => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => false,
                'material'    => '100% Soy Wax, Upcycled Coconut Shell, Cotton Wick',
                'dimensions'  => '220g wax weight • Approx. 40-45 hours burn time',
            ],
            [
                'seller_id'   => $marco?->id ?? 1,
                'category_id' => $catWood?->id ?? 5,
                'name'        => 'Handcrafted Acacia Wood Serving Board',
                'slug'        => 'handcrafted-acacia-wood-serving-board',
                'description' => 'Carved from a single piece of sustainably harvested Philippine acacia wood. Sealed with food-grade organic beeswax and coconut oil.',
                'price'       => 650.00,
                'stock'       => 6,
                'image'       => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => 'Sustainably Harvested Acacia Hardwood, Organic Beeswax Finish',
                'dimensions'  => '42cm length x 22cm width x 2.2cm thickness',
            ],
            [
                'seller_id'   => $elena?->id ?? 1,
                'category_id' => $catBasket?->id ?? 6,
                'name'        => 'Bicolano Tikog & Seagrass Storage Basket',
                'slug'        => 'bicolano-tikog-seagrass-storage-basket',
                'description' => 'Sturdy cylindrical storage basket hand-plaited from sun-dried Bicol seagrass and tikog reeds with two reinforced side handles.',
                'price'       => 520.00,
                'stock'       => 9,
                'image'       => 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => false,
                'material'    => 'Natural Bicol Seagrass, Tikog Grass, Cotton Cord',
                'dimensions'  => '30cm diameter x 28cm height',
            ],
            [
                'seller_id'   => $elena?->id ?? 1,
                'category_id' => $catDecor?->id ?? 4,
                'name'        => 'Boho Cotton Macrame Wall Hanging',
                'slug'        => 'boho-cotton-macrame-wall-hanging',
                'description' => 'Intricately knotted bohemian wall tapestry made of unbleached natural cotton rope suspended on a smoothed driftwood branch.',
                'price'       => 890.00,
                'stock'       => 4,
                'image'       => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => '100% Unbleached Cotton Macrame Rope, Natural Driftwood',
                'dimensions'  => '45cm dowel width x 75cm total hanging length',
            ],
            [
                'seller_id'   => $marco?->id ?? 1,
                'category_id' => $catPottery?->id ?? 1,
                'name'        => 'Matte Olive Ceramic Planter with Saucer',
                'slug'        => 'matte-olive-ceramic-planter-saucer',
                'description' => 'Wheel-thrown cylindrical indoor plant pot with a built-in drainage hole and matching catch saucer in a soft matte olive green finish.',
                'price'       => 480.00,
                'stock'       => 7,
                'image'       => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => false,
                'material'    => 'High-fire Stoneware Clay, Matte Glaze',
                'dimensions'  => '14cm diameter x 13cm height with drainage hole',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
