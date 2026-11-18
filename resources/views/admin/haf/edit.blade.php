@extends('layouts.admin')

@section('title', 'Edit HAF Record - ' . ($haf->employee->name ?? 'Member'))
@section('page-title', 'Edit Health Assistance Record')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.haf.show', $haf) }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-red-700 dark:text-slate-400 dark:hover:text-red-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Member Details</span>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-slate-100">Edit Health Assistance Fund Record</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Update membership parameters for {{ $haf->employee->name }}</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $haf->status_badge_class }}">
                {{ $haf->status_label }}
            </span>
        </div>

        <form action="{{ route('admin.haf.update', $haf) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <!-- Control # and Plan Year -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Member Control Number</label>
                    <input type="text" name="member_number" value="{{ old('member_number', $haf->member_number) }}"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Plan Year</label>
                    <input type="text" name="plan_year" value="{{ old('plan_year', $haf->plan_year) }}"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <!-- Validity Date Range -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Effective Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $haf->start_date ? $haf->start_date->toDateString() : '') }}" required
                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Expiration Date (365d) *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $haf->end_date ? $haf->end_date->toDateString() : '') }}" required
                           class="w-full text-xs rounded-xl bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-bold text-amber-700 dark:text-amber-400">
                </div>
            </div>

            <!-- Financials -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Voluntary Contribution (₱) *</label>
                    <input type="number" step="0.01" name="contribution_amount" value="{{ old('contribution_amount', $haf->contribution_amount) }}" required min="0"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">HMO Coverage Benefit Limit (₱) *</label>
                    <input type="number" step="0.01" name="coverage_amount" value="{{ old('coverage_amount', $haf->coverage_amount) }}" required min="0"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500 font-bold">
                </div>
            </div>

            <!-- Payment Mode & Reference -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                        <option value="voluntary_contribution" {{ old('payment_method', $haf->payment_method) === 'voluntary_contribution' ? 'selected' : '' }}>Voluntary Contribution</option>
                        <option value="payroll_deduction" {{ old('payment_method', $haf->payment_method) === 'payroll_deduction' ? 'selected' : '' }}>Payroll Deduction</option>
                        <option value="direct_cash" {{ old('payment_method', $haf->payment_method) === 'direct_cash' ? 'selected' : '' }}>Direct Cash</option>
                        <option value="bank_transfer" {{ old('payment_method', $haf->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank / GCash Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Receipt / Ref #</label>
                    <input type="text" name="receipt_no" value="{{ old('receipt_no', $haf->receipt_no) }}"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Membership Status</label>
                    <select name="status" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">
                        <option value="active" {{ old('status', $haf->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="cancelled" {{ old('status', $haf->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Receipt attachment -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Replace Receipt / Proof Attachment</label>
                <input type="file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                @if($haf->receipt_file_path)
                    <p class="text-[11px] text-slate-500 mt-1">Current file: <a href="{{ Storage::url($haf->receipt_file_path) }}" target="_blank" class="text-red-700 font-bold hover:underline">View attached receipt</a></p>
                @endif
            </div>

            <!-- Remarks -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Plan Notes</label>
                <textarea name="notes" rows="3" class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 py-2.5 focus:ring-red-500 focus:border-red-500">{{ old('notes', $haf->notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                <!-- Delete Button -->
                <button type="button" onclick="if(confirm('Delete this Health Assistance Fund record permanently?')) document.getElementById('delete-haf-form').submit();"
                        class="px-4 py-2 text-xs font-bold text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 rounded-xl transition">
                    Delete Record
                </button>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.haf.show', $haf) }}" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-800 rounded-xl transition shadow-sm">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>

        <form id="delete-haf-form" action="{{ route('admin.haf.destroy', $haf) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
