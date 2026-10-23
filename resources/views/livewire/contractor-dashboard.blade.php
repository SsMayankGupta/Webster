<div>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Meals Today</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['total_meals_today'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Headcount</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['headcount'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Pending Waste Reports</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats['pending_waste_reports'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Meals -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Today's Meals</h3>
            
            @if(empty($todayMenus))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No meals scheduled for today
                </div>
            @else
                <div class="space-y-4">
                    @foreach($todayMenus as $menu)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $menu['status'] === 'open' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $menu['status'] }}
                                    </span>
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $menu['meal_label'] }}</span>
                                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ $menu['serve_at'] }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ implode(', ', $menu['items']) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Waste Reports -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Recent Waste Reports</h3>
            
            @if(empty($recentWaste))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No waste reports yet
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentWaste as $waste)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div>
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($waste['meal_type']) }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $waste['date'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $waste['amount_kg'] }} kg</p>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $waste['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $waste['status'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
