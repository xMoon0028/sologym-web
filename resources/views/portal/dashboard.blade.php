@extends('layouts.portal')

@section('title', 'Employee Dashboard')
@section('page-title', 'Employee Overview')

@section('content')
<div class="space-y-6">

    <!-- Welcome & Leave/Offset Summary Banner -->
    <div class="bg-gradient-to-r from-red-50/90 via-white to-emerald-50/60 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800 p-5 sm:p-6 rounded-2xl border border-red-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100">{{ $employee->name }}</h2>
            <div class="flex items-center gap-2 flex-wrap text-xs text-slate-600 dark:text-slate-300 mt-1 font-semibold">
                @if($employee->position)
                    <span>{{ $employee->position }}</span>
                    <span>&bull;</span>
                @endif
                <span>{{ $employee->department?->name ?? 'LASAC' }}</span>
                <span class="text-slate-500 dark:text-slate-400 font-mono">(ID: {{ $employee->employee_no ?? '—' }})</span>
                @if($employee->email || optional($employee->user)->email)
                    <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/60">
                        {{ $employee->email ?? optional($employee->user)->email }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2 mt-3.5 flex-wrap text-xs">
                <span class="px-2.5 py-1 rounded-lg font-mono font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-900/60">
                    VL: {{ number_format($employee->remaining_vacation_leave, 1) }}d
                </span>
                <span class="px-2.5 py-1 rounded-lg font-mono font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-900/60">
                    SL: {{ number_format($employee->remaining_sick_leave, 1) }}d
                </span>
                <span class="px-2.5 py-1 rounded-lg font-mono font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60">
                    EL: {{ number_format($employee->remaining_emergency_leave, 1) }}d
                </span>
                <a href="{{ route('portal.leaves.index') }}" class="px-3 py-1 rounded-lg bg-red-700 hover:bg-red-800 text-white text-[11px] font-bold transition shadow-2xs ml-1">
                    File Leave Application
                </a>
            </div>
        </div>
    </div>

    <!-- Active Code of Conduct / Notice to Explain Alert Banner -->
    @php
        $activeNte = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('disciplinary_notices')) {
                $activeNte = \App\Models\DisciplinaryNotice::where('employee_id', $employee->id)
                    ->where('requires_explanation', true)
                    ->whereNull('scanned_nte_path')
                    ->whereNull('nte_received_at')
                    ->whereNotIn('status', ['resolved', 'closed'])
                    ->latest('id')
                    ->first();
            }
        } catch (\Throwable $e) {}
    @endphp
    @if($employee->is_time_in_blocked)
        <div class="p-4 bg-red-600 text-white rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center font-black text-lg shrink-0 mt-0.5">⛔</span>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase tracking-wider text-red-100">
                        Time-In Restricted: Overdue Notice to Explain (NTE)
                    </h4>
                    <p class="text-xs text-red-100 mt-0.5">
                        Your two (2) day deadline to submit your written Notice to Explain to HR has lapsed. Your account is on Time-In restriction. Please proceed to the Human Resources Department immediately.
                    </p>
                </div>
            </div>
            @if($activeNte)
                <a href="{{ route('portal.conduct.show', $activeNte) }}"
                   class="px-4 py-2 bg-white text-red-700 hover:bg-red-50 text-xs font-black rounded-xl transition shadow-xs whitespace-nowrap shrink-0 self-end sm:self-center">
                    View Notice Details
                </a>
            @endif
        </div>
    @elseif($activeNte)
        <div class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border-2 border-amber-300 dark:border-amber-900 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center font-black text-sm shrink-0 mt-0.5">⚠️</span>
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-amber-950 dark:text-amber-200">
                        Notice to Explain (NTE) Required: {{ $activeNte->offense_title }}
                    </h4>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                        Please personally submit your written explanation to HR within 2 days (Deadline: <span class="font-bold underline">{{ $activeNte->explanation_deadline?->format('F d, Y') }}</span>). Failure to submit within 2 days will restrict your Time-In access.
                    </p>
                </div>
            </div>
            <a href="{{ route('portal.conduct.show', $activeNte) }}"
               class="px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white text-xs font-bold rounded-xl transition shadow-xs whitespace-nowrap shrink-0 self-end sm:self-center">
                View Notice Details
            </a>
        </div>
    @endif

    <!-- KPI CARDS: Leaves, Offset, Attendance Logs, Published Payslips -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Leaves -->
        <a href="{{ route('portal.leaves.index') }}" class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border-2 border-blue-200/90 dark:border-blue-900/70 shadow-sm hover:border-red-500 hover:shadow-md transition group">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Leaves</span>
                    <p class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white mt-2 font-mono tracking-tight leading-none">
                        {{ number_format($employee->remaining_vacation_leave + $employee->remaining_sick_leave + $employee->remaining_emergency_leave, 1) }}
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">days</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/60 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-xs text-red-700 dark:text-red-400 font-bold mt-4 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                <span>Apply &amp; track leave requests</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </p>
        </a>

        <!-- Time Off (Offset Credits) -->
        <a href="{{ route('portal.time-credits.index') }}" class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border-2 border-red-200/90 dark:border-red-900/70 shadow-sm hover:border-red-500 hover:shadow-md transition group">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Time Off Credits</span>
                    <p class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white mt-2 font-mono tracking-tight leading-none">
                        {{ number_format($balance, 2) }}
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">hrs</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-100 dark:border-red-900/60 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xs text-red-700 dark:text-red-400 font-bold mt-4 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                <span>View time off &amp; overtime ledger</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </p>
        </a>

        <!-- Attendance Logs -->
        <a href="{{ route('portal.attendance.index') }}" class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border-2 border-amber-200/90 dark:border-amber-900/70 shadow-sm hover:border-red-500 hover:shadow-md transition group">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Attendance Logs</span>
                    <p class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white mt-2 font-mono tracking-tight leading-none">
                        {{ $recentAttendance->count() }}
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">recent</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/60 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="text-xs text-red-700 dark:text-red-400 font-bold mt-4 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                <span>Inspect daily punch logs</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </p>
        </a>

        <!-- Published Payslips Card -->
        <a href="{{ route('portal.payslips.index') }}" class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:border-red-500 hover:shadow-md transition group">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Published Payslips</span>
                    <p class="text-4xl sm:text-5xl font-black text-emerald-600 dark:text-emerald-400 mt-2 font-mono tracking-tight leading-none">
                        {{ $recentPayslips->count() }}
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">ready</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/60 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="text-xs text-red-700 dark:text-red-400 font-bold mt-4 inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                <span>View &amp; download PDF payslips</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </p>
        </a>
    </div>

    <!-- Primary Modules Grid: Leaves, Offset Ledger, Attendance Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Leaves Panel -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/50">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">My Leave Requests</h3>
                <a href="{{ route('portal.leaves.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:underline transition">+ Apply / View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs flex-1">
                @forelse($recentLeaves ?? [] as $lv)
                    <a href="{{ route('portal.leaves.show', $lv) }}" class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition block">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $lv->leave_type_label }} ({{ number_format((float) ($lv->days_count ?? 1.0), 1) }}d)</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $lv->start_date ? $lv->start_date->format('M d') : '' }} - {{ $lv->end_date ? $lv->end_date->format('M d, Y') : '' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lv->status_badge_class ?? 'bg-slate-50 text-slate-700 border-slate-200' }}">
                            {{ $lv->status_label }}
                        </span>
                    </a>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                        No leave requests filed yet.
                        <div class="mt-2">
                            <a href="{{ route('portal.leaves.index') }}" class="text-red-700 dark:text-red-400 font-bold hover:underline">File a leave request (3+ days advance)</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Offset (Time Credits) Panel -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/50">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Offset / Time Credits</h3>
                <a href="{{ route('portal.time-credits.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:underline transition">Full Ledger &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs flex-1">
                @forelse($recentTimeCredits ?? [] as $tc)
                    <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $tc->reason ?? ucfirst($tc->type) }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $tc->date ? $tc->date->format('M d, Y') : '' }}</p>
                        </div>
                        <div class="text-right font-mono">
                            <span class="font-bold {{ $tc->hours >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                {{ $tc->hours >= 0 ? '+' : '' }}{{ number_format($tc->hours, 2) }}h
                            </span>
                            <span class="block text-[10px] text-slate-400">Bal: {{ number_format($tc->running_balance, 2) }}h</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">No offset credit entries recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Attendance Logs Panel -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/50">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Attendance Logs</h3>
                <a href="{{ route('portal.attendance.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:underline transition">Full History &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs flex-1">
                @forelse($recentAttendance as $att)
                    <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-slate-100">{{ $att->date ? $att->date->format('D, M d') : '—' }}</span>
                            <span class="text-slate-500 dark:text-slate-400 ml-2 font-mono text-[11px]">{{ $att->time_in ?? '--:--' }} - {{ $att->time_out ?? '--:--' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($att->late_minutes > 0)
                                <span class="text-amber-700 dark:text-amber-400 font-bold text-[10px] font-mono">Late ({{ $att->late_minutes }}m)</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $att->status === 'present' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900/60' : 'bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/60' }}">
                                {{ $att->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">No attendance recorded recently.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Released Payslips & Loan Balance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Released Payslips Card (2 columns wide) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-800/50">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Released Payslips</h3>
                <a href="{{ route('portal.payslips.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:underline transition">View All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
                @forelse($recentPayslips as $ps)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                        <div>
                            <p class="font-bold text-xs text-slate-900 dark:text-slate-100">{{ $ps->period?->name ?? 'Payroll Period' }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Disbursement: {{ $ps->period?->date_of_payment ? $ps->period->date_of_payment->format('M d, Y') : 'Pending' }}</p>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                            <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-xs">PHP {{ number_format($ps->net_pay, 2) }}</span>
                            <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0" role="group">
                                <a href="{{ route('portal.payslips.pdf', [$ps, 'action' => 'view']) }}" target="_blank" class="px-2.5 py-1 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-700 text-[10px] font-semibold inline-flex items-center gap-1" title="View Payslip in browser">
                                    <svg class="w-3 h-3 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View</span>
                                </a>
                                <a href="{{ route('portal.payslips.pdf', [$ps, 'action' => 'download']) }}" class="px-2.5 py-1 bg-red-50 dark:bg-red-950/60 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 text-[10px] font-bold inline-flex items-center gap-1" title="Download PDF file">
                                    <svg class="w-3 h-3 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>PDF</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-xs">No payslips have been released yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Loan Publish / Balance Card -->
        <div class="bg-slate-50/80 dark:bg-slate-900/60 rounded-2xl border border-slate-200/70 dark:border-slate-800 p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Loan Publish &amp; Balance</span>
                </div>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-200 mt-3 font-mono">
                    ₱{{ number_format($employee->total_loan_balance, 2) }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                    Published SSS, Pag-IBIG, and company loan balances &amp; amortization history.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-200/70 dark:border-slate-800">
                <a href="{{ route('portal.loans.index') }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-red-700 dark:hover:text-red-400 inline-flex items-center gap-1 transition">
                    <span>Open loan records</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
