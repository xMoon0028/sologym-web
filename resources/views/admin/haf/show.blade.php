@extends('layouts.admin')

@section('title', 'HAF Member Details - ' . ($haf->employee->name ?? 'Member'))
@section('page-title', 'HAF Member Profile')

@section('content')
<div class="space-y-6" x-data="{
    claimModal: false,
    renewModal: false,
    activeTab: 'claims',
    claimTypeFilter: '',
    claimRequested: 0,
    claimApproved: 0,
    remainingBalance: {{ (float) $haf->remaining_balance }},
    renewStartDate: '{{ $haf->end_date ? $haf->end_date->copy()->addDay()->toDateString() : now('Asia/Manila')->toDateString() }}',
    renewValidityDays: 365,
    renewEndDate: '{{ $haf->end_date ? $haf->end_date->copy()->addDay()->addDays(365)->toDateString() : now('Asia/Manila')->addDays(365)->toDateString() }}',
    setRenewValidity(days) {
        this.renewValidityDays = days;
        this.updateRenewEndDate();
    },
    updateRenewEndDate() {
        if (!this.renewStartDate) return;
        let d = new Date(this.renewStartDate);
        d.setDate(d.getDate() + parseInt(this.renewValidityDays || 365));
        this.renewEndDate = d.toISOString().split('T')[0];
    },
    syncClaimApproved() {
        this.claimApproved = this.claimRequested;
    }
}">

    <!-- Top Navigation Toolbar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('admin.haf.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-red-700 dark:text-slate-400 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Health Assistance Monitoring</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Print Statement -->
            <button type="button" onclick="window.print()" class="px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Statement</span>
            </button>

            <a href="{{ route('admin.haf.edit', $haf) }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Plan</span>
            </a>

            <button @click="renewModal = true" type="button" class="px-3.5 py-2 text-xs font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 border border-emerald-200 dark:border-emerald-800/60 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Renew (365d)</span>
            </button>

            <button @click="claimModal = true" type="button" class="px-4 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-800 dark:bg-red-600 dark:hover:bg-red-700 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                <span>Record Medical Claim</span>
            </button>
        </div>
    </div>

    <!-- 30-Day Expiration Warning Alert Banner -->
    @if($haf->is_expiring_soon)
        <div class="p-5 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/40 border border-amber-300 dark:border-amber-800/80 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 flex items-center justify-center flex-shrink-0 animate-pulse">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-950 dark:text-amber-100">
                        HMO Coverage Expiring in {{ $haf->days_remaining }} {{ $haf->days_remaining === 1 ? 'Day' : 'Days' }} ({{ $haf->end_date->format('F d, Y') }})
                    </h3>
                    <p class="text-xs text-amber-900/80 dark:text-amber-300/80 mt-0.5">
                        This voluntary 365-day health assistance membership is in its 1-month expiration window.
                        @if($haf->expiration_notified_at)
                            <span class="font-bold underline ml-1">Automated notice was sent to staff on {{ $haf->expiration_notified_at->format('M d, Y h:i A') }}.</span>
                        @else
                            <span class="font-semibold ml-1">Staff has not yet been notified. Click button to dispatch notice now.</span>
                        @endif
                    </p>
                </div>
            </div>

            <form action="{{ route('admin.haf.send-notice', $haf) }}" method="POST" onsubmit="return confirm('Send 30-day expiration notice to {{ $haf->employee->name }} now?');" class="flex-shrink-0">
                @csrf
                <button type="submit" class="px-4 py-2.5 text-xs font-bold text-amber-950 bg-amber-200 hover:bg-amber-300 dark:bg-amber-900 dark:text-amber-100 dark:hover:bg-amber-800 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-amber-800 dark:text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span>Send Expiration Notice Now</span>
                </button>
            </form>
        </div>
    @elseif($haf->is_expired)
        <div class="p-5 bg-slate-100 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-3xl flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <span class="w-11 h-11 rounded-2xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">HMO Membership Expired</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Validity ended on {{ $haf->end_date->format('F d, Y') }}. Activate a new 365-day cycle using the Renew button.</p>
                </div>
            </div>
            <button @click="renewModal = true" type="button" class="px-4 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl transition">
                Renew Plan
            </button>
        </div>
    @endif

    <!-- Digital Card & Benefit Overview Grid (Robust 3-Column Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Executive Digital Member Card (Left Column) -->
        <div>
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-800 via-red-900 to-slate-950 text-white p-7 shadow-xl border border-red-700/60 print:shadow-none">
                <!-- Metallic Gold Smart Chip Graphic -->
                <div class="absolute top-6 right-6 w-11 h-8 rounded-lg bg-amber-400 shadow-sm border border-amber-300 flex items-center justify-center opacity-90 pointer-events-none">
                    <div class="w-full h-px bg-amber-700/50"></div>
                </div>

                <!-- Watermark Background Seal -->
                <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>

                <!-- Top Row -->
                <div class="flex items-center gap-2.5 pb-3 border-b border-red-700/60 mb-5">
                    <img src="{{ asset('images/lasac_logo.png') }}" alt="LASAC" class="h-7 w-auto brightness-200">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-red-200">Lipa Archdiocesan Social Action Commission</p>
                        <p class="text-xs font-black tracking-tight text-white">Health Assistance Fund (HMO)</p>
                    </div>
                </div>

                <!-- Member Identity -->
                <div class="space-y-3 mb-6">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Covered Member</p>
                        <p class="text-xl font-black tracking-tight text-white leading-tight">
                            {{ $haf->employee->name ?? 'Staff Member' }}
                        </p>
                        <p class="text-xs text-red-200/80 font-medium">
                            {{ $haf->employee->position ?? 'Staff' }} • {{ $haf->employee->department->name ?? 'General' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Member Control #</p>
                            <p class="font-mono font-black text-sm tracking-wide text-white">
                                {{ $haf->member_number ?: 'HAF-' . $haf->id }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Employee ID</p>
                            <p class="font-mono font-bold text-xs text-red-100">
                                {{ $haf->employee->employee_no ?: 'EMP-' . $haf->employee_id }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bottom: 365 Days Validity Progress & Meter -->
                <div class="pt-4 border-t border-red-700/60 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] uppercase text-red-300/80 font-bold">Policy Period:</span>
                            <span class="font-bold text-white ml-1 font-mono text-[11px]">
                                {{ $haf->start_date ? $haf->start_date->format('M d, Y') : '-' }} &rarr; {{ $haf->end_date ? $haf->end_date->format('M d, Y') : '-' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-black px-2.5 py-0.5 rounded-md {{ $haf->is_expiring_soon ? 'bg-amber-400 text-amber-950 font-black animate-pulse' : 'bg-white/20 text-white' }}">
                            {{ $haf->days_remaining }}d Left
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div class="w-full bg-red-950/80 rounded-full h-2 overflow-hidden border border-red-700/40">
                        <div class="h-full {{ $haf->is_expired ? 'bg-slate-400' : ($haf->is_expiring_soon ? 'bg-amber-400' : 'bg-emerald-400') }}"
                             style="width: {{ $haf->validity_progress_percentage }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-red-200/70">
                        <span>{{ $haf->validity_days ?: 365 }}-Day Validity</span>
                        <span>{{ $haf->validity_progress_percentage }}% Elapsed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Metrics & Contribution Details (Right - Takes 2 Columns) -->
        <div class="lg:col-span-2 space-y-4">
            <!-- 3 Financial Blocks -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Limit -->
                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total HMO Limit</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1">
                        ₱{{ number_format($haf->coverage_amount, 2) }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1">Annual maximum pool</p>
                </div>

                <!-- Used -->
                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Claims Disbursed</p>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                        ₱{{ number_format($haf->amount_used, 2) }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1">{{ $haf->claims->count() }} claim(s) filed ({{ $haf->coverage_utilization_percentage }}%)</p>
                </div>

                <!-- Remaining Available -->
                <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200/80 dark:border-emerald-900/60 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Remaining Balance</p>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1">
                        ₱{{ number_format($haf->remaining_balance, 2) }}
                    </p>
                    <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-1">Active claimable benefit</p>
                </div>
            </div>

            <!-- Contribution & Policy Ledger Detail -->
            <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-xs font-black uppercase text-slate-800 dark:text-slate-200 tracking-wider">Contribution &amp; Policy Metadata</h4>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $haf->status_badge_class }}">
                        {{ $haf->status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <p class="text-slate-400 dark:text-slate-500 text-[11px]">Voluntary Contribution</p>
                        <p class="font-black text-slate-900 dark:text-slate-100 mt-0.5">₱{{ number_format($haf->contribution_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500 text-[11px]">Payment Mode</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 capitalize">{{ str_replace('_', ' ', $haf->payment_method) }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500 text-[11px]">Receipt / Ref #</p>
                        <p class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $haf->receipt_no ?: 'None' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 dark:text-slate-500 text-[11px]">Recorded By</p>
                        <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $haf->creator->name ?? 'HR Admin' }}</p>
                    </div>
                </div>

                @if($haf->receipt_file_path || $haf->notes)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs">
                        @if($haf->notes)
                            <p class="text-slate-500 dark:text-slate-400 italic">"{{ $haf->notes }}"</p>
                        @endif
                        @if($haf->receipt_file_path)
                            <a href="{{ Storage::url($haf->receipt_file_path) }}" target="_blank" class="font-bold text-red-700 hover:text-red-800 inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>View Attached Proof / Receipt</span>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Renewal History Timeline -->
            @if($haf->allCycles->count() > 1)
                <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-2">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">HAF Coverage History ({{ $haf->allCycles->count() }} cycles)</p>
                    <div class="space-y-1.5 text-xs">
                        @foreach($haf->allCycles as $cycle)
                            <div class="flex items-center justify-between p-2 rounded-xl {{ $cycle->id === $haf->id ? 'bg-red-50 dark:bg-red-950/40 border border-red-200' : 'bg-slate-50 dark:bg-slate-800' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $cycle->id === $haf->id ? 'bg-red-600' : 'bg-slate-400' }}"></span>
                                    <span class="font-bold {{ $cycle->id === $haf->id ? 'text-red-900 dark:text-red-200' : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ $cycle->plan_year ?: 'Annual' }} ({{ $cycle->start_date ? $cycle->start_date->format('M Y') : '' }} - {{ $cycle->end_date ? $cycle->end_date->format('M Y') : '' }})
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-slate-500">Used: ₱{{ number_format($cycle->amount_used, 2) }}</span>
                                    @if($cycle->id !== $haf->id)
                                        <a href="{{ route('admin.haf.show', $cycle) }}" class="text-xs font-bold text-slate-600 hover:text-red-700">View &rarr;</a>
                                    @else
                                        <span class="text-[10px] font-bold uppercase text-red-700">Current</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Health Claims & Reimbursements Section -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">Medical Claims &amp; Assistance Reimbursements</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Total disbursed: ₱{{ number_format($haf->amount_used, 2) }} across {{ $haf->claims->count() }} transaction(s)</p>
            </div>

            <button @click="claimModal = true" type="button" class="px-3.5 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-800 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Record New Claim</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-5 font-bold">Claim Ref #</th>
                        <th class="py-3 px-4 font-bold">Date</th>
                        <th class="py-3 px-4 font-bold">Type</th>
                        <th class="py-3 px-4 font-bold">Provider &amp; Diagnosis</th>
                        <th class="py-3 px-4 font-bold text-right">Requested</th>
                        <th class="py-3 px-4 font-bold text-right">Approved</th>
                        <th class="py-3 px-4 font-bold text-center">Status</th>
                        <th class="py-3 px-4 font-bold">Receipt</th>
                        <th class="py-3 px-5 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($haf->claims as $claim)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-5 font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $claim->claim_number }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $claim->claim_date ? $claim->claim_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 capitalize">
                                    {{ $claim->claim_type_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $claim->healthcare_provider ?: 'General Healthcare' }}</p>
                                <p class="text-[11px] text-slate-400 line-clamp-1">{{ $claim->diagnosis_reason }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                ₱{{ number_format($claim->amount_requested, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-black text-rose-600 dark:text-rose-400">
                                ₱{{ number_format($claim->amount_approved, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $claim->status_badge_class }}">
                                    {{ ucfirst($claim->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($claim->receipt_file_path)
                                    <a href="{{ Storage::url($claim->receipt_file_path) }}" target="_blank" class="p-1.5 text-red-700 hover:text-red-900 hover:bg-red-50 rounded-lg inline-flex items-center gap-1 text-[11px] font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span>View</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <form action="{{ route('admin.haf.destroy-claim', [$haf, $claim]) }}" method="POST" onsubmit="return confirm('Delete this claim of ₱{{ number_format($claim->amount_approved, 2) }} and restore benefit balance?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Claim">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No medical claims recorded yet</p>
                                <p class="text-xs text-slate-400 mt-0.5">Click &ldquo;Record New Claim&rdquo; when the staff submits medical receipts for reimbursement.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Record Medical Claim with Real-time Balance Checking -->
    <div x-show="claimModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60" @click="claimModal = false"></div>

            <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800">
                <form action="{{ route('admin.haf.store-claim', $haf) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-red-700 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Record Medical Claim</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Available remaining balance: <span class="font-black text-emerald-600">₱{{ number_format($haf->remaining_balance, 2) }}</span></p>
                            </div>
                        </div>
                        <button type="button" @click="claimModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Date of Consultation / Procedure *</label>
                                <input type="date" name="claim_date" value="{{ now('Asia/Manila')->toDateString() }}" required
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assistance Type *</label>
                                <select name="claim_type" required class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                                    <option value="outpatient">Outpatient Consultation</option>
                                    <option value="hospitalization">Hospitalization / Inpatient</option>
                                    <option value="laboratory">Laboratory &amp; Diagnostics</option>
                                    <option value="medicine">Prescription Medicine</option>
                                    <option value="dental">Dental Care</option>
                                    <option value="emergency">Emergency Room Care</option>
                                    <option value="other">Other Medical Assistance</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Hospital / Clinic / Healthcare Provider</label>
                            <input type="text" name="healthcare_provider" placeholder="e.g. Mary Mediatrix Medical Center / St. Patrick Hospital"
                                   class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Diagnosis / Reason for Medical Assistance *</label>
                            <textarea name="diagnosis_reason" rows="2" required placeholder="Describe diagnosis, clinical indications, or required diagnostics..."
                                      class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Amount Requested (₱) *</label>
                                <input type="number" step="0.01" name="amount_requested" x-model="claimRequested" @input="syncClaimApproved()" required min="1" placeholder="e.g. 2500.00"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Amount Approved (₱) *</label>
                                <input type="number" step="0.01" name="amount_approved" x-model="claimApproved" required min="0" placeholder="e.g. 2500.00"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-bold font-mono text-rose-600">
                            </div>
                        </div>

                        <!-- Real-time Balance Warning -->
                        <div x-show="claimApproved > remainingBalance" class="p-3 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900/60 rounded-xl text-xs text-rose-800 dark:text-rose-300 space-y-1">
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Note: Approved amount exceeds remaining coverage</span>
                            </p>
                            <p class="text-[11px] text-rose-700 dark:text-rose-400">
                                Remaining benefit is ₱{{ number_format($haf->remaining_balance, 2) }}. The balance will reach ₱0.00 and the excess of ₱<span x-text="(claimApproved - remainingBalance).toFixed(2)"></span> will be out-of-pocket.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt / Prescription / SOA File (Optional)</label>
                            <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Internal Notes</label>
                            <input type="text" name="remarks" placeholder="Optional notes regarding this reimbursement..."
                                   class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>

                    <div class="p-5 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-end gap-2.5">
                        <button type="button" @click="claimModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-red-700 hover:bg-red-800 rounded-xl transition shadow-sm">
                            Confirm &amp; Disburse Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Renew HAF Membership -->
    <div x-show="renewModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60" @click="renewModal = false"></div>

            <div class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800">
                <form action="{{ route('admin.haf.renew', $haf) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-emerald-700 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </span>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Renew Health Assistance Coverage</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Renew {{ $haf->employee->name }} for another 365-day validity cycle</p>
                            </div>
                        </div>
                        <button type="button" @click="renewModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Renewal Validity</span>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="setRenewValidity(365)" class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800">365 Days</button>
                                    <button type="button" @click="setRenewValidity(180)" class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700">180 Days</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Effective Date</label>
                                    <input type="date" name="start_date" x-model="renewStartDate" @change="updateRenewEndDate()" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Duration (d)</label>
                                    <input type="number" name="validity_days" x-model="renewValidityDays" @input="updateRenewEndDate()" min="1" max="730" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 font-mono">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 mb-1">New Expiration</label>
                                    <input type="date" name="end_date" x-model="renewEndDate" required
                                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2 font-bold text-emerald-600">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Voluntary Contribution (₱) *</label>
                                <input type="number" step="0.01" name="contribution_amount" value="{{ $haf->contribution_amount }}" required min="0"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">HMO Benefit Limit (₱) *</label>
                                <input type="number" step="0.01" name="coverage_amount" value="{{ $haf->coverage_amount }}" required min="0"
                                       class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 font-bold font-mono">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Renewal Proof / Contribution Receipt (Optional)</label>
                            <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Renewal Notes</label>
                            <input type="text" name="notes" placeholder="e.g. Annual renewal for 2027"
                                   class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="p-5 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex items-center justify-end gap-2.5">
                        <button type="button" @click="renewModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl transition shadow-sm">
                            Activate New 365-Day Cycle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
