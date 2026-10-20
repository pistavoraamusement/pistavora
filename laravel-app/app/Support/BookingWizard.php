<?php

namespace App\Support;

/**
 * Catalog + pricing for the step-by-step booking wizard (user/booking).
 *
 * Prices are NOT duplicated here: every card's per-person price is read
 * from the effective packages/add-ons the controller already passes in
 * (BookingCatalog + CMS overrides), so editing prices in the CMS still
 * updates the wizard. Only the *presentation* (ride names, icons) and the
 * *deal rules* live here.
 *
 * price() is the single source of truth for totals. The browser runs the
 * same algorithm for the live total, but the server always recomputes
 * from its own catalog on submit — never trust prices from the client.
 */
class BookingWizard
{
    /** Individual PiyestaRides attractions, grouped by the price package they belong to. */
    protected const FOR_RIDES = [
        'rides_60' => [
            ['Tiger Train', '🚂', 'tiger-train.jpg'], ['Mini Carousel', '🎠', 'mini-carousel.jpg'], ['Star Speed', '⭐', 'star-speed.jpg'],
            ['Little Chicken', '🐔', 'little-chicken.jpg'], ['Boat Pool', '🚤', 'boat-pool.jpg'], ['Carousel', '🎠', 'carousel.jpg'],
            ['Flying Chair', '🪑', 'flying-chair.jpg'], ['Mini Ferris Wheel', '🎡', 'mini-ferris-wheel.jpg'], ['Samba Balloon', '🎈', 'samba-balloon.jpg'],
            ['Crazy Plane', '✈️', 'crazy-plane.jpg'],
        ],
        'rides_120' => [
            ['Vikings', '⚓', 'ride-vikings.jpg'], ['Go-Kart', '🏎️', 'go-kart.jpg'],
        ],
        'rides_150' => [
            ['Inflatable Playground (30 mins)', '🏰', 'inflatable-playground-30-mins.jpg'], ['Mini Trampoline (30 mins)', '🤸', 'mini-trampoline-30-mins.jpg'],
            ['Rev & Roll (per car)', '🚗', 'rev-and-roll-per-car.jpg'], ['Happy Cars (per ride)', '🚙', 'happy-cars-per-ride.jpg'],
            ['Jurassic Adventure (per ride)', '🦖', 'jurassic-adventure-per-ride.jpg'],
        ],
    ];

    /**
     * Deal rules. PLACEHOLDER NUMBERS — confirm the real promos with the
     * business and edit here only; the view and the server both read this.
     *
     *  - four_plus_one: buy 4 get 1 free on these passes (same promo as the
     *    old "Group Bundle" packages). Applies per 5 passes of that item.
     *  - group_tiers:   % off the whole ride/pass subtotal by party size
     *    (not applied to lines already on a 4+1 deal, never to extras).
     */
    public static function deals(): array
    {
        return [
            'four_plus_one' => [
                'min_qty'  => 5,
                'packages' => [
                    'rollerfever' => ['walkin_1hour', 'walkin_2hour'],
                ],
            ],
            'group_tiers' => [
                // promo turned off: ['min_guests' => 10, 'pct' => 15],
                // promo turned off: ['min_guests' => 5,  'pct' => 10],
            ],
        ];
    }

    public static function dealApplies(string $service, string $package): bool
    {
        return in_array($package, self::deals()['four_plus_one']['packages'][$service] ?? [], true);
    }

