<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Student support</p>
            <h2 class="mt-2 text-2xl font-medium tracking-tight text-slate-900 dark:text-white">Ticket management</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review student requests and follow each ticket through resolution.</p>
        </header>

        <nav aria-label="Filter tickets" class="flex flex-wrap gap-x-5 border-b border-slate-200 dark:border-slate-800">
            @foreach ([
                'all' => 'All tickets',
                'pending' => 'Pending',
                'under_review' => 'Under review',
                'resolved' => 'Resolved',
            ] as $filterValue => $filterLabel)
                <button
                    type="button"
                    aria-pressed="{{ $filter === $filterValue ? 'true' : 'false' }}"
                    wire:click="setFilter('{{ $filterValue }}')"
                    @class([
                        'border-b-2 px-1 py-3 text-sm transition-colors',
                        'border-emerald-700 font-medium text-emerald-800 dark:border-emerald-400 dark:text-emerald-300' => $filter === $filterValue,
                        'border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' => $filter !== $filterValue,
                    ])
                >
                    {{ $filterLabel }}
                </button>
            @endforeach
        </nav>

        @if (empty($tickets))
            <div class="border-y border-slate-200 py-12 text-center dark:border-slate-800">
                <h3 class="text-base font-medium text-slate-800 dark:text-slate-200">No tickets found</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    @if ($filter === 'all')
                        Student requests will appear here when they are submitted.
                    @else
                        There are no {{ str_replace('_', ' ', $filter) }} tickets right now.
                    @endif
                </p>
            </div>
        @else
            <section aria-label="Tickets" class="divide-y divide-slate-200 border-y border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                @foreach ($tickets as $ticket)
                    <article wire:key="contractor-ticket-{{ $ticket['id'] }}" class="flex flex-col gap-4 py-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <span @class([
                                    'text-xs font-medium capitalize',
                                    'text-amber-700 dark:text-amber-400' => $ticket['status'] === 'pending',
                                    'text-sky-700 dark:text-sky-400' => $ticket['status'] === 'under_review',
                                    'text-emerald-700 dark:text-emerald-400' => $ticket['status'] === 'resolved',
                                    'text-slate-500 dark:text-slate-400' => ! in_array($ticket['status'], ['pending', 'under_review', 'resolved'], true),
                                ])>
                                    {{ str_replace('_', ' ', $ticket['status']) }}
                                </span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $ticket['student_name'] }}</span>
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $ticket['student_college_id'] }}</span>
                            </div>
                            <h3 class="mt-3 text-lg font-medium text-slate-900 dark:text-white">{{ $ticket['subject'] }}</h3>
                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $ticket['description'] }}</p>
                            <p class="mt-3 text-xs tabular-nums text-slate-500 dark:text-slate-500">{{ $ticket['created_at'] }}</p>
                        </div>

                        @if ($ticket['status'] === 'pending')
                            <button
                                type="button"
                                wire:click="updateStatus({{ $ticket['id'] }}, 'under_review')"
                                wire:loading.attr="disabled"
                                wire:target="updateStatus({{ $ticket['id'] }}, 'under_review')"
                                class="btn-ghost shrink-0 border border-slate-200 dark:border-slate-700 disabled:cursor-wait"
                            >
                                Move to review
                            </button>
                        @elseif ($ticket['status'] === 'under_review')
                            <button
                                type="button"
                                wire:click="updateStatus({{ $ticket['id'] }}, 'resolved')"
                                wire:loading.attr="disabled"
                                wire:target="updateStatus({{ $ticket['id'] }}, 'resolved')"
                                class="btn-primary shrink-0 disabled:cursor-wait"
                            >
                                Mark resolved
                            </button>
                        @endif
                    </article>
                @endforeach
            </section>
        @endif
    </div>
</div>
