<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pay Slip - {{ $payslip->employee->name }}</title>
    @php
        $logoPath = public_path('images/lasac_logo.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $paymentDate = $period->date_of_payment 
            ? $period->date_of_payment->format('F d, Y') 
            : ($period->end_date ? $period->end_date->copy()->addDays(5)->format('F d, Y') : 'Pending');

        $cutoffRange = '';
        if (!empty($period->start_date) && !empty($period->end_date)) {
            $s = \Carbon\Carbon::parse($period->start_date);
            $e = \Carbon\Carbon::parse($period->end_date);
            if ($s->year === $e->year) {
                if ($s->month === $e->month) {
                    $cutoffRange = $s->format('M d') . ' - ' . $e->format('d, Y');
                } else {
                    $cutoffRange = $s->format('M d') . ' - ' . $e->format('M d, Y');
                }
            } else {
                $cutoffRange = $s->format('M d, Y') . ' - ' . $e->format('M d, Y');
            }
        } elseif (!empty($period->name)) {
            $cutoffRange = trim($period->name, '()');
        } else {
            $cutoffRange = 'Current Period';
        }

        $otherLoan = max(0, (float)($payslip->loan_deduction ?? 0) - ((float)($payslip->sss_loan_deduction ?? 0) + (float)($payslip->pagibig_loan_deduction ?? 0) + (float)($payslip->salary_loan_deduction ?? 0)));

        // Count all rendered line items to dynamically adapt padding for zero-whitespace A5 fit
        $totalLineItems = 2 // Salary and Allowances + Holiday Pay
            + (($payslip->other_earnings ?? 0) > 0 ? 1 : 0)
            + $payslip->customItems->where('type', 'earning')->count()
            + 6 // PhilHealth, HDMF, SSS, Absences, Late, Undertime
            + (($payslip->sss_loan_deduction ?? 0) > 0 ? 1 : 0)
            + (($payslip->pagibig_loan_deduction ?? 0) > 0 ? 1 : 0)
            + (($payslip->salary_loan_deduction ?? 0) > 0 ? 1 : 0)
            + ($otherLoan > 0 ? 1 : 0)
            + (($payslip->other_deductions ?? 0) > 0 ? 1 : 0)
            + $payslip->customItems->where('type', 'deduction')->count();

        if ($totalLineItems <= 8) {
            $linePad = '9px 10px';
            $infoPad = '4.5px 2px';
            $totalPad = '9px 10px';
            $netPad = '10px 12px';
            $hdrPad = '12px 6px';
        } elseif ($totalLineItems <= 10) {
            $linePad = '7px 10px';
            $infoPad = '3.5px 2px';
            $totalPad = '7.5px 10px';
            $netPad = '8px 10px';
            $hdrPad = '10px 6px';
        } elseif ($totalLineItems <= 12) {
            $linePad = '5.5px 10px';
            $infoPad = '3px 2px';
            $totalPad = '6.5px 10px';
            $netPad = '7px 10px';
            $hdrPad = '8px 6px';
        } else {
            $linePad = '4px 8px';
            $infoPad = '2.5px 2px';
            $totalPad = '5px 8px';
            $netPad = '6px 8px';
            $hdrPad = '7px 5px';
        }
    @endphp
    <style>
        @page {
            size: a5 portrait;
            margin: 10px 14px 8px 14px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #000000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Diagonal Confidential Watermark */
        .watermark {
            position: fixed;
            top: 40%;
            left: -20%;
            right: -20%;
            text-align: center;
            font-size: 62px;
            font-weight: 900;
            color: #000000;
            opacity: 0.055;
            text-transform: uppercase;
            letter-spacing: 14px;
            transform: rotate(-32deg);
            z-index: -1000;
            pointer-events: none;
        }

        .main-frame {
            width: 100%;
            border: 2px solid #000000;
            border-collapse: collapse;
        }
        .main-frame > tbody > tr > td,
        .main-frame > thead > tr > th,
        .main-frame > tbody > tr > th {
            border: 1px solid #000000;
        }

        /* Top 3-Box Header */
        .hdr-logo-cell {
            width: 38%;
            text-align: center;
            vertical-align: middle;
            padding: {{ $hdrPad }};
        }
        .logo-img {
            width: 136px;
            height: auto;
            max-width: 95%;
            display: block;
            margin: 0 auto;
        }
        .hdr-title-cell {
            width: 37%;
            text-align: center;
            vertical-align: middle;
            padding: {{ $hdrPad }};
        }
        .hdr-title {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #000000;
        }
        .hdr-period {
            font-size: 9px;
            font-weight: 800;
            margin-top: 3px;
            color: #000000;
        }
        .hdr-confidential-cell {
            width: 25%;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            padding: {{ $hdrPad }};
            color: #000000;
        }

        /* Employee Info Sub-Table */
        .info-cell {
            padding: 8px 10px;
            vertical-align: top;
        }
        .info-inner-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .info-inner-tbl td {
            border: none !important;
            padding: {{ $infoPad }};
            font-size: 9px;
            vertical-align: middle;
        }
        .info-label {
            font-weight: 700;
            color: #000000;
            white-space: nowrap;
        }
        .info-colon {
            width: 8px;
            text-align: center;
            font-weight: 700;
        }
        .info-val {
            font-weight: 600;
            color: #000000;
        }
        .rate-label {
            font-weight: 700;
            white-space: nowrap;
        }
        .rate-colon {
            width: 8px;
            text-align: center;
            font-weight: 700;
        }
        .rate-val {
            text-align: right;
            font-weight: 600;
        }

        /* Description / Earnings / Deduction Ledger Table */
        .col-header th {
            font-size: 9.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 7px 10px;
            background-color: #f9fafb;
            color: #000000;
        }
        .th-desc {
            text-align: left;
            width: 46%;
        }
        .th-earn {
            text-align: center;
            width: 27%;
        }
        .th-ded {
            text-align: center;
            width: 27%;
        }

        .line-item td {
            padding: {{ $linePad }};
            font-size: 9px;
        }
        .desc-text {
            text-align: left;
            font-weight: 500;
            color: #000000;
        }
        .amt-num {
            text-align: right;
            font-weight: 600;
            color: #000000;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        .dash-symbol {
            text-align: center;
            color: #6b7280;
        }
        .blank-cell {
            text-align: right;
        }

        /* TOTAL Row */
        .total-row td {
            padding: {{ $totalPad }};
            font-size: 10.5px;
            font-weight: 900;
            color: #000000;
        }
        .total-shaded {
            background-color: #e5e7eb;
            text-align: right;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        /* Net Pay Block */
        .payment-date-box {
            padding: {{ $netPad }};
            vertical-align: middle;
            font-size: 9px;
            line-height: 1.45;
        }
        .net-pay-block {
            padding: {{ $netPad }};
            vertical-align: top;
        }
        .net-pay-title {
            text-align: center;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .net-pay-highlight {
            background-color: #d1fae5;
            padding: 8px 12px;
            text-align: right;
            font-size: 14.5px;
            font-weight: 900;
            border: 0.5px solid #a7f3d0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        .net-pay-in-words {
            text-align: center;
            font-weight: 800;
            font-size: 8.5px;
            color: #000000;
            line-height: 1.3;
            margin-top: 5px;
        }

        /* Electronic Copy Notice */
        .electronic-notice {
            margin-top: 9px;
            text-align: center;
            font-size: 8px;
            color: #4b5563;
            font-style: italic;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>

    <!-- Diagonal Confidential Watermark -->
    <div class="watermark">CONFIDENTIAL</div>

    <!-- Outer Frame Container -->
    <table class="main-frame">
        <tbody>
            <!-- Row 1: Top 3-Box Header -->
            <tr>
                <td class="hdr-logo-cell">
                    @if($logoData)
                        <img class="logo-img" src="data:image/png;base64,{{ $logoData }}" alt="LASAC Logo">
                    @else
                        <div style="font-weight: 900; font-size: 15px; color: #b91c1c;">LASAC</div>
                        <div style="font-size: 6.5px; font-weight: bold;">LIPA ARCHDIOCESAN SOCIAL ACTION COMMISSION</div>
                    @endif
                </td>
                <td class="hdr-title-cell">
                    <div class="hdr-title">PAY SLIP</div>
                    <div class="hdr-period">Cut Off ({{ $cutoffRange }})</div>
                </td>
                <td class="hdr-confidential-cell">
                    PRIVATE &amp; CONFIDENTIAL
                </td>
            </tr>

            <!-- Row 2: Employee Details & Rates Grid -->
            <tr>
                <td colspan="3" class="info-cell">
                    <table class="info-inner-tbl">
                        <tr>
                            <td style="width: 22%;" class="info-label">Name of Employee</td>
                            <td class="info-colon">:</td>
                            <td style="width: 44%;" class="info-val">{{ $payslip->employee->name }}</td>
                            <td style="width: 16%;" class="rate-label">Per Day</td>
                            <td class="rate-colon">:</td>
                            <td style="width: 15%;" class="rate-val">{{ number_format($payslip->per_day_rate, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Employee Number</td>
                            <td class="info-colon">:</td>
                            <td class="info-val">{{ $payslip->employee->employee_no ?? '-' }}</td>
                            <td class="rate-label">Per Hour</td>
                            <td class="rate-colon">:</td>
                            <td class="rate-val">{{ number_format($payslip->per_hour_rate, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Designation</td>
                            <td class="info-colon">:</td>
                            <td class="info-val">{{ $payslip->employee->position ?? 'Staff' }}</td>
                            <td class="rate-label">Per Minute</td>
                            <td class="rate-colon">:</td>
                            <td class="rate-val">{{ number_format($payslip->per_minute_rate, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Department/Group</td>
                            <td class="info-colon">:</td>
                            <td class="info-val" colspan="4">{{ $payslip->employee->department->name ?? 'General Staff' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Row 3: Description, Earnings, Deduction Header -->
            <tr class="col-header">
                <th class="th-desc">DESCRIPTION</th>
                <th class="th-earn">EARNINGS</th>
                <th class="th-ded">DEDUCTION</th>
            </tr>

            <!-- Line Items: Earnings -->
            <tr class="line-item">
                <td class="desc-text">Salary and Allowances</td>
                <td class="amt-num">{{ number_format($payslip->salary_and_allowances, 2) }}</td>
                <td class="blank-cell"></td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">Holiday Pay</td>
                <td class="{{ $payslip->holiday_pay > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->holiday_pay > 0 ? number_format($payslip->holiday_pay, 2) : '-' }}
                </td>
                <td class="blank-cell"></td>
            </tr>

            @if(($payslip->other_earnings ?? 0) > 0)
            <tr class="line-item">
                <td class="desc-text">Other Salary Additions / Adjustments</td>
                <td class="amt-num">{{ number_format($payslip->other_earnings, 2) }}</td>
                <td class="blank-cell"></td>
            </tr>
            @endif

            @foreach($payslip->customItems->where('type', 'earning') as $cEarn)
            <tr class="line-item">
                <td class="desc-text">{{ $cEarn->label }}</td>
                <td class="amt-num">{{ number_format($cEarn->amount, 2) }}</td>
                <td class="blank-cell"></td>
            </tr>
            @endforeach

            <!-- Line Items: Deductions -->
            <tr class="line-item">
                <td class="desc-text">Phil Health Contribution</td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->ph_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->ph_deduction > 0 ? number_format($payslip->ph_deduction, 2) : '-' }}
                </td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">HDMF Contribution</td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->hdmf_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->hdmf_deduction > 0 ? number_format($payslip->hdmf_deduction, 2) : '-' }}
                </td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">SSS Contribution</td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->sss_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->sss_deduction > 0 ? number_format($payslip->sss_deduction, 2) : '-' }}
                </td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">
                    Absences{{ $payslip->absent_days > 0 ? ' (' . $payslip->absent_days . ' ' . ($payslip->absent_days == 1 ? 'day' : 'days') . ')' : '' }}
                </td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->absences_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->absences_deduction > 0 ? number_format($payslip->absences_deduction, 2) : '-' }}
                </td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">
                    Late{{ $payslip->late_minutes > 0 ? ' (' . $payslip->late_minutes . ' min)' : '' }}
                </td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->late_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->late_deduction > 0 ? number_format($payslip->late_deduction, 2) : '-' }}
                </td>
            </tr>

            <tr class="line-item">
                <td class="desc-text">
                    Undertime{{ $payslip->undertime_minutes > 0 ? ' (' . $payslip->undertime_minutes . ' min)' : '' }}
                </td>
                <td class="blank-cell"></td>
                <td class="{{ $payslip->undertime_deduction > 0 ? 'amt-num' : 'dash-symbol' }}">
                    {{ $payslip->undertime_deduction > 0 ? number_format($payslip->undertime_deduction, 2) : '-' }}
                </td>
            </tr>

            @if(($payslip->sss_loan_deduction ?? 0) > 0)
            <tr class="line-item">
                <td class="desc-text">SSS Loan Deduction</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($payslip->sss_loan_deduction, 2) }}</td>
            </tr>
            @endif

            @if(($payslip->pagibig_loan_deduction ?? 0) > 0)
            <tr class="line-item">
                <td class="desc-text">Pag-IBIG Loan Deduction</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($payslip->pagibig_loan_deduction, 2) }}</td>
            </tr>
            @endif

            @if(($payslip->salary_loan_deduction ?? 0) > 0)
            <tr class="line-item">
                <td class="desc-text">Salary Loan Deduction</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($payslip->salary_loan_deduction, 2) }}</td>
            </tr>
            @endif

            @if($otherLoan > 0)
            <tr class="line-item">
                <td class="desc-text">Other Employee Loans / Advances</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($otherLoan, 2) }}</td>
            </tr>
            @endif

            @if(($payslip->other_deductions ?? 0) > 0)
            <tr class="line-item">
                <td class="desc-text">Other Deductions / Adjustments</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($payslip->other_deductions, 2) }}</td>
            </tr>
            @endif

            @foreach($payslip->customItems->where('type', 'deduction') as $cDed)
            <tr class="line-item">
                <td class="desc-text">{{ $cDed->label }}</td>
                <td class="blank-cell"></td>
                <td class="amt-num">{{ number_format($cDed->amount, 2) }}</td>
            </tr>
            @endforeach

            <!-- TOTAL Row -->
            <tr class="total-row">
                <td class="desc-text" style="font-weight: 900;">TOTAL</td>
                <td class="total-shaded">{{ number_format($payslip->gross_earnings, 2) }}</td>
                <td class="total-shaded">{{ number_format($payslip->total_deductions, 2) }}</td>
            </tr>

            <!-- NET PAY Row -->
            <tr>
                <td class="payment-date-box">
                    <div style="color: #000000; font-weight: 600;">Date of Payment:</div>
                    <div style="font-weight: 900; font-size: 9.5px; margin-top: 3px;">{{ $paymentDate }}</div>
                </td>
                <td colspan="2" class="net-pay-block">
                    <div class="net-pay-title">NET PAY</div>
                    <div class="net-pay-highlight">{{ number_format($payslip->net_pay, 2) }}</div>
                    <div class="net-pay-in-words">{{ $amountInWords }}</div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Electronic Copy Notice Below Outer Border -->
    <div class="electronic-notice">
        This is a system-generated official electronic copy of the pay slip. No signature is required.
    </div>

</body>
</html>
