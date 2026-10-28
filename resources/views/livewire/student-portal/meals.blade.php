@php
    $today = $state['today'];
@endphp

<div wire:poll.60s="refreshMealState">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="font-lora text-xl font-semibold tracking-tight">Today’s mess menu</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $today['full_label'] }} · service hours and booking cutoff are shown on each meal.</p>
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
                            <h3 class="font-lora text-lg font-semibold">{{ $meal['meal_label'] }}</h3>
                            @if ($meal['is_consumed'])
                                <span class="badge-success">Food consumed</span>
                            @elseif ($meal['is_on_approved_leave'])
                                <span class="badge-info">Approved leave</span>
                            @elseif ($meal['status'] === 'open')
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
                        @if ($meal['is_consumed'])
                            <span class="badge-success">Done</span>
                        @elseif ($meal['is_on_approved_leave'])
                            <span class="badge-info">On approved leave</span>
                        @elseif ($meal['opted_in'])
                            <span class="badge-success">Opted in</span>
                        @else
                            <span class="badge-warning">Opted out</span>
                        @endif
                    </div>

                    @error('meal-'.$meal['id'])
                        <p class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                        @if ($meal['is_consumed'])
                            <div class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">
                                <span class="font-semibold">You have taken this meal. It is complete and can no longer be changed.</span>
                            </div>
                        @elseif ($meal['is_on_approved_leave'])
                            <div class="flex items-start gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5 text-xs text-indigo-800 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200">
                                <span class="font-semibold">Your leave is approved for this date. Meal opt-in/out and QR check-in are unavailable. The daily rebate is shown in your leave history and statement.</span>
                            </div>
                        @elseif ($meal['can_manage'])
                            <div x-show="phase === 'open'" class="space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-3">
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
                                            Opt in
                                        </button>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Opt in before this meal’s {{ $meal['cutoff_hours'] }}-hour cutoff to be included in the headcount.
                                </p>
                            </div>

                            <div x-show="phase !== 'open'" x-cloak class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>
                                    @if ($meal['opted_in'])
                                        Cutoff reached — your booking and the headcount are frozen.
                                    @else
                                        Cutoff reached — you are opted out and cannot receive this meal.
                                    @endif
                                    @if ($meal['lane'])
                                        Proceed to <strong>lane {{ $meal['lane'] }}</strong> for check-in.
                                    @endif
                                </span>
                            </div>
                        @else
                            <div class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300">
                                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>
                                    {{ $meal['status_label'] }}.
                                    @if ($meal['opted_in'])
                                        Your booking is locked for this meal.
                                    @else
                                        You are opted out and cannot receive this meal.
                                    @endif
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
                <p class="font-lora text-lg font-semibold">No meals scheduled for today.</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The weekly grid below shows the next seven days.</p>
            </div>
        @endforelse
    </div>

    <section class="mt-10" aria-labelledby="week-schedule-heading">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <div class="flex items-center gap-2">
                    <h2 id="week-schedule-heading" class="font-lora text-xl font-semibold tracking-tight">Your meal week</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:bg-slate-800 dark:text-slate-400">7 days</span>
                </div>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Service hours and meal-specific booking cutoffs are shown on each meal.</p>
            </div>
            <button type="button" wire:click="setTab('profile')" class="self-start text-xs font-semibold text-brand-700 hover:text-brand-800 sm:self-auto dark:text-brand-300">
                Manage dietary preferences <span aria-hidden="true">→</span>
            </button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($state['days'] as $day)
                <article @class([
                    'overflow-hidden rounded-2xl border p-4 shadow-sm transition-colors',
                    'border-brand-300 bg-brand-50/50 ring-1 ring-brand-100 dark:border-brand-500/40 dark:bg-brand-500/5 dark:ring-brand-500/10' => $day['is_today'],
                    'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900/70' => ! $day['is_today'],
                ])>
                    <header class="flex items-center justify-between gap-3 border-b border-slate-200/70 pb-3 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div @class([
                                'flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl text-[10px] font-bold uppercase leading-tight',
                                'bg-brand-600 text-white' => $day['is_today'],
                                'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' => ! $day['is_today'],
                            ])>
                                <span>{{ $day['date'] ? \Illuminate\Support\Carbon::parse($day['date'])->format('d') : '' }}</span>
                                <span>{{ $day['date'] ? \Illuminate\Support\Carbon::parse($day['date'])->format('M') : '' }}</span>
                            </div>
                            <div>
                                <h3 class="font-lora text-sm font-semibold text-slate-900 dark:text-white">{{ $day['label'] }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $day['full_label'] }}</p>
                            </div>
                        </div>
                        @if ($day['is_today'])
                            <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[10px] font-bold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">TODAY</span>
                        @endif
                    </header>

                    <div class="mt-3 flex flex-col gap-2">
                        @forelse ($day['meals'] as $meal)
                            <div
                                wire:key="week-meal-{{ $meal['id'] }}"
                                x-data="mealClock({{ $meal['cutoff_timestamp'] }}, {{ $meal['serve_timestamp'] }}, @js($state['server_now']))"
                                class="rounded-xl border border-slate-100 bg-white p-3 dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $meal['meal_label'] }}</h4>
                                            @if ($meal['is_consumed'])
                                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Food consumed</span>
                                            @elseif ($meal['is_on_approved_leave'])
                                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">Approved leave</span>
                                            @else
                                                <span @class([
                                                    'rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $meal['status'] === 'open',
                                                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' => $meal['status'] === 'locked',
                                                    'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' => $meal['status'] === 'serving',
                                                    'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' => $meal['status'] === 'closed',
                                                ])>{{ ucfirst($meal['status']) }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $meal['serve_at_label'] }}</p>
                                    </div>
                                    <p class="shrink-0 font-mono text-[11px] font-semibold tabular-nums text-slate-500 dark:text-slate-400" x-text="short"></p>
                                </div>

                                @if ($meal['items'] !== [])
                                    <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300">{{ implode(', ', array_slice($meal['items'], 0, 3)) }}</p>
                                @else
                                    <p class="mt-2 text-xs text-slate-400">Menu to be announced</p>
                                @endif

                                <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-2.5 dark:border-slate-800">
                                    @if ($meal['is_consumed'])
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Done
                                        </span>
                                    @elseif ($meal['is_on_approved_leave'])
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-700 dark:text-indigo-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span> On approved leave
                                        </span>
                                    @elseif ($meal['opted_in'])
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Opted in
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Opted out
                                        </span>
                                    @endif

                                    @if ($day['is_today'] && $meal['can_manage'])
                                        <button
                                            type="button"
                                            wire:click="{{ $meal['opted_in'] ? 'cancelMeal('.$meal['id'].')' : 'confirmMeal('.$meal['id'].', true)' }}"
                                            wire:loading.attr="disabled"
                                            :disabled="phase !== 'open'"
                                            class="rounded-lg px-3 py-1.5 text-[11px] font-semibold transition {{ $meal['opted_in'] ? 'text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950/40' : 'bg-brand-600 text-white hover:bg-brand-700' }}"
                                        >
                                            {{ $meal['opted_in'] ? 'Opt out' : 'Opt in' }}
                                        </button>
                                    @elseif ($day['is_today'] && ! $meal['is_consumed'] && ! $meal['is_on_approved_leave'])
                                        <span class="text-[10px] font-medium text-slate-400">Choice locked</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-200 px-3 py-5 text-center text-xs text-slate-400 dark:border-slate-800">No meals scheduled</p>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
