@extends('layouts.portal')

@section('title', 'Staff Requests & Seminars')
@section('page-title', 'Staff Requests & Seminars')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Requests &amp; Seminars</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Submit seminar/training participation requests or official HR documents with two-stage leadership approval.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            <a href="{{ route('portal.requests.create-seminar') }}" class="flex-1 sm:flex-initial px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>+ Request Seminar</span>
            </a>
            <a href="{{ route('portal.requests.create-document') }}" class="flex-1 sm:flex-initial px-4 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>+ Request Document</span>
            </a>
        </div>
    </div>

    <!-- PENDING APPROVAL QUEUES FOR SECTION HEADS & EXECUTIVE OFFICE -->
    @if(isset($pendingSectionReviews) && $pendingSectionReviews->count() > 0)
        <div class="bg-amber-50 dark:bg-amber-950/40 rounded-2xl border-2 border-amber-300 dark:border-amber-800/80 p-5 space-y-3 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-amber-200 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 flex items-center justify-center font-black text-xs">⚠️</span>
                    <h3 class="text-sm font-black text-amber-950 dark:text-amber-100">
                        Action Required: You have {{ $pendingSectionReviews->count() }} Seminar Request(s) awaiting Section Head endorsement
                    </h3>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-amber-200/80 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200">
                    Section Head Queue
                </span>
            </div>
            <div class="divide-y divide-amber-200/60 dark:divide-amber-900/50 bg-white dark:bg-slate-900 rounded-xl border border-amber-200 dark:border-amber-800/60 overflow-hidden">
                @foreach($pendingSectionReviews as $rev)
                    <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-amber-50/40 dark:hover:bg-slate-800/60 transition">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $rev->employee?->name }}</span>
                                <span class="text-[11px] text-slate-500 font-mono">({{ $rev->employee?->department?->name }})</span>
                                <span class="text-[11px] text-slate-400">&bull; {{ $rev->created_at->format('M d, Y') }}</span>
                            </div>
                            <p class="text-xs font-semibold text-purple-700 dark:text-purple-400 mt-0.5">
                                {{ $rev->seminar_title }}
                                <span class="text-slate-500 font-normal">({{ $rev->start_date?->format('M d') }} - {{ $rev->end_date?->format('M d, Y') }})</span>
                            </p>
                        </div>
                        <a href="{{ route('portal.requests.show', $rev) }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition whitespace-nowrap self-end sm:self-center shadow-2xs">
                            Review &amp; Endorse &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($pendingExecutiveReviews) && $pendingExecutiveReviews->count() > 0)
        <div class="bg-purple-50 dark:bg-purple-950/40 rounded-2xl border-2 border-purple-300 dark:border-purple-800/80 p-5 space-y-3 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-lg bg-purple-200 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200 flex items-center justify-center font-black text-xs">🎓</span>
                    <h3 class="text-sm font-black text-purple-950 dark:text-purple-100">
                        Action Required: You have {{ $pendingExecutiveReviews->count() }} Seminar Request(s) awaiting Executive Office final approval
                    </h3>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-purple-200/80 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200">
                    Executive Queue
                </span>
            </div>
            <div class="divide-y divide-purple-200/60 dark:divide-purple-900/50 bg-white dark:bg-slate-900 rounded-xl border border-purple-200 dark:border-purple-800/60 overflow-hidden">
                @foreach($pendingExecutiveReviews as $rev)
                    <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-purple-50/40 dark:hover:bg-slate-800/60 transition">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs text-slate-900 dark:text-white">{{ $rev->employee?->name }}</span>
                                <span class="text-[11px] text-slate-500 font-mono">({{ $rev->employee?->department?->name }})</span>
                                @if($rev->section_head_approval)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        ✓ Endorsed by {{ $rev->section_head_approval->approver_name }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs font-semibold text-purple-700 dark:text-purple-400 mt-0.5">
                                {{ $rev->seminar_title }}
                                <span class="text-slate-500 font-normal">({{ $rev->start_date?->format('M d') }} - {{ $rev->end_date?->format('M d, Y') }})</span>
                            </p>
                        </div>
                        <a href="{{ route('portal.requests.show', $rev) }}" class="px-3.5 py-1.5 bg-purple-700 hover:bg-purple-800 text-white rounded-lg text-xs font-bold transition whitespace-nowrap self-end sm:self-center shadow-2xs">
                            Grant Final Approval &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 flex-wrap">
        <a href="{{ route('portal.requests.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ !request('type') ? 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            My Requests ({{ $employee->staffRequests()->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['type' => 'seminar']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ request('type') === 'seminar' ? 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            Seminars ({{ $employee->staffRequests()->where('type', 'seminar')->count() }})
        </a>
        <a href="{{ route('portal.requests.index', ['type' => 'document']) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ request('type') === 'document' ? 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200/80 dark:border-red-900/60' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
            Documents ({{ $employee->staffRequests()->where('type', 'document')->count() }})
        </a>
    </div>

    <!-- Requests Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4 w-32 whitespace-nowrap">Type</th>
                        <th class="py-3 px-4 min-w-[200px]">Request Details</th>
                        <th class="py-3 px-4 w-44 whitespace-nowrap">Schedule / Date</th>
                        <th class="py-3 px-4 w-48 whitespace-nowrap">Approval Status</th>
                        <th class="py-3 px-4 w-32 whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($req->is_seminar)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800/60">
                                        🎓 Seminar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60">
                                        📄 Document
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-white">
                                @if($req->is_seminar)
                                    <a href="{{ route('portal.requests.show', $req) }}" class="font-bold hover:text-red-700 dark:hover:text-red-400 transition block">
                                        {{ $req->seminar_title }}
                                    </a>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">
                                        {{ $req->organizer ?: 'Organizer not specified' }} &bull; {{ $req->venue ?: 'Venue TBD' }}
                                    </p>
                                @else
                                    <a href="{{ route('portal.requests.show', $req) }}" class="font-bold hover:text-red-700 dark:hover:text-red-400 transition block">
                                        {{ $req->document_type }}
                                    </a>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 truncate max-w-sm">
                                        Purpose: {{ $req->purpose }}
                                    </p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                @if($req->is_seminar && $req->start_date)
                                    {{ $req->start_date->format('M d, Y') }}
                                    @if($req->end_date && !$req->start_date->equalTo($req->end_date))
                                        — {{ $req->end_date->format('M d, Y') }}
                                    @endif
                                @elseif($req->date_needed)
                                    Needed by: <span class="font-bold">{{ $req->date_needed->format('M d, Y') }}</span>
                                @else
                                    <span class="text-slate-400">Filed {{ $req->created_at->format('M d, Y') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border whitespace-nowrap {{ $req->badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                @if($req->requires_seminar_report)
                                    <a href="{{ route('portal.requests.show', $req) }}#report-section" class="px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Submit Report</span>
                                    </a>
                                @endif

                                @if($req->document_file_path)
                                    <a href="{{ asset('storage/' . $req->document_file_path) }}" target="_blank" class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download File</span>
                                    </a>
                                @endif

                                <a href="{{ route('portal.requests.show', $req) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                    <span>View</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-500 dark:text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-bold">No requests filed yet</p>
                                <p class="text-xs mt-1">Need to attend a seminar or request an official HR document? Use the buttons above.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
