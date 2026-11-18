@extends('layouts.admin')

@section('title', 'Notice #' . $notice->notice_no . ' - Admin')
@section('page-title', 'Disciplinary Notice Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.disciplinary.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Disciplinary Records</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Notice</span>
            </button>
        </div>
    </div>

    <!-- Main Notice Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 relative overflow-hidden">
        
        <!-- Header Info -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="font-mono text-xs font-black text-slate-400">#{{ $notice->notice_no }}</span>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $notice->sanction_label }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $notice->status_badge['class'] }}">
                        {{ $notice->status_badge['label'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Issued on {{ $notice->created_at->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }} &bull;
                    Recipient: <strong class="text-slate-800 dark:text-slate-200">{{ $notice->employee->name }}</strong>
                    ({{ $notice->employee->position ?? 'Staff' }} &bull; {{ $notice->employee->department?->name ?? 'General' }})
                </p>
            </div>

            <!-- Classification Badge -->
            <div class="px-3 py-1.5 rounded-xl border text-center self-stretch sm:self-auto {{ $notice->classification === 'major' ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300' : ($notice->classification === 'special' ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-slate-50 text-slate-700 border-slate-200') }}">
                <span class="text-[10px] font-bold uppercase tracking-wider block">Classification</span>
                <span class="text-xs font-extrabold uppercase">{{ $notice->classification }} Offense</span>
            </div>
        </div>

        <!-- HR Clarification Directive Banner -->
        <div class="my-5 p-4 bg-red-50 dark:bg-red-950/40 rounded-xl border-2 border-red-200 dark:border-red-900/60 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-red-700 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-black text-red-800 dark:text-red-300">
                    {{ $notice->hr_clarification_note ?: 'Please see the HR Officer for clarification.' }}
                </span>
            </div>
            @if($notice->viewed_at)
                <span class="text-[11px] text-red-600 dark:text-red-400 font-medium whitespace-nowrap">
                    Viewed by staff on {{ $notice->viewed_at->timezone('Asia/Manila')->format('M d, h:i A') }}
                </span>
            @else
                <span class="text-[11px] text-slate-500 font-medium whitespace-nowrap">
                    Unopened in portal yet
                </span>
            @endif
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 py-5 border-b border-slate-100 dark:border-slate-800 text-xs">
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Category</span>
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $notice->category_name }}</p>
                <span class="text-[10px] text-slate-400">Occurrence: {{ $notice->offense_occurrence }}</span>
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Incident Date &amp; Time</span>
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $notice->incident_date->format('F d, Y') }}</p>
                <span class="text-[10px] text-slate-400">{{ $notice->incident_time ?: 'Time not specified' }}</span>
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Location</span>
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $notice->incident_location ?: 'Commission Premises' }}</p>
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-slate-400">Issuing Authority</span>
                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $notice->issuer?->name ?? 'HR Department' }}</p>
                <span class="text-[10px] text-slate-400">HRD / Commission</span>
            </div>
        </div>

        <!-- Offense Provision & Statement of Facts -->
        <div class="py-5 border-b border-slate-100 dark:border-slate-800 space-y-4">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Specific Offense Provision Charged:</h4>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-100">
                    {{ $notice->offense_title }}
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Statement of Facts &amp; Narrative:</h4>
                <div class="p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed">
                    {{ $notice->description }}
                </div>
            </div>

            @if($notice->is_time_in_blocked)
                <div class="p-4 bg-red-600 text-white rounded-xl shadow-md flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center font-black text-lg shrink-0">
                        ⛔
                    </div>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-red-100">Time-In Restriction Currently Active</h4>
                        <p class="text-xs font-semibold mt-0.5 leading-relaxed">
                            The 2-day compliance window for <strong>{{ $notice->employee->name }}</strong> to submit their physical Notice to Explain (NTE) has lapsed (Deadline: {{ $notice->explanation_deadline?->format('F d, Y') }}).
                            Staff is currently <strong>restricted from timing in</strong>. Attach the scanned copy of their physically submitted NTE below to immediately restore time-in privileges.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Confidential HR Documents (🔒 HR / Admin Only - Hidden from Staff Portal) -->
        <div class="py-5 border-b border-slate-100 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Confidential HR Documents</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        🔒 HR / Admin Only &bull; Hidden from Staff Portal
                    </span>
                </h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Incident Report (Required) -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase text-red-700 dark:text-red-400 block">Incident Report (Required)</span>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                            {{ $notice->incident_report_path ? basename($notice->incident_report_path) : ($notice->attachment_path ? basename($notice->attachment_path) : 'No file on record') }}
                        </p>
                    </div>
                    @if($notice->incident_report_path || $notice->attachment_path)
                        <a href="{{ Storage::disk('public')->url($notice->incident_report_path ?: $notice->attachment_path) }}" target="_blank"
                           class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs font-bold shrink-0 transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>View Report</span>
                        </a>
                    @endif
                </div>

                <!-- Documentary Evidence (Optional) -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase text-slate-500 block">Documentary Evidence (Optional)</span>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">
                            {{ $notice->documentary_evidence_path ? basename($notice->documentary_evidence_path) : 'No additional evidence attached' }}
                        </p>
                    </div>
                    @if($notice->documentary_evidence_path)
                        <a href="{{ Storage::disk('public')->url($notice->documentary_evidence_path) }}" target="_blank"
                           class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shrink-0 transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>View Evidence</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Notice of Explanation (NTE) Status & Physical Submission -->
        @if($notice->requires_explanation)
            <div class="py-5 border-b border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Notice of Explanation (NTE) &amp; Physical Submission</span>
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Staff must personally submit their written explanation to HR within 2 days. HR will attach the scanned copy below.
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-500">
                        Deadline: {{ $notice->explanation_deadline?->format('M d, Y') }}
                        @if($notice->is_time_in_blocked)
                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-800 font-extrabold ml-1">Time-In Blocked</span>
                        @elseif($notice->is_explanation_pending)
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold ml-1">Pending Submission</span>
                        @else
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold ml-1">Processed</span>
                        @endif
                    </span>
                </div>

                @if($notice->scanned_nte_path)
                    <!-- Scanned Copy Already Attached by HR -->
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-300 dark:border-emerald-800 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-emerald-900 dark:text-emerald-200">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Scanned Copy of NTE Attached &amp; Case Processed</span>
                            </span>
                            <span class="text-[10px] font-normal text-emerald-700 dark:text-emerald-300">
                                Received: {{ $notice->nte_received_at?->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }}
                                @if($notice->nteProcessor)
                                    &bull; Processed by {{ $notice->nteProcessor->name }}
                                @endif
                            </span>
                        </div>
                        <p class="text-[11px] text-emerald-800 dark:text-emerald-300">
                            ✓ The physical NTE was received, verified, and attached. Employee time-in privileges are active.
                        </p>
                        <div class="pt-1">
                            <a href="{{ Storage::disk('public')->url($notice->scanned_nte_path) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View Scanned NTE Document</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- HR Form to Attach Scanned Copy of NTE & Clear Time-In Restriction -->
                    <form action="{{ route('admin.disciplinary.attach-nte', $notice) }}" method="POST" enctype="multipart/form-data"
                          class="p-4 rounded-xl border space-y-3 {{ $notice->is_time_in_blocked ? 'bg-red-50/70 border-red-300 dark:bg-red-950/30 dark:border-red-800' : 'bg-amber-50/60 border-amber-300 dark:bg-amber-950/20 dark:border-amber-800' }}">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h5 class="text-xs font-black uppercase tracking-wider {{ $notice->is_time_in_blocked ? 'text-red-900 dark:text-red-300' : 'text-amber-900 dark:text-amber-300' }}">
                                Attach Scanned Copy of Written NTE from Staff
                            </h5>
                            @if($notice->is_time_in_blocked)
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-red-600 text-white">
                                    Restore Time-In Access
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400">
                            When the staff member submits their physical signed Notice to Explain at the HR office, scan the document and upload it here. Uploading will process the case and immediately restore or ensure their time-in capability.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Scanned NTE Document <span class="text-red-600">*</span>
                                </label>
                                <input type="file" name="scanned_nte" required accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-black">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                    Remarks / Receipt Notes (Optional)
                                </label>
                                <input type="text" name="remarks" placeholder="e.g. Received hard copy in person at HR desk"
                                       class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg text-xs dark:bg-slate-800 dark:text-slate-100">
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-xs transition flex items-center gap-1.5 {{ $notice->is_time_in_blocked ? 'bg-red-700 hover:bg-red-800' : 'bg-amber-700 hover:bg-amber-800' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Attach Scanned NTE &amp; Process Case (Restore Time-In)</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

        <!-- Case Resolution & HR Action -->
        <div class="pt-5 space-y-4">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">
                HR Resolution &amp; Final Disposition
            </h4>

            @if(in_array($notice->status, ['resolved', 'closed']))
                <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800/60 space-y-2 text-xs">
                    <div class="flex items-center justify-between font-bold text-emerald-900 dark:text-emerald-200">
                        <span>Resolution Findings &amp; Final Action:</span>
                        <span class="text-[10px] font-normal text-emerald-700">
                            Concluded: {{ $notice->resolved_at?->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }} by {{ $notice->resolver?->name ?? 'HR' }}
                        </span>
                    </div>
                    <p class="text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                        {{ $notice->hr_resolution_notes }}
                    </p>
                    @if($notice->final_sanction)
                        <div class="pt-1">
                            <span class="font-bold text-emerald-950 dark:text-emerald-300">Final Sanction Imposed:</span>
                            <span class="font-semibold text-emerald-800 dark:text-emerald-400">{{ $notice->final_sanction }}</span>
                        </div>
                    @endif
                </div>
            @else
                <form action="{{ route('admin.disciplinary.resolve', $notice) }}" method="POST" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Record HR Investigation Findings &amp; Resolution Notes *
                        </label>
                        <textarea name="hr_resolution_notes" rows="3" required
                                  placeholder="Document conference findings, investigation results, or reprimand details..."
                                  class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100 focus:ring-red-500 focus:border-red-500">{{ old('hr_resolution_notes', $notice->hr_resolution_notes) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Final Sanction Enforced
                            </label>
                            <input type="text" name="final_sanction" value="{{ old('final_sanction', $notice->sanction_label) }}"
                                   placeholder="e.g. Written Warning, 1-Day Suspension, Cleared"
                                   class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs dark:bg-slate-800 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Update Case Status *
                            </label>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold dark:bg-slate-800 dark:text-slate-100">
                                <option value="resolved" {{ $notice->status == 'resolved' ? 'selected' : '' }}>Mark as Resolved</option>
                                <option value="closed" {{ $notice->status == 'closed' ? 'selected' : '' }}>Mark as Closed</option>
                                <option value="under_review" {{ $notice->status == 'under_review' ? 'selected' : '' }}>Keep Under Investigation</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold transition shadow-xs">
                            Save HR Resolution
                        </button>
                    </div>
                </form>
            @endif
        </div>

    </div>

</div>
@endsection
