@extends('layouts.admin')

@section('title', 'New Payroll Cutoff')
@section('page-title', 'Create Payroll Cutoff Period')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <a href="{{ route('admin.payroll.index') }}" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 flex items-center gap-1.5 transition">
        &larr; Back to Payroll Periods
    </a>

    <form method="POST" action="{{ route('admin.payroll.store') }}" class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Payroll Period Name *</label>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. August 6-20, 2026"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">This name will appear on the payslips (e.g. "August 6-20, 2026").</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{
            updateCutoff() {
                const start = document.getElementById('start_date').value;
                const end = document.getElementById('end_date').value;
                const cutoffSelect = document.getElementById('cutoff_type');
                if (cutoffSelect.dataset.manual === 'true') return;

                if (start || end) {
                    const startDay = start ? parseInt(start.split('-')[2]) : null;
                    const endDay = end ? parseInt(end.split('-')[2]) : null;
                    if ((startDay && startDay >= 18) || (endDay && endDay <= 8)) {
                        cutoffSelect.value = 'mid_month';
                    } else if ((startDay && startDay <= 10) || (endDay && endDay >= 15)) {
                        cutoffSelect.value = 'end_month';
                    }
                }
            }
        }">
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Start Date *</label>
                <input type="date" id="start_date" name="start_date" required value="{{ old('start_date') }}" @change="updateCutoff()"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">End Date *</label>
                <input type="date" id="end_date" name="end_date" required value="{{ old('end_date') }}" @change="updateCutoff()"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Salary &amp; Deduction Schedule</label>
            <select id="cutoff_type" name="cutoff_type" onchange="this.dataset.manual='true'"
                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <option value="auto">Auto-detect from dates (Recommended)</option>
                <option value="mid_month" {{ old('cutoff_type') === 'mid_month' ? 'selected' : '' }}>
                    Mid-Month Salary (21st–5th) &rarr; PhilHealth + HDMF + Absences/Late/Undertime (SSS = ₱0.00)
                </option>
                <option value="end_month" {{ old('cutoff_type') === 'end_month' ? 'selected' : '' }}>
                    End-Month Salary (6th–20th) &rarr; SSS + Absences/Late/Undertime (PhilHealth &amp; HDMF = ₱0.00)
                </option>
            </select>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                Dates 21&ndash;5 automatically deduct PhilHealth &amp; HDMF. Dates 6&ndash;20 automatically deduct SSS. You can also manually toggle this at any time.
            </p>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Date of Payment (Payout Date)</label>
            <input type="date" name="date_of_payment" value="{{ old('date_of_payment') }}"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Printed at the bottom of each payslip (e.g. "Date of Payment: August 28, 2026").</p>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Target Staff Visibility Date &amp; Time (Optional)</label>
            <input type="datetime-local" name="released_at" value="{{ old('released_at') }}"
                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Leave blank to schedule or release manually when finalizing payroll.</p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.payroll.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-center transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-xs text-center">
                Create &amp; Proceed to Upload
            </button>
        </div>
    </form>
</div>
@endsection
