@extends('layouts.admin')

@section('title', 'Staff Request #' . $staffRequest->id)
@section('page-title', 'Review Staff Request')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ approveModal: false, rejectModal: false, ackModal: false }">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Staff Requests</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-xs font-bold rounded-lg border whitespace-nowrap {{ $staffRequest->badge_class }}">
                {{ $staffRequest->status_label }}
            </span>
        </div>
    </div>

    <!-- Request Details Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">

        <!-- Applicant Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 flex items-center justify-center font-bold text-base shrink-0 border border-red-200 dark:border-red-900/60">
                    {{ strtoupper(substr($staffRequest->employee?->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $staffRequest->employee?->name }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $staffRequest->employee?->position }} &bull; {{ $staffRequest->employee?->department?->name }}
                        @if($staffRequest->employee?->employee_no)
                            <span class="font-mono text-slate-400">({{ $staffRequest->employee->employee_no }})</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Filed On</span>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $staffRequest->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        <!-- Request Specifics -->
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 block mb-1">
                {{ $staffRequest->is_seminar ? 'Seminar / Training Participation Request' : 'Official Document Issuance Request' }}
            </span>
            <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">
                {{ $staffRequest->is_seminar ? $staffRequest->seminar_title : $staffRequest->document_type }}
            </h3>
        </div>

        @if($staffRequest->is_seminar)
            <!-- Multi-Stage Stepper for Seminar Request -->
            <div class="bg-slate-50 dark:bg-slate-800/40 p-4 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-800 space-y-3">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 block">
                    Two-Stage Leadership Approval Workflow
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                    <!-- Step 1: Submission -->
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2 text-emerald-600 font-bold text-[11px] uppercase">
                            <span>✓ Step 1</span>
                            <span class="text-slate-400">&bull; Submitted</span>
                        </div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 mt-1">Request Filed</p>
                        <p class="text-[10px] text-slate-400">{{ $staffRequest->created_at->format('M d, Y') }}</p>
                    </div>

                    <!-- Step 2: Section Head Endorsement -->
                    @php $shApproval = $staffRequest->section_head_approval; @endphp
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border {{ $shApproval ? ($shApproval->action === 'rejected' ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20' : 'border-emerald-300 dark:border-emerald-900/60 bg-emerald-50/20') : ($staffRequest->status === 'pending_section_head' ? 'border-amber-400 bg-amber-50/20' : 'border-slate-200 dark:border-slate-800') }}">
                        <div class="flex items-center gap-2 font-bold text-[11px] uppercase {{ $shApproval ? ($shApproval->action === 'rejected' ? 'text-rose-600' : 'text-emerald-600') : ($staffRequest->status === 'pending_section_head' ? 'text-amber-600' : 'text-slate-400') }}">
                            <span>{{ $shApproval ? ($shApproval->action === 'rejected' ? '✕ Disapproved' : '✓ Endorsed') : ($staffRequest->status === 'pending_section_head' ? '⏳ In Review' : 'Step 2') }}</span>
                        </div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 mt-1">Section Head</p>
                        <p class="text-[10px] text-slate-400">
                            {{ $shApproval ? $shApproval->approver_name . ' (' . $shApproval->formatted_action_at . ')' : ($staffRequest->status === 'pending_section_head' ? 'Awaiting Section Head' : 'Queued') }}
                        </p>
                    </div>

                    <!-- Step 3: Executive Office Final Approval -->
                    @php $execApproval = $staffRequest->executive_approval; @endphp
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border {{ $execApproval ? ($execApproval->action === 'rejected' ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20' : 'border-emerald-300 dark:border-emerald-900/60 bg-emerald-50/20') : (in_array($staffRequest->status, ['pending_executive', 'pending_executive_director']) ? 'border-purple-400 bg-purple-50/20' : 'border-slate-200 dark:border-slate-800') }}">
                        <div class="flex items-center gap-2 font-bold text-[11px] uppercase {{ $execApproval ? ($execApproval->action === 'rejected' ? 'text-rose-600' : 'text-emerald-600') : (in_array($staffRequest->status, ['pending_executive', 'pending_executive_director']) ? 'text-purple-600' : 'text-slate-400') }}">
                            <span>{{ $execApproval ? ($execApproval->action === 'rejected' ? '✕ Disapproved' : '✓ Approved') : (in_array($staffRequest->status, ['pending_executive', 'pending_executive_director']) ? '⏳ In Review' : 'Step 3') }}</span>
                        </div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 mt-1">Executive Office</p>
                        <p class="text-[10px] text-slate-400">
                            {{ $execApproval ? $execApproval->approver_name . ' (' . $execApproval->formatted_action_at . ')' : (in_array($staffRequest->status, ['pending_executive', 'pending_executive_director']) ? 'Awaiting Executive Approval' : 'Queued') }}
                        </p>
                    </div>

                    <!-- Step 4: Post-Seminar Report -->
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border {{ $staffRequest->report_status === 'acknowledged' ? 'border-emerald-300 dark:border-emerald-900/60 bg-emerald-50/20' : ($staffRequest->report_submitted_at ? 'border-purple-300 dark:border-purple-900/60 bg-purple-50/20' : 'border-slate-200 dark:border-slate-800') }}">
                        <div class="flex items-center gap-2 font-bold text-[11px] uppercase {{ $staffRequest->report_status === 'acknowledged' ? 'text-emerald-600' : ($staffRequest->report_submitted_at ? 'text-purple-600' : 'text-slate-400') }}">
                            <span>{{ $staffRequest->report_status === 'acknowledged' ? '✓ Completed' : ($staffRequest->report_submitted_at ? '📋 Submitted' : 'Step 4') }}</span>
                        </div>
                        <p class="font-bold text-slate-800 dark:text-slate-200 mt-1">Report &amp; Creds</p>
                        <p class="text-[10px] text-slate-400">
                            {{ $staffRequest->report_status === 'acknowledged' ? 'Credentials Logged' : ($staffRequest->report_submitted_at ? 'Under HR Review' : 'Post-Seminar') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Organizer</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $staffRequest->organizer ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Venue / Mode</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $staffRequest->venue ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Training Dates</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $staffRequest->start_date ? $staffRequest->start_date->format('M d, Y') : 'N/A' }}
                        @if($staffRequest->end_date && !$staffRequest->start_date?->equalTo($staffRequest->end_date))
                            — {{ $staffRequest->end_date->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Estimated Budget</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">
                        ₱{{ number_format((float)$staffRequest->estimated_budget, 2) }}
                    </p>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Justification &amp; Relevance</h4>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ $staffRequest->justification }}
                </div>
            </div>

            @if($staffRequest->invitation_file_path)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Attached Program / Brochure:</span>
                    <a href="{{ asset('storage/' . $staffRequest->invitation_file_path) }}" target="_blank" class="text-xs font-bold text-red-700 dark:text-red-400 hover:underline inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>View Attachment</span>
                    </a>
                </div>
            @endif

        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/70 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Document Requested</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $staffRequest->document_type }}</p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Date Needed By</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $staffRequest->date_needed ? $staffRequest->date_needed->format('F d, Y') : 'Not specified' }}
                    </p>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Purpose of Request</h4>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ $staffRequest->purpose }}
                </div>
            </div>

            @if($staffRequest->document_file_path)
                <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-emerald-900 dark:text-emerald-200">Official Document Uploaded for Employee</p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300">Employee can download this file from their portal.</p>
                    </div>
                    <a href="{{ asset('storage/' . $staffRequest->document_file_path) }}" target="_blank" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition">
                        Download Copy
                    </a>
                </div>
            @endif
        @endif

        <!-- Review Decision Toolbar for Pending Requests -->
        @if($staffRequest->is_pending)
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        Current Review Stage: <strong class="text-red-700 dark:text-red-400">{{ $staffRequest->stage_label }}</strong>
                    </span>
                    <p class="text-[11px] text-slate-400">Taking action here records your user credentials in the official audit trail and dispatches email notifications.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                    <button type="button" @click="rejectModal = true" class="px-4 py-2.5 border border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl text-xs font-bold transition">
                        Disapprove Request
                    </button>
                    <button type="button" @click="approveModal = true" class="px-6 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>
                            @if($staffRequest->is_seminar)
                                {{ $staffRequest->status === 'pending_section_head' ? 'Endorse (Section Head)' : 'Grant Final Approval' }}
                            @else
                                Approve &amp; Fulfill
                            @endif
                        </span>
                    </button>
                </div>
            </div>
        @else
            <!-- Display Past Review Notes -->
            <div class="p-4 rounded-xl border {{ $staffRequest->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/60' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200/60 dark:border-slate-700/60' }}">
                <span class="text-xs font-bold block {{ $staffRequest->status === 'rejected' ? 'text-rose-800 dark:text-rose-300' : 'text-slate-800 dark:text-slate-200' }}">
                    {{ $staffRequest->status === 'rejected' ? 'Disapproval Reason:' : 'Review Remarks:' }}
                </span>
                <p class="text-xs mt-1 {{ $staffRequest->status === 'rejected' ? 'text-rose-700 dark:text-rose-200' : 'text-slate-600 dark:text-slate-300' }}">
                    {{ $staffRequest->rejection_reason ?: ($staffRequest->admin_notes ?: 'No additional notes provided.') }}
                </p>
                @if($staffRequest->reviewer)
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                        Reviewed by {{ $staffRequest->reviewer->name }} on {{ $staffRequest->reviewed_at?->format('M d, Y h:i A') }}
                    </p>
                @endif
            </div>
        @endif

    </div>

    <!-- OFFICIAL APPROVAL & AUDIT TRAIL MONITORING -->
    @if($staffRequest->approvals->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">
                        Official Approval Trail &amp; Monitoring Log
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Timestamped audit trail of all review steps, actions, endorsements, and email dispatch records.
                    </p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ $staffRequest->approvals->count() }} Recorded Steps
                </span>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($staffRequest->approvals as $approval)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-start justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $approval->action_badge_class }}">
                                    {{ ucfirst($approval->action) }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $approval->stage_label }}</span>
                                <span class="text-slate-400">&bull;</span>
                                <span class="text-slate-600 dark:text-slate-400 font-semibold">{{ $approval->approver_name }} ({{ $approval->approver_role }})</span>
                            </div>
                            @if($approval->notes)
                                <p class="text-slate-600 dark:text-slate-300 text-xs pl-0.5 leading-relaxed italic">
                                    &ldquo;{{ $approval->notes }}&rdquo;
                                </p>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap self-start sm:self-auto">
                            {{ $approval->formatted_action_at }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- POST-SEMINAR REPORT SECTION (ADMIN REVIEW) -->
    @if($staffRequest->is_seminar)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Post-Training Seminar Report</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Mandatory report step required from employee upon completion of approved training.</p>
                    </div>
                </div>
                @if($staffRequest->report_submitted_at)
                    <span class="px-3 py-1 text-xs font-bold rounded-lg {{ $staffRequest->report_status === 'acknowledged' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-50 text-purple-800 border border-purple-200 dark:bg-purple-950/60 dark:text-purple-300' }}">
                        {{ $staffRequest->report_status === 'acknowledged' ? 'Report Acknowledged' : 'Report Pending Review' }}
                    </span>
                @else
                    <span class="px-3 py-1 text-xs font-bold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300">
                        Awaiting Submission
                    </span>
                @endif
            </div>

            @if($staffRequest->report_submitted_at)
                <div class="space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Submitted by employee on <span class="font-bold text-slate-800 dark:text-slate-200">{{ $staffRequest->report_submitted_at->format('F d, Y \a\t h:i A') }}</span>
                    </p>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Executive Summary &amp; Key Learnings</h4>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                            {{ $staffRequest->report_summary }}
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Re-echo &amp; Application Action Plan</h4>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                            {{ $staffRequest->report_action_plan }}
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-3 flex-wrap">
                        @if($staffRequest->certificate_file_path)
                            <a href="{{ asset('storage/' . $staffRequest->certificate_file_path) }}" target="_blank" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Download Certificate</span>
                            </a>
                        @endif

                        @if($staffRequest->additional_materials_path)
                            <a href="{{ asset('storage/' . $staffRequest->additional_materials_path) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Download Handouts / Slides</span>
                            </a>
                        @endif
                    </div>

                    @if($staffRequest->report_status !== 'acknowledged')
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                            <button type="button" @click="ackModal = true" class="px-6 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Acknowledge Report &amp; Log Credentials</span>
                            </button>
                        </div>
                    @else
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs text-emerald-800 dark:text-emerald-300">
                            <strong>Acknowledged by:</strong> {{ $staffRequest->reportAcknowledger?->name }} on {{ $staffRequest->report_acknowledged_at?->format('M d, Y h:i A') }}. This training is officially recorded in the employee's certificates &amp; training records.
                        </div>
                    @endif
                </div>
            @else
                <div class="p-6 text-center text-slate-500 dark:text-slate-400">
                    <p class="text-xs font-semibold">The employee has not yet submitted their Seminar Report.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">They will be able to upload their executive summary, action plan, and certificate of completion once they attend the event.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- APPROVE MODAL -->
    <div x-show="approveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 dark:border-slate-800" @click.outside="approveModal = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                @if($staffRequest->is_seminar)
                    {{ $staffRequest->status === 'pending_section_head' ? 'Endorse Seminar Request (Section Head)' : 'Grant Final Executive Approval' }}
                @else
                    Approve Document Request
                @endif
            </h3>
            <p class="text-xs text-slate-500">
                @if($staffRequest->is_seminar)
                    {{ $staffRequest->status === 'pending_section_head' ? 'Endorsing this seminar request will advance it to the Executive Office. The applicant and leadership will receive email notifications.' : 'Granting official approval authorizes the employee to participate. Email notifications will be dispatched to the employee and section head.' }}
                @else
                    Approving will finalize this document request. You can optionally upload the official PDF/scanned copy below.
                @endif
            </p>

            <form method="POST" action="{{ route('admin.requests.approve', $staffRequest) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if($staffRequest->is_document)
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Upload Document Copy (PDF / Image - Optional)</label>
                        <input type="file" name="document_file" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700">
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Official Remarks (Optional)</label>
                    <textarea name="admin_notes" rows="3" placeholder="Add any approval instructions, endorsements, or release notes..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="approveModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition">
                        Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- REJECT MODAL -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 dark:border-slate-800" @click.outside="rejectModal = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Disapprove Staff Request</h3>
            <p class="text-xs text-slate-500">Please provide a clear reason for disapproval. The employee will be notified via email.</p>

            <form method="POST" action="{{ route('admin.requests.reject', $staffRequest) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Reason for Disapproval <span class="text-red-500">*</span></label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Explain why this request cannot be granted (scheduling conflict, budget constraints, etc.)..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-xs font-bold transition">
                        Confirm Disapproval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ACKNOWLEDGE REPORT MODAL -->
    <div x-show="ackModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 dark:border-slate-800" @click.outside="ackModal = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Acknowledge Seminar Report</h3>
            <p class="text-xs text-slate-500">Acknowledging this report marks the seminar request as completed and officially validates the certificate in the employee's HR credentials.</p>

            <form method="POST" action="{{ route('admin.requests.acknowledge-report', $staffRequest) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">HR Remarks (Optional)</label>
                    <textarea name="admin_notes" rows="3" placeholder="Commendations, filing remarks, or follow-up notes..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="ackModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition">
                        Acknowledge &amp; Complete
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
