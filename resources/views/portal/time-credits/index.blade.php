@extends('layouts.portal')

@section('title', 'My Time Credits')
@section('page-title', 'Time Credits & Offset Balance')

@section('content')
<div class="space-y-6">

    <!-- Credit Balance Hero Card (Light Styling) -->
    <div class="bg-gradient-to-r from-emerald-50/90 via-white to-red-50/60 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800 rounded-2xl p-5 sm:p-6 border border-emerald-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block ring-4 ring-emerald-100 dark:ring-emerald-950/60"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400">Offset Leave Balances</span>
            </div>
            <h2 class="text-xl font-black mt-2 tracking-tight text-slate-900 dark:text-slate-100">Available Offset Balance</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md leading-relaxed">
                Time credits accumulate from authorized duties beyond 5:00 PM on weekdays or weekend assignments. Once verified by HR, hours become redeemable for offset leave.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4 w-full md:w-auto">
            <div class="bg-white dark:bg-slate-800/90 px-5 sm:px-6 py-4 rounded-2xl border border-emerald-200/80 dark:border-slate-700 shadow-2xs text-left md:text-right flex-1 md:flex-initial">
                <span class="text-[10px] uppercase text-slate-400 dark:text-slate-500 block font-bold tracking-wider">Approved Balance</span>
                <p class="text-3xl font-black font-mono mt-0.5 text-emerald-700 dark:text-emerald-400">{{ number_format($balance, 2) }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">hrs</span></p>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-1">~{{ round($balance / 8, 1) }} working day(s)</span>
            </div>

            @if(isset($pendingHours) && $pendingHours > 0)
                <div class="bg-amber-50/90 dark:bg-amber-950/60 px-5 py-4 rounded-2xl border border-amber-200 dark:border-amber-900/60 text-left md:text-right flex-1 md:flex-initial">
                    <span class="text-[10px] uppercase text-amber-700 dark:text-amber-400 block font-bold tracking-wider flex items-center gap-1 md:justify-end">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending HR Verification
                    </span>
                    <p class="text-3xl font-black font-mono mt-0.5 text-amber-800 dark:text-amber-300">+{{ number_format($pendingHours, 2) }} <span class="text-xs font-normal text-amber-600 dark:text-amber-400">hrs</span></p>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 block mt-1">({{ round($pendingHours * 60) }} mins awaiting admin approval)</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Earning Rules Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3.5">Policy Rules &amp; Calculation Basis</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 text-xs">
            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700">
                <span class="font-bold text-slate-900 dark:text-slate-100 block mb-0.5">Weekday Past 5:00 PM (e.g. 5:30 PM = 30m)</span>
                <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">Excess time past 17:00 (e.g. 5:30 PM timeout = 30 mins) is recorded and requires HR/Admin approval before becoming valid time credits.</p>
            </div>
            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700">
                <span class="font-bold text-slate-900 dark:text-slate-100 block mb-0.5">Weekend Duty (Sat &amp; Sun)</span>
                <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">Weekend operation shifts are submitted for HR review and credited 1:1 once approved.</p>
            </div>
            <div class="p-4 bg-slate-50/70 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700">
                <span class="font-bold text-slate-900 dark:text-slate-100 block mb-0.5">Overnight Duty Cap</span>
                <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">Shifts running past midnight (00:00) are capped at a maximum of 4 hours credit per HR policy.</p>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">Credit Transaction History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/75 uppercase text-slate-400 dark:text-slate-500 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Activity</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Hours</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Remarks / Reason</th>
                        <th class="px-6 py-3.5 text-right whitespace-nowrap">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($ledger as $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition {{ $item->status === 'pending' ? 'bg-amber-50/30 dark:bg-amber-950/20' : '' }}">
                            <td class="px-6 py-3.5 font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">{{ $item->date ? $item->date->format('D, M d, Y') : '—' }}</td>
                            <td class="px-4 py-3.5 uppercase text-[10px] font-bold whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full {{ $item->type === 'accrual' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60' : 'bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-900/60' }}">
                                    {{ $item->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($item->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60">
                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approved</span>
                                    </span>
                                @elseif($item->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Pending HR</span>
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900/60">
                                        <span>Rejected</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-sm whitespace-nowrap {{ $item->hours >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                                {{ $item->hours >= 0 ? '+' : '' }}{{ number_format($item->hours, 2) }} hrs
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">{{ $item->reason ?? 'Attendance log accrual' }}</td>
                            <td class="px-6 py-3.5 text-right font-bold font-mono text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                @if($item->status === 'approved')
                                    {{ number_format($item->running_balance, 2) }} hrs
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 font-normal italic text-xs">Pending review</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">No time credit transactions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ledger->hasPages())
            <div class="px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $ledger->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
