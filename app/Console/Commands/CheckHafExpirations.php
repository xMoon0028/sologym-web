<?php

namespace App\Console\Commands;

use App\Services\HealthAssistanceService;
use Illuminate\Console\Command;

class CheckHafExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'haf:check-expirations {--dry-run : Check expiring memberships without dispatching notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Health Assistance Fund (HAF) memberships expiring within 30 days and dispatch notifications to staff';

    /**
     * Execute the console command.
     */
    public function handle(HealthAssistanceService $hafService): int
    {
        $this->info('Scanning Health Assistance Fund (HMO) records for memberships expiring within 30 days...');

        if ($this->option('dry-run')) {
            $today = now('Asia/Manila')->startOfDay();
            $thirtyDaysOut = $today->copy()->addDays(30)->endOfDay();

            $expiring = \App\Models\HealthAssistanceFund::with('employee')
                ->where('status', '!=', 'cancelled')
                ->where('end_date', '>=', $today->toDateString())
                ->where('end_date', '<=', $thirtyDaysOut->toDateString())
                ->get();

            $this->info("Dry run complete: Found {$expiring->count()} memberships expiring within 30 days.");
            foreach ($expiring as $fund) {
                $days = $fund->days_remaining;
                $this->line(" - {$fund->employee->name} ({$fund->member_number}): expires {$fund->end_date->format('Y-m-d')} ({$days} days left)");
            }
            return Command::SUCCESS;
        }

        $results = $hafService->checkAndDispatchExpirationNotices();

        $this->info("HAF Expiration Check completed.");
        $this->info(" • Total expiring within 30 days: {$results['total_expiring']}");
        $this->info(" • Notifications dispatched: {$results['dispatched']}");
        if ($results['failed'] > 0) {
            $this->warn(" • Failed notifications: {$results['failed']}");
        }

        return Command::SUCCESS;
    }
}
