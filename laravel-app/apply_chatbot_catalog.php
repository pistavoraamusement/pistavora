<?php
/**
 * apply_chatbot_catalog.php
 * Run from your laravel-app root: php apply_chatbot_catalog.php
 *
 * Problem: ChatController::systemPrompt() had every package, price and pax
 * hardcoded as text, so the bot's answers drifted from the real Booking
 * page (BookingCatalog + prices edited in the CMS via BookingPricingOverrides).
 *
 * Fix: build the "experiences / packages / prices / add-ons / inclusions"
 * section of the prompt at request time from those same sources, so the
 * chatbot always quotes exactly what the Booking page shows.
 */

$path = __DIR__ . '/app/Http/Controllers/ChatController.php';

if (!file_exists($path)) {
    echo "[SKIP] ChatController.php not found at $path\n";
    exit(1);
}

$content = file_get_contents($path);

if (strpos($content, 'function catalogText()') !== false) {
    echo "[OK]   ChatController — na-patch na dati, walang ginalaw.\n";
    exit(0);
}

// 1. imports
$oldUse = "use App\\Models\\ChatLog;\n";
$newUse = "use App\\Models\\ChatLog;\nuse App\\Support\\BookingCatalog;\nuse App\\Support\\BookingPricingOverrides;\n";
if (strpos($content, $oldUse) === false) {
    echo "[WARN] imports — hindi na-match ang 'use App\\Models\\ChatLog;'. I-check manually.\n";
    exit(1);
}
$content = str_replace($oldUse, $newUse, $content);

// 2. new method + $catalog variable at the top of systemPrompt()
$oldHead = "    private function systemPrompt(): string\n    {\n        return <<<PROMPT\n";
if (strpos($content, $oldHead) === false) {
    echo "[WARN] systemPrompt() — hindi na-match ang start ng function. I-check manually.\n";
    exit(1);
}

$method = <<<'EOT'
    /**
     * Builds the packages / prices / add-ons / inclusions part of the prompt
     * from the SAME sources the Booking page uses (BookingCatalog + the
     * prices admins edit in the CMS), so the bot can never quote a stale
     * or made-up price.
     */
    private function catalogText(): string
    {
        $services   = BookingCatalog::services();
        $packages   = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());
        $addons     = BookingPricingOverrides::applyToAddons(BookingCatalog::baseAddons());
        $inclusions = BookingCatalog::inclusions();

        $peso = fn ($n) => number_format((float) $n) . ' PHP';
        $out  = [];

        foreach ($services as $serviceKey => $service) {
            $out[] = strtoupper($service['name']) . ' (' . $service['tagline'] . '):';

            foreach ($packages[$serviceKey] ?? [] as $code => $pkg) {
                $tiers = $pkg['tiers'] ?? [];
                if (!$tiers) {
                    continue;
                }

                $firstPax   = array_key_first($tiers);
                $lastPax    = array_key_last($tiers);
                $firstPrice = $tiers[$firstPax];

                if (($pkg['category'] ?? '') === 'solo') {
                    // Per-guest passes: 1 guest = X, 2 guests = 2X, ...
                    $priceText = $peso($firstPrice) . ' per guest (bookable for 1 to ' . $lastPax . ' guests)';
                } elseif (count($tiers) === 1) {
                    $priceText = $peso($firstPrice) . ' total for ' . $firstPax . ' guests';
                } else {
                    $parts = [];
                    foreach ($tiers as $pax => $price) {
                        $parts[] = $pax . ' pax = ' . $peso($price);
                    }
                    $priceText = 'starts at ' . $peso($firstPrice) . ' for ' . $firstPax
                        . ' pax, up to ' . $lastPax . ' pax. Full prices: ' . implode('; ', $parts);
                }

                $line = '- ' . $pkg['name'] . ' — ' . $pkg['desc'] . '. Price: ' . $priceText . '.';

                $inc = $inclusions[$serviceKey][$code] ?? [];
                if ($inc) {
                    $line .= ' Includes: ' . implode('; ', $inc) . '.';
                }

                $out[] = $line;
            }

            if (!empty($addons[$serviceKey])) {
                $parts = [];
                foreach ($addons[$serviceKey] as $addon) {
                    $parts[] = $addon['name'] . ' ' . $peso($addon['price']);
                }
                $out[] = '- Optional add-ons: ' . implode('; ', $parts) . '.';
            }

            $out[] = '';
        }

        return rtrim(implode("\n", $out));
    }

EOT;

$newHead = $method
    . "    private function systemPrompt(): string\n    {\n        \$catalog = \$this->catalogText();\n\n        return <<<PROMPT\n";

$content = str_replace($oldHead, $newHead, $content);

// 3. swap the hardcoded packages section for the live one
$pattern = '/THE THREE EXPERIENCES AND THEIR PACKAGES:.*?(?=WHAT YOU KNOW ABOUT AVAILABILITY:)/s';
$replacement = "THE THREE EXPERIENCES, PACKAGES, PRICES, ADD-ONS AND INCLUSIONS\n"
    . "(live from the booking system — always quote exactly these figures and\n"
    . "never guess, round or invent a price, pax count or inclusion; if something\n"
    . "is not listed here, say you don't have that detail and point the guest to\n"
    . "the Booking page or the REKS staff):\n\n"
    . "{\$catalog}\n\n";

$count = 0;
$content = preg_replace_callback($pattern, fn () => $replacement, $content, 1, $count);

if ($count !== 1) {
    echo "[WARN] prompt section — hindi na-match ang 'THE THREE EXPERIENCES...' block. I-check manually.\n";
    exit(1);
}

file_put_contents($path, $content);
echo "[DONE] ChatController — live na ang packages/prices/add-ons/inclusions ng chatbot.\n";
