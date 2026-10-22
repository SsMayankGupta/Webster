<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZeroWasteMess.io - Sustainable Dining Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto flex min-h-[80vh] max-w-5xl flex-col items-center justify-center gap-8 text-center">
            <div>
                <span class="badge-success">Sustainable Dining Platform</span>
                <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    ZeroWasteMess.io
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600 dark:text-slate-400 sm:text-lg">
                    Kamar-Taj Refectory Resource Engine — eliminating food waste with secure QR check-ins, smart cutoffs, and real-time analytics.
                </p>
            </div>

            <div class="grid w-full gap-4 sm:grid-cols-2">
                <a href="/" class="card-hover flex items-center gap-4 p-5 text-left">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <div>
                        <div class="font-display font-semibold">Student Portal</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Book meals, scan QR, track health</div>
                    </div>
                </a>
                <a href="{{ route('contractor.login') }}" class="card-hover flex items-center gap-4 p-5 text-left">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-white dark:bg-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div>
                        <div class="font-display font-semibold">Contractor Portal</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Manage menus, headcount, waste</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
