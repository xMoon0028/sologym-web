@extends('layouts.portal')

@section('title', 'My Attendance')
@section('page-title', 'My Attendance Log')

@section('content')
<div class="space-y-6">

    @if($employee->is_time_in_blocked)
        <div class="p-4 bg-red-600 text-white rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center font-black text-lg shrink-0 mt-0.5">⛔</span>
                <div>
                    <h4 class="text-xs sm:text-sm font-black uppercase tracking-wider text-red-100">
                        Time-In Access Restricted: Overdue Notice to Explain (NTE)
                    </h4>
                    <p class="text-xs text-red-100 mt-0.5 leading-relaxed">
                        Your account is currently restricted from timing in because your 2-day Notice to Explain (NTE) compliance window has lapsed. Please proceed to the Human Resources Department immediately to submit your written NTE and clear this restriction.
                    </p>
                </div>
            </div>
            <a href="{{ route('portal.conduct.index') }}"
               class="px-3.5 py-1.5 bg-white text-red-700 hover:bg-red-50 text-xs font-black rounded-xl transition shadow-xs whitespace-nowrap shrink-0">
                View Notice
            </a>
        </div>
    @endif

    <!-- Month Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('portal.attendance.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider whitespace-nowrap">Month:</span>
            <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                   class="flex-1 sm:flex-initial px-3.5 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            <button type="submit" class="px-3.5 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-semibold transition shadow-xs">
                Filter
            </button>
        </form>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Live RFID Biometrics Active
            </span>
        </div>
    </div>

    <!-- Stats Summary for Selected Month -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Days Present</span>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">{{ $summary['present'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Official Biz</span>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 font-mono">{{ $summary['official_business'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">On Leave</span>
            <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">{{ $summary['leave'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Days Late</span>
            <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 font-mono">{{ $summary['late'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Days Absent</span>
            <p class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 font-mono">{{ $summary['absent'] }}</p>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-slate-100">Daily Punch Logs</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/75 uppercase text-slate-400 dark:text-slate-500 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Time In</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Time Out</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Hours Worked</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Late (mins)</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Undertime (mins)</th>
                        <th class="px-6 py-3.5 text-right whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $rec)
                        <tr class="transition
                            @if($rec->status === 'weekend')
                                bg-slate-50/40 dark:bg-slate-800/20 text-slate-500 dark:text-slate-400
                            @elseif($rec->status === 'absent')
                                bg-rose-50/20 dark:bg-rose-950/10
                            @elseif($rec->status === 'upcoming')
                                opacity-60
                            @else
                                hover:bg-slate-50/60 dark:hover:bg-slate-800/50
                            @endif">
                            <td class="px-6 py-3.5 font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                                {{ $rec->date->format('D, M d, Y') }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $rec->time_in ?? '—' }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $rec->time_out ?? '—' }}</td>
                            <td class="px-4 py-3.5 font-mono whitespace-nowrap">
                                @if($rec->hours_worked > 0)
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $rec->hours_worked }} hrs</span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono whitespace-nowrap {{ $rec->late_minutes > 0 ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ $rec->late_minutes > 0 ? $rec->late_minutes . ' min' : '—' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono whitespace-nowrap {{ $rec->undertime_minutes > 0 ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ $rec->undertime_minutes > 0 ? $rec->undertime_minutes . ' min' : '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    @if($rec->status === 'present')
                                        bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60
                                    @elseif($rec->status === 'official_business')
                                        bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-900/60
                                    @elseif($rec->status === 'leave')
                                        bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900/60
                                    @elseif($rec->status === 'absent')
                                        bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900/60
                                    @elseif($rec->status === 'holiday')
                                        bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900/60
                                    @elseif($rec->status === 'weekend')
                                        bg-slate-100 text-slate-500 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700
                                    @elseif($rec->status === 'upcoming')
                                        bg-slate-50 text-slate-400 border border-slate-200 dark:bg-slate-800/40 dark:text-slate-500 dark:border-slate-800
                                    @elseif($rec->status === 'late')
                                        bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900/60
                                    @else
                                        bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900/60
                                    @endif">
                                    @if($rec->status === 'official_business')
                                        Official Business
                                    @elseif($rec->status === 'leave')
                                        On Leave
                                    @elseif($rec->status === 'absent')
                                        Absent / No Log
                                    @elseif($rec->status === 'weekend')
                                        Weekend
                                    @elseif($rec->status === 'upcoming')
                                        Upcoming
                                    @else
                                        {{ str_replace('_', ' ', $rec->status) }}
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">No attendance records for the selected month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
