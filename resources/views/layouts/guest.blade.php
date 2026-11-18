<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-slate-50 dark:bg-[#090e1a]">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LASAC HRIS') }} - Login</title>
        <link rel="icon" type="image/png" href="{{ asset('images/tab_logo.png') }}">
        <!-- Optimized High-Legibility Typography: Inter & JetBrains Mono with Preconnect -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts & Styles -->
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
        </style>
    </head>
    <body class="min-h-full font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f8fafc] dark:bg-[#090e1a] selection:bg-red-500 selection:text-white">
        <div class="min-h-screen flex flex-col justify-center items-center py-6 sm:py-12 px-3 sm:px-6 lg:px-8 relative overflow-hidden bg-[#f8fafc] dark:bg-[#090e1a]">
            <!-- Subtle background ambient gradient -->
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-red-100 dark:bg-red-950/30 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-rose-100 dark:bg-rose-950/30 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

            <div class="w-full max-w-[440px] relative z-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
