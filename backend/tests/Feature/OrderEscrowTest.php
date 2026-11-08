<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEscrowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Woven Goods',
            'slug' => 'woven-goods-' . uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeSeller(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
            'commission_rate' => 8.00,
            'listing_limit' => 10,
        ], $attributes));
    }

    private function makeProduct(User $seller, float $price = 1000.00): Product
    {
        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $this->makeCategory()->id,
            'name' => 'Handwoven Test Tote',
            'slug' => 'handwoven-test-tote-' . uniqid(),
            'description' => 'A test craft.',
            'price' => $price,
            'stock' => 10,
            'status' => 'active',
        ]);
    }

    private function placeOrder(User $customer, Product $product, int $quantity = 1)
    {
        return $this->actingAs($customer, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => $quantity],
            ],
            'payment_method' => 'Cash on Delivery',
            'shipping_address' => '123 Test Street, Manila',
            'contact_number' => '09171234567',
        ]);
    }

    public function test_order_number_uses_cm_yyyymmdd_xxxx_format_and_basic_commission_split(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 1000.00);

        $response = $this->placeOrder($customer, $product);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $order = Order::with('items')->firstOrFail();

        $this->assertMatchesRegularExpression('/^CM-\d{8}-\d{4}$/', $order->order_number);
        $this->assertSame('pending', $order->escrow_status);
        $this->assertSame('8.00', $order->items->first()->commission_rate);
        $this->assertEquals(80.00, (float) $order->items->first()->platform_fee);
        $this->assertEquals(920.00, (float) $order->items->first()->seller_payout);
        $this->assertEquals(80.00, (float) $order->platform_fee);
        $this->assertEquals(920.00, (float) $order->seller_payout);
    }

    public function test_pro_seller_receives_97_percent_payout(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller([
            'subscription_plan' => 'pro',
            'commission_rate' => 3.00,
            'listing_limit' => null,
        ]);
        $product = $this->makeProduct($seller, 1000.00);

        $this->placeOrder($customer, $product)->assertCreated();

        $item = Order::firstOrFail()->items()->firstOrFail();

        $this->assertSame('3.00', $item->commission_rate);
        $this->assertEquals(30.00, (float) $item->platform_fee);
        $this->assertEquals(970.00, (float) $item->seller_payout);
    }

    public function test_payment_moves_order_and_items_into_escrow(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 500.00);

        $this->placeOrder($customer, $product)->assertCreated();

        $order = Order::firstOrFail();
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'payment_method' => 'GCash',
            'payment_gateway' => 'paymongo',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        $payment->markAsPaid('pi_test_reference');

        $order->refresh();
        $this->assertSame('held', $order->escrow_status);

        foreach ($order->items as $item) {
            $this->assertSame('held', $item->escrow_status);
        }
    }

    public function test_delivery_releases_escrow_to_seller(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 500.00);

        $this->placeOrder($customer, $product)->assertCreated();

        $order = Order::firstOrFail();
        $order->update(['payment_status' => 'paid', 'escrow_status' => 'held']);

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/status", ['status' => 'delivered']);

        $response->assertOk();

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('released_to_seller', $order->escrow_status);
        $this->assertNotNull($order->escrow_released_at);

        foreach ($order->items as $item) {
            $this->assertSame('released_to_seller', $item->escrow_status);
            $this->assertNotNull($item->escrow_released_at);
        }
    }

    public function test_cancelling_a_paid_order_refunds_escrow(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = $this->makeSeller();
        $product = $this->makeProduct($seller, 500.00);

        $this->placeOrder($customer, $product)->assertCreated();

        $order = Order::firstOrFail();
        $order->update(['payment_status' => 'paid', 'escrow_status' => 'held']);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertOk();

        $order->refresh();
        $this->assertSame('refunded', $order->escrow_status);
        $this->assertSame('refunded', $order->payment_status);
        $this->assertSame('refunded', $order->items()->firstOrFail()->escrow_status);
    }
}
