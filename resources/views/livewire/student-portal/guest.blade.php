@php
    $features = [
        [
            'title' => 'Weekly Meal Schedule',
            'body' =>
                'See all four meals for the next seven days, with menus, macros and the exact cutoff for every sitting.',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'title' => 'Atomic Pre-Cutoff Cancellations',
            'body' =>
                'Opt out in one tap. A database-level lock freezes the headcount four hours before serving, so the kitchen never over-cooks.',
            'icon' => 'M13 10V3L4 14h7l-1 7 9-11h-7z',
        ],
        [
            'title' => 'Cryptographic 60s QR Check-In',
            'body' =>
                'A single-use ES256 token, re-signed every 60 seconds and pinned to your counter lane. Screenshots cannot be replayed.',
            'icon' =>
                'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
        ],
        [
            'title' => 'Multi-day Leave & Rebates',
            'body' =>
                'Request leave for a day or a month. Approved days post straight to an append-only rebate ledger in integer paise.',
            'icon' => 'M3 10h18M7 3v4m10-4v4M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'title' => 'Monthly Billing Statements',
            'body' =>
                'Base charge, rebates applied and the net amount due, reconciled every month from the rebate ledger.',
            'icon' =>
                'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-2.5-2.5L14 21l-2.5-2.5L9 21l-2.5-2.5L4 21V5a2 2 0 012-2h12a2 2 0 012 2v16z',
        ],
    ];
@endphp

<section class="relative overflow-hidden">
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(60%_50%_at_50%_0%,rgba(16,185,129,0.12),transparent_70%)]">
    </div>

    <div
        class="relative mx-auto grid max-w-7xl gap-12 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 lg:pb-24 lg:pt-20">
        <div>

            <h1
                class="mt-5 font-display text-4xl font-extrabold leading-[1.05] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl dark:text-white">
                Eat well.
                <span class="block text-brand-600 dark:text-brand-500">Waste nothing.</span>
            </h1>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg dark:text-slate-400">
                Zero WasteMess.io turns your hostel mess into a precise, zero-reload operation.
                Confirm meals before the four-hour cutoff, check in with a 60-second signed QR,
                and let every cancelled plate become a rebate on your bill.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <button type="button" wire:click="openAuth('login')" class="btn-primary !px-5 !py-3 text-base">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1" />
                    </svg>
                    Enter student portal
                </button>
                <a href="#features" class="btn-ghost !px-5 !py-3 text-base">
                    Explore features
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </a>
            </div>

            <dl class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-slate-200/80 pt-6 dark:border-slate-800">
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cutoff</dt>
                    <dd class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">4 hours</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">QR lifetime</dt>
                    <dd class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">60 sec</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Double scans</dt>
                    <dd class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">Zero</dd>
                </div>
            </dl>
        </div>

        <div class="relative">
            <div
                class="absolute -inset-6 rounded-[2rem] bg-gradient-to-br from-brand-500/10 via-transparent to-secondary-500/10 blur-2xl">
            </div>
            <div class="card relative overflow-hidden p-6 sm:p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">From meal choice
                            to less waste</p>
                        <h2 class="mt-1 font-display text-xl font-bold">How the process works</h2>
                    </div>
                    <span class="badge-info">4 STEPS</span>
                </div>

                <ol class="mt-7 space-y-1">
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="my-1 h-10 w-px bg-slate-200 dark:bg-slate-800"></span>
                        </div>
                        <div class="pb-2">
                            <p class="text-sm font-semibold">Students choose their meals</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Confirm what you’ll eat or opt out
                                before the cutoff</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <span class="my-1 h-10 w-px bg-slate-200 dark:bg-slate-800"></span>
                        </div>
                        <div class="pb-2">
                            <p class="text-sm font-semibold">The meal count is locked</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Bookings close 4 hours before serving
                                so the kitchen has a reliable count</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M5 10v9a2 2 0 002 2h10a2 2 0 002-2v-9M9 10V6a3 3 0 016 0v4" />
                                </svg>
                            </span>
                            <span class="my-1 h-10 w-px bg-slate-200 dark:bg-slate-800"></span>
                        </div>
                        <div class="pb-2">
                            <p class="text-sm font-semibold">The kitchen cooks to demand</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Meals are prepared for the confirmed
                                headcount, not guesswork</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </span>
                        </div>
                        <div class="pb-2">
                            <p class="text-sm font-semibold">Each meal is checked in once</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">A QR scan records the meal served and
                                prevents duplicate pickup
                            </p>
                        </div>
                    </li>
                </ol>

                <div
                    class="mt-4 rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <div class="flex items-start gap-3">
                        <span
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-brand-900 dark:text-brand-200">Less overproduction,
                                fewer wasted plates</p>
                            <p class="mt-1 text-xs leading-relaxed text-brand-800/80 dark:text-brand-200/80">Accurate
                                bookings help the kitchen prepare what students actually plan to eat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<section id="about"
    class="border-y border-slate-200/70 bg-white py-16 dark:border-slate-800 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">About the
                    platform</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">A mess that cooks
                    exactly what it serves.</h2>
                <p class="mt-4 text-slate-600 dark:text-slate-400">
                    Every meal moves through a strict state machine. Students confirm before the cutoff, the headcount
                    freezes,
                    and the kitchen cooks to the plate. Nothing is guessed, and nothing is thrown away.
                </p>
                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    The portal is a single Livewire page: signing in, confirming a meal, generating a QR and requesting
                    leave
                    all happen without a single full-page reload.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="card p-5">
                    <div class="flex items-center gap-2 text-brand-700 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="font-display font-semibold">Atomic check-in</h3>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">A unique ledger row per booking makes
                        double redemption physically impossible.</p>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-2 text-brand-700 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="font-display font-semibold">Server-clock cutoffs</h3>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">The browser clock never decides. Late
                        writes are rejected with HTTP 409.</p>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-2 text-brand-700 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <h3 class="font-display font-semibold">Real-time state</h3>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Laravel Reverb pushes OPEN → LOCKED →
                        SERVING transitions the instant they happen.</p>
                </div>
                <div class="card p-5">
                    <div class="flex items-center gap-2 text-brand-700 dark:text-brand-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4" />
                        </svg>
                        <h3 class="font-display font-semibold">Rebates, not refunds</h3>
                    </div>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Approved leave credits an append-only
                        ledger in integer paise and lowers your monthly bill.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">System
                features</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Locked until you sign in.
            </h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">
                Every card below is part of the live student dashboard. Click any locked action to open the sign-in
                panel — no reload, no redirect.
            </p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $feature)
                <article class="card-hover group relative flex flex-col p-6">
                    <div class="flex items-start justify-between gap-3">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                            </svg>
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:border-slate-800"
                            title="Sign in to access">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Locked
                        </span>
                    </div>

                    <h3 class="mt-5 font-display text-lg font-bold">{{ $feature['title'] }}</h3>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $feature['body'] }}</p>

                    <button type="button" wire:click="openAuth('login')" title="Sign in to access"
                        class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-500 opacity-70 blur-[0.3px] transition group-hover:border-brand-300 group-hover:bg-brand-50 group-hover:text-brand-700 group-hover:opacity-100 group-hover:blur-0 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400 dark:group-hover:border-brand-500/40 dark:group-hover:bg-brand-500/10 dark:group-hover:text-brand-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Sign in to access
                    </button>
                </article>
            @endforeach

            <article
                class="card flex flex-col justify-between border-dashed bg-gradient-to-br from-brand-50 to-white p-6 dark:from-brand-500/5 dark:to-slate-900">
                <div>
                    <span class="badge-success">One portal</span>
                    <h3 class="mt-4 font-display text-lg font-bold">Everything in a single Livewire page</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Sign in, switch tabs, confirm meals, refresh QR tokens and file leave without leaving the page.
                    </p>
                </div>
                <button type="button" wire:click="openAuth('login')" class="btn-primary mt-6 w-full">
                    Sign in to unlock
                </button>
            </article>
        </div>
    </div>
