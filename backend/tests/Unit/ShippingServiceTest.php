<?php

namespace Tests\Unit;

use App\Services\ShippingService;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    public function test_free_shipping_for_orders_1500_and_above(): void
    {
        $result = ShippingService::calculateShippingFee(
            'Baguio City, Philippines',
            'Davao City, Philippines',
            1500.00
        );

        $this->assertEquals(0, $result['fee']);
        $this->assertEquals('free', $result['zone']);
    }

    public function test_local_shipping_rate(): void
    {
        $result = ShippingService::calculateShippingFee(
            'Baguio City, Benguet',
            'Session Road, Baguio City',
            500.00
        );

        $this->assertEquals(80, $result['fee']);
        $this->assertEquals('local', $result['zone']);
    }

    public function test_same_island_shipping_rate(): void
    {
        $result = ShippingService::calculateShippingFee(
            'Baguio City, Philippines',
            'Makati, Metro Manila',
            500.00
        );

        $this->assertEquals(100, $result['fee']);
        $this->assertEquals('same_island', $result['zone']);
    }

    public function test_cross_island_luzon_to_visayas(): void
    {
        $result = ShippingService::calculateShippingFee(
            'Quezon City, Metro Manila',
            'Cebu City, Visayas',
            500.00
        );

        $this->assertEquals(180, $result['fee']);
        $this->assertEquals('luzon_visayas', $result['zone']);
    }

    public function test_cross_island_luzon_to_mindanao(): void
    {
        $result = ShippingService::calculateShippingFee(
            'Manila, Luzon',
            'Davao City, Mindanao',
            500.00
        );

        $this->assertEquals(200, $result['fee']);
        $this->assertEquals('luzon_mindanao', $result['zone']);
    }
}
