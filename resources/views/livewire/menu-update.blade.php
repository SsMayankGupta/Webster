<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Menu Update</h3>
            <div class="flex gap-2">
                <button
                    wire:click="switchWeek('current')"
                    class="rounded-lg px-4 py-2 text-sm font-medium {{ $selectedWeek === 'current' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
                >
                    Current Week
                </button>
                <button
                    wire:click="switchWeek('next')"
                    class="rounded-lg px-4 py-2 text-sm font-medium {{ $selectedWeek === 'next' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
                >
                    Next Week
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($menus as $date => $dayMenus)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                    <h4 class="mb-4 font-semibold text-slate-900 dark:text-slate-100">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
                    </h4>
                    
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach(['breakfast', 'lunch', 'snacks', 'dinner'] as $mealType)
                            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-sm font-semibold uppercase text-slate-600 dark:text-slate-400">{{ $mealType }}</span>
                                    @if($dayMenus[$mealType])
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $dayMenus[$mealType]['status'] === 'open' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $dayMenus[$mealType]['status'] }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($dayMenus[$mealType])
                                    <div class="space-y-2">
                                        <div>
                                            <label class="mb-1 block text-xs text-slate-500">Items (comma separated)</label>
                                            <input
                                                type="text"
                                                wire:model="menus.{{ $date }}.{{ $mealType }}.items"
                                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                            >
                                        </div>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-500">Protein (g)</label>
                                                <input
                                                    type="number"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.protein"
                                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-500">Carbs (g)</label>
                                                <input
                                                    type="number"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.carbs"
                                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs text-slate-500">Calories</label>
                                                <input
                                                    type="number"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.calories"
                                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-700"
                                                >
                                            </div>
                                        </div>
                                        <button
                                            wire:click="updateMenu({{ $dayMenus[$mealType]['id'] }}, menus.{{ $date }}.{{ $mealType }})"
                                            class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                                        >
                                            Update Meal
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center text-sm text-slate-400 italic">
                                        No meal scheduled
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
