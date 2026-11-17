<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsBridgeTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-bridge-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.cms_bridge.key' => self::KEY]);
    }

    private function bridgeHeaders(): array
    {
        return ['X-CMS-Bridge-Key' => self::KEY];
    }

    private function makeSeller(string $verificationStatus = 'pending'): User
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
        ]);

        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'CMS Test Workshop',
            'shop_slug' => 'cms-test-workshop-' . uniqid(),
            'verification_status' => $verificationStatus,
            'is_verified' => $verificationStatus === 'approved',
            'is_active' => true,
        ]);

        return $seller;
    }

    private function makeProduct(User $seller, array $attributes = []): Product
    {
        $category = Category::create([
            'name' => 'CMS Crafts',
            'slug' => 'cms-crafts-' . uniqid(),
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'CMS Test Basket',
            'slug' => 'cms-test-basket-' . uniqid(),
            'description' => 'A test craft listing.',
            'price' => 750.00,
            'stock' => 5,
            'status' => 'active',
        ], $attributes));
    }

    public function test_cms_bridge_write_endpoints_require_the_shared_secret(): void
    {
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller);

        $this->getJson('/api/cms-bridge/products')->assertStatus(401);
        $this->patchJson("/api/cms-bridge/products/{$product->id}/status", ['status' => 'inactive'])
            ->assertStatus(401);
        $this->patchJson("/api/cms-bridge/users/{$seller->id}/toggle-active", ['is_active' => false])
            ->assertStatus(401);

        $this->getJson('/api/cms-bridge/products', [
            'X-CMS-Bridge-Key' => 'wrong-key',
        ])->assertStatus(401);

        $this->getJson('/api/cms-bridge/products', $this->bridgeHeaders())->assertOk();
    }

    public function test_cms_bridge_fails_closed_when_no_key_is_configured(): void
    {
        config(['services.cms_bridge.key' => '']);

        $this->getJson('/api/cms-bridge/products', $this->bridgeHeaders())
            ->assertStatus(503);
    }

    public function test_cms_bridge_lists_products_including_unverified_artisans(): void
    {
        $seller = $this->makeSeller('pending');
        $product = $this->makeProduct($seller);

        $this->getJson('/api/cms-bridge/products', $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.id', $product->id)
            ->assertJsonPath('data.0.seller.id', $seller->id)
            ->assertJsonPath('meta.statuses', ['active', 'inactive', 'out_of_stock', 'archived']);
    }

    public function test_cms_bridge_can_moderate_product_status(): void
    {
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller);

        $this->patchJson("/api/cms-bridge/products/{$product->id}/status", [
            'status' => 'archived',
        ], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');

        $this->assertSame('archived', $product->fresh()->status);

        $this->patchJson("/api/cms-bridge/products/{$product->id}/status", [
            'status' => 'deleted',
        ], $this->bridgeHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->patchJson('/api/cms-bridge/products/999999/status', [
            'status' => 'inactive',
        ], $this->bridgeHeaders())->assertStatus(404);
    }

    public function test_cms_bridge_deactivation_revokes_active_sessions(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->createToken('cms-bridge-test');

        $this->assertSame(1, $customer->tokens()->count());

        $this->patchJson("/api/cms-bridge/users/{$customer->id}/toggle-active", [
            'is_active' => false,
        ], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('data.revoked_tokens', 1);

        $customer->refresh();
        $this->assertFalse((bool) $customer->is_active);
        $this->assertSame(0, $customer->tokens()->count());

        $this->patchJson("/api/cms-bridge/users/{$customer->id}/toggle-active", [
            'is_active' => true,
        ], $this->bridgeHeaders())
            ->assertOk();

        $this->assertTrue((bool) $customer->fresh()->is_active);
    }

    public function test_cms_bridge_cannot_deactivate_administrators(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->patchJson("/api/cms-bridge/users/{$admin->id}/toggle-active", [
            'is_active' => false,
        ], $this->bridgeHeaders())
            ->assertStatus(422);

        $this->assertTrue((bool) $admin->fresh()->is_active);
    }
}
