@extends('layouts.admin')

@section('title', 'Payroll Periods')
@section('page-title', 'Payroll Runs')

@section('content')
<div class="space-y-6">

    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="font-extrabold text-sm text-slate-900 dark:text-slate-100 tracking-tight">Semi-Monthly Payroll Cutoffs</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage pay cycles, import attendance sheets, audit deductions, and release computer-generated payslips.</p>
        </div>
        <a href="{{ route('admin.payroll.create') }}" class="w-full sm:w-auto justify-center px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>New Payroll Cutoff</span>
        </a>
    </div>

    <!-- Periods Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/80 uppercase text-slate-400 dark:text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Cutoff Period</th>
                        <th class="px-4 py-3.5">Date Range</th>
                        <th class="px-4 py-3.5">Disbursement Date</th>
                        <th class="px-4 py-3.5">Enrolled Payslips</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periods as $period)
                        <tr class="hover:bg-slate-50/60 transition group">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.payroll.show', $period) }}" class="font-bold text-slate-900 group-hover:text-red-700 transition text-sm">
                                    {{ $period->name }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-slate-600 font-mono">
                                {{ $period->start_date->format('M d, Y') }} &ndash; {{ $period->end_date->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-800">
                                {{ $period->date_of_payment ? $period->date_of_payment->format('M d, Y') : 'Pending Schedule' }}
                            </td>
                            <td class="px-4 py-4 font-mono font-bold text-slate-700">
                                {{ $period->payslips_count }} <span class="text-slate-400 font-normal">staff</span>
                            </td>
                            <td class="px-4 py-4 uppercase text-[10px] font-bold">
                                @php
                                    $badge = match(true) {
                                        $period->isScheduled() => 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                                        $period->status === 'draft' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                        $period->status === 'under_review' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                        $period->status === 'finalized' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        $period->status === 'released' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    $label = $period->isScheduled() ? 'SCHEDULED: ' . $period->released_at->format('M d, h:i A') : strtoupper(str_replace('_', ' ', $period->status));
                                @endphp
                                <span class="px-2.5 py-1 rounded-full border {{ $badge }} whitespace-nowrap">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                @if(in_array($period->status, ['finalized', 'released']))
                                    <a href="{{ route('admin.payroll.export-all', [$period, 'format' => 'xlsx']) }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs" title="Export Finalized Payroll Register to Excel">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Export Excel</span>
                                    </a>
                                @endif
                                <a href="{{ route('admin.payroll.show', $period) }}" class="px-3.5 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-800 rounded-xl text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs">
                                    <span>Open Review</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <p class="text-xs font-medium text-slate-600">No payroll periods created yet.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Click "New Payroll Cutoff" to initialize the first cycle.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $periods->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
