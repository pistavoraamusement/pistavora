<?php

namespace App\Support;

/**
 * Static default data for services, packages, add-ons and inclusions.
 *
 * This used to live as hardcoded protected properties directly on
 * BookingController. It's pulled out here so BookingController (the
 * customer-facing booking flow) and BookingCmsController (the admin
 * pricing CMS) both read from one place instead of two copies drifting
 * apart.
 *
 * NOTE: this is still PHP-defined data, not a database table. Tier
 * *prices* can now be overridden at runtime via BookingPricingOverrides
 * (edited from the CMS), but adding/removing whole services, packages,
 * or tiers still means editing this file. Move to real
 * Service/Package/Tier models later if that also needs to be
 * admin-editable.
 */
class BookingCatalog
{
    public static function services(): array
    {
        return [
            'dino_adventure' => [
                'name'    => 'DinoLakbay',
                'tagline' => 'Kids Party & Softplay',
                'image'   => 'dino_1.jpg',
            ],
            'rollerfever' => [
                'name'    => 'Skataya',
                'tagline' => 'Skate Rink Parties',
                'image'   => 'roller-fever.jpg',
            ],
            'field_of_rides' => [
                'name'    => 'PiyestaRides',
                'tagline' => 'Amusement Rides & Games',
                'image'   => 'field-of-rides-icon.jpg',
            ],
        ];
    }

