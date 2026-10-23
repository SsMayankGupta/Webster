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
                wire:click="setFilter('under_review')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'under_review' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Under Review
            </button>
            <button
                wire:click="setFilter('resolved')"
                class="rounded-lg px-4 py-2 text-sm font-medium {{ $filter === 'resolved' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}"
            >
                Resolved
            </button>
        </div>

        <!-- Tickets -->
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            @if(empty($tickets))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No tickets found
                </div>
            @else
                <div class="space-y-4">
                    @foreach($tickets as $ticket)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $ticket['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : ($ticket['status'] === 'under_review' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                            {{ str_replace('_', ' ', $ticket['status']) }}
                                        </span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $ticket['student_name'] }}</span>
                                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $ticket['student_college_id'] }}</span>
                                    </div>
                                    <h4 class="mt-2 font-semibold text-slate-900 dark:text-slate-100">{{ $ticket['subject'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $ticket['description'] }}</p>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">{{ $ticket['created_at'] }}</p>
                                </div>
                                
                                <div class="flex gap-2">
                                    @if($ticket['status'] === 'pending')
                                        <button
                                            wire:click="updateStatus({{ $ticket['id'] }}, 'under_review')"
                                            class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            Review
                                        </button>
                                    @elseif($ticket['status'] === 'under_review')
                                        <button
                                            wire:click="updateStatus({{ $ticket['id'] }}, 'resolved')"
                                            class="rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                        >
                                            Resolve
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
