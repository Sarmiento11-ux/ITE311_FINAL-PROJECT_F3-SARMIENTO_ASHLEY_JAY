<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the local-only PayMongo sandbox simulator used for offline demos.
 */
class SandboxPaymentSimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No real key on this machine, but the demo fallback is enabled.
        config([
            'services.paymongo.secret_key'       => null,
            'services.paymongo.simulation_enabled' => true,
            'services.paymongo.frontend_url'     => 'http://localhost:5173',
        ]);
    }

    private function makeOrder(User $customer): Order
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'subscription_plan' => 'basic',
            'subscription_status' => 'active',
            'commission_rate' => 8.00,
            'listing_limit' => 10,
        ]);

        $category = Category::create([
            'name' => 'Woven Goods',
            'slug' => 'woven-goods-' . uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Handwoven Test Tote',
            'slug' => 'handwoven-test-tote-' . uniqid(),
            'description' => 'A test craft.',
            'price' => 1000.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($customer, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'payment_method'   => 'PayMongo Online Payment',
            'shipping_address' => '123 Test Street, Manila',
            'contact_number'   => '09171234567',
        ])->assertCreated();

        return Order::latest('id')->firstOrFail();
    }

    public function test_checkout_falls_back_to_a_tagged_sandbox_session(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        $response = $this->postJson('/api/payments/checkout-session', [
            'order_id' => $order->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('mode', 'sandbox')
            ->assertJsonPath('success', true);

        $payment = Payment::where('order_id', $order->id)->firstOrFail();

        $this->assertSame('paymongo_sandbox', $payment->payment_gateway);
        $this->assertSame('pending', $payment->status);
        $this->assertStringContainsString(
            '/payment/checkout?order_id=' . $order->id,
            $payment->checkout_url
        );
    }

    public function test_simulated_payment_marks_order_paid_and_holds_escrow(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        $this->postJson('/api/payments/checkout-session', ['order_id' => $order->id])
            ->assertOk();

        $response = $this->postJson('/api/payments/simulate', [
            'order_id' => $order->id,
            'outcome'  => 'paid',
        ]);

        $response->assertOk()
            ->assertJsonPath('mode', 'sandbox')
            ->assertJsonPath('data.payment_status', 'paid');

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('held', $order->escrow_status);
        $this->assertSame('held', $order->items()->firstOrFail()->escrow_status);

        $payment = Payment::where('order_id', $order->id)->latest('id')->firstOrFail();
        $this->assertSame('paid', $payment->status);
        $this->assertStringStartsWith('SIMULATED-', $payment->payment_reference);
    }

    public function test_simulated_cancellation_leaves_order_unpaid(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        $this->postJson('/api/payments/checkout-session', ['order_id' => $order->id])
            ->assertOk();

        $this->postJson('/api/payments/simulate', [
            'order_id' => $order->id,
            'outcome'  => 'cancelled',
        ])->assertOk();

        $order->refresh();
        $this->assertSame('unpaid', $order->payment_status);

        $payment = Payment::where('order_id', $order->id)->latest('id')->firstOrFail();
        $this->assertSame('cancelled', $payment->status);
    }

    public function test_simulation_is_rejected_when_disabled(): void
    {
        config(['services.paymongo.simulation_enabled' => false]);

        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        $this->postJson('/api/payments/checkout-session', ['order_id' => $order->id])
            ->assertStatus(422)
            ->assertJsonPath('data.configured', false);

        $this->postJson('/api/payments/simulate', ['order_id' => $order->id])
            ->assertForbidden();
    }

    public function test_another_customer_cannot_settle_the_order(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $intruder = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($owner);

        $this->postJson('/api/payments/checkout-session', ['order_id' => $order->id])
            ->assertOk();

        $this->actingAs($intruder, 'sanctum')
            ->postJson('/api/payments/simulate', ['order_id' => $order->id])
            ->assertForbidden();

        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }
}
