@extends('layouts.admin')

@section('title', 'Payroll: ' . $period->name)
@section('page-title', 'Payroll Audit: ' . $period->name)

@section('content')
<div class="space-y-6" x-data="{
    uploadModal: false,
    itemModal: false,
    scheduleModal: false,
    selectedPayslipId: null,
    saveRow(payslipId) {
        const row = document.getElementById('row-' + payslipId);
        const inputs = row.querySelectorAll('input');
        const data = new FormData();
        inputs.forEach(input => {
            if (input.name) {
                data.append(input.name, input.value);
            }
        });
        fetch('{{ url("admin/payroll") }}/{{ $period->id }}/payslip/' + payslipId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'PUT',
                'Accept': 'application/json'
            },
            body: data
        })
        .then(res => res.json())
        .then(result => {
            document.getElementById('gross-' + payslipId).innerText = 'PHP ' + result.gross_earnings;
            document.getElementById('ded-' + payslipId).innerText = 'PHP ' + result.total_deductions;
            document.getElementById('net-' + payslipId).innerText = 'PHP ' + result.net_pay;
            const statusEl = document.getElementById('saved-' + payslipId);
            statusEl.classList.remove('hidden');
            setTimeout(() => statusEl.classList.add('hidden'), 2000);
        })
        .catch(err => alert('Save failed: ' + err));
    }
}">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('admin.payroll.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100 flex items-center gap-1 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Cutoff Periods</span>
        </a>
        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            @if($period->is_editable)
                <!-- Cutoff Type Toggle Button -->
                <form method="POST" action="{{ route('admin.payroll.toggle-cutoff-type', $period) }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs {{ $period->is_mid_month ? 'bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-800 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50' : 'bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50' }}" title="Click to toggle between Mid-Month and End-Month deduction rules">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>{{ $period->is_mid_month ? 'Mid-Month (PhilHealth & HDMF)' : 'End-Month (SSS)' }} &bull; Switch</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.payroll.sync-attendance', $period) }}" class="flex-1 sm:flex-initial">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-bold hover:bg-slate-900 dark:hover:bg-slate-600 transition flex items-center justify-center gap-1.5 shadow-xs" title="Sync attendance logs and statutory rates from employee profiles">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Sync Attendance &amp; Profiles</span>
                    </button>
                </form>
                <button type="button" @click="uploadModal = true" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload CSV</span>
                </button>
            @endif

            @if($period->status === 'under_review')
                <form method="POST" action="{{ route('admin.payroll.finalize', $period) }}" onsubmit="return confirm('Finalize payroll? This locks calculations.');" class="flex-1 sm:flex-initial">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto justify-center px-4 py-2 bg-blue-700 text-white rounded-xl text-xs font-bold hover:bg-blue-800 transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Lock &amp; Finalize</span>
                    </button>
                </form>
            @endif

            @if(in_array($period->status, ['finalized', 'released']))
                <div class="inline-flex rounded-xl shadow-xs overflow-hidden border border-emerald-700/30 dark:border-emerald-600/40" role="group">
                    <a href="{{ route('admin.payroll.export-all', [$period, 'format' => 'xlsx']) }}" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export Payroll (Excel)</span>
                    </a>
                    <a href="{{ route('admin.payroll.export-all', [$period, 'format' => 'csv']) }}" class="px-3 py-2 bg-emerald-800 hover:bg-emerald-900 text-emerald-100 text-xs font-bold transition border-l border-emerald-600/50 inline-flex items-center" title="Export Payroll as CSV">
                        <span>CSV</span>
                    </a>
                </div>
            @endif

            @if($period->status === 'finalized')
                <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                    <form method="POST" action="{{ route('admin.payroll.release', $period) }}" onsubmit="return confirm('Release payslips immediately to staff now? They will receive an email with their payslip PDF and see it in the HRIS right away.');">
                        @csrf
                        <input type="hidden" name="release_type" value="now">
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Release &amp; Email Payslips</span>
                        </button>
                    </form>
                    <button type="button" @click="scheduleModal = true" class="px-4 py-2 bg-indigo-700 text-white rounded-xl text-xs font-bold hover:bg-indigo-800 transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Schedule Date &amp; Time</span>
                    </button>
                </div>
            @endif

            @if($period->status === 'released')
                @if($period->isScheduled())
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-3.5 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-300 text-xs font-mono font-bold flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Scheduled: {{ $period->released_at->format('M d, Y @ h:i A') }} ({{ $period->released_at->diffForHumans() }})</span>
                        </span>
                        <button type="button" @click="scheduleModal = true" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Reschedule</span>
                        </button>
                        <form method="POST" action="{{ route('admin.payroll.release', $period) }}" onsubmit="return confirm('Release immediately to staff right now?');">
                            @csrf
                            <input type="hidden" name="release_type" value="now">
                            <button type="submit" class="px-3 py-1.5 bg-emerald-700 text-white rounded-xl text-xs font-bold hover:bg-emerald-800 transition flex items-center gap-1">
                                <span>Release Now</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-mono font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Visible to Staff</span>
                        </span>
                        <form method="POST" action="{{ route('admin.payroll.send-emails', $period) }}" onsubmit="return confirm('Send/Resend payslip notification emails with PDF copies to all staff in this period?');">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs" title="Send or resend email notifications with PDF payslip attachments to all employees">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Resend Payslip Emails</span>
                            </button>
                        </form>
                        <button type="button" @click="scheduleModal = true" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold transition" title="Adjust visibility date/time">
                            Adjust Time
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- 4-Step Workflow Status Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 text-center text-xs">
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold border border-slate-200/80 dark:border-slate-700 flex items-center justify-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                <span>1. Period Created</span>
            </div>
            <div class="p-2.5 rounded-xl {{ in_array($period->status, ['under_review', 'finalized', 'released']) ? 'bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold border border-slate-200/80 dark:border-slate-700' : 'bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-800' }} flex items-center justify-center gap-1.5">
                @if(in_array($period->status, ['under_review', 'finalized', 'released']))
                    <span class="w-2 h-2 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                @endif
                <span>2. Attendance Synced</span>
            </div>
            <div class="p-2.5 rounded-xl {{ in_array($period->status, ['under_review']) ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-900 dark:text-amber-300 font-bold border border-amber-200 dark:border-amber-800' : (in_array($period->status, ['finalized', 'released']) ? 'bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold border border-slate-200/80 dark:border-slate-700' : 'bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-800') }} flex items-center justify-center gap-1.5">
                @if(in_array($period->status, ['under_review']))
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                @elseif(in_array($period->status, ['finalized', 'released']))
                    <span class="w-2 h-2 rounded-full bg-emerald-600 dark:bg-emerald-400"></span>
                @endif
                <span>3. Audit &amp; Edit Freedom</span>
            </div>
            <div class="p-2.5 rounded-xl {{ $period->status === 'released' ? ($period->isScheduled() ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-emerald-700 text-white font-bold shadow-xs') : ($period->status === 'finalized' ? 'bg-blue-700 text-white font-bold shadow-xs' : 'bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-500 border border-slate-100 dark:border-slate-800') }} flex items-center justify-center gap-1.5">
                <span>4. {{ $period->status === 'released' ? ($period->isScheduled() ? 'Scheduled' : 'Published & Visible') : 'Finalize & Publish' }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 gap-2">
            <div class="flex items-center gap-2 flex-wrap">
                <div>
                    <strong>Coverage:</strong> <span class="font-mono text-slate-700 dark:text-slate-300">{{ $period->start_date->format('M d, Y') }} &ndash; {{ $period->end_date->format('M d, Y') }}</span>
                </div>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $period->is_mid_month ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' }}">
                        {{ $period->cutoff_label }}
                    </span>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px]">({{ $period->cutoff_deductions_summary }})</span>
                </div>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <div>
                    <strong>Disbursement:</strong> <span class="text-slate-700 dark:text-slate-300">{{ $period->date_of_payment ? $period->date_of_payment->format('M d, Y') : 'Pending' }}</span>
                </div>
            </div>
            <div>
                <strong>Total Headcount:</strong> <span class="font-mono text-slate-700 dark:text-slate-300 font-bold">{{ $payslips->count() }} staff</span>
                <span class="text-slate-300 dark:text-slate-600 mx-1.5">&bull;</span>
                <strong>Total Net Pay:</strong> <span class="font-extrabold text-slate-900 dark:text-white font-mono text-sm">PHP {{ number_format($payslips->sum('net_pay'), 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Review Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/75 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                    Payroll Spreadsheet &amp; Adjustment Grid &mdash;
                    <span class="{{ $period->is_mid_month ? 'text-indigo-700 dark:text-indigo-400' : 'text-emerald-700 dark:text-emerald-400' }}">{{ $period->cutoff_label }}</span>
                </h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    @if($period->is_mid_month)
                        <strong>Mid-Month (21st&ndash;5th):</strong> PhilHealth &amp; HDMF active; SSS is set to ₱0.00. Absences, late &amp; undertime minutes deducted.
                    @else
                        <strong>End-Month (6th&ndash;20th):</strong> SSS active; PhilHealth &amp; HDMF are set to ₱0.00. Absences, late &amp; undertime minutes deducted.
                    @endif
                </p>
            </div>
            <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-0.5 rounded-md shadow-2xs">PHP Currency</span>
        </div>

        <div class="overflow-x-auto max-h-[calc(100vh-250px)] min-h-[400px] overflow-y-auto relative border-t border-slate-200">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-200/95 dark:bg-slate-800 text-slate-900 dark:text-slate-100 uppercase font-black text-[11px] tracking-wider border-b-2 border-slate-300 dark:border-slate-600 sticky top-0 z-20 shadow-xs">
                    <tr>
                        <th class="px-4 py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-950 dark:text-white sticky top-0 z-20 whitespace-nowrap min-w-[220px]">Employee</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-slate-200 sticky top-0 z-20 whitespace-nowrap min-w-[145px] text-right">Basic + Allow</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-emerald-800 dark:text-emerald-400 sticky top-0 z-20 whitespace-nowrap min-w-[130px] text-right">Other (+)</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-slate-200 sticky top-0 z-20 whitespace-nowrap min-w-[130px] text-right">SSS</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-slate-200 sticky top-0 z-20 whitespace-nowrap min-w-[130px] text-right">PhilHealth</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-slate-200 sticky top-0 z-20 whitespace-nowrap min-w-[130px] text-right">HDMF</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-rose-800 dark:text-rose-400 sticky top-0 z-20 whitespace-nowrap min-w-[155px] text-right">
                            <div>Absences</div>
                            <div class="text-[9px] font-bold text-rose-600/80 normal-case tracking-normal">Valuation (Days × Rate)</div>
                        </th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-amber-900 dark:text-amber-400 sticky top-0 z-20 whitespace-nowrap min-w-[155px] text-right">
                            <div>Late / Tardy</div>
                            <div class="text-[9px] font-bold text-amber-700/80 normal-case tracking-normal">Valuation (Mins × Rate)</div>
                        </th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-amber-900 dark:text-amber-400 sticky top-0 z-20 whitespace-nowrap min-w-[155px] text-right">
                            <div>Undertime</div>
                            <div class="text-[9px] font-bold text-amber-700/80 normal-case tracking-normal">Valuation (Mins × Rate)</div>
                        </th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-blue-900 dark:text-blue-400 sticky top-0 z-20 whitespace-nowrap min-w-[125px] text-right">SSS Loan</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-emerald-900 dark:text-emerald-400 sticky top-0 z-20 whitespace-nowrap min-w-[125px] text-right">Pag-IBIG Loan</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-amber-900 dark:text-amber-400 sticky top-0 z-20 whitespace-nowrap min-w-[125px] text-right">Salary Loan</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 text-rose-800 dark:text-rose-400 sticky top-0 z-20 whitespace-nowrap min-w-[130px] text-right">Other (-)</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 font-black text-slate-950 dark:text-white sticky top-0 z-20 whitespace-nowrap min-w-[140px] text-right">Gross</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 font-black text-red-800 dark:text-red-400 sticky top-0 z-20 whitespace-nowrap min-w-[140px] text-right">Deductions</th>
                        <th class="px-3 py-3.5 bg-slate-200 dark:bg-slate-800 font-black text-emerald-800 dark:text-emerald-400 sticky top-0 z-20 whitespace-nowrap min-w-[155px] text-right">Net Pay</th>
                        <th class="px-4 py-3.5 bg-slate-200 dark:bg-slate-800 text-right sticky top-0 z-20 text-slate-900 dark:text-slate-200 whitespace-nowrap min-w-[190px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $currentDeptKey = null;
                    @endphp
                    @forelse($payslips as $ps)
                        @php
                            $empDept = $ps->employee->department;
                            $deptKey = $empDept ? $empDept->id : 'none';
                            $deptName = $empDept ? $empDept->name : 'General / Unassigned Office';
                            $deptCode = $empDept ? $empDept->code : null;
                        @endphp

                        @if($currentDeptKey !== $deptKey)
                            @php
                                $currentDeptKey = $deptKey;
                                $deptCount = $payslips->filter(fn($p) => ($p->employee->department_id ?? null) === ($empDept->id ?? null))->count();
                            @endphp
                            <tr class="bg-slate-100/90 dark:bg-slate-800/80 border-y-2 border-slate-300 dark:border-slate-700">
                                <td colspan="17" class="px-4 py-2 bg-slate-100 dark:bg-slate-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                                            <span class="font-black text-slate-900 dark:text-white uppercase tracking-wider text-xs">{{ $deptName }}</span>
                                            @if($deptCode)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $deptCode }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] font-semibold text-slate-500 font-mono">{{ $deptCount }} {{ \Illuminate\Support\Str::plural('employee', $deptCount) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition border-b border-slate-100 dark:border-slate-800/80 {{ $ps->employee->approval_role === 'section_head' ? 'bg-amber-50/30 dark:bg-amber-950/20' : '' }}" id="row-{{ $ps->id }}">
                            <form id="form-payslip-{{ $ps->id }}">
                                <td class="px-4 py-3 min-w-[220px]">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="font-bold text-slate-900 dark:text-slate-100">{{ $ps->employee->name }}</p>
                                        @if($ps->employee->approval_role === 'section_head')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-800 shadow-2xs" title="Office Section Head">
                                                ★ Section Head
                                            </span>
                                        @elseif($ps->employee->approval_role === 'hr_officer')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                HR Officer
                                            </span>
                                        @elseif($ps->employee->approval_role === 'executive_office')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-900 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                Exec Office
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span>{{ $ps->employee->position ?? 'Staff' }}</span>
                                        @if($ps->employee->salary_grade)
                                            <span class="text-slate-300 dark:text-slate-600">•</span>
                                            <span class="font-mono text-[10px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-1 py-0.2 rounded">{{ $ps->employee->salary_grade }}</span>
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-1 flex-wrap mt-1">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $ps->employee->employee_no }}</span>
                                        @if($ps->absent_days > 0)
                                            <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-[9px] font-bold" title="{{ $ps->absent_days }} days absent = PHP {{ number_format($ps->absences_deduction, 2) }} deduction">{{ $ps->absent_days }}d absent (-₱{{ number_format($ps->absences_deduction, 2) }})</span>
                                        @endif
                                        @if($ps->late_minutes > 0)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[9px] font-bold" title="{{ $ps->late_minutes }} minutes late = PHP {{ number_format($ps->late_deduction, 2) }} deduction">{{ $ps->late_minutes }}m late (-₱{{ number_format($ps->late_deduction, 2) }})</span>
                                        @endif
                                        @if($ps->undertime_minutes > 0)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[9px] font-bold" title="{{ $ps->undertime_minutes }} minutes undertime = PHP {{ number_format($ps->undertime_deduction, 2) }} deduction">{{ $ps->undertime_minutes }}m UT (-₱{{ number_format($ps->undertime_deduction, 2) }})</span>
                                        @endif
                                        @if($ps->official_business_days > 0)
                                            <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-[9px] font-bold" title="{{ $ps->official_business_days }} days official business">{{ $ps->official_business_days }}d OB</span>
                                        @endif
                                        @if($ps->leave_days > 0)
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-[9px] font-bold" title="{{ $ps->leave_days }} days leave">{{ $ps->leave_days }}d Lv</span>
                                        @endif
                                    </div>
                                    <span id="saved-{{ $ps->id }}" class="hidden text-[10px] text-emerald-700 dark:text-emerald-400 font-bold block mt-1">✓ Recalculated</span>
                                </td>
                                <td class="px-3 py-3 min-w-[145px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="salary_and_allowances" value="{{ $ps->salary_and_allowances }}"
                                               class="w-32 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ps->salary_and_allowances, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[130px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="other_earnings" value="{{ $ps->other_earnings ?? 0 }}" title="Other Salary Earnings / Additions"
                                               class="w-28 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->other_earnings ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[130px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="sss_deduction" value="{{ $ps->sss_deduction }}" title="From employee profile"
                                               class="w-28 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->sss_deduction, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[130px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="ph_deduction" value="{{ $ps->ph_deduction }}" title="From employee profile"
                                               class="w-28 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->ph_deduction, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[130px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="hdmf_deduction" value="{{ $ps->hdmf_deduction }}" title="From employee profile"
                                               class="w-28 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->hdmf_deduction, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[155px] text-right">
                                    @if($period->is_editable)
                                        <div class="flex flex-col items-end">
                                            <input type="number" step="0.01" name="absences_deduction" value="{{ $ps->absences_deduction }}" title="{{ $ps->absent_days }} days absent (Daily rate: PHP {{ number_format($ps->per_day_rate, 2) }})"
                                                   class="w-32 px-2.5 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-1 text-right whitespace-nowrap leading-tight">
                                                <span class="font-bold {{ $ps->absent_days > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->absent_days }}d</span>
                                                <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_day_rate, 2) }}/d
                                            </div>
                                        </div>
                                    @else
                                        <span class="font-mono text-right block font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ps->absences_deduction, 2) }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono block text-right mt-0.5 whitespace-nowrap">
                                            <span class="font-bold {{ $ps->absent_days > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->absent_days }}d</span>
                                            <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_day_rate, 2) }}/d
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[155px] text-right">
                                    @if($period->is_editable)
                                        <div class="flex flex-col items-end">
                                            <input type="number" step="0.01" name="late_deduction" value="{{ $ps->late_deduction }}" title="{{ $ps->late_minutes }} mins late (Minute rate: PHP {{ number_format($ps->per_minute_rate, 4) }})"
                                                   class="w-32 px-2.5 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-1 text-right whitespace-nowrap leading-tight">
                                                <span class="font-bold {{ $ps->late_minutes > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->late_minutes }}m</span>
                                                <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_minute_rate, 2) }}/m
                                            </div>
                                        </div>
                                    @else
                                        <span class="font-mono text-right block font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ps->late_deduction, 2) }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono block text-right mt-0.5 whitespace-nowrap">
                                            <span class="font-bold {{ $ps->late_minutes > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->late_minutes }}m</span>
                                            <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_minute_rate, 2) }}/m
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[155px] text-right">
                                    @if($period->is_editable)
                                        <div class="flex flex-col items-end">
                                            <input type="number" step="0.01" name="undertime_deduction" value="{{ $ps->undertime_deduction }}" title="{{ $ps->undertime_minutes }} mins undertime (Minute rate: PHP {{ number_format($ps->per_minute_rate, 4) }})"
                                                   class="w-32 px-2.5 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-1 text-right whitespace-nowrap leading-tight">
                                                <span class="font-bold {{ $ps->undertime_minutes > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->undertime_minutes }}m</span>
                                                <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_minute_rate, 2) }}/m
                                            </div>
                                        </div>
                                    @else
                                        <span class="font-mono text-right block font-semibold text-slate-800 dark:text-slate-200">{{ number_format($ps->undertime_deduction, 2) }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono block text-right mt-0.5 whitespace-nowrap">
                                            <span class="font-bold {{ $ps->undertime_minutes > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $ps->undertime_minutes }}m</span>
                                            <span class="text-slate-400 dark:text-slate-500">&times;</span> ₱{{ number_format($ps->per_minute_rate, 2) }}/m
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[125px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="sss_loan_deduction" value="{{ $ps->sss_loan_deduction ?? 0 }}" title="SSS Loan Deduction"
                                               class="w-24 px-2 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-blue-600/30 focus:border-blue-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->sss_loan_deduction ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[125px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="pagibig_loan_deduction" value="{{ $ps->pagibig_loan_deduction ?? 0 }}" title="Pag-IBIG Loan Deduction"
                                               class="w-24 px-2 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-emerald-600/30 focus:border-emerald-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->pagibig_loan_deduction ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[125px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="salary_loan_deduction" value="{{ $ps->salary_loan_deduction ?? 0 }}" title="Salary Loan Deduction"
                                               class="w-24 px-2 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-amber-600/30 focus:border-amber-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->salary_loan_deduction ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[130px] text-right">
                                    @if($period->is_editable)
                                        <input type="number" step="0.01" name="other_deductions" value="{{ $ps->other_deductions ?? 0 }}" title="HR Input: Other Salary Deductions"
                                               class="w-28 px-3 py-1.5 text-xs text-right border border-slate-300 dark:border-slate-700 rounded-lg font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    @else
                                        <span class="font-mono text-right block text-slate-800 dark:text-slate-200">{{ number_format($ps->other_deductions ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 min-w-[140px] text-right font-bold font-mono text-slate-900 dark:text-slate-100 whitespace-nowrap" id="gross-{{ $ps->id }}">
                                    PHP {{ number_format($ps->gross_earnings, 2) }}
                                </td>
                                <td class="px-3 py-3 min-w-[140px] text-right font-bold font-mono text-red-700 dark:text-red-400 whitespace-nowrap" id="ded-{{ $ps->id }}">
                                    PHP {{ number_format($ps->total_deductions, 2) }}
                                </td>
                                <td class="px-3 py-3 min-w-[155px] text-right font-extrabold font-mono text-emerald-700 dark:text-emerald-400 text-sm whitespace-nowrap" id="net-{{ $ps->id }}">
                                    PHP {{ number_format($ps->net_pay, 2) }}
                                </td>
                                <td class="px-4 py-3 min-w-[190px] text-right space-x-1.5 whitespace-nowrap">
                                    @if($period->is_editable)
                                        <button type="button" @click="saveRow({{ $ps->id }})" class="px-2.5 py-1 bg-red-700 hover:bg-red-800 text-white rounded-lg text-[10px] font-bold transition shadow-2xs">
                                            Save
                                        </button>
                                        <button type="button" @click="selectedPayslipId = {{ $ps->id }}; itemModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-[10px] font-semibold transition" title="Add bonus or custom deduction">
                                            + Item
                                        </button>
                                    @endif
                                    <div class="inline-flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 dark:border-slate-700" role="group">
                                        <a href="{{ route('admin.payroll.pdf', [$period, $ps, 'action' => 'view']) }}" target="_blank" class="px-2.5 py-1 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-r border-slate-200 dark:border-slate-700 text-[10px] font-semibold inline-flex items-center gap-1" title="View Payslip in browser">
                                            <svg class="w-3 h-3 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.payroll.pdf', [$period, $ps, 'action' => 'download']) }}" class="px-2.5 py-1 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 text-[10px] font-bold inline-flex items-center gap-1" title="Download PDF file">
                                            <svg class="w-3 h-3 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            Download
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-6 py-14 text-center">
                                <p class="text-xs text-slate-500 font-medium">No draft calculations yet.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Click "Sync Attendance &amp; Profiles" or "Upload CSV" above to populate all staff entries.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attendance Upload Modal -->
    <div x-show="uploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="uploadModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.payroll.upload-attendance', $period) }}" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/50 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Upload Attendance Data</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Match employee logs &amp; auto-compute deductions</p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Upload the CSV or Excel file exported from your attendance device. The parser will detect Employee No., Name, Tardiness, and Absences, instantly generating drafts.
                        </p>

                        <div class="p-4 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800/40">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Select Attendance File (.xls, .xlsx, .csv, .tsv, .txt)</label>
                            <input type="file" name="file" required accept=".xlsx,.xls,.csv,.tsv,.txt,.html,.htm,.xml"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-red-50 dark:file:bg-red-950/50 file:text-red-700 dark:file:text-red-300 font-semibold">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="uploadModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">
                            Process Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Custom Item Modal -->
    <div x-show="itemModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="itemModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" :action="'{{ url('admin/payroll/' . $period->id . '/payslip') }}/' + selectedPayslipId + '/items'" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-center font-bold flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Add Line Item</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Append special bonus or deduction</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Line Category</label>
                            <select name="type" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <option value="earning">Additional Earning (Bonus, Overtime, Honorarium)</option>
                                <option value="deduction">Additional Deduction (Loan, Cash Advance, Uniform)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Label Description</label>
                            <input type="text" name="label" required placeholder="e.g. Special Project Bonus, Emergency Loan"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Amount (PHP)</label>
                            <input type="number" step="0.01" name="amount" required placeholder="0.00"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="itemModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Schedule Release Date & Time Modal -->
    <div x-show="scheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="scheduleModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.payroll.release', $period) }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <input type="hidden" name="release_type" value="scheduled">
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Set Staff Visibility Schedule</h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Specify when staff can view their payslips</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Release Date &amp; Time *
                            </label>
                            <input type="datetime-local" name="released_at" required
                                   value="{{ $period->released_at ? $period->released_at->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\T17:00') }}"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-900 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">
                                Choose the exact date and hour when payslip amounts and PDF downloads will unlock for employees.
                            </p>
                        </div>

                        <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-xl space-y-1.5">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-900 dark:text-indigo-300">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Security &amp; Gating Notice</span>
                            </div>
                            <p class="text-[11px] text-indigo-800 dark:text-indigo-300/80 leading-relaxed">
                                Before this scheduled time arrives, staff will only see an upcoming notification notice. Take-home pay, earnings, deductions, and PDF downloads are strictly locked.
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="scheduleModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-700 text-white rounded-xl text-xs font-bold hover:bg-indigo-800 transition shadow-sm">Save &amp; Activate Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
