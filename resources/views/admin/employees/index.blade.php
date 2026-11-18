@extends('layouts.admin')

@section('title', 'Employees')
@section('page-title', 'Employee Directory')

@section('content')
<div class="space-y-5" x-data="{ importModal: false }">

    <!-- Directory Status Tabs -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <a href="{{ route('admin.employees.index', array_merge(request()->except(['page', 'view']), ['view' => 'active'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $view === 'active' ? 'bg-red-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>Active Staff</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $view === 'active' ? 'bg-red-800/80 text-white' : 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">{{ $activeCount }}</span>
        </a>
        <a href="{{ route('admin.employees.index', array_merge(request()->except(['page', 'view']), ['view' => 'inactive'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $view === 'inactive' ? 'bg-red-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>Resigned / Inactive Records</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $view === 'inactive' ? 'bg-red-800/80 text-white' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">{{ $inactiveCount }}</span>
        </a>
        <a href="{{ route('admin.employees.index', array_merge(request()->except(['page', 'view']), ['view' => 'all'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $view === 'all' ? 'bg-red-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>All Staff Records</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $view === 'all' ? 'bg-red-800/80 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">{{ $totalCount }}</span>
        </a>
    </div>

    <!-- Top Bar: Action Buttons & Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1 w-full">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..."
                       class="pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-100 rounded-xl text-xs w-full focus:bg-white dark:focus:bg-slate-800 focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
            </div>

            <select name="department" class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>

            <select name="status" class="w-full sm:w-auto px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 dark:text-slate-200 rounded-xl text-xs text-slate-700 focus:bg-white focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                <option value="">All Statuses</option>
                <option value="Regular" {{ request('status') == 'Regular' ? 'selected' : '' }}>Regular</option>
                <option value="Probationary" {{ request('status') == 'Probationary' ? 'selected' : '' }}>Probationary</option>
                <option value="Contractual" {{ request('status') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
            </select>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-red-700 hover:bg-red-800 text-white rounded-xl text-xs font-semibold transition shadow-xs text-center">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'department', 'status']))
                    <a href="{{ route('admin.employees.index') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-medium px-2 py-1">Reset</a>
                @endif
            </div>
        </form>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full lg:w-auto justify-start sm:justify-end">
            <a href="{{ route('admin.employees.export') }}" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="whitespace-nowrap">Export</span>
            </a>
            <button type="button" @click="importModal = true" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-transparent dark:border-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span class="whitespace-nowrap">Import</span>
            </button>
            <a href="{{ route('admin.employees.create') }}" class="flex-1 sm:flex-initial justify-center px-3.5 py-2 bg-red-700 text-white rounded-xl text-xs font-bold hover:bg-red-800 transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="whitespace-nowrap">Add Staff</span>
            </a>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50/75 dark:bg-slate-800/80 text-[11px] uppercase text-slate-400 dark:text-slate-400 font-bold tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Employee Name</th>
                        <th class="px-4 py-3.5">ID Number</th>
                        <th class="px-4 py-3.5">Designation & Office</th>
                        <th class="px-4 py-3.5">Tenure / Status</th>
                        <th class="px-4 py-3.5">Date Hired</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-slate-50/60 transition group">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if($employee->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-2xs">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs uppercase border border-slate-200/80">
                                            {{ substr($employee->name, 0, 2) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('admin.employees.profile', $employee) }}" class="font-bold text-slate-900 group-hover:text-red-700 transition block">
                                            {{ $employee->name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 flex-wrap text-[11px] text-slate-400">
                                            <span>{{ $employee->sex ?? 'Unspecified' }} &bull; Age {{ $employee->age ?? 'N/A' }}</span>
                                            @if($employee->email || optional($employee->user)->email)
                                                <span class="text-[10px] text-red-700 font-mono flex items-center gap-0.5 font-medium">
                                                    &bull; {{ $employee->email ?? optional($employee->user)->email }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-xs font-semibold text-slate-700">
                                {{ $employee->employee_no ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-slate-800">{{ $employee->position ?? '—' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $employee->department->name ?? 'Unassigned' }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($employee->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/70 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60">
                                        {{ $employee->employment_status ?? 'Regular' }}
                                    </span>
                                @else
                                    <div class="space-y-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-bold rounded-full bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800/60">
                                            {{ $employee->separation_reason ?: 'Resigned / Inactive' }}
                                        </span>
                                        @if($employee->separation_date)
                                            <p class="text-[10px] text-slate-400 font-mono">Sep: {{ $employee->separation_date->format('M d, Y') }}</p>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                <div class="font-medium">{{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : '—' }}</div>
                                @if($employee->years_of_service)
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $employee->years_of_service }} yrs tenure</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.employees.profile', $employee) }}"
                                       class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200/80 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs"
                                       title="View Full Employee Profile">
                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Profile</span>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}"
                                       class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 hover:border-slate-300 rounded-lg text-xs font-semibold transition inline-flex items-center gap-1 shadow-2xs"
                                       title="Edit Employee Information">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                    @if(!$employee->is_active)
                                        <form method="POST" action="{{ route('admin.employees.reactivate', $employee) }}" class="inline" onsubmit="return confirm('Reactivate {{ addslashes($employee->name) }} to active staff?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 shadow-2xs" title="Reactivate Staff Record">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reactivate</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <p class="text-xs font-medium text-slate-600">No employee records match the filters.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Use the import button or add an employee record to begin.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Import Modal -->
    <div x-show="importModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="importModal = false"></div>

            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all my-4 sm:my-8 max-w-lg w-full border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col z-10">
                <form method="POST" action="{{ route('admin.employees.import') }}" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 overflow-y-auto flex-1">
                        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center border border-red-100 dark:border-red-900/60 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Import Master Employee Roster</h3>
                                <p class="text-xs text-slate-400">Excel / CSV spreadsheet batch processing</p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-4 leading-relaxed">
                            Upload your HRIS Excel or CSV file containing employee records. Columns including Name, ID Number, Position, Salary 80%, Allowance 20%, SSS/PhilHealth/HDMF, Address, Education, and Family will be automatically parsed.
                        </p>

                        <div class="mt-4 p-4 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Select Spreadsheet (.xlsx, .xls, .csv, .tsv)</label>
                            <input type="file" name="file" required accept=".xlsx,.xls,.csv,.tsv,.txt,.html,.htm,.xml"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 dark:file:bg-red-950/60 dark:file:text-red-300 hover:file:bg-red-100">
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 sm:px-6 py-3.5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="importModal = false" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-xl px-4 py-2 bg-red-700 text-xs font-bold text-white hover:bg-red-800 transition shadow-sm">
                            Upload &amp; Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
