<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LoanPayment;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Create a new employee loan with balance initialization and notification.
     */
    public function createLoan(array $data, ?int $userId = null): EmployeeLoan
    {
        return DB::transaction(function () use ($data, $userId) {
            $principal = (float) ($data['principal_amount'] ?? 0);
            $interest = (float) ($data['interest_amount'] ?? 0);
            $totalAmount = (float) ($data['total_amount'] ?? ($principal + $interest));

            // Default amortization calculations
            $monthlyAmort = (float) ($data['monthly_amortization'] ?? 0);
            $cutoffAmort = (float) ($data['amortization_per_cutoff'] ?? 0);

            if ($cutoffAmort <= 0 && $monthlyAmort > 0) {
                $cutoffAmort = round($monthlyAmort / 2, 2);
            } elseif ($monthlyAmort <= 0 && $cutoffAmort > 0) {
                $monthlyAmort = round($cutoffAmort * 2, 2);
            }

            $loan = EmployeeLoan::create([
                'employee_id'            => $data['employee_id'],
                'loan_type'              => $data['loan_type'],
                'loan_name'              => $data['loan_name'],
                'reference_no'           => $data['reference_no'] ?? null,
                'principal_amount'       => $principal,
                'interest_amount'        => $interest,
                'total_amount'           => $totalAmount,
                'monthly_amortization'   => $monthlyAmort,
                'amortization_per_cutoff'=> $cutoffAmort,
                'deduction_schedule'     => $data['deduction_schedule'] ?? 'both',
                'amount_paid'            => 0.0,
                'remaining_balance'      => $totalAmount,
                'start_date'             => $data['start_date'],
                'end_date'               => $data['end_date'] ?? null,
                'status'                 => $data['status'] ?? 'active',
                'notes'                  => $data['notes'] ?? null,
                'created_by'             => $userId,
            ]);

            $this->notifyLoanCreated($loan);

            return $loan;
        });
    }

    /**
     * Update an existing loan.
     */
    public function updateLoan(EmployeeLoan $loan, array $data): EmployeeLoan
    {
        return DB::transaction(function () use ($loan, $data) {
            $principal = (float) ($data['principal_amount'] ?? $loan->principal_amount);
            $interest = (float) ($data['interest_amount'] ?? $loan->interest_amount);
            $totalAmount = (float) ($data['total_amount'] ?? ($principal + $interest));

            $monthlyAmort = (float) ($data['monthly_amortization'] ?? $loan->monthly_amortization);
            $cutoffAmort = (float) ($data['amortization_per_cutoff'] ?? $loan->amortization_per_cutoff);

            if ($cutoffAmort <= 0 && $monthlyAmort > 0) {
                $cutoffAmort = round($monthlyAmort / 2, 2);
            } elseif ($monthlyAmort <= 0 && $cutoffAmort > 0) {
                $monthlyAmort = round($cutoffAmort * 2, 2);
            }

            $loan->update([
                'loan_type'              => $data['loan_type'] ?? $loan->loan_type,
                'loan_name'              => $data['loan_name'] ?? $loan->loan_name,
                'reference_no'           => $data['reference_no'] ?? $loan->reference_no,
                'principal_amount'       => $principal,
                'interest_amount'        => $interest,
                'total_amount'           => $totalAmount,
                'monthly_amortization'   => $monthlyAmort,
                'amortization_per_cutoff'=> $cutoffAmort,
                'deduction_schedule'     => $data['deduction_schedule'] ?? $loan->deduction_schedule,
                'start_date'             => $data['start_date'] ?? $loan->start_date,
                'end_date'               => $data['end_date'] ?? $loan->end_date,
                'status'                 => $data['status'] ?? $loan->status,
                'notes'                  => $data['notes'] ?? $loan->notes,
            ]);

            $loan->recomputeBalances();

            return $loan;
        });
    }

    /**
     * Get active scheduled loan amortizations for an employee in a given period & cutoff.
     */
    public function getScheduledDeductionsForPeriod(Employee $employee, PayrollPeriod $period, string $cutoffType): array
    {
        $activeLoans = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->where('remaining_balance', '>', 0)
            ->where(function ($q) use ($period) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $period->end_date);
            })
            ->where(function ($q) use ($cutoffType) {
                $q->where('deduction_schedule', 'both')
                  ->orWhere('deduction_schedule', $cutoffType);
            })
            ->get();

        $items = [];
        $totalDeduction = 0.0;

        foreach ($activeLoans as $loan) {
            $deduction = min((float) $loan->amortization_per_cutoff, (float) $loan->remaining_balance);
            if ($deduction > 0) {
                $items[] = [
                    'loan'      => $loan,
                    'amount'    => round($deduction, 2),
                    'loan_name' => $loan->loan_name,
                    'loan_type' => $loan->loan_type,
                ];
                $totalDeduction += $deduction;
            }
        }

        return [
            'total_deduction' => round($totalDeduction, 2),
            'items'           => $items,
        ];
    }

    /**
     * Apply and record loan payments for all payslips in a finalized or released payroll period.
     */
    public function applyPayrollDeductionsForPeriod(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            $cutoffType = $period->cutoff_type ?? 'end_month';

            // Find payslips in this period that have loan_deduction > 0
            $payslips = $period->payslips()->where('loan_deduction', '>', 0)->get();

            foreach ($payslips as $payslip) {
                $employee = $payslip->employee;
                if (!$employee) {
                    continue;
                }

                // Check if payments have already been posted for this exact payslip
                $alreadyPosted = LoanPayment::where('payslip_id', $payslip->id)->exists();
                if ($alreadyPosted) {
                    continue;
                }

                $scheduled = $this->getScheduledDeductionsForPeriod($employee, $period, $cutoffType);
                $remainingToAllocate = (float) $payslip->loan_deduction;

                foreach ($scheduled['items'] as $item) {
                    /** @var EmployeeLoan $loan */
                    $loan = $item['loan'];
                    $scheduledAmt = (float) $item['amount'];

                    $deductAmount = min($scheduledAmt, $remainingToAllocate);
                    if ($deductAmount <= 0) {
                        break;
                    }

                    $payment = LoanPayment::create([
                        'employee_loan_id' => $loan->id,
                        'payslip_id'       => $payslip->id,
                        'payroll_period_id'=> $period->id,
                        'payment_date'     => $period->date_of_payment ?? now()->toDateString(),
                        'amount'           => $deductAmount,
                        'payment_type'     => 'payroll_deduction',
                        'reference_no'     => "Payroll: {$period->name}",
                        'remarks'          => "Deducted from semi-monthly payslip ({$cutoffType})",
                        'recorded_by'      => auth()->id(),
                    ]);

                    $loan->recomputeBalances();
                    $remainingToAllocate -= $deductAmount;

                    $this->notifyPaymentPosted($payment);

                    if ($loan->remaining_balance <= 0) {
                        $this->notifyLoanCompleted($loan);
                    }
                }
            }
        });
    }

    /**
     * Record a manual payment or direct adjustment outside payroll.
     */
    public function recordManualPayment(
        EmployeeLoan $loan,
        float $amount,
        string $paymentType = 'manual_payment',
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?int $recordedBy = null
    ): LoanPayment {
        return DB::transaction(function () use ($loan, $amount, $paymentType, $referenceNo, $remarks, $recordedBy) {
            $payment = LoanPayment::create([
                'employee_loan_id' => $loan->id,
                'payslip_id'       => null,
                'payroll_period_id'=> null,
                'payment_date'     => now()->toDateString(),
                'amount'           => $amount,
                'payment_type'     => $paymentType,
                'reference_no'     => $referenceNo,
                'remarks'          => $remarks,
                'recorded_by'      => $recordedBy ?? auth()->id(),
            ]);

            $loan->recomputeBalances();
            $this->notifyPaymentPosted($payment);

            if ($loan->remaining_balance <= 0) {
                $this->notifyLoanCompleted($loan);
            }

            return $payment;
        });
    }

    /**
     * Notify employee about a new loan record.
     */
    public function notifyLoanCreated(EmployeeLoan $loan): void
    {
        $employee = $loan->employee;
        $user = $employee?->user;
        if (!$user) {
            return;
        }

        $formattedTotal = number_format($loan->total_amount, 2);
        $formattedAmort = number_format($loan->amortization_per_cutoff, 2);

        $this->notificationService->notifyUser(
            $user,
            'loan_created',
            "💳 New Loan Recorded: {$loan->loan_name}",
            "A loan of ₱{$formattedTotal} has been registered with scheduled cutoff deduction of ₱{$formattedAmort}.",
            route('portal.loans.index')
        );
    }

    /**
     * Notify employee when a payment is posted.
     */
    public function notifyPaymentPosted(LoanPayment $payment): void
    {
        $loan = $payment->loan;
        $employee = $loan?->employee;
        $user = $employee?->user;
        if (!$user) {
            return;
        }

        $formattedAmt = number_format($payment->amount, 2);
        $formattedBal = number_format($loan->remaining_balance, 2);

        $this->notificationService->notifyUser(
            $user,
            'loan_payment_posted',
            "✅ Loan Payment Posted: {$loan->loan_name}",
            "Payment of ₱{$formattedAmt} applied ({$payment->payment_type}). Remaining balance is now ₱{$formattedBal}.",
            route('portal.loans.index')
        );
    }

    /**
     * Notify employee when a loan is fully settled.
     */
    public function notifyLoanCompleted(EmployeeLoan $loan): void
    {
        $employee = $loan->employee;
        $user = $employee?->user;
        if (!$user) {
            return;
        }

        $this->notificationService->notifyUser(
            $user,
            'loan_completed',
            "🎉 Loan Fully Paid: {$loan->loan_name}",
            "Congratulations! Your {$loan->loan_name} has been fully settled. No further payroll deductions will be made.",
            route('portal.loans.index')
        );
    }
}
