@extends('layouts.admin')

@section('title', 'Leave Application #' . str_pad($leave->id, 4, '0', STR_PAD_LEFT) . ' - Admin')
@section('page-title', 'Leave Application Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.leaves.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Leave Management</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Record</span>
            </button>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="font-mono text-xs font-black text-slate-400">#LV-{{ str_pad($leave->id, 4, '0', STR_PAD_LEFT) }}</span>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $leave->leave_type_label }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $leave->status_badge_class }}">
                        {{ $leave->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Filed on {{ $leave->created_at->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }} &bull; Applicant: <strong class="text-slate-800 dark:text-slate-200">{{ $leave->employee->name }}</strong> ({{ $leave->employee->department?->name ?? 'General' }} &bull; {{ $leave->employee->approval_role_label }})
                </p>
            </div>

            <!-- Duration -->
            <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700 text-right self-stretch sm:self-auto flex sm:flex-col justify-between sm:justify-center items-center sm:items-end">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Leave Duration</span>
                <span class="text-base sm:text-lg font-black font-mono text-slate-900 dark:text-white">
                    {{ $leave->days_count }} {{ Str::plural('Day', $leave->days_count) }}
                    @if($leave->is_half_day)
                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 block sm:inline">({{ ucfirst($leave->half_day_type ?? 'Half Day') }})</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Schedule & Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 py-5 border-b border-slate-100 dark:border-slate-800 text-xs">
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Employee Details</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $leave->employee->name }}</p>
                <span class="text-[11px] text-slate-400 font-mono">{{ $leave->employee->employee_no ?? 'ID-' . $leave->employee->id }}</span>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Start Date</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $leave->start_date->format('M d, Y') }}</p>
                <span class="text-[11px] text-slate-400">{{ $leave->start_date->format('l') }}</span>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">End Date</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $leave->end_date->format('M d, Y') }}</p>
                <span class="text-[11px] text-slate-400">{{ $leave->end_date->format('l') }}</span>
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">1-Day Stage Expiration</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                    @if($leave->is_pending && $leave->stage_expires_at)
                        {{ $leave->stage_expires_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                    @elseif($leave->status === 'expired' && $leave->expired_at)
                        <span class="text-orange-700 dark:text-orange-400">Expired {{ $leave->expired_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                    @else
                        &mdash;
                    @endif
                </p>
                <span class="text-[11px] text-slate-400">24-hour SLA per stage</span>
            </div>
        </div>

        <!-- Reason -->
        <div class="pt-5">
            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Reason Stated by Applicant</span>
            <div class="p-3.5 bg-slate-50/80 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed italic">
                &ldquo;{{ $leave->reason }}&rdquo;
            </div>
        </div>

        @if(in_array($leave->status, ['rejected', 'expired']) && $leave->rejection_reason)
            <div class="mt-4 p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl text-xs space-y-1">
                <div class="flex items-center gap-2 font-bold text-rose-900 dark:text-rose-200">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $leave->status === 'expired' ? 'Expiration Notice' : 'Disapproval Reason' }}</span>
                </div>
                <p class="text-rose-800 dark:text-rose-300 pl-6 leading-relaxed italic">&ldquo;{{ $leave->rejection_reason }}&rdquo;</p>
                @if($leave->rejector)
                    <p class="text-[10px] text-rose-600 dark:text-rose-400 pl-6 pt-0.5">By: {{ $leave->rejector->name }} &bull; {{ $leave->rejected_at?->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        @endif
    </div>

    <!-- APPROVAL TRAIL -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 space-y-6">
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Approval Workflow Trail</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                @if($leave->employee->approval_role === 'section_head')
                    Section Head Applicant: Direct to Executive Office (1-day limit).
                @elseif(in_array($leave->employee->approval_role, ['executive_office', 'executive_director']))
                    Executive Office Applicant: Direct to Executive Director (1-day limit).
                @else
                    Stage 1: Section Head (1-day limit) &rarr; Stage 2: Executive Office (1-day limit).
                @endif
                HR receives records &amp; email notifications.
            </p>
        </div>

        @php
            $shApproval = $leave->approvals->firstWhere('stage', 'section_head');
            $execApproval = $leave->approvals->whereIn('stage', ['executive_office', 'executive_director'])->first();
            $requiresSectionHead = !in_array($leave->employee->approval_role, ['section_head', 'executive_office', 'executive_director']);
            $finalApproverTitle = in_array($leave->employee->approval_role, ['executive_office', 'executive_director'])
                ? 'Executive Director Final Approval (1-Day Limit)'
                : 'Executive Office Final Approval (1-Day Limit)';
        @endphp

        <div class="relative pl-6 sm:pl-8 space-y-6">

            @if($requiresSectionHead)
                <!-- STAGE 1: SECTION HEAD -->
                <div class="relative">
                    <div class="absolute -left-6 sm:-left-8 top-7 bottom-0 w-6 flex justify-center -mb-6" aria-hidden="true">
                        <div class="w-0.5 h-full bg-slate-200 dark:bg-slate-700"></div>
                    </div>
                    <span class="absolute -left-6 sm:-left-8 top-1 w-6 h-6 rounded-full border-2 bg-white dark:bg-slate-900 flex items-center justify-center text-[10px] font-black z-10
                        {{ $shApproval ? (in_array($shApproval->action, ['rejected', 'expired']) ? 'border-rose-600 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400' : 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400') : ($leave->current_stage === 'section_head' ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 animate-pulse' : 'border-slate-300 dark:border-slate-600 text-slate-400') }}">
                        1
                    </span>
                    <div class="bg-white dark:bg-slate-800/80 rounded-xl border p-4 shadow-2xs {{ $shApproval ? (in_array($shApproval->action, ['rejected', 'expired']) ? 'border-rose-200 dark:border-rose-900/60 bg-rose-50/20 dark:bg-rose-950/20' : 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/20 dark:bg-emerald-950/20') : ($leave->current_stage === 'section_head' ? 'border-amber-300 dark:border-amber-700/60 bg-amber-50/30 dark:bg-amber-950/20' : 'border-slate-200 dark:border-slate-700') }}">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">Stage 1: Section Head Approval (1-Day Limit)</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Department schedule &amp; staff coverage</p>
                            </div>
                            <div>
                                @if($shApproval)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array($shApproval->action, ['rejected', 'expired']) ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' }}">
                                        {{ ucfirst($shApproval->action) }}
                                    </span>
                                @elseif($leave->current_stage === 'section_head' && $leave->is_pending)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 animate-pulse">Pending Review</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300">Queued</span>
                                @endif
                            </div>
                        </div>

                        @if($shApproval)
                            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/60 text-xs space-y-1.5">
                                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px]">
                                    <span>By: <strong class="text-slate-800 dark:text-slate-200">{{ $shApproval->approver_name ?? 'Section Head' }}</strong> ({{ $shApproval->approver_role }})</span>
                                    <span>{{ $shApproval->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                                </div>
                                @if(!empty($shApproval->notes))
                                    <div class="p-2.5 bg-white dark:bg-slate-900/90 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 italic">
                                        &ldquo;{{ $shApproval->notes }}&rdquo;
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- FINAL STAGE: EXECUTIVE OFFICE / EXECUTIVE DIRECTOR -->
            <div class="relative">
                <span class="absolute -left-6 sm:-left-8 top-1 w-6 h-6 rounded-full border-2 bg-white dark:bg-slate-900 flex items-center justify-center text-[10px] font-black z-10
                    {{ $execApproval ? (in_array($execApproval->action, ['rejected', 'expired']) ? 'border-rose-600 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400' : 'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400') : (in_array($leave->current_stage, ['executive_office', 'executive_director']) ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 animate-pulse' : 'border-slate-300 dark:border-slate-600 text-slate-400') }}">
                    {{ $requiresSectionHead ? '2' : '1' }}
                </span>
                <div class="bg-white dark:bg-slate-800/80 rounded-xl border p-4 shadow-2xs {{ $execApproval ? (in_array($execApproval->action, ['rejected', 'expired']) ? 'border-rose-200 dark:border-rose-900/60 bg-rose-50/20 dark:bg-rose-950/20' : 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/20 dark:bg-emerald-950/20') : (in_array($leave->current_stage, ['executive_office', 'executive_director']) ? 'border-amber-300 dark:border-amber-700/60 bg-amber-50/30 dark:bg-amber-950/20' : 'border-slate-200 dark:border-slate-700') }}">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">{{ $requiresSectionHead ? 'Stage 2: ' : 'Stage 1: ' }}{{ $finalApproverTitle }}</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">Official authorization &amp; attendance ledger posting</p>
                        </div>
                        <div>
                            @if($execApproval)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ in_array($execApproval->action, ['rejected', 'expired']) ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' }}">
                                    {{ ucfirst($execApproval->action) }}
                                </span>
                            @elseif(in_array($leave->current_stage, ['executive_office', 'executive_director']) && $leave->is_pending)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 animate-pulse">Awaiting Executive</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300">&mdash;</span>
                            @endif
                        </div>
                    </div>

                    @if($execApproval)
                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/60 text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px]">
                                <span>Signed off by: <strong class="text-slate-800 dark:text-slate-200">{{ $execApproval->approver_name ?? 'Executive Office' }}</strong> ({{ $execApproval->approver_role }})</span>
                                <span>{{ $execApproval->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                            </div>
                            @if(!empty($execApproval->notes))
                                <div class="p-2.5 bg-white dark:bg-slate-900/90 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 italic">
                                    &ldquo;{{ $execApproval->notes }}&rdquo;
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @php
        $currentAdminEmp = auth()->user()->employee;
        $isHrUser = $currentAdminEmp && $currentAdminEmp->approval_role === 'hr_officer';
        $isExecutiveUser = $currentAdminEmp && in_array($currentAdminEmp->approval_role, ['executive_office', 'executive_director']);
        $isExecutiveDirector = $currentAdminEmp && $currentAdminEmp->approval_role === 'executive_director';
        $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com');
    @endphp

    <!-- ADMIN ACTION CONTROLS (If leave is pending review) -->
    @if($leave->is_pending)
        @if($isHrUser)
            <div class="bg-blue-50/80 dark:bg-blue-950/40 rounded-2xl border border-blue-200 dark:border-blue-800/60 p-6 shadow-xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 flex items-center justify-center font-black text-sm flex-shrink-0">
                    ℹ️
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-950 dark:text-blue-200">HR Record &amp; Notification Copy</h3>
                    <p class="text-xs text-blue-800/90 dark:text-blue-300/90 mt-0.5">HR has full visibility of leave records and receives all email notifications. Approval decisions are completed by the Section Head and Executive Office / Executive Director.</p>
                </div>
            </div>
        @elseif(($isExecutiveUser && $leave->status === 'pending_executive') || ($isExecutiveDirector && $leave->status === 'pending_executive_director') || $isSuperAdmin)
            <div class="bg-purple-50/70 dark:bg-purple-950/30 rounded-2xl border border-purple-200 dark:border-purple-800/60 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-200 flex items-center justify-center font-black text-sm">
                        🏛️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $leave->stage_label }}</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300">Authorize or disapprove this leave request within the 1-day approval window.</p>
                    </div>
                </div>

                <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="space-y-4 bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-xl border border-purple-100 dark:border-purple-800/50">
                    @csrf
                    <input type="hidden" name="override_stage" value="{{ $leave->current_stage }}">
                    <input type="hidden" name="stage" value="{{ $leave->current_stage }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Decision *</label>
                            <select name="action" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-purple-500 focus:border-purple-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                                <option value="approved">🎉 Approve Leave Request</option>
                                <option value="rejected">❌ Disapprove &amp; Reject Request</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Review Stage</label>
                            <input type="text" readonly value="{{ $leave->stage_label }}" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-600 dark:text-slate-400 font-semibold cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Evaluation Notes / Remarks</label>
                        <textarea name="notes" rows="3" placeholder="Optional directive or executive remarks..."
                                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-purple-500 focus:border-purple-500"></textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Authorize &amp; Submit Decision
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif

</div>
@endsection
