<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MlForecastController extends Controller
{
    private const ZONES = ['Skataya', 'Snackbar', 'Piyestarides', 'Dinolakbay'];

    public function index()
    {
        return view('ml_forecast.ml_forecast', ['zones' => self::ZONES]);
    }

    /**
     * Gross summary per zone:
     *  - this month (month-to-date) vs the same day-range of last month
     *  - last 6 months series for the sparkline / comparison chart
     */
    public function zoneSummary()
    {
        $now = Carbon::now();

        $thisStart = $now->copy()->startOfMonth();
        $thisEnd   = $now->copy()->addDay()->startOfDay();          // exclusive

        $lastStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $daysSoFar = $thisStart->diffInDays($thisEnd);              // days elapsed this month
        $lastEnd   = $lastStart->copy()->addDays($daysSoFar);       // same range last month
        $monthEnd  = $lastStart->copy()->endOfMonth()->addSecond();
        if ($lastEnd->gt($monthEnd)) {
            $lastEnd = $monthEnd;                                   // cap (e.g. 31 days vs 30-day month)
        }

        $placeholders = implode(',', array_fill(0, count(self::ZONES), '?'));

        // Month-to-date vs previous month (same range)
        $mtdRows = DB::select("
            SELECT c.zone,
                   SUM(CASE WHEN st.created_at >= ? AND st.created_at < ? THEN ti.subtotal ELSE 0 END) AS this_gross,
                   SUM(CASE WHEN st.created_at >= ? AND st.created_at < ? THEN ti.subtotal ELSE 0 END) AS last_gross,
                   COUNT(DISTINCT CASE WHEN st.created_at >= ? AND st.created_at < ? THEN st.transaction_id END) AS this_txn
            FROM transaction_items ti
            JOIN sales_transactions st ON st.transaction_id = ti.transaction_id
            JOIN products p ON p.product_id = ti.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone IN ({$placeholders})
              AND st.created_at >= ? AND st.created_at < ?
            GROUP BY c.zone
        ", array_merge(
            [$thisStart, $thisEnd, $lastStart, $lastEnd, $thisStart, $thisEnd],
            self::ZONES,
            [$lastStart, $thisEnd]
        ));

        $mtd = [];
        foreach ($mtdRows as $r) {
            $mtd[$r->zone] = $r;
        }

        // Last 6 months series per zone
        $seriesStart = $now->copy()->subMonthsNoOverflow(5)->startOfMonth();

        $monthlyRows = DB::select("
            SELECT c.zone,
                   DATE_FORMAT(st.created_at, '%Y-%m') AS month,
                   SUM(ti.subtotal) AS gross
            FROM transaction_items ti
            JOIN sales_transactions st ON st.transaction_id = ti.transaction_id
            JOIN products p ON p.product_id = ti.product_id
            JOIN categories c ON c.category_id = p.category_id
            WHERE st.transaction_status = 'Completed'
              AND c.zone IN ({$placeholders})
              AND st.created_at >= ?
            GROUP BY c.zone, DATE_FORMAT(st.created_at, '%Y-%m')
            ORDER BY month
        ", array_merge(self::ZONES, [$seriesStart]));

        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $months[] = $seriesStart->copy()->addMonthsNoOverflow($i)->format('Y-m');
        }

        $byZoneMonth = [];
        foreach ($monthlyRows as $r) {
            $byZoneMonth[$r->zone][$r->month] = (float) $r->gross;
        }

        $zones = [];
        foreach (self::ZONES as $zone) {
            $thisGross = (float) ($mtd[$zone]->this_gross ?? 0);
            $lastGross = (float) ($mtd[$zone]->last_gross ?? 0);
            $txn       = (int)   ($mtd[$zone]->this_txn ?? 0);

            if ($lastGross > 0) {
                $changePct = round((($thisGross - $lastGross) / $lastGross) * 100, 1);
            } else {
                $changePct = $thisGross > 0 ? 100.0 : 0.0;
            }

            $trend = $changePct > 1 ? 'up' : ($changePct < -1 ? 'down' : 'stable');

            $series = [];
            foreach ($months as $m) {
                $series[] = $byZoneMonth[$zone][$m] ?? 0.0;
            }

            $zones[] = [
                'zone'         => $zone,
                'this_gross'   => $thisGross,
                'last_gross'   => $lastGross,
                'change_pct'   => $changePct,
                'trend'        => $trend,
                'transactions' => $txn,
                'series'       => $series,
            ];
        }

        return response()->json([
            'zones'        => $zones,
            'months'       => $months,
            'generated_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }
}