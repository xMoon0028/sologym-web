@extends('layouts.portal')

@section('title', 'My Health Assistance Fund (HMO)')
@section('page-title', 'Health Assistance Fund (HMO)')

@section('content')
<div class="space-y-6">

    <!-- Top Executive Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-red-700 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Health Assistance Fund (HMO)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Voluntary staff healthcare benefit • 365-day validity with automated 30-day advance renewal notification</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($currentFund && $currentFund->is_valid)
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Card</span>
                </button>
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $currentFund->status_badge_class }}">
                    @if($currentFund->is_expiring_soon)
                        ⚠️ Expiring in {{ $currentFund->days_remaining }} {{ $currentFund->days_remaining === 1 ? 'Day' : 'Days' }}
                    @else
                        Active ({{ $currentFund->days_remaining }} Days Left)
                    @endif
                </span>
            @elseif($currentFund && $currentFund->is_expired)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 dark:bg-slate-800 dark:text-slate-400">
                    Coverage Expired
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-300">
                    Not Enrolled
                </span>
            @endif
        </div>
    </div>

    <!-- 1-Month Expiration Alert Banner (Appears when 30 days or less remaining) -->
    @if($currentFund && $currentFund->is_expiring_soon)
        <div class="p-5 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/60 dark:to-orange-950/60 border border-amber-300 dark:border-amber-800/80 rounded-3xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-start gap-3.5">
                <span class="p-3 rounded-2xl bg-amber-100 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 flex-shrink-0 animate-pulse">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-black text-amber-950 dark:text-amber-100">
                        Renewal Notice: Your HMO Coverage Expiring in {{ $currentFund->days_remaining }} {{ $currentFund->days_remaining === 1 ? 'Day' : 'Days' }}
                    </h3>
                    <p class="text-xs text-amber-900/90 dark:text-amber-200/90 mt-1 leading-relaxed">
                        Your voluntary Health Assistance Fund membership is valid for 365 days and will expire on <strong class="underline font-bold">{{ $currentFund->end_date->format('F d, Y') }}</strong>.
                        To ensure continuous medical assistance and prevent interruption of health coverage, please arrange your voluntary renewal contribution with the Human Resources (HR) Office.
                    </p>

                    <!-- Renewal steps pill -->
                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px] font-bold text-amber-900 dark:text-amber-300">
                        <span class="px-2.5 py-0.5 rounded-lg bg-white/70 dark:bg-amber-900/60 border border-amber-300/60">Step 1: Visit HR Office</span>
                        <span class="px-2.5 py-0.5 rounded-lg bg-white/70 dark:bg-amber-900/60 border border-amber-300/60">Step 2: Choose Payroll or Cash Contribution</span>
                        <span class="px-2.5 py-0.5 rounded-lg bg-white/70 dark:bg-amber-900/60 border border-amber-300/60">Step 3: Immediate 365-Day Renewal</span>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 self-start md:self-center">
                <span class="px-3.5 py-2 text-xs font-bold text-amber-950 bg-amber-200/90 dark:bg-amber-900 dark:text-amber-100 border border-amber-300 dark:border-amber-700 rounded-xl inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-amber-700 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Renewal Window Open</span>
                </span>
            </div>
        </div>
    @elseif($currentFund && $currentFund->is_expired)
        <div class="p-5 bg-slate-100 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-2xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                        Coverage Expired on {{ $currentFund->end_date->format('F d, Y') }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Your previous 365-day Health Assistance Fund cycle has ended. Please visit the HR Department to make your voluntary renewal contribution for continuous healthcare assistance.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($currentFund)
        <!-- Digital HMO Member Card & Benefit Balances (Robust 3-Column Layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 print:block">
            <!-- Digital Member Card (Left) -->
            <div class="print:max-w-md print:mx-auto">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-800 via-red-900 to-slate-950 text-white p-7 shadow-xl border border-red-700/60 print:shadow-none">
                    <!-- Gold Metallic Smart Chip Graphic -->
                    <div class="absolute top-6 right-6 w-11 h-8 rounded-lg bg-amber-400 shadow-sm border border-amber-300 flex items-center justify-center opacity-90 pointer-events-none">
                        <div class="w-full h-px bg-amber-700/50"></div>
                    </div>

                    <!-- Watermark Logo -->
                    <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
                        <svg class="w-60 h-60" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>

                    <!-- Header -->
                    <div class="flex items-center gap-2.5 border-b border-red-700/60 pb-3 mb-5">
                        <img src="{{ asset('images/lasac_logo.png') }}" alt="LASAC" class="h-7 w-auto brightness-200">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-red-200">Lipa Archdiocesan Social Action Commission</p>
                            <p class="text-xs font-black tracking-tight text-white">Health Assistance Fund (HMO)</p>
                        </div>
                    </div>

                    <!-- Member Identity -->
                    <div class="space-y-3 mb-6">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Member Name</p>
                            <p class="text-xl font-black tracking-tight text-white leading-tight">{{ $employee->name }}</p>
                            <p class="text-xs text-red-200/80 font-medium">{{ $employee->position ?: 'Staff Member' }} • {{ $employee->department->name ?? 'General' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Member Control #</p>
                                <p class="font-mono font-black text-sm tracking-wide text-white">{{ $currentFund->member_number ?: 'HAF-' . $currentFund->id }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-red-300/80 tracking-wider">Employee ID</p>
                                <p class="font-mono font-bold text-xs text-red-100">{{ $employee->employee_no ?: 'EMP-' . $employee->id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Validity Period & 365 Days Meter -->
                    <div class="pt-4 border-t border-red-700/60 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] uppercase text-red-300/80 font-bold">Policy Period:</span>
                                <span class="font-bold text-white ml-1 font-mono text-[11px]">
                                    {{ $currentFund->start_date ? $currentFund->start_date->format('M d, Y') : '-' }} &rarr; {{ $currentFund->end_date ? $currentFund->end_date->format('M d, Y') : '-' }}
                                </span>
                            </div>
                            <span class="text-[10px] font-black px-2.5 py-0.5 rounded-md {{ $currentFund->is_expiring_soon ? 'bg-amber-400 text-amber-950 font-black animate-pulse' : 'bg-white/20 text-white' }}">
                                {{ $currentFund->days_remaining }}d Left
                            </span>
                        </div>

                        <!-- Progress Bar of 365 days -->
                        <div class="w-full bg-red-950/80 rounded-full h-2 overflow-hidden border border-red-700/40">
                            <div class="h-full {{ $currentFund->is_expired ? 'bg-slate-400' : ($currentFund->is_expiring_soon ? 'bg-amber-400' : 'bg-emerald-400') }}"
                                 style="width: {{ $currentFund->validity_progress_percentage }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-red-200/70">
                            <span>365-Day Validity</span>
                            <span>{{ $currentFund->validity_progress_percentage }}% Elapsed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financials / HMO Benefit Balance Overview (Right - Takes 2 Cols) -->
            <div class="lg:col-span-2 space-y-4 print:hidden">
                <!-- 3 Metric Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Total Benefit Limit -->
                    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total HMO Coverage</p>
                        <p class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1">
                            ₱{{ number_format($currentFund->coverage_amount, 2) }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">Annual maximum pool</p>
                    </div>

                    <!-- Assistance Utilized -->
                    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Assistance Claimed</p>
                        <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                            ₱{{ number_format($currentFund->amount_used, 2) }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">{{ $currentFund->claims->count() }} claim(s) ({{ $currentFund->coverage_utilization_percentage }}%)</p>
                    </div>

                    <!-- Available Remaining Balance -->
                    <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200/80 dark:border-emerald-900/60 shadow-sm">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Remaining Balance</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 mt-1">
                            ₱{{ number_format($currentFund->remaining_balance, 2) }}
                        </p>
                        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80 mt-1">Available for use</p>
                    </div>
                </div>

                <!-- Membership Contribution Summary -->
                <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-3">
                    <h4 class="text-xs font-black uppercase text-slate-800 dark:text-slate-200 tracking-wider">Plan &amp; Contribution Record</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <p class="text-slate-400 text-[11px]">Voluntary Contribution</p>
                            <p class="font-black text-slate-800 dark:text-slate-200 mt-0.5">₱{{ number_format($currentFund->contribution_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[11px]">Payment Mode</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5 capitalize">{{ str_replace('_', ' ', $currentFund->payment_method) }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[11px]">Receipt / Ref #</p>
                            <p class="font-mono font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $currentFund->receipt_no ?: 'Recorded in HR' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-[11px]">Validity Duration</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5">365 Days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claims & Utilization History Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden print:hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Medical Claims &amp; Assistance History</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Assistance disbursed by HR under your current 365-day HMO cycle</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-5 font-bold">Claim Reference</th>
                            <th class="py-3.5 px-4 font-bold">Date</th>
                            <th class="py-3.5 px-4 font-bold">Assistance Type</th>
                            <th class="py-3.5 px-4 font-bold">Hospital / Provider</th>
                            <th class="py-3.5 px-4 font-bold">Medical Indication</th>
                            <th class="py-3.5 px-4 font-bold text-right">Amount Approved</th>
                            <th class="py-3.5 px-5 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($currentFund->claims as $claim)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $claim->claim_no ?: 'HAC-' . $claim->id }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $claim->claim_date->format('M d, Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $claim->claim_type_label }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $claim->healthcare_provider ?: 'General Care' }}
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-slate-600 dark:text-slate-400">
                                    {{ $claim->diagnosis_reason }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-rose-600 dark:text-rose-400">
                                    ₱{{ number_format($claim->amount_approved, 2) }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $claim->status_badge_class }}">
                                        {{ $claim->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    No claims recorded yet under your current 365-day HMO coverage cycle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- Empty State (No HAF records at all) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-8 sm:p-12 text-center shadow-sm">
            <div class="max-w-md mx-auto space-y-4">
                <div class="w-16 h-16 mx-auto rounded-3xl bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-400 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100">You Are Not Currently Enrolled in HAF</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    The <strong>Health Assistance Fund (HAF)</strong> is a voluntary staff contribution that functions as organizational HMO coverage, providing members with health assistance for hospitalization, outpatient consultations, diagnostic procedures, and prescription medicines for <strong>365 days</strong>.
                </p>
                <div class="pt-2">
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        Interested in enrolling? Coordinate with the Human Resources (HR) Department to submit your voluntary contribution and activate your 365-day healthcare assistance.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Past Membership History (if any) -->
    @if(isset($historyFunds) && $historyFunds->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden print:hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800">
                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Past HAF Membership Cycles</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Previous annual coverage periods</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-5 font-bold">Plan Year / Control #</th>
                            <th class="py-3.5 px-4 font-bold">Coverage Dates (365d)</th>
                            <th class="py-3.5 px-4 font-bold text-right">Voluntary Contrib.</th>
                            <th class="py-3.5 px-4 font-bold text-right">HMO Limit</th>
                            <th class="py-3.5 px-4 font-bold text-right">Total Claimed</th>
                            <th class="py-3.5 px-5 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($historyFunds as $hFund)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 px-5">
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $hFund->member_number ?: 'HAF-' . $hFund->id }}</span>
                                    <p class="text-[11px] text-slate-400">{{ $hFund->plan_year ?: 'Annual' }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $hFund->start_date ? $hFund->start_date->format('M d, Y') : '-' }} &rarr; {{ $hFund->end_date ? $hFund->end_date->format('M d, Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-700 dark:text-slate-300">
                                    ₱{{ number_format($hFund->contribution_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-slate-100">
                                    ₱{{ number_format($hFund->coverage_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-rose-600 dark:text-rose-400">
                                    ₱{{ number_format($hFund->amount_used, 2) }}
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $hFund->status_badge_class }}">
                                        {{ $hFund->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Medical Assistance Reimbursement Guide -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm space-y-4 print:hidden">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>HMO Medical Assistance Claiming Guidelines</span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 space-y-1.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">1. Covered Medical Services</p>
                <p>Hospitalization (room, board, professional fees), outpatient doctor consultations, prescribed medicines, dental care, emergency room care, and laboratory diagnostics.</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 space-y-1.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">2. Required Claim Documents</p>
                <p>Present to HR: Official Receipts (OR) bearing the hospital or pharmacy TIN, Doctor's Prescription or Clinical Abstract, and Itemized Hospital Statement of Account (for admissions).</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 space-y-1.5">
                <p class="font-bold text-slate-800 dark:text-slate-200">3. 365-Day Renewal Period</p>
                <p>Your policy is valid for exactly 365 days. 30 days before expiration, an automated reminder is sent to your portal and email so you can settle renewal contributions without interruption.</p>
            </div>
        </div>
    </div>
</div>
@endsection
