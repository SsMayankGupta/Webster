<div class="space-y-6">
    <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Guest services</p>
        <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Guest meal passes</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review each request and set the per-person charge before approving. Approved charges are due even if guests do not eat.</p>
    </header>

    @if ($pendingRequests === [])
        <section class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
            No pending guest pass requests.
        </section>
    @else
        <section aria-label="Pending guest pass requests" class="grid gap-4 xl:grid-cols-2">
            @foreach ($pendingRequests as $request)
                <article wire:key="guest-request-{{ $request['id'] }}" class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $request['student_name'] }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Student ID: {{ $request['student_id'] }} · requested {{ $request['created_at'] }}</p>
                        </div>
                        <span class="badge-warning">Pending approval</span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Meal</dt>
                            <dd class="mt-0.5 font-medium capitalize text-slate-900 dark:text-white">{{ $request['meal_type'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Service</dt>
                            <dd class="mt-0.5 font-medium text-slate-900 dark:text-white">{{ $request['serve_at'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 dark:text-slate-400">Relatives</dt>
                            <dd class="mt-0.5 font-medium text-slate-900 dark:text-white">{{ $request['guest_count'] }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <label for="guest-price-{{ $request['id'] }}" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Price per relative for this meal (₹)</label>
                        <input
                            id="guest-price-{{ $request['id'] }}"
                            type="number"
                            min="0"
                            step="0.01"
                            wire:model="guestPrices.{{ $request['id'] }}"
                            class="input max-w-xs"
                        >
                        @error("guestPrices.{$request['id']}")
                            <p role="alert" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="approve({{ $request['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="approve({{ $request['id'] }})"
                            class="btn-primary disabled:cursor-wait disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="approve({{ $request['id'] }})">Approve and record charge</span>
                            <span wire:loading wire:target="approve({{ $request['id'] }})">Approving…</span>
                        </button>
                        <button
                            type="button"
                            wire:click="reject({{ $request['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="reject({{ $request['id'] }})"
                            class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 disabled:opacity-60 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40"
                        >
                            <span wire:loading.remove wire:target="reject({{ $request['id'] }})">Reject</span>
                            <span wire:loading wire:target="reject({{ $request['id'] }})">Rejecting…</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
