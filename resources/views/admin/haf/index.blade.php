@extends('layouts.admin')

@section('title', 'Health Assistance Fund (HMO) Monitoring')
@section('page-title', 'Health Assistance Fund (HMO)')

@section('content')
<div class="space-y-6" x-data="{
    enrollModal: false,
    selectedEmployeeId: '',
    employeeSearch: '',
    startDate: '{{ now('Asia/Manila')->toDateString() }}',
    validityDays: 365,
    endDate: '{{ now('Asia/Manila')->addDays(365)->toDateString() }}',
    contributionAmount: 1200,
    coverageAmount: 15000,
    setValidityPreset(days) {
        this.validityDays = days;
        this.updateEndDate();
    },
    setContributionPreset(amt) {
        this.contributionAmount = amt;
    },
    setCoveragePreset(amt) {
        this.coverageAmount = amt;
    },
    updateEndDate() {
        if (!this.startDate) return;
        let d = new Date(this.startDate);
        d.setDate(d.getDate() + parseInt(this.validityDays || 365));
        this.endDate = d.toISOString().split('T')[0];
    }
}">

    <!-- Top Executive Header Banner -->
    <div class="relative overflow-hidden bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-red-700 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Health Assistance Fund (HMO) Monitoring</h2>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-red-100 dark:bg-red-950/70 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-900/60">
                            365d Policy
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Voluntary staff healthcare assistance fund functioning as HMO • 365 days coverage with automatic 30-day advance renewal notification
                    </p>
                </div>
            </div>

            <!-- Actions Toolbar -->
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <form action="{{ route('admin.haf.check-expirations') }}" method="POST" onsubmit="return confirm('Scan all HAF records and send 30-day expiration notices to qualifying staff via email and in-app notification?');" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2.5 text-xs font-bold text-amber-900 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-300/80 dark:border-amber-800/80 rounded-xl transition inline-flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Scan &amp; Notify Expiring</span>
                    </button>
                </form>

                <button @click="enrollModal = true" type="button" class="px-4 py-2.5 text-xs font-bold text-white bg-red-700 hover:bg-red-800 dark:bg-red-600 dark:hover:bg-red-700 rounded-xl transition inline-flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                    <span>Enroll Staff in HAF</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Active Members -->
        <a href="{{ route('admin.haf.index', ['status' => 'active']) }}" class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:border-emerald-300 dark:hover:border-emerald-800/80 transition group">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Covered</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 group-hover:scale-125 transition"></span>
            </div>
            <p class="text-2xl font-black text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 mt-2 transition">
                {{ number_format($totalActive) }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Valid HMO policies</p>
        </a>

        <!-- Expiring Soon (30 Days) - Highlighted with Glow -->
        <a href="{{ route('admin.haf.index', ['status' => 'expiring_soon']) }}" class="p-4 bg-amber-50/90 dark:bg-amber-950/40 rounded-2xl border border-amber-300/90 dark:border-amber-800/80 shadow-sm hover:border-amber-400 transition relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-900 dark:text-amber-200">Expiring in 30d</span>
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
            </div>
            <p class="text-2xl font-black text-amber-800 dark:text-amber-300 mt-2 group-hover:scale-105 transition transform origin-left">
                {{ number_format($expiringSoonCount) }}
            </p>
            <p class="text-[10px] font-bold text-amber-700/80 dark:text-amber-400/80 mt-0.5">Due for renewal</p>
        </a>

        <!-- Expired Cycles -->
        <a href="{{ route('admin.haf.index', ['status' => 'expired']) }}" class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:border-slate-400 transition">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Expired Cycles</span>
                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span>
            </div>
            <p class="text-2xl font-black text-slate-700 dark:text-slate-300 mt-2">
                {{ number_format($expiredCount) }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Past 365 days</p>
        </a>

        <!-- Total Voluntary Contributions -->
        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pooled Funds</span>
            <p class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 mt-2">
                ₱{{ number_format($totalContributions, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Staff voluntary sum</p>
        </div>

        <!-- Claims Disbursed -->
        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Claims Disbursed</span>
            <p class="text-lg sm:text-xl font-black text-rose-600 dark:text-rose-400 mt-2">
                ₱{{ number_format($totalClaimsDisbursed, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Medical claims paid</p>
        </div>

        <!-- Available Remaining Coverage -->
        <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Available Coverage</span>
            <p class="text-lg sm:text-xl font-black text-indigo-600 dark:text-indigo-400 mt-2">
                ₱{{ number_format($totalRemainingCoverage, 2) }}
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Active claimable pool</p>
        </div>
    </div>

    <!-- Quick Status Pill Tabs & Filter Bar -->
    <div class="space-y-3">
        <!-- Interactive Pill Switcher -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a href="{{ route('admin.haf.index', array_merge(request()->except('status', 'page'), [])) }}"
               class="px-3.5 py-1.5 rounded-full font-bold transition inline-flex items-center gap-1.5 {{ !request('status') ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 border border-slate-200/80 dark:border-slate-700' }}">
                <span>All Records</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-white/20 dark:bg-slate-900/20">{{ $funds->total() }}</span>
            </a>

            <a href="{{ route('admin.haf.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
               class="px-3.5 py-1.5 rounded-full font-bold transition inline-flex items-center gap-1.5 {{ request('status') === 'active' ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 border border-emerald-200 dark:border-emerald-800/60' }}">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Active Coverage</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-800/30">{{ $totalActive }}</span>
            </a>

            <a href="{{ route('admin.haf.index', array_merge(request()->except('status', 'page'), ['status' => 'expiring_soon'])) }}"
               class="px-3.5 py-1.5 rounded-full font-bold transition inline-flex items-center gap-1.5 {{ request('status') === 'expiring_soon' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 hover:bg-amber-100 border border-amber-300 dark:border-amber-800/80' }}">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                <span>Expiring Soon (Within 30d)</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-amber-900/20 font-black">{{ $expiringSoonCount }}</span>
            </a>

            <a href="{{ route('admin.haf.index', array_merge(request()->except('status', 'page'), ['status' => 'expired'])) }}"
               class="px-3.5 py-1.5 rounded-full font-bold transition inline-flex items-center gap-1.5 {{ request('status') === 'expired' ? 'bg-slate-700 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 border border-slate-200 dark:border-slate-700' }}">
                <span>Expired</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-700">{{ $expiredCount }}</span>
            </a>
        </div>

        <!-- Search & Filter Controls (Clean Flex Layout) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <form method="GET" action="{{ route('admin.haf.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1 w-full">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="relative flex-1 min-w-[240px] sm:max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search staff name, employee #, member control #, receipt #..."
                           class="pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs w-full focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                </div>

                <select name="department_id" class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-semibold transition shadow-sm text-center">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'department_id', 'status']))
                        <a href="{{ route('admin.haf.index') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-medium px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Health Assistance Records Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-5 font-bold">Staff Member</th>
                        <th class="py-3.5 px-4 font-bold">Member Control #</th>
                        <th class="py-3.5 px-4 font-bold">365-Day Validity Period</th>
                        <th class="py-3.5 px-4 font-bold">Status &amp; Countdown</th>
                        <th class="py-3.5 px-4 font-bold text-right">Voluntary Contrib.</th>
                        <th class="py-3.5 px-4 font-bold text-right">HMO Coverage</th>
                        <th class="py-3.5 px-4 font-bold text-right">Available Balance</th>
                        <th class="py-3.5 px-5 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($funds as $fund)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition {{ $fund->is_expiring_soon ? 'bg-amber-50/30 dark:bg-amber-950/10' : '' }}">
                            <!-- Staff info -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-700 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-sm">
                                        {{ strtoupper(substr($fund->employee->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.haf.show', $fund) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-red-700 dark:hover:text-red-400 transition truncate block">
                                            {{ $fund->employee->name ?? 'N/A' }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">
                                            {{ $fund->employee->position ?? 'Staff' }} • <span class="font-medium text-slate-600 dark:text-slate-300">{{ $fund->employee->department->name ?? 'General' }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Member Number & Plan Year -->
                            <td class="py-4 px-4">
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $fund->member_number ?: 'HAF-' . $fund->id }}</span>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $fund->plan_year ?: 'Annual' }}</p>
                            </td>

                            <!-- Validity Period & Progress Meter -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $fund->start_date ? $fund->start_date->format('M d, Y') : '-' }} &rarr; {{ $fund->end_date ? $fund->end_date->format('M d, Y') : '-' }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                            <div class="h-full {{ $fund->is_expired ? 'bg-slate-400' : ($fund->is_expiring_soon ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ $fund->validity_progress_percentage }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $fund->validity_progress_percentage }}%</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Badge & 30-Day Expiration Tag -->
                            <td class="py-4 px-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $fund->status_badge_class }}">
                                        @if($fund->is_expiring_soon)
                                             <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-ping"></span>
                                        @elseif($fund->computed_status === 'active')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        @endif
                                        {{ $fund->status_label }}
                                    </span>

                                    @if($fund->is_expiring_soon)
                                        <p class="text-[10px] font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>30-Day Window</span>
                                        </p>
                                    @endif

                                    @if($fund->expiration_notified_at)
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500" title="Notice sent {{ $fund->expiration_notified_at->format('M d, Y h:i A') }}">
                                            ✓ Notice sent {{ $fund->expiration_notified_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            <!-- Voluntary Contribution -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-bold text-slate-800 dark:text-slate-200">₱{{ number_format($fund->contribution_amount, 2) }}</span>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 capitalize">{{ str_replace('_', ' ', $fund->payment_method) }}</p>
                            </td>

                            <!-- Total HMO Limit -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-black text-slate-900 dark:text-slate-100">₱{{ number_format($fund->coverage_amount, 2) }}</span>
                                <p class="text-[10px] text-rose-500 dark:text-rose-400 font-medium">Used: ₱{{ number_format($fund->amount_used, 2) }}</p>
                            </td>

                            <!-- Available Remaining Balance -->
                            <td class="py-4 px-4 text-right">
                                <span class="font-black text-sm {{ $fund->remaining_balance > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400' }}">
                                    ₱{{ number_format($fund->remaining_balance, 2) }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.haf.show', $fund) }}" class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition" title="View Member Profile &amp; Manage Claims">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @if($fund->is_expiring_soon || $fund->is_expired)
                                        <form action="{{ route('admin.haf.send-notice', $fund) }}" method="POST" onsubmit="return confirm('Send 30-day expiration notice to {{ $fund->employee->name }} now?');" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-amber-600 hover:text-amber-800 hover:bg-amber-50 dark:hover:bg-amber-950/60 rounded-xl transition" title="Send Expiration Notice Now">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.haf.edit', $fund) }}" class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/60 rounded-xl transition" title="Edit HAF Parameters">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="max-w-sm mx-auto space-y-1">
                                    <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No Health Assistance Fund records found</p>
                                    <p class="text-xs text-slate-400">Click &ldquo;Enroll Staff in HAF&rdquo; above to register an employee or try modifying your search filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($funds->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800">
                {{ $funds->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Enroll Staff in Health Assistance Fund (Optimized UI) -->
    <div x-show="enrollModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60" @click="enrollModal = false"></div>

            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800">
                <form action="{{ route('admin.haf.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-red-700 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Enroll Staff into Health Assistance Fund (HMO)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Voluntary staff contribution coverage with 365 days active validity</p>
                            </div>
                        </div>
                        <button type="button" @click="enrollModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                        <!-- Step 1: Employee Select -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Select Staff Member *</label>
                            <select name="employee_id" required class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-medium">
                                <option value="">-- Choose Staff Member --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->name }} ({{ $emp->employee_no ?: 'No ID' }} • {{ $emp->department->name ?? 'General' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Step 2: 365 Days Validity Auto-Calculator with Presets -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">365-Day Validity Period</span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="setValidityPreset(365)"
                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition"
                                            :class="validityDays == 365 ? 'bg-red-700 text-white border-red-700' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'">
                                        365 Days (1 Year)
                                    </button>
                                    <button type="button" @click="setValidityPreset(180)"
                                            class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition"
                                            :class="validityDays == 180 ? 'bg-red-700 text-white border-red-700' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'">
                                        180 Days (Semi-Annual)
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Effective Date *</label>
                                    <input type="date" name="start_date" x-model="startDate" @change="updateEndDate()" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-red-500 focus:border-red-500">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Duration (Days) *</label>
                                    <input type="number" name="validity_days" x-model="validityDays" @input="updateEndDate()" min="1" max="730" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-red-500 focus:border-red-500 font-mono">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Expiration Date</label>
                                    <input type="date" name="end_date" x-model="endDate" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-red-500 focus:border-red-500 font-bold text-amber-700 dark:text-amber-400">
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Financial Parameters & Presets -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Voluntary Contribution -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Voluntary Contribution (₱) *</label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="setContributionPreset(1200)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700">₱1.2k</button>
                                        <button type="button" @click="setContributionPreset(2400)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700">₱2.4k</button>
                                    </div>
                                </div>
                                <input type="number" step="0.01" name="contribution_amount" x-model="contributionAmount" required min="0" placeholder="1200.00"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-mono">
                                <p class="text-[10px] text-slate-400">Amount paid or deducted voluntarily</p>
                            </div>

                            <!-- HMO Coverage Limit -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Total HMO Coverage Limit (₱) *</label>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="setCoveragePreset(10000)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700">₱10k</button>
                                        <button type="button" @click="setCoveragePreset(15000)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700">₱15k</button>
                                        <button type="button" @click="setCoveragePreset(20000)" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700">₱20k</button>
                                    </div>
                                </div>
                                <input type="number" step="0.01" name="coverage_amount" x-model="coverageAmount" required min="0" placeholder="15000.00"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-bold font-mono">
                                <p class="text-[10px] text-slate-400">Maximum assistance claimable in 365 days</p>
                            </div>
                        </div>

                        <!-- Step 4: Mode of Payment & OR Receipt -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                                <select name="payment_method" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                                    <option value="voluntary_contribution">Voluntary Staff Contribution</option>
                                    <option value="payroll_deduction">Payroll Salary Deduction</option>
                                    <option value="direct_cash">Direct Cash Over the Counter</option>
                                    <option value="bank_transfer">Bank / GCash Fund Transfer</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt / Control #</label>
                                <input type="text" name="receipt_no" placeholder="e.g. OR-2026-8812"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-mono">
                            </div>
                        </div>

                        <!-- Step 5: Scanned Proof of Contribution -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contribution Receipt / Signed Enrollment Form (Optional)</label>
                            <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-red-950/60 dark:file:text-red-400">
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Plan Notes</label>
                            <textarea name="notes" rows="2" placeholder="e.g. Annual voluntary HMO coverage for FY 2026-2027."
                                      class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                    </div>

                    <div class="p-5 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-end gap-2.5">
                        <button type="button" @click="enrollModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-700 hover:bg-red-800 dark:bg-red-600 dark:hover:bg-red-700 rounded-xl transition shadow-sm">
                            Enroll &amp; Activate 365-Day HMO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
