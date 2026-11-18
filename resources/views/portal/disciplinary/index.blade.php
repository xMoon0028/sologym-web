@extends('layouts.portal')

@section('title', 'Conduct & Disciplinary Notices - My Portal')
@section('page-title', 'Conduct & Disciplinary Notices')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Notice Header -->
    <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">Code of Conduct &amp; Memoranda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Official disciplinary records, warnings, and Notices to Explain (NTE) under the LASAC Code of Conduct. Permanently recorded in your official 201 file.
                </p>
            </div>
            <div class="flex items-center gap-2 self-start md:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ $employee->is_time_in_blocked ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-900' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900' }}">
                    <span class="w-2 h-2 rounded-full {{ $employee->is_time_in_blocked ? 'bg-red-600 animate-pulse' : 'bg-emerald-500' }}"></span>
                    <span>Time-In Access: {{ $employee->is_time_in_blocked ? 'Restricted' : 'Active' }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- 4 Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Total Notices -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Notices</span>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1 font-mono">
                {{ $stats['total'] ?? $notices->total() }}
            </p>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">In official 201 record</span>
        </div>

        <!-- Pending NTE -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] sm:text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Pending NTE (2 Days)</span>
            <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">
                {{ $stats['pending'] ?? $pendingExplanationCount }}
            </p>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Awaiting submission to HR</span>
        </div>

        <!-- Overdue (Time-In Blocked) -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] sm:text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider block">Overdue (Blocked)</span>
            <p class="text-2xl sm:text-3xl font-black {{ ($stats['overdue'] ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }} mt-1 font-mono">
                {{ $stats['overdue'] ?? 0 }}
            </p>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Time-in restricted</span>
        </div>

        <!-- Resolved & Closed -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[10px] sm:text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Resolved &amp; Closed</span>
            <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                {{ $stats['resolved'] ?? 0 }}
            </p>
            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Dispositions cleared</span>
        </div>
    </div>

    <!-- Time-In Blocked Warning Banner -->
    @if($employee->is_time_in_blocked)
        <div class="p-5 bg-red-600 text-white rounded-2xl shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-black text-xl shrink-0 mt-0.5">
                    ⛔
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-red-100">
                        Time-In Restricted: Overdue Notice to Explain (NTE)
                    </h3>
                    <p class="text-xs text-red-100 mt-0.5 leading-relaxed">
                        Your two (2) day compliance window to submit your written Notice to Explain to Human Resources has lapsed. You are currently restricted from timing in. Please report to the HR Department immediately to submit your NTE and clear this restriction.
                    </p>
                </div>
            </div>
            <span class="text-xs font-black px-4 py-2 rounded-xl bg-white text-red-700 shadow-2xs whitespace-nowrap shrink-0 self-end sm:self-center">
                Proceed to HR
            </span>
        </div>
    @elseif($pendingExplanationCount > 0)
        <div class="p-4 sm:p-5 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border-2 border-amber-300 dark:border-amber-800/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center font-black text-base shrink-0 mt-0.5">
                    ⚠️
                </div>
                <div>
                    <h3 class="text-sm font-black text-amber-950 dark:text-amber-200">
                        Action Required: You have {{ $pendingExplanationCount }} Notice to Explain (NTE) awaiting submission to HR
                    </h3>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5 leading-relaxed">
                        Please personally submit your written NTE to the HR Department within two (2) days to avoid time-in restriction.
                    </p>
                </div>
            </div>
            <span class="text-xs font-bold px-3.5 py-1.5 rounded-xl bg-amber-600 text-white shadow-2xs whitespace-nowrap shrink-0 self-end sm:self-center">
                2-Day Window
            </span>
        </div>
    @endif

    <!-- Notices List Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notice Records (Permanently Filed in 201)</h3>
            <span class="text-xs font-semibold text-slate-400">{{ $notices->total() }} Total</span>
        </div>

        @if($notices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50/90 dark:bg-slate-800/90 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5 w-36 whitespace-nowrap">Notice # &amp; Date</th>
                            <th class="px-4 py-3.5 min-w-[240px]">Category &amp; Offense</th>
                            <th class="px-4 py-3.5 w-44 whitespace-nowrap">Sanction Imposed</th>
                            <th class="px-4 py-3.5 w-52 whitespace-nowrap">Notice to Explain (NTE)</th>
                            <th class="px-4 py-3.5 w-40 whitespace-nowrap">Status</th>
                            <th class="px-4 py-3.5 w-32 whitespace-nowrap text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($notices as $notice)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono font-black text-slate-900 dark:text-slate-100 block">#{{ $notice->notice_no }}</span>
                                    <span class="text-[11px] text-slate-400 font-medium">{{ $notice->incident_date->format('M d, Y') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 block text-xs">{{ $notice->category_name }}</span>
                                    <span class="text-slate-600 dark:text-slate-400 block text-[11px] mt-0.5 line-clamp-2" title="{{ $notice->offense_title }}">
                                        {{ $notice->offense_title }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">
                                        {{ ucfirst($notice->classification) }} Offense &bull; {{ $notice->offense_occurrence }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border whitespace-nowrap {{ $notice->sanction_badge_class }}">
                                        {{ $notice->short_sanction_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                    @if($notice->requires_explanation)
                                        @if($notice->scanned_nte_path || $notice->nte_received_at)
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Submitted to HR
                                                </span>
                                                <span class="block text-[10px] text-emerald-600 dark:text-emerald-400 font-mono">
                                                    Time-In Active
                                                </span>
                                            </div>
                                        @elseif($notice->is_time_in_blocked)
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black bg-red-100 text-red-900 dark:bg-red-950/80 dark:text-red-300 border border-red-300 dark:border-red-800 whitespace-nowrap">
                                                    ⛔ Overdue (Time-In Blocked)
                                                </span>
                                                <span class="block text-[10px] text-red-600 dark:text-red-400 font-mono">
                                                    Due: {{ $notice->explanation_deadline?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800 whitespace-nowrap">
                                                    ⚠️ Submit to HR (2 Days)
                                                </span>
                                                <span class="block text-[10px] text-amber-700 dark:text-amber-400 font-mono">
                                                    Due: {{ $notice->explanation_deadline?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-xs italic">Notice Only</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border whitespace-nowrap {{ $notice->status_badge['class'] }}">
                                        {{ $notice->status_badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    <a href="{{ route('portal.conduct.show', $notice) }}"
                                       class="whitespace-nowrap inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-2xs {{ $notice->is_time_in_blocked ? 'bg-red-700 hover:bg-red-800 text-white' : ($notice->is_explanation_pending ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700') }}">
                                        <span>View Notice</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($notices->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $notices->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">Full Code of Conduct Compliance</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    You have no disciplinary notices or warnings on record. Thank you for maintaining proper decorum and discipline.
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
