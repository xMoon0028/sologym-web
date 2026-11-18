<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class FixStaffAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hris:fix-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and repair all employee user accounts, roles, and profile linkages to eliminate 500/403 login errors.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing role and account repair...');

        // 0. Ensure database columns can store long OAuth data (e.g. Google avatar URLs) and expanded leave types
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY `avatar` TEXT NULL");
        } catch (\Throwable $e) {}
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `leave_requests` MODIFY `leave_type` VARCHAR(64) NOT NULL");
        } catch (\Throwable $e) {}

        // 1. Ensure Spatie Roles exist
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'employee']);

        $adminEmails = config('hris.admin_emails', []);

        $linkedCount = 0;
        $createdCount = 0;
        $rolesUpdatedCount = 0;

        // Ensure no employee records are hidden by soft-deletes
        DB::table('employees')->whereNotNull('deleted_at')->update([
            'is_active' => false,
            'separation_reason' => DB::raw("COALESCE(separation_reason, 'Resigned / Inactive')"),
            'deleted_at' => null,
        ]);

        $employees = Employee::all();

        foreach ($employees as $employee) {
            $email = strtolower(trim($employee->email ?? ''));

            if (empty($email)) {
                // If employee has a linked user with an email, copy that email over
                if ($employee->user && !empty($employee->user->email)) {
                    $employee->update(['email' => strtolower(trim($employee->user->email))]);
                    $email = strtolower(trim($employee->user->email));
                }
            }

            if (empty($email)) {
                continue;
            }

            // Find or link User account
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Create user account for employee
                $user = User::create([
                    'name'     => $employee->name,
                    'email'    => $email,
                    'password' => Hash::make(Str::random(32)),
                ]);
                $createdCount++;
                $this->line("Created User account for: {$employee->name} ({$email})");
            }

            // Link user_id on employee if not matched
            if ($employee->user_id !== $user->id) {
                $employee->update(['user_id' => $user->id]);
                $linkedCount++;
                $this->line("Linked employee {$employee->name} to user ID #{$user->id}");
            }

            // Ensure employee role is granted
            if (!$user->hasRole('employee')) {
                $user->assignRole('employee');
                $rolesUpdatedCount++;
            }

            // Check if admin privileges apply
            $isConfiguredAdmin = in_array(strtolower($user->email), $adminEmails);
            $isItDepartment = $employee->department && (
                strtoupper($employee->department->code ?? '') === 'IT' ||
                stripos($employee->department->name ?? '', 'Information Technology') !== false
            );
            $hasApprovalRole = in_array($employee->approval_role, ['hr_officer', 'executive_office', 'executive_director', 'it_officer']);

            if ($isConfiguredAdmin || $isItDepartment || $hasApprovalRole) {
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                    $this->info("Assigned 'admin' role to: {$user->name} ({$email})");
                }
            }
        }

        // 2. Also ensure every existing User in the database with no role receives 'employee'
        $usersWithoutRole = User::doesntHave('roles')->get();
        foreach ($usersWithoutRole as $u) {
            $u->assignRole('employee');
            $rolesUpdatedCount++;
            $this->line("Assigned 'employee' role to orphaned user #{$u->id} ({$u->email})");
        }

        // Clear Spatie permissions cache so changes apply immediately
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->newLine();
        $this->info("Account & Role Repair Complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['New User Accounts Created', $createdCount],
                ['Employee Profiles Linked', $linkedCount],
                ['Roles Synchronized / Repaired', $rolesUpdatedCount],
            ]
        );

        // 3. Diagnostic Test: Verify portal dashboard rendering for each staff member
        $this->newLine();
        $this->info("Running self-check: Testing portal dashboard rendering for all staff accounts...");
        $hasErrors = false;
        $testedCount = 0;

        foreach ($employees as $employee) {
            $user = $employee->user ?: User::where('email', $employee->email)->first();
            if (!$user) {
                continue;
            }

            $testedCount++;
            try {
                auth()->login($user);
                $controller = app(\App\Http\Controllers\Employee\PortalController::class);
                $response = $controller->index();
                if ($response instanceof \Illuminate\View\View) {
                    $response->render();
                }
            } catch (\Throwable $e) {
                $hasErrors = true;
                $this->error("✕ FAILED for [{$employee->name}] ({$user->email}): {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
            }
        }

        if (!$hasErrors) {
            $this->info("✓ SUCCESS: All {$testedCount} staff portal accounts verified successfully with zero errors!");
        } else {
            $this->warn("⚠ Some accounts had errors during verification. See output above.");
        }

        return $hasErrors ? Command::FAILURE : Command::SUCCESS;
    }
}
