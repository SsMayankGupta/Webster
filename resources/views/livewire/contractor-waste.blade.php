<div>
    <div class="space-y-6">
        <!-- Log Waste Form -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Log Waste</h3>
            
            <form wire:submit="logWaste" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Weight (kg)</label>
                        <input
                            type="number"
                            wire:model.live="weightKg"
                            step="0.1"
                            min="0"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="0.0"
                        >
                        @error('weightKg')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
                        <select
                            wire:model.live="type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                        >
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Earnings (₹)</label>
                        <input
                            type="number"
                            wire:model.live="earnings"
                            step="0.01"
                            min="0"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="0.00"
                        >
                        @error('earnings')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span wire:loading>Logging...</span>
                    <span wire:loading.remove>Log Waste</span>
                </button>
            </form>
        </div>

        <!-- Waste Transactions -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Waste History</h3>
            
            @if(empty($transactions))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No waste transactions recorded yet
                </div>
            @else
                <div class="space-y-3">
                    @foreach($transactions as $transaction)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $transaction['weight_kg'] }} kg</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $transaction['type_label'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-500">{{ $transaction['date'] }}</p>
                            </div>
                            <div class="text-right">
                                @if($transaction['earnings'] > 0)
                                    <p class="font-semibold text-emerald-600">₹{{ number_format($transaction['earnings'], 2) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
