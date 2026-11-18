<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function appNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppNotification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotificationsCount(): int
    {
        try {
            return $this->appNotifications()->where('is_read', false)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Ensure the user has the admin role if they represent HR Officer or Executive Office approval bodies.
     */
    public function syncAdminRoleFromApprovalBody(): void
    {
        // Ensure roles table has the required roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee']);

        $adminEmails = config('hris.admin_emails', []);
        $isConfiguredAdmin = in_array(strtolower($this->email), $adminEmails);

        if ($isConfiguredAdmin) {
            if (!$this->hasRole('admin')) {
                $this->assignRole('admin');
            }
            if (!$this->hasRole('employee')) {
                $this->assignRole('employee');
            }
        }

        $employee = $this->employee ?: ($this->email ? Employee::where('email', $this->email)->first() : null);

        if (!$employee) {
            return;
        }

        // Auto-link employee if not yet linked
        if ($employee->user_id !== $this->id) {
            $employee->update(['user_id' => $this->id]);
        }

        // EVERY linked employee MUST have the 'employee' role to access the staff portal
        if (!$this->hasRole('employee')) {
            $this->assignRole('employee');
        }

        $isItDepartment = $employee->department && (
            strtoupper($employee->department->code ?? '') === 'IT' ||
            stripos($employee->department->name ?? '', 'Information Technology') !== false
        );

        if ($isItDepartment || in_array($employee->approval_role, ['hr_officer', 'executive_office', 'executive_director', 'it_officer'])) {
            if (!$this->hasRole('admin')) {
                $this->assignRole('admin');
            }
        }
    }
}
