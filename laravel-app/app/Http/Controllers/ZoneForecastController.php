<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ZoneForecastController extends Controller
{
    private const ZONES = ['Skataya', 'Snackbar', 'Piyestarides', 'Dinolakbay'];

    // Zones with an empty list have no online-booking vouchers (Snackbar).
    private const ZONE_SERVICES = [
        'Skataya'      => ['rollerfever'],
        'Snackbar'     => [],
        'Piyestarides' => ['field_of_rides'],
        'Dinolakbay'   => ['dino_adventure'],
    ];

    public function index(string $zone)
    {
        $zone = $this->resolveZone($zone);

        return view('zone_forecast.zone_forecast', [
            'zone'        => $zone,
            'hasVouchers' => $this->zoneHasVouchers($zone),
        ]);
    }

    public function voucherSummary(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        return response()->json($this->voucherData($request, self::ZONE_SERVICES[$zone] ?? []));
    }

    public function summary(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        [$dateSql, $dateBindings] = $this->buildDateFilter($request);
        [$paySql, $payBindings]   = $this->buildPaymentFilter($request);

        $defaultMonthSql = $dateSql === ''
            ? " AND MONTH(st.created_at) = MONTH(CURDATE()) AND YEAR(st.created_at) = YEAR(CURDATE()) "
            : $dateSql;

        $row = DB::selectOne("
            SELECT
                COUNT(DISTINCT st.transaction_id) AS total_txn,
                SUM(ti.subtotal)                  AS total_revenue,
                SUM(ti.quantity)                  AS total_items_sold
            FROM sales_transactions st
            JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$defaultMonthSql} {$paySql}
        ", array_merge([$zone], $dateSql === '' ? [] : $dateBindings, $payBindings));

        $lowStock = DB::selectOne("
            SELECT COUNT(*) AS low_stock_count
            FROM products p
            JOIN categories c ON c.category_id = p.category_id
            WHERE p.stock <= 20
              AND p.status = 'active'
              AND c.zone = ?
              AND c.is_stock_tracked = 1
        ", [$zone]);

        return response()->json([
            'zone'              => $zone,
            'total_txn'         => (int) ($row->total_txn ?? 0),
            'total_revenue'     => (float) ($row->total_revenue ?? 0),
            'total_items_sold'  => (int) ($row->total_items_sold ?? 0),
            'low_stock_count'   => (int) ($lowStock->low_stock_count ?? 0),
        ]);
    }

    public function topProducts(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        [$dateSql, $dateBindings] = $this->buildDateFilter($request);
        [$paySql, $payBindings]   = $this->buildPaymentFilter($request);

        $allTime = DB::select("
            SELECT p.product_name, c.category_name,
                   SUM(ti.quantity) AS total_qty,
                   SUM(ti.subtotal) AS total_revenue
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$dateSql} {$paySql}
            GROUP BY p.product_id, p.product_name, c.category_name
            ORDER BY total_qty DESC
            LIMIT 15
        ", array_merge([$zone], $dateBindings, $payBindings));

        $monthSql = $dateSql === ''
            ? " AND MONTH(st.created_at) = MONTH(CURDATE()) AND YEAR(st.created_at) = YEAR(CURDATE()) "
            : $dateSql;

        $thisMonth = DB::select("
            SELECT p.product_name, c.category_name,
                   SUM(ti.quantity) AS total_qty,
                   SUM(ti.subtotal) AS total_revenue
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$monthSql} {$paySql}
            GROUP BY p.product_id, p.product_name, c.category_name
            ORDER BY total_qty DESC
            LIMIT 10
        ", array_merge([$zone], $dateSql === '' ? [] : $dateBindings, $payBindings));

        return response()->json([
            'all_time'   => $allTime,
            'this_month' => $thisMonth,
        ]);
    }

    public function paymentBreakdown(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        [$dateSql, $dateBindings] = $this->buildDateFilter($request);

        $rows = DB::select("
            SELECT st.payment_method,
                   COUNT(DISTINCT st.transaction_id) AS num_transactions,
                   SUM(ti.subtotal)                   AS total_revenue
            FROM sales_transactions st
            JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$dateSql}
            GROUP BY st.payment_method
            ORDER BY total_revenue DESC
        ", array_merge([$zone], $dateBindings));

        return response()->json([
            'methods' => $this->withPercentages($rows, 'total_revenue'),
        ]);
    }

    public function categoryBreakdown(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        [$dateSql, $dateBindings] = $this->buildDateFilter($request);
        [$paySql, $payBindings]   = $this->buildPaymentFilter($request);

        $byType = DB::select("
            SELECT c.category_type,
                   SUM(ti.quantity) AS total_qty,
                   SUM(ti.subtotal) AS total_revenue
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$dateSql} {$paySql}
            GROUP BY c.category_type
            ORDER BY total_revenue DESC
        ", array_merge([$zone], $dateBindings, $payBindings));

        $byCategory = DB::select("
            SELECT c.category_type, c.category_name,
                   SUM(ti.quantity) AS total_qty,
                   SUM(ti.subtotal) AS total_revenue
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$dateSql} {$paySql}
            GROUP BY c.category_type, c.category_id, c.category_name
            ORDER BY total_revenue DESC
        ", array_merge([$zone], $dateBindings, $payBindings));

        $byCategory = $this->withPercentages($byCategory, 'total_revenue');

        $byProduct = DB::select("
            SELECT c.category_type, c.category_name, p.product_id, p.product_name,
                   SUM(ti.quantity) AS total_qty,
                   SUM(ti.subtotal) AS total_revenue
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              {$dateSql} {$paySql}
            GROUP BY c.category_type, c.category_id, c.category_name, p.product_id, p.product_name
            ORDER BY c.category_name, total_revenue DESC
        ", array_merge([$zone], $dateBindings, $payBindings));

        $productsByCategory = [];
        foreach ($byProduct as $r) {
            $productsByCategory[$r->category_name][] = $r;
        }

        foreach ($byCategory as &$cat) {
            $catTotal = (float) $cat['total_revenue'];
            $products = $productsByCategory[$cat['category_name']] ?? [];

            $cat['products'] = array_map(function ($p) use ($catTotal) {
                return [
                    'product_id'             => $p->product_id,
                    'product_name'           => $p->product_name,
                    'total_qty'              => (int) $p->total_qty,
                    'total_revenue'          => (float) $p->total_revenue,
                    'percentage_of_category' => $catTotal > 0
                        ? round(((float) $p->total_revenue / $catTotal) * 100, 1)
                        : 0,
                ];
            }, $products);
        }
        unset($cat);

        $flatProducts = $this->withPercentages($byProduct, 'total_revenue');
        usort($flatProducts, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']);

        return response()->json([
            'by_type'       => $this->withPercentages($byType, 'total_revenue'),
            'by_category'   => $byCategory,
            'best_sellers'  => array_slice($flatProducts, 0, 5),
            'worst_sellers' => array_slice(array_reverse($flatProducts), 0, 5),
        ]);
    }

    public function dayOfWeek(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);

        $byDay = DB::select("
            SELECT
                DAYOFWEEK(st.created_at)          AS dow_num,
                DAYNAME(st.created_at)             AS day_name,
                COUNT(DISTINCT st.transaction_id)  AS num_transactions,
                SUM(ti.quantity)                   AS total_qty,
                SUM(ti.subtotal)                   AS total_revenue
            FROM sales_transactions st
            JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
            GROUP BY dow_num, day_name
            ORDER BY dow_num
        ", [$zone]);

        $weekendTop = DB::select("
            SELECT p.product_name, c.category_name,
                   DAYNAME(st.created_at) AS day_name,
                   SUM(ti.quantity) AS total_qty
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
              AND DAYOFWEEK(st.created_at) IN (1, 7)
            GROUP BY p.product_id, p.product_name, c.category_name, DAYNAME(st.created_at)
            ORDER BY total_qty DESC
            LIMIT 20
        ", [$zone]);

        return response()->json([
            'by_day'      => $byDay,
            'weekend_top' => $weekendTop,
        ]);
    }

    public function hourlyPeaks(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);

        $hourly = DB::select("
            SELECT
                HOUR(st.created_at)               AS hour,
                COUNT(DISTINCT st.transaction_id) AS num_transactions,
                SUM(ti.quantity)                  AS total_items,
                SUM(ti.subtotal)                  AS total_revenue
            FROM sales_transactions st
            JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
            GROUP BY HOUR(st.created_at)
            ORDER BY hour
        ", [$zone]);

        if (empty($hourly)) {
            return response()->json(['hourly' => [], 'peak_hours' => [], 'manpower_advice' => []]);
        }

        $counts = array_map(fn($r) => $r->num_transactions, $hourly);
        sort($counts);
        $threshold = $this->quantile($counts, 0.70);

        $hourLabel = function (int $h) {
            $suffix  = $h < 12 ? 'AM' : 'PM';
            $display = $h <= 12 ? $h : $h - 12;
            if ($display == 0) $display = 12;
            return "{$display}:00 {$suffix}";
        };

        $peakHours = [];
        $advice = [];
        foreach ($hourly as $r) {
            if ($r->num_transactions >= $threshold) {
                $h   = (int) $r->hour;
                $txn = (int) $r->num_transactions;
                $staffNeeded = max(1, round($txn / 10));

                $peakHours[] = $hourLabel($h);
                $advice[] = [
                    'hour'              => $hourLabel($h),
                    'transactions'      => $txn,
                    'recommended_staff' => $staffNeeded,
                    'note'              => "Deploy {$staffNeeded} cashier(s) — avg {$txn} transactions at this hour",
                ];
            }
        }

        $byCategory = DB::select("
            SELECT HOUR(st.created_at) AS hour, c.category_name,
                   SUM(ti.quantity) AS total_qty
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
            GROUP BY HOUR(st.created_at), c.category_name
            ORDER BY hour, total_qty DESC
        ", [$zone]);

        return response()->json([
            'hourly'          => $hourly,
            'peak_hours'      => $peakHours,
            'manpower_advice' => $advice,
            'by_category'     => $byCategory,
        ]);
    }

    public function nextMonthForecast(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);

        $rows = DB::select("
            SELECT p.product_id, p.product_name, c.category_name,
                DATE(st.created_at) AS sale_date,
                SUM(ti.quantity)    AS daily_qty
            FROM transaction_items ti
            JOIN sales_transactions st ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
            AND c.zone = ?
            AND st.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            GROUP BY p.product_id, p.product_name, c.category_name, DATE(st.created_at)
            ORDER BY p.product_id, sale_date
        ", [$zone]);

        if (empty($rows)) {
            return response()->json([
                'forecasts'    => [],
                'note'         => 'Not enough data yet.',
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r->product_id]['name'] = $r->product_name;
            $grouped[$r->product_id]['cat']  = $r->category_name;
            $grouped[$r->product_id]['dates'][] = $r->sale_date;
            $grouped[$r->product_id]['qty'][]   = (float) $r->daily_qty;
        }

        $today = Carbon::today();
        $results = [];

        foreach ($grouped as $productId => $g) {
            $dates = $g['dates'];
            sort($dates);
            $minDate = Carbon::parse($dates[0]);

            $X = [];
            $Y = $g['qty'];
            foreach ($g['dates'] as $d) {
                $X[] = (int) $minDate->diffInDays(Carbon::parse($d));
            }
            if (count($X) < 3) {
            // Not enough history for regression: use a flat average estimate instead
            $windowDays = max(1, $minDate->diffInDays($today) + 1);
            $dailyAvg   = array_sum($Y) / $windowDays;

            $results[] = [
                'product_name'        => $g['name'],
                'category_name'       => $g['cat'],
                'forecast_next_month' => round($dailyAvg * 30),
                'forecast_daily_avg'  => round($dailyAvg, 1),
                'current_month_avg'   => round($dailyAvg, 1),
                'trend'               => 'stable',
                'data_points'         => count($X),
                'low_data'            => false,
            ];
            continue;
        }

            [$slope, $intercept] = $this->linearRegression($X, $Y);

            $lastDay = max($X);
            $futureQty = [];
            for ($d = $lastDay + 1; $d <= $lastDay + 30; $d++) {
                $futureQty[] = max(0, $slope * $d + $intercept);
            }

            $forecastMonthly  = array_sum($futureQty);
            $forecastDailyAvg = $forecastMonthly / count($futureQty);
            $trend = $slope > 0.05 ? 'up' : ($slope < -0.05 ? 'down' : 'stable');

            $thisMonthQty = [];
            foreach ($g['dates'] as $i => $d) {
                if (Carbon::parse($d)->month == $today->month) {
                    $thisMonthQty[] = $g['qty'][$i];
                }
            }
            $thisMonthAvg = count($thisMonthQty) ? array_sum($thisMonthQty) / count($thisMonthQty) : 0;

            $results[] = [
                'product_name'        => $g['name'],
                'category_name'       => $g['cat'],
                'forecast_next_month' => round($forecastMonthly),
                'forecast_daily_avg'  => round($forecastDailyAvg, 1),
                'current_month_avg'   => round($thisMonthAvg, 1),
                'trend'               => $trend,
                'data_points'         => count($X),
            ];
        }

        usort($results, fn($a, $b) => $b['forecast_next_month'] <=> $a['forecast_next_month']);

        return response()->json([
            'forecasts'    => array_slice($results, 0, 20),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function monthlyTrend(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);

        $rows = DB::select("
            SELECT DATE_FORMAT(st.created_at, '%Y-%m') AS month,
                   COUNT(DISTINCT st.transaction_id)    AS num_transactions,
                   SUM(ti.quantity)                     AS total_qty,
                   SUM(ti.subtotal)                     AS total_revenue
            FROM sales_transactions st
            JOIN transaction_items ti ON ti.transaction_id = st.transaction_id
            JOIN products p ON ti.product_id = p.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone = ?
            GROUP BY DATE_FORMAT(st.created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12
        ", [$zone]);

        $rows = array_reverse($rows);

        return response()->json(['monthly' => array_values($rows)]);
    }

    /**
     * Generates and downloads a PDF report for this zone, scoped to the
     * currently selected date_from / date_to / payment_method filters.
     */
    public function downloadReport(Request $request, string $zone)
    {
        $zone = $this->resolveZone($zone);
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $payment  = $request->query('payment_method', 'all');

        $pdf = Pdf::loadView('reports.sales_report', [
            'title'             => $zone . ' — Forecast Report',
            'zone'              => $zone,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
            'paymentMethod'     => $payment === 'all' ? null : $payment,
            'summary'           => $this->summary($request, $zone)->getData(true),
            'topProducts'       => $this->topProducts($request, $zone)->getData(true),
            'paymentBreakdown'  => $this->paymentBreakdown($request, $zone)->getData(true),
            'categoryBreakdown' => $this->categoryBreakdown($request, $zone)->getData(true),
            'voucherSummary'    => $this->voucherData($request, self::ZONE_SERVICES[$zone] ?? []),
            'hasVouchers'       => $this->zoneHasVouchers($zone),
            'generatedAt'       => now()->format('F j, Y g:i A'),
        ])->setPaper('a4', 'portrait');

        $slug = str_replace(' ', '-', strtolower($zone));
        return $pdf->download("zone-report-{$slug}-" . now()->format('Y-m-d_His') . '.pdf');
    }

    /* ── Helpers ───────────────────────────────────────────── */

    private function resolveZone(string $zone): string
    {
        $zone = urldecode($zone);

        if (!in_array($zone, self::ZONES, true)) {
            abort(404, 'Unknown zone: ' . $zone);
        }

        return $zone;
    }

    private function zoneHasVouchers(string $zone): bool
    {
        return !empty(self::ZONE_SERVICES[$zone] ?? []);
    }

    private function buildDateFilter(Request $request): array
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $sql = '';
        $bindings = [];

        if ($dateFrom) {
            $sql .= ' AND DATE(st.created_at) >= ? ';
            $bindings[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= ' AND DATE(st.created_at) <= ? ';
            $bindings[] = $dateTo;
        }

        return [$sql, $bindings];
    }

    private function buildPaymentFilter(Request $request): array
    {
        $method = $request->query('payment_method');

        if ($method && $method !== 'all') {
            return [' AND st.payment_method = ? ', [$method]];
        }

        return ['', []];
    }

    private function voucherData(Request $request, ?array $services = null): array
    {
        [$dateSql, $dateBindings] = $this->buildDateFilter($request);

        $svcSql = '';
        $svcBindings = [];
        if ($services !== null) {
            if (empty($services)) {
                return ['total_vouchers' => 0, 'total_amount' => 0.0, 'by_item' => [], 'by_day' => [], 'upcoming' => []];
            }
            $svcSql = ' AND b.service IN (' . implode(',', array_fill(0, count($services), '?')) . ') ';
            $svcBindings = $services;
        }
        $bindings = array_merge($dateBindings, $svcBindings);

        $redeemed = DB::select("
            SELECT b.service, b.package, b.tier,
                   COUNT(*)     AS vouchers,
                   SUM(b.price) AS total_amount
            FROM bookings b
            JOIN sales_transactions st ON st.transaction_id = b.redeemed_transaction_id
            WHERE b.status = 'done'
              AND st.transaction_status = 'Completed'
              {$dateSql} {$svcSql}
            GROUP BY b.service, b.package, b.tier
            ORDER BY total_amount DESC
        ", $bindings);

        $byDay = DB::select("
            SELECT DATE(st.created_at) AS day,
                   COUNT(*)            AS vouchers,
                   SUM(b.price)        AS total_amount
            FROM bookings b
            JOIN sales_transactions st ON st.transaction_id = b.redeemed_transaction_id
            WHERE b.status = 'done'
              AND st.transaction_status = 'Completed'
              {$dateSql} {$svcSql}
            GROUP BY DATE(st.created_at)
            ORDER BY day
        ", $bindings);

        $upcoming = DB::select("
            SELECT b.visit_date,
                   COUNT(*)     AS vouchers,
                   SUM(b.price) AS total_amount
            FROM bookings b
            WHERE b.status = 'confirmed'
              AND b.visit_date >= CURDATE()
              {$svcSql}
            GROUP BY b.visit_date
            ORDER BY b.visit_date
            LIMIT 30
        ", $svcBindings);

        $booking = app(\App\Http\Controllers\User\BookingController::class);
        $items = [];
        $totalV = 0;
        $totalA = 0.0;
        foreach ($redeemed as $r) {
            $info = $booking->packageInfo($r->service, $r->package);
            $items[] = [
                'name'         => $info['service_name'] . ' — ' . $info['package_name'] . ' (' . $r->tier . ' pax)',
                'vouchers'     => (int) $r->vouchers,
                'total_amount' => (float) $r->total_amount,
            ];
            $totalV += (int) $r->vouchers;
            $totalA += (float) $r->total_amount;
        }

        return [
            'total_vouchers' => $totalV,
            'total_amount'   => $totalA,
            'by_item'        => $items,
            'by_day'         => $byDay,
            'upcoming'       => $upcoming,
        ];
    }

    private function withPercentages(array $rows, string $field): array
    {
        $grandTotal = array_sum(array_map(fn($r) => (float) $r->$field, $rows));

        return array_map(function ($r) use ($field, $grandTotal) {
            $arr = (array) $r;
            $arr['percentage'] = $grandTotal > 0
                ? round(((float) $r->$field / $grandTotal) * 100, 1)
                : 0;
            return $arr;
        }, $rows);
    }

    private function linearRegression(array $X, array $Y): array
    {
        $n = count($X);
        $sumX = array_sum($X);
        $sumY = array_sum($Y);
        $sumXY = 0;
        $sumXX = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $X[$i] * $Y[$i];
            $sumXX += $X[$i] * $X[$i];
        }
        $denom = ($n * $sumXX - $sumX * $sumX);
        if ($denom == 0) {
            return [0, $sumY / $n];
        }
        $slope = ($n * $sumXY - $sumX * $sumY) / $denom;
        $intercept = ($sumY - $slope * $sumX) / $n;
        return [$slope, $intercept];
    }

    private function quantile(array $sortedValues, float $q): float
    {
        $n = count($sortedValues);
        if ($n === 0) return 0;
        $pos = $q * ($n - 1);
        $base = (int) floor($pos);
        $rest = $pos - $base;
        if (isset($sortedValues[$base + 1])) {
            return $sortedValues[$base] + $rest * ($sortedValues[$base + 1] - $sortedValues[$base]);
        }
        return $sortedValues[$base];
    }
}