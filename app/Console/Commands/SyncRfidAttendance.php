<?php

namespace App\Console\Commands;

use App\Services\RfidAttendanceSyncService;
use Illuminate\Console\Command;

class SyncRfidAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-rfid 
                            {--all : Synchronize all attendance records across all months in the RFID database}
                            {--days= : Number of past days to sync including today (default 3)}
                            {--month= : Sync a specific month (e.g. 2026-09 or 2026-10)}
                            {--from= : Start date in YYYY-MM-DD format}
                            {--to= : End date in YYYY-MM-DD format}
                            {--employee= : Optional HRIS Employee ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize attendance punch logs from the external RFID attendance database';

    /**
     * Execute the console command.
     */
    public function handle(RfidAttendanceSyncService $syncService): int
    {
        $this->info('Checking RFID database connection...');

        if (!$syncService->isConnected()) {
            $this->error('Failed to connect to the RFID attendance database. Please verify your DB_RFID credentials.');
            return Command::FAILURE;
        }

        $employeeId = $this->option('employee') ? (int) $this->option('employee') : null;

        if ($this->option('all')) {
            $this->info('Synchronizing all available attendance logs across all months from the RFID database...');
            $synced = $syncService->syncAll($employeeId);
            $this->info("Successfully synchronized {$synced} attendance records across all months.");
            return Command::SUCCESS;
        }

        if ($this->option('month')) {
            $monthStr = $this->option('month');
            try {
                $mCarbon   = \Carbon\Carbon::parse($monthStr);
                $startDate = $mCarbon->copy()->startOfMonth()->toDateString();
                $endDate   = $mCarbon->copy()->endOfMonth()->toDateString();
                if ($mCarbon->isCurrentMonth()) {
                    $endDate = now()->toDateString();
                }
            } catch (\Throwable $e) {
                $this->error("Invalid month format '{$monthStr}'. Please use YYYY-MM (e.g. 2026-09).");
                return Command::FAILURE;
            }
        } elseif ($this->option('from') || $this->option('to')) {
            $startDate = $this->option('from') ?: now()->startOfMonth()->toDateString();
            $endDate   = $this->option('to') ?: now()->toDateString();
        } else {
            $days = $this->option('days') ? (int) $this->option('days') : 3;
            if ($days < 1) $days = 1;
            $startDate = now()->subDays($days - 1)->toDateString();
            $endDate   = now()->toDateString();
        }

        $this->info("Syncing attendance logs from {$startDate} to {$endDate}...");

        $synced = $syncService->syncDateRange($startDate, $endDate, $employeeId);

        $this->info("Successfully synchronized {$synced} attendance records from the RFID database.");

        return Command::SUCCESS;
    }
}
