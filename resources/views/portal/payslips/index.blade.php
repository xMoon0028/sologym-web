@extends('layouts.portal')

@section('title', 'My Payslips')
@section('page-title', 'Issued Payslips')

@section('content')
<div class="space-y-6">

    @if(isset($upcomingPayslip) && $upcomingPayslip)
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950/40 dark:to-blue-950/40 p-4 sm:p-5 rounded-2xl border border-indigo-200 dark:border-indigo-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-indigo-950 dark:text-indigo-200">Upcoming Payslip Scheduled</h4>
                    <p class="text-xs text-indigo-800 dark:text-indigo-300 mt-0.5">
                        Your payslip for <strong class="font-bold text-indigo-950 dark:text-indigo-100">{{ $upcomingPayslip->period->name }}</strong> has been prepared and will unlock on
                        <span class="font-bold underline decoration-indigo-400">{{ $upcomingPayslip->period->released_at->format('F d, Y \a\t h:i A') }}</span>
                        ({{ $upcomingPayslip->period->released_at->diffForHumans() }}).
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300 text-xs font-bold border border-indigo-300 dark:border-indigo-700 whitespace-nowrap self-start sm:self-auto">
                🔒 Locked Until Schedule
            </span>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/50">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Payroll History</h3>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">View and download official computer-generated PDF payslips.</p>
            </div>
            <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1 rounded-full shadow-2xs">{{ $payslips->total() }} Documents</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/75 uppercase text-slate-400 dark:text-slate-500 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5 whitespace-nowrap">Cutoff Period</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Gross Earnings</th>
                        <th class="px-4 py-3.5 whitespace-nowrap text-red-700 dark:text-red-400">Total Deductions</th>
                        <th class="px-4 py-3.5 whitespace-nowrap text-emerald-700 dark:text-emerald-400">Net Take Home</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Disbursement Date</th>
                        <th class="px-6 py-3.5 text-right whitespace-nowrap">PDF Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payslips as $ps)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 dark:text-slate-100 text-sm block">{{ $ps->period->name }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium block mt-0.5">
                                    Visible since: {{ $ps->period->released_at ? $ps->period->released_at->format('M d, Y @ h:i A') : 'Instant release' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-mono whitespace-nowrap">
                                PHP {{ number_format($ps->gross_earnings, 2) }}
                            </td>
                            <td class="px-4 py-4 font-mono text-red-700 dark:text-red-400 whitespace-nowrap">
                                - PHP {{ number_format($ps->total_deductions, 2) }}
                            </td>
                            <td class="px-4 py-4 font-mono font-black text-emerald-700 dark:text-emerald-400 text-sm whitespace-nowrap">
                                PHP {{ number_format($ps->net_pay, 2) }}
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $ps->period->date_of_payment ? $ps->period->date_of_payment->format('F d, Y') : 'Pending' }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 dark:border-slate-700" role="group">
                                    <a href="{{ route('portal.payslips.pdf', [$ps, 'action' => 'view']) }}" target="_blank" class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-700 text-xs font-semibold inline-flex items-center gap-1.5 transition" title="View Payslip in browser">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>View PDF</span>
                                    </a>
                                    <a href="{{ route('portal.payslips.pdf', [$ps, 'action' => 'download']) }}" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold inline-flex items-center gap-1.5 transition" title="Download PDF file">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-14 text-center text-slate-400 dark:text-slate-500">No released payslips found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
            <div class="px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $payslips->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
