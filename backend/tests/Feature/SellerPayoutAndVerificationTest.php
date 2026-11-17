<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerPayoutAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Woodcrafts',
            'slug' => 'woodcrafts-' . uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeSeller(array $attributes = [], array $profile = []): User
    {
        $seller = User::factory()->create(array_merge([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
            'commission_rate' => 8.00,
            'listing_limit' => 10,
        ], $attributes));

        SellerProfile::create(array_merge([
            'user_id' => $seller->id,
            'shop_name' => 'Test Artisan Workshop',
            'shop_slug' => 'test-artisan-' . uniqid(),
            'location' => 'Baguio City, Philippines',
            'verification_status' => 'pending',
            'is_verified' => false,
            'is_active' => true,
        ], $profile));

        return $seller;
    }

    private function makeProduct(User $seller, float $price = 1000.00): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $this->makeCategory()->id,
            'name' => 'Handcarved Test Bowl',
            'slug' => 'handcarved-test-bowl-' . uniqid(),
            'description' => 'A test craft.',
            'price' => $price,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function placeOrder(User $customer, Product $product, int $quantity = 1): Order
    {
        $this->actingAs($customer, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => $quantity],
            ],
            'payment_method' => 'Cash on Delivery',
            'shipping_address' => '123 Test Street, Manila',
            'contact_number' => '09171234567',
        ])->assertCreated();

        return Order::with('items')->firstOrFail();
    }

    public function test_admin_can_approve_and_reject_a_seller_storefront(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = $this->makeSeller();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/sellers/{$seller->id}/verification", [
                'status' => 'approved',
                'notes' => 'Valid workshop documents submitted.',
            ])
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'approved');

        $profile = $seller->sellerProfile()->firstOrFail();
        $this->assertTrue((bool) $profile->is_verified);
        $this->assertSame('approved', $profile->verification_status);
        $this->assertNotNull($profile->verified_at);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/sellers/{$seller->id}/verification", [
                'status' => 'rejected',
                'notes' => 'Incomplete business permit.',
            ])
            ->assertOk();

        $profile->refresh();
        $this->assertFalse((bool) $profile->is_verified);
        $this->assertSame('rejected', $profile->verification_status);
        $this->assertNull($profile->verified_at);
    }

    public function test_seller_can_request_payout_only_against_released_escrow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 1000.00);

        $order = $this->placeOrder($customer, $product);
        $item = $order->items()->firstOrFail();

        // Escrow must be released before it can fund a payout request.
        $this->actingAs($seller, 'sanctum')
            ->postJson('/api/seller/payouts', ['amount' => 100])
            ->assertStatus(422);

        $item->update([
            'escrow_status' => 'released_to_seller',
            'escrow_released_at' => now(),
        ]);

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/seller/payouts')
            ->assertOk()
            ->assertJsonPath('data.released_earnings', 920)
            ->assertJsonPath('data.available_balance', 920);

        $this->actingAs($seller, 'sanctum')
            ->postJson('/api/seller/payouts', [
                'amount' => 500,
                'payout_method' => 'GCash',
                'account_name' => 'Test Artisan',
                'account_number' => '09171234567',
            ])
            ->assertCreated()
            ->assertJsonPath('data.available_balance', 420);

        $payout = PayoutRequest::firstOrFail();
        $this->assertSame('pending', $payout->status);

        // A second request cannot exceed the remaining balance.
        $this->actingAs($seller, 'sanctum')
            ->postJson('/api/seller/payouts', ['amount' => 500])
            ->assertStatus(422);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/payouts')
            ->assertOk()
            ->assertJsonPath('data.summary.pending_amount', 500);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/payouts/{$payout->id}", ['action' => 'approve'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/payouts/{$payout->id}", ['action' => 'pay'])
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/seller/payouts')
            ->assertOk()
            ->assertJsonPath('data.paid_amount', 500)
            ->assertJsonPath('data.available_balance', 420);
    }

    public function test_admin_can_manually_release_escrow_for_a_paid_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 500.00);

        $order = $this->placeOrder($customer, $product);
        $order->update(['payment_status' => 'paid', 'escrow_status' => 'held']);
        $order->items()->update(['escrow_status' => 'held']);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/escrow", ['action' => 'release'])
            ->assertOk()
            ->assertJsonPath('data.escrow_status', 'released_to_seller');

        $order->refresh();
        $this->assertSame('released_to_seller', $order->escrow_status);
        $this->assertNotNull($order->escrow_released_at);
        $this->assertSame('released_to_seller', $order->items()->firstOrFail()->escrow_status);
    }

    public function test_qr_dispatch_code_can_be_generated_and_confirmed_by_the_seller(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 500.00);

        $order = $this->placeOrder($customer, $product);
        $item = $order->items()->firstOrFail();

        $codeResponse = $this->actingAs($seller, 'sanctum')
            ->postJson("/api/seller/order-items/{$item->id}/dispatch-code")
            ->assertOk();

        $code = $codeResponse->json('data.dispatch_code');
        $this->assertNotEmpty($code);
        $this->assertStringStartsWith('CM-DSP-', $code);

        $this->actingAs($seller, 'sanctum')
            ->postJson("/api/seller/order-items/{$item->id}/dispatch-confirm", [
                'dispatch_code' => 'CM-DSP-WRONGCODE',
            ])
            ->assertStatus(422);

        $this->actingAs($seller, 'sanctum')
            ->postJson("/api/seller/order-items/{$item->id}/dispatch-confirm", [
                'dispatch_code' => $code,
                'tracking_number' => 'JRS-1234567890',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped');

        $item->refresh();
        $this->assertSame('shipped', $item->status);
        $this->assertSame('JRS-1234567890', $item->tracking_number);
        $this->assertNotNull($item->dispatched_at);
        $this->assertNotNull($item->dispatch_confirmed_at);
    }
}
