<?php

namespace App\Services;

class PromoRecommendationService
{
    /**
     * Multiple promo templates per age bracket. Because more than one
     * template exists per bracket, two attractions that share the same
     * top age group will not necessarily get the same promo text —
     * we pick a variant based on the attraction itself (see selectVariant()).
     *
     * Use %s inside title/desc where the attraction name should be injected.
     */
    protected array $templateBank = [
        '13-17' => [
            [
                'title' => 'Teen Squad Pass',
                'desc'  => 'This bracket books in friend groups, not solo — try a 4-in-a-group discount for %s.',
            ],
            [
                'title' => 'After-School Rate',
                'desc'  => 'Offer a weekday afternoon discount on %s to catch this age group right after school.',
            ],
        ],
        '18-24' => [
            [
                'title' => 'Barkada Bundle',
                'desc'  => 'Group bookings of 5+ are common here — bundle %s with a friend-group rate.',
            ],
            [
                'title' => 'Student Weekday Deal',
                'desc'  => 'A student-ID discount on weekday slots could pull more of this age bracket into %s.',
            ],
        ],
        '25-34' => [
            [
                'title' => 'Date & Duo Pass',
                'desc'  => 'This bracket often books in pairs — a 2-pax %s rate could raise conversion.',
            ],
            [
                'title' => 'Payday Weekend Promo',
                'desc'  => 'Time a %s discount around payday weekends when this group is most active.',
            ],
        ],
        '35-44' => [
            [
                'title' => 'Family Fun Package',
                'desc'  => 'This bracket is likely booking for their kids — try a kids-go-free or 2-adults-2-kids bundle for %s.',
            ],
            [
                'title' => 'Weekend Family Bundle',
                'desc'  => 'Pair %s with a sibling discount — this age group tends to bring the whole family.',
            ],
            [
                'title' => 'Parent & Child Rate',
                'desc'  => 'A flat per-family rate on %s may convert more of this bracket than per-head pricing.',
            ],
        ],
        '45-54' => [
            [
                'title' => 'Multigenerational Pass',
                'desc'  => 'This bracket often brings extended family — offer a larger group rate on %s.',
            ],
            [
                'title' => 'Comfort Hours Promo',
                'desc'  => 'Slower, less crowded time slots on %s may appeal more to this age group.',
            ],
        ],
        'default' => [
            [
                'title' => 'General Group Promo',
                'desc'  => 'Not enough age data yet for %s — a simple group discount is a safe default.',
            ],
        ],
    ];

    /**
     * Visual identity per attraction category. Extend this as you add
     * more categories.
     */
    protected array $attractionStyle = [
        'DinoLakbay'  => ['icon' => 'fa-water', 'color' => '#16A34A'],
        'Skataya'    => ['icon' => 'fa-bolt', 'color' => '#F97316'],
        'PiyestaRides'  => ['icon' => 'fa-circle-half-stroke', 'color' => '#6366F1'],
    ];

    /**
     * @param array $attractionRanking Same shape used by the Top Attractions
     *                                 table: [['name' => ..., 'top_age_group' => ..., 'bookings' => ...], ...]
     * @return array Ready to pass straight into the view as $recommendations
     */
    public function generate(array $attractionRanking): array
    {
        $recommendations = [];

        foreach ($attractionRanking as $attraction) {
            $name  = $attraction['name'] ?? 'this attraction';
            $age   = $attraction['top_age_group'] ?? null;

            $variant = $this->selectVariant($name, $age);
            $style   = $this->attractionStyle[$name] ?? ['icon' => 'fa-ticket', 'color' => '#C81E5C'];

            $recommendations[] = [
                'attraction' => $name,
                'age_group'  => $age ?? '—',
                'title'      => $variant['title'],
                'desc'       => sprintf($variant['desc'], $name),
                'icon'       => $style['icon'],
                'color'      => $style['color'],
            ];
        }

        return $recommendations;
    }

    /**
     * Deterministically pick a template variant for this attraction so the
     * same attraction always gets the same promo on refresh, but different
     * attractions sharing an age bracket don't collide.
     */
    protected function selectVariant(string $attractionName, ?string $ageGroup): array
    {
        $options = $this->templateBank[$ageGroup] ?? $this->templateBank['default'];
        $index   = crc32($attractionName) % count($options);

        return $options[$index];
    }
}