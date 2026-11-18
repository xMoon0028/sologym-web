<?php

use App\Services\RfidAttendanceSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:sync-rfid {--days=3 : Number of past days to sync including today} {--employee= : Optional HRIS Employee ID}', function (RfidAttendanceSyncService $syncService) {
    $this->info('Checking RFID database connection...');

    if (!$syncService->isConnected()) {
        $this->error('Failed to connect to the RFID attendance database. Please verify your DB_RFID credentials in .env.');
        return 1;
    }

    $days = (int) $this->option('days');
    if ($days < 1) $days = 1;

    $startDate = now()->subDays($days - 1)->toDateString();
    $endDate   = now()->toDateString();
    $employeeId = $this->option('employee') ? (int) $this->option('employee') : null;

    $this->info("Syncing attendance logs from {$startDate} to {$endDate}...");

    $synced = $syncService->syncDateRange($startDate, $endDate, $employeeId);

    $this->info("Successfully synchronized {$synced} attendance records from the RFID database.");
    return 0;
})->purpose('Synchronize attendance punch logs from the external RFID attendance database');

\Illuminate\Support\Facades\Schedule::command('haf:check-expirations')->dailyAt('08:00');

