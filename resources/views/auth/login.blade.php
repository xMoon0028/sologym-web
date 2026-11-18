<x-guest-layout>
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none p-5 sm:p-8 md:p-10 transition-all"
         x-data="{ 
             showPassword: false,
             showPasswordForm: {{ $errors->any() ? 'true' : 'false' }} 
         }">

        <!-- 60th Anniversary Emblem & Brand Header -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="relative mb-4 group">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-b from-white to-slate-50 dark:from-slate-800 dark:to-slate-800 border border-slate-200/80 dark:border-slate-700 p-2 shadow-sm flex items-center justify-center transition group-hover:scale-105 duration-300">
                    <img src="{{ asset('images/lasac_60th.png') }}" alt="LASAC 60th Anniversary (1967-2027)" class="w-full h-full object-contain">
                </div>
                <span class="absolute -bottom-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 bg-red-700 text-white text-[10px] font-bold tracking-wider uppercase rounded-full shadow-sm whitespace-nowrap">
                    60 Years
                </span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 mt-2">
                LASAC HRIS
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                Lipa Archdiocesan Social Action Commission
            </p>
            <p class="text-[11px] text-red-700 dark:text-red-400 font-semibold tracking-wide uppercase mt-0.5">
                Building Communities, Together • 1967–2027
            </p>
        </div>

        <!-- Session Status & Alerts -->
        <x-auth-session-status class="mb-4" :status="session('status')" />
        @if(session('error'))
            <div class="mb-5 p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200/90 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 rounded-2xl text-xs font-semibold flex items-start gap-2.5 shadow-sm">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1 leading-relaxed">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Google Sign-In Button (Primary) -->
        <div>
            <a href="{{ route('auth.google') }}"
               class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 text-slate-800 dark:text-slate-200 font-bold text-sm rounded-2xl border-2 border-slate-200/90 dark:border-slate-700 hover:border-red-500 dark:hover:border-red-500 shadow-sm hover:shadow-md transition-all group">
                <svg class="w-5 h-5 flex-shrink-0 transition group-hover:scale-105" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Sign in with Google Work Account</span>
            </a>
        </div>

        <!-- Divider with Toggle for Password Login -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200 dark:border-slate-800"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <button type="button" 
                        @click="showPasswordForm = !showPasswordForm" 
                        class="px-3 bg-white dark:bg-slate-900 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 font-semibold transition flex items-center gap-1.5">
                    <span>Or sign in with password</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showPasswordForm ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>

        <!-- Password Sign-In Form -->
        <div x-show="showPasswordForm" x-transition x-cloak>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Work Email Address
                    </label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="username" 
                           placeholder="yourname@lasaclipa.org"
                           class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 px-3.5 py-2.5 focus:bg-white focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                    @error('email')
                        <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-red-700 dark:text-red-400 hover:underline">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <input id="password" 
                               :type="showPassword ? 'text' : 'password'" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 pl-3.5 pr-10 py-2.5 focus:bg-white focus:ring-2 focus:ring-red-600/20 focus:border-red-600 transition">
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember" 
                               class="rounded border-slate-300 dark:border-slate-700 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Remember this device</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        Sign In with Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer note -->
    <div class="mt-6 text-center text-xs text-slate-400 dark:text-slate-500 space-y-1">
        <div>&copy; 1967–{{ date('Y') }} LASAC. All rights reserved.</div>
        <div class="text-[11px]">
            Developed by <a href="https://xmoon0028.github.io/" target="_blank" rel="noopener noreferrer" class="font-medium text-red-600 dark:text-red-400 hover:underline">MoonDev</a>
        </div>
    </div>
</x-guest-layout>
