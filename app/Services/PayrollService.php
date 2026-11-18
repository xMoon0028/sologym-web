<?php
namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayrollPeriod;

class PayrollService
{
    /**
     * Auto-populate a payslip draft for one employee in a payroll period.
     * Pulls compensation from HRIS and attendance from the period date range.
     */
    public function generateDraft(Employee $employee, PayrollPeriod $period): Payslip
    {
        $compensation = $employee->compensation;
        $existing     = Payslip::where('employee_id', $employee->id)->where('period_id', $period->id)->first();

        if ($compensation && $compensation->total_compensation > 0) {
            $perDay           = $compensation->per_day_rate;
            $perHour          = $compensation->per_hour_rate;
            $perMinute        = $compensation->per_minute_rate;
            $semiMonthlyGross = round($compensation->total_compensation / 2, 2);
        } elseif ($existing && $existing->salary_and_allowances > 0) {
            $semiMonthlyGross = (float) $existing->salary_and_allowances;
            $monthly          = $semiMonthlyGross * 2;
            $perDay           = round(($monthly * 12) / 261, 4);
            $perHour          = round($perDay / 8, 4);
            $perMinute        = round($perHour / 60, 4);
        } else {
            $perDay = 0; $perHour = 0; $perMinute = 0; $semiMonthlyGross = 0;
        }

        // Get attendance in the payroll period
        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        $absentFull   = $records->where('status', 'absent')->count();
        $halfDayCount = $records->where('status', 'half_day')->count();
        $absentDays   = $absentFull + (0.5 * $halfDayCount);

        $obDays       = $records->where('status', 'official_business')->count();
        $leaveDays    = $records->where('status', 'leave')->count();

        $lateMinutes  = (int) $records->sum('late_minutes');
        $undertimeMin = (int) $records->sum('undertime_minutes');

        $absenceDeduction   = round($absentDays * $perDay, 2);
        $lateDeduction      = round($lateMinutes * $perMinute, 2);
        $undertimeDeduction = round($undertimeMin * $perMinute, 2);

        $cutoffType = $period->cutoff_type ?? \App\Models\PayrollPeriod::detectCutoffType($period->start_date, $period->end_date);

        if ($cutoffType === 'mid_month') {
            // Mid-month (21st–5th): Deductions are PhilHealth, HDMF (Pag-IBIG), absences, late, undertime (SSS = 0)
            $sssDeduction = 0.0;
            $phDeduction  = $compensation ? (float) $compensation->ph_deduction : 0.0;
            $piDeduction  = $compensation ? (float) $compensation->pi_deduction : 0.0;
        } else {
            // End-month (6th–20th): Deductions are SSS, absences, late, undertime (PhilHealth = 0, HDMF = 0)
            $sssDeduction = $compensation ? (float) $compensation->sss_deduction : 0.0;
            $phDeduction  = 0.0;
            $piDeduction  = 0.0;
        }

        // Compute loan deductions (specifically SSS, Pag-IBIG, and Salary loans)
        $existing = Payslip::where('employee_id', $employee->id)->where('period_id', $period->id)->first();

        $sssLoanDeduction     = $existing ? (float) ($existing->sss_loan_deduction ?? 0) : 0.0;
        $pagibigLoanDeduction = $existing ? (float) ($existing->pagibig_loan_deduction ?? 0) : 0.0;
        $salaryLoanDeduction  = $existing ? (float) ($existing->salary_loan_deduction ?? 0) : 0.0;

        if (!$existing || ($sssLoanDeduction <= 0 && $pagibigLoanDeduction <= 0 && $salaryLoanDeduction <= 0)) {
            $scheduledLoans = app(LoanService::class)->getScheduledDeductionsForPeriod($employee, $period, $cutoffType);
            foreach ($scheduledLoans['items'] as $item) {
                $loanType = strtolower($item['loan_type'] ?? '');
                $itemAmt = (float) ($item['amount'] ?? 0);
                if ($loanType === 'sss') {
                    $sssLoanDeduction += $itemAmt;
                } elseif ($loanType === 'pagibig' || $loanType === 'hdmf') {
                    $pagibigLoanDeduction += $itemAmt;
                } elseif ($loanType === 'salary') {
                    $salaryLoanDeduction += $itemAmt;
                }
            }
        }

        $loanDeduction = round($sssLoanDeduction + $pagibigLoanDeduction + $salaryLoanDeduction, 2);
        if ($existing && (float) ($existing->loan_deduction ?? 0) > $loanDeduction) {
            $loanDeduction = (float) $existing->loan_deduction;
        }

        $otherEarnings   = $existing ? (float) $existing->other_earnings : 0;
        $otherDeductions = $existing ? (float) $existing->other_deductions : 0;
        $customEarnings  = $existing ? (float) $existing->customItems()->where('type', 'earning')->sum('amount') : 0;
        $customDeducts   = $existing ? (float) $existing->customItems()->where('type', 'deduction')->sum('amount') : 0;

        // Holidays are subject to time credits and not computed into cash payroll (holiday_pay = 0)
        $grossEarnings   = round($semiMonthlyGross + $otherEarnings + $customEarnings, 2);
        $totalDeductions = round(
            $sssDeduction + $phDeduction + $piDeduction
            + $absenceDeduction + $lateDeduction + $undertimeDeduction
            + $loanDeduction + $otherDeductions + $customDeducts,
            2
        );
        $netPay = round($grossEarnings - $totalDeductions, 2);

        return Payslip::updateOrCreate(
            ['employee_id' => $employee->id, 'period_id' => $period->id],
            [
                'per_day_rate'           => $perDay,
                'per_hour_rate'          => $perHour,
                'per_minute_rate'        => $perMinute,
                'salary_and_allowances'  => $semiMonthlyGross,
                'holiday_pay'            => 0,
                'other_earnings'         => $otherEarnings,
                'sss_deduction'          => $sssDeduction,
                'ph_deduction'           => $phDeduction,
                'hdmf_deduction'         => $piDeduction,
                'absent_days'            => $absentDays,
                'absences_deduction'     => $absenceDeduction,
                'late_minutes'           => $lateMinutes,
                'late_deduction'         => $lateDeduction,
                'undertime_minutes'      => $undertimeMin,
                'undertime_deduction'    => $undertimeDeduction,
                'loan_deduction'         => $loanDeduction,
                'sss_loan_deduction'     => $sssLoanDeduction,
                'pagibig_loan_deduction' => $pagibigLoanDeduction,
                'salary_loan_deduction'  => $salaryLoanDeduction,
                'other_deductions'       => $otherDeductions,
                'official_business_days' => $obDays,
                'leave_days'             => $leaveDays,
                'gross_earnings'         => $grossEarnings,
                'total_deductions'       => $totalDeductions,
                'net_pay'                => $netPay,
                'is_finalized'           => $existing ? $existing->is_finalized : false,
            ]
        );
    }

