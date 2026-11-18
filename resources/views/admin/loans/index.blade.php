@extends('layouts.admin')

@section('title', 'Loans Monitoring')
@section('page-title', 'Employee Loans Monitoring')

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    paymentModal: false,
    editModal: false,
    selectedLoan: null,
    paymentLoan: null,
    loanType: 'sss',
    loanName: 'SSS Salary Loan',
    principal: 0,
    interest: 0,
    monthlyAmort: 0,
    cutoffAmort: 0,
    totalAmount: 0,
    schedule: 'both',
    syncCalculations() {
        this.totalAmount = (parseFloat(this.principal) || 0) + (parseFloat(this.interest) || 0);
        if (this.monthlyAmort > 0 && (!this.cutoffAmort || this.cutoffAmort === 0)) {
            this.cutoffAmort = (this.monthlyAmort / 2).toFixed(2);
        }
    },
    setLoanTypePresets(type) {
        this.loanType = type;
        if (type === 'sss') {
            this.loanName = 'SSS Salary Loan';
            this.schedule = 'end_month';
        } else if (type === 'pagibig') {
            this.loanName = 'Pag-IBIG Multi-Purpose Loan';
            this.schedule = 'mid_month';
        } else if (type === 'salary') {
            this.loanName = 'LASAC Company Salary Loan';
            this.schedule = 'both';
        } else if (type === 'emergency') {
            this.loanName = 'Emergency Assistance Loan';
            this.schedule = 'both';
        } else {
            this.loanName = 'Employee Loan';
            this.schedule = 'both';
        }
    },
    openPayment(loan) {
        this.paymentLoan = loan;
        this.paymentModal = true;
    },
    openEdit(loan) {
        this.selectedLoan = loan;
        this.editModal = true;
    }
}">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Employee Loans Monitoring</h2>
                <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 rounded-full text-xs font-black">
                    {{ $totalActiveCount }} Active
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Centralized monitoring for SSS, Pag-IBIG, Company Salary, and Emergency loan balances, automated payroll deductions, and payment ledgers.
            </p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('admin.loans.export') }}"
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV</span>
            </a>

            <button type="button" @click="createModal = true; setLoanTypePresets('sss')"
                    class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Record New Loan</span>
            </button>
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
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ number_format($totalActiveCount) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $typeCounts['all'] }} total recorded</p>
            </div>
        </div>

        <!-- Total Outstanding Balance -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center text-lg font-bold border border-rose-200 dark:border-rose-900/60">
                📉
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Outstanding Balance</p>
                <h3 class="text-lg font-black text-rose-700 dark:text-rose-400 tracking-tight">₱{{ number_format($totalOutstanding, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Principal &amp; charges remaining</p>
            </div>
        </div>

        <!-- Cutoff Amortization Total -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-200 dark:border-amber-900/60">
                ⏱️
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payroll Amort / Cutoff</p>
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">₱{{ number_format($totalCutoffDeductions, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Scheduled deduction per cycle</p>
            </div>
        </div>

        <!-- Total Collected / Repaid -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-200 dark:border-emerald-900/60">
                ✅
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Repaid to Date</p>
                <h3 class="text-lg font-black text-emerald-700 dark:text-emerald-400 tracking-tight">₱{{ number_format($totalCollected, 2) }}</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $statusCounts['completed'] }} loans fully settled</p>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
        <!-- Loan Type Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs font-bold">
            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type', 'all') === 'all' ? 'bg-red-700 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>All Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type', 'all') === 'all' ? 'bg-red-800 text-red-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['all'] }}</span>
            </a>

            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'sss'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type') === 'sss' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🏛️ SSS Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type') === 'sss' ? 'bg-blue-700 text-blue-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['sss'] }}</span>
            </a>

            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'pagibig'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type') === 'pagibig' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🏠 Pag-IBIG Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type') === 'pagibig' ? 'bg-emerald-700 text-emerald-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['pagibig'] }}</span>
            </a>

            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'salary'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type') === 'salary' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>💵 Salary Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type') === 'salary' ? 'bg-amber-700 text-amber-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['salary'] }}</span>
            </a>

            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'emergency'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type') === 'emergency' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🚨 Emergency Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type') === 'emergency' ? 'bg-rose-700 text-rose-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['emergency'] }}</span>
            </a>

            <a href="{{ route('admin.loans.index', array_merge(request()->query(), ['type' => 'other'])) }}"
               class="px-3.5 py-1.5 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ request('type') === 'other' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>📋 Other Loans</span>
                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ request('type') === 'other' ? 'bg-purple-700 text-purple-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">{{ $typeCounts['other'] }}</span>
            </a>
        </div>

        <!-- Search & Secondary Selectors -->
        <form method="GET" action="{{ route('admin.loans.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <input type="hidden" name="type" value="{{ request('type', 'all') }}">

            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by employee name, employee #, or loan reference..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-red-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <select name="status" class="w-full sm:w-40 py-2 px-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                <option value="all">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active ({{ $statusCounts['active'] }})</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed ({{ $statusCounts['completed'] }})</option>
                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused ({{ $statusCounts['paused'] }})</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled ({{ $statusCounts['cancelled'] }})</option>
            </select>

            <select name="department_id" class="w-full sm:w-48 py-2 px-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>

            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'department_id']) || request('type', 'all') !== 'all')
                    <a href="{{ route('admin.loans.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Loans Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-extrabold tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Loan Details</th>
                        <th class="py-3 px-4 text-right">Total Obligation</th>
                        <th class="py-3 px-4">Repayment Progress</th>
                        <th class="py-3 px-4 text-right">Balance Remaining</th>
                        <th class="py-3 px-4">Amort / Schedule</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($loans as $loan)
                        @php
                            $badge = $loan->type_badge;
                            $statusBadge = $loan->status_badge;
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <!-- Employee -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center font-black text-xs flex-shrink-0">
                                        {{ strtoupper(substr($loan->employee?->name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.employees.profile', $loan->employee_id) }}" class="font-bold text-slate-900 dark:text-slate-100 hover:text-red-700 dark:hover:text-red-400 transition truncate block">
                                            {{ $loan->employee?->name }}
                                        </a>
                                        <p class="text-[10px] text-slate-400">{{ $loan->employee?->employee_no }} &bull; {{ $loan->employee?->department?->name }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Loan Details -->
                            <td class="py-3 px-4">
                                <div class="space-y-0.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['border'] }}">
                                        <span>{{ $badge['icon'] }}</span>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                    <p class="font-bold text-slate-900 dark:text-slate-100 text-xs">{{ $loan->loan_name }}</p>
                                    @if($loan->reference_no)
                                        <p class="text-[10px] text-slate-400 font-mono">Ref: {{ $loan->reference_no }}</p>
                                    @endif
                                </div>
                            </td>

                            <!-- Total Obligation -->
                            <td class="py-3 px-4 text-right font-medium">
                                <p class="font-bold text-slate-900 dark:text-slate-100">₱{{ number_format($loan->total_amount, 2) }}</p>
                                @if($loan->interest_amount > 0)
                                    <p class="text-[10px] text-slate-400">Principal: ₱{{ number_format($loan->principal_amount, 2) }}</p>
                                @endif
                            </td>

                            <!-- Repayment Progress -->
                            <td class="py-3 px-4 min-w-[140px]">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] mb-1">
                                        <span class="font-bold text-slate-700 dark:text-slate-300">₱{{ number_format($loan->amount_paid, 2) }}</span>
                                        <span class="font-extrabold text-slate-500">{{ $loan->progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full {{ $loan->status === 'completed' ? 'bg-emerald-500' : 'bg-red-600' }} transition-all" style="width: {{ $loan->progress_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Remaining Balance -->
                            <td class="py-3 px-4 text-right">
                                <p class="font-black text-xs {{ $loan->remaining_balance > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                    ₱{{ number_format($loan->remaining_balance, 2) }}
                                </p>
                                <p class="text-[10px] text-slate-400">Started {{ $loan->start_date?->format('M Y') }}</p>
                            </td>

                            <!-- Amort / Schedule -->
                            <td class="py-3 px-4">
                                <p class="font-bold text-slate-900 dark:text-slate-100">₱{{ number_format($loan->amortization_per_cutoff, 2) }} <span class="text-[10px] font-normal text-slate-400">/ cutoff</span></p>
                                <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                    @if($loan->deduction_schedule === 'both')
                                        Every Cutoff
                                    @elseif($loan->deduction_schedule === 'mid_month')
                                        Mid-Month (21st–5th)
                                    @else
                                        End-Month (6th–20th)
                                    @endif
                                </p>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black border {{ $statusBadge['bg'] }} {{ $statusBadge['text'] }} {{ $statusBadge['border'] }}">
                                    {{ $statusBadge['label'] }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.loans.show', $loan) }}"
                                       class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1"
                                       title="View Payment Ledger">
                                        <span>Ledger</span>
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>

                                    @if($loan->remaining_balance > 0 && $loan->status === 'active')
                                        <button type="button" @click="openPayment({{ $loan->toJson() }})"
                                                class="px-2 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-bold transition"
                                                title="Record Direct Payment">
                                            + Pay
                                        </button>
                                    @endif

                                    <button type="button" @click="openEdit({{ $loan->toJson() }})"
                                            class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                            title="Edit Loan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-xl">
                                    💳
                                </div>
                                <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">No loan records found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Click "+ Record New Loan" to register an employee loan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($loans->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Record New Loan -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="createModal = false"></div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-xl sm:w-full border border-slate-200 dark:border-slate-800">
                <form method="POST" action="{{ route('admin.loans.store') }}">
                    @csrf
                    <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                            <span>💳 Record New Employee Loan</span>
                        </h3>
                        <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 text-xs">
                        <!-- Employee Selection -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Employee <span class="text-red-500">*</span></label>
                            <select name="employee_id" required class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_no }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Loan Type Buttons -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Loan Category <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                <button type="button" @click="setLoanTypePresets('sss')"
                                        :class="loanType === 'sss' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                        class="p-2 text-center rounded-xl border font-bold text-xs transition">
                                    🏛️ SSS
                                </button>
                                <button type="button" @click="setLoanTypePresets('pagibig')"
                                        :class="loanType === 'pagibig' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                        class="p-2 text-center rounded-xl border font-bold text-xs transition">
                                    🏠 Pag-IBIG
                                </button>
                                <button type="button" @click="setLoanTypePresets('salary')"
                                        :class="loanType === 'salary' ? 'bg-amber-600 text-white border-amber-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                        class="p-2 text-center rounded-xl border font-bold text-xs transition">
                                    💵 Salary
                                </button>
                                <button type="button" @click="setLoanTypePresets('emergency')"
                                        :class="loanType === 'emergency' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                        class="p-2 text-center rounded-xl border font-bold text-xs transition">
                                    🚨 Emergency
                                </button>
                                <button type="button" @click="setLoanTypePresets('other')"
                                        :class="loanType === 'other' ? 'bg-purple-600 text-white border-purple-600' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
                                        class="p-2 text-center rounded-xl border font-bold text-xs transition">
                                    📋 Other
                                </button>
                            </div>
                            <input type="hidden" name="loan_type" :value="loanType">
                        </div>

                        <!-- Loan Title & Reference -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Loan Title <span class="text-red-500">*</span></label>
                                <input type="text" name="loan_name" x-model="loanName" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reference / Application #</label>
                                <input type="text" name="reference_no" placeholder="e.g. SSS-2026-90412"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                        </div>

                        <!-- Principal & Interest -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Principal Amount (₱) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="principal_amount" x-model="principal" @input="syncCalculations()" required placeholder="0.00"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Interest / Fees (₱)</label>
                                <input type="number" step="0.01" name="interest_amount" x-model="interest" @input="syncCalculations()" placeholder="0.00"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Total to Repay (₱)</label>
                                <input type="number" step="0.01" name="total_amount" x-model="totalAmount" required
                                       class="w-full py-2 px-3 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-black text-red-700 dark:text-red-400">
                            </div>
                        </div>

                        <!-- Amortization & Schedule -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Monthly Amort (₱)</label>
                                <input type="number" step="0.01" name="monthly_amortization" x-model="monthlyAmort" @input="cutoffAmort = (monthlyAmort / 2).toFixed(2)" placeholder="0.00"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deduct / Cutoff (₱) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" name="amortization_per_cutoff" x-model="cutoffAmort" required placeholder="0.00"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold text-emerald-700 dark:text-emerald-400">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deduction Schedule <span class="text-red-500">*</span></label>
                                <select name="deduction_schedule" x-model="schedule" required class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                                    <option value="both">Both Cutoffs (Semi-Monthly)</option>
                                    <option value="mid_month">Mid-Month Only (21st–5th)</option>
                                    <option value="end_month">End-Month Only (6th–20th)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Start & End Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deductions Start Date <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" required value="{{ date('Y-m-d') }}"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Target End Date</label>
                                <input type="date" name="end_date"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Terms Notes</label>
                            <textarea name="notes" rows="2" placeholder="e.g. Approved 24-month term with SSS Lipa branch."
                                      class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Save &amp; Activate Loan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Record Direct Payment -->
    <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="paymentModal = false"></div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-800">
                <form method="POST" :action="'{{ url('admin/loans') }}/' + (paymentLoan ? paymentLoan.id : '') + '/payments'">
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
                            <p class="font-bold text-slate-900 dark:text-slate-100" x-text="paymentLoan ? paymentLoan.loan_name : ''"></p>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="'Remaining Balance: ₱' + (paymentLoan ? Number(paymentLoan.remaining_balance).toLocaleString('en-US', {minimumFractionDigits: 2}) : '0.00')"></p>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Amount (₱) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00"
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

    <!-- Modal: Edit Loan Details / Status -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editModal = false"></div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                <form method="POST" :action="'{{ url('admin/loans') }}/' + (selectedLoan ? selectedLoan.id : '')">
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

                    <div class="p-6 space-y-3.5 text-xs" x-if="selectedLoan">
                        <input type="hidden" name="loan_type" :value="selectedLoan.loan_type">

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Loan Title</label>
                            <input type="text" name="loan_name" :value="selectedLoan.loan_name" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reference #</label>
                                <input type="text" name="reference_no" :value="selectedLoan.reference_no"
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" :value="selectedLoan.status" class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold">
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="paused">Paused</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Deduct / Cutoff (₱)</label>
                                <input type="number" step="0.01" name="amortization_per_cutoff" :value="selectedLoan.amortization_per_cutoff" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-bold text-emerald-700 dark:text-emerald-400">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Schedule</label>
                                <select name="deduction_schedule" :value="selectedLoan.deduction_schedule" class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                                    <option value="both">Both Cutoffs (Semi-Monthly)</option>
                                    <option value="mid_month">Mid-Month Only (21st–5th)</option>
                                    <option value="end_month">End-Month Only (6th–20th)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Principal (₱)</label>
                                <input type="number" step="0.01" name="principal_amount" :value="selectedLoan.principal_amount" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Total to Pay (₱)</label>
                                <input type="number" step="0.01" name="total_amount" :value="selectedLoan.total_amount" required
                                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-red-700 dark:text-red-400">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Notes</label>
                            <textarea name="notes" rows="2" :value="selectedLoan.notes"
                                      class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200"></textarea>
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
