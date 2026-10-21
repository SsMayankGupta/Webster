@php
    $booking = $state['active_booking'];
@endphp

<div wire:poll.{{ $state['qr']['refresh_seconds'] }}s="refreshQrToken">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-display text-xl font-extrabold tracking-tight">Signed QR check-in</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                A fresh ES256 token every {{ $state['qr']['refresh_seconds'] }} seconds. Valid for {{ $state['qr']['ttl_seconds'] }} seconds, pinned to your lane.
            </p>
        </div>
        <button type="button" wire:click="refreshQrToken" class="btn-secondary" wire:loading.attr="disabled" wire:target="refreshQrToken">
            <svg wire:loading wire:target="refreshQrToken" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="refreshQrToken">Refresh token</span>
            <span wire:loading wire:target="refreshQrToken">Signing…</span>
        </button>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active booking</p>
                    <p class="mt-1 font-display text-lg font-bold">
                        @if ($booking)
                            {{ ucfirst($booking['meal_type']) }} · {{ $booking['serve_at_label'] }}
                        @else
                            No active booking
                        @endif
                    </p>
                </div>
                @if ($booking)
                    <span class="badge-info">Lane {{ $booking['lane'] ?? 'pending' }}</span>
                @endif
            </div>

            <div class="p-5">
                @if ($qrSvg)
                    <div class="mx-auto flex max-w-[300px] flex-col items-center">
                        <div class="relative rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700">
                            <div class="h-[260px] w-[260px] [&>svg]:h-full [&>svg]:w-full" wire:key="qr-{{ $qrExpiresAt }}">
                                {!! $qrSvg !!}
                            </div>
                            <div wire:loading.flex wire:target="refreshQrToken" class="absolute inset-0 items-center justify-center rounded-2xl bg-white/80 dark:bg-slate-900/80">
                                <svg class="h-6 w-6 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </div>

                        <div
                            x-data="qrClock(@js($qrExpiresAt), @js($state['server_now']))"
                            x-init="init()"
                            class="mt-4 flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Expires in</p>
                                <p class="font-mono text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white" x-text="label">00:00</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Lane</p>
                                <p class="font-display text-2xl font-extrabold text-brand-600 dark:text-brand-400">{{ $qrLane ?? '—' }}</p>
                            </div>
                        </div>

                        <p class="mt-3 text-center text-[11px] text-slate-400">
                            The token is single-use. Do not screenshot or share it — a fresh one is generated automatically.
                        </p>

                        <button
                            type="button"
                            class="btn-ghost mt-2 !text-xs"
                            x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText(@js($qrToken)).then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                        >
                            <span x-text="copied ? 'Token copied' : 'Copy raw token'">Copy raw token</span>
                        </button>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <p class="mt-4 font-display text-lg font-bold">Check-in is not open yet</p>
                        <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                            {{ $qrError ?? 'Confirm an upcoming meal to generate your signed check-in QR.' }}
                        </p>
                        <button type="button" wire:click="setTab('meals')" class="btn-primary mt-5">
                            Review meal schedule
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="font-display text-base font-bold">How the token is protected</h3>
                <ul class="mt-3 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span><strong class="text-slate-800 dark:text-slate-200">ES256 signature</strong> — scanners verify with the public key, even offline.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong class="text-slate-800 dark:text-slate-200">60-second TTL</strong> — the portal rotates the token every {{ $state['qr']['refresh_seconds'] }} seconds so the queue never waits.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span><strong class="text-slate-800 dark:text-slate-200">Lane pinning</strong> — the token is bound to the counter assigned when the meal locked.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span><strong class="text-slate-800 dark:text-slate-200">Single-use ledger</strong> — a UNIQUE booking index means exactly one scan wins a race.</span>
                    </li>
                </ul>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="font-display text-base font-bold">Scanner public key</h3>
                    <a href="{{ $state['qr']['public_key_url'] }}" target="_blank" rel="noopener" class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300">
                        Download PEM
                    </a>
                </div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Scanner devices import this P-256 public key with the Web Crypto API and verify each QR locally.
                </p>
                <p class="mt-3 break-all rounded-lg bg-slate-100 px-3 py-2 font-mono text-[11px] text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    SHA-256 · {{ $state['qr']['fingerprint'] }}
                </p>
                <p class="mt-2 text-[11px] text-slate-400">Algorithm: {{ $state['qr']['algorithm'] }} · Curve: P-256</p>
            </div>
        </div>
    </div>
</div>
