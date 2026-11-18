@extends('layouts.admin')

@section('title', 'Attendance Management')
@section('page-title', 'Attendance Records')

@section('content')
<div class="space-y-6" x-data="{
    importModal: false,
    manualModal: false,
    editModal: false,
    editForm: {
        id: null,
        employee_id: null,
        employee_name: '',
        employee_no: '',
        date: '',
        formatted_date: '',
        status: 'leave',
        time_in: '',
        time_out: '',
        hours_worked: 0,
        late_minutes: 0,
        undertime_minutes: 0,
        overtime_minutes: 0,
        overtime_status: 'none',
        actionUrl: ''
    },
    setPreset(type) {
        if (type === 'leave') {
            this.editForm.status = 'leave';
            this.editForm.time_in = '';
            this.editForm.time_out = '';
            this.editForm.hours_worked = 0;
            this.editForm.late_minutes = 0;
            this.editForm.undertime_minutes = 0;
            this.editForm.overtime_minutes = 0;
            this.editForm.overtime_status = 'none';
        } else if (type === 'regular') {
            this.editForm.status = 'present';
            this.editForm.time_in = '08:00';
            this.editForm.time_out = '17:00';
            this.editForm.hours_worked = 8;
            this.editForm.late_minutes = 0;
            this.editForm.undertime_minutes = 0;
            this.editForm.overtime_minutes = 0;
            this.editForm.overtime_status = 'none';
        } else if (type === 'ob') {
            this.editForm.status = 'official_business';
            this.editForm.time_in = '08:00';
            this.editForm.time_out = '17:00';
            this.editForm.hours_worked = 8;
            this.editForm.late_minutes = 0;
            this.editForm.undertime_minutes = 0;
            this.editForm.overtime_minutes = 0;
            this.editForm.overtime_status = 'none';
        } else if (type === 'absent') {
            this.editForm.status = 'absent';
            this.editForm.time_in = '';
            this.editForm.time_out = '';
            this.editForm.hours_worked = 0;
            this.editForm.late_minutes = 0;
            this.editForm.undertime_minutes = 0;
            this.editForm.overtime_minutes = 0;
            this.editForm.overtime_status = 'none';
        }
    },
    calcTimes() {
        if (this.editForm.time_in && this.editForm.time_out) {
            const inParts = this.editForm.time_in.split(':');
            const outParts = this.editForm.time_out.split(':');
            if (inParts.length === 2 && outParts.length === 2) {
                const inMins = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
                let outMins = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
                if (outMins < inMins) outMins += 24 * 60;
                this.editForm.hours_worked = parseFloat(((outMins - inMins) / 60).toFixed(2));
                
                const expectedIn = 8 * 60; // 08:00
                if (inMins > expectedIn) {
                    this.editForm.late_minutes = inMins - expectedIn;
                } else {
                    this.editForm.late_minutes = 0;
                }
                
                if (['present', 'late', 'absent'].includes(this.editForm.status)) {
                    this.editForm.status = this.editForm.late_minutes >= 5 ? 'late' : 'present';
                }
            }
        }
    },
    openEdit(rec) {
        this.editForm = {
            id: rec.id || null,
            employee_id: rec.employee_id,
            employee_name: rec.employee_name,
            employee_no: rec.employee_no,
            date: rec.date,
            formatted_date: rec.formatted_date,
            status: rec.status === 'upcoming' ? 'leave' : rec.status,
            time_in: rec.time_in || '',
            time_out: rec.time_out || '',
            hours_worked: rec.hours_worked || 0,
            late_minutes: rec.late_minutes || 0,
            undertime_minutes: rec.undertime_minutes || 0,
            overtime_minutes: rec.overtime_minutes || 0,
            overtime_status: rec.overtime_status || 'none',
            actionUrl: rec.id ? ('{{ url('admin/attendance') }}/' + rec.id) : '{{ route('admin.attendance.store') }}'
        };
        this.editModal = true;
    }
}">

    <!-- Actions & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1 w-full">
            <select name="employee_id" class="w-full sm:w-auto px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <option value="">-- All Employees (Overview) --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ $emp->employee_no ?? 'No ID' }})
                    </option>
                @endforeach
            </select>

            <input type="month" name="month" value="{{ $month }}"
                   class="w-full sm:w-auto px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-semibold transition shadow-xs text-center">
                    Load Records
                </button>

                @if(request('employee_id') || request('month'))
                    <a href="{{ route('admin.attendance.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full md:w-auto">
            <div class="relative inline-flex rounded-xl shadow-xs" x-data="{ syncMenu: false }">
                <form method="POST" action="{{ route('admin.attendance.sync-rfid') }}" class="inline">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <button type="submit" class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-l-xl text-xs font-bold transition flex items-center gap-1.5" title="Pull latest punches for {{ \Carbon\Carbon::parse($month)->format('F Y') }}">
                        <svg class="w-3.5 h-3.5 animate-spin-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="whitespace-nowrap">Sync RFID ({{ \Carbon\Carbon::parse($month)->format('M Y') }})</span>
                    </button>
                </form>
                <button type="button" @click="syncMenu = !syncMenu" class="px-2 py-2 bg-emerald-800 hover:bg-emerald-900 text-white rounded-r-xl border-l border-emerald-600 transition" title="More Sync Options">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="syncMenu" @click.away="syncMenu = false" x-cloak class="absolute right-0 top-full z-50 mt-1 w-56 rounded-xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 p-1">
                    <form method="POST" action="{{ route('admin.attendance.sync-rfid') }}">
                        @csrf
                        <input type="hidden" name="all" value="1">
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                        <button type="submit" onclick="return confirm('Synchronize all attendance logs across all history from the RFID database? This may take several seconds.');" class="w-full text-left px-3 py-2 text-xs rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Sync All Past Months</span>
                        </button>
                    </form>
                </div>
            </div>
            <button type="button" @click="manualModal = true" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-transparent dark:border-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="whitespace-nowrap">Manual Entry</span>
            </button>
            <button type="button" @click="importModal = true" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span class="whitespace-nowrap">Upload Logs</span>
            </button>
        </div>
    </div>

    @if(($pendingOvertimeCount ?? 0) > 0)
        <!-- Pending Overtime Verification Banner -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20 border border-amber-200/90 dark:border-amber-800/80 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-800 flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-700 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-900 dark:text-amber-200">
                        {{ $pendingOvertimeCount }} Pending Excess Overtime {{ Str::plural('Record', $pendingOvertimeCount) }} Awaiting Verification
                    </p>
                    <p class="text-[11px] text-amber-700 dark:text-amber-300 mt-0.5">
                        Excess time (e.g. employee timeout at 5:30 PM = 30 mins) is recorded and requires HR/Admin approval before it becomes valid time credits.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <form method="POST" action="{{ route('admin.attendance.approve-all-overtime') }}" onsubmit="return confirm('Approve all pending overtime records in the system as valid time credits?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Approve All Pending</span>
                    </button>
                </form>
                <a href="{{ route('admin.time-credits.index') }}" class="px-3 py-1.5 bg-white dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200 hover:bg-amber-100/50 dark:hover:bg-amber-900/40 rounded-xl text-xs font-semibold transition">
                    Time Credits Queue
                </a>
            </div>
        </div>
    @endif

    <!-- Attendance Details & Stats Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                {{ $selectedEmployee ? 'Employee & Period' : 'Selected Period' }}
            </span>
            @if($selectedEmployee)
                <p class="font-bold text-sm text-slate-900 dark:text-slate-100 mt-1 truncate">{{ $selectedEmployee->name }}</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">{{ date('F Y', strtotime($month)) }} &bull; <span class="font-mono">{{ $selectedEmployee->employee_no ?? 'No ID' }}</span></p>
            @else
                <p class="font-bold text-sm text-slate-900 dark:text-slate-100 mt-1">{{ date('F Y', strtotime($month)) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">All Personnel ({{ $totalRecords }} logs)</p>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Present</span>
            <p class="font-black text-2xl text-emerald-600 dark:text-emerald-400 mt-1 font-mono">{{ $presents }} <span class="text-xs font-normal text-slate-400">entries</span></p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Official Biz</span>
            <p class="font-black text-2xl text-indigo-600 dark:text-indigo-400 mt-1 font-mono">{{ $officialBusinesses }} <span class="text-xs font-normal text-slate-400">OB</span></p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">On Leave</span>
            <p class="font-black text-2xl text-blue-600 dark:text-blue-400 mt-1 font-mono">{{ $leaves }} <span class="text-xs font-normal text-slate-400">excused</span></p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tardiness</span>
            <p class="font-black text-2xl text-amber-600 dark:text-amber-400 mt-1 font-mono">{{ $lates }} <span class="text-xs font-normal text-slate-400">({{ $totalLateMins }}m)</span></p>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Absences</span>
            <p class="font-black text-2xl text-rose-600 dark:text-rose-400 mt-1 font-mono">{{ $absents }} <span class="text-xs font-normal text-slate-400">unexcused</span></p>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-2.5 py-2.5">Date</th>
                        @if(!$selectedEmployee)
                            <th class="px-2.5 py-2.5">Employee</th>
                        @endif
                        <th class="px-2.5 py-2.5">In</th>
                        <th class="px-2.5 py-2.5">Out</th>
                        <th class="px-2.5 py-2.5">Hours</th>
                        <th class="px-2.5 py-2.5">Late</th>
                        <th class="px-2.5 py-2.5">Under</th>
                        <th class="px-2.5 py-2.5">OT / Credits</th>
                        <th class="px-2.5 py-2.5">Status</th>
                        <th class="px-2.5 py-2.5">Src</th>
                        <th class="px-2.5 py-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition">
                            <td class="px-2.5 py-2 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                <div>{{ $rec->date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-normal">{{ $rec->date->format('D') }}</div>
                            </td>
                            @if(!$selectedEmployee)
                                <td class="px-2.5 py-2">
                                    <a href="{{ route('admin.attendance.index', ['employee_id' => $rec->employee_id, 'month' => $month]) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-red-700 dark:hover:text-red-400 transition block max-w-[140px] truncate" title="{{ $rec->employee?->name ?? 'Unknown' }}">
                                        {{ $rec->employee?->name ?? 'Unknown' }}
                                    </a>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $rec->employee?->employee_no }}</p>
                                </td>
                            @endif
                            <td class="px-2.5 py-2 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ $rec->time_in ? date('g:i A', strtotime($rec->time_in)) : '—' }}
                            </td>
                            <td class="px-2.5 py-2 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ $rec->time_out ? date('g:i A', strtotime($rec->time_out)) : '—' }}
                            </td>
                            <td class="px-2.5 py-2 font-bold font-mono text-slate-800 dark:text-slate-200 whitespace-nowrap">
                                {{ number_format($rec->hours_worked, 1) }}h
                            </td>
                            <td class="px-2.5 py-2 font-mono whitespace-nowrap {{ $rec->late_minutes > 0 ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                                @if($rec->late_minutes > 0)
                                    <span>{{ $rec->late_minutes }}m</span>
                                    @if($rec->late_minutes < 5)
                                        <span class="inline-block px-1 rounded text-[9px] font-sans font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800">&lt;5m</span>
                                    @else
                                        <span class="inline-block px-1 rounded text-[9px] font-sans font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/60">&ge;5m</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="px-2.5 py-2 font-mono whitespace-nowrap {{ $rec->undertime_minutes > 0 ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ $rec->undertime_minutes > 0 ? $rec->undertime_minutes . 'm' : '—' }}
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                @if($rec->id && $rec->overtime_minutes > 0)
                                    <div class="inline-flex items-center gap-1">
                                        <span class="font-bold text-indigo-700 dark:text-indigo-400 font-mono text-xs">+{{ $rec->overtime_minutes }}m</span>
                                        @if($rec->overtime_status === 'pending')
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Pending HR/Admin Verification">
                                                Pending
                                            </span>
                                            <div class="inline-flex items-center gap-0.5">
                                                <form method="POST" action="{{ route('admin.attendance.approve-overtime', $rec) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Approve as valid time credit" class="p-0.5 text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 rounded transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.attendance.reject-overtime', $rec) }}" class="inline" onsubmit="return confirm('Reject this excess time? It will NOT be credited.');">
                                                    @csrf
                                                    <button type="submit" title="Reject" class="p-0.5 text-rose-500 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($rec->overtime_status === 'approved')
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 inline-flex items-center gap-0.5" title="Valid time credit approved by HR/Admin">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Credited
                                            </span>
                                        @elseif($rec->overtime_status === 'rejected')
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800" title="Rejected by HR/Admin - not credited">
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="px-2.5 py-2 whitespace-nowrap">
                                <button type="button"
                                        @click="openEdit({
                                            id: {{ $rec->id ? $rec->id : 'null' }},
                                            employee_id: {{ $rec->employee_id ?? ($selectedEmployee?->id ?? 'null') }},
                                            employee_name: '{{ addslashes($rec->employee?->name ?? ($selectedEmployee?->name ?? 'Unknown')) }}',
                                            employee_no: '{{ addslashes($rec->employee?->employee_no ?? ($selectedEmployee?->employee_no ?? '')) }}',
                                            date: '{{ $rec->date->format('Y-m-d') }}',
                                            formatted_date: '{{ $rec->date->format('D, M d, Y') }}',
                                            status: '{{ $rec->status }}',
                                            time_in: '{{ $rec->time_in ?? '' }}',
                                            time_out: '{{ $rec->time_out ?? '' }}',
                                            hours_worked: {{ $rec->hours_worked ?? 0 }},
                                            late_minutes: {{ $rec->late_minutes ?? 0 }},
                                            undertime_minutes: {{ $rec->undertime_minutes ?? 0 }},
                                            overtime_minutes: {{ $rec->overtime_minutes ?? 0 }},
                                            overtime_status: '{{ $rec->overtime_status ?? 'none' }}'
                                        })"
                                        class="group inline-flex items-center gap-1 focus:outline-hidden"
                                        title="{{ $rec->id ? 'Click to edit status or hours' : 'Click to adjust punch or set leave' }}">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-tight inline-flex items-center gap-0.5 transition group-hover:ring-1 group-hover:ring-slate-300
                                        @if($rec->status === 'present')
                                            bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                        @elseif($rec->status === 'official_business')
                                            bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800
                                        @elseif($rec->status === 'leave')
                                            bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                        @elseif($rec->status === 'absent')
                                            bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800
                                        @elseif($rec->status === 'holiday')
                                            bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800
                                        @elseif($rec->status === 'weekend')
                                            bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700
                                        @elseif($rec->status === 'upcoming')
                                            bg-slate-50 dark:bg-slate-900 text-slate-400 border border-dashed border-slate-300 dark:border-slate-700
                                        @else
                                            bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                        @endif">
                                        <span>
                                            @if($rec->status === 'official_business')
                                                OB
                                            @elseif($rec->status === 'leave')
                                                Leave
                                            @elseif($rec->status === 'half_day')
                                                Half-Day
                                            @elseif($rec->status === 'absent' && !$rec->id)
                                                No Log / Absent
                                            @else
                                                {{ ucfirst($rec->status) }}
                                            @endif
                                        </span>
                                        <svg class="w-2.5 h-2.5 opacity-50 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </span>
                                </button>
                            </td>
                            <td class="px-2.5 py-2 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-mono whitespace-nowrap">{{ substr($rec->source, 0, 3) }}</td>
                            <td class="px-2.5 py-2 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <button type="button"
                                            @click="openEdit({
                                                id: {{ $rec->id ? $rec->id : 'null' }},
                                                employee_id: {{ $rec->employee_id ?? ($selectedEmployee?->id ?? 'null') }},
                                                employee_name: '{{ addslashes($rec->employee?->name ?? ($selectedEmployee?->name ?? 'Unknown')) }}',
                                                employee_no: '{{ addslashes($rec->employee?->employee_no ?? ($selectedEmployee?->employee_no ?? '')) }}',
                                                date: '{{ $rec->date->format('Y-m-d') }}',
                                                formatted_date: '{{ $rec->date->format('D, M d, Y') }}',
                                                status: '{{ $rec->status }}',
                                                time_in: '{{ $rec->time_in ?? '' }}',
                                                time_out: '{{ $rec->time_out ?? '' }}',
                                                hours_worked: {{ $rec->hours_worked ?? 0 }},
                                                late_minutes: {{ $rec->late_minutes ?? 0 }},
                                                undertime_minutes: {{ $rec->undertime_minutes ?? 0 }},
                                                overtime_minutes: {{ $rec->overtime_minutes ?? 0 }},
                                                overtime_status: '{{ $rec->overtime_status ?? 'none' }}'
                                            })"
                                            class="p-1 text-slate-500 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-md transition"
                                            title="{{ $rec->id ? 'Edit record' : 'Adjust / Record attendance' }}">
                                        @if($rec->id)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 dark:text-red-400 hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Adjust / Set Leave
                                            </span>
                                        @endif
                                    </button>
                                    @if($rec->id)
                                        <form method="POST" action="{{ route('admin.attendance.destroy', $rec) }}" class="inline" onsubmit="return confirm('Delete this attendance record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-400 dark:text-slate-500 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-md transition" title="Delete record">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $selectedEmployee ? '10' : '11' }}" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">
                                <p class="font-semibold text-slate-600 dark:text-slate-300 mb-1">No attendance records found for {{ date('F Y', strtotime($month)) }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Try selecting a different month, choosing another employee, or uploading an attendance spreadsheet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    <!-- CSV/Excel Upload Modal -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="importModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.attendance.import') }}" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/60 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Upload Attendance Sheet</h3>
                                <p class="text-xs text-slate-400">Batch parse bio/time logs</p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Upload CSV/Excel exported from your attendance hardware or portal. Supports both single-employee and all-employee consolidated attendance summary matrix sheets.
                        </p>

                        <div class="p-4 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800/50">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Attendance File (.xls, .xlsx, .csv, .tsv, .txt)</label>
                            <input type="file" name="file" required accept=".xlsx,.xls,.csv,.tsv,.txt,.html,.htm,.xml"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-red-50 file:text-red-700 dark:file:bg-red-950/60 dark:file:text-red-300 font-semibold">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="importModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">Import Logs</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manual Entry Modal -->
    <div x-show="manualModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="manualModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.attendance.store') }}" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-center font-bold flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Manual Punch Entry</h3>
                                <p class="text-xs text-slate-400">Record a single attendance log</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Employee *</label>
                            <select name="employee_id" required class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Date *</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Time In</label>
                                <input type="time" name="time_in" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Time Out</label>
                                <input type="time" name="time_out" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Late (Minutes)</label>
                                <input type="number" name="late_minutes" value="0" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Undertime (Minutes)</label>
                                <input type="number" name="undertime_minutes" value="0" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Overtime (Minutes)</label>
                                <input type="number" min="0" name="overtime_minutes" placeholder="Auto" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <span class="text-[9px] text-slate-400 block mt-1 leading-tight">Blank for auto-calc</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1.5">Status *</label>
                                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                    <option value="present">Present</option>
                                    <option value="official_business">Official Business (OB)</option>
                                    <option value="leave">Leave</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="absent">Absent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="manualModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">Save Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Status Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="editModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-md w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" :action="editForm.actionUrl" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <template x-if="editForm.id">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="employee_id" :value="editForm.employee_id">
                    <input type="hidden" name="date" :value="editForm.date">

                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/60">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="editForm.id ? 'Edit Attendance Record' : 'Record / Adjust Attendance'"></h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    <span x-text="editForm.employee_name"></span>
                                    <span class="text-slate-400 font-mono text-[10px]" x-show="editForm.employee_no" x-text="' (' + editForm.employee_no + ')'"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Date indicator -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200/70 dark:border-slate-700 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Attendance Date:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="editForm.formatted_date || editForm.date"></span>
                        </div>

                        <!-- Quick Presets -->
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quick Actions / Presets</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                                <button type="button" @click="setPreset('leave')"
                                        class="px-2.5 py-1.5 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-[11px] font-bold hover:bg-blue-100 transition text-center shadow-2xs">
                                    ★ Set On Leave
                                </button>
                                <button type="button" @click="setPreset('regular')"
                                        class="px-2.5 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/80 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold hover:bg-emerald-100 transition text-center shadow-2xs">
                                    ✓ 8am - 5pm Shift
                                </button>
                                <button type="button" @click="setPreset('ob')"
                                        class="px-2.5 py-1.5 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/80 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 text-[11px] font-bold hover:bg-indigo-100 transition text-center shadow-2xs">
                                    ✈ Official Biz
                                </button>
                                <button type="button" @click="setPreset('absent')"
                                        class="px-2.5 py-1.5 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50/80 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-[11px] font-bold hover:bg-rose-100 transition text-center shadow-2xs">
                                    ✕ Set Absent
                                </button>
                            </div>
                        </div>

                        <!-- Status Selection -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Attendance Status *</label>
                            <select name="status" x-model="editForm.status" required
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <option value="official_business">Official Business (OB / Field Assignment)</option>
                                <option value="leave">On Leave (Approved / Official)</option>
                                <option value="present">Present (Normal)</option>
                                <option value="late">Late Arrival</option>
                                <option value="half_day">Half Day</option>
                                <option value="holiday">Holiday</option>
                                <option value="absent">Absent (Unexcused / No Log)</option>
                                <option value="weekend">Weekend / Rest Day</option>
                            </select>
                        </div>

                        <!-- Contextual notice for Official Business -->
                        <div x-show="editForm.status === 'official_business'" class="p-3 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200/80 dark:border-indigo-800 rounded-xl text-xs text-indigo-800 dark:text-indigo-200 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>Official Business (OB) Selected</span>
                            </div>
                            <p class="text-[11px] text-indigo-700 dark:text-indigo-300 leading-relaxed">
                                Authorized assignment or mission outside the office. Automatically counted as a working day (defaults to 8.0 hrs) with 0 late or undertime deductions.
                            </p>
                        </div>

                        <!-- Contextual notice for Leave -->
                        <div x-show="editForm.status === 'leave'" class="p-3 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-200 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Excused Leave Selected</span>
                            </div>
                            <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-relaxed">
                                Marking this entry as <strong>On Leave</strong> treats it as an authorized leave instead of an absent day. Tardiness and undertime penalties will be cleared.
                            </p>
                        </div>

                        <!-- Contextual notice for Absent -->
                        <div x-show="editForm.status === 'absent'" class="p-3 bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800 rounded-xl text-xs text-rose-800 dark:text-rose-200 space-y-1">
                            <div class="flex items-center gap-1.5 font-bold">
                                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Unexcused Absence</span>
                            </div>
                            <p class="text-[11px] text-rose-700 dark:text-rose-300 leading-relaxed">
                                Hours worked and time stamps will be cleared for unexcused absence.
                            </p>
                        </div>

                        <!-- Time inputs -->
                        <div class="grid grid-cols-2 gap-3" x-show="editForm.status !== 'absent' && editForm.status !== 'leave'">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Time In</label>
                                <input type="time" name="time_in" x-model="editForm.time_in" @input="calcTimes()" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Time Out</label>
                                <input type="time" name="time_out" x-model="editForm.time_out" @input="calcTimes()" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                        </div>

                        <!-- Hours and penalties -->
                        <div class="grid grid-cols-3 gap-3" x-show="editForm.status !== 'absent'">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Hours Worked</label>
                                <input type="number" step="any" min="0" max="24" name="hours_worked" x-model="editForm.hours_worked" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Late (min)</label>
                                <input type="number" min="0" name="late_minutes" x-model="editForm.late_minutes" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Undertime (min)</label>
                                <input type="number" min="0" name="undertime_minutes" x-model="editForm.undertime_minutes" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                            </div>
                        </div>

                        <!-- Real-time Late Policy Explanation Banner -->
                        <div x-show="editForm.status !== 'absent'" class="rounded-xl p-3 text-xs border transition"
                             :class="{
                                 'bg-emerald-50/90 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200': editForm.late_minutes > 0 && editForm.late_minutes < 5,
                                 'bg-amber-50/90 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200': editForm.late_minutes >= 5,
                                 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300': !editForm.late_minutes || editForm.late_minutes == 0
                             }">
                            <div>
                                <template x-if="editForm.late_minutes > 0 && editForm.late_minutes < 5">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div>
                                            <p class="font-bold text-emerald-800 dark:text-emerald-300">
                                                Grace Period Active: Not Counted as Late (<span x-text="editForm.late_minutes"></span> min)
                                            </p>
                                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5 leading-relaxed">
                                                Because tardiness is <strong>less than 5 minutes</strong>, attendance is classified as <strong>Present</strong> (clean record), but the <strong x-text="editForm.late_minutes + ' minute(s)'"></strong> will be automatically deducted from salary in payroll.
                                            </p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="editForm.late_minutes >= 5">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <div>
                                            <p class="font-bold text-amber-800 dark:text-amber-300">
                                                Tardiness Infraction (<span x-text="editForm.late_minutes"></span> mins late)
                                            </p>
                                            <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5 leading-relaxed">
                                                Tardiness is <strong>5 minutes or more</strong>. Counted as an official <strong>Late</strong> infraction and full <span x-text="editForm.late_minutes"></span> mins are deducted in payroll.
                                            </p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!editForm.late_minutes || editForm.late_minutes == 0">
                                    <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-[11px]"><strong>On Time / No Late:</strong> 0 minutes late. No salary deduction or tardiness record.</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Overtime and Verification Status -->
                        <div class="grid grid-cols-2 gap-3" x-show="editForm.status !== 'absent'">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Overtime (Minutes)</label>
                                <input type="number" min="0" name="overtime_minutes" x-model="editForm.overtime_minutes" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-mono focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 block mt-1 leading-tight">e.g. Out at 5:30 PM = 30 mins</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5">Overtime Status *</label>
                                <select name="overtime_status" x-model="editForm.overtime_status" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                                    <option value="none">None (0 min)</option>
                                    <option value="pending">Pending HR/Admin Verification</option>
                                    <option value="approved">Approved (Valid Time Credit)</option>
                                    <option value="rejected">Rejected (Not Credited)</option>
                                </select>
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 block mt-1 leading-tight">Only Approved credits count towards balance</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="editModal = false" class="w-full sm:w-auto px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
