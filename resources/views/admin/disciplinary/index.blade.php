@extends('layouts.admin')

@section('title', 'Disciplinary & Code of Conduct Notices - Admin')
@section('page-title', 'Code of Conduct & Disciplinary Action')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Code of Conduct &amp; Disciplinary Notices</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Issue official Written Warnings, Stern Warnings, and Notices to Explain (NTE) with 2-day compliance deadlines per LASAC Disciplinary Policy.
            </p>
        </div>
        <a href="{{ route('admin.disciplinary.create') }}"
           class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Issue Disciplinary Notice</span>
        </a>
    </div>

    <!-- Quick Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Notices -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Total Notices Issued</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xs">ALL</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono">{{ $totalCount }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">records</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Permanently filed in 201 files</p>
        </div>

        <!-- Pending Staff Explanations -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending Explanations (NTE)</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-xs">2D</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-900 dark:text-amber-200 font-mono">{{ $pendingExplanationCount }}</span>
                <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">awaiting staff reply</span>
            </div>
            <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-2">2-day compliance window active</p>
        </div>

        <!-- Overdue (Time-In Blocked) -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border {{ $overdueCount > 0 ? 'border-red-300 bg-red-50/20 dark:border-red-800/80' : 'border-slate-200/80 dark:border-slate-800' }} shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-red-700 dark:text-red-400">Overdue (Blocked)</span>
                <span class="w-8 h-8 rounded-xl {{ $overdueCount > 0 ? 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 font-extrabold' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xs">
                    ⛔
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black {{ $overdueCount > 0 ? 'text-red-700 dark:text-red-400' : 'text-slate-900 dark:text-white' }} font-mono">{{ $overdueCount }}</span>
                <span class="text-xs {{ $overdueCount > 0 ? 'text-red-600 font-bold' : 'text-slate-500' }}">staff restricted</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Requires scanned NTE to restore</p>
        </div>

        <!-- Resolved Cases -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Resolved &amp; Closed</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">OK</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-900 dark:text-emerald-200 font-mono">{{ $resolvedCount }}</span>
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">concluded</span>
            </div>
            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-2">Duly investigated &amp; recorded</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('admin.disciplinary.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search notice #, staff, offense..."
                       class="w-full pl-9 pr-3 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <select name="employee_id" class="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->department?->code ?? 'GEN' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="sanction_type" class="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                    <option value="">All Sanctions</option>
                    <option value="written_warning" {{ request('sanction_type') == 'written_warning' ? 'selected' : '' }}>Written Warning (WW)</option>
                    <option value="stern_warning" {{ request('sanction_type') == 'stern_warning' ? 'selected' : '' }}>Stern Warning (SW)</option>
                    <option value="suspension" {{ request('sanction_type') == 'suspension' ? 'selected' : '' }}>Suspension (SU)</option>
                    <option value="dismissal" {{ request('sanction_type') == 'dismissal' ? 'selected' : '' }}>Dismissal (DI)</option>
                    <option value="notice_to_explain" {{ request('sanction_type') == 'notice_to_explain' ? 'selected' : '' }}>Notice to Explain (NTE)</option>
                    <option value="counseling" {{ request('sanction_type') == 'counseling' ? 'selected' : '' }}>Verbal Counseling</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">
                    <option value="">All Statuses</option>
                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Notice Issued</option>
                    <option value="explanation_submitted" {{ request('status') == 'explanation_submitted' ? 'selected' : '' }}>NTE Submitted to HR</option>
                    <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Investigation</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Case Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'employee_id', 'sanction_type', 'status']))
                    <a href="{{ route('admin.disciplinary.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notices Records Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($notices->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5 w-36 whitespace-nowrap">Notice # &amp; Date</th>
                            <th class="px-5 py-3.5 min-w-[190px]">Employee</th>
                            <th class="px-5 py-3.5 min-w-[170px] whitespace-nowrap">Category &amp; Classification</th>
                            <th class="px-5 py-3.5 min-w-[240px]">Offense / Incident</th>
                            <th class="px-5 py-3.5 min-w-[160px] whitespace-nowrap">Sanction</th>
                            <th class="px-5 py-3.5 min-w-[160px] whitespace-nowrap">Explanation (NTE)</th>
                            <th class="px-5 py-3.5 min-w-[160px] whitespace-nowrap">Status</th>
                            <th class="px-5 py-3.5 w-28 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($notices as $notice)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <!-- Notice # & Date -->
                                <td class="px-5 py-4 whitespace-nowrap align-top">
                                    <span class="font-mono font-black text-slate-900 dark:text-slate-100 text-xs block">#{{ $notice->notice_no }}</span>
                                    <div class="flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>{{ $notice->incident_date->format('M d, Y') }}</span>
                                    </div>
                                </td>

                                <!-- Employee -->
                                <td class="px-5 py-4 align-top">
                                    <div class="font-bold text-slate-900 dark:text-slate-100 text-xs">
                                        @if($notice->employee)
                                            <a href="{{ route('admin.employees.profile', $notice->employee) }}" class="hover:text-red-700 dark:hover:text-red-400 hover:underline">
                                                {{ $notice->employee->name }}
                                            </a>
                                        @else
                                            <span>N/A</span>
                                        @endif
                                    </div>
                                    <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $notice->employee?->position ?? 'Staff' }} &bull; {{ $notice->employee?->department?->name ?? 'General' }}
                                    </span>
                                </td>

                                <!-- Category & Classification -->
                                <td class="px-5 py-4 whitespace-nowrap align-top">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block text-xs">{{ $notice->category_name }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase mt-1 border {{ $notice->classification === 'major' ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900' : ($notice->classification === 'special' ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900' : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700') }}">
                                        {{ ucfirst($notice->classification) }} Offense
                                    </span>
                                </td>

                                <!-- Offense / Incident -->
                                <td class="px-5 py-4 align-top max-w-xs sm:max-w-sm">
                                    <p class="text-xs font-medium text-slate-800 dark:text-slate-200 line-clamp-2 leading-relaxed" title="{{ $notice->offense_title }}">
                                        {{ $notice->offense_title }}
                                    </p>
                                    <span class="inline-block mt-1 text-[11px] text-slate-400">
                                        Occurrence: <strong class="text-slate-600 dark:text-slate-300">{{ $notice->offense_occurrence }}</strong>
                                    </span>
                                </td>

                                <!-- Sanction (Clean Badge) -->
                                <td class="px-5 py-4 whitespace-nowrap align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $notice->sanction_badge_class }} whitespace-nowrap">
                                        {{ $notice->short_sanction_label }}
                                    </span>
                                </td>

                                <!-- Explanation (NTE) -->
                                <td class="px-5 py-4 whitespace-nowrap align-top">
                                    @if($notice->requires_explanation)
                                        @if($notice->scanned_nte_path || $notice->nte_received_at)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>NTE Attached</span>
                                            </span>
                                        @elseif($notice->is_time_in_blocked)
                                            <div class="space-y-0.5 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold text-red-900 bg-red-100 border border-red-300 dark:bg-red-950/80 dark:text-red-200 dark:border-red-800">
                                                    <span>⛔ Time-In Blocked</span>
                                                </span>
                                                <span class="block text-[10px] font-mono text-red-600 dark:text-red-400 font-semibold">
                                                    Lapsed: {{ $notice->explanation_deadline?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="space-y-0.5 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-amber-900 bg-amber-50 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-200 dark:border-amber-800">
                                                    Pending 2-Day Reply
                                                </span>
                                                <span class="block text-[10px] font-mono text-slate-400">
                                                    Due: {{ $notice->explanation_deadline?->format('M d, Y') }}
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-xs italic">Not Required</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4 whitespace-nowrap align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $notice->status_badge['class'] }} whitespace-nowrap">
                                        {{ $notice->status_badge['label'] }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4 whitespace-nowrap text-right align-top">
                                    <a href="{{ route('admin.disciplinary.show', $notice) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 rounded-xl text-xs font-bold transition shadow-2xs whitespace-nowrap group">
                                        <span>View Notice</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No Disciplinary Notices Found</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                    Either no disciplinary notices match your filter, or all employees are currently in full compliance with the Code of Conduct.
                </p>
                <a href="{{ route('admin.disciplinary.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition">
                    + Issue First Notice
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
