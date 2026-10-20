<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SiteVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VisitorSummaryController extends Controller
{
    protected array $ageBrackets = [
        '0-12'  => [0, 12],
        '13-17' => [13, 17],
        '18-24' => [18, 24],
        '25-34' => [25, 34],
        '35-44' => [35, 44],
        '45-54' => [45, 54],
        '55+'   => [55, 200],
    ];

    // Keyed by the raw value stored in bookings.service
    protected array $attractionMeta = [
        'field_of_rides' => ['name' => 'Field of Rides', 'icon' => 'fa-circle-half-stroke', 'color' => '#4C6FFF'],
        'dino_adventure' => ['name' => 'Dino Adventure', 'icon' => 'fa-water',              'color' => '#1FA37A'],
        'rollerfever'    => ['name' => 'Roller Fever',   'icon' => 'fa-bolt',               'color' => '#FF7A45'],
    ];

    // Possible columns in `users` that hold the visitor's place (first one found is used).
    // Palitan/dagdagan ayon sa aktwal na column mo.
    protected array $placeColumnCandidates = ['city', 'municipality', 'address', 'province'];

    protected array $promoPlaybook = [
        '0-12' => [
            ['title' => 'Kiddie Explorer Bundle', 'desc' => 'Bundle in extra playground/slide access — most of this group are young children who love repeat play.'],
            ['title' => 'Add-On Slide Pass',      'desc' => 'Offer a discounted add-on for an extra slide or ride — this bracket skews young and benefits from more play variety.'],
        ],
        '13-17' => [
            ['title' => 'Student Squad Promo', 'desc' => 'Offer a discounted weekday rate for school groups of 10+ pax to fill mid-week slots with this bracket.'],
            ['title' => 'Field Trip Rate',     'desc' => 'Pitch a bulk-booking group rate to schools targeting this attraction for organized outings.'],
        ],
        '18-24' => [
            ['title' => 'Barkada Bundle',             'desc' => 'Push a 4-6 pax group package with a free photo pass — this age group books in groups and shares on social media.'],
            ['title' => 'Social Media Shoutout Deal', 'desc' => 'Offer a small discount for visitors who tag the attraction on social media before entry.'],
        ],
        '25-34' => [
            ['title' => 'Weekend Getaway Deal', 'desc' => 'Bundle tickets with a food voucher for young professionals looking for a weekend escape.'],
            ['title' => 'Couples Combo',        'desc' => 'Offer a 2-pax date package with a discount on a second attraction.'],
        ],
        '35-44' => [
            ['title' => 'Family Fun Package',   'desc' => 'Try a kids-go-free or 2-adults-2-kids bundle — this bracket is likely booking for their children.'],
            ['title' => 'Weekday Family Saver', 'desc' => 'Offer an off-peak weekday family bundle to spread out weekend crowding.'],
            ['title' => 'Group of 4+ Discount', 'desc' => 'Push a flat discount for families booking 4 or more tickets together.'],
        ],
        '45-54' => [
            ['title' => 'Weekday Leisure Rate',  'desc' => 'Offer off-peak weekday pricing — this group tends to prefer quieter, less crowded visits.'],
            ['title' => 'Relaxed Visit Package', 'desc' => 'Bundle a slower-paced attraction with a dining voucher for this bracket.'],
        ],
        '55+' => [
            ['title' => 'Senior Citizen Discount', 'desc' => 'Highlight your senior discount and lower-intensity attractions or packages for this bracket.'],
        ],
        'Unknown' => [
            ['title' => 'Encourage Profile Completion', 'desc' => 'Prompt these visitors to complete their age info at checkout so future promos can target them properly.'],
        ],
    ];

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        // Detect which "place" column exists in users (avoids SQL errors).
        $placeColumn = collect($this->placeColumnCandidates)
            ->first(fn ($c) => Schema::hasColumn('users', $c));
        $placeSelect = $placeColumn ? "users.`{$placeColumn}`" : 'NULL';

        $bookings = Booking::query()
            // One join on whichever id is set -> no duplicate rows (the old orOn could duplicate).
            ->leftJoin('users', 'users.user_id', '=', DB::raw('COALESCE(bookings.customer_id, bookings.user_id)'))
            ->whereRaw('COALESCE(bookings.reservation_date, bookings.visit_date) >= ?', [$from->toDateString()])
            ->select('bookings.*', 'users.age as user_age', 'users.email as user_email')
            ->addSelect(DB::raw("{$placeSelect} as user_place"))
            ->addSelect(DB::raw('COALESCE(bookings.customer_name, users.fullname) as resolved_customer_name'))
            ->addSelect(DB::raw('COALESCE(bookings.customer_id, bookings.user_id) as resolved_user_id'))
            ->orderByDesc('bookings.created_at')
            ->get();

        $totalOnlineBookings = $bookings->count();

        // ---------- Attraction ranking ----------
        $attractionRanking = $bookings
            ->groupBy('service')
            ->map(function ($group, $serviceCode) {
                $meta = $this->metaFor($serviceCode);

                $ageBreakdown = collect($this->ageCountsForBookings($group))
                    ->filter(fn ($count, $label) => $label !== 'Unknown' || $count > 0)
                    ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
                    ->values();

                $total = $group->count();

                return [
                    'name'           => $meta['name'],
                    'icon'           => $meta['icon'],
                    'color'          => $meta['color'],
                    'bookings'       => $total,
                    'total_bookings' => $total,
                    'top_age_group'  => $ageBreakdown->sortByDesc('count')->first()['label'] ?? '—',
                ];
            })
            ->sortByDesc('total_bookings')
            ->values();

        $serviceDistribution = $attractionRanking
            ->map(fn ($a) => [
                'name'       => $a['name'],
                'count'      => $a['total_bookings'],
                'percentage' => $totalOnlineBookings > 0
                    ? round(($a['total_bookings'] / $totalOnlineBookings) * 100, 1)
                    : 0,
                'color'      => $a['color'],
            ])
            ->values();

        // ---------- Package ranking (per service, NOT limited to top 5 overall) ----------
        $packageRanking = $bookings
            ->groupBy(fn ($b) => ($b->service ?: 'other') . '|' . ($b->package ?: 'Unspecified'))
            ->map(function ($group) {
                $first = $group->first();
                $meta  = $this->metaFor($first->service);

                return [
                    'package' => $this->prettifyPackageCode($first->package ?: 'Unspecified'),
                    'service' => $meta['name'],
                    'color'   => $meta['color'],
                    'icon'    => $meta['icon'],
                    'count'   => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        // ---------- Origins per service (taga-saan) ----------
        $originsByService = $bookings
            ->groupBy(fn ($b) => $this->metaFor($b->service)['name'])
            ->map(fn ($group) => $group
                ->groupBy(fn ($b) => $this->cleanPlace($b->user_place))
                ->map(fn ($g, $place) => ['place' => $place, 'count' => $g->count()])
                ->sortByDesc('count')
                ->values()
                ->all()
            )
            ->all();

        // ---------- Top online bookers ----------
        $topBookers = $bookings
            ->groupBy(fn ($b) => $b->resolved_user_id ?: ($b->user_email ?: $b->resolved_customer_name))
            ->map(function ($g) {
                $b = $g->first();
                return [
                    'name'         => $b->resolved_customer_name ?: ('Visitor #' . $b->resolved_user_id),
                    'email'        => $b->user_email,
                    'place'        => $this->cleanPlace($b->user_place, null),
                    'bookings'     => $g->count(),
                    'last_booking' => $g->max('created_at'),
                ];
            })
            ->sortByDesc('bookings')
            ->take(10)
            ->values();

        // ---------- Promo recommendations ----------
        $recommendations = $attractionRanking
            ->filter(fn ($a) => $a['total_bookings'] > 0 && isset($this->promoPlaybook[$a['top_age_group']]))
            ->map(function ($a) {
                $variants = $this->promoPlaybook[$a['top_age_group']];
                $promo = $variants[crc32($a['name']) % count($variants)];

                return [
                    'attraction' => $a['name'],
                    'icon'       => $a['icon'],
                    'color'      => $a['color'],
                    'age_group'  => $a['top_age_group'],
                    'title'      => $promo['title'],
                    'desc'       => $promo['desc'],
                ];
            })
            ->values();

        // ---------- Visitor logins ----------
        $recentSince = now()->subMinutes(5);

        $onlineUserIds = SiteVisit::where('last_seen_at', '>=', $recentSince)
            ->pluck('user_id')->filter()->unique()->values()->all();

        // Real last activity per user (falls back to booking created_at).
        $lastSeenByUser = SiteVisit::whereNotNull('user_id')
            ->groupBy('user_id')
            ->selectRaw('user_id, MAX(last_seen_at) as last_seen')
            ->pluck('last_seen', 'user_id');

        $today = now()->startOfDay();
        $upcomingBookings = $bookings->filter(function ($b) use ($today) {
            $date = $b->reservation_date ?? $b->visit_date;
            return $date && Carbon::parse($date)->startOfDay()->gte($today);
        });

        $visitorLogins = $upcomingBookings
            ->groupBy(fn ($b) => $b->resolved_user_id ?: ($b->user_email ?: $b->resolved_customer_name))
            ->map(function ($group) use ($onlineUserIds, $lastSeenByUser) {
                $b = $group->first();
                $name = $b->resolved_customer_name ?: ('Visitor #' . $b->resolved_user_id);

                $lastSeen = $b->resolved_user_id ? ($lastSeenByUser[$b->resolved_user_id] ?? null) : null;
                $lastLogin = $lastSeen ? Carbon::parse($lastSeen) : $b->created_at;

                return [
                    'id'             => $b->id,
                    'name'           => $name,
                    'initials'       => $this->initialsFromName($name),
                    'email'          => $b->user_email,
                    'last_login'     => optional($lastLogin)->format('M d, Y h:i A'),
                    'online'         => $b->resolved_user_id ? in_array($b->resolved_user_id, $onlineUserIds) : false,
                    'reservation_id' => $b->id,
                    '_sort'          => $lastLogin,
                ];
            })
            ->sortByDesc('_sort')
            ->take(50)
            ->map(fn ($v) => collect($v)->except('_sort')->all())
            ->values();

        return view('customer.visitor-summary', compact(
            'days',
            'totalOnlineBookings',
            'attractionRanking',
            'serviceDistribution',
            'packageRanking',
            'originsByService',
            'topBookers',
            'recommendations',
            'visitorLogins'
        ));
    }

    public function liveCount()
    {
        return response()->json([
            'online_now' => SiteVisit::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
        ]);
    }

    // ---------------------------------------------------------------- helpers

    protected function metaFor(?string $serviceCode): array
    {
        return $this->attractionMeta[$serviceCode] ?? [
            'name'  => $serviceCode ? $this->prettifyPackageCode($serviceCode) : 'Other',
            'icon'  => 'fa-ticket',
            'color' => '#6B7280',
        ];
    }

    protected function cleanPlace($place, ?string $fallback = 'Unknown'): ?string
    {
        $place = trim((string) $place);
        return $place !== '' ? $place : $fallback;
    }

    protected function ageCountsForBookings($bookings): array
    {
        $labels = array_merge(array_keys($this->ageBrackets), ['Unknown']);
        $totals = array_fill_keys($labels, 0);

        foreach ($bookings as $booking) {
            foreach ($this->bookingAgeCounts($booking) as $label => $count) {
                $totals[$label] += $count;
            }
        }

        return $totals;
    }

    protected function bookingAgeCounts($booking): array
    {
        $labels = array_merge(array_keys($this->ageBrackets), ['Unknown']);
        $counts = array_fill_keys($labels, 0);

        $raw = $booking->age_breakdown ?? null;
        $decoded = is_array($raw) ? $raw : (is_string($raw) ? json_decode($raw, true) : null);

        if (is_array($decoded) && array_sum(array_map('intval', $decoded)) > 0) {
            foreach ($this->ageBrackets as $label => $range) {
                $counts[$label] = (int) ($decoded[$label] ?? 0);
            }
            $counts['Unknown'] = (int) ($decoded['Unknown'] ?? 0);

            return $counts;
        }

        $label = $this->resolveAgeGroup($booking->user_age);
        $counts[$label ?? 'Unknown'] = 1;

        return $counts;
    }

    protected function ageInRange(mixed $age, int $min, int $max): bool
    {
        if ($age === null || $age === '' || !is_numeric($age)) {
            return false;
        }

        $age = (int) $age;

        return $age >= $min && $age <= $max;
    }

    protected function resolveAgeGroup(mixed $age): ?string
    {
        foreach ($this->ageBrackets as $label => [$min, $max]) {
            if ($this->ageInRange($age, $min, $max)) {
                return $label;
            }
        }

        return null;
    }

    protected function prettifyPackageCode(string $code): string
    {
        $label = str_replace('_', ' ', $code);
        $label = preg_replace('/(\d+)([a-zA-Z])/', '$1 $2', $label);

        return ucwords(trim($label));
    }

    protected function initialsFromName(string $name): string
    {
        $parts = array_values(array_filter(preg_split('/\s+/', trim($name)), fn ($p) => $p !== ''));

        if (empty($parts)) {
            return '?';
        }

        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 2));
        }

        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }
}