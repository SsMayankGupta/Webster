<div>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="mb-4 text-lg font-semibold">Staff Members</h3>
            
            @if(empty($staff))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No staff members found
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/50">
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">College ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Email</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Hostel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staff as $member)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $member['name'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $member['college_id'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $member['email'] }}</td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $member['hostel'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
