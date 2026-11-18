<div class="relative" x-data="{
    open: false,
    unreadCount: {{ auth()->user()->unreadNotificationsCount() }},
    notifications: [],
    loading: false,
    fetchNotifications() {
        this.loading = true;
        fetch('{{ route('notifications.index') }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            this.notifications = data.notifications;
            this.unreadCount = data.unreadCount;
            this.loading = false;
        })
        .catch(() => { this.loading = false; });
    },
    markAllRead() {
        fetch('{{ route('notifications.read-all') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(() => {
            this.unreadCount = 0;
            this.notifications.forEach(n => n.is_read = true);
        });
    }
}" @click.outside="open = false">

    <!-- Bell Icon Button -->
    <button type="button" @click="open = !open; if(open) fetchNotifications()"
            class="relative p-2 text-slate-500 dark:text-slate-400 hover:text-red-700 dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition focus:outline-hidden"
            title="Notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <template x-if="unreadCount > 0">
            <span class="absolute top-1 right-1 min-w-4 h-4 px-1 bg-red-600 text-white rounded-full text-[9px] font-black flex items-center justify-center ring-2 ring-white dark:ring-slate-900"
                  x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
        </template>
    </button>

    <!-- Notification Dropdown Panel -->
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-[calc(100vw-1.5rem)] max-w-xs sm:max-w-sm sm:w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200/90 dark:border-slate-800 z-50 overflow-hidden">

        <!-- Header -->
        <div class="px-4 py-3 bg-slate-50/90 dark:bg-slate-800/90 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Notifications</span>
                <template x-if="unreadCount > 0">
                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 rounded-full text-[10px] font-extrabold" x-text="unreadCount + ' new'"></span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="unreadCount > 0">
                    <button type="button" @click="markAllRead()"
                            class="text-[11px] font-semibold text-red-700 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition">
                        Mark all read
                    </button>
                </template>
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-[380px] sm:max-h-[420px] overflow-y-auto overscroll-contain divide-y divide-slate-100 dark:divide-slate-800 focus:outline-hidden">
            <template x-if="loading">
                <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">
                    <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading notifications...
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                        🔔
                    </div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">No notifications yet</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Staff alerts for payslips, leaves, time credits, and attendance will show here.</p>
                </div>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <form method="POST" :action="'{{ url('notifications') }}/' + item.id + '/read'">
                    @csrf
                    <button type="submit"
                            class="w-full text-left p-3 hover:bg-slate-50/80 dark:hover:bg-slate-850 transition flex items-start gap-3 relative group"
                            :class="!item.is_read ? 'bg-amber-50/30 dark:bg-amber-950/20' : ''">
                        <!-- Category Icon Badge -->
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5 border"
                             :class="item.badge_color ? item.badge_color.bg : 'bg-slate-100 text-slate-700 border-slate-200'">
                            <span x-text="item.badge_color ? item.badge_color.icon : '🔔'"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate"
                                   :class="!item.is_read ? 'font-black' : ''"
                                   x-text="item.title"></p>
                                <span class="text-[9px] text-slate-400 whitespace-nowrap" x-text="item.time_ago"></span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 leading-snug line-clamp-2" x-text="item.message"></p>
                        </div>

                        <!-- Unread Dot -->
                        <span class="w-2 h-2 rounded-full bg-red-600 flex-shrink-0 mt-1" x-show="!item.is_read"></span>
                    </button>
                </form>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-2.5 px-4 bg-slate-50/90 dark:bg-slate-800/90 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <template x-if="notifications.length > 3">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium flex items-center gap-1">
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    Scroll for more
                </span>
            </template>
            <template x-if="notifications.length <= 3">
                <span></span>
            </template>
            <a href="{{ route('notifications.index') }}"
               class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-red-700 dark:hover:text-red-400 transition inline-flex items-center gap-1.5 ml-auto">
                <span>Notifications Center</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
