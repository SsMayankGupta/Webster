<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#064e3b">
    <title>Webster · Choose your portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gabriela&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-white antialiased">
    <main class="relative isolate flex min-h-screen items-center overflow-hidden px-5 py-14 sm:px-8"
        style="background-image: linear-gradient(110deg, rgba(5, 28, 23, .86), rgba(5, 28, 23, .55)), url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=2200&q=85'); background-size: cover; background-position: center;">
        <div class="mx-auto w-full max-w-6xl">
            <header class="mb-12 max-w-2xl">
                <a href="/" class="inline-flex items-center gap-3 font-ui text-sm font-semibold tracking-wide text-white">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-400 text-lg font-bold text-emerald-950 shadow-lg">W</span>
                    WEBSTER
                </a>
                <p class="mt-10 font-ui text-sm font-semibold uppercase tracking-[0.24em] text-emerald-200">One campus, better connected</p>
                <h1 class="mt-4 font-display text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">Choose your portal</h1>
                <p class="mt-4 max-w-xl leading-7 text-white/75">Select the workspace that matches your role and get straight to what matters.</p>
            </header>

            @if (session('status'))
                <p role="status" class="mb-6 rounded-xl border border-amber-200/30 bg-amber-950/60 px-4 py-3 text-sm text-amber-100 backdrop-blur">{{ session('status') }}</p>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <a href="{{ config('portals.contractor_url') ?: route('contractor.login') }}"
                    class="group relative isolate flex min-h-72 flex-col justify-end overflow-hidden rounded-3xl border border-white/20 p-7 shadow-xl transition duration-300 hover:-translate-y-1 hover:shadow-2xl sm:p-9"
                    style="background-image: linear-gradient(0deg, rgba(8, 24, 20, .92), rgba(8, 24, 20, .15)), url('https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=85'); background-size: cover; background-position: center;">
                    <span class="absolute right-7 top-7 flex h-12 w-12 items-center justify-center rounded-2xl border border-white/30 bg-white/15 text-xl font-bold backdrop-blur" aria-hidden="true">C</span>
                    <p class="font-ui text-sm font-semibold uppercase tracking-[0.2em] text-emerald-200">Operations</p>
                    <h2 class="mt-2 font-display text-3xl font-bold">Contractor</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-white/80">Manage hostel meals, review student access, and run your contractor workspace.</p>
                    <span class="mt-6 inline-flex items-center gap-2 font-ui font-semibold text-white transition group-hover:gap-3">Open contractor portal <span aria-hidden="true">→</span></span>
                </a>

                <a href="{{ config('portals.student_url') ?: route('student.portal') }}"
                    class="group relative isolate flex min-h-72 flex-col justify-end overflow-hidden rounded-3xl border border-white/20 p-7 shadow-xl transition duration-300 hover:-translate-y-1 hover:shadow-2xl sm:p-9"
                    style="background-image: linear-gradient(0deg, rgba(8, 24, 20, .92), rgba(8, 24, 20, .12)), url('https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=1200&q=85'); background-size: cover; background-position: center;">
                    <span class="absolute right-7 top-7 flex h-12 w-12 items-center justify-center rounded-2xl border border-white/30 bg-white/15 text-xl font-bold backdrop-blur" aria-hidden="true">S</span>
                    <p class="font-ui text-sm font-semibold uppercase tracking-[0.2em] text-emerald-200">Campus life</p>
                    <h2 class="mt-2 font-display text-3xl font-bold">Student</h2>
                    <p class="mt-2 max-w-md text-sm leading-6 text-white/80">View your mess schedule, manage bookings, and access your student account.</p>
                    <span class="mt-6 inline-flex items-center gap-2 font-ui font-semibold text-white transition group-hover:gap-3">Open student portal <span aria-hidden="true">→</span></span>
                </a>
            </div>
            <p class="mt-8 text-center text-sm text-white/70">Warden access is provisioned separately and is not listed here.</p>
        </div>
    </main>
</body>
</html>
