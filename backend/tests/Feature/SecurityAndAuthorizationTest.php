<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Security Crafts',
            'slug' => 'security-crafts-' . uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeSeller(string $verificationStatus = 'approved'): User
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
            'commission_rate' => 8.00,
            'listing_limit' => 10,
        ]);

        SellerProfile::create([
            'user_id' => $seller->id,
            'shop_name' => 'Security Test Workshop',
            'shop_slug' => 'security-test-' . uniqid(),
            'verification_status' => $verificationStatus,
            'is_verified' => $verificationStatus === 'approved',
            'is_active' => true,
        ]);

        return $seller;
    }

    private function makeProduct(User $seller, string $name = 'Security Test Bowl', float $price = 500.00): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $this->makeCategory()->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'description' => 'A test craft.',
            'price' => $price,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    public function test_guests_cannot_access_protected_admin_endpoints(): void
    {
        $this->getJson('/api/admin/reports')->assertStatus(401);
        $this->getJson('/api/users')->assertStatus(401);
        $this->getJson('/api/seller/stats')->assertStatus(401);
    }

    public function test_customers_and_sellers_cannot_access_admin_endpoints(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/users')
            ->assertStatus(403);

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/admin/reports')
            ->assertStatus(403);
    }

    public function test_admin_can_assign_a_role_and_provisions_a_pending_storefront(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/users/{$customer->id}", ['role' => 'seller'])
            ->assertOk()
            ->assertJsonPath('data.role', 'seller');

        $this->assertSame('seller', $customer->fresh()->role);
        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $customer->id,
            'verification_status' => 'pending',
            'is_verified' => false,
        ]);
    }

    public function test_admin_cannot_demote_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/users/{$admin->id}", ['role' => 'customer'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_public_catalog_only_lists_products_from_approved_storefronts(): void
    {
        $approvedSeller = $this->makeSeller('approved');
        $pendingSeller = $this->makeSeller('pending');

        $approvedProduct = $this->makeProduct($approvedSeller, 'Approved Artisan Bowl');
        $pendingProduct = $this->makeProduct($pendingSeller, 'Pending Artisan Bowl');

        $response = $this->getJson('/api/products?paginate=0');
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame($approvedProduct->id, $response->json('data.0.id'));

        $this->getJson("/api/products/{$approvedProduct->id}")->assertOk();
        $this->getJson("/api/products/{$pendingProduct->id}")->assertStatus(404);
    }

    public function test_admin_product_oversight_includes_pending_storefronts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pendingSeller = $this->makeSeller('pending');
        $this->makeProduct($pendingSeller, 'Pending Artisan Bowl');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/products?paginate=0')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_seller_analytics_returns_six_month_trend_and_top_products(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller('approved');
        $product = $this->makeProduct($seller, 'Analytics Test Bowl', 500.00);

        $this->actingAs($customer, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_method' => 'Cash on Delivery',
            'shipping_address' => '123 Security Street, Manila',
            'contact_number' => '09171234567',
        ])->assertCreated();

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/seller/stats')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'sales_trend',
                    'top_products',
                    'revenue_this_month',
                    'revenue_last_month',
                    'revenue_growth_percent',
                    'average_order_value',
                ],
            ])
            ->assertJsonCount(6, 'data.sales_trend')
            ->assertJsonPath('data.top_products.0.name', 'Analytics Test Bowl')
            ->assertJsonPath('data.average_order_value', 1000);
    }

    public function test_cms_bridge_requires_a_shared_secret(): void
    {
        config(['services.cms_bridge.key' => 'test-bridge-key']);

        $this->getJson('/api/cms-bridge/users')->assertStatus(401);
        $this->getJson('/api/cms-bridge/reports')->assertStatus(401);

        $this->getJson('/api/cms-bridge/reports', [
            'X-CMS-Bridge-Key' => 'wrong-key',
        ])->assertStatus(401);

        $this->getJson('/api/cms-bridge/reports', [
            'X-CMS-Bridge-Key' => 'test-bridge-key',
        ])->assertOk();
    }

    public function test_admin_reports_include_payout_and_subscription_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = $this->makeSeller('approved');

        PayoutRequest::create([
            'seller_id' => $seller->id,
            'amount' => 250.00,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/reports')
            ->assertOk()
            ->assertJsonPath('data.payout_summary.pending_amount', 250)
            ->assertJsonPath('data.payout_summary.pending_count', 1)
            ->assertJsonStructure([
                'data' => [
                    'payout_summary',
                    'subscription_revenue',
                    'commission_ledger',
                    'total_commission',
                ],
            ]);
    }
}
