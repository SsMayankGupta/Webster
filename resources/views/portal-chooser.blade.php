<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#059669">
    <title>Webster · Choose your portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-white">
    <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col justify-center px-5 py-14">
        <header class="mb-12 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-brand-700 dark:text-brand-400">Webster</p>
            <h1 class="mt-4 font-display text-4xl font-bold tracking-tight sm:text-5xl">Choose your portal</h1>
            <p class="mx-auto mt-4 max-w-xl text-slate-600 dark:text-slate-300">Select the workspace that matches your role.</p>
        </header>
        @if (session('status'))
            <p role="status" class="mb-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200">{{ session('status') }}</p>
        @endif
        <div class="grid gap-5 md:grid-cols-2">
            <a href="{{ config('portals.contractor_url') ?: route('contractor.login') }}" class="group rounded-2xl border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-700">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300" aria-hidden="true">C</span>
                <h2 class="mt-6 text-2xl font-semibold">Contractor</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Manage hostel meals, review student access, and run your contractor workspace.</p>
                <span class="mt-6 inline-flex items-center gap-2 font-semibold text-brand-700 group-hover:gap-3 dark:text-brand-400">Open contractor portal <span aria-hidden="true">→</span></span>
            </a>
            <a href="{{ config('portals.student_url') ?: route('student.portal') }}" class="group rounded-2xl border border-slate-200 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-700">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300" aria-hidden="true">S</span>
                <h2 class="mt-6 text-2xl font-semibold">Student</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">View your mess schedule, manage bookings, and access your student account.</p>
                <span class="mt-6 inline-flex items-center gap-2 font-semibold text-brand-700 group-hover:gap-3 dark:text-brand-400">Open student portal <span aria-hidden="true">→</span></span>
            </a>
        </div>
        <p class="mt-10 text-center text-xs text-slate-500 dark:text-slate-400">Warden access is provisioned separately and is not listed here.</p>
    </main>
</body>
</html>