    /**
     * Tabs for the ride-picker step. Each tab: label, emoji, items[], extras[].
     */
    public static function catalog(array $packages, array $addons, array $services): array
    {
        $tabs = [];

        // ---- DinoLakbay: per-guest play passes -------------------
        $tabs['dino_adventure'] = [
            'label'   => $services['dino_adventure']['name'] ?? 'DinoLakbay',
            'tagline' => 'Softplay & dino fun',
            'emoji'   => '🦖',
            'items'   => self::passItems('dino_adventure', $packages, '🦖'),
            'extras'  => self::extras('dino_adventure', $addons),
        ];

        // ---- PiyestaRides: one card per ride -----------------------
        $forItems = [];
        foreach (self::FOR_RIDES as $code => $rides) {
            $unit = self::unitPrice($packages['field_of_rides'][$code] ?? null);
            if ($unit === null) {
                continue;
            }
            foreach ($rides as $ride) {
                $forItems[] = [
                    'key'        => 'field_of_rides:' . $code . ':' . self::slug($ride[0]),
                    'service'    => 'field_of_rides',
                    'package'    => $code,
                    'name'       => $ride[0],
                    'desc'       => 'Per person, per ride',
                    'emoji'      => $ride[1],
                    'image'      => $ride[2] ?? null,
                    'unit_price' => $unit,
                    'badge'      => null,
                    'deal'       => null,
                ];
            }
        }
        $all = $packages['field_of_rides']['try_every_ride'] ?? null;
        if ($all && self::unitPrice($all) !== null) {
            array_unshift($forItems, [
                'key'        => 'field_of_rides:try_every_ride',
                'service'    => 'field_of_rides',
                'package'    => 'try_every_ride',
                'name'       => $all['name'],
                'desc'       => 'One ride on every attraction on the price board',
                'emoji'      => '🎢',
                'image'      => $all['image'] ?? null,
                'unit_price' => self::unitPrice($all),
                'badge'      => 'Best value',
                'deal'       => null,
            ]);
        }
        $tabs['field_of_rides'] = [
            'label'   => $services['field_of_rides']['name'] ?? 'PiyestaRides',
            'tagline' => 'Rides & games',
            'emoji'   => '🎡',
            'items'   => $forItems,
            'extras'  => [],
        ];

        // ---- Skataya: per-guest skate passes ---------------------
        $tabs['rollerfever'] = [
            'label'   => $services['rollerfever']['name'] ?? 'Skataya',
            'tagline' => 'Skate rink',
            'emoji'   => '🛼',
            'items'   => self::passItems('rollerfever', $packages, '🛼'),
            'extras'  => self::extras('rollerfever', $addons),
        ];

        // ---- Party packages: flat price by guest-count tier ------------
        foreach (['dino_adventure', 'rollerfever'] as $svc) {
            $tabs[$svc]['items'] = array_merge($tabs[$svc]['items'], self::partyItems($svc, $packages));
        }

        // ---- "What's included" list for every card ----------------------
        $inc = BookingCatalog::inclusions();
        foreach ($tabs as $svc => $tab) {
            foreach ($tab['items'] as $i => $item) {
                $tabs[$svc]['items'][$i]['inclusions'] = array_values($inc[$item['service']][$item['package']] ?? []);
                if (self::dealApplies($item['service'], $item['package'])) {
                    $tabs[$svc]['items'][$i]['inclusions'][] = 'Buy 4 guest passes, get 1 free (5 pax total)';
                }
            }
        }

        return $tabs;
    }

    /** key => item/extra lookup across all tabs (server-trusted prices). */
    public static function lookup(array $catalog): array
    {
        $items = [];
        $extras = [];
        foreach ($catalog as $tab) {
            foreach ($tab['items'] as $i) {
                $items[$i['key']] = $i;
            }
            foreach ($tab['extras'] as $e) {
                $extras[$e['key']] = $e;
            }
        }

        return ['items' => $items, 'extras' => $extras];
    }

    /**
     * @param array $lines  each: ['key','service','package','name','unit_price','qty']
     * @param array $extras each: ['key','name','unit_price','qty']
     */
    public static function price(array $lines, array $extras, int $guests): array
    {
        $deals = self::deals();
        $subtotal = 0.0;
        $bundleDiscount = 0.0;
        $groupBase = 0.0;
        $outLines = [];

        foreach ($lines as $l) {
            $gross = $l['unit_price'] * $l['qty'];
            $free = 0;
            if (self::dealApplies($l['service'], $l['package']) && $l['qty'] >= $deals['four_plus_one']['min_qty']) {
                $free = intdiv($l['qty'], $deals['four_plus_one']['min_qty']);
            }
            $disc = $free * $l['unit_price'];

            $subtotal += $gross;
            $bundleDiscount += $disc;
            if ($free === 0 && empty($l['flat'])) {
                $groupBase += $gross;
            }

            $outLines[] = $l + [
                'gross'           => round($gross, 2),
                'free_units'      => $free,
                'bundle_discount' => round($disc, 2),
                'line_total'      => round($gross - $disc, 2),
            ];
        }

        $groupPct = 0;
        foreach ($deals['group_tiers'] as $tier) {
            if ($guests >= $tier['min_guests']) {
                $groupPct = max($groupPct, $tier['pct']);
            }
        }
        $groupDiscount = round($groupBase * $groupPct / 100); // whole pesos (bookings.price is an integer column)

        $extrasTotal = 0.0;
        $outExtras = [];
        foreach ($extras as $e) {
            $line = $e['unit_price'] * $e['qty'];
            $extrasTotal += $line;
            $outExtras[] = $e + ['line_total' => round($line, 2)];
        }

        $discountTotal = round($bundleDiscount + $groupDiscount, 2);

        return [
            'lines'           => $outLines,
            'extras'          => $outExtras,
            'subtotal'        => round($subtotal, 2),
            'bundle_discount' => round($bundleDiscount, 2),
            'group_pct'       => $groupPct,
            'group_discount'  => $groupDiscount,
            'extras_total'    => round($extrasTotal, 2),
            'discount_total'  => $discountTotal,
            'total'           => round($subtotal - $discountTotal + $extrasTotal, 2),
        ];
    }