</section>

<section id="how-it-works"
    class="border-t border-slate-200/70 bg-white py-16 dark:border-slate-800 dark:bg-slate-900/40 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">How it
                works</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Three steps, zero reloads.
            </h2>
        </div>

        <ol class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ([['01', 'Verify your college account', 'Enter your registered college email or ID. The portal checks it live against the student database before you submit.'], ['02', 'Confirm before the cutoff', 'Opt in, opt out or set a dietary preference for any meal in the next seven days. The server clock enforces the four-hour cutoff.'], ['03', 'Scan once at your lane', 'Show the rotating signed QR at your assigned counter. The ledger accepts exactly one scan per booking.']] as [$number, $title, $body])
                <li class="card p-6">
                    <span
                        class="font-mono text-3xl font-bold text-brand-200 dark:text-brand-500/30">{{ $number }}</span>
                    <h3 class="mt-2 font-display text-lg font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            class="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-12 text-center shadow-soft dark:bg-slate-900 sm:px-12">
            <div
                class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -bottom-20 -left-10 h-64 w-64 rounded-full bg-secondary-500/20 blur-3xl">
            </div>
            <h2 class="relative font-display text-3xl font-extrabold text-white sm:text-4xl">Cook to the plate. Not to
                the bin.</h2>
            <p class="relative mx-auto mt-3 max-w-xl text-slate-300">Sign in with your college account to see today's
                menu, your lane and your rebates.</p>
            <button type="button" wire:click="openAuth('login')"
                class="btn-primary relative mt-7 !px-6 !py-3 text-base">
                Sign in to your mess account
            </button>
        </div>
    </div>
</section>

<footer class="border-t border-slate-200/70 py-8 dark:border-slate-800">
    <div
        class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8 dark:text-slate-400">
        <p>© {{ now()->year }} Zero WasteMess.io · Kamar-Taj Refectory Resource Engine</p>
        <p class="flex items-center gap-2">
            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
            Live cutoffs · Signed QR · Atomic ledger
        </p>
    </div>
</footer>