    /**
     * Base package/tier prices, BEFORE any CMS overrides are applied.
     * Callers that need the *effective* (admin-edited) prices should go
     * through BookingPricingOverrides::applyToPackages() instead of
     * calling this directly.
     */
    public static function basePackages(): array
    {
        return [
            'dino_adventure' => [
                'walkin_1hour' => [
                    'name'  => '1 Hour Play Pass',
                    'desc'  => '1 hour of softplay access per guest',
                    'image' => '1-hour-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 299, 2  => 598, 3  => 897, 4  => 1196, 5  => 1495,
                        6  => 1794, 7  => 2093, 8  => 2392, 9  => 2691, 10 => 2990,
                    ],
                ],
                'walkin_2hour' => [
                    'name'  => '2 Hour Play Pass',
                    'desc'  => '2 hours of softplay access per guest',
                    'image' => '2-hour-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 399, 2  => 798, 3  => 1197, 4  => 1596, 5  => 1995,
                        6  => 2394, 7  => 2793, 8  => 3192, 9  => 3591, 10 => 3990,
                    ],
                ],
                'walkin_allday' => [
                    'name'  => 'All Day Play Pass',
                    'desc'  => 'Unlimited-time softplay access per guest for the day',
                    'image' => 'all-day-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 599, 2  => 1198, 3  => 1797, 4  => 2396, 5  => 2995,
                        6  => 3594, 7  => 4193, 8  => 4792, 9  => 5391, 10 => 5990,
                    ],
                ],
                'non_exclusive' => [
                    'name'  => 'Non-Exclusive — Shared Play Area',
                    'desc'  => 'Shared softplay & party area · 1-hr Dino mascot appearance included',
                    'image' => 'shared-play-area.jpg',
                    'badge' => 'Most affordable',
                    'category' => 'packages',
                    'tiers' => [10 => 15000, 20 => 28000, 30 => 39000],
                ],
                'exclusive_weekdays' => [
                    'name'  => 'Exclusive — Private Party (Mon–Fri)',
                    'desc'  => 'Private use of the whole play area, weekdays only · 1-hr Dino mascot appearance',
                    'image' => 'private-party-weekday.jpg',
                    'badge' => 'Weekdays only',
                    'category' => 'packages',
                    'tiers' => [40 => 62000, 50 => 75000, 60 => 88000, 70 => 97500, 80 => 110000],
                ],
                'exclusive_weekends' => [
                    'name'  => 'Exclusive — Private Party (Sat, Sun & Holidays)',
                    'desc'  => 'Private use of the whole play area, weekends & holidays · 1-hr Dino mascot appearance',
                    'image' => 'private-party-weekend.jpg',
                    'badge' => 'Weekends & holidays',
                    'category' => 'packages',
                    'tiers' => [40 => 67000, 50 => 80000, 60 => 93000, 70 => 102500, 80 => 115000],
                ],
            ],

            'rollerfever' => [
                'walkin_1hour' => [
                    'name'  => '1 Hour Skate Pass',
                    'desc'  => '1 hour of skating per guest',
                    'image' => '1-hour-skate-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 249, 2  => 498, 3  => 747, 4  => 996, 5  => 1245,
                        6  => 1494, 7  => 1743, 8  => 1992, 9  => 2241, 10 => 2490,
                    ],
                ],
                'walkin_2hour' => [
                    'name'  => '2 Hour Skate Pass',
                    'desc'  => '2 hours of skating per guest',
                    'image' => '2-hour-skate-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 399, 2  => 798, 3  => 1197, 4  => 1596, 5  => 1995,
                        6  => 2394, 7  => 2793, 8  => 3192, 9  => 3591, 10 => 3990,
                    ],
                ],
                'walkin_allday' => [
                    'name'  => 'All Day Skate Pass',
                    'desc'  => 'Unlimited-time skating per guest for the day',
                    'image' => 'all-day-skate-pass.jpg',
                    'badge' => 'Per guest',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 599, 2  => 1198, 3  => 1797, 4  => 2396, 5  => 2995,
                        6  => 3594, 7  => 4193, 8  => 4792, 9  => 5391, 10 => 5990,
                    ],
                ],
                'group_bundle_1hour' => [
                    'name'  => 'Group Bundle — 1 Hour (4+1 Free)',
                    'desc'  => 'Buy 4 guest passes, get 1 free — good for barkada or family groups (1 hour skating)',
                    'image' => 'group-bundle.jpg',
                    'badge' => 'Group promo',
                    'category' => 'bundle',
                    'tiers' => [5 => 996],
                ],
                'group_bundle_2hour' => [
                    'name'  => 'Group Bundle — 2 Hours (4+1 Free)',
                    'desc'  => 'Buy 4 guest passes, get 1 free — good for barkada or family groups (2 hours skating)',
                    'image' => 'group-bundle.jpg',
                    'badge' => 'Group promo',
                    'category' => 'bundle',
                    'tiers' => [5 => 1596],
                ],
                'weekday' => [
                    'name'  => 'Weekday Rates',
                    'desc'  => 'Skate rink party packages by number of guests (Mon–Fri)',
                    'image' => 'weekday-rates.jpg',
                    'badge' => 'Weekdays',
                    'category' => 'packages',
                    'tiers' => [
                        10 => 11994, 15 => 16200, 20 => 20400, 25 => 24390,
                        30 => 28800, 35 => 32550, 40 => 36000, 45 => 39150,
                    ],
                ],
                'weekend' => [
                    'name'  => 'Weekend Rates',
                    'desc'  => 'Skate rink party packages by number of guests (Sat–Sun)',
                    'image' => 'weekend-rates.jpg',
                    'badge' => 'Weekends',
                    'category' => 'packages',
                    'tiers' => [
                        10 => 15600, 15 => 22500, 20 => 28800, 25 => 34500,
                        30 => 39600, 35 => 42000, 40 => 45600, 45 => 48600,
                    ],
                ],
            ],

            'field_of_rides' => [
                'rides_60' => [
                    'name'  => 'Rides — ₱60 Per Head',
                    'desc'  => 'Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane',
                    'image' => 'field-of-rides.jpg',
                    'badge' => 'Per ride',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 60, 2  => 120, 3  => 180, 4  => 240, 5  => 300,
                        6  => 360, 7  => 420, 8  => 480, 9  => 540, 10 => 600,
                    ],
                ],
                'rides_120' => [
                    'name'  => 'Rides — ₱120 Per Head',
                    'desc'  => 'Vikings, Go-Kart',
                    'image' => 'field-of-rides.jpg',
                    'badge' => 'Per ride',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 120, 2  => 240, 3  => 360, 4  => 480, 5  => 600,
                        6  => 720, 7  => 840, 8  => 960, 9  => 1080, 10 => 1200,
                    ],
                ],
                'rides_150' => [
                    'name'  => 'Rides — ₱150 Per Head',
                    'desc'  => 'Inflatable Playground (30 mins), Mini Trampoline (30 mins), Rev & Roll (per car), Happy Cars (per ride), Jurassic Adventure (per ride)',
                    'image' => 'field-of-rides.jpg',
                    'badge' => 'Per ride',
                    'category' => 'solo',
                    'tiers' => [
                        1  => 150, 2  => 300, 3  => 450, 4  => 600, 5  => 750,
                        6  => 900, 7  => 1050, 8  => 1200, 9  => 1350, 10 => 1500,
                    ],
                ],
                'try_every_ride' => [
                    'name'  => 'Try Every Ride — All Access Pass',
                    'desc'  => 'One ride each on every attraction listed on the price board (Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane, Vikings, Go-Kart)',
                    'image' => 'try-every-ride-all-access-pass.jpg',
                    'badge' => 'Best value',
                    'category' => 'bundle',
                    'tiers' => [
                        1  => 600, 2  => 1200, 3  => 1800, 4  => 2400, 5  => 3000,
                        6  => 3600, 7  => 4200, 8  => 4800, 9  => 5400, 10 => 6000,
                    ],
                ],
            ],
        ];
    }

    /**
     * Flat add-on fees per service, BEFORE any CMS overrides. See
     * basePackages() note above — go through BookingPricingOverrides
     * for the effective/admin-edited prices.
     */
    public static function baseAddons(): array
    {
        return [
            'dino_adventure' => [
                'guardian'          => ['name' => 'Guardian Entry',        'price' => 50],
                'additional_30mins' => ['name' => 'Additional 30 Minutes', 'price' => 149],
                'additional_hour'   => ['name' => 'Additional Hour',       'price' => 199],
            ],
            'rollerfever' => [
                'socks'        => ['name' => 'Socks (required for entry)', 'price' => 50],
                'skate_rental' => ['name' => 'Skates and Gears Rental',    'price' => 50],
            ],
        ];
    }

    public static function inclusions(): array
    {
        return [
            'dino_adventure' => [
                'non_exclusive' => [
                    '3 hrs unlimited play at softplay (max no. of package chosen)',
                    '3 hrs exclusive use of party area',
                    '1 set meal each (max no. of package chosen)',
                    'Dino mascot dance & photo ops',
                    'Party program with host',
                    'Basic balloon set-up',
                    'Basic sound system',
                    'Tables & chairs (max no. of package chosen)',
                    'Nametag & party games',
                    'Digital themed invitation',
                ],
                'exclusive_weekdays' => [
                    '3 hrs unlimited play at softplay (max no. of package chosen)',
                    '3 hrs exclusive use of party area',
                    '1 set meal each (max no. of package chosen)',
                    'Dino mascot dance & photo ops',
                    'Party program with host',
                    'Basic balloon set-up',
                    'Basic sound system',
                    'Tables & chairs (max no. of package chosen)',
                    'Nametag & party games',
                    'Digital themed invitation',
                ],
                'exclusive_weekends' => [
                    '3 hrs unlimited play at softplay (max no. of package chosen)',
                    '3 hrs exclusive use of party area',
                    '1 set meal each (max no. of package chosen)',
                    'Dino mascot dance & photo ops',
                    'Party program with host',
                    'Basic balloon set-up',
                    'Basic sound system',
                    'Tables & chairs (max no. of package chosen)',
                    'Nametag & party games',
                    'Digital themed invitation',
                ],
                'walkin_1hour' => [
                    '1 hour of softplay access per guest',
                    'Guardian entry available for an additional fee',
                ],
                'walkin_2hour' => [
                    '2 hours of softplay access per guest',
                    'Guardian entry available for an additional fee',
                ],
                'walkin_allday' => [
                    'Unlimited-time softplay access per guest for the day',
                    'Guardian entry available for an additional fee',
                ],
            ],
            'rollerfever' => [
                'weekday' => [
                    '2 hours skating',
                    '3 hours use of party area',
                    '1 set meal per pax',
                    'Basic balloon set-up',
                    'Basic sound system',
                    'Tables & chairs',
                    'Digital themed invitation',
                ],
                'weekend' => [
                    '2 hours skating',
                    '3 hours use of party area',
                    '1 set meal per pax',
                    'Basic balloon set-up',
                    'Basic sound system',
                    'Tables & chairs',
                    'Digital themed invitation',
                ],
                'walkin_1hour' => [
                    '1 hour of skating access per guest',
                    'Socks and skate rental available for an additional fee',
                ],
                'walkin_2hour' => [
                    '2 hours of skating access per guest',
                    'Socks and skate rental available for an additional fee',
                ],
                'walkin_allday' => [
                    'Unlimited-time skating access per guest for the day',
                    'Socks and skate rental available for an additional fee',
                ],
                'group_bundle_1hour' => [
                    '1 hour of skating access per guest',
                    'Buy 4 guest passes, get 1 free (5 pax total)',
                    'Socks and skate rental available for an additional fee',
                ],
                'group_bundle_2hour' => [
                    '2 hours of skating access per guest',
                    'Buy 4 guest passes, get 1 free (5 pax total)',
                    'Socks and skate rental available for an additional fee',
                ],
            ],
            'field_of_rides' => [
                'try_every_ride' => [
                    'One ride each on every attraction listed on the price board',
                    'Minimum height requirement applies per ride (posted at each attraction)',
                    'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
                    'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
                    'Management reserves the right to refuse service to guests who do not follow safety guidelines',
                ],
                'rides_60' => [
                    'One ride/round per ticket purchased',
                    'Minimum height requirement applies per ride (posted at each attraction)',
                    'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
                    'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
                    'Management reserves the right to refuse service to guests who do not follow safety guidelines',
                ],
                'rides_120' => [
                    'One ride/round per ticket purchased',
                    'Minimum height requirement applies per ride (posted at each attraction)',
                    'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
                    'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
                    'Management reserves the right to refuse service to guests who do not follow safety guidelines',
                ],
                'rides_150' => [
                    'One ride/round per ticket purchased',
                    'Minimum height requirement applies per ride (posted at each attraction)',
                    'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
                    'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
                    'Management reserves the right to refuse service to guests who do not follow safety guidelines',
                ],
            ],
        ];
    }
}