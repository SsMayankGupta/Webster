<div>
    <div class="space-y-6">
        <!-- Filter -->
        <div class="flex gap-2">
            <button
                wire:click="setFilter('all')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                All
            </button>
            <button
                wire:click="setFilter('pending')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'pending' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Pending
            </button>
            <button
                wire:click="setFilter('approved')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'approved' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Approved
            </button>
            <button
                wire:click="setFilter('rejected')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'rejected' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Rejected
            </button>
        </div>

        <!-- Leave Requests -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            @if(empty($leaves))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No leave requests found
                </div>
            @else
                <div class="space-y-4">
                    @foreach($leaves as $leave)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $leave['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($leave['status'] === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $leave['status'] }}
                                        </span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $leave['student_name'] }}</span>
                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $leave['student_college_id'] }}</span>
                                    </div>
                                    <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-slate-500 dark:text-slate-400">From:</span>
                                            <span class="ml-2 text-slate-900 dark:text-slate-100">{{ $leave['from_date'] }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 dark:text-slate-400">To:</span>
                                            <span class="ml-2 text-slate-900 dark:text-slate-100">{{ $leave['to_date'] }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span class="text-slate-500 dark:text-slate-400">Duration:</span>
                                        <span class="ml-2 text-slate-900 dark:text-slate-100">{{ $leave['days'] }} day(s)</span>
                                    </div>
                                    @if($leave['reason'])
                                        <div class="mt-2 text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">Reason:</span>
                                            <span class="ml-2 text-slate-900 dark:text-slate-100">{{ $leave['reason'] }}</span>
                                        </div>
                                    @endif
                                    @if($leave['rebate_amount'] > 0)
                                        <div class="mt-2 text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">Rebate:</span>
                                            <span class="ml-2 font-semibold text-emerald-600">₹{{ number_format($leave['rebate_amount'], 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($leave['status'] === 'pending')
                                    <div class="flex gap-2">
                                        <button
                                            wire:click="approveLeave({{ $leave['id'] }})"
                                            class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            wire:click="rejectLeave({{ $leave['id'] }})"
                                            class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
