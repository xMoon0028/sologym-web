@extends('layouts.admin')

@section('title', $employee->name . ' - Profile')
@section('page-title', 'Employee Profile')

@section('content')
<div class="space-y-6" x-data="{
    tab: 'info',
    adjustModal: false,
    certModal: false,
    separateModal: false
}">

    @if(!$employee->is_active)
        <!-- Resigned / Inactive Preservation Notice Banner -->
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-300/50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">Resigned / Inactive Staff Record (Permanently Preserved)</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 text-amber-900 dark:bg-amber-900 dark:text-amber-200">Archived 201 Record</span>
                    </div>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-1">
                        Separation Date: <strong>{{ $employee->separation_date ? $employee->separation_date->format('F d, Y') : 'Archived' }}</strong>
                        &bull; Reason: <strong>{{ $employee->separation_reason ?: 'Resigned' }}</strong>
                        @if($employee->separation_notes)
                            &bull; Notes: <em>"{{ $employee->separation_notes }}"</em>
                        @endif
                    </p>
                    <p class="text-[11px] text-amber-700/90 dark:text-amber-400 mt-0.5">All historical compensation rates, RFID attendance, leave history, loans, and past payslips are retained. This employee is excluded from new payroll runs.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.employees.reactivate', $employee) }}" class="flex-shrink-0" onsubmit="return confirm('Reactivate {{ addslashes($employee->name) }} to active staff?');">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reactivate Staff Record</span>
                </button>
            </form>
        </div>
    @endif

    <!-- Employee Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full lg:w-auto min-w-0">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-red-700 shadow-sm flex-shrink-0">
                @else
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-black text-xl uppercase border border-slate-200 dark:border-slate-700 shadow-2xs flex-shrink-0">
                        {{ substr($employee->name, 0, 2) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $employee->name }}</h2>
                        @if($employee->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                {{ $employee->employment_status ?? 'Regular' }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                                {{ $employee->separation_reason ?: 'Resigned / Inactive' }}
                            </span>
                        @endif
                        @if($employee->approval_role && $employee->approval_role !== 'none')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800/60">
                                <svg class="w-3.5 h-3.5 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Approver: {{ $employee->approval_role_label }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1">
                        {{ $employee->position ?? 'Unassigned Designation' }}
                        <span class="text-slate-300 dark:text-slate-600 mx-1">&bull;</span>
                        <span class="text-slate-500 dark:text-slate-400">{{ $employee->department->name ?? 'Unassigned Office' }}</span>
                    </p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap text-xs">
                        <span class="px-2.5 py-0.5 rounded-md font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                            ID: {{ $employee->employee_no ?? '—' }}
                        </span>
                        @if($employee->email || optional($employee->user)->email)
                            <span class="px-2.5 py-0.5 rounded-md font-mono font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/60 flex items-center gap-1.5" title="Google Account linked for SSO">
                                <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $employee->email ?? optional($employee->user)->email }}
                            </span>
                        @else
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="px-2.5 py-0.5 rounded-md font-mono text-[11px] bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 dark:hover:bg-amber-900 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 flex items-center gap-1 transition" title="Click to add work email for Google sign-in">
                                <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                No Work Email (Click to Set)
                            </a>
                        @endif
                        <span class="px-2.5 py-0.5 rounded-md font-mono font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60">
                            Offset: {{ number_format($employee->time_credit_balance, 2) }} hrs
                        </span>
                        <span class="px-2.5 py-0.5 rounded-md font-mono font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                            VL: {{ number_format($employee->remaining_vacation_leave, 1) }}d &bull; SL: {{ number_format($employee->remaining_sick_leave, 1) }}d &bull; EL: {{ number_format($employee->remaining_emergency_leave, 1) }}d
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap w-full lg:w-auto">
                <a href="{{ route('admin.employees.edit', $employee) }}" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-transparent dark:border-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Profile</span>
                </a>
                @if($employee->is_active)
                    <button type="button" @click="adjustModal = true" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-emerald-700 text-white rounded-xl text-xs font-semibold hover:bg-emerald-800 transition flex items-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Adjust Credits</span>
                    </button>
                    <a href="{{ route('admin.employees.certificates.create', $employee) }}" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>+ Certificate</span>
                    </a>
                    <button type="button" @click="separateModal = true" class="flex-1 sm:flex-initial justify-center px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs" title="Record employee resignation or separation">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Mark Resigned</span>
                    </button>
                @else
                    <form method="POST" action="{{ route('admin.employees.reactivate', $employee) }}" class="inline" onsubmit="return confirm('Reactivate {{ addslashes($employee->name) }} to active staff?');">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reactivate Staff Record</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 border-b border-slate-200 dark:border-slate-800 mt-6 overflow-x-auto scrollbar-none flex-nowrap pb-px">
            <button @click="tab = 'info'" :class="tab === 'info' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap">
                Personal &amp; Job Record
            </button>
            <button @click="tab = 'certs'" :class="tab === 'certs' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap flex items-center gap-1.5">
                <span>Certificates &amp; Training</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $employee->certificates->count() }}</span>
            </button>
            <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap">
                Attendance Punch Logs
            </button>
            <button @click="tab = 'credits'" :class="tab === 'credits' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap">
                Time Credits Ledger
            </button>
            <button @click="tab = 'loans'" :class="tab === 'loans' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap">
                Loans Ledger ({{ $employee->loans->count() }})
            </button>
            <button @click="tab = 'payslips'" :class="tab === 'payslips' ? 'border-red-700 text-red-700 dark:text-red-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'"
                    class="py-2.5 px-4 text-xs tracking-wider border-b-2 transition whitespace-nowrap">
                Payslips
            </button>
        </div>
    </div>

    <!-- TAB 1: Personal & Job Details -->
    <div x-show="tab === 'info'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Compensation & Rates -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Compensation &amp; Payroll Rates</h3>
                    <span class="text-[11px] font-mono text-slate-400">Monthly breakdown</span>
                </div>

                <div class="grid grid-cols-2 gap-3.5 text-xs">
                    <div class="p-3 bg-slate-50/70 rounded-xl border border-slate-200/60">
                        <span class="text-[11px] text-slate-400 uppercase font-semibold block">Monthly Total</span>
                        <p class="font-bold text-sm text-slate-900 mt-0.5 font-mono">PHP {{ number_format(optional($employee->compensation)->total_compensation, 2) }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100">
                        <span class="text-[11px] text-emerald-800 uppercase font-semibold block">Semi-Monthly Gross</span>
                        <p class="font-bold text-sm text-emerald-700 mt-0.5 font-mono">PHP {{ number_format(optional($employee->compensation)->semi_monthly_gross, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase font-semibold block">Basic Salary (80%)</span>
                        <p class="font-semibold text-slate-800 font-mono mt-0.5">PHP {{ number_format(optional($employee->compensation)->basic_salary_80, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase font-semibold block">Allowances (20%)</span>
                        <p class="font-semibold text-slate-800 font-mono mt-0.5">PHP {{ number_format(optional($employee->compensation)->allowance_20, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase font-semibold block">Daily Rate (x12 ÷ 261)</span>
                        <p class="font-semibold text-slate-800 font-mono mt-0.5">PHP {{ number_format(optional($employee->compensation)->per_day_rate, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[11px] uppercase font-semibold block">Hourly / Minute Rate</span>
                        <p class="font-semibold text-slate-800 font-mono mt-0.5">PHP {{ number_format(optional($employee->compensation)->per_hour_rate, 2) }} / {{ number_format(optional($employee->compensation)->per_minute_rate, 2) }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Standard Semi-Monthly Deductions</span>
                    <div class="grid grid-cols-3 gap-2.5 text-xs">
                        <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">SSS</span>
                            <p class="font-mono font-semibold text-slate-800 mt-0.5">PHP {{ number_format(optional($employee->compensation)->sss_deduction, 2) }}</p>
                        </div>
                        <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">PhilHealth</span>
                            <p class="font-mono font-semibold text-slate-800 mt-0.5">PHP {{ number_format(optional($employee->compensation)->ph_deduction, 2) }}</p>
                        </div>
                        <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Pag-IBIG</span>
                            <p class="font-mono font-semibold text-slate-800 mt-0.5">PHP {{ number_format(optional($employee->compensation)->pi_deduction, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Government IDs & Contact -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Government Identifiers</h3>
                </div>

                <div class="grid grid-cols-2 gap-3.5 text-xs font-mono">
                    <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                        <span class="text-slate-400 font-sans text-[10px] uppercase font-semibold block">SSS Number</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ optional($employee->governmentIds)->sss_no ?? '—' }}</p>
                    </div>
                    <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                        <span class="text-slate-400 font-sans text-[10px] uppercase font-semibold block">Pag-IBIG Number</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ optional($employee->governmentIds)->pagibig_no ?? '—' }}</p>
                    </div>
                    <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                        <span class="text-slate-400 font-sans text-[10px] uppercase font-semibold block">PhilHealth Number</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ optional($employee->governmentIds)->philhealth_no ?? '—' }}</p>
                    </div>
                    <div class="p-2.5 bg-slate-50/60 rounded-lg border border-slate-200/50">
                        <span class="text-slate-400 font-sans text-[10px] uppercase font-semibold block">Tax Identification (TIN)</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ optional($employee->governmentIds)->tin_no ?? '—' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Google Workspace SSO &amp; Account</span>
                    <div class="p-3 bg-red-50/50 rounded-xl border border-red-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-red-700 block">Work Email (@lasaclipa.org)</span>
                            <p class="font-bold text-slate-900 mt-0.5 text-xs font-mono">
                                {{ $employee->email ?? optional($employee->user)->email ?? 'No official email linked' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.employees.edit', $employee) }}" class="text-xs text-red-700 hover:text-red-800 font-semibold underline">
                            {{ ($employee->email || optional($employee->user)->email) ? 'Edit Email' : 'Add Email' }}
                        </a>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Geographic Location</span>
                    <div class="space-y-2 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2.5 bg-red-50/40 rounded-lg border border-red-100">
                                <span class="text-[10px] uppercase font-semibold text-red-700 block">Region</span>
                                <p class="font-bold text-red-900 mt-0.5 text-xs truncate">{{ optional($employee->address)->region ?? 'Region IV-A (CALABARZON)' }}</p>
                            </div>
                            <div class="p-2.5 bg-red-50/40 rounded-lg border border-red-100">
                                <span class="text-[10px] uppercase font-semibold text-red-700 block">Province</span>
                                <p class="font-bold text-red-900 mt-0.5 text-xs">{{ optional($employee->address)->province ?? 'Batangas' }}</p>
                            </div>
                            <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/60">
                                <span class="text-[10px] uppercase font-semibold text-slate-500 block">City / Municipality</span>
                                <p class="font-bold text-slate-800 mt-0.5 text-xs">{{ optional($employee->address)->municipality_city ?? 'Unspecified' }}</p>
                            </div>
                            <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/60">
                                <span class="text-[10px] uppercase font-semibold text-slate-500 block">Barangay</span>
                                <p class="font-bold text-slate-800 mt-0.5 text-xs">{{ optional($employee->address)->barangay ?? '—' }}</p>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Permanent Address</span>
                            <p class="font-medium text-slate-800 mt-0.5">{{ optional($employee->address)->permanent_address ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Current Address</span>
                            <p class="font-medium text-slate-800 mt-0.5">{{ optional($employee->address)->current_address ?? '—' }} <span class="text-slate-400 font-mono">(ZIP: {{ optional($employee->address)->zip_code ?? '—' }})</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Education & IQ Test -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Educational Background &amp; IQ Profile</h3>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Primary Education</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ optional($employee->education)->primary_school ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Secondary / Higher Education</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ optional($employee->education)->secondary_school ?? '—' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">Assessment &amp; Temperament</span>
                    <div class="grid grid-cols-4 gap-2 text-xs">
                        <div class="p-2 bg-slate-50/60 rounded-lg text-center border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Stanine</span>
                            <p class="font-bold text-slate-900 font-mono mt-0.5">{{ optional($employee->iq)->total_stanine ?? '—' }}</p>
                        </div>
                        <div class="p-2 bg-slate-50/60 rounded-lg text-center border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Verbal</span>
                            <p class="font-bold text-slate-900 font-mono mt-0.5">{{ optional($employee->iq)->verbal ?? '—' }}</p>
                        </div>
                        <div class="p-2 bg-slate-50/60 rounded-lg text-center border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Non-Verbal</span>
                            <p class="font-bold text-slate-900 font-mono mt-0.5">{{ optional($employee->iq)->non_verbal ?? '—' }}</p>
                        </div>
                        <div class="p-2 bg-slate-50/60 rounded-lg text-center border border-slate-200/50">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold block">Holland Fit</span>
                            <p class="font-bold text-slate-900 font-mono mt-0.5">{{ optional($employee->iq)->holland_personality ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Family & Previous Employment -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Family Background &amp; Prior Work</h3>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Marital Status / Wedding</span>
                        <p class="font-semibold text-slate-800 capitalize mt-0.5">{{ optional($employee->family)->marriage_type ?? 'Single' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Spouse Occupation</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ optional($employee->family)->spouse_occupation ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Children / Siblings</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ optional($employee->family)->no_of_children ?? 0 }} child / {{ optional($employee->family)->no_of_siblings ?? 0 }} siblings</p>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold block">Dependents</span>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ optional($employee->family)->dependents ?? 'None' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Previous Employment</span>
                    <div class="text-xs space-y-1.5">
                        <p><span class="text-slate-400 text-[10px] uppercase font-semibold">Prior Company:</span> <span class="font-semibold text-slate-800">{{ optional($employee->lastEmployment)->company ?? '—' }}</span></p>
                        <p><span class="text-slate-400 text-[10px] uppercase font-semibold">Position / Salary:</span> <span class="font-semibold text-slate-800">{{ optional($employee->lastEmployment)->position ?? '—' }} ({{ optional($employee->lastEmployment)->last_salary ? 'PHP ' . number_format((float) $employee->lastEmployment->last_salary, 2) : 'Salary not specified' }})</span></p>
                        <p><span class="text-slate-400 text-[10px] uppercase font-semibold">Reason for Leaving:</span> <span class="italic text-slate-600">{{ optional($employee->lastEmployment)->reason_for_leaving ?? '—' }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Leave Entitlements & Approver Authority (Admin Only) -->
            <div class="md:col-span-2 bg-amber-50/20 rounded-2xl border border-amber-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-amber-200/60 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800">Admin</span>
                            <span>Leave Entitlements &amp; Approver Authority</span>
                        </h3>
                    </div>
                    <span class="text-[10px] text-amber-700 font-bold bg-amber-100/70 px-2 py-0.5 rounded-full uppercase">Confidential</span>
                </div>

                <div class="space-y-3">
                    <div class="p-3 bg-white rounded-xl border border-amber-200/60 flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Approval Body Role</span>
                            <span class="text-xs font-bold text-slate-900">{{ $employee->approval_role_label }}</span>
                        </div>
                        @if(in_array($employee->approval_role, ['hr_officer', 'executive_office']))
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-900 border border-red-300 inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Admin Privileges Active</span>
                            </span>
                        @elseif($employee->approval_role === 'section_head')
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                Section Head Approver
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600">
                                Regular Staff (No Review Authority)
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Vacation Leave</span>
                            <p class="text-sm font-black text-slate-900 font-mono mt-0.5">{{ number_format($employee->remaining_vacation_leave, 1) }} <span class="text-[10px] text-slate-400 font-sans font-normal">/ {{ number_format($employee->vacation_leave_credits, 1) }}d</span></p>
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Sick Leave</span>
                            <p class="text-sm font-black text-slate-900 font-mono mt-0.5">{{ number_format($employee->remaining_sick_leave, 1) }} <span class="text-[10px] text-slate-400 font-sans font-normal">/ {{ number_format($employee->sick_leave_credits, 1) }}d</span></p>
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Emergency Leave</span>
                            <p class="text-sm font-black text-slate-900 font-mono mt-0.5">{{ number_format($employee->remaining_emergency_leave, 1) }} <span class="text-[10px] text-slate-400 font-sans font-normal">/ {{ number_format($employee->emergency_leave_credits, 1) }}d</span></p>
                        </div>
                    </div>

                    <div class="p-3 bg-sky-50/70 rounded-xl border border-sky-200 flex items-center justify-between">
                        <div>
                            <span class="text-sky-800 text-[10px] uppercase font-bold block">Time Credit (Offset) Balance</span>
                            <span class="text-xs text-sky-700">Accumulated through overtime / weekend duties</span>
                        </div>
                        <span class="text-sm font-black text-sky-900 font-mono">{{ number_format($employee->time_credit_balance, 2) }} hrs</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- TAB 2: Certificates & Training -->
    <div x-show="tab === 'certs'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Credential Records</h3>
            <a href="{{ route('admin.employees.certificates.create', $employee) }}" class="px-3.5 py-1.5 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition">
                + Add Record
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/75 uppercase text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Title</th>
                        <th class="px-4 py-3.5">Issuer</th>
                        <th class="px-4 py-3.5">Date Issued</th>
                        <th class="px-4 py-3.5">Expiry Date</th>
                        <th class="px-4 py-3.5">Attachment</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->certificates as $cert)
                        <tr class="hover:bg-slate-50/60 {{ $cert->is_expiring ? 'bg-amber-50/40' : '' }}">
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cert->type === 'certificate' ? 'bg-purple-50 text-purple-800 border border-purple-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                    {{ $cert->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900">{{ $cert->title }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $cert->issuer ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $cert->date_issued ? $cert->date_issued->format('M d, Y') : '—' }}</td>
                            <td class="px-4 py-3.5">
                                @if($cert->expiry_date)
                                    <span class="font-mono font-bold {{ $cert->is_expiring ? 'text-amber-700' : ($cert->is_expired ? 'text-red-700' : 'text-slate-800') }}">
                                        {{ $cert->expiry_date->format('M d, Y') }}
                                        @if($cert->is_expiring)
                                            <span class="block text-[10px] text-amber-600 font-sans font-semibold">(Expires in {{ (int) now()->diffInDays($cert->expiry_date) }}d)</span>
                                        @elseif($cert->is_expired)
                                            <span class="block text-[10px] text-red-600 font-sans font-bold">(EXPIRED)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">No Expiry</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($cert->file_path)
                                    <a href="{{ asset('storage/' . $cert->file_path) }}" target="_blank" class="text-red-700 font-semibold underline">View File</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.certificates.edit', $cert) }}" class="text-slate-600 hover:text-slate-900 font-semibold">Edit</a>
                                <form method="POST" action="{{ route('admin.certificates.destroy', $cert) }}" class="inline" onsubmit="return confirm('Delete this certificate?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:text-red-900 font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No certificates or trainings recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: Attendance -->
    <div x-show="tab === 'attendance'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Recent Attendance Punch Logs</h3>
            <a href="{{ route('admin.attendance.index', ['employee_id' => $employee->id]) }}" class="text-xs font-bold text-red-700 hover:text-red-800">
                Full Attendance History &rarr;
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/75 uppercase text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Date</th>
                        <th class="px-4 py-3.5">Time In</th>
                        <th class="px-4 py-3.5">Time Out</th>
                        <th class="px-4 py-3.5">Hours</th>
                        <th class="px-4 py-3.5">Late (min)</th>
                        <th class="px-4 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->attendanceRecords as $att)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-semibold">{{ $att->date->format('D, M d, Y') }}</td>
                            <td class="px-4 py-3 font-mono text-slate-800">{{ $att->time_in ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-slate-800">{{ $att->time_out ?? '—' }}</td>
                            <td class="px-4 py-3 font-bold font-mono">{{ $att->hours_worked }}</td>
                            <td class="px-4 py-3 font-mono {{ $att->late_minutes > 0 ? 'text-red-700 font-bold' : 'text-slate-400' }}">{{ $att->late_minutes }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    @if($att->status === 'present')
                                        bg-emerald-50 text-emerald-800 border border-emerald-200
                                    @elseif($att->status === 'official_business')
                                        bg-indigo-50 text-indigo-800 border border-indigo-200
                                    @elseif($att->status === 'leave')
                                        bg-blue-50 text-blue-800 border border-blue-200
                                    @elseif($att->status === 'absent')
                                        bg-red-50 text-red-800 border border-red-200
                                    @elseif($att->status === 'holiday')
                                        bg-purple-50 text-purple-800 border border-purple-200
                                    @else
                                        bg-amber-50 text-amber-800 border border-amber-200
                                    @endif">
                                    {{ $att->status === 'official_business' ? 'Official Business' : ($att->status === 'leave' ? 'On Leave' : str_replace('_', ' ', $att->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No attendance records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: Time Credits Ledger -->
    <div x-show="tab === 'credits'" class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Offset Credit Balance &amp; Ledger</h3>
                <p class="text-xs text-slate-500 mt-0.5">Available Balance: <strong class="text-slate-900 font-mono">{{ number_format($employee->time_credit_balance, 2) }} hours</strong></p>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.time-credits.process-attendance', $employee) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition shadow-2xs">
                        Compute From Attendance
                    </button>
                </form>
                <button type="button" @click="adjustModal = true" class="px-3.5 py-1.5 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition">
                    Adjust Balance
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/75 uppercase text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-4 py-3.5">Date</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Hours</th>
                        <th class="px-4 py-3.5">Reason</th>
                        <th class="px-4 py-3.5 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employee->timeCreditLedger as $entry)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-mono">{{ $entry->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 uppercase text-[10px] font-bold">
                                <span class="px-2 py-0.5 rounded-full {{ $entry->type === 'accrual' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                                    {{ $entry->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold {{ $entry->hours >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $entry->hours >= 0 ? '+' : '' }}{{ $entry->hours }} hrs
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $entry->reason ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 font-mono">{{ number_format($entry->running_balance, 2) }} hrs</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No time credit entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 5: Payslips -->
    <div x-show="tab === 'payslips'" class="space-y-4">
        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Issued Payslips</h3>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/80 uppercase text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3.5">Period</th>
                            <th class="px-4 py-3.5">Gross</th>
                            <th class="px-4 py-3.5">Deductions</th>
                            <th class="px-4 py-3.5">Net Pay</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">PDF Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($employee->payslips as $ps)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/60">
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-slate-100 whitespace-nowrap">{{ $ps->period->name }}</td>
                                <td class="px-4 py-3 font-mono whitespace-nowrap">PHP {{ number_format($ps->gross_earnings, 2) }}</td>
                                <td class="px-4 py-3 font-mono text-red-700 dark:text-red-400 whitespace-nowrap">PHP {{ number_format($ps->total_deductions, 2) }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-emerald-700 dark:text-emerald-400 whitespace-nowrap">PHP {{ number_format($ps->net_pay, 2) }}</td>
                                <td class="px-4 py-3 uppercase text-[10px] font-bold whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full {{ $ps->period->status === 'released' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                        {{ $ps->period->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 dark:border-slate-700" role="group">
                                        <a href="{{ route('admin.payroll.pdf', [$ps->period, $ps, 'action' => 'view']) }}" target="_blank" class="px-2.5 py-1 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold inline-flex items-center gap-1 border-r border-slate-200 dark:border-slate-700" title="View Payslip in browser">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View</span>
                                        </a>
                                        <a href="{{ route('admin.payroll.pdf', [$ps->period, $ps, 'action' => 'download']) }}" class="px-2.5 py-1 bg-red-50 dark:bg-red-950/60 hover:bg-red-100 dark:hover:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold inline-flex items-center gap-1" title="Download PDF">
                                            <svg class="w-3.5 h-3.5 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            <span>Download</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No payslips generated for this employee yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Loans Ledger -->
    <div x-show="tab === 'loans'" class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Employee Loans &amp; Amortizations</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Total Remaining Balance: <strong class="text-rose-700 dark:text-rose-400 font-mono">PHP {{ number_format($employee->total_loan_balance, 2) }}</strong></p>
                </div>
                <a href="{{ route('admin.loans.index', ['search' => $employee->employee_no]) }}" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                    <span>Manage in Loans Center &rarr;</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2.5">Loan Category</th>
                            <th class="px-4 py-2.5">Loan Title</th>
                            <th class="px-4 py-2.5 text-right">Total (PHP)</th>
                            <th class="px-4 py-2.5 text-right">Paid (PHP)</th>
                            <th class="px-4 py-2.5 text-right">Balance (PHP)</th>
                            <th class="px-4 py-2.5">Amort / Cutoff</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                            <th class="px-4 py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($employee->loans as $l)
                            @php
                                $b = $l->type_badge;
                                $sb = $l->status_badge;
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $b['bg'] }} {{ $b['text'] }} {{ $b['border'] }}">
                                        {{ $b['icon'] }} {{ $b['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900 dark:text-slate-100">{{ $l->loan_name }}</p>
                                    @if($l->reference_no)
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $l->reference_no }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-slate-100">
                                    {{ number_format($l->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($l->amount_paid, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-rose-700 dark:text-rose-400">
                                    {{ number_format($l->remaining_balance, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-200">₱{{ number_format($l->amortization_per_cutoff, 2) }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $l->deduction_schedule === 'both' ? 'Every Cutoff' : ($l->deduction_schedule === 'mid_month' ? 'Mid-Month' : 'End-Month') }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $sb['bg'] }} {{ $sb['text'] }} {{ $sb['border'] }}">
                                        {{ $sb['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.loans.show', $l) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                        <span>Statement</span>
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                                    No loans registered for this employee yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Adjust Time Credits Modal -->
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="adjustModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.time-credits.adjust', $employee) }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-center font-bold flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Adjust Time Credits</h3>
                                <p class="text-xs text-slate-400">Current Balance: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ number_format($employee->time_credit_balance, 2) }} hrs</strong></p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Hours (+ to add, - to deduct)</label>
                            <input type="number" step="0.25" name="hours" required placeholder="e.g. 8 or -4"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 font-mono transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Reason / Justification</label>
                            <input type="text" name="reason" required placeholder="e.g. Offset leave approved, Official travel"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="adjustModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Separate / Mark Resigned Modal -->
    <div x-show="separateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="separateModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    @method('DELETE')
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center font-bold flex-shrink-0 border border-rose-200 dark:border-rose-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Record Employee Resignation</h3>
                                <p class="text-xs text-slate-400">{{ $employee->name }} (ID: {{ $employee->employee_no ?? '—' }})</p>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 text-[11px] text-amber-800 dark:text-amber-300">
                            <strong>Data Preservation Guarantee:</strong> This employee's complete profile, historical attendance, compensations, certificates, and past payslips will be retained 100% in the archives. They will simply be excluded from future payroll runs.
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Effective Separation Date</label>
                            <input type="date" name="separation_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Separation Reason</label>
                            <select name="separation_reason" required
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition">
                                <option value="Resigned" selected>Resigned (Voluntary)</option>
                                <option value="End of Contract">End of Contract</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Retired">Retired</option>
                                <option value="Personal Reasons">Personal / Health Reasons</option>
                                <option value="Other">Other / Transferred</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Notes / Clearance Remarks (Optional)</label>
                            <textarea name="separation_notes" rows="2" placeholder="e.g. Exit clearance completed, turnover done"
                                      class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-rose-600/20 focus:border-rose-600 transition"></textarea>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="separateModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-700 text-white rounded-xl text-xs font-bold hover:bg-rose-800 transition shadow-sm">Mark Resigned / Inactive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
