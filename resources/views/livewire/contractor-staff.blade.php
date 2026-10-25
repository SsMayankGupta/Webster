<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">People operations</p>
            <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Staff directory</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">View staff members and their contact information.</p>
        </header>

        <section aria-labelledby="staff-members-heading">
            <div class="mb-4 flex items-baseline justify-between gap-4">
                <h3 id="staff-members-heading" class="text-xl font-medium text-slate-900 dark:text-white">Staff members</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($staff) }} {{ count($staff) === 1 ? 'member' : 'members' }}</span>
            </div>

            @if (empty($staff))
                <div class="border-y border-slate-200 py-8 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    No staff members found.
                </div>
            @else
                <div class="overflow-x-auto border-y border-slate-200 dark:border-slate-800">
                    <table class="w-full min-w-[36rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th scope="col" class="py-3 pr-4 font-medium text-slate-500 dark:text-slate-400">Name</th>
                                <th scope="col" class="px-4 py-3 font-medium text-slate-500 dark:text-slate-400">College ID</th>
                                <th scope="col" class="px-4 py-3 font-medium text-slate-500 dark:text-slate-400">Email</th>
                                <th scope="col" class="py-3 pl-4 font-medium text-slate-500 dark:text-slate-400">Hostel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($staff as $member)
                                <tr wire:key="staff-member-{{ $member['id'] }}">
                                    <td class="py-4 pr-4 font-medium text-slate-900 dark:text-white">{{ $member['name'] }}</td>
                                    <td class="px-4 py-4 tabular-nums text-slate-600 dark:text-slate-400">{{ $member['college_id'] }}</td>
                                    <td class="px-4 py-4 text-slate-600 dark:text-slate-400">{{ $member['email'] }}</td>
                                    <td class="py-4 pl-4 text-slate-600 dark:text-slate-400">{{ $member['hostel'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>
