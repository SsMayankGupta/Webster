@php
    $billing = $state['billing'];
@endphp

<div wire:poll.60s="refreshMealState" class="grid gap-4 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
    <div class="space-y-4">
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-lora text-xl font-semibold tracking-tight">Request leave</h2>
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

                    <section aria-labelledby="student-wallet-heading" class="card overflow-hidden xl:col-span-2">
                        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                            <h2 id="student-wallet-heading" class="font-lora text-xl font-semibold tracking-tight">My wallet</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Other totals cover {{ $state['wallet']['period_label'] }}; guest-meal balances are all-time so payments made after semester end still reduce the due amount.</p>
                        </div>

                        <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ([
                                ['label' => 'Fees submitted', 'amount' => $state['wallet']['fee_paid_paise'], 'class' => 'border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-800 dark:bg-slate-900/60 dark:text-white'],
                                ['label' => 'Meal refunds', 'amount' => $state['wallet']['meal_refund_paise'], 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300'],
                                ['label' => 'Transferred', 'amount' => $state['wallet']['transferred_refund_paise'], 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300'],
                                ['label' => 'Awaiting transfer', 'amount' => $state['wallet']['pending_refund_paise'], 'class' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300'],
                                ['label' => 'Approved-leave rebates', 'amount' => $state['wallet']['approved_leave_rebate_paise'], 'class' => 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-200'],
                                ['label' => 'Guest meals charged · all time', 'amount' => $state['wallet']['extra_meal_charged_paise'], 'class' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300'],
                                ['label' => 'Guest meals collected · all time', 'amount' => $state['wallet']['extra_meal_collected_paise'], 'class' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300'],
                                ['label' => 'Guest meals outstanding · all time', 'amount' => $state['wallet']['extra_meal_outstanding_paise'], 'class' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300'],
                            ] as $walletTotal)
                                <div class="rounded-xl border p-4 {{ $walletTotal['class'] }}">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider opacity-75">{{ $walletTotal['label'] }}</p>
                                    <p class="mt-1 font-lora text-xl font-semibold">₹{{ number_format($walletTotal['amount'] / 100, 2) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid gap-5 border-t border-slate-100 p-5 lg:grid-cols-2 dark:border-slate-800">
                            <section aria-labelledby="fee-payment-history-heading">
                                <h3 id="fee-payment-history-heading" class="text-sm font-semibold">Fee payments submitted</h3>
                                <div class="mt-2 divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse ($state['wallet']['fee_payments'] as $payment)
                                        <div wire:key="wallet-fee-{{ $payment['id'] }}" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                            <span class="text-slate-500 dark:text-slate-400">{{ $payment['date'] }}</span>
                                            <span class="font-semibold tabular-nums">₹{{ number_format($payment['amount_paise'] / 100, 2) }}</span>
                                        </div>
                                    @empty
                                        <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No fee payments have been recorded yet.</p>
                                    @endforelse
                                </div>
                            </section>

                            <section aria-labelledby="meal-refund-history-heading">
                                <h3 id="meal-refund-history-heading" class="text-sm font-semibold">Meal refund history</h3>
                                <div class="mt-2 divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse ($state['wallet']['refunds'] as $refund)
                                        <div wire:key="wallet-refund-{{ $refund['id'] }}" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                            <div>
                                                <p class="font-medium">₹{{ number_format($refund['amount_paise'] / 100, 2) }} · {{ ucfirst($refund['status']) }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    Approved leave {{ $refund['leave_from'] }} – {{ $refund['leave_to'] }}
                                                    · recorded {{ $refund['date'] }}
                                                    @if ($refund['transferred_at'])
                                                        · transferred {{ $refund['transferred_at'] }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No meal refunds have been recorded yet.</p>
                                    @endforelse
                                </div>
                            </section>

                            <section aria-labelledby="guest-meal-history-heading" class="lg:col-span-2">
                                <h3 id="guest-meal-history-heading" class="text-sm font-semibold">Guest-meal charges and collections</h3>
                                <div class="mt-2 grid gap-5 lg:grid-cols-2">
                                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <h4 class="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Charges</h4>
                                        @forelse ($state['wallet']['extra_meal_charges'] as $charge)
                                            <div wire:key="wallet-guest-charge-{{ $charge['id'] }}" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                                <div>
                                                    <p class="font-medium">{{ ucfirst($charge['meal_type'] ?? 'Guest meal') }} · {{ $charge['guest_count'] }} relative(s)</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $charge['date'] }} · ₹{{ number_format($charge['price_per_guest_paise'] / 100, 2) }} each</p>
                                                </div>
                                                <span class="font-semibold tabular-nums">₹{{ number_format($charge['amount_paise'] / 100, 2) }}</span>
                                            </div>
                                        @empty
                                            <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No guest-meal charges recorded.</p>
                                        @endforelse
                                    </div>
                                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <h4 class="pb-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Collections</h4>
                                        @forelse ($state['wallet']['extra_meal_collections'] as $collection)
                                            <div wire:key="wallet-guest-collection-{{ $collection['id'] }}" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                                                <span class="text-slate-500 dark:text-slate-400">{{ $collection['date'] }}</span>
                                                <span class="font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">₹{{ number_format($collection['amount_paise'] / 100, 2) }}</span>
                                            </div>
                                        @empty
                                            <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No guest-meal collections recorded.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </section>
                        </div>
                    </section>
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
                <h2 class="font-lora text-base font-semibold">Leave history</h2>
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
                            @if ($leave['ended_early'])
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    End date modified by contractor; originally approved through {{ $leave['requested_to_label'] }}.
                                </p>
                            @endif
                            @if ($leave['end_date_modifications'] !== [])
                                <div class="mt-2 space-y-2 border-l-2 border-slate-200 pl-3 dark:border-slate-700">
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">End-date modification history</p>
                                    @foreach ($leave['end_date_modifications'] as $modification)
                                        <div wire:key="student-leave-modification-{{ $leave['id'] }}-{{ $loop->index }}" class="text-xs text-slate-500 dark:text-slate-400">
                                            <p>
                                                {{ $modification['previous_to_date'] }} → {{ $modification['new_to_date'] }}
                                                · Modified by {{ $modification['modified_by_name'] }}
                                                · {{ $modification['modified_at'] }}
                                            </p>
                                            @if ($modification['rebate_adjustment_paise'] !== 0)
                                                <p @class([
                                                    'mt-0.5 font-medium',
                                                    'text-emerald-700 dark:text-emerald-400' => $modification['rebate_adjustment_paise'] > 0,
                                                    'text-amber-700 dark:text-amber-400' => $modification['rebate_adjustment_paise'] < 0,
                                                ])>
                                                    Rebate adjustment:
                                                    {{ $modification['rebate_adjustment_paise'] < 0 ? '− ' : '+' }}₹{{ number_format(abs($modification['rebate_adjustment_paise']) / 100, 2) }}
                                                </p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($leave['rebate_paise'] > 0)
                                <p class="mt-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    ₹{{ number_format($leave['rebate_paise'] / 100, 2) }} net rebate credited
                                </p>
                            @elseif ($leave['rebate_paise'] < 0)
                                <p class="mt-1 text-xs font-semibold text-amber-700 dark:text-amber-400">
                                    − ₹{{ number_format(abs($leave['rebate_paise']) / 100, 2) }} rebate adjustment
                                </p>
                            @elseif ($leave['status'] === 'approved')
                                <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                                    No additional rebate — these dates were already credited.
                                </p>
                            @elseif ($leave['estimated_rebate_paise'] > 0)
                                <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                                    Estimated rebate if approved: ₹{{ number_format($leave['estimated_rebate_paise'] / 100, 2) }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if ($leave['ended_early'])
                                <span class="badge-info">Ended early</span>
                            @elseif ($leave['status'] === 'approved')
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
                <h2 class="font-lora text-xl font-semibold tracking-tight">Monthly statement</h2>
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
                <p class="mt-1 font-lora text-xl font-semibold">₹{{ number_format($billing['base_paise'] / 100, 2) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Rebates applied</p>
                <p class="mt-1 font-lora text-xl font-semibold text-emerald-700 dark:text-emerald-300">− ₹{{ number_format($billing['rebate_paise'] / 100, 2) }}</p>
            </div>
            <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/40 dark:bg-brand-500/10">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">Net payable</p>
                <p class="mt-1 font-lora text-xl font-semibold text-brand-800 dark:text-brand-200">₹{{ number_format($billing['net_paise'] / 100, 2) }}</p>
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
