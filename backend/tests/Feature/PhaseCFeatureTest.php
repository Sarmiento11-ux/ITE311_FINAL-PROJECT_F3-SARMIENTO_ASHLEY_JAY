<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhaseCFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Handmade Pottery',
            'slug' => 'handmade-pottery-' . Str::random(5),
            'description' => 'Earthenware and ceramic items',
        ]);
    }

    private function makeProduct(User $seller, Category $category, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'seller_id'   => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Artisan Pot',
            'slug'        => 'artisan-pot-' . Str::random(5),
            'price'       => 450.00,
            'stock'       => 10,
            'status'      => 'published',
            'is_featured' => false,
        ], $attributes));
    }

    public function test_guest_cannot_toggle_featured_product(): void
    {
        $response = $this->patchJson('/api/products/1/featured');
        $response->assertStatus(401);
    }

    public function test_basic_seller_cannot_feature_product(): void
    {
        $category = $this->makeCategory();
        $basicSeller = User::factory()->create([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
        ]);

        $product = $this->makeProduct($basicSeller, $category, ['is_featured' => false]);

        $response = $this->actingAs($basicSeller)
            ->patchJson("/api/products/{$product->id}/featured");

        $response->assertStatus(403);
    }

    public function test_pro_seller_can_toggle_featured_status_with_cap(): void
    {
        $category = $this->makeCategory();
        $proSeller = User::factory()->create([
            'role' => 'seller',
            'subscription_plan' => 'pro',
            'subscription_status' => 'active',
        ]);

        $products = [];
        for ($i = 0; $i < 4; $i++) {
            $products[] = $this->makeProduct($proSeller, $category, ['is_featured' => false]);
        }

        // Toggle first 3 products to featured
        for ($i = 0; $i < 3; $i++) {
            $res = $this->actingAs($proSeller)
                ->patchJson("/api/products/{$products[$i]->id}/featured");
            $res->assertStatus(200)
                ->assertJson(['success' => true, 'is_featured' => true]);
        }

        // 4th product should be blocked by 3-craft limit
        $res4 = $this->actingAs($proSeller)
            ->patchJson("/api/products/{$products[3]->id}/featured");
        $res4->assertStatus(422)
            ->assertJson(['success' => false]);

        // Unfeaturing product 0 should work and free up a slot
        $resUnfeature = $this->actingAs($proSeller)
            ->patchJson("/api/products/{$products[0]->id}/featured");
        $resUnfeature->assertStatus(200)
            ->assertJson(['success' => true, 'is_featured' => false]);

        // Now 4th product can be featured
        $res4Retry = $this->actingAs($proSeller)
            ->patchJson("/api/products/{$products[3]->id}/featured");
        $res4Retry->assertStatus(200)
            ->assertJson(['success' => true, 'is_featured' => true]);
    }

    public function test_customer_can_submit_custom_inquiry(): void
    {
        $category = $this->makeCategory();
        $seller = User::factory()->create(['role' => 'seller']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->makeProduct($seller, $category, [
            'name' => 'Carved Narra Chest',
        ]);

        $payload = [
            'customer_name' => 'Maria Clara',
            'customer_email' => 'maria@example.ph',
            'customer_phone' => '0917-123-4567',
            'custom_description' => 'I would like custom floral carvings on the top lid with brass hardware.',
            'preferred_specs' => '24 x 16 x 12 inches',
            'desired_quantity' => 1,
            'contact_preference' => 'email',
        ];

        $response = $this->actingAs($customer)
            ->postJson("/api/products/{$product->id}/inquiry", $payload);
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => 'Carved Narra Chest',
                ],
            ]);
    }
}
