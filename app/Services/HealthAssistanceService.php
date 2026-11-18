<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\HealthAssistanceClaim;
use App\Models\HealthAssistanceFund;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HealthAssistanceService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Enroll an employee into the Health Assistance Fund (HMO).
     */
    public function enroll(Employee $employee, array $data, ?User $creator = null): HealthAssistanceFund
    {
        return DB::transaction(function () use ($employee, $data, $creator) {
            $startDate = !empty($data['start_date'])
                ? Carbon::parse($data['start_date'])->startOfDay()
                : now('Asia/Manila')->startOfDay();

            $validityDays = (int) ($data['validity_days'] ?? 365);
            if ($validityDays <= 0) {
                $validityDays = 365;
            }

            // Expiration date defaults to exact 365 days from start date
            $endDate = !empty($data['end_date'])
                ? Carbon::parse($data['end_date'])->startOfDay()
                : $startDate->copy()->addDays($validityDays);

            $contributionAmount = (float) ($data['contribution_amount'] ?? 0);
            $coverageAmount = isset($data['coverage_amount']) && $data['coverage_amount'] !== ''
                ? (float) $data['coverage_amount']
                : ($contributionAmount > 0 ? $contributionAmount : 15000.00);

            $year = $startDate->year;
            $memberNumber = !empty($data['member_number'])
                ? trim($data['member_number'])
                : $this->generateMemberNumber($year);

            $receiptPath = null;
            if (isset($data['receipt_file']) && $data['receipt_file']) {
                $receiptPath = $data['receipt_file']->store('haf-receipts', 'public');
            }

            $fund = HealthAssistanceFund::create([
                'employee_id'         => $employee->id,
                'member_number'       => $memberNumber,
                'plan_year'           => $data['plan_year'] ?? "{$year}-" . ($year + 1),
                'contribution_amount' => $contributionAmount,
                'coverage_amount'     => $coverageAmount,
                'amount_used'         => 0.00,
                'remaining_balance'   => $coverageAmount,
                'start_date'          => $startDate->toDateString(),
                'end_date'            => $endDate->toDateString(),
                'validity_days'       => $validityDays,
                'payment_method'      => $data['payment_method'] ?? 'voluntary_contribution',
                'receipt_no'          => $data['receipt_no'] ?? null,
                'receipt_file_path'   => $receiptPath,
                'status'              => 'active',
                'notes'               => $data['notes'] ?? null,
                'created_by'          => $creator?->id ?: auth()->id(),
            ]);

            // Notify staff of enrollment / coverage
            try {
                $this->notificationService->notifyHealthAssistanceEnrolled($fund);
            } catch (\Throwable $e) {
                Log::error("Failed to notify staff of HAF enrollment: " . $e->getMessage());
            }

            return $fund;
        });
    }

    /**
     * Renew an existing member's Health Assistance Fund for another 365 days.
     */
    public function renew(HealthAssistanceFund $previousFund, array $data, ?User $creator = null): HealthAssistanceFund
    {
        return DB::transaction(function () use ($previousFund, $data, $creator) {
            $employee = $previousFund->employee;

            // Start date defaults to the day after previous end date, or today if already lapsed
            $defaultStart = $previousFund->end_date && $previousFund->end_date->isFuture()
                ? $previousFund->end_date->copy()->addDay()
                : now('Asia/Manila')->startOfDay();

            $startDate = !empty($data['start_date'])
                ? Carbon::parse($data['start_date'])->startOfDay()
                : $defaultStart;

            $validityDays = (int) ($data['validity_days'] ?? 365);
            $endDate = !empty($data['end_date'])
                ? Carbon::parse($data['end_date'])->startOfDay()
                : $startDate->copy()->addDays($validityDays);

            $contributionAmount = isset($data['contribution_amount'])
                ? (float) $data['contribution_amount']
                : (float) $previousFund->contribution_amount;

            $coverageAmount = isset($data['coverage_amount']) && $data['coverage_amount'] !== ''
                ? (float) $data['coverage_amount']
                : (float) $previousFund->coverage_amount;

            $receiptPath = null;
            if (isset($data['receipt_file']) && $data['receipt_file']) {
                $receiptPath = $data['receipt_file']->store('haf-receipts', 'public');
            }

            $year = $startDate->year;

            $newFund = HealthAssistanceFund::create([
                'employee_id'         => $employee->id,
                'member_number'       => $previousFund->member_number ?: $this->generateMemberNumber($year),
                'plan_year'           => $data['plan_year'] ?? "{$year}-" . ($year + 1),
                'contribution_amount' => $contributionAmount,
                'coverage_amount'     => $coverageAmount,
                'amount_used'         => 0.00,
                'remaining_balance'   => $coverageAmount,
                'start_date'          => $startDate->toDateString(),
                'end_date'            => $endDate->toDateString(),
                'validity_days'       => $validityDays,
                'payment_method'      => $data['payment_method'] ?? $previousFund->payment_method,
                'receipt_no'          => $data['receipt_no'] ?? null,
                'receipt_file_path'   => $receiptPath,
                'status'              => 'active',
                'notes'               => $data['notes'] ?? 'Renewed voluntary membership.',
                'created_by'          => $creator?->id ?: auth()->id(),
            ]);

            try {
                $this->notificationService->notifyHealthAssistanceEnrolled($newFund);
            } catch (\Throwable $e) {
                Log::error("Failed to notify staff of HAF renewal: " . $e->getMessage());
            }

            return $newFund;
        });
    }

    /**
     * Record a health assistance claim / utilization against an active fund.
     */
    public function recordClaim(HealthAssistanceFund $fund, array $data, ?User $processor = null): HealthAssistanceClaim
    {
        return DB::transaction(function () use ($fund, $data, $processor) {
            $claimDate = !empty($data['claim_date'])
                ? Carbon::parse($data['claim_date'])->startOfDay()
                : now('Asia/Manila')->startOfDay();

            $amountRequested = (float) ($data['amount_requested'] ?? 0);
            $amountApproved = isset($data['amount_approved'])
                ? (float) $data['amount_approved']
                : $amountRequested;

            $status = $data['status'] ?? 'approved';

            $receiptPath = null;
            if (isset($data['receipt_file']) && $data['receipt_file']) {
                $receiptPath = $data['receipt_file']->store('haf-claims', 'public');
            }

            $claimNo = $this->generateClaimNumber($claimDate->year);

            $claim = HealthAssistanceClaim::create([
                'health_assistance_fund_id' => $fund->id,
                'claim_no'                  => $claimNo,
                'claim_date'                => $claimDate->toDateString(),
                'claim_type'                => $data['claim_type'] ?? 'outpatient',
                'healthcare_provider'       => $data['healthcare_provider'] ?? null,
                'diagnosis_reason'          => $data['diagnosis_reason'] ?? 'Medical assistance utilization',
                'amount_requested'          => $amountRequested,
                'amount_approved'           => $amountApproved,
                'status'                    => $status,
                'receipt_file_path'         => $receiptPath,
                'processed_by'              => $processor?->id ?: auth()->id(),
                'remarks'                   => $data['remarks'] ?? null,
            ]);

            // Recompute balance
            $fund->recomputeBalance();

            return $claim;
        });
    }

    /**
     * Check all HAF memberships and dispatch the 1-month (30-day) expiration notice to staff.
     */
    public function checkAndDispatchExpirationNotices(): array
    {
        $today = now('Asia/Manila')->startOfDay();
        $thirtyDaysOut = $today->copy()->addDays(30)->endOfDay();

        // Target: active or expiring soon funds that end within 30 days
        $expiringFunds = HealthAssistanceFund::with(['employee.user'])
            ->where('status', '!=', 'cancelled')
            ->where('end_date', '>=', $today->toDateString())
            ->where('end_date', '<=', $thirtyDaysOut->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('expiration_notified_at')
                  ->orWhere('expiration_notified_at', '<', $today->copy()->subDays(30));
            })
            ->get();

        $dispatched = 0;
        $failed = 0;

        foreach ($expiringFunds as $fund) {
            try {
                $notified = $this->notificationService->notifyHealthAssistanceExpiring($fund);
                $fund->update(['expiration_notified_at' => now('Asia/Manila')]);
                $dispatched++;
            } catch (\Throwable $e) {
                Log::error("Failed to send 30-day HAF expiration notice for fund #{$fund->id}: " . $e->getMessage());
                $failed++;
            }
        }

        return [
            'total_expiring' => $expiringFunds->count(),
            'dispatched'     => $dispatched,
            'failed'         => $failed,
        ];
    }

    /**
     * Manually send the 30-day expiration notice to a member right now.
     */
    public function sendManualExpirationNotice(HealthAssistanceFund $fund): bool
    {
        try {
            $this->notificationService->notifyHealthAssistanceExpiring($fund);
            $fund->update(['expiration_notified_at' => now('Asia/Manila')]);
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to manually dispatch HAF expiration notice for fund #{$fund->id}: " . $e->getMessage());
            return false;
        }
    }

    protected function generateMemberNumber(int $year): string
    {
        $count = HealthAssistanceFund::whereYear('start_date', $year)->count() + 1;
        return sprintf('HAF-%d-%04d', $year, $count);
    }

    protected function generateClaimNumber(int $year): string
    {
        $count = HealthAssistanceClaim::whereYear('claim_date', $year)->count() + 1;
        return sprintf('HAC-%d-%04d', $year, $count);
    }
}
