<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1B1D28; margin: 24px; }
  .header { border-bottom: 3px solid #6C5CE0; padding-bottom: 10px; margin-bottom: 16px; }
  .header h1 { font-size: 18px; margin: 0 0 4px; color: #1E1B3A; }
  .header .meta { font-size: 10px; color: #5B5D72; }
  h2.section { font-size: 13px; background: #EFEBFF; color: #5445C4; padding: 6px 10px; margin: 18px 0 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  th { background: #1E1B3A; color: #fff; padding: 6px 8px; text-align: left; font-size: 9.5px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #ECEDF6; font-size: 10.5px; }
  tr:nth-child(even) td { background: #FBFBFD; }
  .kpi-row td { padding: 8px; }
  .kpi-label { font-size: 9px; color: #5B5D72; text-transform: uppercase; }
  .kpi-value { font-size: 14px; font-weight: bold; color: #1B1D28; }
  .footer { margin-top: 20px; font-size: 9px; color: #5B5D72; text-align: right; }
</style>
</head>
<body>

  <div class="header">
    <h1>{{ $title }}</h1>
    <div class="meta">
      Period: {{ $dateFrom ?: 'All time' }} &rarr; {{ $dateTo ?: 'Present' }}
      &nbsp;|&nbsp; Payment: {{ $paymentMethod ?? 'All Methods' }}
      @if($zone) &nbsp;|&nbsp; Zone: {{ $zone }} @endif
    </div>
  </div>

  <h2 class="section">Key Metrics</h2>
  <table>
    <tr class="kpi-row">
      <td style="width:25%"><div class="kpi-label">Transactions</div><div class="kpi-value">{{ number_format($summary['total_txn'] ?? 0) }}</div></td>
      <td style="width:25%"><div class="kpi-label">Gross</div><div class="kpi-value">&#8369;{{ number_format($summary['total_revenue'] ?? 0, 2) }}</div></td>
      <td style="width:25%"><div class="kpi-label">Items Sold</div><div class="kpi-value">{{ number_format($summary['total_items_sold'] ?? 0) }}</div></td>
      <td style="width:25%"><div class="kpi-label">Discounts</div><div class="kpi-value">&#8369;{{ number_format($summary['total_discounts'] ?? 0, 2) }}</div></td>
    </tr>
  </table>

  <h2 class="section">Top Products (Selected Period)</h2>
  <table>
    <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units Sold</th><th>Gross</th></tr></thead>
    <tbody>
      @forelse(($topProducts['this_month'] ?? []) as $i => $r)
      <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $r['product_name'] }}</td>
        <td>{{ $r['category_name'] }}</td>
        <td>{{ number_format($r['total_qty']) }}</td>
        <td>&#8369;{{ number_format($r['total_revenue'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="5">No data for this period.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Category Breakdown</h2>
  <table>
    <thead><tr><th>Category</th><th>Type</th><th>Units Sold</th><th>Gross</th><th>% of Total</th></tr></thead>
    <tbody>
      @forelse(($categoryBreakdown['by_category'] ?? []) as $r)
      <tr>
        <td>{{ $r['category_name'] }}</td>
        <td>{{ $r['category_type'] }}</td>
        <td>{{ number_format($r['total_qty']) }}</td>
        <td>&#8369;{{ number_format($r['total_revenue'], 2) }}</td>
        <td>{{ $r['percentage'] }}%</td>
      </tr>
      @empty
      <tr><td colspan="5">No data for this period.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Best Sellers</h2>
  <table>
    <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units</th><th>Gross</th></tr></thead>
    <tbody>
      @forelse(($categoryBreakdown['best_sellers'] ?? []) as $i => $r)
      <tr>
        <td>{{ $i+1 }}</td><td>{{ $r['product_name'] }}</td><td>{{ $r['category_name'] }}</td>
        <td>{{ number_format($r['total_qty']) }}</td><td>&#8369;{{ number_format($r['total_revenue'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="5">No data.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Least Sellers</h2>
  <table>
    <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units</th><th>Gross</th></tr></thead>
    <tbody>
      @forelse(($categoryBreakdown['worst_sellers'] ?? []) as $i => $r)
      <tr>
        <td>{{ $i+1 }}</td><td>{{ $r['product_name'] }}</td><td>{{ $r['category_name'] }}</td>
        <td>{{ number_format($r['total_qty']) }}</td><td>&#8369;{{ number_format($r['total_revenue'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="5">No data.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Payment Method Breakdown</h2>
  <table>
    <thead><tr><th>Method</th><th>Transactions</th><th>Gross</th><th>% of Total</th></tr></thead>
    <tbody>
      @forelse(($paymentBreakdown['methods'] ?? []) as $r)
      <tr>
        <td>{{ $r['payment_method'] }}</td>
        <td>{{ number_format($r['num_transactions']) }}</td>
        <td>&#8369;{{ number_format($r['total_revenue'], 2) }}</td>
        <td>{{ $r['percentage'] }}%</td>
      </tr>
      @empty
      <tr><td colspan="4">No data for this period.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Discounts</h2>
  <table>
    <tr class="kpi-row">
      <td style="width:33%"><div class="kpi-label">Total Discounts</div><div class="kpi-value">&#8369;{{ number_format($discountSummary['total_discount'] ?? 0, 2) }}</div></td>
      <td style="width:33%"><div class="kpi-label">Discounted Transactions</div><div class="kpi-value">{{ number_format($discountSummary['total_transactions'] ?? 0) }}</div></td>
      <td style="width:33%"><div class="kpi-label">Share of Gross</div><div class="kpi-value">{{ $discountSummary['percent_of_gross'] ?? 0 }}%</div></td>
    </tr>
  </table>
  <table>
    <thead><tr><th>#</th><th>Discount Type</th><th>Transactions</th><th>Total Discount</th><th>% of Discounts</th></tr></thead>
    <tbody>
      @forelse(($discountSummary['by_type'] ?? []) as $i => $r)
      <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $r['discount_type'] }}</td>
        <td>{{ number_format($r['num_transactions']) }}</td>
        <td>&#8369;{{ number_format($r['total_discount'], 2) }}</td>
        <td>{{ $r['percentage'] }}%</td>
      </tr>
      @empty
      <tr><td colspan="5">No discounts in this period.</td></tr>
      @endforelse
    </tbody>
  </table>

  @if(!empty($hasVouchers))
  <h2 class="section">Online Booking Vouchers (Redeemed)</h2>
  <table>
    <tr class="kpi-row">
      <td style="width:50%"><div class="kpi-label">Vouchers Redeemed</div><div class="kpi-value">{{ number_format($voucherSummary['total_vouchers'] ?? 0) }}</div></td>
      <td style="width:50%"><div class="kpi-label">Voucher Amount</div><div class="kpi-value">&#8369;{{ number_format($voucherSummary['total_amount'] ?? 0, 2) }}</div></td>
    </tr>
  </table>

  <table>
    <thead><tr><th>#</th><th>Package</th><th>Vouchers</th><th>Amount</th></tr></thead>
    <tbody>
      @forelse(($voucherSummary['by_item'] ?? []) as $i => $r)
      <tr>
        <td>{{ $i+1 }}</td>
        <td>{{ $r['name'] }}</td>
        <td>{{ number_format($r['vouchers']) }}</td>
        <td>&#8369;{{ number_format($r['total_amount'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="4">No voucher redemptions for this period.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h2 class="section">Upcoming Voucher Visits (Not Yet Redeemed)</h2>
  <table>
    <thead><tr><th>Visit Date</th><th>Vouchers</th><th>Amount</th></tr></thead>
    <tbody>
      @forelse(($voucherSummary['upcoming'] ?? []) as $r)
      <tr>
        <td>{{ $r['visit_date'] }}</td>
        <td>{{ number_format($r['vouchers']) }}</td>
        <td>&#8369;{{ number_format($r['total_amount'], 2) }}</td>
      </tr>
      @empty
      <tr><td colspan="3">No upcoming unredeemed vouchers.</td></tr>
      @endforelse
    </tbody>
  </table>
  @endif

  <div class="footer">Generated {{ $generatedAt }}</div>

</body>
</html>