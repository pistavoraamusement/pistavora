<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $sal->name }}</title>

    @php
        use Carbon\Carbon;

        // ── Cut Off Date: galing sa mismong period ng payroll (hindi sa petsa
        //    kung kailan ginawa), kaya laging tama ang 1-15 o 16-katapusan. ──
        if (!empty($sal->payroll_period_start) && !empty($sal->payroll_period_end)) {
            $pStart = Carbon::parse($sal->payroll_period_start);
            $pEnd = Carbon::parse($sal->payroll_period_end);
            $cutoffLabel = $pStart->format('F j') . '-' . $pEnd->format('j, Y');
        } else {
            $base = Carbon::parse($sal->created_at ?? now());
            if (($sal->cutoff_type ?? '1st') === '2nd') {
                $cutoffLabel = $base->format('F') . ' 16-' . $base->copy()->endOfMonth()->day . ', ' . $base->format('Y');
            } else {
                $cutoffLabel = $base->format('F') . ' 1-15, ' . $base->format('Y');
            }
        }

        // ── Salary date ──
        $payDate = Carbon::parse(
            data_get($sal, 'pay_date') ?: (data_get($sal, 'generated_at') ?: (data_get($sal, 'created_at') ?: now()))
        );

        // ── Earnings ──
        $presentDays = (int) ($sal->present_days ?? $sal->total_days ?? 0);
        $dailyRate = (float) ($sal->daily_rate ?? 0);
        $basicPay = (float) ($sal->basic_salary ?? ($presentDays * $dailyRate));
        $holidayPay = (float) ($sal->holiday_pay ?? 0);
        $overtimePay = (float) ($sal->overtime_pay ?? 0);
        $holidayOtPay = (float) ($sal->holiday_ot_pay ?? 0);
        $allowance = (float) data_get($sal, 'allowance', data_get($sal, 'allowances', 0));
        $refund = (float) data_get($sal, 'refund', 0);

        // Total Earnings = Basic + Holiday + Overtime + Holiday OT
        $grossPay = (float) ($sal->gross_pay ?? ($basicPay + $holidayPay + $overtimePay + $holidayOtPay));

        // ── Deductions ──
        // "Late" (lampas sa grace period) at "Undertime" (kulang ang oras na
        // nagawa) ay magkahiwalay na linya, para malinaw kung alin ang alin.
        $lateAmt = (float) ($sal->deduction ?? 0);
        $undertimeAmt = (float) ($sal->undertime_deduction ?? 0);
        $totalDeduction =
            $lateAmt +
            $undertimeAmt +
            (float) ($sal->sss_deduction ?? 0) +
            (float) ($sal->philhealth_deduction ?? 0) +
            (float) ($sal->pagibig_deduction ?? 0) +
            (float) ($sal->withholding_tax ?? 0);

        $netPay = (float) ($sal->net_salary ?? ($sal->net_pay ?? ($grossPay - $totalDeduction)));

        // ── Detalye ng oras (galing sa controller) ──
        $sum = $summary ?? [];
        $otHours = (float) ($sum['ot_hours'] ?? 0);
        $lateMin = (int) ($sum['late_minutes'] ?? 0);
        $underMin = (int) ($sum['undertime_minutes'] ?? 0);

        $fmtHrs = function ($v) {
            $t = rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
            return $t === '' ? '0' : $t;
        };
        $dashIfZero = fn($v) => ((float) $v) > 0 ? number_format((float) $v, 2) : '-';
    @endphp

    <style>
        :root {
            --border-color: #2E7D6E;
            --border-thin: #000;
            --ink: #1A1523;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #EFEFEF;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            padding: 30px 16px;
            font-size: 13px;
        }

        .page-wrap {
            max-width: 760px;
            margin: 0 auto;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 14px;
        }

        .btn-print,
        .btn-back {
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: Arial, sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ccc;
            text-decoration: none;
        }

        .btn-print {
            background: #B82850;
            color: #fff;
            border: none;
        }

        .btn-print:hover {
            background: #a11f43;
        }

        .btn-back {
            background: #fff;
            color: #333;
        }

        .btn-back:hover {
            background: #f5f5f5;
        }

        .payslip-doc {
            background: #fff;
            border: 1.5px solid var(--border-thin);
        }

        .row {
            display: flex;
        }

        .cell {
            border: 1px solid var(--border-thin);
            padding: 5px 10px;
        }

        /* Header */
        .company-row {
            border: 1.5px solid var(--border-color);
        }

        .company-row .cell {
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            padding: 8px;
        }

        .dept-row {
            border-left: 1.5px solid var(--border-color);
            border-right: 1.5px solid var(--border-color);
            border-bottom: 1.5px solid var(--border-color);
        }

        .dept-row .cell {
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            padding: 6px;
        }

        .address-block {
            text-align: center;
            padding: 10px 8px 4px;
            font-size: 12px;
            line-height: 1.5;
        }

        .payslip-title {
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 1px;
            padding: 12px 0 8px;
        }

        /* Employee info */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .info-table td {
            padding: 4px 10px;
            font-size: 12.5px;
            vertical-align: middle;
        }

        .info-label {
            font-weight: 600;
            white-space: nowrap;
            width: 150px;
        }

        .info-value {
            border-bottom: 1px solid #000;
            font-weight: 700;
            padding-bottom: 2px;
        }

        .info-value.wide {
            min-width: 220px;
        }

        /* Earnings / Deductions table */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .pay-table th,
        .pay-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12.5px;
        }

        .pay-table th {
            font-weight: 700;
            text-align: left;
            border-bottom: 2px solid #000;
        }

        .pay-table td.amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .pay-table td.label {
            text-align: left;
        }

        .pay-table .total-row td {
            font-weight: 700;
            background: #F5F5F5;
        }

        .note {
            display: block;
            font-weight: 400;
            font-size: 10.5px;
            color: #555;
            margin-top: 1px;
        }

        .col-amt {
            width: 120px;
        }

        .signature-block {
            padding: 26px 10px 10px;
            text-align: center;
        }

        .signature-line-wrap {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 6px;
        }

        .received-label {
            font-weight: 600;
            white-space: nowrap;
        }

        .signature-line {
            flex: 1;
            border-bottom: 1px solid #000;
            height: 26px;
        }

        .signature-caption {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .action-bar {
                display: none;
            }

            .page-wrap {
                max-width: 100%;
            }

            .payslip-doc {
                border-width: 1.5px;
            }

            .pay-table .total-row td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media(max-width:600px) {
            .info-table td {
                display: block;
                width: 100% !important;
                padding: 3px 10px;
            }

            .info-label {
                width: auto;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrap">

        <div class="action-bar">
            <a href="{{ route('salary') }}" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Payslip
            </button>
        </div>

        <div class="payslip-doc">

            <div class="row company-row">
                <div class="cell" style="flex:1;">PISTAVORA</div>
            </div>
            <div class="row dept-row">
                <div class="cell" style="flex:1;">Skataya</div>
            </div>

            <div class="address-block">
                Lower Deck, Building J, The Outlets at Lipa<br>
                Lima Estates, Brgy. Bugtong na Pulo, Lipa City, Batangas, 4217
            </div>

            <div class="payslip-title">PAYSLIP</div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Name of the Employee:</td>
                    <td colspan="3"><span class="info-value wide">{{ strtoupper($sal->name) }}</span></td>
                </tr>
                <tr>
                    <td class="info-label">Designation:</td>
                    <td colspan="3"><span class="info-value wide">{{ strtoupper($sal->category ?? '-') }}</span></td>
                </tr>
                <tr>
                    <td class="info-label">Cut Off Date:</td>
                    <td><span class="info-value">{{ $cutoffLabel }}</span></td>
                    <td class="info-label" style="text-align:right;">Salary date:</td>
                    <td><span class="info-value">{{ $payDate->format('F d, Y') }}</span></td>
                </tr>
            </table>

            <table class="pay-table">
                <thead>
                    <tr>
                        <th>Earnings</th>
                        <th class="col-amt">Amount</th>
                        <th>Deductions</th>
                        <th class="col-amt">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">
                            Basic Pay
                            <span class="note">{{ $presentDays }} day(s) &times; {{ number_format($dailyRate, 2) }}</span>
                        </td>
                        <td class="amount">{{ number_format($basicPay, 2) }}</td>
                        <td class="label">SSS</td>
                        <td class="amount">{{ number_format($sal->sss_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total working days</td>
                        <td class="amount">{{ $presentDays }}</td>
                        <td class="label">PhilHealth</td>
                        <td class="amount">{{ number_format($sal->philhealth_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Daily Rate</td>
                        <td class="amount">{{ number_format($dailyRate, 2) }}</td>
                        <td class="label">Pag-IBIG</td>
                        <td class="amount">{{ number_format($sal->pagibig_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">
                            Holiday
                            @if ($holidayPay > 0)
                        
                            @endif
                        </td>
                        <td class="amount">{{ number_format($holidayPay, 2) }}</td>
                        <td class="label">Withholding Tax</td>
                        <td class="amount">{{ number_format($sal->withholding_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Allowances</td>
                        <td class="amount">{{ number_format($allowance, 2) }}</td>
                        <td class="label">Salary Loan</td>
                        <td class="amount">{{ $dashIfZero(data_get($sal, 'salary_loan', 0)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">
                            Overtime
                            @if ($otHours > 0)
                                <span class="note">{{ $fmtHrs($otHours) }} hr(s) OT</span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($overtimePay, 2) }}</td>
                        <td class="label">Pag-IBIG Loan</td>
                        <td class="amount">{{ $dashIfZero(data_get($sal, 'pagibig_loan', 0)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Holiday OT</td>
                        <td class="amount">{{ number_format($holidayOtPay, 2) }}</td>
                        <td class="label">SSS Loan</td>
                        <td class="amount">{{ $dashIfZero(data_get($sal, 'sss_loan', 0)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Refund</td>
                        <td class="amount">{{ $dashIfZero($refund) }}</td>
                        <td class="label">
                            Late
                            @if ($lateMin > 0)
                                <span class="note">{{ $lateMin }} minute(s) late</span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($lateAmt, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label"></td>
                        <td class="amount"></td>
                        <td class="label">
                            Undertime
                            @if ($undertimeAmt > 0)
                                <span class="note">
                                    @if ($underMin > 0){{ $underMin }} minute(s) kulang &middot; @endif
                                    hindi kumpleto ang shift, hindi kasama sa Late
                                </span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($undertimeAmt, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label"></td>
                        <td class="amount"></td>
                        <td class="label">Total Deduction</td>
                        <td class="amount">{{ number_format($totalDeduction, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total Earnings &nbsp;Php</td>
                        <td class="amount">{{ number_format($grossPay, 2) }}</td>
                        <td class="label">Net Pay &nbsp;Php</td>
                        <td class="amount">{{ number_format($netPay, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-block">
                <div class="signature-line-wrap">
                    <span class="received-label">Received by:</span>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-caption">Signature over printed name</div>
            </div>

        </div>
    </div>

</body>

</html>