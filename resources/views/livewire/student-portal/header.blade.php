@php
    $tabs = [
        ['meals', 'Meal Schedule', 'M4 6h16M4 12h16M4 18h10'],
        ['qr', 'QR Check-In', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
        ['leave', 'Leave & Rebates', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['profile', 'Profile', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];

    $nextCutoff = null;

    if ($isAuthenticated) {
        $nextCutoff = collect($state['days'] ?? [])
            ->flatMap(fn (array $day): array => $day['meals'])
            ->filter(fn (array $meal): bool => $meal['status'] === 'open')
            ->sortBy('cutoff_timestamp')
            ->first();
    }
@endphp

<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/85">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 md:grid md:grid-cols-[1fr_auto_1fr] lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <a
                href="{{ route('home') }}"
                wire:navigate
                aria-label="ZeroWasteMess.io home"
                class="group flex items-center gap-2.5 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[14px] bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-glow transition-transform duration-200 group-hover:scale-105">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" opacity=".55"/>
                        <circle cx="12" cy="12" r="6.2" opacity=".8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.7 14.8c1.1-3 3.5-5.2 7.2-5.5-.2 3.7-2.4 6.1-6.1 6.3m.2-.1 3.4-3.4"/>
                    </svg>
                </span>
                <span class="truncate font-display text-[17px] font-bold leading-none tracking-tight text-slate-900 dark:text-white">
                    ZeroWaste<span class="text-brand-600 dark:text-brand-400">Mess</span><span class="ml-0.5 align-top font-sans text-[10px] font-semibold tracking-wide text-slate-400">.io</span>
                </span>
            </a>

            @if ($isAuthenticated)
                <span
                    class="hidden items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold 2xl:inline-flex"
                    :class="{
                        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300': status.tone === 'emerald',
                        'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-300': status.tone === 'amber',
                        'border-slate-200 bg-slate-100 text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400': status.tone === 'slate'
                    }"
                    :title="'WebSocket: ' + status.label"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full"
                        :class="{
                            'bg-emerald-500': status.tone === 'emerald',
                            'animate-pulse bg-amber-500': status.tone === 'amber',
                            'bg-slate-400': status.tone === 'slate'
                        }"
                    ></span>
                    <span x-text="status.label">Offline</span>
                </span>
            @endif
        </div>

        @if ($isAuthenticated)
            <nav class="hidden min-w-0 items-center justify-center lg:flex" aria-label="Student portal sections">
                <div class="flex items-center gap-0.5 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-0.5 xl:gap-1 xl:p-1 dark:border-slate-800 dark:bg-slate-900/60">
                    @foreach ($tabs as [$key, $label, $icon])
                        <button
                            type="button"
                            wire:click="setTab('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 text-[13px] font-semibold transition xl:px-3 xl:text-sm {{ $activeTab === $key ? 'bg-white text-brand-700 shadow-sm dark:bg-slate-800 dark:text-brand-300' : 'text-slate-600 hover:bg-white/70 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-white' }}"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </nav>
        @else
            <nav class="hidden min-w-0 items-center justify-center md:flex" aria-label="Portal information">
                <div class="flex items-center gap-0.5 rounded-full border border-slate-200/80 bg-slate-50/80 p-0.5 lg:gap-1 lg:p-1 dark:border-slate-800 dark:bg-slate-900/60">
                    <a href="#about" class="rounded-full px-2.5 py-1.5 text-[13px] font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 hover:shadow-sm lg:px-4 lg:py-2 lg:text-sm dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">About</a>
                    <a href="#features" class="rounded-full px-2.5 py-1.5 text-[13px] font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 hover:shadow-sm lg:px-4 lg:py-2 lg:text-sm dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">Features</a>
                    <a href="#how-it-works" class="rounded-full px-2.5 py-1.5 text-[13px] font-semibold text-slate-600 transition hover:bg-white hover:text-slate-900 hover:shadow-sm lg:px-4 lg:py-2 lg:text-sm dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">How it works</a>
                </div>
            </nav>
        @endif

        <div class="flex shrink-0 items-center justify-self-end gap-2">
            @if ($isAuthenticated && $nextCutoff)
                <div
                    x-data="mealClock({{ $nextCutoff['cutoff_timestamp'] }}, {{ $nextCutoff['serve_timestamp'] }}, @js($state['server_now']))"
                    class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 xl:flex dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                >
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                    </span>
                    <span class="font-mono text-[13px] tabular-nums text-slate-900 dark:text-white" x-text="short"></span>
                    <span class="text-slate-400">to {{ $nextCutoff['meal_label'] }} cutoff</span>
                </div>
            @endif

            <button
                type="button"
                @click="dark = !dark"
                class="btn-ghost !px-2.5 !py-2"
                aria-label="Toggle colour theme"
            >
                <svg x-show="!dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="dark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>

            @if ($isAuthenticated)
                <div class="hidden text-right 2xl:block">
                    <div class="text-sm font-semibold leading-tight">{{ $state['student']['name'] }}</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $state['student']['college_id'] ?? $state['student']['email'] }}</div>
                </div>
                <button
                    type="button"
                    wire:click="logout"
                    wire:confirm="Sign out of the student portal?"
                    class="btn-ghost !px-2.5 !py-2"
                    aria-label="Sign out"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            @else
                <button type="button" wire:click="openAuth('login')" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"/>
                    </svg>
                    Sign in
                </button>
            @endif
        </div>
    </div>

    @if ($isAuthenticated)
        <div class="border-t border-slate-200/80 lg:hidden dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-3 py-2 text-xs scrollbar-hide">
                @foreach ($tabs as [$key, $label, $icon])
                    <button
                        type="button"
                        wire:click="setTab('{{ $key }}')"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 font-semibold transition {{ $activeTab === $key ? 'bg-brand-600 text-white shadow-glow' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900' }}"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</header>
