<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" x-init="$watch('dark', value => { localStorage.setItem('theme', value ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', value); }); if (dark) document.documentElement.classList.add('dark');">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Warden Portal · Webster</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gabriela&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="relative isolate min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-white">
    <div aria-hidden="true" class="fixed inset-0 z-0 bg-cover bg-center bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=2200&q=85');">
        <div class="absolute inset-0 bg-white/85 dark:bg-slate-950/85"></div>
    </div>
    <header class="relative z-10 border-b border-white/60 bg-white/80 backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-900/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4">
            <div>
                <p class="font-ui text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Restricted workspace</p>
                <p class="mt-1 font-display text-lg font-semibold">Warden Portal</p>
            </div>
            @auth('warden')
                <form method="POST" action="{{ route('warden.logout') }}">
                    @csrf
                    <button class="rounded-lg border border-slate-200 px-4 py-2 font-ui text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">Sign out</button>
                </form>
            @endauth
        </div>
    </header>
    <main class="relative z-10 mx-auto max-w-7xl px-5 py-8 sm:py-12">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
