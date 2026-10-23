<div>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-6 text-lg font-semibold">Meal Timing Configuration</h3>
            
            <div class="space-y-6">
                @foreach(['breakfast', 'lunch', 'snacks', 'dinner'] as $mealType)
                    <div class="rounded-xl border border-slate-200 p-5 dark:border-slate-700">
                        <h4 class="mb-4 font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($mealType) }}</h4>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Start Time</label>
                                <input
                                    type="time"
                                    wire:model.live="timeSlots.{{ $mealType }}.start_time"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                >
                            </div>
                            
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">End Time</label>
                                <input
                                    type="time"
                                    wire:model.live="timeSlots.{{ $mealType }}.end_time"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                >
                            </div>
                            
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Cutoff Before (Hours)</label>
                                <input
                                    type="number"
                                    wire:model.live="timeSlots.{{ $mealType }}.cutoff_hours_before"
                                    min="1"
                                    max="24"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                >
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button
                wire:click="saveTimings"
                wire:loading.attr="disabled"
                class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
            >
                <span wire:loading>Saving...</span>
                <span wire:loading.remove>Save All Timings</span>
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Information</h3>
            <div class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <p>• Start Time: When the meal service begins and students can start checking in</p>
                <p>• End Time: When the meal service ends and QR check-in closes</p>
                <p>• Cutoff Before: Hours before meal when booking/cancellation is locked</p>
                <p>• Changes apply immediately to the student portal</p>
            </div>
        </div>
    </div>
</div>
