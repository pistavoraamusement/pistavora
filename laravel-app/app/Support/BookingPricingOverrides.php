<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Admin-edited price overrides, layered on top of BookingCatalog's
 * hardcoded defaults.
 *
 * Stored as a single JSON file on the 'local' disk (storage/app/) rather
 * than a database table, so this ships without a migration. Shape:
 *
 *   {
 *     "packages": { "<service>": { "<package>": { "<pax>": <price> } } },
 *     "addons":   { "<service>": { "<addon>": <price> } }
 *   }
 *
 * Only tiers/add-ons an admin has actually edited appear here — anything
 * not listed just falls back to BookingCatalog's default price. Move
 * this to a real `booking_price_overrides` table later if you need
 * per-branch pricing, an edit history/audit trail, or multiple admins
 * editing concurrently without last-write-wins.
 */
class BookingPricingOverrides
{
    protected const FILE = 'booking-pricing-overrides.json';

    public static function all(): array
    {
        if (!Storage::disk('local')->exists(self::FILE)) {
            return ['packages' => [], 'addons' => []];
        }

        $decoded = json_decode(Storage::disk('local')->get(self::FILE), true);

        return is_array($decoded)
            ? array_merge(['packages' => [], 'addons' => []], $decoded)
            : ['packages' => [], 'addons' => []];
    }

    public static function save(array $overrides): void
    {
        Storage::disk('local')->put(
            self::FILE,
            json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Returns $packages (BookingCatalog::basePackages() shape) with any
     * saved tier-price overrides applied on top.
     */
    public static function applyToPackages(array $packages): array
    {
        $overrides = self::all()['packages'] ?? [];

        foreach ($packages as $service => &$servicePackages) {
            foreach ($servicePackages as $code => &$package) {
                foreach ($package['tiers'] as $pax => &$price) {
                    if (isset($overrides[$service][$code][$pax])) {
                        $price = (float) $overrides[$service][$code][$pax];
                    }
                }
                unset($price);
            }
            unset($package);
        }
        unset($servicePackages);

        return $packages;
    }

    /**
     * Returns $addons (BookingCatalog::baseAddons() shape) with any
     * saved price overrides applied on top.
     */
    public static function applyToAddons(array $addons): array
    {
        $overrides = self::all()['addons'] ?? [];

        foreach ($addons as $service => &$serviceAddons) {
            foreach ($serviceAddons as $code => &$addon) {
                if (isset($overrides[$service][$code])) {
                    $addon['price'] = (float) $overrides[$service][$code];
                }
            }
            unset($addon);
        }
        unset($serviceAddons);

        return $addons;
    }

    /**
     * Remove every saved override for one package (all its tiers revert
     * to BookingCatalog's default prices).
     */
    public static function resetPackage(string $service, string $package): void
    {
        $overrides = self::all();
        unset($overrides['packages'][$service][$package]);
        self::save($overrides);
    }

    /**
     * Remove the saved override for one add-on (reverts to default price).
     */
    public static function resetAddon(string $service, string $addon): void
    {
        $overrides = self::all();
        unset($overrides['addons'][$service][$addon]);
        self::save($overrides);
    }
}