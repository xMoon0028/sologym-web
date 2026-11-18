@extends('layouts.portal')

@section('title', 'Notice #' . $notice->notice_no . ' - My Portal')
@section('page-title', 'Code of Conduct Notice')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portal.conduct.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Conduct Notices</span>
        </a>

        <button type="button" onclick="window.print()"
                class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Print Copy</span>
        </button>
    </div>

    <!-- MANDATORY DIRECTIVE BANNER: PLEASE SEE HR OFFICER FOR CLARIFICATION -->
    <div class="p-4 sm:p-5 bg-red-600 text-white rounded-2xl shadow-md flex items-center justify-between gap-3">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-black text-xl shrink-0">
                ⚠️
            </div>
            <div>
                <p class="text-[10px] sm:text-xs uppercase font-extrabold tracking-wider text-red-100">Official HR Directive</p>
                <h3 class="text-sm sm:text-base font-black tracking-tight">
                    {{ $notice->hr_clarification_note ?: 'Please see the HR Officer for clarification.' }}
                </h3>
            </div>
        </div>
        <span class="hidden sm:inline-block px-3 py-1.5 bg-white/10 rounded-xl text-xs font-bold text-red-50 shrink-0">
            LASAC HRD
        </span>
    </div>

    <!-- Main Notice Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 relative overflow-hidden space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
            <div class="space-y-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="font-mono text-xs font-black text-slate-400">#{{ $notice->notice_no }}</span>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $notice->offense_title }}</h2>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Served on {{ $notice->created_at->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }} &bull;
                    Category: <strong class="text-slate-800 dark:text-slate-200">{{ $notice->category_name }}</strong>
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold border whitespace-nowrap {{ $notice->sanction_badge_class }}">
                    {{ $notice->short_sanction_label }}
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold border whitespace-nowrap {{ $notice->status_badge['class'] }}">
                    {{ $notice->status_badge['label'] }}
                </span>
            </div>
        </div>

        <!-- Incident & Provision Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Category &amp; Class</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 block mt-0.5">{{ $notice->category_name }}</span>
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ ucfirst($notice->classification) }} Offense &bull; {{ $notice->offense_occurrence }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Date &amp; Time</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 block mt-0.5">{{ $notice->incident_date->format('F d, Y') }}</span>
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $notice->incident_time ?: 'Time not specified' }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Location</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 block mt-0.5">{{ $notice->incident_location ?: 'Commission Premises' }}</span>
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Place of occurrence</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Issuing Authority</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 block mt-0.5">{{ $notice->issuer?->name ?? 'HR Department' }}</span>
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Authorized Officer</span>
            </div>
        </div>

        <!-- Offense Charged & Factual Narrative -->
        <div class="space-y-4 text-xs">
            <div>
                <h4 class="font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-[11px] mb-1.5">Offense Charged Under Code of Conduct:</h4>
                <div class="p-3.5 bg-red-50/60 dark:bg-red-950/30 rounded-xl border border-red-200 dark:border-red-900/60 font-bold text-red-900 dark:text-red-300 leading-snug">
                    {{ $notice->offense_title }}
                </div>
            </div>

            <div>
                <h4 class="font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 text-[11px] mb-1.5">Statement of Facts &amp; Specification:</h4>
                <div class="p-4 bg-slate-50/70 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                    {{ $notice->description }}
                </div>
            </div>
        </div>

        <!-- Notice to Explain (NTE) Plain Directive Section -->
        @if($notice->requires_explanation)
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Notice to Explain (NTE) Requirement</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Requirement to personally submit written explanation to the Human Resources Department.
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase text-slate-400 block tracking-wider">Compliance Deadline</span>
                        <span class="text-xs font-mono font-black {{ $notice->is_time_in_blocked ? 'text-red-600 dark:text-red-400' : 'text-amber-700 dark:text-amber-400' }}">
                            {{ $notice->explanation_deadline?->format('F d, Y') }}
                        </span>
                    </div>
                </div>

                @if($notice->scanned_nte_path || $notice->nte_received_at)
                    <!-- Physically Submitted & Scanned Copy Attached by HR -->
                    <div class="p-5 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border-2 border-emerald-300 dark:border-emerald-800 space-y-2.5 text-xs">
                        <div class="flex items-center gap-2 font-black text-emerald-900 dark:text-emerald-200 text-sm">
                            <span class="w-7 h-7 rounded-lg bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold">✓</span>
                            <span>Notice to Explain (NTE) Submitted &amp; Processed</span>
                        </div>
                        <p class="text-emerald-800 dark:text-emerald-300 leading-relaxed font-medium">
                            Your physical Notice to Explain (NTE) has been received, acknowledged, and processed by the Human Resources Department on <strong>{{ $notice->nte_received_at?->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }}</strong>.
                        </p>
                        <div class="pt-1 text-[11px] text-emerald-700 dark:text-emerald-400 font-bold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Time-In Access Status: Fully Active</span>
                        </div>
                    </div>
                @elseif($notice->is_time_in_blocked)
                    <!-- Overdue: Time-In Blocked Warning Notice -->
                    <div class="p-5 bg-red-600 text-white rounded-2xl shadow-md space-y-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-black text-xl shrink-0">
                                ⛔
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-red-200 block">Access Restricted</span>
                                <h4 class="text-sm sm:text-base font-black">
                                    Time-In Restricted: Notice to Explain (NTE) Overdue
                                </h4>
                            </div>
                        </div>
                        <p class="text-xs text-red-100 leading-relaxed">
                            The two (2) day compliance window for submitting your written Notice to Explain (NTE) lapsed on <strong>{{ $notice->explanation_deadline?->format('F d, Y') }}</strong>.
                            As per Commission policy, your account has been placed on <strong>Time-In restriction</strong>.
                        </p>
                        <div class="p-3 bg-black/20 rounded-xl text-xs font-black text-white text-center leading-relaxed">
                            *** Please report immediately in person to the Human Resources Department to submit your written NTE and restore your Time-In access. ***
                        </div>
                    </div>
                @else
                    <!-- Pending: 2-Day Plain Notice to submit to HR in person -->
                    <div class="p-5 bg-amber-50 dark:bg-amber-950/30 rounded-2xl border-2 border-amber-300 dark:border-amber-800/80 space-y-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center font-black text-xl shrink-0">
                                ⚠️
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 block">Official HR Directive</span>
                                <h4 class="text-sm sm:text-base font-black text-amber-950 dark:text-amber-100">
                                    Action Required: Submit Written Notice to Explain (NTE) to HR
                                </h4>
                            </div>
                        </div>

                        <div class="text-xs text-amber-900 dark:text-amber-200 space-y-2 leading-relaxed">
                            <p>
                                You are required to <strong>personally go to the Human Resources (HR) Department</strong> for the submission of your written Notice to Explain (NTE) within two (2) days (Compliance Deadline: <strong>{{ $notice->explanation_deadline?->format('F d, Y') }}</strong>).
                            </p>
                            <div class="p-3 bg-amber-100/90 dark:bg-amber-900/40 rounded-xl border border-amber-300 dark:border-amber-700 font-bold text-amber-950 dark:text-amber-100">
                                ⚠️ Important: Failure to submit your NTE to HR within two (2) days will restrict your account and you will NOT be able to time in.
                            </div>
                        </div>

                        <div class="pt-1 text-center font-black text-xs text-red-700 dark:text-red-400">
                            *** Please see the HR Officer for clarification. ***
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Case Resolution if Available -->
        @if(in_array($notice->status, ['resolved', 'closed']))
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                <h4 class="font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 text-[11px]">
                    HR Final Disposition &amp; Resolution
                </h4>
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                    {{ $notice->hr_resolution_notes }}
                </div>
            </div>
        @endif

    </div>

</div>
@endsection
