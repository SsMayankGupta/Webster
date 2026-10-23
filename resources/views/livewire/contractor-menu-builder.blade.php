<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Planning</p>
            <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Build a menu</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create the week’s meals with nutrition targets, a previous schedule, or your own entries.</p>
        </header>

        <section aria-labelledby="menu-effective-dates" class="border-l-2 border-emerald-700 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-400 dark:bg-emerald-950/30 dark:text-emerald-200">
            <h3 id="menu-effective-dates" class="font-medium">New mess menu applies next week</h3>
            <p class="mt-1">
                Any menu created here—AI-generated, cloned, or entered manually—will be scheduled from
                <strong>{{ $menuWeekStart->format('l, F j') }}</strong>
                through
                <strong>{{ $menuWeekEnd->format('l, F j') }}</strong>.
            </p>
        </section>

        @if ($scheduleCreatedSuccessfully)
            <div role="status" class="border-l-2 border-emerald-700 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-400 dark:bg-emerald-950/30 dark:text-emerald-200">
                <p class="font-medium">Menu created successfully.</p>
                <p class="mt-1">The saved menu is scheduled for {{ implode(', ', $scheduledMenuDates) }}.</p>
                <a href="{{ route('contractor.menu-update') }}" class="mt-2 inline-block font-medium underline underline-offset-2">
                    Open Menu Update and choose Next Week to edit
                </a>
            </div>
        @elseif ($scheduledMenuDates !== [])
            <div role="status" class="border-l-2 border-amber-600 bg-amber-50/70 px-4 py-3 text-sm text-amber-900 dark:border-amber-400 dark:bg-amber-950/30 dark:text-amber-200">
                <p class="font-medium">Menus are already scheduled for next week.</p>
                <p class="mt-1">The database shows menu dates: {{ implode(', ', $scheduledMenuDates) }}.</p>
                <a href="{{ route('contractor.menu-update') }}" class="mt-2 inline-block font-medium underline underline-offset-2">
                    Open Menu Update and choose Next Week to edit
                </a>
            </div>
        @endif

        <div aria-label="Menu creation method" class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800">
            <button
                type="button"
                aria-pressed="{{ $mode === 'ai' ? 'true' : 'false' }}"
                wire:click="$set('mode', 'ai')"
                @class([
                    'border-b-2 px-3 py-3 text-sm transition-colors',
                    'border-emerald-700 font-medium text-emerald-800 dark:border-emerald-400 dark:text-emerald-300' => $mode === 'ai',
                    'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $mode !== 'ai',
                ])
            >
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>AI Generator</span>
                </div>
            </button>
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
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    <span>Clone Week</span>
                </div>
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
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Manual Entry</span>
                </div>
            </button>
        </div>

        @if($mode === 'ai')
            <section class="space-y-5 border-t border-slate-300 pt-4 dark:border-slate-700">
                <div>
                    <h3 class="text-xl font-medium text-slate-900 dark:text-white">Nutrition targets</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose daily energy and macro proportions for the generated menu.</p>
                </div>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="menu-target-calories" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Target calories</label>
                        <input
                            id="menu-target-calories"
                            type="number"
                            wire:model.live="aiPreferences.target_calories"
                            class="input"
                            placeholder="2000"
                        >
                    </div>
                    
                    <div>
                        <label for="menu-protein-ratio" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Protein ratio (%)</label>
                        <input
                            id="menu-protein-ratio"
                            type="number"
                            wire:model.live="aiPreferences.protein_ratio"
                            class="input"
                            placeholder="30"
                        >
                    </div>
                    
                    <div>
                        <label for="menu-carbs-ratio" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Carbohydrate ratio (%)</label>
                        <input
                            id="menu-carbs-ratio"
                            type="number"
                            wire:model.live="aiPreferences.carbs_ratio"
                            class="input"
                            placeholder="50"
                        >
                    </div>
                    
                    <div>
                        <label for="menu-fat-ratio" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Fat ratio (%)</label>
                        <input
                            id="menu-fat-ratio"
                            type="number"
                            wire:model.live="aiPreferences.fat_ratio"
                            class="input"
                            placeholder="20"
                        >
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="menu-cuisine-type" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Cuisine type</label>
                        <select
                            id="menu-cuisine-type"
                            wire:model.live="aiPreferences.cuisine_type"
                            class="input"
                        >
                            @foreach($cuisineTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('-', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 pb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                wire:model.live="aiPreferences.vegetarian"
                                class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600"
                            >
                            Vegetarian Only
                        </label>
                    </div>
                </div>

                <button
                    wire:click="generateAIMenu"
                    wire:loading.attr="disabled"
                    class="btn-primary mt-1 disabled:cursor-wait"
                >
                    <span wire:loading wire:target="generateAIMenu">Preparing menu…</span>
                    <span wire:loading.remove wire:target="generateAIMenu">Generate menu</span>
                </button>
            </section>
        @elseif($mode === 'clone')
            <section class="max-w-xl space-y-5 border-t border-slate-300 pt-4 dark:border-slate-700">
                <div>
                    <h3 class="text-xl font-medium text-slate-900 dark:text-white">Reuse a previous week</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Copy a past week’s meal plan to use as a starting point.</p>
                </div>
                
                <div>
                    <label for="menu-previous-week" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Week to copy</label>
                    <select
                        id="menu-previous-week"
                        wire:model.live="selectedWeek"
                        class="input"
                    >
                        <option value="">Choose a past week</option>
                        @foreach($pastWeeks as $index => $week)
                            <option value="{{ $index }}">{{ $week['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    wire:click="clonePastWeek"
                    wire:loading.attr="disabled"
                    class="btn-primary disabled:cursor-wait"
                >
                    <span wire:loading wire:target="clonePastWeek">Copying schedule…</span>
                    <span wire:loading.remove wire:target="clonePastWeek">Copy week schedule</span>
                </button>
            </section>
        @elseif($mode === 'manual')
            <section class="space-y-5 border-t border-slate-300 pt-4 dark:border-slate-700">
                <div>
                    <h3 class="text-xl font-medium text-slate-900 dark:text-white">Enter the week’s meals</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add meal items and nutrition estimates. Separate multiple items with commas.</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Day</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Breakfast</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Lunch</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Snacks</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dinner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $day)
                                <tr wire:key="menu-builder-day-{{ $day }}" class="border-b border-slate-200 dark:border-slate-800">
                                    <th scope="row" class="px-3 py-4 text-left align-top text-sm font-medium text-slate-800 dark:text-slate-200">{{ $day }}</th>
                                    @foreach($mealTypes as $mealType)
                                        <td class="px-3 py-2">
                                            <input
                                                type="text"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.items"
                                                placeholder="Menu items (comma separated)"
                                                class="input mb-2 text-xs"
                                            >
                                            <div class="grid grid-cols-3 gap-1">
                                                <div>
                                                    <label class="sr-only" for="menu-protein-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} protein in grams</label>
                                                    <input
                                                        id="menu-protein-{{ $day }}-{{ $mealType }}"
                                                        type="number"
                                                        wire:model.live="schedule.{{ $day }}.{{ $mealType }}.protein"
                                                        placeholder="P (g)"
                                                        class="input px-2 text-xs"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="sr-only" for="menu-carbs-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} carbohydrates in grams</label>
                                                    <input
                                                        id="menu-carbs-{{ $day }}-{{ $mealType }}"
                                                        type="number"
                                                        wire:model.live="schedule.{{ $day }}.{{ $mealType }}.carbs"
                                                        placeholder="C (g)"
                                                        class="input px-2 text-xs"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="sr-only" for="menu-calories-{{ $day }}-{{ $mealType }}">{{ $day }} {{ strtolower($mealType) }} calories</label>
                                                    <input
                                                        id="menu-calories-{{ $day }}-{{ $mealType }}"
                                                        type="number"
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

                <button
                    wire:click="saveSchedule"
                    wire:loading.attr="disabled"
                    class="btn-primary disabled:cursor-wait"
                >
                    <span wire:loading wire:target="saveSchedule">Saving schedule…</span>
                    <span wire:loading.remove wire:target="saveSchedule">Save weekly schedule</span>
                </button>
            </section>
        @endif

        <!-- Preview Section -->
        @if($mode !== 'clone' || $selectedWeek !== null)
            <section class="space-y-5 border-t border-slate-300 pt-4 dark:border-slate-700">
                <div>
                    <h3 class="text-xl font-medium text-slate-900 dark:text-white">Week preview</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review meals and nutrition estimates before saving.</p>
                </div>
                
                <div class="space-y-4">
                    @foreach($days as $day)
                        <div wire:key="menu-preview-day-{{ $day }}" class="border-t border-slate-200 pt-4 dark:border-slate-800">
                            <h4 class="mb-3 text-base font-medium text-slate-900 dark:text-white">{{ $day }}</h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach($mealTypes as $mealType)
                                    <div wire:key="menu-preview-{{ $day }}-{{ $mealType }}" class="border-l-2 border-emerald-700 pl-3 dark:border-emerald-500">
                                        <div class="mb-1 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ ucfirst($mealType) }}</div>
                                        @if(!empty($schedule[$day][$mealType]['items']))
                                            <div class="mb-2 text-sm text-slate-700 dark:text-slate-300">{{ $schedule[$day][$mealType]['items'] }}</div>
                                        @else
                                            <div class="mb-2 text-sm text-slate-400 italic">Not set</div>
                                        @endif
                                        <div class="flex gap-2 text-xs text-slate-500 dark:text-slate-400">
                                            <span>P: {{ $schedule[$day][$mealType]['protein'] }}g</span>
                                            <span>C: {{ $schedule[$day][$mealType]['carbs'] }}g</span>
                                            <span>K: {{ $schedule[$day][$mealType]['calories'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($mode !== 'clone')
                    <button
                        wire:click="saveSchedule"
                        wire:loading.attr="disabled"
                        class="btn-primary disabled:cursor-wait"
                    >
                        <span wire:loading wire:target="saveSchedule">Saving schedule…</span>
                        <span wire:loading.remove wire:target="saveSchedule">Save weekly schedule</span>
                    </button>
                @endif
            </section>
        @endif
    </div>
</div>
