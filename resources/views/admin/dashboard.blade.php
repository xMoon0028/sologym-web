@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'HR Dashboard')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards (Larger Numbers & Darkened Titles, Expiring Certs Removed) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Employees -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Active Staff</span>
                    <p class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white mt-2 font-mono tracking-tight leading-none">{{ $totalEmployees }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 rounded-xl flex items-center justify-center border border-red-100 dark:border-red-900/60 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end text-xs">
                <a href="{{ route('admin.employees.index') }}" class="font-bold text-red-700 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 inline-flex items-center gap-1 group">
                    <span>Manage directory</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Card 2: New Hires -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">New Hires</span>
                    <p class="text-4xl sm:text-5xl font-black text-emerald-600 dark:text-emerald-400 mt-2 font-mono tracking-tight leading-none">{{ $newHiresThisMonth }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center justify-center border border-emerald-100 dark:border-emerald-900/60 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ now()->format('F Y') }} Intake</span>
                <span class="text-emerald-700 dark:text-emerald-400 font-bold inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Onboarded
                </span>
            </div>
        </div>

        <!-- Card 3: Leave Applications -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Pending Leaves</span>
                    <p class="text-4xl sm:text-5xl font-black {{ ($pendingLeavesCount ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-950 dark:text-white' }} mt-2 font-mono tracking-tight leading-none">
                        {{ $pendingLeavesCount ?? 0 }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 rounded-xl flex items-center justify-center border border-amber-100 dark:border-amber-900/60 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end text-xs">
                <a href="{{ route('admin.leaves.index') }}" class="font-bold text-red-700 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 inline-flex items-center gap-1 group">
                    <span>Review leaves</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Card 4: Payroll Periods -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Payroll Cutoffs</span>
                    <p class="text-4xl sm:text-5xl font-black text-slate-950 dark:text-white mt-2 font-mono tracking-tight leading-none">{{ $recentPayroll->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end text-xs">
                <a href="{{ route('admin.payroll.index') }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-red-700 dark:hover:text-white inline-flex items-center gap-1 group">
                    <span>Manage periods</span>
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Operations Launchpad -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between mb-3.5">
            <h2 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Quick Actions &amp; Launchpad</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.analytics.index') }}" class="text-[11px] font-bold text-red-700 dark:text-red-400 hover:underline inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>HR Analytics</span>
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">6 Core Modules</span>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('admin.employees.create') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Add Employee</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">Create personnel profile</span>
            </a>

            <a href="{{ route('admin.attendance.index') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Attendance</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">CSV &amp; biometric logs</span>
            </a>

            <a href="{{ route('admin.leaves.index') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Leave Records</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">Section &amp; Exec workflow</span>
            </a>

            <a href="{{ route('admin.time-credits.index') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Time Credits</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">Offset ledger &amp; caps</span>
            </a>

            <a href="{{ route('admin.payroll.create') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">New Cutoff</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">Semi-monthly run</span>
            </a>

            <a href="{{ route('admin.loans.index') }}" class="flex flex-col items-start p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-700/80 hover:border-red-300 dark:hover:border-red-800 hover:bg-red-50/40 dark:hover:bg-red-950/20 text-slate-800 dark:text-slate-100 hover:text-red-900 dark:hover:text-red-300 transition group">
                <div class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-xs font-extrabold text-slate-900 dark:text-white">Loans Monitoring</span>
                <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">SSS, Pag-IBIG, Company</span>
            </a>
        </div>
    </div>

    <!-- Dual Column Section: Recent Leave Requests & Recent Payroll Periods -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Leave Applications Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Recent Leave Applications</h3>
                        <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">3-day advance filing &bull; 24h approval limit per stage</p>
                    </div>
                </div>
                <a href="{{ route('admin.leaves.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition">
                    All Leaves &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
                @forelse($recentLeaves ?? [] as $leave)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 dark:hover:bg-slate-800/60 transition gap-3">
                        <div>
                            <a href="{{ route('admin.leaves.show', $leave) }}" class="font-bold text-xs text-slate-900 dark:text-slate-100 hover:text-red-700 dark:hover:text-red-400 transition">
                                {{ $leave->employee->name ?? 'Staff' }}
                            </a>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 font-medium">
                                {{ $leave->leave_type_label }} ({{ number_format((float) ($leave->total_days ?? 0), 1) }}d) &bull;
                                <span class="font-mono text-[11px]">{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}</span>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border {{ $leave->status_badge_classes }}">
                                {{ $leave->status_label }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">No leave applications recorded yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payroll Periods -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Recent Payroll Runs</h3>
                        <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">Semi-monthly compensation cycles</p>
                    </div>
                </div>
                <a href="{{ route('admin.payroll.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition">
                    View Roster &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
                @forelse($recentPayroll as $p)
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50/70 dark:hover:bg-slate-800/60 transition gap-3">
                        <div>
                            <a href="{{ route('admin.payroll.show', $p) }}" class="font-bold text-xs text-slate-900 dark:text-slate-100 hover:text-red-700 dark:hover:text-red-400 transition">
                                {{ $p->name }}
                            </a>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $p->start_date->format('M d') }} - {{ $p->end_date->format('M d, Y') }}
                                @if($p->date_of_payment)
                                    <span class="text-slate-300 dark:text-slate-600 mx-1">&bull;</span> Payout: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $p->date_of_payment->format('M d, Y') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if(in_array($p->status, ['finalized', 'released']))
                                <a href="{{ route('admin.payroll.export-all', [$p, 'format' => 'xlsx']) }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold transition" title="Export Finalized Payroll to Excel">
                                    Export
                                </a>
                            @endif
                            @php
                                $badge = match($p->status) {
                                    'draft' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                                    'under_review' => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800/80',
                                    'finalized' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/80',
                                    'released' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/80',
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-[11px] font-bold font-mono rounded-full border {{ $badge }}">
                                {{ strtoupper(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">No payroll cycles created yet</p>
                        <a href="{{ route('admin.payroll.create') }}" class="text-xs text-red-700 dark:text-red-400 font-bold hover:underline mt-1 inline-block">Create the first cutoff</a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Email System Diagnostic Tool -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Email Delivery System Diagnostics</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Send an instant test email via SMTP to verify TLS handshake, host connection, and credentials.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.test-email') }}" class="flex items-center gap-2 w-full sm:w-auto">
                @csrf
                <input type="email" name="email" value="{{ auth()->user()->email ?? 'it@lasaclipa.org' }}" required placeholder="recipient@domain.com"
                       class="px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 w-full sm:w-64">
                <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shrink-0 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Send Test Email</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
