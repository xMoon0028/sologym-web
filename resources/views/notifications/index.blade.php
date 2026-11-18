@php
    $layout = auth()->user()->hasRole('admin') ? 'layouts.admin' : 'layouts.portal';
    $currentFilter = $filter ?? 'all';
@endphp
@extends($layout)

@section('title', 'Notifications Center')
@section('page-title', 'Notifications Center')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Staff Notifications Center</h2>
                @if(($counts['unread'] ?? 0) > 0)
                    <span class="px-2.5 py-0.5 bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 rounded-full text-xs font-black">
                        {{ $counts['unread'] }} Unread
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Real-time updates regarding your payslips, leave applications, time credits, and attendance.
            </p>
        </div>

        @if(($counts['unread'] ?? 0) > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Mark All as Read</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs font-bold">
        <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'all' ? 'bg-red-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>All</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'all' ? 'bg-red-800 text-red-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['all'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'unread' ? 'bg-red-700 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>Unread</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'unread' ? 'bg-red-800 text-red-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['unread'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('notifications.index', ['filter' => 'payslips']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'payslips' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>💵 Payslips</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'payslips' ? 'bg-emerald-700 text-emerald-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['payslips'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('notifications.index', ['filter' => 'leaves']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'leaves' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>🏖️ Leaves</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'leaves' ? 'bg-blue-700 text-blue-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['leaves'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('notifications.index', ['filter' => 'time_credits']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'time_credits' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>⏱️ Time Credits</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'time_credits' ? 'bg-amber-700 text-amber-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['time_credits'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('notifications.index', ['filter' => 'attendance']) }}"
           class="px-3.5 py-2 rounded-xl transition whitespace-nowrap flex items-center gap-1.5 {{ $currentFilter === 'attendance' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-800' }}">
            <span>📅 Attendance</span>
            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $currentFilter === 'attendance' ? 'bg-purple-700 text-purple-100' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                {{ $counts['attendance'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Notification Feed -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <!-- Feed Subheader with count & scroll indicator -->
        <div class="px-4 sm:px-5 py-3 bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-slate-700 dark:text-slate-200">
                    @if($notifications->total() > 0)
                        Showing {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} of {{ $notifications->total() }} notifications
                    @else
                        0 notifications
                    @endif
                </span>
            </div>
            @if($notifications->count() > 3)
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                    <span>Scroll to view more</span>
                </div>
            @endif
        </div>

        <!-- Scrollable Notifications List -->
        <div class="max-h-[580px] sm:max-h-[640px] overflow-y-auto overscroll-contain divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($notifications as $notif)
                @php
                    $badge = $notif->badge_color;
                @endphp
                <div class="p-4 sm:p-5 flex items-start gap-3.5 transition hover:bg-slate-50/70 dark:hover:bg-slate-850/60 {{ !$notif->is_read ? 'bg-amber-50/25 dark:bg-amber-950/20' : '' }}">
                    <!-- Category Icon Badge -->
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 text-base font-bold border {{ $badge['bg'] }}">
                        {{ $badge['icon'] }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 {{ !$notif->is_read ? 'font-black' : '' }}">
                                    {{ $notif->title }}
                                </h3>
                                @if(!$notif->is_read)
                                    <span class="w-2 h-2 rounded-full bg-red-600 flex-shrink-0"></span>
                                @endif
                            </div>
                            <span class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 font-medium whitespace-nowrap">
                                {{ $notif->time_ago }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                            {{ $notif->message }}
                        </p>

                        <div class="mt-3 flex items-center gap-3">
                            @if($notif->link)
                                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-red-700 dark:text-red-400 hover:text-red-800 hover:underline flex items-center gap-1">
                                        <span>View Details</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </form>
                            @endif

                            @if(!$notif->is_read)
                                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-slate-300">
                                        Mark as read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mx-auto mb-3 text-xl">
                        🔔
                    </div>
                    <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">No notifications found</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                        @if($currentFilter !== 'all')
                            No updates in the "{{ ucfirst(str_replace('_', ' ', $currentFilter)) }}" category.
                        @else
                            You're all caught up! New staff alerts will appear here.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
