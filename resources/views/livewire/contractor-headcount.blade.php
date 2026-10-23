<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Daily operations</p>
            <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Today’s meal headcount</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Student opt-ins by meal for {{ today()->format('D, d M Y') }}.
            </p>
        </header>

        <section aria-label="Today's meal headcount" class="border-y border-slate-200 dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Meal</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Date &amp; time</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Items</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Opted in</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($menus as $menu)
                            <tr wire:key="headcount-menu-{{ $menu['id'] }}">
                                <th scope="row" class="px-4 py-4 text-left font-medium text-slate-900 dark:text-white">{{ $menu['meal_label'] }}</th>
                                <td class="whitespace-nowrap px-4 py-4 tabular-nums text-slate-600 dark:text-slate-400">{{ $menu['serve_at'] }}</td>
                                <td class="min-w-48 px-4 py-4 text-slate-600 dark:text-slate-400">
                                    @if ($menu['items'] !== [])
                                        {{ implode(', ', array_slice($menu['items'], 0, 3)) }}{{ count($menu['items']) > 3 ? '…' : '' }}
                                    @else
                                        <span class="italic text-slate-400 dark:text-slate-500">No items listed</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center text-base font-medium tabular-nums text-slate-900 dark:text-white">{{ $menu['opted_in_count'] }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span @class([
                                        'inline-flex items-center px-2 py-1 text-xs font-medium capitalize',
                                        'text-emerald-700 dark:text-emerald-400' => $menu['status'] === 'open',
                                        'text-amber-700 dark:text-amber-400' => $menu['status'] === 'locked',
                                        'text-slate-500 dark:text-slate-400' => ! in_array($menu['status'], ['open', 'locked'], true),
                                    ])>
                                        {{ $menu['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <p class="text-base font-medium text-slate-800 dark:text-slate-200">No meals scheduled today</p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Today’s meal headcount will appear here once menus are added.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="border-l-2 border-slate-300 pl-4 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-400">
            <p><span class="font-medium text-slate-800 dark:text-slate-200">About these counts.</span> Opt-in totals include confirmed and already-consumed meals. Cancelled bookings are excluded. Meals lock four hours before serving.</p>
        </aside>
    </div>
</div>
