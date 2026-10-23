<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Daily operations</p>
            <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Waste management</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record food waste and track how it is reused or recovered.</p>
        </header>

        <section aria-labelledby="log-waste-heading" class="border-b border-slate-200 pb-8 dark:border-slate-800">
            <div class="mb-5">
                <h3 id="log-waste-heading" class="text-lg font-medium text-slate-900 dark:text-white">Log waste</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add the amount, its destination, and any earnings received.</p>
            </div>

            <form wire:submit="logWaste" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="waste-weight" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Weight (kg)</label>
                        <input
                            id="waste-weight"
                            type="number"
                            wire:model.live="weightKg"
                            step="0.1"
                            min="0"
                            aria-describedby="@error('weightKg') waste-weight-error @enderror"
                            @class([
                                'input',
                                'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('weightKg'),
                            ])
                            placeholder="0.0"
                        >
                        @error('weightKg')
                            <p id="waste-weight-error" class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="waste-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Destination</label>
                        <select id="waste-type" wire:model.live="type" class="input">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="waste-earnings" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Earnings (₹)</label>
                        <input
                            id="waste-earnings"
                            type="number"
                            wire:model.live="earnings"
                            step="0.01"
                            min="0"
                            aria-describedby="@error('earnings') waste-earnings-error @enderror"
                            @class([
                                'input',
                                'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('earnings'),
                            ])
                            placeholder="0.00"
                        >
                        @error('earnings')
                            <p id="waste-earnings-error" class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" wire:loading.attr="disabled" wire:target="logWaste" class="btn-primary disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="logWaste">Log waste</span>
                        <span wire:loading wire:target="logWaste">Logging…</span>
                    </button>
                </div>
            </form>
        </section>

        <section aria-labelledby="waste-history-heading">
            <div class="mb-4">
                <h3 id="waste-history-heading" class="text-lg font-medium text-slate-900 dark:text-white">Waste history</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Recent waste records and any reported earnings.</p>
            </div>

            <div class="divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                @forelse ($transactions as $transaction)
                    <article wire:key="waste-transaction-{{ $transaction['id'] }}" class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $transaction['weight_kg'] }} kg</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $transaction['type_label'] }}</p>
                            <p class="mt-1 text-xs tabular-nums text-slate-500 dark:text-slate-500">{{ $transaction['date'] }}</p>
                        </div>
                        @if ($transaction['earnings'] > 0)
                            <p class="font-medium tabular-nums text-emerald-700 dark:text-emerald-400">₹{{ number_format($transaction['earnings'], 2) }}</p>
                        @endif
                    </article>
                @empty
                    <div class="py-10 text-center">
                        <p class="text-base font-medium text-slate-800 dark:text-slate-200">No waste recorded yet</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your waste records will appear here after you log one.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
