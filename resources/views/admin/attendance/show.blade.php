@extends('layouts.admin')

@section('title', $employee->name . ' - Attendance History')
@section('page-title', 'Employee Attendance History')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.attendance.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Attendance Roster</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.employees.profile', $employee) }}"
               class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>View Full Profile</span>
            </a>
        </div>
    </div>

    <!-- Employee Profile Summary -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-800 flex items-center justify-center font-black text-lg">
                {{ substr($employee->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-base font-black text-slate-900">{{ $employee->name }}</h2>
                <p class="text-xs text-slate-500">
                    {{ $employee->position ?? 'Staff' }} &bull; {{ $employee->department?->name ?? 'Unassigned' }} &bull;
                    <span class="font-mono font-bold">{{ $employee->employee_no ?? 'ID-' . $employee->id }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Recorded Biometrics &amp; Attendance Logs</h3>
            <span class="text-xs text-slate-400 font-medium">{{ $records->total() }} total records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/75 uppercase text-slate-400 font-bold text-[11px] tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-5 py-3.5">Date</th>
                        <th class="px-5 py-3.5">Time In</th>
                        <th class="px-5 py-3.5">Time Out</th>
                        <th class="px-5 py-3.5">Hours Worked</th>
                        <th class="px-5 py-3.5">Late / Undertime</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-800">
                                {{ $rec->date->format('M d, Y') }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $rec->date->format('l') }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-mono">{{ $rec->time_in ? date('h:i A', strtotime($rec->time_in)) : '—' }}</td>
                            <td class="px-5 py-3.5 font-mono">{{ $rec->time_out ? date('h:i A', strtotime($rec->time_out)) : '—' }}</td>
                            <td class="px-5 py-3.5 font-mono font-bold">{{ $rec->hours_worked }} hrs</td>
                            <td class="px-5 py-3.5">
                                @if($rec->late_minutes > 0)
                                    <span class="text-rose-600 font-semibold block">{{ $rec->late_minutes }}m late</span>
                                @endif
                                @if($rec->undertime_minutes > 0)
                                    <span class="text-amber-600 font-semibold block">{{ $rec->undertime_minutes }}m undertime</span>
                                @endif
                                @if(!$rec->late_minutes && !$rec->undertime_minutes)
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $rec->status === 'present' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $rec->status === 'late' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $rec->status === 'absent' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                    {{ $rec->status === 'official_business' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $rec->status === 'leave' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                    {{ !in_array($rec->status, ['present','late','absent','official_business','leave']) ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                    {{ str_replace('_', ' ', $rec->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 text-xs">No attendance records found for this employee.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
