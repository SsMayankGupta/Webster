<div>
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Today’s meal headcount</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Student opt-ins by meal for {{ today()->format('D, d M Y') }}.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/50">
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Meal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Date & Time</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Items</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Opted in</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $menu['meal_label'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $menu['serve_at'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ implode(', ', array_slice($menu['items'], 0, 3)) }}{{ count($menu['items']) > 3 ? '...' : '' }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-900 dark:text-slate-100">{{ $menu['opted_in_count'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $menu['status'] === 'open' ? 'bg-green-100 text-green-700' : ($menu['status'] === 'locked' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $menu['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                    No meals are scheduled for today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                <strong>Note:</strong> Opt-in counts include confirmed and already-consumed meals. Cancelled bookings are excluded. Meals lock 4 hours before serving.
            </p>
        </div>
    </div>
</div>
