<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'maria@example.com')->first();
        if (!$customer) return;

        $tote = Product::where('slug', 'handwoven-abaca-market-tote')->first();
        $mug  = Product::where('slug', 'earthy-terracotta-coffee-mug')->first();
        $candle = Product::where('slug', 'scented-soy-wax-candle-coconut-shell')->first();

        if ($tote && $mug) {
            // Order 1: Completed / Delivered
            $order1 = Order::updateOrCreate(
                ['order_number' => 'CM-89214'],
                [
                    'user_id'          => $customer->id,
                    'subtotal'         => $tote->price + $mug->price,
                    'shipping_fee'     => 120.00,
                    'total_amount'     => $tote->price + $mug->price + 120.00,
                    'status'           => 'delivered',
                    'payment_method'   => 'GCash',
                    'payment_status'   => 'paid',
                    'shipping_address' => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                    'contact_number'   => '+63 917 123 4567',
                    'notes'            => 'Please ring doorbell upon delivery.',
                    'confirmed_at'     => now()->subDays(5),
                    'shipped_at'       => now()->subDays(3),
                    'delivered_at'     => now()->subDay(),
                ]
            );

            $order1->items()->updateOrCreate(
                ['order_id' => $order1->id, 'product_id' => $tote->id],
                [
                    'seller_id'    => $tote->seller_id,
                    'product_name' => $tote->name,
                    'unit_price'   => $tote->price,
                    'quantity'     => 1,
                    'subtotal'     => $tote->price,
                ]
            );

            $order1->items()->updateOrCreate(
                ['order_id' => $order1->id, 'product_id' => $mug->id],
                [
                    'seller_id'    => $mug->seller_id,
                    'product_name' => $mug->name,
                    'unit_price'   => $mug->price,
                    'quantity'     => 1,
                    'subtotal'     => $mug->price,
                ]
            );
        }

        if ($candle) {
            // Order 2: Pending
            $order2 = Order::updateOrCreate(
                ['order_number' => 'CM-90412'],
                [
                    'user_id'          => $customer->id,
                    'subtotal'         => $candle->price * 2,
                    'shipping_fee'     => 80.00,
                    'total_amount'     => ($candle->price * 2) + 80.00,
                    'status'           => 'pending',
                    'payment_method'   => 'Cash on Delivery',
                    'payment_status'   => 'unpaid',
                    'shipping_address' => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                    'contact_number'   => '+63 917 123 4567',
                    'notes'            => 'Fragile package.',
                ]
            );

            $order2->items()->updateOrCreate(
                ['order_id' => $order2->id, 'product_id' => $candle->id],
                [
                    'seller_id'    => $candle->seller_id,
                    'product_name' => $candle->name,
                    'unit_price'   => $candle->price,
                    'quantity'     => 2,
                    'subtotal'     => $candle->price * 2,
                ]
            );
        }
    }
}
