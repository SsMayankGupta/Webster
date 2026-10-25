<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">People operations</p>
            <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Leave approvals</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review student leave requests and manage their status.</p>
        </header>

        <nav aria-label="Filter leave requests" class="flex flex-wrap gap-x-6 border-b border-slate-200 dark:border-slate-800">
            @foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $filterValue => $filterLabel)
                <button
                    wire:click="setFilter('{{ $filterValue }}')"
                    aria-pressed="{{ $filter === $filterValue ? 'true' : 'false' }}"
                    @class([
                        'border-b-2 px-1 py-3 text-sm font-medium transition',
                        'border-emerald-600 text-emerald-700 dark:text-emerald-400' => $filter === $filterValue,
                        'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' => $filter !== $filterValue,
                    ])
                >
                    {{ $filterLabel }}
                </button>
            @endforeach
        </nav>

        <section aria-labelledby="leave-requests-heading">
            <div class="mb-4 flex items-baseline justify-between gap-4">
                <h3 id="leave-requests-heading" class="text-xl font-medium text-slate-900 dark:text-white">Leave requests</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($leaves) }} {{ count($leaves) === 1 ? 'request' : 'requests' }}</span>
            </div>

            @if (empty($leaves))
                <div class="border-y border-slate-200 py-8 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    No leave requests found.
                </div>
            @else
                <div class="divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                    @foreach ($leaves as $leave)
                        <article wire:key="leave-request-{{ $leave['id'] }}" class="flex flex-col gap-4 py-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 space-y-3">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize',
                                        'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' => $leave['status'] === 'pending',
                                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' => $leave['status'] === 'approved',
                                        'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300' => $leave['status'] === 'rejected',
                                    ])>{{ $leave['status'] }}</span>
                                    <h4 class="font-medium text-slate-900 dark:text-white">{{ $leave['student_name'] }}</h4>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $leave['student_college_id'] }}</span>
                                </div>

                                <dl class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                                    <div class="flex gap-2">
                                        <dt class="text-slate-500 dark:text-slate-400">From</dt>
                                        <dd class="text-slate-800 dark:text-slate-200">{{ $leave['from_date'] }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="text-slate-500 dark:text-slate-400">To</dt>
                                        <dd class="text-slate-800 dark:text-slate-200">{{ $leave['to_date'] }}</dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="text-slate-500 dark:text-slate-400">Duration</dt>
                                        <dd class="text-slate-800 dark:text-slate-200">{{ $leave['days'] }} day(s)</dd>
                                    </div>
                                    @if ($leave['rebate_amount'] > 0)
                                        <div class="flex gap-2">
                                            <dt class="text-slate-500 dark:text-slate-400">Rebate</dt>
                                            <dd class="font-medium tabular-nums text-emerald-700 dark:text-emerald-400">₹{{ number_format($leave['rebate_amount'], 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>

                                @if ($leave['reason'])
                                    <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                        <span class="text-slate-500 dark:text-slate-500">Reason:</span>
                                        {{ $leave['reason'] }}
                                    </p>
                                @endif
                            </div>

                            @if ($leave['status'] === 'pending')
                                <div class="flex shrink-0 gap-2">
                                    <button
                                        wire:click="approveLeave({{ $leave['id'] }})"
                                        class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-800"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        wire:click="rejectLeave({{ $leave['id'] }})"
                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    >
                                        Reject
                                    </button>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