    /**
     * Convert a number to Philippine peso words.
     * e.g. 7643.89 → "Seven Thousand Six Hundred Forty Three & 89/100"
     */
    public function amountInWords(float $amount): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $intPart  = (int) floor($amount);
        $centavos = round(($amount - $intPart) * 100);

        $words = preg_replace('/\s+/', ' ', trim($this->convertInteger($intPart, $ones, $tens)));
        return $words . ' & ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100';
    }

    private function convertInteger(int $n, array $ones, array $tens): string
    {
        if ($n === 0) return 'Zero';
        if ($n < 0)  return 'Negative ' . $this->convertInteger(-$n, $ones, $tens);

        $words = '';
        if ($n >= 1000000) {
            $words .= $this->convertInteger((int) ($n / 1000000), $ones, $tens) . ' Million ';
            $n %= 1000000;
        }
        if ($n >= 1000) {
            $words .= $this->convertInteger((int) ($n / 1000), $ones, $tens) . ' Thousand ';
            $n %= 1000;
        }
        if ($n >= 100) {
            $words .= $ones[(int) ($n / 100)] . ' Hundred ';
            $n %= 100;
        }
        if ($n >= 20) {
            $words .= $tens[(int) ($n / 10)] . ' ';
            $n %= 10;
        }
        if ($n > 0) {
            $words .= $ones[$n] . ' ';
        }
        return $words;
    }

    /**
     * Retrieves payslips sequenced per office, with the Section Head first,
     * followed by organizational hierarchy, salary grade, and employee name.
     */
    public function getSortedPayslipsForPeriod(PayrollPeriod $period): \Illuminate\Support\Collection
    {
        return $period->payslips()
            ->with(['employee.department', 'customItems'])
            ->get()
            ->sort(function (Payslip $a, Payslip $b) {
                $empA = $a->employee;
                $empB = $b->employee;

                if (!$empA || !$empB) {
                    return $empA ? -1 : 1;
                }

                // 1. Sequence per Office / Department
                $deptA = $empA->department->name ?? 'Z_General / Unassigned';
                $deptB = $empB->department->name ?? 'Z_General / Unassigned';

                // Prioritize Executive Office if present
                $isExecA = str_contains(strtolower($deptA), 'executive') ? 0 : 1;
                $isExecB = str_contains(strtolower($deptB), 'executive') ? 0 : 1;
                if ($isExecA !== $isExecB) {
                    return $isExecA <=> $isExecB;
                }

                $deptDiff = strcasecmp($deptA, $deptB);
                if ($deptDiff !== 0) {
                    return $deptDiff;
                }

                // 2. Section Head FIRST within the office
                $isHeadA = ($empA->approval_role === 'section_head') ? 0 : 1;
                $isHeadB = ($empB->approval_role === 'section_head') ? 0 : 1;
                if ($isHeadA !== $isHeadB) {
                    return $isHeadA <=> $isHeadB;
                }

                // 3. Organizational hierarchy rank (lower number = higher leadership rank)
                $rankA = $empA->hierarchy_rank;
                $rankB = $empB->hierarchy_rank;
                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                // 4. Salary Grade level descending (higher salary grade first)
                $sgA = $empA->salary_grade_level;
                $sgB = $empB->salary_grade_level;
                if ($sgA !== $sgB) {
                    return $sgB <=> $sgA;
                }

                // 5. Employee Name ascending
                return strcasecmp($empA->name, $empB->name);
            })
            ->values();
    }

    /**
     * Refreshes rates and attendance valuations (absences, late, undertime) for all payslips
     * in a period according to the statutory 261-day factor.
     */
    public function refreshRatesAndDeductionsForPeriod(PayrollPeriod $period): void
    {
        $payslips = $period->payslips()->with(['employee.compensation', 'customItems'])->get();
        foreach ($payslips as $payslip) {
            $employee = $payslip->employee;
            $comp     = $employee?->compensation;

            if ($comp && $comp->total_compensation > 0) {
                $perDay    = $comp->per_day_rate;
                $perHour   = $comp->per_hour_rate;
                $perMinute = $comp->per_minute_rate;
                if ($payslip->salary_and_allowances <= 0) {
                    $payslip->salary_and_allowances = round($comp->total_compensation / 2, 2);
                }
            } elseif ($payslip->salary_and_allowances > 0 && (empty($payslip->getRawOriginal('per_day_rate')) || $payslip->getRawOriginal('per_day_rate') == 0)) {
                $monthly   = $payslip->salary_and_allowances * 2;
                $perDay    = round(($monthly * 12) / 261, 4);
                $perHour   = round($perDay / 8, 4);
                $perMinute = round($perHour / 60, 4);
            } else {
                continue;
            }

            $expectedAbsences  = $payslip->absent_days > 0 ? round($payslip->absent_days * $perDay, 2) : 0;
            $expectedLate      = $payslip->late_minutes > 0 ? round($payslip->late_minutes * $perMinute, 2) : 0;
            $expectedUndertime = $payslip->undertime_minutes > 0 ? round($payslip->undertime_minutes * $perMinute, 2) : 0;

            // Only perform write/recompute if values need updating
            $needsUpdate = abs((float) $payslip->per_day_rate - $perDay) > 0.0001
                || abs((float) $payslip->per_hour_rate - $perHour) > 0.0001
                || abs((float) $payslip->per_minute_rate - $perMinute) > 0.0001
                || abs((float) $payslip->absences_deduction - $expectedAbsences) > 0.01
                || abs((float) $payslip->late_deduction - $expectedLate) > 0.01
                || abs((float) $payslip->undertime_deduction - $expectedUndertime) > 0.01;

            if ($needsUpdate) {
                $payslip->per_day_rate        = $perDay;
                $payslip->per_hour_rate       = $perHour;
                $payslip->per_minute_rate     = $perMinute;
                $payslip->absences_deduction  = $expectedAbsences;
                $payslip->late_deduction      = $expectedLate;
                $payslip->undertime_deduction = $expectedUndertime;
                $payslip->recompute();
            }
        }
    }
}
