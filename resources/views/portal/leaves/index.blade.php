@extends('layouts.portal')

@section('title', 'My Leave Requests - Portal')
@section('page-title', 'My Leave Requests')

@section('content')
<div class="space-y-6" x-data="{
    applyModal: false,
    trackModal: false,
    selectedRequest: null,
    reviewModal: false,
    reviewTarget: null,
    leaveType: 'vacation',
    isHalfDay: false,
    todayDate: '{{ now()->format('Y-m-d') }}',
    advanceDate: '{{ now()->addDays(3)->format('Y-m-d') }}',
    startDate: '{{ now()->addDays(3)->format('Y-m-d') }}',
    endDate: '{{ now()->addDays(3)->format('Y-m-d') }}',
    isVacation() {
        return this.leaveType === 'vacation' || this.leaveType === 'vacation_leave';
    },
    isSick() {
        return this.leaveType === 'sick' || this.leaveType === 'sick_leave';
    },
    onTypeChange() {
        if (this.isVacation()) {
            if (this.startDate < this.advanceDate) {
                this.startDate = this.advanceDate;
            }
            if (this.endDate < this.startDate) {
                this.endDate = this.startDate;
            }
        } else if (this.isSick()) {
            if (this.startDate > this.todayDate) {
                this.startDate = this.todayDate;
            }
            if (this.endDate > this.todayDate) {
                this.endDate = this.todayDate;
            }
        }
    },
    openTrack(req) {
        this.selectedRequest = req;
        this.trackModal = true;
    },
    openReview(req) {
        this.reviewTarget = req;
        this.reviewModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Leave Credits &amp; Applications</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Vacation Leave requires 3 days advance notice. Sick Leave must be filed within 3 days of return to work. 1-day (24h) approval window per stage.</p>
        </div>
        <button type="button" @click="applyModal = true" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Apply for Leave</span>
        </button>
    </div>

    <!-- Leave Balances Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Vacation Leave -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Vacation Leave</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center font-bold text-xs">VL</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($employee->remaining_vacation_leave, 1) }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">/ {{ number_format($employee->vacation_leave_credits, 1) }} days avail</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Planned time off &amp; rest</p>
        </div>

        <!-- Sick Leave -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Sick Leave</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center font-bold text-xs">SL</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($employee->remaining_sick_leave, 1) }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">/ {{ number_format($employee->sick_leave_credits, 1) }} days avail</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Medical recovery &amp; appointments</p>
        </div>

        <!-- Emergency Leave -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Emergency Leave</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-xs">EL</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($employee->remaining_emergency_leave, 1) }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">/ {{ number_format($employee->emergency_leave_credits, 1) }} days avail</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Urgent personal events</p>
        </div>

        <!-- Time Off Leave -->
        <div class="p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Time Off Leave</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 flex items-center justify-center font-bold text-xs">TOL</span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-purple-900 dark:text-purple-200 font-mono">{{ number_format($employee->time_credit_balance, 2) }}</span>
                <span class="text-xs text-purple-600 dark:text-purple-400 font-semibold">hrs ({{ floor($employee->time_credit_balance / 8) }}d {{ fmod($employee->time_credit_balance, 8) }}h)</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">Earned overtime/weekend credits (8 hrs = 1 day)</p>
        </div>
    </div>

    <!-- SECTION HEAD REVIEW QUEUE (If user has section_head role) -->
    @if($isSectionHead)
        <div class="bg-amber-50/60 dark:bg-amber-950/30 rounded-2xl border border-amber-200/80 dark:border-amber-800/60 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center text-xs font-black">1</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Section Head Approval Queue (1-Day Limit)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Submissions from your section staff awaiting Stage 1 approval within 24 hours.</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                    {{ $pendingDepartmentRequests->count() }} Pending
                </span>
            </div>

            @if($pendingDepartmentRequests->count() > 0)
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-amber-200/70 dark:border-amber-900/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                            <thead class="bg-amber-50/50 dark:bg-amber-950/50 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] border-b border-amber-200/60 dark:border-amber-900/50">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Leave Type</th>
                                    <th class="px-4 py-3">Duration &amp; Dates</th>
                                    <th class="px-4 py-3">Reason</th>
                                    <th class="px-4 py-3">1-Day Deadline</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($pendingDepartmentRequests as $depReq)
                                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $depReq->employee->name }}
                                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-normal">{{ $depReq->employee->position }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $depReq->badge_class }}">
                                                {{ $depReq->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $depReq->days_count }} {{ Str::plural('day', $depReq->days_count) }}</span>
                                            <span class="block text-[10px] text-slate-500 dark:text-slate-400">{{ $depReq->start_date->format('M d, Y') }} - {{ $depReq->end_date->format('M d, Y') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $depReq->reason }}">
                                            {{ $depReq->reason }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] font-semibold text-amber-800 dark:text-amber-300">
                                            {{ $depReq->stage_expires_at ? $depReq->stage_expires_at->timezone('Asia/Manila')->format('M d, h:i A') : '24h Window' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" @click="openReview({{ json_encode($depReq) }})"
                                                    class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                                Review &amp; Approve
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400 italic bg-white/60 dark:bg-slate-900/60 p-4 rounded-xl border border-dashed border-amber-200 dark:border-amber-900/50 text-center">
                    All clear! No section staff requests are pending your review.
                </p>
            @endif
        </div>
    @endif

    <!-- EXECUTIVE OFFICE / EXECUTIVE DIRECTOR APPROVAL QUEUE -->
    @if($isExecutive)
        <div class="bg-purple-50/60 dark:bg-purple-950/30 rounded-2xl border border-purple-200/80 dark:border-purple-800/60 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-purple-200 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300 flex items-center justify-center text-xs font-black">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                            {{ ($isExecutiveDirector ?? false) ? 'Executive Director & Executive Office Approval Queue (1-Day Limit)' : 'Executive Office Final Approval Queue (1-Day Limit)' }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Final authorizations awaiting sign-off within the 1-day approval window.</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-900 dark:text-purple-300 border border-purple-300 dark:border-purple-700">
                    {{ $pendingExecutiveRequests->count() }} Pending
                </span>
            </div>

            @if($pendingExecutiveRequests->count() > 0)
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-purple-200/70 dark:border-purple-900/50 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                            <thead class="bg-purple-50/50 dark:bg-purple-950/50 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] border-b border-purple-200/60 dark:border-purple-900/50">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Leave Type</th>
                                    <th class="px-4 py-3">Duration &amp; Dates</th>
                                    <th class="px-4 py-3">Reason</th>
                                    <th class="px-4 py-3">1-Day Deadline</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($pendingExecutiveRequests as $execReq)
                                    <tr class="hover:bg-purple-50/30 dark:hover:bg-purple-950/20 transition">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $execReq->employee?->name }}
                                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 font-normal">{{ $execReq->employee?->position ?? 'Staff' }} &bull; {{ $execReq->employee?->department?->name ?? 'No Dept' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $execReq->badge_class }}">
                                                {{ $execReq->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $execReq->days_count }} {{ Str::plural('day', $execReq->days_count) }}</span>
                                            <span class="block text-[10px] text-slate-500 dark:text-slate-400">{{ $execReq->start_date->format('M d, Y') }} - {{ $execReq->end_date->format('M d, Y') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $execReq->reason }}">
                                            {{ $execReq->reason }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] font-semibold text-purple-800 dark:text-purple-300">
                                            {{ $execReq->stage_expires_at ? $execReq->stage_expires_at->timezone('Asia/Manila')->format('M d, h:i A') : '24h Window' }}
                                        </td>
                                        <td class="px-4 py-3 text-right space-x-1">
                                            <a href="{{ route('portal.leaves.show', $execReq) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold">
                                                View Details
                                            </a>
                                            <button type="button" @click="openReview({{ json_encode($execReq) }})"
                                                    class="px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                                Review &amp; Final Approve
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400 italic bg-white/60 dark:bg-slate-900/60 p-4 rounded-xl border border-dashed border-purple-200 dark:border-purple-900/50 text-center">
                    All clear! No leave applications are awaiting Executive approval.
                </p>
            @endif
        </div>
    @endif

    <!-- My Leave Applications Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">My Leave History &amp; Workflow Status</h3>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Showing {{ $requests->total() }} total submissions</span>
        </div>

        @if($requests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/80 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Ref / Type</th>
                            <th class="px-5 py-3.5">Schedule &amp; Dates</th>
                            <th class="px-5 py-3.5">Days</th>
                            <th class="px-5 py-3.5">Reason</th>
                            <th class="px-5 py-3.5">Workflow &amp; Expiration</th>
                            <th class="px-5 py-3.5 text-right">Tracking</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($requests as $req)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-[11px] font-bold text-slate-500 dark:text-slate-400 block mb-1">#LV-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $req->badge_class }}">
                                        {{ $req->type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $req->start_date->format('M d, Y') }}</span>
                                    @if(!$req->start_date->isSameDay($req->end_date))
                                        <span class="text-slate-400 dark:text-slate-500"> to </span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $req->end_date->format('M d, Y') }}</span>
                                    @endif
                                    @if($req->is_half_day)
                                        <span class="block text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-0.5">Half Day ({{ ucfirst($req->half_day_type ?? 'Morning') }})</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-bold font-mono text-slate-800 dark:text-slate-200">
                                    {{ $req->days_count }} {{ Str::plural('day', $req->days_count) }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($req->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Approved
                                        </span>
                                    @elseif($req->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Disapproved
                                        </span>
                                    @elseif($req->status === 'expired')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-50 dark:bg-orange-950/60 text-orange-800 dark:text-orange-300 border border-orange-200 dark:border-orange-800">
                                            ⌛ Expired (1-Day Limit)
                                        </span>
                                    @else
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300">
                                                    {{ $req->status_label }}
                                                </span>
                                            </div>
                                            @if($req->stage_expires_at)
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                    1-Day Limit: <strong class="text-slate-700 dark:text-slate-300">{{ $req->stage_expires_at->timezone('Asia/Manila')->format('M d, h:i A') }}</strong>
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('portal.leaves.show', $req) }}"
                                       class="px-3 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>View Trail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $requests->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No leave requests filed yet</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">Submit your leave application anytime (Vacation Leave requires at least 3 days advance notice).</p>
                <button type="button" @click="applyModal = true" class="mt-4 px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition">
                    File Your First Leave Application
                </button>
            </div>
        @endif
    </div>

    <!-- MODAL 1: FILE LEAVE APPLICATION -->
    <div x-show="applyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto" @click.outside="applyModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-5">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Application for Leave</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Vacation Leave requires 3 days advance notice &bull; 1-day approval limit per stage</p>
                </div>
                <button type="button" @click="applyModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('portal.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Leave Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Type of Leave *</label>
                    <select name="type" x-model="leaveType" @change="onTypeChange()" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-red-500 focus:border-red-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                        <option value="vacation">Vacation Leave (VL) — {{ number_format($employee->remaining_vacation_leave, 1) }} days avail (Min 3 days advance)</option>
                        <option value="sick">Sick Leave (SL) — {{ number_format($employee->remaining_sick_leave, 1) }} days avail (Within 3 days of return to work)</option>
                        <option value="emergency">Emergency Leave (EL) — {{ number_format($employee->remaining_emergency_leave, 1) }} days avail</option>
                        <option value="time_off">Time Off Leave (TOL) — {{ number_format($employee->time_credit_balance, 2) }} hrs avail</option>
                        <option value="maternity">Maternity Leave (ML)</option>
                        <option value="paternity">Paternity Leave (PL)</option>
                        <option value="solo_parent">Parental Leave / Solo Parent Leave (SPL)</option>
                        <option value="vawc">Leave for Victims of VAWC / Battered Women Leave (BWL)</option>
                        <option value="gynecological">Gynecological Leaves for Women (GL)</option>
                    </select>
                </div>

                <!-- Dates (Advance notice strictly enforced only for Vacation Leave) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Start Date <span x-show="isVacation()" class="text-amber-600 dark:text-amber-400 font-extrabold">(Min 3 Days Advance)</span> *
                        </label>
                        <input type="date" name="start_date" required
                               x-model="startDate"
                               :min="isVacation() ? advanceDate : ''"
                               :max="isSick() ? todayDate : ''"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">End Date *</label>
                        <input type="date" name="end_date" required
                               x-model="endDate"
                               :min="startDate || (isVacation() ? advanceDate : '')"
                               :max="isSick() ? todayDate : ''"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 font-semibold">
                    </div>
                </div>

                <!-- Notice Banners based on selected leave type -->
                <div x-show="isVacation()" class="px-3.5 py-2.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl text-[11px] text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <span class="font-extrabold uppercase tracking-wide">Advance Notice Required:</span> Vacation Leave must be filed at least 3 days in advance (earliest start date: <code class="font-bold font-mono" x-text="advanceDate"></code>).
                    </div>
                </div>

                <div x-show="isSick()" class="px-3.5 py-2.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl text-[11px] text-rose-800 dark:text-rose-300 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <strong>Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.</strong>
                    </div>
                </div>

                <div x-show="!isVacation() && !isSick()" class="px-3 py-2 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-[11px] text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><strong>Standard Leave:</strong> This leave type can be filed immediately for today or upcoming dates.</span>
                </div>
                <div x-show="leaveType === 'time_off' || leaveType === 'offset_leave' || leaveType === 'offset'" class="px-3 py-2 bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/50 rounded-xl text-[11px] text-purple-800 dark:text-purple-300 flex items-center gap-2">
                    <span>⏳ <strong>Time Off Leave:</strong> Deducted from your earned time credit balance (8 hrs = 1 day leave).</span>
                </div>

                <!-- Half day toggle -->
                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_half_day" value="1" x-model="isHalfDay" class="rounded text-red-700 focus:ring-red-500">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Half-Day Application (0.5 day / 4 hours)</span>
                    </label>

                    <div x-show="isHalfDay" x-cloak class="pt-2 border-t border-slate-200/60 dark:border-slate-700">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Session Schedule</label>
                        <select name="half_day_session" class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-lg text-xs bg-white">
                            <option value="morning">Morning Shift (8:00 AM - 12:00 PM)</option>
                            <option value="afternoon">Afternoon Shift (1:00 PM - 5:00 PM)</option>
                        </select>
                    </div>
                </div>

                <!-- Mandatory Reason -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Reason for Leave (Required) *</label>
                    <textarea name="reason" rows="3" required minlength="3" placeholder="Provide your mandatory reason/justification for this leave request..."
                              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500">{{ old('reason') }}</textarea>
                </div>

                <!-- Supporting Document / Medical Certificate Attachment -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Supporting Document / Attachment 
                        <span x-show="isSick()" class="text-rose-600 dark:text-rose-400 font-extrabold">(Med Cert / Fit to Work if SL &gt; 3 days)</span>
                    </label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500 font-semibold file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200">
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1" x-show="isSick()">
                        Notice: Sick Leave must be accompanied by a medical certificate and/or Fit to Work recommendation if SL is more than 3 days. Accepts PDF, JPG, PNG (Max 5MB).
                    </p>
                </div>

                <!-- Workflow Notice -->
                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200/80 dark:border-amber-800/80 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-700 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[11px] text-amber-900 dark:text-amber-200 leading-relaxed">
                        @if($employee->approval_role === 'section_head')
                            <strong>Section Head Routing:</strong> Your request routes directly to the <strong>Executive Office</strong> (1-day approval limit). HR receives a record copy &amp; email notification.
                        @elseif(in_array($employee->approval_role, ['executive_office', 'executive_director']))
                            <strong>Executive Office Routing:</strong> Your request routes directly to the <strong>Executive Director</strong> (1-day approval limit). HR receives a record copy &amp; email notification.
                        @else
                            <strong>Approval Process:</strong> 1. <strong>Section Head</strong> (1-day approval limit) &rarr; 2. <strong>Executive Office</strong> (1-day approval limit). HR receives a record copy &amp; email notifications.
                        @endif
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="applyModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-center">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-xs text-center">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: APPROVAL DIALOG FOR SECTION HEAD / EXECUTIVE OFFICE / EXECUTIVE DIRECTOR -->
    <template x-if="reviewTarget">
        <div x-show="reviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 relative max-h-[90vh] overflow-y-auto" @click.outside="reviewModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white"
                            x-text="reviewTarget.status === 'pending_executive_director' ? 'Executive Director Approval' : (reviewTarget.status === 'pending_executive' ? 'Executive Office Final Approval' : 'Section Head Approval')"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">1-Day (24-Hour) Approval Window</p>
                    </div>
                    <button type="button" @click="reviewModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-3 rounded-xl border mb-4 text-xs space-y-1 bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200">
                    <p><strong>Applicant:</strong> <span x-text="reviewTarget.employee ? reviewTarget.employee.name : ''"></span></p>
                    <p><strong>Duration:</strong> <span x-text="reviewTarget.days_count + ' day(s)' + ' (' + (reviewTarget.start_date ? reviewTarget.start_date.substring(0, 10) : '') + ' to ' + (reviewTarget.end_date ? reviewTarget.end_date.substring(0, 10) : '') + ')'"></span></p>
                    <p><strong>Reason:</strong> <span class="italic" x-text="reviewTarget.reason"></span></p>
                </div>

                <form :action="'{{ url('/portal/leaves') }}/' + reviewTarget.id + '/' + (reviewTarget.status === 'pending_section_head' ? 'section-approve' : 'executive-approve')" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Decision / Action *</label>
                        <select name="action" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs font-semibold focus:ring-red-500 focus:border-red-500 bg-white">
                            <template x-if="reviewTarget.status === 'pending_section_head'">
                                <option value="approved">✅ Approve &amp; Forward to Executive Office</option>
                            </template>
                            <template x-if="reviewTarget.status !== 'pending_section_head'">
                                <option value="approved">🎉 Final Approve (Generate Leave Attendance &amp; Deduct Balance)</option>
                            </template>
                            <option value="rejected">❌ Disapprove &amp; Reject Request</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Evaluation Notes / Remarks</label>
                        <textarea name="notes" rows="3" placeholder="Comments regarding schedule coverage or reason for decision..."
                                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="reviewModal = false" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-center">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-xs text-center">
                            Submit Decision
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
@endsection
