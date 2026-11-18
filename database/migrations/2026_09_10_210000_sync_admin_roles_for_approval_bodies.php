<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up(): void
    {
        // Ensure roles exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // Sync admin role for existing HR Officer and Executive Office accounts
        $employees = Employee::whereIn('approval_role', ['hr_officer', 'executive_office'])->get();

        foreach ($employees as $emp) {
            $user = $emp->user ?: ($emp->email ? User::where('email', $emp->email)->first() : null);
            if ($user) {
                if (!$emp->user_id) {
                    $emp->update(['user_id' => $user->id]);
                }
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                }
                if (!$user->hasRole('employee')) {
                    $user->assignRole('employee');
                }
            }
        }
    }

    public function down(): void
    {
        // No destructive reversal needed
    }
};
