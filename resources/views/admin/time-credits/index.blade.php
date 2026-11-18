@extends('layouts.admin')

@section('title', 'Offset Time Credits')
@section('page-title', 'Offset Time Credits & Overtime Governance')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ request('tab', ($pendingCount > 0 && (request('pending_page') || request('pending_search') || request('pending_dept')) ? 'pending' : 'balances')) }}',
    showPolicy: false,
    adjustModal: false,
    rejectModal: false,
    selectedEmployeeId: '',
    selectedEmployeeName: '',
    rejectEntryId: null,
    rejectUrl: '',
    openAdjust(empId = '', empName = '') {
        this.selectedEmployeeId = empId;
        this.selectedEmployeeName = empName;
        this.adjustModal = true;
    },
    openReject(entryId, url) {
        this.rejectEntryId = entryId;
        this.rejectUrl = url;
        this.rejectModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs transition-colors">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h2 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                    Offset Time Credits Management
                </h2>
                <span class="text-[10px] bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 font-bold px-2 py-0.5 rounded-full border border-red-200/60 dark:border-red-900/60">
                    Overtime Governance
                </span>
                @if($pendingCount > 0)
                    <span class="text-[10px] bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800/60 inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        {{ $pendingCount }} Awaiting Approval
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Authorize excess shift credits, manage manual grants, and audit offset leave balances</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            <button type="button" @click="showPolicy = !showPolicy"
                    class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition border border-slate-200 dark:border-slate-700 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="showPolicy ? 'Hide Policy' : 'Policy & Rules'">Policy &amp; Rules</span>
                <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showPolicy }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <button type="button" @click="openAdjust()"
                    class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-2 px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>New Adjustment</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Policy Rules Banner -->
    <div x-show="showPolicy" x-cloak x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-gradient-to-r from-red-50/90 via-white to-amber-50/60 dark:from-slate-900 dark:via-slate-900/90 dark:to-slate-800 rounded-2xl p-5 border border-red-200/80 dark:border-slate-800 shadow-xs">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-600 inline-block ring-4 ring-red-100 dark:ring-red-950/60"></span>
                <h3 class="font-extrabold text-xs uppercase tracking-wider text-red-900 dark:text-red-300">
                    Verification &amp; Accrual Policy Rules
                </h3>
            </div>
            <span class="text-[10px] bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 font-bold px-2 py-0.5 rounded-full">
                HR Admin Approval Required
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-xl border border-red-100/80 dark:border-slate-700/80 shadow-2xs">
                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 dark:text-slate-100 mb-1 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                    Weekday Past 5:00 PM (e.g. 5:30 PM = 30m)
                </span>
                <p class="text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed">Logged timeout after 17:00 records candidate overtime, but is ONLY VALID as time credits once HR/Admin approves it.</p>
            </div>
            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-xl border border-amber-100/80 dark:border-slate-700/80 shadow-2xs">
                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 dark:text-slate-100 mb-1 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    Weekend Work Duty
                </span>
                <p class="text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed">Saturday and Sunday emergency relief or operations duty must be verified by HR prior to becoming valid time credits.</p>
            </div>
            <div class="bg-white dark:bg-slate-800/80 p-3.5 rounded-xl border border-emerald-100/80 dark:border-slate-700/80 shadow-2xs">
                <span class="inline-flex items-center gap-1.5 font-bold text-slate-900 dark:text-slate-100 mb-1 text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    Overnight Shift Cap (4h / 240m Max)
                </span>
                <p class="text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed">Duties extending past midnight (00:00) are capped at a maximum of 4 credit hours (240 minutes) per policy.</p>
            </div>
        </div>
    </div>

    <!-- Clean Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
        <button type="button" @click="activeTab = 'balances'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5"
                :class="activeTab === 'balances' 
                    ? 'bg-red-700 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Employee Credit Balances</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-extrabold"
                  :class="activeTab === 'balances' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                {{ $employees->total() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'pending'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2.5"
                :class="activeTab === 'pending' 
                    ? 'bg-amber-600 text-white shadow-xs' 
                    : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Pending Verifications</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-extrabold flex items-center gap-1"
                  :class="activeTab === 'pending' ? 'bg-white/20 text-white' : ($pendingCount > 0 ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300')">
                @if($pendingCount > 0)
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                @endif
                {{ $pendingVerifications->total() }}
            </span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: EMPLOYEE CREDIT BALANCES TABLE                          -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'balances'" x-cloak class="space-y-4">
        <!-- Search and Filter Bar -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 transition-colors">
            <form method="GET" action="{{ route('admin.time-credits.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="balances">

                <!-- Search by name or ID -->
                <div class="relative flex-1 min-w-[220px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="balance_search" value="{{ request('balance_search', request('search')) }}"
                           placeholder="Search employee name or ID..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                </div>

                <!-- Department Filter -->
                <select name="dept" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Balance Status Filter -->
                <select name="balance_filter" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    <option value="">All Balances</option>
                    <option value="positive" {{ request('balance_filter') == 'positive' ? 'selected' : '' }}>With Available Credits</option>
                    <option value="pending" {{ request('balance_filter') == 'pending' ? 'selected' : '' }}>With Pending Overtime</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>

                @if(request()->anyFilled(['balance_search', 'search', 'dept', 'balance_filter']))
                    <a href="{{ route('admin.time-credits.index', ['tab' => 'balances']) }}"
                       class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-semibold px-2 py-1">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Master Balances Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-800/40">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Employee Offset Credit Balances</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Accrued hours available for time-off deduction and approved leave offset</p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1 rounded-full shadow-2xs">
                    {{ $employees->total() }} Staff Listed
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-200">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/60 uppercase text-slate-400 dark:text-slate-400 font-bold text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Employee</th>
                            <th class="px-4 py-3.5">Department / Unit</th>
                            <th class="px-4 py-3.5">Tenure</th>
                            <th class="px-4 py-3.5">Approved Available Balance</th>
                            <th class="px-4 py-3.5">Pending Approval</th>
                            <th class="px-6 py-3.5 text-right">Module Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition group">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 font-bold flex items-center justify-center text-xs flex-shrink-0 border border-red-200/60 dark:border-red-900/60">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.time-credits.show', $emp) }}" class="font-bold text-slate-900 dark:text-slate-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">
                                                {{ $emp->name }}
                                            </a>
                                            <p class="text-slate-400 dark:text-slate-500 font-mono text-[10px]">{{ $emp->employee_no ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $emp->department->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
                                    {{ $emp->years_of_service ? $emp->years_of_service . ' yrs' : '—' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold font-mono {{ $emp->balance > 0 ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                                        {{ number_format($emp->balance, 2) }} hrs
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($emp->pending_hours > 0)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            +{{ number_format($emp->pending_hours, 2) }} hrs pending
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-[11px]">0.00 hrs</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <button type="button"
                                            @click="openAdjust('{{ $emp->id }}', '{{ addslashes($emp->name) }}')"
                                            class="px-3 py-1.5 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white rounded-xl font-bold text-xs transition shadow-2xs inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                        <span>Adjust</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.time-credits.process-attendance', $emp) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Scan attendance logs and submit eligible hours for verification"
                                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-xs transition shadow-2xs">
                                            Scan Logs
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.time-credits.show', $emp) }}" class="px-3 py-1.5 bg-red-50 dark:bg-red-950/60 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 rounded-xl font-bold text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                        <span>Ledger</span>
                                        <svg class="w-3 h-3 text-red-700 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mb-2">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">No employees match the specified filters.</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Try clearing your search query or department filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($employees->hasPages())
                <div class="px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Showing <span class="font-bold text-slate-700 dark:text-slate-200">{{ $employees->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700 dark:text-slate-200">{{ $employees->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700 dark:text-slate-200">{{ $employees->total() }}</span> employees
                    </p>
                    <div>
                        {{ $employees->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: PENDING VERIFICATIONS QUEUE TABLE                       -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'pending'" x-cloak class="space-y-4">
        <!-- Search, Filter and Batch Bar -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 transition-colors">
            <form method="GET" action="{{ route('admin.time-credits.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="pending">

                <!-- Search employee or reason -->
                <div class="relative flex-1 min-w-[220px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="pending_search" value="{{ request('pending_search') }}"
                           placeholder="Search staff name, ID, or shift reason..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                </div>

                <!-- Department Filter -->
                <select name="pending_dept" class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('pending_dept') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter</span>
                </button>

                @if(request()->anyFilled(['pending_search', 'pending_dept']))
                    <a href="{{ route('admin.time-credits.index', ['tab' => 'pending']) }}"
                       class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-semibold px-2 py-1">
                        Reset
                    </a>
                @endif
            </form>

            @if($pendingVerifications->isNotEmpty())
                <form method="POST" action="{{ route('admin.time-credits.approve-all') }}" onsubmit="return confirm('Approve all {{ $pendingCount }} pending time credit records across the organization?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve All ({{ $pendingCount }})</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- Pending Verifications Queue Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between bg-amber-50/50 dark:bg-amber-950/20">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 border border-amber-200 dark:border-amber-800/80 flex items-center justify-center text-amber-800 dark:text-amber-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                            Pending Shift Verifications
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Review and authorize excess time logs before hours are credited to employee balances</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold {{ $pendingVerifications->total() > 0 ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                    {{ $pendingVerifications->total() }} Records
                </span>
            </div>

            @if($pendingVerifications->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-200">
                        <thead class="bg-slate-50/75 dark:bg-slate-800/60 uppercase text-slate-400 dark:text-slate-400 font-bold text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3.5">Employee</th>
                                <th class="px-4 py-3.5">Date &amp; Shift</th>
                                <th class="px-4 py-3.5">Requested Excess Time</th>
                                <th class="px-4 py-3.5">Origin / Reason</th>
                                <th class="px-6 py-3.5 text-right">Verification Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($pendingVerifications as $item)
                                <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">
                                    <td class="px-6 py-3.5">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">{{ $item->employee?->name ?? 'Unknown' }}</p>
                                        <p class="text-slate-400 dark:text-slate-500 font-mono text-[10px]">{{ $item->employee?->department?->name ?? 'Unassigned' }} &bull; {{ $item->employee?->employee_no ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->date ? $item->date->format('D, M d, Y') : '—' }}</p>
                                        @if($item->attendanceRecord)
                                            <p class="text-slate-500 dark:text-slate-400 font-mono text-[10px]">
                                                {{ $item->attendanceRecord->time_in ?? '--' }} &rarr; {{ $item->attendanceRecord->time_out ?? '--' }}
                                                ({{ $item->attendanceRecord->hours_worked }} hrs worked)
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                +{{ round($item->hours * 60) }} mins
                                            </span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">({{ number_format($item->hours, 2) }} hrs)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 max-w-xs">
                                        <p class="truncate" title="{{ $item->reason }}">{{ $item->reason ?? 'Shift overtime' }}</p>
                                    </td>
                                    <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                        <form method="POST" action="{{ route('admin.time-credits.approve', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Verify and credit these hours to employee"
                                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Approve</span>
                                            </button>
                                        </form>
                                        <button type="button"
                                                @click="openReject({{ $item->id }}, '{{ route('admin.time-credits.reject', $item) }}')"
                                                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-red-700 dark:hover:text-red-400 rounded-xl font-semibold text-xs transition shadow-2xs">
                                            Reject
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if($pendingVerifications->hasPages())
                    <div class="px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Showing <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pendingVerifications->firstItem() ?? 0 }}</span> to <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pendingVerifications->lastItem() ?? 0 }}</span> of <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pendingVerifications->total() }}</span> pending verifications
                        </p>
                        <div>
                            {{ $pendingVerifications->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="px-6 py-12 text-center bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center mx-auto mb-2 border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @if(request()->anyFilled(['pending_search', 'pending_dept']))
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">No Pending Verifications Match Criteria</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Try changing or clearing your search filter above.</p>
                    @else
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">All Overtime &amp; Weekend Shift Credits Are Verified</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">New shifts past 5:00 PM or weekend work will automatically appear here for verification upon attendance log scan.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Time Credit Adjustment Modal -->
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="adjustModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.time-credits.store-adjustment') }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Time Credit Adjustment</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Add or deduct offset credits directly from this module</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Instant Approved
                            </span>
                        </div>

                        <!-- Employee Selection (Full list so pagination does not restrict selection) -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Employee *</label>
                            <select name="employee_id" x-model="selectedEmployeeId" required
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <option value="" disabled>-- Select Employee --</option>
                                @foreach($allEmployees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Type (Add vs Deduct) -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Action Type *</label>
                                <select name="type" required
                                        class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                    <option value="add">Grant / Add (+)</option>
                                    <option value="deduct">Deduct / Use (-)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Hours *</label>
                                <input type="number" step="0.25" min="0.25" name="hours" required placeholder="e.g. 4.0 or 8.0"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Effective Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Reason / Justification *</label>
                            <input type="text" name="reason" required placeholder="e.g. Special relief mission grant, manual offset correction"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/60 px-4 sm:px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <button type="button" @click="adjustModal = false" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 shadow-xs transition">
                            Apply Time Credit Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="rejectModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" :action="rejectUrl" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 text-rose-700 dark:text-rose-400">
                            <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center flex-shrink-0 border border-rose-200 dark:border-rose-900/60">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Reject Time Credit Accrual</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">This excess time will not be credited to the employee.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Rejection Reason / Remarks (Optional)</label>
                            <input type="text" name="reason" placeholder="e.g. Unauthorized overtime, meal break overlap"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/60 px-4 sm:px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <button type="button" @click="rejectModal = false" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-bold hover:bg-rose-800 shadow-xs transition">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
