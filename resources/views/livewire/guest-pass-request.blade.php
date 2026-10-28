<div class="space-y-6">
    <header>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700 dark:text-brand-400">Visitors</p>
        <h2 class="mt-2 font-lora text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Guest meal passes</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose one or more upcoming meals and the number of relatives for each. The contractor sets the per-person price when approving each request.</p>
    </header>

    <section class="card p-5 sm:p-6" aria-labelledby="guest-pass-request-heading">
        <h3 id="guest-pass-request-heading" class="font-lora text-lg font-semibold">Request passes</h3>

        @if ($errorMessage)
            <p role="alert" class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">{{ $errorMessage }}</p>
        @endif
        @error('selectedMenus')
            <p role="alert" class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">{{ $message }}</p>
        @enderror
        @error('guestCounts')
            <p role="alert" class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300">{{ $message }}</p>
        @enderror

        <form wire:submit="submit" class="mt-4 space-y-4">
            @if ($menus->isNotEmpty())
                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                    @foreach ($menus as $menu)
                        <div wire:key="guest-menu-{{ $menu->id }}" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <label for="guest-menu-{{ $menu->id }}-select" class="flex min-w-0 items-start gap-3">
                                <input
                                    id="guest-menu-{{ $menu->id }}-select"
                                    type="checkbox"
                                    wire:model="selectedMenus"
                                    value="{{ $menu->id }}"
                                    class="mt-1 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                >
                                <span>
                                    <span class="block text-sm font-semibold capitalize text-slate-900 dark:text-white">{{ $menu->meal_type }}</span>
                                    <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $menu->serve_at->timezone(config('mess.display_timezone'))->format('D, d M g:i A') }} · request closes {{ $menu->cutoffAt()->timezone(config('mess.display_timezone'))->format('g:i A') }}</span>
                                </span>
                            </label>

                            <div class="flex items-center gap-3 sm:pl-8">
                                <label for="guest-count-{{ $menu->id }}" class="text-xs font-medium text-slate-600 dark:text-slate-400">Relatives</label>
                                <input
                                    id="guest-count-{{ $menu->id }}"
                                    type="number"
                                    min="1"
                                    max="5"
                                    wire:model="guestCounts.{{ $menu->id }}"
                                    class="input w-24"
                                >
                                @error("guestCounts.$menu->id")
                                    <p role="alert" class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400">The displayed default estimate is ₹{{ number_format($estimatePerGuestPaise / 100, 2) }} per person and meal. Final charges are set by the contractor at approval and are due whether or not guests eat.</p>

                <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="btn-primary disabled:cursor-wait disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">Submit selected meal requests</span>
                    <span wire:loading wire:target="submit">Submitting requests…</span>
                </button>
            @else
                <p class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400">There are no upcoming meals currently accepting guest requests.</p>
            @endif
        </form>
    </section>

    <section class="card overflow-hidden" aria-labelledby="guest-pass-history-heading">
        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 id="guest-pass-history-heading" class="font-lora text-lg font-semibold">Your guest passes</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Approved group passes have one QR code for the whole party. Each approved pass is charged at the price set by the contractor.</p>
        </div>

        @if ($guestPasses === [])
            <p class="p-5 text-sm text-slate-500 dark:text-slate-400">You have not requested any guest meal passes.</p>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($guestPasses as $pass)
                    <article wire:key="guest-pass-{{ $pass['id'] }}" class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_auto]">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold capitalize text-slate-900 dark:text-white">{{ $pass['meal_type'] }} · {{ $pass['serve_at'] }}</h4>
                                @if ($pass['status'] === 'booked')
                                    <span class="badge-success">Approved</span>
                                @elseif ($pass['status'] === 'pending_approval')
                                    <span class="badge-warning">Pending approval</span>
                                @else
                                    <span class="badge-info">{{ str_replace('_', ' ', $pass['status']) }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $pass['guest_count'] }} relative(s)</p>
                            @if ($pass['charge_paise'] !== null)
                                <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-300">Extra-meal charge: ₹{{ number_format($pass['charge_paise'] / 100, 2) }} · payable at semester end or when leaving</p>
                            @elseif ($pass['status'] === 'pending_approval')
                                <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">The contractor will set the amount when approving this request.</p>
                            @endif

                            @if (isset($guestPassErrors[$pass['id']]))
                                <p role="alert" class="mt-3 text-sm text-rose-700 dark:text-rose-300">{{ $guestPassErrors[$pass['id']] }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col items-start gap-3 lg:items-end">
                            @if ($pass['status'] === 'booked')
                                <button
                                    type="button"
                                    wire:click="refreshPassToken({{ $pass['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="refreshPassToken({{ $pass['id'] }})"
                                    class="btn-secondary disabled:cursor-wait disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="refreshPassToken({{ $pass['id'] }})">Show / refresh group QR</span>
                                    <span wire:loading wire:target="refreshPassToken({{ $pass['id'] }})">Preparing QR…</span>
                                </button>
                            @endif

                            @if (! empty($guestPassTokens[$pass['id']]['svg'] ?? null))
                                <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700" wire:key="guest-qr-{{ $pass['id'] }}-{{ $guestPassTokens[$pass['id']]['expires_at'] }}">
                                    {!! $guestPassTokens[$pass['id']]['svg'] !!}
                                    <p class="mt-2 text-center text-xs text-slate-500 dark:text-slate-400">Lane {{ $guestPassTokens[$pass['id']]['lane'] ?? 'pending' }} · expires {{ \Carbon\Carbon::parse($guestPassTokens[$pass['id']]['expires_at'])->format('g:i:s A') }}</p>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
