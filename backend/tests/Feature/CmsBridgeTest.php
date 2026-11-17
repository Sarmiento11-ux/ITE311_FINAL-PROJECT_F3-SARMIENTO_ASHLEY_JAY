<?php

namespace Tests\Feature;

use App\Mail\SellerVerificationUpdateMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CmsBridgeTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-bridge-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.cms_bridge.key' => self::KEY]);
        NotificationService::flushSentCache();
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
        $this->getJson('/api/cms-bridge/categories')->assertStatus(401);
        $this->getJson('/api/cms-bridge/sellers')->assertStatus(401);
        $this->patchJson("/api/cms-bridge/products/{$product->id}/status", ['status' => 'inactive'])
            ->assertStatus(401);
        $this->patchJson("/api/cms-bridge/users/{$seller->id}/toggle-active", ['is_active' => false])
            ->assertStatus(401);
        $this->patchJson("/api/cms-bridge/sellers/{$seller->id}/verification", ['status' => 'approved'])
            ->assertStatus(401);
        $this->postJson('/api/cms-bridge/categories', [
            'name' => 'Unauthorized Crafts',
            'slug' => 'unauthorized-crafts',
        ])->assertStatus(401);
        $this->deleteJson("/api/cms-bridge/categories/{$product->category_id}")
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

    public function test_cms_bridge_can_list_and_create_categories(): void
    {
        Category::create([
            'name' => 'Ceramics & Pottery',
            'slug' => 'ceramics-pottery',
            'is_active' => true,
        ]);

        $this->getJson('/api/cms-bridge/categories', $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.slug', 'ceramics-pottery')
            ->assertJsonPath('data.0.products_count', 0);

        $this->postJson('/api/cms-bridge/categories', [
            'name'        => 'Woven Goods',
            'slug'        => 'woven-goods',
            'description' => 'Handwoven baskets, mats, and storage.',
            'image'       => 'https://example.test/woven.jpg',
            'is_active'   => true,
        ], $this->bridgeHeaders())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Woven Goods')
            ->assertJsonPath('data.slug', 'woven-goods')
            ->assertJsonPath('data.products_count', 0);

        $this->assertDatabaseHas('categories', [
            'slug' => 'woven-goods',
            'is_active' => true,
        ]);

        $this->postJson('/api/cms-bridge/categories', [
            'name' => 'Duplicate Weaves',
            'slug' => 'woven-goods',
        ], $this->bridgeHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_cms_bridge_can_update_a_category(): void
    {
        $category = Category::create([
            'name' => 'Woodcrafts',
            'slug' => 'woodcrafts',
            'is_active' => true,
        ]);

        $this->putJson("/api/cms-bridge/categories/{$category->id}", [
            'name'        => 'Carved Woodcrafts',
            'slug'        => 'carved-woodcrafts',
            'description' => 'Hand-carved homeware and decor.',
            'is_active'   => false,
        ], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('data.name', 'Carved Woodcrafts')
            ->assertJsonPath('data.slug', 'carved-woodcrafts')
            ->assertJsonPath('data.is_active', false);

        $category->refresh();
        $this->assertSame('Carved Woodcrafts', $category->name);
        $this->assertFalse((bool) $category->is_active);

        $this->putJson('/api/cms-bridge/categories/999999', [
            'name' => 'Missing',
        ], $this->bridgeHeaders())->assertStatus(404);
    }

    public function test_cms_bridge_deletes_empty_categories_but_blocks_linked_ones(): void
    {
        $empty = Category::create([
            'name' => 'Empty Category',
            'slug' => 'empty-category',
            'is_active' => true,
        ]);

        $this->deleteJson("/api/cms-bridge/categories/{$empty->id}", [], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $empty->id]);

        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller);
        $linked = $product->category;

        $this->deleteJson("/api/cms-bridge/categories/{$linked->id}", [], $this->bridgeHeaders())
            ->assertStatus(422)
            ->assertJsonPath('data.product_count', 1);

        $this->assertDatabaseHas('categories', ['id' => $linked->id]);

        $this->deleteJson('/api/cms-bridge/categories/999999', [], $this->bridgeHeaders())
            ->assertStatus(404);
    }

    public function test_cms_bridge_lists_artisan_storefront_applications(): void
    {
        $pending = $this->makeSeller('pending');
        $this->makeSeller('approved');

        $this->getJson('/api/cms-bridge/sellers?verification_status=pending', $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.id', $pending->id)
            ->assertJsonPath('data.0.shop_name', 'CMS Test Workshop')
            ->assertJsonPath('data.0.products_count', 0)
            ->assertJsonPath('data.0.seller_profile.verification_status', 'pending');
    }

    public function test_cms_bridge_approves_storefront_and_emails_the_artisan(): void
    {
        Mail::fake();

        $seller = $this->makeSeller('pending');

        $this->patchJson("/api/cms-bridge/sellers/{$seller->id}/verification", [
            'status' => 'approved',
        ], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'approved')
            ->assertJsonPath('data.is_verified', true);

        $profile = $seller->fresh()->sellerProfile;
        $this->assertSame('approved', $profile->verification_status);
        $this->assertTrue((bool) $profile->is_verified);
        $this->assertNotNull($profile->verified_at);
        $this->assertNotNull($profile->verification_reviewed_at);

        Mail::assertSent(SellerVerificationUpdateMail::class, function (SellerVerificationUpdateMail $mail) use ($seller) {
            return $mail->hasTo($seller->email) && $mail->status === 'approved';
        });
    }

    public function test_cms_bridge_rejects_storefront_with_moderator_notes(): void
    {
        Mail::fake();

        $seller = $this->makeSeller('pending');
        $notes = 'Please upload a clearer business permit and workshop photo.';

        $this->patchJson("/api/cms-bridge/sellers/{$seller->id}/verification", [
            'status' => 'rejected',
            'notes'  => $notes,
        ], $this->bridgeHeaders())
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'rejected')
            ->assertJsonPath('data.is_verified', false)
            ->assertJsonPath('data.verification_notes', $notes);

        $profile = $seller->fresh()->sellerProfile;
        $this->assertSame('rejected', $profile->verification_status);
        $this->assertFalse((bool) $profile->is_verified);
        $this->assertNull($profile->verified_at);

        Mail::assertSent(SellerVerificationUpdateMail::class, function (SellerVerificationUpdateMail $mail) use ($seller, $notes) {
            return $mail->hasTo($seller->email)
                && $mail->status === 'rejected'
                && $mail->notes === $notes;
        });
    }

    public function test_cms_bridge_seller_verification_validates_status_and_profile(): void
    {
        $seller = $this->makeSeller('pending');

        $this->patchJson("/api/cms-bridge/sellers/{$seller->id}/verification", [
            'status' => 'deleted',
        ], $this->bridgeHeaders())
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->patchJson('/api/cms-bridge/sellers/999999/verification', [
            'status' => 'approved',
        ], $this->bridgeHeaders())->assertStatus(404);

        $sellerWithoutProfile = User::factory()->create(['role' => 'seller']);

        $this->patchJson("/api/cms-bridge/sellers/{$sellerWithoutProfile->id}/verification", [
            'status' => 'approved',
        ], $this->bridgeHeaders())->assertStatus(422);
    }
}
