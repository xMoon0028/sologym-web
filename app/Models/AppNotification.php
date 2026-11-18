<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected $appends = [
        'category',
        'time_ago',
        'badge_color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '';
    }

    public function getCategoryAttribute(): string
    {
        if ($this->type === 'payslip_released') {
            return 'payslip';
        }
        if (str_starts_with($this->type, 'leave_')) {
            return 'leave';
        }
        if (str_starts_with($this->type, 'time_credit_')) {
            return 'time_credit';
        }
        if (str_starts_with($this->type, 'attendance_')) {
            return 'attendance';
        }
        if (str_starts_with($this->type, 'certificate_')) {
            return 'certificate';
        }
        if (str_starts_with($this->type, 'compensation_')) {
            return 'compensation';
        }
        if (str_starts_with($this->type, 'loan_')) {
            return 'loan';
        }

        return 'general';
    }

    public function getBadgeColorAttribute(): array
    {
        return match ($this->category) {
            'loan' => [
                'bg' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20',
                'dot' => 'bg-amber-600',
                'icon' => '💳',
            ],
            'payslip' => [
                'bg' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20',
                'dot' => 'bg-emerald-600',
                'icon' => '💵',
            ],
            'leave' => [
                'bg' => 'bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-500/20',
                'dot' => 'bg-blue-600',
                'icon' => '🏖️',
            ],
            'time_credit' => [
                'bg' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20',
                'dot' => 'bg-amber-600',
                'icon' => '⏱️',
            ],
            'attendance' => [
                'bg' => 'bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-500/20',
                'dot' => 'bg-purple-600',
                'icon' => '📅',
            ],
            'certificate' => [
                'bg' => 'bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-500/20',
                'dot' => 'bg-orange-600',
                'icon' => '📜',
            ],
            'compensation' => [
                'bg' => 'bg-teal-500/10 text-teal-700 dark:text-teal-400 border-teal-500/20',
                'dot' => 'bg-teal-600',
                'icon' => '💼',
            ],
            default => [
                'bg' => 'bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-500/20',
                'dot' => 'bg-red-600',
                'icon' => '🔔',
            ],
        };
    }
}
