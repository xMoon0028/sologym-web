@extends('layouts.admin')

@section('title', 'Staff Requests')
@section('page-title', 'Staff Requests & Seminar Reports')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Pending Requests</span>
            <p class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-2 font-mono">{{ $stats['total_pending'] }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Awaiting HR Review</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Pending Seminars</span>
            <p class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2 font-mono">{{ $stats['pending_seminars'] }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Training Attendance Requests</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Seminar Reports Due</span>
            <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-2 font-mono">{{ $stats['pending_reports'] }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Submitted / Needs Review</p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider block">Pending Documents</span>
            <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-2 font-mono">{{ $stats['pending_documents'] }}</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">COE, Clearance, etc.</p>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.requests.index') }}" class="flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee or title..."
                       class="px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500 w-full sm:w-64">

                <select name="type" onchange="this.form.submit()"
                        class="px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500">
                    <option value="">All Types</option>
                    <option value="seminar" {{ request('type') === 'seminar' ? 'selected' : '' }}>Seminars Only</option>
                    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Documents Only</option>
                </select>

                <select name="status" onchange="this.form.submit()"
                        class="px-3.5 py-2 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved / Report Due</option>
                    <option value="report_pending" {{ request('status') === 'report_pending' ? 'selected' : '' }}>Report Submitted</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Disapproved</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('admin.requests.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Employee</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Title / Purpose</th>
                        <th class="py-3 px-4">Dates / Deadline</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                <span class="font-bold block">{{ $req->employee?->name }}</span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $req->employee?->department?->name }}</span>
                            </td>
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
                                <a href="{{ route('admin.requests.show', $req) }}" class="font-bold hover:text-red-700 dark:hover:text-red-400 transition block">
                                    {{ $req->is_seminar ? $req->seminar_title : $req->document_type }}
                                </a>
                                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5 truncate max-w-md">
                                    {{ $req->is_seminar ? ($req->organizer ?: 'External event') : ('Purpose: ' . $req->purpose) }}
                                </p>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                @if($req->is_seminar && $req->start_date)
                                    {{ $req->start_date->format('M d, Y') }}
                                    @if($req->end_date && !$req->start_date->equalTo($req->end_date))
                                        — {{ $req->end_date->format('M d') }}
                                    @endif
                                @elseif($req->date_needed)
                                    Needed: {{ $req->date_needed->format('M d, Y') }}
                                @else
                                    {{ $req->created_at->format('M d, Y') }}
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full border {{ $req->badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.requests.show', $req) }}" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs">
                                    <span>Review / Manage</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-500 dark:text-slate-400">
                                <p class="text-sm font-bold">No requests found</p>
                                <p class="text-xs mt-1">There are no staff requests matching your current filters.</p>
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
