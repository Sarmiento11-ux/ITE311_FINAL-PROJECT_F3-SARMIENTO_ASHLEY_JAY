<?php

namespace App\Services;

class ShippingService
{
    /**
     * Philippine island group lookup.
     * Maps common province/city keywords → island group.
     */
    private static array $luzon = [
        'metro manila','manila','quezon city','makati','pasig','taguig','mandaluyong',
        'marikina','caloocan','malabon','navotas','valenzuela','las pinas','muntinlupa',
        'paranaque','pasay','pateros','san juan',
        'cavite','batangas','laguna','rizal','bulacan','pampanga','tarlac','zambales',
        'bataan','nueva ecija','aurora','pangasinan','la union','ilocos norte','ilocos sur',
        'abra','apayao','benguet','ifugao','kalinga','mountain province','baguio',
        'cagayan','isabela','nueva vizcaya','quirino','batanes',
        'albay','camarines','catanduanes','masbate','sorsogon','naga',
        'marinduque','mindoro','occidental mindoro','oriental mindoro','palawan',
        'romblon','quezon','lucena',
    ];

    private static array $visayas = [
        'cebu','lapu-lapu','mandaue','cebu city',
        'bohol','tagbilaran',
        'negros occidental','bacolod','negros oriental','dumaguete',
        'iloilo','iloilo city','capiz','roxas city','aklan','boracay','antique','guimaras',
        'leyte','tacloban','samar','eastern samar','northern samar',
        'biliran','siquijor',
    ];

    private static array $mindanao = [
        'davao','davao city','davao del norte','davao del sur','davao oriental','davao occidental',
        'compostela valley','cotabato','south cotabato','sarangani','general santos',
        'sultan kudarat','maguindanao','lanao del norte','lanao del sur','iligan',
        'misamis oriental','cagayan de oro','misamis occidental','bukidnon',
        'agusan del norte','agusan del sur','surigao del norte','surigao del sur',
        'zamboanga','zamboanga del norte','zamboanga del sur','zamboanga sibugay',
        'basilan','sulu','tawi-tawi','butuan',
        'north cotabato','kidapawan','tacurong','koronadal',
        // Oriental Mindoro is Luzon but Victoria is a municipality — keeping it simple
        'victoria','macatoc',
    ];

    /**
     * Determine the island group from a location string.
     * Returns: 'luzon' | 'visayas' | 'mindanao' | 'unknown'
     */
    public static function getIslandGroup(string $location): string
    {
        $loc = strtolower($location);

        foreach (self::$mindanao as $keyword) {
            if (str_contains($loc, $keyword)) return 'mindanao';
        }
        foreach (self::$visayas as $keyword) {
            if (str_contains($loc, $keyword)) return 'visayas';
        }
        foreach (self::$luzon as $keyword) {
            if (str_contains($loc, $keyword)) return 'luzon';
        }

        return 'unknown';
    }

    /**
     * Calculate shipping fee based on seller location and customer address.
     *
     * Zone rates:
     *   local        — same province/city keyword found in both → ₱80
     *   same_island  — same island group                        → ₱100
     *   cross_island — different island groups                  → ₱150–₱200
     *   free         — subtotal ≥ ₱1,500                        → ₱0
     *
     * @return array{ fee: float, zone: string, label: string }
     */
    public static function calculateShippingFee(
        string $sellerLocation,
        string $customerAddress,
        float  $subtotal
    ): array {
        // Free shipping threshold
        if ($subtotal >= 1500) {
            return ['fee' => 0, 'zone' => 'free', 'label' => 'Free Shipping (₱1,500+ order)'];
        }

        $sellerLoc  = strtolower(trim($sellerLocation));
        $customerLoc = strtolower(trim($customerAddress));

        // Local: check for a shared significant keyword (city/province name ≥ 5 chars)
        $sellerWords   = array_filter(explode(' ', preg_replace('/[,.]/', ' ', $sellerLoc)),  fn($w) => strlen($w) >= 5);
        $customerWords = array_filter(explode(' ', preg_replace('/[,.]/', ' ', $customerLoc)), fn($w) => strlen($w) >= 5);
        $commonWords   = array_intersect($sellerWords, $customerWords);

        if (!empty($commonWords)) {
            return ['fee' => 80, 'zone' => 'local', 'label' => 'Local Delivery'];
        }

        $sellerIsland   = self::getIslandGroup($sellerLocation);
        $customerIsland = self::getIslandGroup($customerAddress);

        // Same island group
        if ($sellerIsland === $customerIsland && $sellerIsland !== 'unknown') {
            return ['fee' => 100, 'zone' => 'same_island', 'label' => 'Same Island Shipping'];
        }

        // Cross-island rates
        $pair = implode('_', array_map(fn($g) => $g === 'unknown' ? 'unknown' : $g, [
            min($sellerIsland, $customerIsland),
            max($sellerIsland, $customerIsland),
        ]));

        return match ($pair) {
            'luzon_visayas'    => ['fee' => 180, 'zone' => 'luzon_visayas',    'label' => 'Luzon → Visayas'],
            'luzon_mindanao'   => ['fee' => 200, 'zone' => 'luzon_mindanao',   'label' => 'Luzon → Mindanao'],
            'mindanao_visayas',
            'visayas_mindanao' => ['fee' => 180, 'zone' => 'visayas_mindanao', 'label' => 'Visayas → Mindanao'],
            default            => ['fee' => 150, 'zone' => 'standard',         'label' => 'Standard Shipping'],
        };
    }
}
