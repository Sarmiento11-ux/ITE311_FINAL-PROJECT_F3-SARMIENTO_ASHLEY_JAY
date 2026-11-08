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
            // Order 1: Completed / Delivered — escrow released to artisan.
            $totePrice = (float) $tote->price;
            $mugPrice  = (float) $mug->price;
            $toteFee   = round($totePrice * 0.08, 2);
            $mugFee    = round($mugPrice * 0.08, 2);

            $order1 = Order::updateOrCreate(
                ['order_number' => 'CM-20261103-0001'],
                [
                    'user_id'          => $customer->id,
                    'subtotal'         => $totePrice + $mugPrice,
                    'shipping_fee'     => 120.00,
                    'total_amount'     => $totePrice + $mugPrice + 120.00,
                    'status'           => 'delivered',
                    'payment_method'   => 'GCash',
                    'payment_status'   => 'paid',
                    'escrow_status'    => 'released_to_seller',
                    'platform_fee'     => round($toteFee + $mugFee, 2),
                    'seller_payout'    => round(($totePrice - $toteFee) + ($mugPrice - $mugFee), 2),
                    'shipping_address' => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                    'contact_number'   => '+63 917 123 4567',
                    'notes'            => 'Please ring doorbell upon delivery.',
                    'confirmed_at'     => now()->subDays(5),
                    'shipped_at'       => now()->subDays(3),
                    'delivered_at'     => now()->subDay(),
                    'escrow_released_at' => now()->subDay(),
                ]
            );

            $order1->items()->updateOrCreate(
                ['order_id' => $order1->id, 'product_id' => $tote->id],
                [
                    'seller_id'       => $tote->seller_id,
                    'product_name'    => $tote->name,
                    'unit_price'      => $totePrice,
                    'quantity'        => 1,
                    'subtotal'        => $totePrice,
                    'status'          => 'delivered',
                    'commission_rate' => 8.00,
                    'platform_fee'    => $toteFee,
                    'seller_payout'   => round($totePrice - $toteFee, 2),
                    'escrow_status'   => 'released_to_seller',
                    'escrow_released_at' => now()->subDay(),
                ]
            );

            $order1->items()->updateOrCreate(
                ['order_id' => $order1->id, 'product_id' => $mug->id],
                [
                    'seller_id'       => $mug->seller_id,
                    'product_name'    => $mug->name,
                    'unit_price'      => $mugPrice,
                    'quantity'        => 1,
                    'subtotal'        => $mugPrice,
                    'status'          => 'delivered',
                    'commission_rate' => 8.00,
                    'platform_fee'    => $mugFee,
                    'seller_payout'   => round($mugPrice - $mugFee, 2),
                    'escrow_status'   => 'released_to_seller',
                    'escrow_released_at' => now()->subDay(),
                ]
            );
        }

        if ($candle) {
            // Order 2: Pending — awaiting payment, funds not yet in escrow.
            $candlePrice = (float) $candle->price;
            $candleSubtotal = $candlePrice * 2;
            $candleFee = round($candleSubtotal * 0.08, 2);

            $order2 = Order::updateOrCreate(
                ['order_number' => 'CM-20261107-0002'],
                [
                    'user_id'          => $customer->id,
                    'subtotal'         => $candleSubtotal,
                    'shipping_fee'     => 80.00,
                    'total_amount'     => $candleSubtotal + 80.00,
                    'status'           => 'pending',
                    'payment_method'   => 'Cash on Delivery',
                    'payment_status'   => 'unpaid',
                    'escrow_status'    => 'pending',
                    'platform_fee'     => $candleFee,
                    'seller_payout'    => round($candleSubtotal - $candleFee, 2),
                    'shipping_address' => 'Unit 402 Sunshine Towers, Pasig City, Metro Manila',
                    'contact_number'   => '+63 917 123 4567',
                    'notes'            => 'Fragile package.',
                ]
            );

            $order2->items()->updateOrCreate(
                ['order_id' => $order2->id, 'product_id' => $candle->id],
                [
                    'seller_id'       => $candle->seller_id,
                    'product_name'    => $candle->name,
                    'unit_price'      => $candlePrice,
                    'quantity'        => 2,
                    'subtotal'        => $candleSubtotal,
                    'status'          => 'pending',
                    'commission_rate' => 8.00,
                    'platform_fee'    => $candleFee,
                    'seller_payout'   => round($candleSubtotal - $candleFee, 2),
                    'escrow_status'   => 'pending',
                ]
            );
        }
    }
}
