@extends('layouts.admin')

@section('title', 'HR Analytics & Reports')
@section('page-title', 'Workforce & Geographic Analytics')

@section('content')
<div class="space-y-6">

    <!-- Top Header Card with Export & Print Actions -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-400 flex items-center justify-center border border-red-200/80 dark:border-red-900/60 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        <span>Workforce Geographic &amp; Demographics Analytics</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Workforce residency, unit headcount, and employee demographics.
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.employees.export', ['format' => 'xlsx']) }}"
               class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel (.xlsx)</span>
            </a>
            <a href="{{ route('admin.employees.export', ['format' => 'csv']) }}"
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
            <button onclick="window.print()" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- PRIMARY SECTION: Address Analytics (Per Municipality / City) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Municipality / City Breakdown (2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-5">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">📍</span>
                        <span>Employee Residency per Municipality / City</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Distribution of workforce across local municipalities and cities</p>
                </div>
                <span class="text-xs font-mono font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-900/60 px-3 py-1 rounded-full flex items-center gap-1.5 shadow-2xs">
                    <span>🏙️</span>
                    <span>{{ count($municipalityData) }} Locations</span>
                </span>
            </div>

            <div class="space-y-4">
                @forelse($municipalityData as $index => $item)
                    @php
                        $rank = $index + 1;
                        $rankPalettes = [
                            1 => ['bar' => 'from-red-600 via-rose-600 to-red-500', 'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-800/80', 'icon' => '🥇 #1 Top', 'pin' => 'text-red-600 dark:text-red-400', 'count' => 'bg-red-50/90 dark:bg-red-950/60 text-red-800 dark:text-red-300 border-red-200 dark:border-red-900/60', 'pct' => 'bg-red-100 dark:bg-red-900/60 text-red-900 dark:text-red-200 border-red-300/80 dark:border-red-700/80'],
                            2 => ['bar' => 'from-indigo-600 via-blue-600 to-sky-500', 'badge' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/80', 'icon' => '🥈 #2', 'pin' => 'text-indigo-600 dark:text-indigo-400', 'count' => 'bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-900/60', 'pct' => 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-900 dark:text-indigo-200 border-indigo-300/80 dark:border-indigo-700/80'],
                            3 => ['bar' => 'from-emerald-600 via-teal-600 to-green-500', 'badge' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/80', 'icon' => '🥉 #3', 'pin' => 'text-emerald-600 dark:text-emerald-400', 'count' => 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/60', 'pct' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-200 border-emerald-300/80 dark:border-emerald-700/80'],
                            4 => ['bar' => 'from-sky-500 via-cyan-500 to-blue-500', 'badge' => 'bg-sky-50 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border-sky-200 dark:border-sky-800/80', 'icon' => '📍 #4', 'pin' => 'text-sky-600 dark:text-sky-400', 'count' => 'bg-sky-50/90 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border-sky-200 dark:border-sky-900/60', 'pct' => 'bg-sky-100 dark:bg-sky-900/60 text-sky-900 dark:text-sky-200 border-sky-300/80 dark:border-sky-700/80'],
                            5 => ['bar' => 'from-purple-500 via-violet-500 to-fuchsia-500', 'badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800/80', 'icon' => '📍 #5', 'pin' => 'text-purple-600 dark:text-purple-400', 'count' => 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-900/60', 'pct' => 'bg-purple-100 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200 border-purple-300/80 dark:border-purple-700/80'],
                            6 => ['bar' => 'from-teal-500 via-emerald-500 to-cyan-500', 'badge' => 'bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border-teal-200 dark:border-teal-800/80', 'icon' => '📍 #6', 'pin' => 'text-teal-600 dark:text-teal-400', 'count' => 'bg-teal-50/90 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border-teal-200 dark:border-teal-900/60', 'pct' => 'bg-teal-100 dark:bg-teal-900/60 text-teal-900 dark:text-teal-200 border-teal-300/80 dark:border-teal-700/80'],
                            7 => ['bar' => 'from-amber-500 via-orange-500 to-yellow-500', 'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800/80', 'icon' => '📍 #7', 'pin' => 'text-amber-600 dark:text-amber-400', 'count' => 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900/60', 'pct' => 'bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border-amber-300/80 dark:border-amber-700/80'],
                            8 => ['bar' => 'from-rose-500 via-pink-500 to-rose-600', 'badge' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800/80', 'icon' => '📍 #8', 'pin' => 'text-rose-600 dark:text-rose-400', 'count' => 'bg-rose-50/90 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-900/60', 'pct' => 'bg-rose-100 dark:bg-rose-900/60 text-rose-900 dark:text-rose-200 border-rose-300/80 dark:border-rose-700/80'],
                        ];
                        $rCfg = $rankPalettes[$rank] ?? [
                            'bar'   => 'from-blue-600 via-indigo-600 to-purple-600',
                            'badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-900/60',
                            'icon'  => '#' . $rank,
                            'pin'   => 'text-blue-600 dark:text-blue-400',
                            'count' => 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-900/60',
                            'pct'   => 'bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 border-blue-300/80 dark:border-blue-700/80',
                        ];
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold border {{ $rCfg['badge'] }} shadow-2xs inline-flex items-center gap-1 shrink-0">
                                    {{ $rCfg['icon'] }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 {{ $rCfg['pin'] }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ $item['city'] }}</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0 font-mono">
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold border shadow-2xs {{ $rCfg['count'] }} flex items-center gap-1">
                                    <span>👥</span>
                                    <span>{{ $item['count'] }}</span>
                                </span>
                                <span class="px-1.5 py-0.5 rounded-md text-[11px] font-black border shadow-2xs {{ $rCfg['pct'] }}">
                                    {{ $item['percentage'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden shadow-2xs">
                            <div class="bg-gradient-to-r {{ $rCfg['bar'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ max($item['percentage'], 3) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-8 text-center">No employee addresses recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Provincial / Regional Summary (1 col) -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-5">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">🗺️</span>
                        <span>Provincial Summary</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Residency by province &amp; administrative region</p>
                </div>

                <div class="space-y-2.5">
                    @foreach($provinceCounts as $prov => $pCount)
                        <div class="p-3.5 bg-slate-50/70 dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/80 flex items-center justify-between hover:border-red-300 dark:hover:border-red-900/60 transition">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center font-bold text-xs border border-red-200 dark:border-red-900/60 shadow-2xs">
                                    🏛️
                                </div>
                                <div>
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $prov }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Region IV-A (CALABARZON)</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-2xs inline-flex items-center gap-1">
                                <span>👥</span>
                                <span>{{ $pCount }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>


    <!-- Section 2: Headcount by Organizational Unit (The 6 Departments) -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 mb-5">
            <div>
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                    <span class="text-base">🏢</span>
                    <span>Headcount by Organizational Unit</span>
                </h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Workforce distribution across the 6 major offices</p>
            </div>
            <span class="text-xs font-mono font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1 rounded-full flex items-center gap-1.5 shadow-2xs">
                <span>🏛️</span>
                <span>6 Units Configured</span>
            </span>
        </div>

        @php
            $unitPalette = [
                'EO'    => [
                    'symbol'       => '👑',
                    'bar'          => 'from-purple-600 via-violet-600 to-indigo-600',
                    'badge'        => 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900/60',
                    'countBadge'   => 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-900/60',
                    'percentBadge' => 'bg-purple-100 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200 border-purple-300/80 dark:border-purple-700/80',
                ],
                'ADMIN' => [
                    'symbol'       => '🏢',
                    'bar'          => 'from-blue-600 via-indigo-600 to-sky-600',
                    'badge'        => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900/60',
                    'countBadge'   => 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-900/60',
                    'percentBadge' => 'bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 border-blue-300/80 dark:border-blue-700/80',
                ],
                'FMO'   => [
                    'symbol'       => '💰',
                    'bar'          => 'from-emerald-600 via-teal-600 to-green-500',
                    'badge'        => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60',
                    'countBadge'   => 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/60',
                    'percentBadge' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-200 border-emerald-300/80 dark:border-emerald-700/80',
                ],
                'FIN'   => [
                    'symbol'       => '💰',
                    'bar'          => 'from-emerald-600 via-teal-600 to-green-500',
                    'badge'        => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60',
                    'countBadge'   => 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/60',
                    'percentBadge' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-200 border-emerald-300/80 dark:border-emerald-700/80',
                ],
                'AKP'   => [
                    'symbol'       => '🤝',
                    'bar'          => 'from-rose-600 via-red-600 to-amber-500',
                    'badge'        => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900/60',
                    'countBadge'   => 'bg-rose-50/90 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-900/60',
                    'percentBadge' => 'bg-rose-100 dark:bg-rose-900/60 text-rose-900 dark:text-rose-200 border-rose-300/80 dark:border-rose-700/80',
                ],
                'MSS'   => [
                    'symbol'       => '🌿',
                    'bar'          => 'from-teal-600 via-emerald-500 to-cyan-500',
                    'badge'        => 'bg-teal-50 text-teal-800 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-900/60',
                    'countBadge'   => 'bg-teal-50/90 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border-teal-200 dark:border-teal-900/60',
                    'percentBadge' => 'bg-teal-100 dark:bg-teal-900/60 text-teal-900 dark:text-teal-200 border-teal-300/80 dark:border-teal-700/80',
                ],
                'RDO'   => [
                    'symbol'       => '🚀',
                    'bar'          => 'from-amber-500 via-orange-500 to-yellow-500',
                    'badge'        => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900/60',
                    'countBadge'   => 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900/60',
                    'percentBadge' => 'bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border-amber-300/80 dark:border-amber-700/80',
                ],
                'DRRM'  => [
                    'symbol'       => '🛡️',
                    'bar'          => 'from-orange-500 via-rose-600 to-red-600',
                    'badge'        => 'bg-orange-50 text-orange-800 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-900/60',
                    'countBadge'   => 'bg-orange-50/90 dark:bg-orange-950/60 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-900/60',
                    'percentBadge' => 'bg-orange-100 dark:bg-orange-900/60 text-orange-900 dark:text-orange-200 border-orange-300/80 dark:border-orange-700/80',
                ],
                'HR'    => [
                    'symbol'       => '👥',
                    'bar'          => 'from-fuchsia-600 via-purple-600 to-pink-500',
                    'badge'        => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-200 dark:bg-fuchsia-950/60 dark:text-fuchsia-300 dark:border-fuchsia-900/60',
                    'countBadge'   => 'bg-fuchsia-50/90 dark:bg-fuchsia-950/60 text-fuchsia-800 dark:text-fuchsia-300 border-fuchsia-200 dark:border-fuchsia-900/60',
                    'percentBadge' => 'bg-fuchsia-100 dark:bg-fuchsia-900/60 text-fuchsia-900 dark:text-fuchsia-200 border-fuchsia-300/80 dark:border-fuchsia-700/80',
                ],
                'OPS'   => [
                    'symbol'       => '⚙️',
                    'bar'          => 'from-cyan-600 via-sky-600 to-blue-500',
                    'badge'        => 'bg-cyan-50 text-cyan-800 border-cyan-200 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border-cyan-900/60',
                    'countBadge'   => 'bg-cyan-50/90 dark:bg-cyan-950/60 text-cyan-800 dark:text-cyan-300 border-cyan-200 dark:border-cyan-900/60',
                    'percentBadge' => 'bg-cyan-100 dark:bg-cyan-900/60 text-cyan-900 dark:text-cyan-200 border-cyan-300/80 dark:border-cyan-700/80',
                ],
                'IT'    => [
                    'symbol'       => '💻',
                    'bar'          => 'from-indigo-600 via-blue-600 to-teal-500',
                    'badge'        => 'bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-900/60',
                    'countBadge'   => 'bg-indigo-50/90 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-900/60',
                    'percentBadge' => 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-900 dark:text-indigo-200 border-indigo-300/80 dark:border-indigo-700/80',
                ],
            ];
            $distinctFallbacks = [
                ['symbol' => '👑', 'bar' => 'from-purple-600 via-violet-600 to-indigo-600', 'badge' => 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900/60', 'countBadge' => 'bg-purple-50/90 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-900/60', 'percentBadge' => 'bg-purple-100 dark:bg-purple-900/60 text-purple-900 dark:text-purple-200 border-purple-300/80 dark:border-purple-700/80'],
                ['symbol' => '🏢', 'bar' => 'from-blue-600 via-indigo-600 to-sky-600', 'badge' => 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900/60', 'countBadge' => 'bg-blue-50/90 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-900/60', 'percentBadge' => 'bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-200 border-blue-300/80 dark:border-blue-700/80'],
                ['symbol' => '💰', 'bar' => 'from-emerald-600 via-teal-600 to-green-500', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900/60', 'countBadge' => 'bg-emerald-50/90 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900/60', 'percentBadge' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-900 dark:text-emerald-200 border-emerald-300/80 dark:border-emerald-700/80'],
                ['symbol' => '🤝', 'bar' => 'from-rose-600 via-red-600 to-amber-500', 'badge' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900/60', 'countBadge' => 'bg-rose-50/90 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-900/60', 'percentBadge' => 'bg-rose-100 dark:bg-rose-900/60 text-rose-900 dark:text-rose-200 border-rose-300/80 dark:border-rose-700/80'],
                ['symbol' => '🌿', 'bar' => 'from-teal-600 via-emerald-500 to-cyan-500', 'badge' => 'bg-teal-50 text-teal-800 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-900/60', 'countBadge' => 'bg-teal-50/90 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border-teal-200 dark:border-teal-900/60', 'percentBadge' => 'bg-teal-100 dark:bg-teal-900/60 text-teal-900 dark:text-teal-200 border-teal-300/80 dark:border-teal-700/80'],
                ['symbol' => '🚀', 'bar' => 'from-amber-500 via-orange-500 to-yellow-500', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900/60', 'countBadge' => 'bg-amber-50/90 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900/60', 'percentBadge' => 'bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 border-amber-300/80 dark:border-amber-700/80'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            @foreach($deptData as $deptIndex => $dept)
                @php
                    $code = strtoupper(trim($dept['code'] ?? ''));
                    $cfg = $unitPalette[$code] ?? $distinctFallbacks[$deptIndex % count($distinctFallbacks)];
                @endphp
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold border {{ $cfg['badge'] }} shadow-2xs inline-flex items-center gap-1 shrink-0">
                                <span>{{ $cfg['symbol'] }}</span>
                                <span>{{ $dept['code'] }}</span>
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $dept['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 font-mono">
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold border shadow-2xs {{ $cfg['countBadge'] }} flex items-center gap-1">
                                <span>👥</span>
                                <span>{{ $dept['count'] }} <span class="text-[10px] font-normal opacity-80">staff</span></span>
                            </span>
                            <span class="px-1.5 py-0.5 rounded-md text-[11px] font-black border shadow-2xs {{ $cfg['percentBadge'] }}">
                                {{ $dept['percentage'] }}%
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden shadow-2xs">
                        <div class="bg-gradient-to-r {{ $cfg['bar'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ $dept['percentage'] > 0 ? max($dept['percentage'], 4) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Demographics (Gender & Age Distribution) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Gender Distribution Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">⚧</span>
                        <span>Gender &amp; Sex Distribution</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Workforce gender ratio &amp; balance</p>
                </div>

                @php
                    $tot = $males + $females + $otherGender;
                    $malePercent = $tot > 0 ? round(($males / $tot) * 100, 1) : 0;
                    $femalePercent = $tot > 0 ? round(($females / $tot) * 100, 1) : 0;
                @endphp

                <div class="grid grid-cols-2 gap-4 my-2">
                    <!-- Male Metric -->
                    <div class="p-4 rounded-xl bg-sky-50/70 dark:bg-sky-950/40 border border-sky-200/80 dark:border-sky-800/60 text-center shadow-2xs">
                        <div class="flex items-center justify-center gap-1 text-[11px] uppercase font-extrabold text-sky-800 dark:text-sky-300 tracking-wider">
                            <span>♂</span>
                            <span>Male Staff</span>
                        </div>
                        <p class="text-3xl font-black text-sky-900 dark:text-sky-200 mt-1.5 font-mono">{{ $males }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold text-sky-800 dark:text-sky-300 bg-sky-100/80 dark:bg-sky-900/60 font-mono">{{ $malePercent }}%</span>
                    </div>

                    <!-- Female Metric -->
                    <div class="p-4 rounded-xl bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/60 text-center shadow-2xs">
                        <div class="flex items-center justify-center gap-1 text-[11px] uppercase font-extrabold text-rose-800 dark:text-rose-300 tracking-wider">
                            <span>♀</span>
                            <span>Female Staff</span>
                        </div>
                        <p class="text-3xl font-black text-rose-900 dark:text-rose-200 mt-1.5 font-mono">{{ $females }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold text-rose-800 dark:text-rose-300 bg-rose-100/80 dark:bg-rose-900/60 font-mono">{{ $femalePercent }}%</span>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden flex shadow-2xs">
                    <div class="bg-sky-500 transition-all duration-500" style="width: {{ $malePercent }}%" title="Male: {{ $malePercent }}%"></div>
                    <div class="bg-rose-500 transition-all duration-500" style="width: {{ $femalePercent }}%" title="Female: {{ $femalePercent }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono font-semibold text-slate-500 dark:text-slate-400 mt-2">
                    <span class="flex items-center gap-1 text-sky-700 dark:text-sky-400">
                        <span class="w-2 h-2 rounded-full bg-sky-500 inline-block"></span>
                        <span>Male: {{ $malePercent }}% ({{ $males }})</span>
                    </span>
                    <span class="flex items-center gap-1 text-rose-700 dark:text-rose-400">
                        <span>Female: {{ $femalePercent }}% ({{ $females }})</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Age Distribution Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                    <span class="text-base">🎂</span>
                    <span>Age Demographics</span>
                </h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Headcount segmented by generational career brackets</p>
            </div>

            @php
                $ageMeta = [
                    'Under 25'     => ['symbol' => '🌱', 'bar' => 'from-teal-500 to-emerald-500', 'chip' => 'Early Career'],
                    '25 - 34'      => ['symbol' => '⚡', 'bar' => 'from-sky-500 to-blue-600', 'chip' => 'Emerging'],
                    '35 - 44'      => ['symbol' => '🎯', 'bar' => 'from-indigo-500 to-purple-600', 'chip' => 'Core Prime'],
                    '45 - 54'      => ['symbol' => '💼', 'bar' => 'from-purple-500 to-fuchsia-600', 'chip' => 'Senior'],
                    '55 and above' => ['symbol' => '🏅', 'bar' => 'from-amber-500 to-orange-500', 'chip' => 'Veteran'],
                ];
                $totAge = array_sum($ageBrackets);
            @endphp

            <div class="space-y-3.5">
                @foreach($ageBrackets as $bracket => $count)
                    @php
                        $percent = $totAge > 0 ? round(($count / $totAge) * 100, 1) : 0;
                        $meta = $ageMeta[$bracket] ?? ['symbol' => '👥', 'bar' => 'from-indigo-600 to-blue-500', 'chip' => 'Age Group'];
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="text-xs shrink-0">{{ $meta['symbol'] }}</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $bracket }}</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono hidden sm:inline whitespace-nowrap">({{ $meta['chip'] }})</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1 shrink-0 whitespace-nowrap">
                                <span>{{ $count }}</span>
                                <span class="text-slate-400 font-normal text-[11px]">({{ $percent }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-2xs">
                            <div class="bg-gradient-to-r {{ $meta['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ max($percent, 2) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Section 4: Tenure & Employment Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Tenure & Retention Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                            <span class="text-base">⏳</span>
                            <span>Tenure &amp; Service Length</span>
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Years of service &amp; institutional retention</p>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 shrink-0 shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400">Avg Retention:</span>
                        <span class="text-xs font-black font-mono text-emerald-900 dark:text-emerald-200">{{ $avgTenure }} yrs</span>
                    </div>
                </div>

                @php
                    $tenureMeta = [
                        'Less than 1 yr' => ['symbol' => '🐣', 'bar' => 'from-sky-400 to-cyan-500', 'desc' => 'New / Onboarding'],
                        '1 - 3 yrs'      => ['symbol' => '🌿', 'bar' => 'from-emerald-500 to-green-600', 'desc' => 'Formative'],
                        '3 - 5 yrs'      => ['symbol' => '🌳', 'bar' => 'from-teal-500 to-emerald-600', 'desc' => 'Established'],
                        '5+ yrs'         => ['symbol' => '🏆', 'bar' => 'from-amber-500 to-yellow-500', 'desc' => 'Loyal Pillars'],
                    ];
                    $totTenure = array_sum($tenureBrackets);
                @endphp

                <div class="space-y-3.5">
                    @foreach($tenureBrackets as $bracket => $count)
                        @php
                            $percent = $totTenure > 0 ? round(($count / $totTenure) * 100, 1) : 0;
                            $meta = $tenureMeta[$bracket] ?? ['symbol' => '⏱️', 'bar' => 'from-emerald-600 to-teal-600', 'desc' => 'Tenure'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-xs shrink-0">{{ $meta['symbol'] }}</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 whitespace-nowrap">{{ $bracket }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono hidden sm:inline whitespace-nowrap">({{ $meta['desc'] }})</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1 shrink-0 whitespace-nowrap">
                                    <span>{{ $count }}</span>
                                    <span class="text-slate-400 font-normal text-[11px]">({{ $percent }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-2xs">
                                <div class="bg-gradient-to-r {{ $meta['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ max($percent, 2) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Employment Status Card -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">📑</span>
                        <span>Employment Status</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Regular plantilla vs. non-regular workforce ratio</p>
                </div>

                @php
                    $statusMeta = [
                        'regular'       => ['symbol' => '🛡️', 'bar' => 'from-emerald-600 to-teal-600', 'label' => 'Regular / Permanent'],
                        'probationary'  => ['symbol' => '⏳', 'bar' => 'from-amber-500 to-orange-500', 'label' => 'Probationary'],
                        'contractual'   => ['symbol' => '📋', 'bar' => 'from-blue-500 to-indigo-600', 'label' => 'Contractual / Project'],
                        'casual'        => ['symbol' => '💼', 'bar' => 'from-purple-500 to-violet-600', 'label' => 'Casual / Temporary'],
                    ];
                    $totStat = array_sum($statusCounts);
                @endphp

                <div class="space-y-3.5">
                    @forelse($statusCounts as $status => $count)
                        @php
                            $key = strtolower(trim($status ?: 'unclassified'));
                            $meta = $statusMeta[$key] ?? ['symbol' => '📄', 'bar' => 'from-slate-600 to-slate-500', 'label' => ucfirst($status ?: 'Unclassified')];
                            $percent = $totStat > 0 ? round(($count / $totStat) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs mb-1.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-xs shrink-0">{{ $meta['symbol'] }}</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $meta['label'] }}</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1 shrink-0 whitespace-nowrap">
                                    <span>{{ $count }}</span>
                                    <span class="text-slate-400 font-normal text-[11px]">({{ $percent }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-2xs">
                                <div class="bg-gradient-to-r {{ $meta['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ max($percent, 2) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No employee records.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Section 5: Training Compliance & Civil Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Certificate & Training Compliance -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">📜</span>
                        <span>Training &amp; Certificate Compliance</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Professional licenses, renewals &amp; expiry monitoring</p>
                </div>

                <div class="grid grid-cols-3 gap-3 text-center my-3">
                    <!-- Valid -->
                    <div class="p-3.5 bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200/70 dark:border-emerald-800/60 rounded-xl shadow-2xs">
                        <div class="flex items-center justify-center gap-1 text-[10px] uppercase font-extrabold text-emerald-800 dark:text-emerald-300 tracking-wider">
                            <span>🟢</span>
                            <span>Valid</span>
                        </div>
                        <p class="text-2xl font-black text-emerald-900 dark:text-emerald-200 mt-1 font-mono">{{ $validCerts }}</p>
                        <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-semibold">Active</span>
                    </div>

                    <!-- Expiring -->
                    <div class="p-3.5 bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/60 rounded-xl shadow-2xs">
                        <div class="flex items-center justify-center gap-1 text-[10px] uppercase font-extrabold text-amber-800 dark:text-amber-300 tracking-wider">
                            <span>⚠️</span>
                            <span>Expiring</span>
                        </div>
                        <p class="text-2xl font-black text-amber-900 dark:text-amber-200 mt-1 font-mono">{{ $expiringSoon }}</p>
                        <span class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold font-mono">&lt; 30 days</span>
                    </div>

                    <!-- Expired -->
                    <div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200/70 dark:border-rose-800/60 rounded-xl shadow-2xs">
                        <div class="flex items-center justify-center gap-1 text-[10px] uppercase font-extrabold text-rose-800 dark:text-rose-300 tracking-wider">
                            <span>🔴</span>
                            <span>Expired</span>
                        </div>
                        <p class="text-2xl font-black text-rose-900 dark:text-rose-200 mt-1 font-mono">{{ $expiredCerts }}</p>
                        <span class="text-[10px] text-rose-700 dark:text-rose-400 font-semibold">Renewal Due</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center justify-between">
                    <span>Total credential records tracked:</span>
                    <strong class="text-slate-900 dark:text-slate-100 font-mono">{{ $totalCerts }} files</strong>
                </p>
                @if($expiringSoon > 0)
                    <div class="mt-2.5 p-2.5 bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 rounded-xl text-xs font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-2 shadow-2xs">
                        <span class="text-sm">⚠️</span>
                        <span>Attention: {{ $expiringSoon }} certificate(s) require renewal within 30 days.</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Civil Status Distribution -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <span class="text-base">💍</span>
                        <span>Civil Status Distribution</span>
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Marital &amp; family status profile of workforce</p>
                </div>

                @php
                    $civilMeta = [
                        'single'    => ['symbol' => '👤', 'bar' => 'from-cyan-500 to-blue-500'],
                        'married'   => ['symbol' => '💍', 'bar' => 'from-rose-500 to-pink-500'],
                        'widowed'   => ['symbol' => '🕊️', 'bar' => 'from-purple-500 to-slate-500'],
                        'separated' => ['symbol' => '🤝', 'bar' => 'from-amber-500 to-orange-500'],
                    ];
                    $totCiv = array_sum($civilCounts);
                @endphp

                <div class="space-y-3.5">
                    @forelse($civilCounts as $civil => $count)
                        @php
                            $key = strtolower(trim($civil ?: 'not specified'));
                            $meta = $civilMeta[$key] ?? ['symbol' => '👥', 'bar' => 'from-teal-600 to-emerald-600'];
                            $percent = $totCiv > 0 ? round(($count / $totCiv) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs mb-1.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-xs shrink-0">{{ $meta['symbol'] }}</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ $civil ?: 'Not Specified' }}</span>
                                </div>
                                <span class="font-mono font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1 shrink-0 whitespace-nowrap">
                                    <span>{{ $count }}</span>
                                    <span class="text-slate-400 font-normal text-[11px]">({{ $percent }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden shadow-2xs">
                                <div class="bg-gradient-to-r {{ $meta['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ max($percent, 2) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No employee records.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
