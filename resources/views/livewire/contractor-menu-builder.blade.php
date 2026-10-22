<div>
    <div class="space-y-6">
        <!-- Mode Selector -->
        <div class="flex gap-2">
            <button
                wire:click="$set('mode', 'ai')"
                class="flex-1 rounded-xl border-2 px-4 py-3 text-sm font-semibold transition {{ $mode === 'ai' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-300' : 'border-slate-200 text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400 dark:hover:border-slate-600' }}"
            >
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>AI Generator</span>
                </div>
            </button>
            <button
                wire:click="$set('mode', 'clone')"
                class="flex-1 rounded-xl border-2 px-4 py-3 text-sm font-semibold transition {{ $mode === 'clone' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-300' : 'border-slate-200 text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400 dark:hover:border-slate-600' }}"
            >
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                    <span>Clone Week</span>
                </div>
            </button>
            <button
                wire:click="$set('mode', 'manual')"
                class="flex-1 rounded-xl border-2 px-4 py-3 text-sm font-semibold transition {{ $mode === 'manual' ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-300' : 'border-slate-200 text-slate-600 hover:border-slate-300 dark:border-slate-700 dark:text-slate-400 dark:hover:border-slate-600' }}"
            >
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Manual Entry</span>
                </div>
            </button>
        </div>

        @if($mode === 'ai')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold">AI Menu Generator</h3>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Target Calories</label>
                        <input
                            type="number"
                            wire:model.live="aiPreferences.target_calories"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="2000"
                        >
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Protein Ratio (%)</label>
                        <input
                            type="number"
                            wire:model.live="aiPreferences.protein_ratio"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="30"
                        >
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Carbs Ratio (%)</label>
                        <input
                            type="number"
                            wire:model.live="aiPreferences.carbs_ratio"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="50"
                        >
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Fat Ratio (%)</label>
                        <input
                            type="number"
                            wire:model.live="aiPreferences.fat_ratio"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                            placeholder="20"
                        >
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Cuisine Type</label>
                        <select
                            wire:model.live="aiPreferences.cuisine_type"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                        >
                            @foreach($cuisineTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('-', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                wire:model.live="aiPreferences.vegetarian"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            Vegetarian Only
                        </label>
                    </div>
                </div>

                <button
                    wire:click="generateAIMenu"
                    wire:loading.attr="disabled"
                    class="mt-6 w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span wire:loading>Generating...</span>
                    <span wire:loading.remove>Generate AI Menu</span>
                </button>
            </div>
        @elseif($mode === 'clone')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold">Clone Previous Week</h3>
                
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Select Week to Clone</label>
                    <select
                        wire:model.live="selectedWeek"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                    >
                        <option value="">-- Select a week --</option>
                        @foreach($pastWeeks as $index => $week)
                            <option value="{{ $index }}">{{ $week['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    wire:click="clonePastWeek"
                    wire:loading.attr="disabled"
                    class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span wire:loading>Cloning...</span>
                    <span wire:loading.remove>Clone Week Schedule</span>
                </button>
            </div>
        @elseif($mode === 'manual')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold">Manual Menu Entry - 7 Days</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/50">
                                <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Day</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Breakfast</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Lunch</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Snacks</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Dinner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $day)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-3 py-2 font-medium text-slate-900 dark:text-slate-100">{{ $day }}</td>
                                    @foreach($mealTypes as $mealType)
                                        <td class="px-3 py-2">
                                            <input
                                                type="text"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.items"
                                                placeholder="Menu items (comma separated)"
                                                class="mb-1 w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                            >
                                            <div class="grid grid-cols-3 gap-1">
                                                <input
                                                    type="number"
                                                    wire:model.live="schedule.{{ $day }}.{{ $mealType }}.protein"
                                                    placeholder="P (g)"
                                                    class="rounded border border-slate-300 px-2 py-1 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
                                                <input
                                                    type="number"
                                                    wire:model.live="schedule.{{ $day }}.{{ $mealType }}.carbs"
                                                    placeholder="C (g)"
                                                    class="rounded border border-slate-300 px-2 py-1 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
                                                <input
                                                    type="number"
                                                    wire:model.live="schedule.{{ $day }}.{{ $mealType }}.calories"
                                                    placeholder="Kcal"
                                                    class="rounded border border-slate-300 px-2 py-1 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
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
                    class="mt-4 w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                >
                    <span wire:loading>Saving...</span>
                    <span wire:loading.remove>Save Schedule</span>
                </button>
            </div>
        @endif

        <!-- Preview Section -->
        @if($mode !== 'clone' || $selectedWeek !== null)
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <h3 class="mb-4 text-lg font-semibold">7-Day Schedule Preview</h3>
                
                <div class="space-y-4">
                    @foreach($days as $day)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <h4 class="mb-3 font-semibold text-slate-900 dark:text-slate-100">{{ $day }}</h4>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach($mealTypes as $mealType)
                                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-900/50">
                                        <div class="mb-1 text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">{{ $mealType }}</div>
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
                        class="mt-6 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                    >
                        <span wire:loading>Saving...</span>
                        <span wire:loading.remove>Save to Database</span>
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
