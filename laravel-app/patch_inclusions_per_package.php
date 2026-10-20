<?php
/**
 * patch_inclusions_per_package.php
 *
 * Run from your laravel-app root:
 *   php patch_inclusions_per_package.php
 *
 * Replaces the flat "per service" $inclusions array in
 * app/Http/Controllers/User/BookingController.php with a nested
 * "per package" array, so "What's included" shows the right list
 * for each specific package (walk-in vs exclusive vs group bundle, etc.)
 * instead of one generic list per service.
 */

$path = __DIR__ . '/app/Http/Controllers/User/BookingController.php';

if (!file_exists($path)) {
    fwrite(STDERR, "ERROR: file not found at $path\n");
    fwrite(STDERR, "Run this script from your laravel-app root folder.\n");
    exit(1);
}

$content = file_get_contents($path);

$old = <<<'OLD'
    protected array $inclusions = [
        'dino_adventure' => [
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
        'rollerfever' => [
            '2 hours skating',
            '3 hours use of party area',
            '1 set meal per pax',
            'Basic balloon set-up',
            'Basic sound system',
            'Tables & chairs',
            'Digital themed invitation',
        ],
        'field_of_rides' => [
            'One ride/round per ticket purchased',
            'Minimum height requirement applies per ride (posted at each attraction)',
            'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
            'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
            'Management reserves the right to refuse service to guests who do not follow safety guidelines',
        ],
    ];
OLD;

$new = <<<'NEW'
    protected array $inclusions = [
        'dino_adventure' => [
            'non_exclusive' => [
                'Shared use of the softplay area (not exclusive)',
                '1 hr Dino mascot appearance & photo ops',
                'Party program with host',
                'Basic balloon set-up',
                'Basic sound system',
                'Tables & chairs (max no. of package chosen)',
                'Nametag & party games',
                'Digital themed invitation',
            ],
            'exclusive_weekdays' => [
                '3 hrs exclusive use of the whole play area',
                '3 hrs exclusive use of party area',
                '1 set meal each (max no. of package chosen)',
                'Dino mascot dance & photo ops',
                'Party program with host',
                'Basic balloon set-up',
                'Basic sound system',
                'Tables & chairs (max no. of package chosen)',
                'Nametag & party games',
                'Digital themed invitation',
                'Available Monday–Friday only',
            ],
            'exclusive_weekends' => [
                '3 hrs exclusive use of the whole play area',
                '3 hrs exclusive use of party area',
                '1 set meal each (max no. of package chosen)',
                'Dino mascot dance & photo ops',
                'Party program with host',
                'Basic balloon set-up',
                'Basic sound system',
                'Tables & chairs (max no. of package chosen)',
                'Nametag & party games',
                'Digital themed invitation',
                'Available Saturdays, Sundays & holidays',
            ],
            'walkin_1hour' => [
                '1 hour of softplay access per guest',
                'Access to all play structures',
                'Walk-in promo rate — no party area, host, or meal included',
            ],
            'walkin_2hour' => [
                '2 hours of softplay access per guest',
                'Access to all play structures',
                'Walk-in promo rate — no party area, host, or meal included',
            ],
            'walkin_allday' => [
                'Unlimited-time softplay access per guest for the day',
                'Access to all play structures',
                'Walk-in promo rate — no party area, host, or meal included',
            ],
        ],
        'rollerfever' => [
            'weekday' => [
                '2 hours skating for all guests',
                '3 hours exclusive use of party area',
                '1 set meal per guest',
                'Basic balloon set-up',
                'Basic sound system',
                'Tables & chairs',
                'Digital themed invitation',
                'Available Monday–Friday only',
            ],
            'weekend' => [
                '2 hours skating for all guests',
                '3 hours exclusive use of party area',
                '1 set meal per guest',
                'Basic balloon set-up',
                'Basic sound system',
                'Tables & chairs',
                'Digital themed invitation',
                'Available Saturdays & Sundays',
            ],
            'walkin_1hour' => [
                '1 hour of skating per guest',
                'Walk-in promo rate',
                'Socks and skate/gear rental available as add-ons',
            ],
            'walkin_2hour' => [
                '2 hours of skating per guest',
                'Walk-in promo rate',
                'Socks and skate/gear rental available as add-ons',
            ],
            'walkin_allday' => [
                'Unlimited-time skating per guest for the day',
                'Walk-in promo rate',
                'Socks and skate/gear rental available as add-ons',
            ],
            'group_bundle_1hour' => [
                '1 hour of skating for 5 guests (buy 4, get 1 free)',
                'Group promo rate — best for barkada or family groups',
                'Socks and skate/gear rental available as add-ons',
            ],
            'group_bundle_2hour' => [
                '2 hours of skating for 5 guests (buy 4, get 1 free)',
                'Group promo rate — best for barkada or family groups',
                'Socks and skate/gear rental available as add-ons',
            ],
        ],
        'field_of_rides' => [
            'try_every_ride' => [
                'One ride each on every attraction on the price board',
                'Covers Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane, Vikings, and Go-Kart',
                'Best value versus paying per ride',
                'Minimum height requirement applies per ride (posted at each attraction)',
            ],
            'rides_60' => [
                'One ride/round per ticket purchased',
                'Covers Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, and Crazy Plane',
                'Minimum height requirement applies per ride (posted at each attraction)',
            ],
            'rides_120' => [
                'One ride/round per ticket purchased',
                'Covers Vikings and Go-Kart',
                'Minimum height requirement applies per ride (posted at each attraction)',
            ],
            'rides_150' => [
                'One ride/round per ticket purchased',
                'Covers Inflatable Playground (30 mins), Mini Trampoline (30 mins), Rev & Roll (per car), Happy Cars (per ride), and Jurassic Adventure (per ride)',
                'Minimum height requirement applies per ride (posted at each attraction)',
            ],
        ],
    ];
NEW;

if (strpos($content, $old) === false) {
    fwrite(STDERR, "ERROR: expected \$inclusions block not found in BookingController.php — no changes made.\n");
    fwrite(STDERR, "The file may already differ from what this script expects.\n");
    exit(1);
}
$content = str_replace($old, $new, $content);

file_put_contents($path, $content);

echo "Done. BookingController.php patched — \$inclusions is now nested per package.\n";
