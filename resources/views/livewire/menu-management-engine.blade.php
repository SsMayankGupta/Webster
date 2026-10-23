<div class="font-lora">
    <section class="space-y-6">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Planning</p>
            <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Menu management</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Build next week's meals by reusing a past schedule, setting nutrition targets, or entering meals yourself.</p>
        </header>

        <div aria-label="Menu planning method" class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800">
            <button
                type="button"
                aria-pressed="{{ $mode === 'clone' ? 'true' : 'false' }}"
                wire:click="$set('mode', 'clone')"
                @class([
                    'border-b-2 px-3 py-3 text-sm transition-colors',
                    'border-emerald-700 font-medium text-emerald-800 dark:border-emerald-400 dark:text-emerald-300' => $mode === 'clone',
                    'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $mode !== 'clone',
                ])
            >
                Clone a past week
            </button>
            <button
                type="button"
                aria-pressed="{{ $mode === 'macros' ? 'true' : 'false' }}"
                wire:click="$set('mode', 'macros')"
                @class([
                    'border-b-2 px-3 py-3 text-sm transition-colors',
                    'border-emerald-700 font-medium text-emerald-800 dark:border-emerald-400 dark:text-emerald-300' => $mode === 'macros',
                    'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $mode !== 'macros',
                ])
            >
                Set nutrition targets
            </button>
            <button
                type="button"
                aria-pressed="{{ $mode === 'manual' ? 'true' : 'false' }}"
                wire:click="$set('mode', 'manual')"
                @class([
                    'border-b-2 px-3 py-3 text-sm transition-colors',
                    'border-emerald-700 font-medium text-emerald-800 dark:border-emerald-400 dark:text-emerald-300' => $mode === 'manual',
                    'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $mode !== 'manual',
                ])
            >
                Enter manually
            </button>
        </div>

        @if ($mode === 'clone')
            <div class="max-w-xl space-y-5">
                <div>
                    <label for="past-week" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Week to copy</label>
                    <select
                        id="past-week"
                        wire:model.live="selectedWeek"
                        class="input"
                    >
                        <option value="">Choose a past week</option>
                        @foreach ($pastWeeks as $index => $week)
                            <option value="{{ $index }}">{{ $week['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="button"
                    wire:click="clonePastWeek"
                    wire:loading.attr="disabled"
                    class="btn-primary disabled:cursor-wait"
                >
                    <span wire:loading wire:target="clonePastWeek">Copying schedule…</span>
                    <span wire:loading.remove wire:target="clonePastWeek">Copy week schedule</span>
                </button>
            </div>
        @elseif ($mode === 'macros')
            <div class="max-w-3xl space-y-5">
                <p class="text-sm text-slate-500 dark:text-slate-400">Set weekly nutrition goals. The values will be distributed across each day and meal.</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="target-protein" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Protein <span class="font-normal text-slate-500">(g / week)</span></label>
                        <input id="target-protein" type="number" min="0" wire:model.live="targetMacros.protein" class="input">
                    </div>
                    <div>
                        <label for="target-carbs" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Carbohydrates <span class="font-normal text-slate-500">(g / week)</span></label>
                        <input id="target-carbs" type="number" min="0" wire:model.live="targetMacros.carbs" class="input">
                    </div>
                    <div>
                        <label for="target-calories" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Energy <span class="font-normal text-slate-500">(kcal / week)</span></label>
                        <input id="target-calories" type="number" min="0" wire:model.live="targetMacros.calories" class="input">
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="generateFromMacros"
                    wire:loading.attr="disabled"
                    class="btn-primary disabled:cursor-wait"
                >
                    <span wire:loading wire:target="generateFromMacros">Preparing schedule…</span>
                    <span wire:loading.remove wire:target="generateFromMacros">Prepare schedule</span>
                </button>
            </div>
        @elseif ($mode === 'manual')
            <div>
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Add meal items and nutrition estimates for each service. Separate multiple items with commas.</p>

                <div class="overflow-x-auto border-y border-slate-200 dark:border-slate-800">
                    <table class="min-w-[900px] divide-y divide-slate-200 text-left dark:divide-slate-800">
                        <thead>
                            <tr>
                                <th scope="col" class="w-28 py-3 pr-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Day</th>
                                @foreach (['Breakfast', 'Lunch', 'Snacks', 'Dinner'] as $mealLabel)
                                    <th scope="col" class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $mealLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <tr wire:key="schedule-day-{{ $day }}">
                                    <th scope="row" class="py-4 pr-4 align-top text-sm font-medium text-slate-800 dark:text-slate-200">{{ $day }}</th>
                                    @foreach (['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER'] as $mealType)
                                        <td class="px-3 py-3 align-top">
                                            <label class="sr-only" for="items-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} items</label>
                                            <input
                                                id="items-{{ $day }}-{{ $mealType }}"
                                                type="text"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.items"
                                                placeholder="Meal items"
                                                class="input text-sm"
                                            >
                                            <div class="mt-2 grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="sr-only" for="protein-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} protein in grams</label>
                                                    <input
                                                        id="protein-{{ $day }}-{{ $mealType }}"
                                                        type="number"
                                                        min="0"
                                                        wire:model.live="schedule.{{ $day }}.{{ $mealType }}.protein"
                                                        placeholder="Protein"
                                                        class="input px-2 text-xs"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="sr-only" for="carbs-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} carbohydrates in grams</label>
                                                    <input
                                                        id="carbs-{{ $day }}-{{ $mealType }}"
                                                        type="number"
                                                        min="0"
                                                        wire:model.live="schedule.{{ $day }}.{{ $mealType }}.carbs"
                                                        placeholder="Carbs"
                                                        class="input px-2 text-xs"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="sr-only" for="calories-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} calories</label>
                                                    <input
                                                        id="calories-{{ $day }}-{{ $mealType }}"
                                                        type="number"
                                                        min="0"
                                                        wire:model.live="schedule.{{ $day }}.{{ $mealType }}.calories"
                                                        placeholder="Kcal"
                                                        class="input px-2 text-xs"
                                                    >
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    <button
                        type="button"
                        wire:click="saveSchedule"
                        wire:loading.attr="disabled"
                        class="btn-primary disabled:cursor-wait"
                    >
                        <span wire:loading wire:target="saveSchedule">Saving schedule…</span>
                        <span wire:loading.remove wire:target="saveSchedule">Save weekly schedule</span>
                    </button>
                </div>
            </div>
        @endif
    </section>
</div>
