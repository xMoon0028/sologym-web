@extends('layouts.admin')

@section('title', 'Offset Time Credit Ledger - ' . $employee->name)
@section('page-title', 'Offset Time Credit Ledger: ' . $employee->name)

@section('content')
<div class="space-y-6" x-data="{
    adjustModal: false,
    rejectModal: false,
    rejectUrl: '',
    openReject(url) {
        this.rejectUrl = url;
        this.rejectModal = true;
    }
}">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.time-credits.index') }}" class="text-xs font-bold text-slate-500 hover:text-red-700 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Offset Time Credits Overview</span>
        </a>
    </div>

    <!-- Summary Box -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $employee->name }}</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ $employee->employee_no ?? 'No ID' }}</span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $employee->position ?? '—' }} &bull; {{ $employee->department->name ?? 'Unassigned Unit' }}</p>
        </div>

        <div class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-4 sm:gap-6 w-full lg:w-auto">
            <!-- Approved Balance -->
            <div class="text-left sm:text-right">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Approved Offset Balance</span>
                <p class="text-2xl font-black text-emerald-700 dark:text-emerald-400 font-mono">{{ number_format($balance, 2) }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">hours</span></p>
            </div>

            <!-- Pending Hours -->
            @if($pendingHours > 0)
                <div class="text-left sm:text-right border-l-0 sm:border-l border-slate-200 dark:border-slate-700 pl-0 sm:pl-6">
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block flex items-center gap-1 sm:justify-end">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending Verification
                    </span>
                    <p class="text-2xl font-black text-amber-700 dark:text-amber-400 font-mono">+{{ number_format($pendingHours, 2) }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">hrs</span></p>
                    <span class="text-[10px] text-amber-700 dark:text-amber-400 block mt-0.5">({{ round($pendingHours * 60) }} mins unapproved)</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <form method="POST" action="{{ route('admin.time-credits.process-attendance', $employee) }}">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition shadow-2xs text-center">
                        Scan Attendance Logs
                    </button>
                </form>
                <button type="button" @click="adjustModal = true" class="w-full sm:w-auto px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition shadow-xs inline-flex items-center justify-center gap-1.5 text-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Adjust Credits</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Offset Credit Ledger &amp; Verification History</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Chronological record of earned overtimes, manual adjustments, and approved leaves</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 uppercase text-slate-400 font-bold text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Hours</th>
                        <th class="px-4 py-3">Details / Reason</th>
                        <th class="px-4 py-3 text-right">Balance After</th>
                        <th class="px-6 py-3 text-right">Verification Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ledger as $item)
                        <tr class="hover:bg-slate-50/60 transition {{ $item->status === 'pending' ? 'bg-amber-50/30' : '' }}">
                            <td class="px-6 py-3.5 font-semibold text-slate-800">{{ $item->date ? $item->date->format('D, M d, Y') : '—' }}</td>
                            <td class="px-4 py-3.5 uppercase text-[10px] font-bold">
                                <span class="px-2.5 py-0.5 rounded-full {{ $item->type === 'accrual' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($item->type === 'deduction' ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-blue-50 text-blue-800 border border-blue-200') }}">
                                    {{ $item->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($item->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Approved</span>
                                    </span>
                                @elseif($item->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Pending HR</span>
                                    </span>
                                @elseif($item->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200" title="{{ $item->rejection_reason }}">
                                        <span>Rejected</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1 font-bold font-mono text-xs {{ $item->hours >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                    <span>{{ $item->hours >= 0 ? '+' : '' }}{{ number_format($item->hours, 2) }} hrs</span>
                                    <span class="text-[10px] font-normal text-slate-500">({{ round($item->hours * 60) }}m)</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 max-w-xs">
                                <p class="truncate" title="{{ $item->reason }}">{{ $item->reason ?? 'Shift overtime' }}</p>
                                @if($item->approver && $item->status === 'approved')
                                    <p class="text-[10px] text-slate-400">Verified by: {{ $item->approver->name }}</p>
                                @elseif($item->rejection_reason)
                                    <p class="text-[10px] text-rose-600">Reason: {{ $item->rejection_reason }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold font-mono text-slate-900">
                                @if($item->status === 'approved')
                                    {{ number_format($item->running_balance, 2) }} hrs
                                @else
                                    <span class="text-slate-400 font-normal italic text-[11px]">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                @if($item->status === 'pending')
                                    <form method="POST" action="{{ route('admin.time-credits.approve', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] transition shadow-2xs">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" @click="openReject('{{ route('admin.time-credits.reject', $item) }}')"
                                            class="px-2.5 py-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg font-semibold text-[11px] transition shadow-2xs">
                                        Reject
                                    </button>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No time credit records found for this employee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ledger->hasPages())
            <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50">
                {{ $ledger->links() }}
            </div>
        @endif
    </div>

    <!-- Adjust Modal -->
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="adjustModal = false">
            <form method="POST" action="{{ route('admin.time-credits.store-adjustment') }}" class="flex flex-col h-full">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Adjust Offset Credits</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Target: <strong>{{ $employee->name }}</strong> (Available: {{ number_format($balance, 2) }} hrs)</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Action *</label>
                            <select name="type" required
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <option value="add">Grant / Add (+)</option>
                                <option value="deduct">Deduct / Use (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Hours *</label>
                            <input type="number" step="0.25" min="0.25" name="hours" required placeholder="e.g. 4.0 or 8.0"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Effective Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Reason / Justification *</label>
                        <input type="text" name="reason" required placeholder="e.g. Approved Offset Leave, Weekend Operations Compensation"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
                    <button type="button" @click="adjustModal = false" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition text-center">
                        Cancel
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 shadow-xs transition text-center">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden max-h-[90vh] flex flex-col" @click.outside="rejectModal = false">
            <form method="POST" :action="rejectUrl" class="flex flex-col h-full">
                @csrf
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Reject Time Credit Accrual</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">This excess time will not be credited to this employee's balance.</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Remarks / Reason (Optional)</label>
                        <input type="text" name="reason" placeholder="e.g. Unauthorized overtime"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
                    <button type="button" @click="rejectModal = false" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition text-center">
                        Cancel
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-bold hover:bg-rose-800 shadow-xs transition text-center">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
