@extends('layouts.portal')

@section('title', 'Request #' . $staffRequest->id)
@section('page-title', 'Staff Request Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ sectionModal: false, execModal: false, actionType: 'approved' }">

    <div class="flex items-center justify-between">
        <a href="{{ route('portal.requests.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Requests</span>
        </a>
        <span class="px-3 py-1 text-xs font-bold rounded-lg border whitespace-nowrap {{ $staffRequest->badge_class }}">
            {{ $staffRequest->status_label }}
        </span>
    </div>

    <!-- PENDING APPROVER CALLOUT BANNER (SECTION HEAD OR EXECUTIVE) -->
    @if(isset($canSectionApprove) && $canSectionApprove)
        <div class="p-5 bg-amber-50 dark:bg-amber-950/40 rounded-2xl border-2 border-amber-300 dark:border-amber-800 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 flex items-center justify-center font-black text-lg shrink-0 mt-0.5">⚠️</span>
                <div>
                    <h3 class="text-sm font-black text-amber-950 dark:text-amber-100 uppercase tracking-wide">
                        Section Head Endorsement Required
                    </h3>
                    <p class="text-xs text-amber-800 dark:text-amber-200 mt-0.5 leading-relaxed">
                        {{ $staffRequest->employee?->name }} has requested approval to attend this seminar. Please review the details below and grant endorsement or disapproval.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                <button type="button" @click="actionType = 'rejected'; sectionModal = true" class="px-3.5 py-2 border border-rose-300 text-rose-700 bg-white hover:bg-rose-50 dark:bg-slate-900 dark:text-rose-300 rounded-xl text-xs font-bold transition shadow-2xs">
                    Disapprove
                </button>
                <button type="button" @click="actionType = 'approved'; sectionModal = true" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Endorse &amp; Approve</span>
                </button>
            </div>
        </div>
    @elseif(isset($canExecutiveApprove) && $canExecutiveApprove)
        <div class="p-5 bg-purple-50 dark:bg-purple-950/40 rounded-2xl border-2 border-purple-300 dark:border-purple-800 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-purple-200 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200 flex items-center justify-center font-black text-lg shrink-0 mt-0.5">🎓</span>
                <div>
                    <h3 class="text-sm font-black text-purple-950 dark:text-purple-100 uppercase tracking-wide">
                        Executive Office Final Approval Required
                    </h3>
                    <p class="text-xs text-purple-800 dark:text-purple-200 mt-0.5 leading-relaxed">
                        This seminar request is now awaiting your final official decision. Review the budget, schedule, and previous Section Head endorsement below.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                <button type="button" @click="actionType = 'rejected'; execModal = true" class="px-3.5 py-2 border border-rose-300 text-rose-700 bg-white hover:bg-rose-50 dark:bg-slate-900 dark:text-rose-300 rounded-xl text-xs font-bold transition shadow-2xs">
                    Disapprove
                </button>
                <button type="button" @click="actionType = 'approved'; execModal = true" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Grant Final Approval</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Main Request Overview Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {{ $staffRequest->is_seminar ? 'Seminar / Training Participation' : 'Official Document Request' }}
                </span>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">
                    {{ $staffRequest->is_seminar ? $staffRequest->seminar_title : $staffRequest->document_type }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Filed by <strong>{{ $staffRequest->employee?->name }}</strong> ({{ $staffRequest->employee?->department?->name }}) &bull; {{ $staffRequest->created_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>
            @if($staffRequest->document_file_path)
                <a href="{{ asset('storage/' . $staffRequest->document_file_path) }}" target="_blank" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Issued Document</span>
                </a>
            @endif
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

            <!-- Seminar Specifics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50/70 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Organizer</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $staffRequest->organizer ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Venue / Location</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">{{ $staffRequest->venue ?: 'N/A' }}</p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Schedule</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $staffRequest->start_date ? $staffRequest->start_date->format('M d, Y') : 'N/A' }}
                        @if($staffRequest->end_date && !$staffRequest->start_date?->equalTo($staffRequest->end_date))
                            — {{ $staffRequest->end_date->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Estimated Budget</span>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">₱{{ number_format((float)$staffRequest->estimated_budget, 2) }}</p>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Justification &amp; Purpose</h4>
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
            <!-- Document Specifics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/70 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                <div>
                    <span class="text-[11px] font-bold uppercase text-slate-400 dark:text-slate-500 block">Requested Document</span>
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
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Purpose</h4>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ $staffRequest->purpose }}
                </div>
            </div>
        @endif

        <!-- Remarks Summary if applicable -->
        @if($staffRequest->admin_notes || $staffRequest->rejection_reason)
            <div class="p-4 rounded-xl border {{ $staffRequest->status === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/60' : 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800/60' }}">
                <span class="text-xs font-bold block {{ $staffRequest->status === 'rejected' ? 'text-rose-800 dark:text-rose-300' : 'text-blue-800 dark:text-blue-300' }}">
                    {{ $staffRequest->status === 'rejected' ? 'Disapproval Remarks:' : 'Leadership Approval Remarks:' }}
                </span>
                <p class="text-xs mt-1 {{ $staffRequest->status === 'rejected' ? 'text-rose-700 dark:text-rose-200' : 'text-blue-700 dark:text-blue-200' }}">
                    {{ $staffRequest->rejection_reason ?: $staffRequest->admin_notes }}
                </p>
            </div>
        @endif
    </div>

    <!-- OFFICIAL APPROVAL & AUDIT TRAIL MONITORING -->
    @if($staffRequest->approvals->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wide">
                    Official Approval Trail &amp; Monitoring Log
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Complete chronological history of reviews, endorsements, decisions, and dispatched email notifications.
                </p>
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

    <!-- SEMINAR REPORT SECTION -->
    @if($staffRequest->is_seminar)
        <div id="report-section" class="scroll-mt-6">
            @if($staffRequest->requires_seminar_report)
                <!-- Mandatory Report Submission Form for Applicant -->
                @if(isset($isOwner) && $isOwner)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border-2 border-purple-500/40 dark:border-purple-600/40 shadow-md p-6 sm:p-8 space-y-6">
                        <div class="flex items-center gap-3 border-b border-purple-100 dark:border-purple-900/50 pb-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300">
                                    Mandatory Post-Seminar Step
                                </span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-1">Submit Post-Training Seminar Report</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Share your learnings, institutional action plan, and official certificate of attendance.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('portal.requests.submit-report', $staffRequest) }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Executive Summary &amp; Key Learnings <span class="text-red-500">*</span>
                                </label>
                                <textarea name="report_summary" rows="5" required placeholder="Summarize the core concepts, modules covered, key takeaways, and relevant insights gained from the training..."
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-purple-500">{{ old('report_summary') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Application &amp; Knowledge Sharing Action Plan (Re-echo) <span class="text-red-500">*</span>
                                </label>
                                <textarea name="report_action_plan" rows="4" required placeholder="How will you apply these learnings in LASAC? When and how will you re-echo or cascade this knowledge to your team / colleagues?"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-purple-500">{{ old('report_action_plan') }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        Certificate of Completion / Attendance <span class="text-red-500">*</span>
                                    </label>
                                    <input type="file" name="certificate_file" required accept=".pdf,.jpg,.jpeg,.png"
                                           class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-50 dark:file:bg-purple-950/60 file:text-purple-700 dark:file:text-purple-300">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Upload your official Certificate (PDF, PNG, JPG). It will also be logged in your HR credentials.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        Presentation Slides / Handouts (Optional)
                                    </label>
                                    <input type="file" name="materials_file" accept=".pdf,.zip,.ppt,.pptx,.doc,.docx"
                                           class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Upload training materials, slide deck, or zip archive (up to 20MB).</p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Submit Seminar Report to HR</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-purple-50/60 dark:bg-purple-950/30 p-5 rounded-2xl border border-purple-200 dark:border-purple-900/50 text-xs text-purple-900 dark:text-purple-200">
                        <strong>Seminar Report Pending:</strong> The participant has not yet completed and submitted their post-training report and certificate.
                    </div>
                @endif

            @elseif($staffRequest->report_submitted_at)
                <!-- Display Already Submitted Seminar Report -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Submitted Seminar Report</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Submitted on {{ $staffRequest->report_submitted_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-bold rounded-lg {{ $staffRequest->report_status === 'acknowledged' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-50 text-purple-800 border border-purple-200 dark:bg-purple-950/60 dark:text-purple-300' }}">
                            {{ $staffRequest->report_status === 'acknowledged' ? 'Acknowledged & Logged' : 'Pending HR Acknowledgement' }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Executive Summary &amp; Learnings</h4>
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
                                    <span>Download Slides / Materials</span>
                                </a>
                            @endif
                        </div>

                        @if($staffRequest->report_acknowledged_by && $staffRequest->reportAcknowledger)
                            <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs text-emerald-800 dark:text-emerald-300">
                                <strong>Acknowledged by HR:</strong> {{ $staffRequest->reportAcknowledger->name }} on {{ $staffRequest->report_acknowledged_at?->format('M d, Y h:i A') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- SECTION HEAD MODAL -->
    <div x-show="sectionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 dark:border-slate-800" @click.outside="sectionModal = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="actionType === 'approved' ? 'Endorse & Approve Seminar Request (Section Head)' : 'Disapprove Seminar Request'"></h3>
            <p class="text-xs text-slate-500" x-text="actionType === 'approved' ? 'Endorsing this request will advance it to the Executive Office for final approval. The applicant and leadership will be notified via email.' : 'Please provide the reason for disapproval. The applicant will be notified via email.'"></p>

            <form method="POST" action="{{ route('portal.requests.section-approve', $staffRequest) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="action" :value="actionType">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1" x-text="actionType === 'approved' ? 'Endorsement Remarks (Optional)' : 'Disapproval Reason (Required)'"></label>
                    <textarea name="notes" rows="3" :required="actionType === 'rejected'" placeholder="Add comments, scheduling notes, or reasons..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="sectionModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit" :class="actionType === 'approved' ? 'bg-emerald-700 hover:bg-emerald-800 text-white' : 'bg-rose-700 hover:bg-rose-800 text-white'" class="px-5 py-2 rounded-xl text-xs font-bold transition">
                        <span x-text="actionType === 'approved' ? 'Confirm Endorsement' : 'Confirm Disapproval'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EXECUTIVE OFFICE MODAL -->
    <div x-show="execModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 dark:border-slate-800" @click.outside="execModal = false">
            <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="actionType === 'approved' ? 'Grant Final Executive Approval' : 'Disapprove Seminar Request'"></h3>
            <p class="text-xs text-slate-500" x-text="actionType === 'approved' ? 'Granting final official approval will authorize the employee to attend this seminar. They will be instructed to submit their seminar report upon completion.' : 'Please provide the reason for disapproval. The employee and section head will be notified via email.'"></p>

            <form method="POST" action="{{ route('portal.requests.executive-approve', $staffRequest) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="action" :value="actionType">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1" x-text="actionType === 'approved' ? 'Executive Remarks / Directives (Optional)' : 'Disapproval Reason (Required)'"></label>
                    <textarea name="notes" rows="3" :required="actionType === 'rejected'" placeholder="Add executive remarks, budget approvals, or instructions..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="execModal = false" class="px-4 py-2 border border-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit" :class="actionType === 'approved' ? 'bg-purple-700 hover:bg-purple-800 text-white' : 'bg-rose-700 hover:bg-rose-800 text-white'" class="px-5 py-2 rounded-xl text-xs font-bold transition">
                        <span x-text="actionType === 'approved' ? 'Confirm Final Approval' : 'Confirm Disapproval'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