    // ---------------------------------------------------------------

    /**
     * Party packages (category "packages"): one flat price per guest-count tier,
     * e.g. 10 guests = P15,000. Weekday/weekend packages carry a 'days' rule.
     */
    protected static function partyItems(string $service, array $packages): array
    {
        $items = [];
        foreach ($packages[$service] ?? [] as $code => $pkg) {
            if (($pkg['category'] ?? null) !== 'packages' || empty($pkg['tiers'])) {
                continue;
            }
            $tiers = $pkg['tiers'];
            ksort($tiers);
            $list = [];
            foreach ($tiers as $pax => $price) {
                $list[] = ['pax' => (int) $pax, 'price' => (float) $price];
            }
            $days = null;
            if (strpos($code, 'weekday') !== false) {
                $days = 'weekday';
            } elseif (strpos($code, 'weekend') !== false) {
                $days = 'weekend';
            }
            $items[] = [
                'key'        => $service . ':' . $code,
                'service'    => $service,
                'package'    => $code,
                'name'       => $pkg['name'],
                'desc'       => $pkg['desc'] ?? '',
                'emoji'      => '🎉',
                'image'      => $pkg['image'] ?? null,
                'unit_price' => $list[0]['price'],
                'badge'      => $pkg['badge'] ?? null,
                'deal'       => null,
                'flat'       => true,
                'tiers'      => $list,
                'days'       => $days,
            ];
        }

        return $items;
    }

    /** Smallest tier that fits $guests, or null when the party is bigger than the top tier. */
    public static function tierFor(array $item, int $guests): ?array
    {
        foreach ($item['tiers'] ?? [] as $t) {
            if ($guests <= $t['pax']) {
                return $t;
            }
        }

        return null;
    }

    protected static function passItems(string $service, array $packages, string $emoji): array
    {
        $items = [];
        foreach ($packages[$service] ?? [] as $code => $pkg) {
            if (($pkg['category'] ?? null) !== 'solo') {
                continue;
            }
            $unit = self::unitPrice($pkg);
            if ($unit === null) {
                continue;
            }
            $deal = self::dealApplies($service, $code) ? 'Buy 4, get 1 free' : null;
            $items[] = [
                'key'        => $service . ':' . $code,
                'service'    => $service,
                'package'    => $code,
                'name'       => $pkg['name'],
                'desc'       => $pkg['desc'] ?? '',
                'emoji'      => $emoji,
                'image'      => $pkg['image'] ?? null,
                'unit_price' => $unit,
                'badge'      => null,
                'deal'       => $deal,
            ];
        }

        return $items;
    }

    protected static function extras(string $service, array $addons): array
    {
        $out = [];
        foreach ($addons[$service] ?? [] as $code => $a) {
            $out[] = [
                'key'        => $service . ':' . $code,
                'service'    => $service,
                'addon'      => $code,
                'name'       => $a['name'],
                'unit_price' => (float) $a['price'],
            ];
        }

        return $out;
    }

    /** Price for ONE guest of a package = its 1-pax tier. */
    protected static function unitPrice(?array $pkg): ?float
    {
        if (!$pkg || !isset($pkg['tiers'][1])) {
            return null;
        }

        return (float) $pkg['tiers'][1];
    }

    protected static function slug(string $s): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');
    }
}
