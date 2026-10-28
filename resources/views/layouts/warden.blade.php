<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" x-init="$watch('dark', value => { localStorage.setItem('theme', value ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', value); }); if (dark) document.documentElement.classList.add('dark');">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warden Portal · Webster</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-white">
    <header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Restricted workspace</p>
                <p class="mt-1 text-lg font-semibold">Warden Portal</p>
            </div>
            @auth('warden')
                <form method="POST" action="{{ route('warden.logout') }}">
                    @csrf
                    <button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Sign out</button>
                </form>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-8 sm:py-12">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
