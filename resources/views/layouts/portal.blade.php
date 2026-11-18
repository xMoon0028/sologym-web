<!DOCTYPE html>
<html lang="en" class="min-h-full bg-slate-50 dark:bg-[#090e1a]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LASAC HRIS') — Employee Portal</title>
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
<div class="flex min-h-screen bg-[#f8fafc] dark:bg-[#090e1a]" x-data="{ sidebarOpen: false }">

    <!-- Light/Dark Executive Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border-r border-slate-200/90 dark:border-slate-800 shadow-sm transition-transform duration-200"
           @click="if (window.innerWidth < 1024 && $event.target.closest('a')) sidebarOpen = false"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo Header -->
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/lasac_logo.png') }}" alt="LASAC" class="h-8 w-auto object-contain">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                    PORTAL
                </span>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Self-Service Suite
            </div>

            <!-- Dashboard -->
            <a href="{{ route('portal.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.dashboard') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.dashboard') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>

            <!-- 1. My Leaves (Priority 1) -->
            <a href="{{ route('portal.leaves.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.leaves.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.leaves.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Leaves</span>
            </a>

            <!-- Requests (Seminars & Documents) -->
            <a href="{{ route('portal.requests.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.requests.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('portal.requests.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Requests (Seminars &amp; Docs)</span>
                </div>
                @php
                    $pendingReportCount = 0;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('staff_requests') && auth()->user()->employee) {
                            $pendingReportCount = \App\Models\StaffRequest::where('employee_id', auth()->user()->employee->id)
                                ->where('status', 'approved')
                                ->where('type', 'seminar')
                                ->whereNull('report_submitted_at')
                                ->count();
                        }
                    } catch (\Throwable $e) {
                        $pendingReportCount = 0;
                    }
                @endphp
                @if($pendingReportCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60" title="Seminar Report Required">
                        Report Due
                    </span>
                @endif
            </a>

            <!-- 2. Time Credits / Offset (Priority 2) -->
            <a href="{{ route('portal.time-credits.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.time-credits.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.time-credits.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Offset (Time Credits)</span>
            </a>

            <!-- 3. Attendance Logs (Priority 3) -->
            <a href="{{ route('portal.attendance.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.attendance.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.attendance.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Attendance Logs</span>
            </a>

            <!-- My Payslips -->
            <a href="{{ route('portal.payslips.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.payslips.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.payslips.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>My Payslips</span>
            </a>

            <!-- My Profile -->
            <a href="{{ route('portal.profile') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.profile') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.profile') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>My Profile Record</span>
            </a>

            <!-- Health Assistance Fund (HMO) -->
            <a href="{{ route('portal.haf.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.haf.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('portal.haf.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span>Health Assistance (HMO)</span>
                </div>
                @php
                    $userHafExpiring = false;
                    try {
                        if (auth()->check() && auth()->user()->employee && \Illuminate\Support\Facades\Schema::hasTable('health_assistance_funds')) {
                            $userFund = \App\Models\HealthAssistanceFund::where('employee_id', auth()->user()->employee->id)
                                ->where('status', '!=', 'cancelled')
                                ->where('end_date', '>=', now('Asia/Manila')->startOfDay()->toDateString())
                                ->where('end_date', '<=', now('Asia/Manila')->startOfDay()->addDays(30)->toDateString())
                                ->first();
                            $userHafExpiring = $userFund !== null;
                        }
                    } catch (\Throwable $e) {}
                @endphp
                @if($userHafExpiring)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950 dark:text-amber-300 animate-pulse" title="Coverage expiring within 30 days">
                        Expiring Soon
                    </span>
                @endif
            </a>

            <!-- Code of Conduct Notices -->
            <a href="{{ route('portal.conduct.index') }}"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('portal.conduct.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 {{ request()->routeIs('portal.conduct.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Conduct &amp; Notices</span>
                </div>
                @php
                    $empPendingNte = 0;
                    try {
                        if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('disciplinary_notices')) {
                            $userEmp = auth()->user()->employee;
                            if ($userEmp) {
                                $empPendingNte = \App\Models\DisciplinaryNotice::where('employee_id', $userEmp->id)
                                    ->where('requires_explanation', true)
                                    ->whereNull('scanned_nte_path')
                                    ->whereNull('nte_received_at')
                                    ->whereNotIn('status', ['resolved', 'closed'])
                                    ->count();
                            }
                        }
                    } catch (\Throwable $e) {}
                @endphp
                @if($empPendingNte > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950 dark:text-amber-300">
                        NTE Due
                    </span>
                @endif
            </a>

            <div class="pt-3 px-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Other Records
            </div>

            <!-- My Loans (Secondary) -->
            <a href="{{ route('portal.loans.index') }}"
               class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('portal.loans.*') ? 'bg-red-50 dark:bg-red-950/50 text-red-800 dark:text-red-300 border border-red-200/80 dark:border-red-900/60 shadow-2xs font-bold' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/80 hover:text-red-700 dark:hover:text-red-400' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('portal.loans.*') ? 'text-red-700 dark:text-red-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Loan Publish</span>
            </a>
        </nav>

        <!-- User profile at bottom -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-emerald-200 dark:border-emerald-800/60">
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

    <div class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

    <div class="flex-1 flex flex-col lg:pl-64 min-h-screen bg-[#f8fafc] dark:bg-[#090e1a] min-w-0 overflow-x-hidden">
        <header class="glass-panel border-b border-slate-200/80 dark:border-slate-800 px-3 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md transition-colors">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <button class="lg:hidden p-2 -ml-1 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex-shrink-0 min-h-[38px] min-w-[38px] flex items-center justify-center" @click="sidebarOpen = !sidebarOpen" title="Toggle Navigation Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xs sm:text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight truncate max-w-[130px] sm:max-w-xs md:max-w-none">@yield('page-title', 'My Portal')</h1>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0">
                <div class="hidden md:flex items-center gap-2 px-3 py-1 bg-slate-100/70 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 rounded-full text-[11px] font-medium text-slate-600 dark:text-slate-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ date('l, M d, Y') }}</span>
                </div>
                <x-theme-toggle />
                <x-notification-bell />
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 hover:text-red-800 dark:hover:text-red-200 bg-red-50 dark:bg-red-950/50 hover:bg-red-100/80 dark:hover:bg-red-900/50 border border-red-200/60 dark:border-red-900/60 rounded-xl transition inline-flex items-center gap-1.5 shadow-2xs">
                        <span class="hidden md:inline">Admin Console</span>
                        <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>
        </header>

        <div class="px-3.5 sm:px-6 lg:px-8 pt-4">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
        </div>

        <main class="flex-1 px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 min-w-0">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
