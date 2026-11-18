@extends('layouts.admin')

@section('title', 'Loan Statement — ' . $loan->loan_name)
@section('page-title', 'Loan Statement & Payment Ledger')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    paymentModal: false,
    editModal: false
}">

    <!-- Top Navigation Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <a href="{{ route('admin.loans.index') }}"
           class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Loans Monitoring</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            @if($loan->remaining_balance > 0 && $loan->status === 'active')
                <button type="button" @click="paymentModal = true"
                        class="flex-1 sm:flex-initial justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Record Direct Payment</span>
                </button>
            @endif

            <button type="button" @click="editModal = true"
                    class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Loan</span>
            </button>

            @if($loan->payments->isEmpty())
                <form method="POST" action="{{ route('admin.loans.destroy', $loan) }}" onsubmit="return confirm('Are you sure you want to delete this loan record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition" title="Delete Loan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>

    @php
        $badge = $loan->type_badge;
        $statusBadge = $loan->status_badge;
    @endphp

    <!-- Loan Overview Master Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 border {{ $badge['bg'] }} {{ $badge['border'] }}">
                        {{ $badge['icon'] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $loan->loan_name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <span>Borrower: <a href="{{ route('admin.employees.profile', $loan->employee_id) }}" class="font-bold text-red-700 dark:text-red-400 hover:underline">{{ $loan->employee?->name }}</a> ({{ $loan->employee?->employee_no }})</span>
                            <span>&bull;</span>
                            <span>{{ $loan->employee?->department?->name }}</span>
                            @if($loan->reference_no)
                                <span>&bull;</span>
                                <span class="font-mono text-slate-600 dark:text-slate-300">Ref: {{ $loan->reference_no }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Balance Summary Pill -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/70 rounded-2xl border border-slate-200/80 dark:border-slate-700 text-right min-w-[200px]">
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Remaining Balance</p>
                    <p class="text-xl font-black text-rose-700 dark:text-rose-400">₱{{ number_format($loan->remaining_balance, 2) }}</p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $loan->progress_percentage }}% Paid Off</p>
                </div>
            </div>

            <!-- Repayment Progress Bar -->
            <div class="mt-6">
                <div class="flex items-center justify-between text-xs mb-1.5 font-bold">
                    <span class="text-slate-600 dark:text-slate-300">Repayment Progress</span>
                    <span class="text-slate-900 dark:text-slate-100">₱{{ number_format($loan->amount_paid, 2) }} of ₱{{ number_format($loan->total_amount, 2) }}</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                    <div class="h-3 rounded-full {{ $loan->status === 'completed' ? 'bg-emerald-500' : 'bg-red-600' }} transition-all" style="width: {{ $loan->progress_percentage }}%"></div>
                </div>
            </div>
        </div>

        <!-- Financial Breakdown Matrix -->
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-slate-100 dark:divide-slate-800 bg-slate-50/50 dark:bg-slate-850/40 text-xs text-center">
            <div class="p-4">
                <p class="text-[10px] uppercase font-bold text-slate-400">Principal Amount</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5">₱{{ number_format($loan->principal_amount, 2) }}</p>
            </div>
            <div class="p-4">
                <p class="text-[10px] uppercase font-bold text-slate-400">Interest &amp; Charges</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5">₱{{ number_format($loan->interest_amount, 2) }}</p>
            </div>
            <div class="p-4">
                <p class="text-[10px] uppercase font-bold text-slate-400">Cutoff Amortization</p>
                <p class="text-sm font-black text-emerald-700 dark:text-emerald-400 mt-0.5">₱{{ number_format($loan->amortization_per_cutoff, 2) }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">
                    {{ $loan->deduction_schedule === 'both' ? 'Semi-Monthly' : ($loan->deduction_schedule === 'mid_month' ? 'Mid-Month Only' : 'End-Month Only') }}
                </p>
            </div>
            <div class="p-4">
                <p class="text-[10px] uppercase font-bold text-slate-400">Amortization Period</p>
                <p class="text-xs font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ $loan->start_date?->format('M d, Y') }}</p>
                <p class="text-[9px] text-slate-400 mt-0.5">To {{ $loan->end_date ? $loan->end_date->format('M d, Y') : 'Until Fully Paid' }}</p>
            </div>
        </div>

        @if($loan->notes)
            <div class="p-4 bg-amber-50/40 dark:bg-amber-950/20 border-t border-amber-200/50 dark:border-amber-900/40 text-xs text-amber-900 dark:text-amber-300">
                <span class="font-bold">Terms &amp; Remarks:</span> {{ $loan->notes }}
            </div>
        @endif
    </div>

    <!-- Chronological Payment Ledger -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Payment Ledger &amp; Deductions History</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $loan->payments->count() }} payments applied</p>
            </div>

            @if($loan->payments->count() > 0)
                <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 rounded-full text-xs font-bold">
                    Total: ₱{{ number_format($loan->amount_paid, 2) }}
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-slate-500 uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Payment Method / Source</th>
                        <th class="py-3 px-4">Period / Reference</th>
                        <th class="py-3 px-4 text-right">Amount Paid</th>
                        <th class="py-3 px-4">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($loan->payments as $payment)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                {{ $payment->payment_date?->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4">
                                @if($payment->payment_type === 'payroll_deduction')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <span>🏦 Payroll Deduction</span>
                                    </span>
                                @elseif($payment->payment_type === 'manual_payment')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span>💵 Direct Cash</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <span>⚖️ Adjustment</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($payment->payslip)
                                    <a href="{{ route('admin.payroll.show', $payment->payslip->period_id) }}" class="font-bold text-red-700 dark:text-red-400 hover:underline">
                                        {{ $payment->payslip->period?->name }}
                                    </a>
                                @else
                                    <span class="font-mono text-slate-600 dark:text-slate-300">{{ $payment->reference_no ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-black text-xs text-emerald-700 dark:text-emerald-400">
                                ₱{{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $payment->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-lg">
                                    📜
                                </div>
                                <p class="font-bold text-slate-700 dark:text-slate-300">No payments recorded yet</p>
                                <p class="text-xs text-slate-400 mt-0.5">Deductions will automatically appear here once semi-monthly payroll is finalized, or when manual payments are posted.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Record Direct Payment -->
    <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="paymentModal = false"></div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800">
                <form method="POST" action="{{ route('admin.loans.record-payment', $loan) }}">
                    @csrf
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                            <span>💵 Record Direct Loan Payment</span>
                        </h3>
                        <button type="button" @click="paymentModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-3.5 text-xs">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700">
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $loan->loan_name }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">Remaining Balance: ₱{{ number_format($loan->remaining_balance, 2) }}</p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Amount (₱) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00" max="{{ $loan->remaining_balance }}"
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold text-emerald-700 dark:text-emerald-400">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method / Channel</label>
                            <select name="payment_type" class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                                <option value="manual_payment">Direct Cash Payment (Over-the-Counter)</option>
                                <option value="adjustment">Balance Adjustment / Waiver</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt # / Deposit Reference</label>
                            <input type="text" name="reference_no" placeholder="e.g. OR #10492"
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks</label>
                            <input type="text" name="remarks" placeholder="e.g. Employee settled partial balance in cash."
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" @click="paymentModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Post Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Edit Loan Details -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editModal = false"></div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                <form method="POST" action="{{ route('admin.loans.update', $loan) }}">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                            <span>✏️ Edit Loan Settings</span>
                        </h3>
                        <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-3.5 text-xs">
                        <input type="hidden" name="loan_type" value="{{ $loan->loan_type }}">

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Loan Title</label>
                            <input type="text" name="loan_name" value="{{ $loan->loan_name }}" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reference #</label>
                                <input type="text" name="reference_no" value="{{ $loan->reference_no }}"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold">
                                    <option value="active" {{ $loan->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ $loan->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="paused" {{ $loan->status === 'paused' ? 'selected' : '' }}>Paused</option>
                                    <option value="cancelled" {{ $loan->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deduct / Cutoff (₱)</label>
                                <input type="number" step="0.01" name="amortization_per_cutoff" value="{{ $loan->amortization_per_cutoff }}" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold text-emerald-700 dark:text-emerald-400">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Schedule</label>
                                <select name="deduction_schedule" class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                                    <option value="both" {{ $loan->deduction_schedule === 'both' ? 'selected' : '' }}>Both Cutoffs (Semi-Monthly)</option>
                                    <option value="mid_month" {{ $loan->deduction_schedule === 'mid_month' ? 'selected' : '' }}>Mid-Month Only (21st–5th)</option>
                                    <option value="end_month" {{ $loan->deduction_schedule === 'end_month' ? 'selected' : '' }}>End-Month Only (6th–20th)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Principal (₱)</label>
                                <input type="number" step="0.01" name="principal_amount" value="{{ $loan->principal_amount }}" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Total to Pay (₱)</label>
                                <input type="number" step="0.01" name="total_amount" value="{{ $loan->total_amount }}" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-red-700 dark:text-red-400">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Notes</label>
                            <textarea name="notes" rows="2"
                                      class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">{{ $loan->notes }}</textarea>
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Update Loan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
