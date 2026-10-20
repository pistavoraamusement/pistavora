<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\BookingCatalog;
use App\Support\BookingPricingOverrides;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingCmsController extends Controller
{
    /**
     * GET /admin/cms/booking
     */
    public function edit()
    {
        $services  = BookingCatalog::services();
        $packages  = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());
        $addons    = BookingPricingOverrides::applyToAddons(BookingCatalog::baseAddons());
        $overrides = BookingPricingOverrides::all();

        return view('cms.booking', compact('services', 'packages', 'addons', 'overrides'));
    }

    /**
     * PUT /admin/cms/booking
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tiers'       => ['nullable', 'array'],
            'tiers.*.*.*' => ['nullable', 'numeric', 'min:0'],
            'addons'      => ['nullable', 'array'],
            'addons.*.*'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $basePackages = BookingCatalog::basePackages();
        $baseAddons   = BookingCatalog::baseAddons();
        $overrides    = BookingPricingOverrides::all();

        foreach ($validated['tiers'] ?? [] as $service => $servicePackages) {
            foreach ($servicePackages as $package => $tiers) {
                foreach ($tiers as $pax => $price) {
                    $defaultPrice = $basePackages[$service][$package]['tiers'][$pax] ?? null;

                    if ($defaultPrice === null) {
                        continue;
                    }

                    if ((float) $price === (float) $defaultPrice) {
                        unset($overrides['packages'][$service][$package][$pax]);
                    } else {
                        $overrides['packages'][$service][$package][$pax] = (float) $price;
                    }
                }
            }
        }

        foreach ($validated['addons'] ?? [] as $service => $serviceAddons) {
            foreach ($serviceAddons as $addon => $price) {
                $defaultPrice = $baseAddons[$service][$addon]['price'] ?? null;

                if ($defaultPrice === null) {
                    continue;
                }

                if ((float) $price === (float) $defaultPrice) {
                    unset($overrides['addons'][$service][$addon]);
                } else {
                    $overrides['addons'][$service][$addon] = (float) $price;
                }
            }
        }

        BookingPricingOverrides::save($overrides);

        return back()->with('success', 'Booking prices updated.');
    }

    /**
     * DELETE /cms/booking/package/{service}/{package}/reset
     */
    public function resetPackage(string $service, string $package): RedirectResponse
    {
        BookingPricingOverrides::resetPackage($service, $package);

        return back()->with('success', 'Reverted to default pricing for this package.');
    }

    /**
     * DELETE /cms/booking/addon/{service}/{addon}/reset
     */
    public function resetAddon(string $service, string $addon): RedirectResponse
    {
        BookingPricingOverrides::resetAddon($service, $addon);

        return back()->with('success', 'Reverted to default price for this add-on.');
    }
}