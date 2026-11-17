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
        $elena    = User::where('email', 'artisan@example.com')->first();
        $marco    = User::where('email', 'clayworks@example.com')->first();
        $mateo    = User::where('email', 'mateo.cebu@example.com')->first();
        $reshally = User::where('email', 'reshally@gmail.com')->first();
        $amina    = User::where('email', 'amina.mindanao@example.com')->first();

        $catPottery = Category::where('slug', 'ceramics-pottery')->first();
        $catWoven   = Category::where('slug', 'woven-goods-bags')->first();
        $catJewelry = Category::where('slug', 'artisan-jewelry')->first();
        $catDecor   = Category::where('slug', 'home-decor-candles')->first();
        $catWood    = Category::where('slug', 'wooden-crafts')->first();
        $catBasket  = Category::where('slug', 'baskets-storage')->first();

        $products = [
            // ─── Studio Clayworks (Marco Gomez — Calamba, Laguna / Luzon) ─────────
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

            // ─── Cordillera Weaves (Elena Bautista — Baguio City / Luzon) ─────────
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

            // ─── Sugbo Rattan Crafts (Mateo Alvarez — Cebu City / Visayas) ────────
            [
                'seller_id'   => $mateo?->id ?? ($elena?->id ?? 1),
                'category_id' => $catBasket?->id ?? 6,
                'name'        => 'Sugbo Handwoven Hanging Rattan Planter',
                'slug'        => 'sugbo-hanging-rattan-planter',
                'description' => 'Classic Cebuano hanging planter meticulously woven from seasoned rattan peel. Comes with durable braided abaca ropes ready for indoor hanging.',
                'price'       => 550.00,
                'stock'       => 14,
                'image'       => 'https://images.unsplash.com/photo-1545241047-6083a3684587?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => '100% Philippine Rattan, Braided Abaca Hanging Cord',
                'dimensions'  => '22cm diameter x 18cm depth • 60cm rope drop',
            ],
            [
                'seller_id'   => $mateo?->id ?? ($elena?->id ?? 1),
                'category_id' => $catWood?->id ?? 5,
                'name'        => 'Cebuano Bamboo Desktop Desk Organizer',
                'slug'        => 'cebuano-bamboo-desk-organizer',
                'description' => 'Multi-compartment organizer fashioned from seasoned Visayan moso bamboo. Designed for stationery, eyeglasses, and daily home office essentials.',
                'price'       => 380.00,
                'stock'       => 18,
                'image'       => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => false,
                'material'    => 'Solid Philippine Bamboo, Natural Beeswax Polish',
                'dimensions'  => '25cm length x 15cm width x 10cm height',
            ],

            // ─── Mindoro Heritage (Reshally P. Rubion — Victoria / Mindoro) ───────
            [
                'seller_id'   => $reshally?->id ?? ($elena?->id ?? 1),
                'category_id' => $catDecor?->id ?? 4,
                'name'        => 'Mindoro Pure Calamansi Syrup (500ml)',
                'slug'        => 'mindoro-pure-calamansi-syrup-500ml',
                'description' => 'Freshly extracted from sun-ripened Calamansi orchards in Victoria, Oriental Mindoro. Pure fruit concentrate simmered with natural cane sugar.',
                'price'       => 450.00,
                'stock'       => 25,
                'image'       => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => '100% Real Victoria Calamansi Juice, Pure Cane Sugar',
                'dimensions'  => '500ml glass bottle with tamper-evident seal',
            ],
            [
                'seller_id'   => $reshally?->id ?? ($elena?->id ?? 1),
                'category_id' => $catBasket?->id ?? 6,
                'name'        => 'Mangyan Hanunuo Woven Beaded Basket',
                'slug'        => 'mangyan-hanunuo-woven-beaded-basket',
                'description' => 'Traditional small utility basket woven from nito vine and rattan by indigenous Hanunuo Mangyan women artisans of Oriental Mindoro.',
                'price'       => 680.00,
                'stock'       => 8,
                'image'       => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => 'Nito Vine, Buri Palm Strips, Natural Vegetable Dye',
                'dimensions'  => '20cm diameter x 15cm height',
            ],

            // ─── Mindanao Yakan Silks (Datu Bai Amina — Davao / Mindanao) ─────────
            [
                'seller_id'   => $amina?->id ?? ($elena?->id ?? 1),
                'category_id' => $catDecor?->id ?? 4,
                'name'        => 'Mindanao Yakan Hand-Loomed Table Runner',
                'slug'        => 'mindanao-yakan-table-runner',
                'description' => 'Vibrant geometric patterns woven on a traditional backstrap loom by master Yakan weavers in Davao. A timeless centerpiece for dining or credenzas.',
                'price'       => 1250.00,
                'stock'       => 6,
                'image'       => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => true,
                'material'    => 'Hand-Spun Cotton and Silk Blend Threads, Colorfast Pigment',
                'dimensions'  => '180cm length x 35cm width with fringed edges',
            ],
            [
                'seller_id'   => $amina?->id ?? ($elena?->id ?? 1),
                'category_id' => $catWoven?->id ?? 2,
                'name'        => 'Davao T’nalak Abaca Accent Cushion Cover',
                'slug'        => 'davao-tnalak-abaca-cushion-cover',
                'description' => 'Sacred T’nalak dream-weaving abaca cloth crafted in Mindanao, converted into a high-end decorative throw pillow cover with a hidden zipper.',
                'price'       => 620.00,
                'stock'       => 11,
                'image'       => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
                'status'      => 'active',
                'is_featured' => false,
                'material'    => 'Genuine T’nalak Tie-Dyed Abaca Fiber, Cotton Canvas Backing',
                'dimensions'  => '45cm x 45cm (18in x 18in) cushion size',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
