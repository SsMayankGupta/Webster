<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Daily operations</p>
            <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Update menu</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        @if ($previousWeeksLoaded > 0)
                            Showing previous menu history along with all upcoming scheduled meals.
                        @else
                            Showing every scheduled meal from today through all available upcoming dates.
                        @endif
                    </p>
                </div>

                @if ($hasPreviousMenus)
                    <button
                        wire:click="loadPreviousData"
                        wire:loading.attr="disabled"
                        wire:target="loadPreviousData"
                        class="rounded-lg border border-emerald-700 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 disabled:opacity-50 dark:text-emerald-400 dark:hover:bg-emerald-400/10"
                    >
                        <span wire:loading.remove wire:target="loadPreviousData">Load previous menu data</span>
                        <span wire:loading wire:target="loadPreviousData">Loading previous menus...</span>
                    </button>
                @endif
            </div>
        </header>

        @if ($statusMessage)
            <p role="status" class="border-l-2 border-emerald-700 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-400 dark:bg-emerald-950/30 dark:text-emerald-200">{{ $statusMessage }}</p>
        @endif

        <div class="space-y-8">
            @php($displayedWeek = null)
            @forelse ($menus as $date => $dayMenus)
                @php($weekStart = \Carbon\Carbon::parse($date)->startOfWeek())
                @if ($displayedWeek !== $weekStart->toDateString())
                    @php($weekEnd = $weekStart->copy()->endOfWeek())
                    <div class="border-t border-slate-300 pt-4 dark:border-slate-700">
                        <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200">
                            Week of {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j') }}
                        </h3>
                    </div>
                    @php($displayedWeek = $weekStart->toDateString())
                @endif

                <section wire:key="menu-day-{{ $date }}" aria-labelledby="menu-day-{{ $date }}-heading">
                    <div class="mb-4 border-t border-slate-300 pt-4 dark:border-slate-700">
                        <h4 id="menu-day-{{ $date }}-heading" class="text-xl font-medium text-slate-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 gap-x-6 md:grid-cols-2 xl:grid-cols-4">
                        @foreach (['breakfast', 'lunch', 'snacks', 'dinner'] as $mealType)
                            <section wire:key="menu-{{ $date }}-{{ $mealType }}" aria-labelledby="menu-{{ $date }}-{{ $mealType }}-heading" class="border-t border-slate-200 py-4 dark:border-slate-800">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <h4 id="menu-{{ $date }}-{{ $mealType }}-heading" class="text-sm font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">{{ $mealType }}</h4>
                                    @if ($dayMenus[$mealType])
                                        <span @class([
                                            'rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' => $dayMenus[$mealType]['status'] === 'open',
                                            'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' => $dayMenus[$mealType]['status'] !== 'open',
                                        ])>
                                            {{ $dayMenus[$mealType]['status'] }}
                                        </span>
                                    @endif
                                </div>

                                @if ($dayMenus[$mealType])
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label for="menu-{{ $date }}-{{ $mealType }}-items" class="block text-xs font-medium text-slate-600 dark:text-slate-400">Items (comma separated)</label>
                                            <input
                                                id="menu-{{ $date }}-{{ $mealType }}-items"
                                                type="text"
                                                wire:model="menus.{{ $date }}.{{ $mealType }}.items"
                                                class="input"
                                            >
                                            @error("menus.$date.$mealType.items")
                                                <p role="alert" class="text-xs text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="space-y-2">
                                                <label for="menu-{{ $date }}-{{ $mealType }}-protein" class="block text-xs font-medium text-slate-600 dark:text-slate-400">Protein (g)</label>
                                                <input
                                                    id="menu-{{ $date }}-{{ $mealType }}-protein"
                                                    type="number"
                                                    min="0"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.protein"
                                                    class="input px-2"
                                                >
                                                @error("menus.$date.$mealType.protein")
                                                    <p role="alert" class="text-xs text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="space-y-2">
                                                <label for="menu-{{ $date }}-{{ $mealType }}-carbs" class="block text-xs font-medium text-slate-600 dark:text-slate-400">Carbs (g)</label>
                                                <input
                                                    id="menu-{{ $date }}-{{ $mealType }}-carbs"
                                                    type="number"
                                                    min="0"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.carbs"
                                                    class="input px-2"
                                                >
                                                @error("menus.$date.$mealType.carbs")
                                                    <p role="alert" class="text-xs text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="space-y-2">
                                                <label for="menu-{{ $date }}-{{ $mealType }}-calories" class="block text-xs font-medium text-slate-600 dark:text-slate-400">Calories</label>
                                                <input
                                                    id="menu-{{ $date }}-{{ $mealType }}-calories"
                                                    type="number"
                                                    min="0"
                                                    wire:model="menus.{{ $date }}.{{ $mealType }}.calories"
                                                    class="input px-2"
                                                >
                                                @error("menus.$date.$mealType.calories")
                                                    <p role="alert" class="text-xs text-rose-700 dark:text-rose-300">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="updateMenu({{ $dayMenus[$mealType]['id'] }}, '{{ $date }}', '{{ $mealType }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="updateMenu({{ $dayMenus[$mealType]['id'] }}, '{{ $date }}', '{{ $mealType }}')"
                                            class="w-full rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-60"
                                        >
                                            <span wire:loading.remove wire:target="updateMenu({{ $dayMenus[$mealType]['id'] }}, '{{ $date }}', '{{ $mealType }}')">Update meal</span>
                                            <span wire:loading wire:target="updateMenu({{ $dayMenus[$mealType]['id'] }}, '{{ $date }}', '{{ $mealType }}')">Updating meal…</span>
                                        </button>
                                    </div>
                                @else
                                    <p class="py-2 text-sm italic text-slate-500 dark:text-slate-400">No meal scheduled</p>
                                @endif
                            </section>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">No upcoming meals scheduled.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
