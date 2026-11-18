@extends('layouts.portal')

@section('title', 'My Loans & Amortization')
@section('page-title', 'My Loans & Amortization')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">My Loans &amp; Amortization</h2>
                @if($activeCount > 0)
                    <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 rounded-full text-xs font-black">
                        {{ $activeCount }} Active
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Monitor your SSS, Pag-IBIG, and company loan balances, repayment progress, and payroll deductions.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('portal.payslips.index') }}"
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                <span>View Payslips</span>
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Loans -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center text-lg font-bold border border-blue-200 dark:border-blue-900/60">
                💳
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Loans</p>
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $activeCount }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $completedLoans->count() }} completed in past</p>
            </div>
        </div>

        <!-- Total Outstanding Balance -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center text-lg font-bold border border-rose-200 dark:border-rose-900/60">
                📉
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Remaining Balance</p>
                <h3 class="text-lg font-black text-rose-700 dark:text-rose-400 tracking-tight">₱{{ number_format($totalBalance, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Principal and interest due</p>
            </div>
        </div>

        <!-- Next Cutoff Deduction -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-200 dark:border-amber-900/60">
                ⏱️
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Deduction / Cutoff</p>
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">₱{{ number_format($totalCutoffDeduction, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Deducted from net pay</p>
            </div>
        </div>

        <!-- Total Repaid to Date -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-200 dark:border-emerald-900/60">
                ✅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Repaid</p>
                <h3 class="text-lg font-black text-emerald-700 dark:text-emerald-400 tracking-tight">₱{{ number_format($totalRepaid, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Across all registered loans</p>
            </div>
        </div>
    </div>

    <!-- Active Loans Cards List -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Active Loans</h3>
            <span class="text-xs text-slate-400 font-semibold">{{ $activeLoans->count() }} active</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse($activeLoans as $loan)
                @php
                    $badge = $loan->type_badge;
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 border {{ $badge['bg'] }} {{ $badge['border'] }}">
                                {{ $badge['icon'] }}
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                <h4 class="text-sm font-black text-slate-900 dark:text-slate-100 mt-1">{{ $loan->loan_name }}</h4>
                                @if($loan->reference_no)
                                    <p class="text-[10px] text-slate-400 font-mono">Ref: {{ $loan->reference_no }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Balance</span>
                            <p class="text-base font-black text-rose-700 dark:text-rose-400">₱{{ number_format($loan->remaining_balance, 2) }}</p>
                            <p class="text-[10px] text-slate-500 font-medium">of ₱{{ number_format($loan->total_amount, 2) }}</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-700 dark:text-slate-300">₱{{ number_format($loan->amount_paid, 2) }} paid</span>
                            <span class="font-black text-red-700 dark:text-red-400">{{ $loan->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full bg-red-600 transition-all" style="width: {{ $loan->progress_percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Details Matrix -->
                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 font-semibold block">Cutoff Deduction</span>
                            <span class="font-black text-slate-800 dark:text-slate-200">₱{{ number_format($loan->amortization_per_cutoff, 2) }}</span>
                            <span class="text-[10px] text-slate-500 block">
                                {{ $loan->deduction_schedule === 'both' ? 'Every Cutoff' : ($loan->deduction_schedule === 'mid_month' ? 'Mid-Month Only' : 'End-Month Only') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-semibold block">Start Date</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $loan->start_date?->format('M d, Y') }}</span>
                            @if($loan->end_date)
                                <span class="text-[10px] text-slate-500 block">Target: {{ $loan->end_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($loan->notes)
                        <p class="text-[11px] text-slate-500 italic bg-amber-50/30 dark:bg-amber-950/20 p-2 rounded-lg border border-amber-200/40 dark:border-amber-900/30">
                            {{ $loan->notes }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="lg:col-span-2 p-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-xl">
                        🎉
                    </div>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">No Active Loans</p>
                    <p class="text-xs text-slate-400 mt-0.5">You currently do not have any active loans or scheduled payroll deductions.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Payments & Deductions Ledger -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Payment &amp; Deduction History</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Verified deductions applied across your payslips and receipts.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-slate-500 uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Loan</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Period / Reference</th>
                        <th class="py-3 px-4 text-right">Amount Deducted</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($recentPayments as $pay)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                {{ $pay->payment_date?->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 dark:text-slate-100">{{ $pay->loan?->loan_name }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($pay->payment_type === 'payroll_deduction')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        🏦 Payroll Deduction
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        💵 Direct Payment
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($pay->payslip)
                                    <a href="{{ route('portal.payslips.view', $pay->payslip) }}" target="_blank" class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                        {{ $pay->payslip->period?->name }}
                                    </a>
                                @else
                                    <span class="font-mono text-slate-600 dark:text-slate-300">{{ $pay->reference_no ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-black text-xs text-emerald-700 dark:text-emerald-400">
                                ₱{{ number_format($pay->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $pay->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-lg">
                                    📜
                                </div>
                                <p class="font-bold text-slate-700 dark:text-slate-300">No payment records found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Deductions applied on published payslips will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recentPayments->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $recentPayments->links() }}
            </div>
        @endif
    </div>

    <!-- Completed Loans Archive -->
    @if($completedLoans->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5">
            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight mb-3">Settled / Completed Loans</h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($completedLoans as $comp)
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold">
                                ✓
                            </span>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-slate-100">{{ $comp->loan_name }}</p>
                                <p class="text-[10px] text-slate-400">Total Paid: ₱{{ number_format($comp->total_amount, 2) }} &bull; {{ $comp->start_date?->format('M Y') }} to {{ $comp->end_date?->format('M Y') ?? 'Payoff' }}</p>
                            </div>
                        </div>

                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            Fully Settled
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
