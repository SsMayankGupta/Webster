<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Daily operations</p>
            <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Meal timing</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Set service hours and booking cutoffs for each meal.</p>
        </header>

        <section aria-labelledby="meal-timing-heading">
            <div class="mb-4">
                <h3 id="meal-timing-heading" class="text-xl font-medium text-slate-900 dark:text-white">Service schedule</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Changes apply immediately to the student portal.</p>
            </div>

            <div class="divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                @foreach (['breakfast', 'lunch', 'snacks', 'dinner'] as $mealType)
                    <section aria-labelledby="meal-{{ $mealType }}-heading" class="py-5">
                        <h4 id="meal-{{ $mealType }}-heading" class="mb-4 text-base font-medium capitalize text-slate-900 dark:text-white">{{ $mealType }}</h4>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="space-y-2">
                                <label for="meal-{{ $mealType }}-start" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Start time</label>
                                <input
                                    id="meal-{{ $mealType }}-start"
                                    type="time"
                                    wire:model.live="timeSlots.{{ $mealType }}.start_time"
                                    class="input"
                                >
                            </div>

                            <div class="space-y-2">
                                <label for="meal-{{ $mealType }}-end" class="block text-sm font-medium text-slate-700 dark:text-slate-300">End time</label>
                                <input
                                    id="meal-{{ $mealType }}-end"
                                    type="time"
                                    wire:model.live="timeSlots.{{ $mealType }}.end_time"
                                    class="input"
                                >
                            </div>

                            <div class="space-y-2">
                                <label for="meal-{{ $mealType }}-cutoff" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cutoff before (hours)</label>
                                <input
                                    id="meal-{{ $mealType }}-cutoff"
                                    type="number"
                                    wire:model.live="timeSlots.{{ $mealType }}.cutoff_hours_before"
                                    min="1"
                                    max="24"
                                    class="input"
                                >
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button
                    wire:click="saveTimings"
                    wire:loading.attr="disabled"
                    wire:target="saveTimings"
                    class="btn-primary disabled:cursor-wait disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="saveTimings">Save all timings</span>
                    <span wire:loading wire:target="saveTimings">Saving...</span>
                </button>
            </div>
        </section>

        <section aria-labelledby="meal-timing-information-heading">
            <div class="mb-4">
                <h3 id="meal-timing-information-heading" class="text-xl font-medium text-slate-900 dark:text-white">Information</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">How these settings affect meal service.</p>
            </div>

            <ul class="divide-y divide-slate-200 border-y border-slate-200 text-sm leading-6 text-slate-600 dark:divide-slate-800 dark:border-slate-800 dark:text-slate-400">
                <li class="py-3"><span class="font-medium text-slate-800 dark:text-slate-200">Start time:</span> When meal service begins and students can check in.</li>
                <li class="py-3"><span class="font-medium text-slate-800 dark:text-slate-200">End time:</span> When meal service ends and QR check-in closes.</li>
                <li class="py-3"><span class="font-medium text-slate-800 dark:text-slate-200">Cutoff before:</span> Hours before the meal when booking and cancellation are locked.</li>
                <li class="py-3">Changes apply immediately to the student portal.</li>
            </ul>
        </section>
    </div>
</div>
