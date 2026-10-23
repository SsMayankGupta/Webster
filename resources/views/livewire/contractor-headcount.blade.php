<div>
    <div class="space-y-6">
        <!-- Date Filter -->
        <div class="flex items-center gap-4">
            <button
                wire:click="filterByDate('{{ now()->format('Y-m-d') }}')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $selectedDate === now()->format('Y-m-d') ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Today
            </button>
            <button
                wire:click="filterByDate('{{ now()->addDay()->format('Y-m-d') }}')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $selectedDate === now()->addDay()->format('Y-m-d') ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Tomorrow
            </button>
            <button
                wire:click="loadMenus"
                class="rounded-lg px-4 py-2 text-sm font-medium bg-slate-200 text-slate-700 hover:bg-slate-300"
            >
                Next 7 Days
            </button>
        </div>

        <!-- Headcount Table -->
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/50">
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Meal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Date & Time</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Items</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Booked</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Locked</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $menu['meal_label'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $menu['serve_at'] }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ implode(', ', array_slice($menu['items'], 0, 3)) }}{{ count($menu['items']) > 3 ? '...' : '' }}</td>
                                <td class="px-4 py-3 text-center text-slate-900 dark:text-slate-100">{{ $menu['booked_count'] }}</td>
                                <td class="px-4 py-3 text-center text-slate-900 dark:text-slate-100">{{ $menu['headcount_locked'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $menu['status'] === 'open' ? 'bg-green-100 text-green-700' : ($menu['status'] === 'locked' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $menu['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-400">
                <strong>Note:</strong> Meals are automatically locked 4 hours before serving time. Once locked, the headcount is frozen and no further bookings can be made.
            </p>
        </div>
    </div>
</div>
