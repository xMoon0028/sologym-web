@extends('layouts.portal')

@section('title', 'My Profile')
@section('page-title', 'My Employee Record')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Header -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5">
        @if($employee->photo)
            <img src="{{ asset('storage/' . $employee->photo) }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-red-700 shadow-sm flex-shrink-0">
        @else
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-black text-xl uppercase border border-slate-200 dark:border-slate-700 flex-shrink-0">
                {{ substr($employee->name, 0, 2) }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 tracking-tight">{{ $employee->name }}</h2>
                <span class="text-xs bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 px-2.5 py-0.5 rounded-full font-semibold">{{ $employee->employment_status }}</span>
                @if($employee->approval_role && $employee->approval_role !== 'none')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-400 border border-amber-300 dark:border-amber-800/60">
                        Approver: {{ $employee->approval_role_label }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $employee->position ?? 'Staff' }} &bull; {{ $employee->department?->name ?? 'LASAC' }}</p>
            <div class="flex items-center gap-2 mt-2 flex-wrap text-xs">
                <span class="font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700 px-2.5 py-0.5 rounded-md font-bold">ID: {{ $employee->employee_no ?? '—' }}</span>
                @if($employee->email || optional($employee->user)->email)
                    <span class="font-mono font-bold bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/60 px-2.5 py-0.5 rounded-md flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $employee->email ?? optional($employee->user)->email }}
                    </span>
                @endif
                <span class="font-mono font-bold bg-sky-50 dark:bg-sky-950/40 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60 px-2.5 py-0.5 rounded-md">
                    Offset: {{ number_format($employee->time_credit_balance, 2) }} hrs
                </span>
            </div>
        </div>
    </div>

    <!-- Leave Balances Card -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider">Leave Credit Balances</h3>
            <a href="{{ route('portal.leaves.index') }}" class="text-xs font-bold text-red-700 dark:text-red-400 hover:text-red-800 transition">Apply for Leave &rarr;</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3.5 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50">
                <span class="text-[10px] font-bold text-blue-800 dark:text-blue-400 uppercase block">Vacation Leave</span>
                <p class="text-xl font-black text-blue-900 dark:text-blue-300 font-mono mt-1">{{ number_format($employee->remaining_vacation_leave, 1) }} <span class="text-xs font-normal text-blue-600 dark:text-blue-400">/ {{ number_format($employee->vacation_leave_credits, 1) }}d</span></p>
            </div>
            <div class="p-3.5 rounded-xl bg-rose-50/60 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50">
                <span class="text-[10px] font-bold text-rose-800 dark:text-rose-400 uppercase block">Sick Leave</span>
                <p class="text-xl font-black text-rose-900 dark:text-rose-300 font-mono mt-1">{{ number_format($employee->remaining_sick_leave, 1) }} <span class="text-xs font-normal text-rose-600 dark:text-rose-400">/ {{ number_format($employee->sick_leave_credits, 1) }}d</span></p>
            </div>
            <div class="p-3.5 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/50">
                <span class="text-[10px] font-bold text-amber-800 dark:text-amber-400 uppercase block">Emergency Leave</span>
                <p class="text-xl font-black text-amber-900 dark:text-amber-300 font-mono mt-1">{{ number_format($employee->remaining_emergency_leave, 1) }} <span class="text-xs font-normal text-amber-600 dark:text-amber-400">/ {{ number_format($employee->emergency_leave_credits, 1) }}d</span></p>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/50">
                <span class="text-[10px] font-bold text-purple-800 dark:text-purple-400 uppercase block">Offset (Time Credits)</span>
                <p class="text-xl font-black text-purple-900 dark:text-purple-300 font-mono mt-1">{{ number_format($employee->time_credit_balance, 2) }} <span class="text-xs font-normal text-purple-600 dark:text-purple-400">hrs</span></p>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Employment & Personal -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Employment &amp; Personal</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Date Hired</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $employee->date_hired ? $employee->date_hired->format('M d, Y') : '—' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Tenure</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 font-mono mt-0.5">{{ $employee->years_of_service ? $employee->years_of_service . ' yrs' : '—' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Birthdate / Age</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $employee->birthdate ? $employee->birthdate->format('M d, Y') : '—' }} ({{ $employee->age }}yo)</p>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Sex / Civil Status</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $employee->sex ?? '—' }} / {{ $employee->civil_status ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Government IDs -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Government Numbers</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs font-mono">
                <div class="p-2.5 bg-slate-50/60 dark:bg-slate-800/60 rounded-xl border border-slate-200/50 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 font-sans text-[10px] uppercase font-semibold block">SSS</span>
                    <p class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ optional($employee->governmentIds)->sss_no ?? '—' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50/60 dark:bg-slate-800/60 rounded-xl border border-slate-200/50 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 font-sans text-[10px] uppercase font-semibold block">Pag-IBIG</span>
                    <p class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ optional($employee->governmentIds)->pagibig_no ?? '—' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50/60 dark:bg-slate-800/60 rounded-xl border border-slate-200/50 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 font-sans text-[10px] uppercase font-semibold block">PhilHealth</span>
                    <p class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ optional($employee->governmentIds)->philhealth_no ?? '—' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50/60 dark:bg-slate-800/60 rounded-xl border border-slate-200/50 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 font-sans text-[10px] uppercase font-semibold block">TIN</span>
                    <p class="font-bold text-slate-900 dark:text-slate-100 mt-0.5">{{ optional($employee->governmentIds)->tin_no ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Residency &amp; Geographic Location</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs mb-3">
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 text-[10px] uppercase font-semibold block">Region</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs truncate">{{ optional($employee->address)->region ?? 'Region IV-A (CALABARZON)' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 text-[10px] uppercase font-semibold block">Province</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">{{ optional($employee->address)->province ?? 'Batangas' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 text-[10px] uppercase font-semibold block">City / Municipality</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">{{ optional($employee->address)->municipality_city ?? 'Unspecified' }}</p>
                </div>
                <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-100 dark:border-slate-700">
                    <span class="text-slate-400 dark:text-slate-400 text-[10px] uppercase font-semibold block">Barangay</span>
                    <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5 text-xs">{{ optional($employee->address)->barangay ?? '—' }}</p>
                </div>
            </div>
            <div class="space-y-2.5 text-xs pt-2 border-t border-slate-100 dark:border-slate-800">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Permanent Address</span>
                    <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ optional($employee->address)->permanent_address ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-semibold block">Current Address</span>
                    <p class="font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ optional($employee->address)->current_address ?? '—' }} <span class="text-slate-400 dark:text-slate-500 font-mono">(ZIP: {{ optional($employee->address)->zip_code ?? '—' }})</span></p>
                </div>
            </div>
        </div>

        <!-- Certificates on File -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Certificates &amp; Training Records</h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @forelse($employee->certificates as $c)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-slate-100">{{ $c->title }}</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $c->issuer ?? '—' }} &bull; Issued: {{ $c->date_issued ? $c->date_issued->format('M Y') : '—' }}</p>
                        </div>
                        @if($c->file_path)
                            <a href="{{ asset('storage/' . $c->file_path) }}" target="_blank" class="text-red-700 dark:text-red-400 font-bold hover:underline">View</a>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-400 dark:text-slate-500 text-xs py-4 text-center">No certificates uploaded on file.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
