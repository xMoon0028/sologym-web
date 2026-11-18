@extends('layouts.admin')

@section('title', 'Manual Leave Encoding - HR Admin')
@section('page-title', 'Manual Leave Encoding')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.leaves.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Leave Records</span>
        </a>
        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300">
            HR Admin Direct Entry
        </span>
    </div>

    <!-- Info banner -->
    <div class="p-4 bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex items-start gap-3 text-xs text-amber-950 dark:text-amber-200">
        <span class="text-xl">ℹ️</span>
        <div>
            <p class="font-bold text-sm">Manual Leave Encoding by HR</p>
            <p class="text-amber-900/80 dark:text-amber-300/80 mt-1 leading-relaxed">
                This form allows HR to directly record authorized leaves for staff members (including emergency situations, offline approvals, or retrospective recordings).
                Submitting this form immediately marks the leave as <strong>Approved</strong>, records attendance logs as <strong>Leave</strong>, deducts appropriate leave balances/time credits, and notifies the employee via email.
            </p>
        </div>
    </div>

    <!-- Encoding Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.leaves.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{
            isHalfDay: {{ old('is_half_day') ? 'true' : 'false' }},
            startDate: '{{ old('start_date') }}',
            endDate: '{{ old('end_date') }}',
            daysCount: {{ old('days_count', 1.0) }},
            selectedLeaveType: '{{ old('leave_type', 'vacation') }}',

            updateDates() {
                if (this.isHalfDay) {
                    this.daysCount = 0.5;
                    if (this.startDate) {
                        this.endDate = this.startDate;
                    }
                } else if (this.startDate && this.endDate) {
                    const start = new Date(this.startDate);
                    const end = new Date(this.endDate);
                    if (end >= start) {
                        const diffTime = Math.abs(end - start);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        this.daysCount = diffDays;
                    }
                }
            }
        }">
            @csrf

            <!-- Employee Selection -->
            <div>
                <label for="employee_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Select Employee <span class="text-red-600">*</span>
                </label>
                <select name="employee_id" id="employee_id" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">
                    <option value="">-- Choose Staff Member --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }} ({{ $emp->employee_no ?? 'ID: ' . $emp->id }}) - {{ $emp->department->name ?? 'No Department' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Leave Type & Duration Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="leave_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Leave Type <span class="text-red-600">*</span>
                    </label>
                    <select name="leave_type" id="leave_type" required x-model="selectedLeaveType"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">
                        <option value="vacation">Vacation Leave (VL)</option>
                        <option value="sick">Sick Leave (SL)</option>
                        <option value="emergency">Emergency Leave (EL)</option>
                        <option value="time_off">Time Off Leave (TOL)</option>
                        <option value="maternity">Maternity Leave (ML)</option>
                        <option value="paternity">Paternity Leave (PL)</option>
                        <option value="solo_parent">Parental Leave / Solo Parent Leave (SPL)</option>
                        <option value="vawc">Leave for Victims of Violence Against Women &amp; Children / Battered Women Leave (BWL)</option>
                        <option value="gynecological">Gynecological Leaves for Women (GL)</option>
                        <option value="bereavement">Bereavement Leave</option>
                        <option value="unpaid">Leave Without Pay (Unpaid)</option>
                    </select>
                    @error('leave_type')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror

                    <div x-show="selectedLeaveType === 'sick'" class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60 rounded-xl text-xs text-rose-800 dark:text-rose-300 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <strong>Sick Leave must be filed within 3 days of return to work and must be accompanied by a medical certificate and/or Fit to Work recommendation, if SL is more than 3 days.</strong>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Duration Option
                    </label>
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <input type="checkbox" name="is_half_day" value="1" x-model="isHalfDay" @change="updateDates()"
                                   class="w-4 h-4 rounded text-red-700 border-slate-300 focus:ring-red-600">
                            <span>Half-Day Leave (0.5 day)</span>
                        </label>
                    </div>

                    <div x-show="isHalfDay" x-cloak class="mt-3">
                        <select name="half_day_type"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-medium">
                            <option value="first_half" {{ old('half_day_type') == 'first_half' ? 'selected' : '' }}>Morning (First Half: 8:00 AM - 12:00 NN)</option>
                            <option value="second_half" {{ old('half_day_type') == 'second_half' ? 'selected' : '' }}>Afternoon (Second Half: 1:00 PM - 5:00 PM)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dates & Number of Days -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="start_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Start Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" required x-model="startDate" @change="updateDates()"
                           :max="selectedLeaveType === 'sick' ? '{{ now()->format('Y-m-d') }}' : ''"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">
                    @error('start_date')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        End Date <span class="text-red-600">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" required x-model="endDate" @change="updateDates()" :disabled="isHalfDay"
                           :max="selectedLeaveType === 'sick' ? '{{ now()->format('Y-m-d') }}' : ''"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium disabled:opacity-50">
                    @error('end_date')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="days_count" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Total Days <span class="text-red-600">*</span>
                    </label>
                    <input type="number" step="0.5" min="0.5" name="days_count" id="days_count" required x-model="daysCount"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">
                    @error('days_count')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Reason for Leave <span class="text-red-600">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" required placeholder="State the specific reason or context for this leave..."
                          class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Internal HR Notes -->
            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    HR Internal Notes / Authority (Optional)
                </label>
                <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="e.g. Authorized by Executive Director via memo / Emergency hospital call"
                       class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-red-600 focus:border-red-600 font-medium">
                @error('notes')
                    <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Optional Attachment -->
            <div>
                <label for="attachment" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Supporting Document / Memo (Optional)
                </label>
                <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-700 file:text-white hover:file:bg-red-800">
                <p class="text-[11px] text-slate-400 mt-1">Accepted formats: PDF, JPG, PNG (Max 5MB).</p>
                @error('attachment')
                    <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="{{ route('admin.leaves.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold tracking-wide shadow-sm hover:shadow transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save &amp; Record Approved Leave</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
