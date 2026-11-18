<!DOCTYPE html>
<html lang="en" class="min-h-full bg-slate-50 dark:bg-[#090e1a]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LASAC HRIS') — Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tab_logo.png') }}">
    <!-- Optimized High-Legibility Typography: Inter & JetBrains Mono with Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        html, body {
            font-family: var(--font-sans);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        .font-sans {
            font-family: var(--font-sans) !important;
        }
        .font-mono {
            font-family: var(--font-mono) !important;
        }
        table, .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="min-h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#090e1a] selection:bg-red-600 selection:text-white transition-colors duration-200">
<div class="flex min-h-screen bg-[#f8fafc] dark:bg-[#090e1a]" x-data="{ 
    sidebarOpen: false,
    sidebarHidden: localStorage.getItem('admin_sidebar_hidden') === 'true',
    toggleSidebar() {
        if (window.innerWidth >= 1024) {
            this.sidebarHidden = !this.sidebarHidden;
            localStorage.setItem('admin_sidebar_hidden', this.sidebarHidden);
        } else {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }
}">

    <!-- Frosted Glass Top Bar (Always retained on top) -->
    <header class="fixed top-0 inset-x-0 z-40 h-14 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-3 sm:px-6 flex items-center justify-between transition-colors">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <!-- Single subtle toggle button (not so obvious) -->
            <button @click="toggleSidebar()" type="button" 
                    class="p-2 -ml-1 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition flex-shrink-0 min-h-[38px] min-w-[38px] flex items-center justify-center"
                    :title="sidebarHidden ? 'Show Sidebar' : 'Hide Sidebar'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Always retain LASAC logo on top -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 sm:gap-2.5 flex-shrink-0">
                <img src="{{ asset('images/lasac_logo.png') }}" alt="LASAC" class="h-6 sm:h-7 w-auto object-contain">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black tracking-wider uppercase bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/60 dark:text-red-300 dark:border-red-900/60">
                    HRIS
                </span>
            </a>

            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block flex-shrink-0"></div>

            <h1 class="text-xs sm:text-base font-bold text-slate-800 dark:text-slate-100 tracking-tight truncate max-w-[130px] sm:max-w-xs md:max-w-none">@yield('page-title', 'HR Dashboard')</h1>
        </div>

        <!-- Header Right Status -->
        <div class="flex items-center gap-1.5 sm:gap-2.5 flex-shrink-0">
            <div class="hidden md:flex items-center gap-2 px-3 py-1 bg-slate-100/70 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-[11px] font-medium text-slate-600 dark:text-slate-300">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ date('l, M d, Y') }}</span>
            </div>
            <x-theme-toggle />
            <x-notification-bell />
            <a href="{{ route('portal.dashboard') }}" target="_blank" class="px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200 bg-red-50 dark:bg-red-950/50 hover:bg-red-100/80 dark:hover:bg-red-900/50 border border-red-200/60 dark:border-red-900/60 rounded-xl transition inline-flex items-center gap-1.5 shadow-2xs">
                <span class="hidden md:inline">Staff Portal</span>
                <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </header>

    <!-- Light/Dark Executive Sidebar (starts below top bar) -->
    <aside class="fixed inset-y-0 top-14 left-0 z-30 flex flex-col w-64 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-r border-slate-200/90 dark:border-slate-800 shadow-xs transition-all duration-300 ease-in-out"
           @click="if (window.innerWidth < 1024 && $event.target.closest('a')) sidebarOpen = false"
           :class="{
               'translate-x-0': sidebarOpen,
               '-translate-x-full': !sidebarOpen,
               'lg:translate-x-0': !sidebarHidden,
               'lg:-translate-x-full': sidebarHidden
           }">

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Workforce Intelligence
            </div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- HR Analytics -->
            <a href="{{ route('admin.analytics.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.analytics.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.analytics.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>HR Analytics</span>
            </a>

            <!-- Employees -->
            <a href="{{ route('admin.employees.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.employees.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.employees.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Employees</span>
            </a>

            <div class="px-3 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Operations &amp; Compensation
            </div>

            <!-- Attendance -->
            <a href="{{ route('admin.attendance.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.attendance.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.attendance.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Attendance Logs</span>
            </a>

            <!-- Time Credits -->
            <a href="{{ route('admin.time-credits.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.time-credits.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.time-credits.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Offset Time Credits</span>
            </a>

            <!-- Leaves -->
            <a href="{{ route('admin.leaves.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.leaves.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.leaves.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Leave Requests</span>
                </div>
                @php
                    $pendingLeavesTotal = \App\Models\LeaveRequest::whereIn('status', ['pending_section_head', 'pending_hr', 'pending_executive'])->count();
                @endphp
                @if($pendingLeavesTotal > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                        {{ $pendingLeavesTotal }}
                    </span>
                @endif
            </a>

            <!-- Loans Monitoring -->
            <a href="{{ route('admin.loans.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.loans.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.loans.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Loans Monitoring</span>
            </a>

            <!-- Health Assistance Fund (HMO) Monitoring -->
            <a href="{{ route('admin.haf.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.haf.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.haf.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span>Health Assistance (HMO)</span>
                </div>
                @php
                    $hafExpiringCount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('health_assistance_funds')) {
                            $todayDate = now('Asia/Manila')->startOfDay()->toDateString();
                            $thirtyOut = now('Asia/Manila')->startOfDay()->addDays(30)->toDateString();
                            $hafExpiringCount = \App\Models\HealthAssistanceFund::where('status', '!=', 'cancelled')
                                ->where('end_date', '>=', $todayDate)
                                ->where('end_date', '<=', $thirtyOut)
                                ->count();
                        }
                    } catch (\Throwable $e) {}
                @endphp
                @if($hafExpiringCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60" title="Memberships expiring within 30 days">
                        {{ $hafExpiringCount }} Expiring
                    </span>
                @endif
            </a>

            <!-- Staff Requests (Seminars & Documents) -->
            <a href="{{ route('admin.requests.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.requests.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.requests.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Staff Requests</span>
                </div>
                @php
                    $pendingRequestsTotal = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('staff_requests')) {
                            $pendingRequestsTotal = \App\Models\StaffRequest::whereIn('status', ['pending', 'report_pending'])->count();
                        }
                    } catch (\Throwable $e) {
                        $pendingRequestsTotal = 0;
                    }
                @endphp
                @if($pendingRequestsTotal > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                        {{ $pendingRequestsTotal }}
                    </span>
                @endif
            </a>

            <!-- Code of Conduct & Disciplinary Notices -->
            <a href="{{ route('admin.disciplinary.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.disciplinary.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.disciplinary.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Code of Conduct</span>
                </div>
                @php
                    $pendingNteCount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('disciplinary_notices')) {
                            $pendingNteCount = \App\Models\DisciplinaryNotice::where('requires_explanation', true)->whereNull('employee_explanation')->where('status', 'issued')->count();
                        }
                    } catch (\Throwable $e) {}
                @endphp
                @if($pendingNteCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60" title="Pending Notice of Explanation replies">
                        {{ $pendingNteCount }} NTE
                    </span>
                @endif
            </a>

            <!-- Payroll -->
            <a href="{{ route('admin.payroll.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.payroll.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.payroll.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Payroll &amp; Payslips</span>
            </a>
        </nav>

        <!-- User profile at bottom -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-red-200 dark:border-red-900/60">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="p-1.5 text-slate-400 hover:text-red-700 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-slate-700 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div class="fixed inset-0 top-14 z-20 bg-slate-900/40 backdrop-blur-xs lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen pt-14 bg-[#f8fafc] dark:bg-[#090e1a] transition-all duration-300 ease-in-out min-w-0 overflow-x-hidden" :class="sidebarHidden ? 'lg:pl-0' : 'lg:pl-64'">

        <!-- Flash Notifications -->
        <div class="px-3.5 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200/80 text-emerald-800 rounded-2xl text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200/80 text-rose-800 rounded-2xl text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200/80 text-rose-800 rounded-2xl text-xs sm:text-sm font-medium shadow-xs">
                    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
        </div>

        <!-- Page Content Body -->
        <main class="flex-1 px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 min-w-0">
            @yield('content')
        </main>
    </div>
</div>

<script>
function psgcAddressSelector(config = {}) {
    return {
        regions: [],
        provinces: [],
        cities: [],
        barangays: [],

        selectedRegion: config.region || '',
        selectedProvince: config.province || '',
        selectedCity: config.city || '',
        selectedBarangay: config.barangay || '',

        loadingRegions: false,
        loadingProvinces: false,
        loadingCities: false,
        loadingBarangays: false,

        async init() {
            this.loadingRegions = true;
            try {
                const res = await fetch('/psgc/regions');
                this.regions = await res.json();
            } catch (e) {
                console.error('Failed to load PSGC regions:', e);
            } finally {
                this.loadingRegions = false;
            }

            // If an initial region is present (edit mode or validation reload)
            if (this.selectedRegion) {
                await this.loadProvinces(this.selectedRegion, this.selectedProvince);
                if (this.selectedProvince) {
                    await this.loadCities(this.selectedProvince, this.selectedCity);
                    if (this.selectedCity) {
                        await this.loadBarangays(this.selectedCity, this.selectedBarangay);
                    }
                }
            }
        },

        async onRegionChange() {
            this.selectedProvince = '';
            this.selectedCity = '';
            this.selectedBarangay = '';
            this.provinces = [];
            this.cities = [];
            this.barangays = [];

            if (!this.selectedRegion) return;
            await this.loadProvinces(this.selectedRegion);
        },

        async loadProvinces(regionNameOrCode, preserveValue = '') {
            this.loadingProvinces = true;
            try {
                const res = await fetch('/psgc/provinces?region=' + encodeURIComponent(regionNameOrCode));
                this.provinces = await res.json();
                if (preserveValue) {
                    if (!this.provinces.some(p => p.name.toLowerCase() === preserveValue.toLowerCase())) {
                        this.provinces.unshift({ code: '', name: preserveValue });
                    }
                    this.selectedProvince = preserveValue;
                }
            } catch (e) {
                console.error('Failed to load provinces:', e);
            } finally {
                this.loadingProvinces = false;
            }
        },

        async onProvinceChange() {
            this.selectedCity = '';
            this.selectedBarangay = '';
            this.cities = [];
            this.barangays = [];

            if (!this.selectedProvince) return;
            await this.loadCities(this.selectedProvince);
        },

        async loadCities(provinceNameOrCode, preserveValue = '') {
            this.loadingCities = true;
            try {
                const res = await fetch('/psgc/cities?province=' + encodeURIComponent(provinceNameOrCode));
                this.cities = await res.json();
                if (preserveValue) {
                    if (!this.cities.some(c => c.name.toLowerCase() === preserveValue.toLowerCase())) {
                        this.cities.unshift({ code: '', name: preserveValue });
                    }
                    this.selectedCity = preserveValue;
                }
            } catch (e) {
                console.error('Failed to load cities:', e);
            } finally {
                this.loadingCities = false;
            }
        },

        async onCityChange() {
            this.selectedBarangay = '';
            this.barangays = [];

            if (!this.selectedCity) return;
            await this.loadBarangays(this.selectedCity);
        },

        async loadBarangays(cityNameOrCode, preserveValue = '') {
            this.loadingBarangays = true;
            try {
                const res = await fetch('/psgc/barangays?city=' + encodeURIComponent(cityNameOrCode));
                this.barangays = await res.json();
                if (preserveValue) {
                    if (!this.barangays.some(b => b.toLowerCase() === preserveValue.toLowerCase())) {
                        this.barangays.unshift(preserveValue);
                    }
                    this.selectedBarangay = preserveValue;
                }
            } catch (e) {
                console.error('Failed to load barangays:', e);
            } finally {
                this.loadingBarangays = false;
            }
        }
    };
}
</script>
@stack('scripts')
</body>
</html>
