<div class="font-lora">
    <div class="space-y-8">
        <header class="flex flex-col gap-3 border-b border-slate-200 pb-6 dark:border-slate-800 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Contractor overview</p>
                <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Service at a glance</h2>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j') }}</p>
        </header>

        <section aria-labelledby="contractor-help-heading" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Support</p>
            <h3 id="contractor-help-heading" class="mt-2 text-xl font-medium text-slate-900 dark:text-white">Need help?</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Contact your warden for support with your hostel workspace.</p>
            @if ($wardenContact)
                <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/70">
                    <p class="font-medium text-slate-900 dark:text-white">{{ $wardenContact['name'] }}</p>
                    @if (filled($wardenContact['email']))
                        <a href="mailto:{{ $wardenContact['email'] }}" class="mt-1 block break-all text-sm text-emerald-700 hover:underline dark:text-emerald-400">{{ $wardenContact['email'] }}</a>
                    @endif
                    @if (filled($wardenContact['phone']))
                        <a href="tel:{{ $wardenContact['phone'] }}" class="mt-1 block text-sm text-emerald-700 hover:underline dark:text-emerald-400">{{ $wardenContact['phone'] }}</a>
                    @endif
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Warden contact details are not available. Please contact hostel administration.</p>
            @endif
        </section>

        <section aria-label="Today's summary" class="grid grid-cols-1 divide-y divide-slate-200 border-y border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="px-5 py-5 sm:px-6">
                <p class="text-sm text-slate-500 dark:text-slate-400">Meals today</p>
                <p class="mt-2 text-3xl font-medium tabular-nums text-slate-900 dark:text-white">{{ $stats['total_meals_today'] }}</p>
            </div>
            <div class="px-5 py-5 sm:px-6">
                <p class="text-sm text-slate-500 dark:text-slate-400">Booked headcount</p>
                <p class="mt-2 text-3xl font-medium tabular-nums text-slate-900 dark:text-white">{{ $stats['headcount'] }}</p>
            </div>
            <div class="px-5 py-5 sm:px-6">
                <p class="text-sm text-slate-500 dark:text-slate-400">Waste reports to date</p>
                <p class="mt-2 text-3xl font-medium tabular-nums text-slate-900 dark:text-white">{{ $stats['total_waste_reports'] }}</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <section aria-labelledby="todays-meals-heading" class="border-t border-slate-300 pt-4 dark:border-slate-700">
                <div class="flex items-baseline justify-between gap-4">
                    <h3 id="todays-meals-heading" class="text-xl font-medium text-slate-900 dark:text-white">Today's meals</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($todayMenus) }} scheduled</span>
                </div>

                @if (empty($todayMenus))
                    <p class="mt-5 border-t border-slate-200 py-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Nothing is scheduled for today.</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($todayMenus as $menu)
                            <li wire:key="today-menu-{{ $menu['id'] }}" class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-5">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <h4 class="font-medium text-slate-900 dark:text-white">{{ $menu['meal_label'] }}</h4>
                                        <span @class([
                                            'text-xs font-medium capitalize',
                                            'text-emerald-700 dark:text-emerald-400' => $menu['status'] === 'open',
                                            'text-amber-700 dark:text-amber-400' => $menu['status'] === 'locked',
                                            'text-slate-500 dark:text-slate-400' => ! in_array($menu['status'], ['open', 'locked'], true),
                                        ])>{{ $menu['status'] }}</span>
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ implode(', ', $menu['items']) }}</p>
                                </div>
                                <time class="shrink-0 text-sm tabular-nums text-slate-500 dark:text-slate-400">{{ $menu['serve_at'] }}</time>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section aria-labelledby="recent-waste-heading" class="border-t border-slate-300 pt-4 dark:border-slate-700">
                <div class="flex items-baseline justify-between gap-4">
                    <h3 id="recent-waste-heading" class="text-xl font-medium text-slate-900 dark:text-white">Recent waste reports</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Latest activity</span>
                </div>

                @if (empty($recentWaste))
                    <p class="mt-5 border-t border-slate-200 py-5 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">No waste reports have been recorded yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($recentWaste as $waste)
                            <li wire:key="recent-waste-{{ $waste['id'] }}" class="flex items-center justify-between gap-4 py-4">
                                <div>
                                    <h4 class="font-medium text-slate-900 dark:text-white">{{ $waste['type_label'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $waste['date'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium tabular-nums text-slate-900 dark:text-white">{{ $waste['weight_kg'] }} kg</p>
                                    @if ($waste['earnings'] > 0)
                                        <p class="mt-1 text-sm tabular-nums text-emerald-700 dark:text-emerald-400">₹{{ number_format($waste['earnings'], 2) }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</div>
