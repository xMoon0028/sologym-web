@extends('layouts.admin')

@section('title', 'Leave Records & Approvals')
@section('page-title', 'Leave Management')

@section('content')
@php
    $currentAdminEmp = auth()->user()->employee;
    $isHrUser = $currentAdminEmp && $currentAdminEmp->approval_role === 'hr_officer';
    $isExecutiveUser = $currentAdminEmp && in_array($currentAdminEmp->approval_role, ['executive_office', 'executive_director']);
    $isExecutiveDirector = $currentAdminEmp && $currentAdminEmp->approval_role === 'executive_director';
    $isSuperAdmin = (!auth()->user()->employee && auth()->user()->email === 'admin@lasac.com');
@endphp
<div class="space-y-6" x-data="{
    reviewModal: false,
    selectedReq: null,
    
    openReview(req) {
        this.selectedReq = req;
        this.reviewModal = true;
    }
}">

    <!-- Header Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <a href="{{ route('admin.leaves.index', ['stage' => 'section_head']) }}"
           class="p-4 bg-white dark:bg-slate-900 rounded-2xl border transition shadow-2xs hover:border-amber-300 dark:hover:border-amber-500 {{ $tab === 'section_head' ? 'ring-2 ring-amber-500 border-amber-500' : 'border-slate-200/80 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Stage 1: Section Head</span>
                <span class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 flex items-center justify-center font-bold text-xs">1</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white font-mono mt-2">{{ $counts['pending_section_head'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">1-day approval window</p>
        </a>

        <a href="{{ route('admin.leaves.index', ['stage' => 'executive_office']) }}"
           class="p-4 bg-white dark:bg-slate-900 rounded-2xl border transition shadow-2xs hover:border-purple-300 dark:hover:border-purple-500 {{ $tab === 'executive_office' ? 'ring-2 ring-purple-500 border-purple-500' : 'border-slate-200/80 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Stage 2: Exec Office</span>
                <span class="w-6 h-6 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-800 dark:text-purple-300 flex items-center justify-center font-bold text-xs">2</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white font-mono mt-2">{{ $counts['pending_executive'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">1-day approval window</p>
        </a>

        <a href="{{ route('admin.leaves.index', ['stage' => 'executive_director']) }}"
           class="p-4 bg-white dark:bg-slate-900 rounded-2xl border transition shadow-2xs hover:border-fuchsia-300 dark:hover:border-fuchsia-500 {{ $tab === 'executive_director' ? 'ring-2 ring-fuchsia-500 border-fuchsia-500' : 'border-slate-200/80 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Exec Director</span>
                <span class="w-6 h-6 rounded-lg bg-fuchsia-100 dark:bg-fuchsia-950/80 text-fuchsia-800 dark:text-fuchsia-300 flex items-center justify-center font-bold text-xs">★</span>
            </div>
            <p class="text-3xl font-black text-slate-900 dark:text-white font-mono mt-2">{{ $counts['pending_executive_director'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Exec Office leave requests</p>
        </a>

        <a href="{{ route('admin.leaves.index', ['stage' => 'approved']) }}"
           class="p-4 bg-white dark:bg-slate-900 rounded-2xl border transition shadow-2xs hover:border-emerald-300 dark:hover:border-emerald-500 {{ $tab === 'approved' ? 'ring-2 ring-emerald-500 border-emerald-500' : 'border-slate-200/80 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Approved Leaves</span>
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-xs">✓</span>
            </div>
            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-400 font-mono mt-2">{{ $counts['approved'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Attendance logs generated</p>
        </a>

        <a href="{{ route('admin.leaves.index', ['stage' => 'expired']) }}"
           class="p-4 bg-white dark:bg-slate-900 rounded-2xl border transition shadow-2xs hover:border-orange-300 dark:hover:border-orange-500 {{ $tab === 'expired' ? 'ring-2 ring-orange-500 border-orange-500' : 'border-slate-200/80 dark:border-slate-800' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200">Expired (1-Day)</span>
                <span class="w-6 h-6 rounded-lg bg-orange-100 dark:bg-orange-950/80 text-orange-800 dark:text-orange-300 flex items-center justify-center font-bold text-xs">⌛</span>
            </div>
            <p class="text-3xl font-black text-orange-700 dark:text-orange-400 font-mono mt-2">{{ $counts['expired'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Exceeded 24h approval limit</p>
        </a>
    </div>

    @if($isHrUser)
        <div class="p-4 bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 rounded-2xl flex items-center gap-3 text-xs text-blue-950 dark:text-blue-200">
            <span class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 flex items-center justify-center font-black">ℹ️</span>
            <div>
                <p class="font-bold">HR Leave Records &amp; Email Monitoring View</p>
                <p class="text-blue-800/80 dark:text-blue-300/80 mt-0.5">HR has full visibility of all leave records and receives all email notifications. Leave approvals are processed directly by the Section Head (1-day limit) and Executive Office / Executive Director (1-day limit).</p>
            </div>
        </div>
    @endif

    <!-- Navigation Tabs & Manual Encoding Button -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 px-3 sm:px-4 pt-3 flex-wrap gap-2">
            <div class="flex items-center gap-1 overflow-x-auto scrollbar-none flex-nowrap pb-px">
                @php
                    $tabs = [
                        'all' => 'All Records (' . ($counts['all'] ?? 0) . ')',
                        'section_head' => '1. Section Head (' . ($counts['pending_section_head'] ?? 0) . ')',
                        'executive_office' => '2. Executive Office (' . ($counts['pending_executive'] ?? 0) . ')',
                        'executive_director' => 'Executive Director (' . ($counts['pending_executive_director'] ?? 0) . ')',
                        'approved' => 'Approved (' . ($counts['approved'] ?? 0) . ')',
                        'rejected' => 'Rejected (' . ($counts['rejected'] ?? 0) . ')',
                        'expired' => 'Expired (' . ($counts['expired'] ?? 0) . ')',
                    ];
                @endphp
                @foreach($tabs as $tabKey => $label)
                    <a href="{{ route('admin.leaves.index', ['stage' => $tabKey]) }}"
                       class="py-2.5 px-3 sm:px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap {{ $tab === $tabKey ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="pb-2">
                <a href="{{ route('admin.leaves.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Encode Manual Leave</span>
                </a>
            </div>
        </div>

        <!-- Leaves Table -->
        @if($requests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/60 uppercase text-slate-500 dark:text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Ref / Employee</th>
                            <th class="px-5 py-3.5">Leave Type</th>
                            <th class="px-5 py-3.5">Inclusive Dates</th>
                            <th class="px-5 py-3.5">Days</th>
                            <th class="px-5 py-3.5">Reason</th>
                            <th class="px-5 py-3.5">Stage &amp; 1-Day Limit</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($requests as $req)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-[10px] font-bold text-slate-400 block">#LV-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <div class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">
                                        <a href="{{ route('admin.employees.profile', $req->employee) }}" class="hover:text-red-700 dark:hover:text-red-400 hover:underline">
                                            {{ $req->employee->name }}
                                        </a>
                                    </div>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">{{ $req->employee->department->name ?? 'No Department' }} &bull; {{ $req->employee->approval_role_label }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $req->badge_class }}">
                                        {{ $req->type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $req->start_date->format('M d, Y') }}</span>
                                    @if(!$req->start_date->isSameDay($req->end_date))
                                        <span class="text-slate-400"> to </span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $req->end_date->format('M d, Y') }}</span>
                                    @endif
                                    @if($req->is_half_day)
                                        <span class="block text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-0.5">Half Day ({{ ucfirst($req->half_day_type ?? 'Morning') }})</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-bold font-mono text-slate-800 dark:text-slate-200">
                                    {{ $req->days_count }} {{ Str::plural('day', $req->days_count) }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $req->reason }}">
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
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>{{ $req->status_label }}</span>
                                            </span>
                                            @if($req->stage_expires_at)
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                                    Expires: <strong>{{ $req->stage_expires_at->timezone('Asia/Manila')->format('M d, h:i A') }}</strong>
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a href="{{ route('admin.leaves.show', $req) }}"
                                       class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                        View Record
                                    </a>
                                    @if($req->is_pending && (($req->status === 'pending_executive' && $isExecutiveUser) || ($req->status === 'pending_executive_director' && $isExecutiveDirector) || $isSuperAdmin))
                                        <button type="button" @click="openReview({{ json_encode($req) }})"
                                                class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                            Review &amp; Approve
                                        </button>
                                    @endif
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
                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No leave requests found in this category.</p>
                <p class="text-xs text-slate-400 mt-1">Leave applications will appear here as staff file them.</p>
            </div>
        @endif
    </div>

    <!-- REVIEW & APPROVAL MODAL -->
    <div x-show="reviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto" @click.outside="reviewModal = false">
            <template x-if="selectedReq">
                <div>
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-5">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Leave Application #LV-<span x-text="String(selectedReq.id).padStart(4, '0')"></span></h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border" :class="selectedReq.badge_class" x-text="selectedReq.type_label"></span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">1-Day Approval Window Per Stage</p>
                        </div>
                        <button type="button" @click="reviewModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5 text-xs">
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-1">
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">Applicant Details</span>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-sm" x-text="selectedReq.employee ? selectedReq.employee.name : ''"></p>
                            <p class="text-slate-600 dark:text-slate-400" x-text="selectedReq.employee ? (selectedReq.employee.position + ' • ' + (selectedReq.employee.department ? selectedReq.employee.department.name : '')) : ''"></p>
                        </div>

                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-1">
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">Schedule &amp; Units</span>
                            <p class="font-bold text-slate-900 dark:text-slate-100 text-sm" x-text="selectedReq.days_count + ' day(s)'"></p>
                            <p class="text-slate-600 dark:text-slate-400" x-text="(selectedReq.start_date ? selectedReq.start_date.substring(0, 10) : '') + ' to ' + (selectedReq.end_date ? selectedReq.end_date.substring(0, 10) : '')"></p>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/80 mb-5 text-xs">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block mb-1">Reason Stated by Staff:</span>
                        <p class="text-slate-800 dark:text-slate-200 italic" x-text="selectedReq.reason"></p>
                    </div>

                    <form :action="'{{ url('/admin/leaves') }}/' + selectedReq.id + '/approve'" method="POST" class="space-y-4 pt-3 border-t border-slate-200 dark:border-slate-800">
                        @csrf
                        <input type="hidden" name="override_stage" :value="selectedReq.current_stage">

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Decision *</label>
                            <select name="action" required class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold focus:ring-red-500 focus:border-red-500 bg-white dark:bg-slate-800 dark:text-slate-100">
                                <option value="approve">✅ Approve Leave Request</option>
                                <option value="reject">❌ Disapprove &amp; Reject Request</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Evaluation Notes / Remarks</label>
                            <textarea name="notes" rows="3" placeholder="Optional directive or remarks..."
                                      class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl text-xs focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="reviewModal = false" class="w-full sm:w-auto px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                                Cancel
                            </button>
                            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-red-700 hover:bg-red-800 text-white text-xs font-bold transition shadow-xs">
                                Submit Decision
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
