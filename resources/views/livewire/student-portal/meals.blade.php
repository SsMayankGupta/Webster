@php
    $today = $state['today'];
@endphp

<div wire:poll.60s>
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-display text-xl font-extrabold tracking-tight">Today’s mess menu</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $today['full_label'] }} · confirmation closes {{ $state['cutoff_hours'] }} hours before serving.</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
            </span>
            Live cutoff status
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        @forelse ($today['meals'] as $meal)
            <article
                class="card overflow-hidden"
                wire:key="today-meal-{{ $meal['id'] }}"
                x-data="mealClock({{ $meal['cutoff_timestamp'] }}, {{ $meal['serve_timestamp'] }}, @js($state['server_now']))"
            >
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-display text-lg font-bold">{{ $meal['meal_label'] }}</h3>
                            @if ($meal['status'] === 'open')
                                <span class="badge-success">Open</span>
                            @elseif ($meal['status'] === 'locked')
                                <span class="badge-warning">Locked</span>
                            @elseif ($meal['status'] === 'serving')
                                <span class="badge-info">Serving</span>
                            @else
                                <span class="badge bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Closed</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $meal['serve_at_label'] }} · {{ $meal['cutoff_label'] }} cutoff</p>
                    </div>

                    <div class="text-right">
                        <p class="font-mono text-sm font-bold tabular-nums text-slate-900 dark:text-white" x-text="short">--:--</p>
                        <p class="text-[11px] text-slate-400" x-text="phase === 'open' ? 'until lock' : 'state'"></p>
                    </div>
                </div>

                <div class="p-5">
                    @if ($meal['items'] !== [])
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($meal['items'] as $item)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $item }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400">Menu items will be published by the kitchen.</p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($meal['calories']) }} kcal</span>
                        <span>P {{ number_format($meal['protein'], 1) }}g</span>
                        <span>C {{ number_format($meal['carbs'], 1) }}g</span>
                        @if ($meal['lane'])
                            <span class="badge-info">Lane {{ $meal['lane'] }}</span>
                        @endif
                        @if ($meal['opted_in'])
                            <span class="badge-success">Opted in</span>
                        @else
                            <span class="badge-warning">Opted out</span>
                        @endif
                    </div>

                    @error('meal-'.$meal['id'])
                        <p class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                        @if ($meal['can_manage'])
                            <div x-show="phase === 'open'" class="space-y-3">
                                <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                                    <div>
                                        <label for="diet-{{ $meal['id'] }}" class="label !mb-1 text-xs">Dietary preference for this meal</label>
                                        <select
                                            id="diet-{{ $meal['id'] }}"
                                            class="input !py-2 text-sm"
                                            wire:change="confirmMeal({{ $meal['id'] }}, true, $event.target.value)"
                                        >
                                            <option value="">No preference</option>
                                            @foreach ($state['dietary_options'] as $option)
                                                <option value="{{ $option['value'] }}" @selected($meal['dietary_pref'] === $option['value'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if ($meal['opted_in'])
                                        <button
                                            type="button"
                                            wire:click="cancelMeal({{ $meal['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="btn-ghost !border !border-rose-200 !text-rose-600 hover:!bg-rose-50 dark:!border-rose-900 dark:!text-rose-300 dark:hover:!bg-rose-950/40"
                                        >
                                            Opt out
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="confirmMeal({{ $meal['id'] }}, true)"
                                            wire:loading.attr="disabled"
                                            class="btn-primary"
                                        >
                                            Opt back in
                                        </button>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Changing the preference confirms the meal. All updates are validated against the server clock.
                                </p>
                            </div>

                            <div x-show="phase !== 'open'" x-cloak class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>
                                    Cutoff reached — the headcount is frozen.
                                    @if ($meal['lane'])
                                        Proceed to <strong>lane {{ $meal['lane'] }}</strong> for check-in.
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>
                                    {{ $meal['status_label'] }}. Editing is closed for this meal.
                                    @if ($meal['lane'])
                                        Your lane: <strong>{{ $meal['lane'] }}</strong>.
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="card col-span-full p-10 text-center">
                <p class="font-display text-lg font-bold">No meals scheduled for today.</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The weekly grid below shows the next seven days.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 class="font-display text-xl font-extrabold tracking-tight">Seven-day schedule</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Confirm ahead or opt out of any open meal.</p>
            </div>
            <button type="button" wire:click="setTab('profile')" class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300">
                Manage global dietary tags →
            </button>
        </div>

        <div class="card mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <div class="grid min-w-[960px] grid-cols-7 gap-2 p-3">
                    @foreach ($state['days'] as $day)
                        <div class="rounded-xl border p-2 {{ $day['is_today'] ? 'border-brand-300 bg-brand-50/40 dark:border-brand-500/40 dark:bg-brand-500/5' : 'border-slate-200 dark:border-slate-800' }}">
                            <div class="mb-2 px-1">
                                <p class="text-xs font-bold uppercase tracking-wide {{ $day['is_today'] ? 'text-brand-700 dark:text-brand-300' : 'text-slate-500 dark:text-slate-400' }}">{{ $day['label'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $day['full_label'] }}</p>
                            </div>

                            <div class="space-y-2">
                                @forelse ($day['meals'] as $meal)
                                    <div
                                        class="rounded-lg border border-slate-100 bg-white p-2 dark:border-slate-800 dark:bg-slate-900"
                                        wire:key="week-meal-{{ $meal['id'] }}"
                                        x-data="mealClock({{ $meal['cutoff_timestamp'] }}, {{ $meal['serve_timestamp'] }}, @js($state['server_now']))"
                                    >
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200">{{ $meal['meal_label'] }}</span>
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="{
                                                    'bg-emerald-500': phase === 'open',
                                                    'bg-amber-500': phase === 'locked',
                                                    'bg-indigo-500': phase === 'serving',
                                                    'bg-slate-400': phase === 'closed'
                                                }"
                                            ></span>
                                        </div>
                                        <p class="mt-0.5 text-[10px] text-slate-400">{{ $meal['serve_at_label'] }}</p>

                                        @if ($meal['items'] !== [])
                                            <p class="mt-1 line-clamp-2 text-[10px] leading-snug text-slate-500 dark:text-slate-400">{{ implode(', ', array_slice($meal['items'], 0, 3)) }}</p>
                                        @endif

                                        <p class="mt-1 font-mono text-[10px] font-semibold text-slate-500 dark:text-slate-400" x-text="short"></p>

                                        <div class="mt-1.5 flex items-center justify-between gap-1">
                                            @if ($meal['opted_in'])
                                                <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">In</span>
                                            @else
                                                <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400">Out</span>
                                            @endif

                                            @if ($meal['can_manage'])
                                                <button
                                                    type="button"
                                                    wire:click="{{ $meal['opted_in'] ? 'cancelMeal('.$meal['id'].')' : 'confirmMeal('.$meal['id'].', true)' }}"
                                                    class="rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $meal['opted_in'] ? 'text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950/40' : 'text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10' }}"
                                                    :disabled="phase !== 'open'"
                                                >
                                                    {{ $meal['opted_in'] ? 'Opt out' : 'Opt in' }}
                                                </button>
                                            @else
                                                <svg class="h-3 w-3 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-6 text-center text-[10px] text-slate-400">No meals</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
