@php
    $billing = $state['billing'];
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
    <div class="space-y-4">
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-extrabold tracking-tight">Request leave</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Approved days cancel your meals and credit a rebate to your ledger.
                    </p>
                </div>
                <span class="badge-info">Multi-day</span>
            </div>

            <form wire:submit="requestLeave" class="mt-5 space-y-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="leave-from" class="label">From</label>
                        <input id="leave-from" type="date" wire:model="leaveFrom" class="input @error('leaveFrom') !border-rose-400 @enderror">
                        @error('leaveFrom')
                            <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="leave-to" class="label">To</label>
                        <input id="leave-to" type="date" wire:model="leaveTo" class="input @error('leaveTo') !border-rose-400 @enderror">
                        @error('leaveTo')
                            <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="leave-reason" class="label">Reason <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="leave-reason" type="text" wire:model="leaveReason" maxlength="255" class="input" placeholder="Home visit, medical leave, exam break…">
                    @error('leaveReason')
                        <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="requestLeave">
                    <svg wire:loading wire:target="requestLeave" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="requestLeave">Submit leave request</span>
                    <span wire:loading wire:target="requestLeave">Submitting…</span>
                </button>

                <p class="text-[11px] text-slate-400">
                    Rebate rate: ₹{{ number_format((int) config('mess.rebate_per_day_paise') / 100, 2) }} per approved day, posted atomically as integer paise.
                </p>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-display text-base font-bold">Leave history</h2>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($state['leaves'] as $leave)
                    <div class="flex items-start justify-between gap-3 px-5 py-4" wire:key="leave-{{ $leave['id'] }}">
                        <div>
                            <p class="text-sm font-semibold">{{ $leave['from_label'] }} → {{ $leave['to_label'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ $leave['days'] }} day{{ $leave['days'] === 1 ? '' : 's' }}
                                @if ($leave['reason']) · {{ $leave['reason'] }} @endif
                            </p>
                            @if ($leave['rebate_paise'] > 0)
                                <p class="mt-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    + ₹{{ number_format($leave['rebate_paise'] / 100, 2) }} rebate credited
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if ($leave['status'] === 'approved')
                                <span class="badge-success">Approved</span>
                            @elseif ($leave['status'] === 'rejected')
                                <span class="badge-danger">Rejected</span>
                            @else
                                <span class="badge-warning">Pending</span>
                            @endif
                            <p class="mt-1 text-[11px] text-slate-400">Filed {{ $leave['created_at'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-slate-400">No leave requests yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="flex flex-col justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center dark:border-slate-800">
            <div>
                <h2 class="font-display text-xl font-extrabold tracking-tight">Monthly statement</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Base charge minus rebates, in integer paise.</p>
            </div>
            <select wire:model.live="billingMonth" class="input !w-auto !py-2 text-sm">
                @foreach ($state['billing_months'] as $month)
                    <option value="{{ $month['month'] }}">{{ $month['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-3 p-5 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Base charge</p>
                <p class="mt-1 font-display text-xl font-extrabold">₹{{ number_format($billing['base_paise'] / 100, 2) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Rebates applied</p>
                <p class="mt-1 font-display text-xl font-extrabold text-emerald-700 dark:text-emerald-300">− ₹{{ number_format($billing['rebate_paise'] / 100, 2) }}</p>
            </div>
            <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/40 dark:bg-brand-500/10">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">Net payable</p>
                <p class="mt-1 font-display text-xl font-extrabold text-brand-800 dark:text-brand-200">₹{{ number_format($billing['net_paise'] / 100, 2) }}</p>
            </div>
        </div>

        <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
            <h3 class="text-sm font-semibold">Ledger entries · {{ $billing['label'] }}</h3>

            <div class="mt-3 divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($billing['entries'] as $entry)
                    <div class="flex items-center justify-between gap-3 py-2.5 text-sm" wire:key="rebate-{{ $entry['id'] }}">
                        <div>
                            <p class="font-medium">{{ $entry['reason'] }}</p>
                            <p class="text-xs text-slate-400">{{ $entry['date'] }}</p>
                        </div>
                        <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">+ ₹{{ number_format($entry['amount_paise'] / 100, 2) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">No rebates posted for this month.</p>
                @endforelse
            </div>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-3 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-900/40">
            Rebates post only when a leave request is approved. The ledger is append-only and never edited.
        </div>
    </div>
</div>
