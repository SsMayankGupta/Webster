<div class="font-lora">
    <div class="space-y-8">
        <header class="border-b border-slate-200 pb-5 dark:border-slate-800">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">People operations</p>
            <h2 class="mt-2 text-3xl font-medium tracking-tight text-slate-900 dark:text-white sm:text-4xl">Leave approvals</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Review student leave requests, record fees and meal refunds, and track transfers.</p>
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

        <section aria-labelledby="student-wallet-heading" class="space-y-5 border-t border-slate-200 pt-7 dark:border-slate-800">
            <header>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-400">Student finance</p>
                <h3 id="student-wallet-heading" class="mt-2 text-2xl font-medium text-slate-900 dark:text-white">Fees and meal refunds</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Record fee payments and refunds against an approved leave. Transfer status is tracking only; no payment is initiated here.</p>
            </header>

            <form wire:submit="saveSemesterPeriod" class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <h4 class="font-medium text-slate-900 dark:text-white">Semester total period</h4>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You can edit these dates during the semester. Totals recalculate from the saved dates; transaction history is retained.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="semester-name" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Semester name</label>
                        <input id="semester-name" type="text" maxlength="100" wire:model="semesterName" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Semester 1">
                        @error('semesterName')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="semester-start" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Start date</label>
                        <input id="semester-start" type="date" wire:model="semesterStart" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('semesterStart')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="semester-end" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">End date</label>
                        <input id="semester-end" type="date" wire:model="semesterEnd" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('semesterEnd')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="rounded-lg border border-emerald-700 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-950/40" wire:loading.attr="disabled" wire:target="saveSemesterPeriod">Save semester dates</button>
            </form>

            <div class="max-w-xl">
                <label for="wallet-student" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Student</label>
                <select id="wallet-student" wire:model.live="selectedStudentId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">Select a student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} · {{ $student->college_id }}{{ $student->archived_at ? ' · Deactivated' : '' }}</option>
                    @endforeach
                </select>
                @if ($students->isEmpty())
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No students are assigned to your account.</p>
                @endif
            </div>

            @if ($walletStudent)
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fees submitted · {{ $semesterRange ? $semesterName : 'all time (semester not set)' }}</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-slate-900 dark:text-white">₹{{ number_format($feeTotalPaise / 100, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Meal refunds · {{ $semesterRange ? $semesterName : 'all time (semester not set)' }}</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-emerald-800 dark:text-emerald-300">₹{{ number_format($refundTotalPaise / 100, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-500/40 dark:bg-brand-500/10">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">Approved-leave rebates · {{ $semesterRange ? $semesterName : 'all time' }}</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-brand-800 dark:text-brand-200">₹{{ number_format($leaveRebatePaise / 100, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/40">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Guest meals charged · all time</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-amber-800 dark:text-amber-300">₹{{ number_format($extraMealChargedPaise / 100, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Guest meals collected · all time</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-emerald-800 dark:text-emerald-300">₹{{ number_format($extraMealCollectedPaise / 100, 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-900 dark:bg-rose-950/40">
                        <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Guest meals outstanding · all time</p>
                        <p class="mt-1 font-lora text-2xl font-semibold text-rose-800 dark:text-rose-300">₹{{ number_format($extraMealOutstandingPaise / 100, 2) }}</p>
                    </div>
                </div>
                @if ($semesterEnded && $extraMealOutstandingPaise > 0)
                    <p role="status" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                        The configured semester has ended. Collect the outstanding guest-meal balance before deactivating this student; collections must reflect money actually received.
                    </p>
                @endif

                <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                    <form wire:submit="addFeePayment" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h4 class="font-medium text-slate-900 dark:text-white">Record fee payment</h4>
                        <div>
                            <label for="fee-amount" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Amount submitted (₹)</label>
                            <input id="fee-amount" type="number" min="0.01" step="0.01" wire:model="feeAmount" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="0.00">
                            @error('feeAmount')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-800" wire:loading.attr="disabled" wire:target="addFeePayment">Add fee payment</button>
                    </form>

                    <form wire:submit="addMealRefund" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <h4 class="font-medium text-slate-900 dark:text-white">Record meal refund</h4>
                        <div>
                            <label for="refund-leave" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Approved leave</label>
                            <select id="refund-leave" wire:model="selectedLeaveId" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                                <option value="">Select an approved leave</option>
                                @foreach ($approvedLeaves as $approvedLeave)
                                    <option value="{{ $approvedLeave->id }}">{{ $approvedLeave->from_date->format('d M Y') }} – {{ $approvedLeave->to_date->format('d M Y') }}</option>
                                @endforeach
                            </select>
                            @error('selectedLeaveId')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="refund-amount" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Meal refund (₹)</label>
                            <input id="refund-amount" type="number" min="0.01" step="0.01" wire:model="refundAmount" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="0.00">
                            @error('refundAmount')
                                <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" @disabled($approvedLeaves->isEmpty()) class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition enabled:hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50" wire:loading.attr="disabled" wire:target="addMealRefund">Add meal refund</button>
                        @if ($approvedLeaves->isEmpty())
                            <p class="text-xs text-slate-500 dark:text-slate-400">This student has no approved leave to attach a refund to.</p>
                        @endif
                    </form>

                    <form wire:submit="addExtraMealCollection" class="space-y-3 rounded-xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900 dark:bg-amber-950/20">
                        <h4 class="font-medium text-slate-900 dark:text-white">Record guest-meal payment</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Outstanding balance: ₹{{ number_format($extraMealOutstandingPaise / 100, 2) }}. Record only money actually received.</p>
                        <div>
                            <label for="extra-meal-amount" class="mb-1 block text-sm text-slate-600 dark:text-slate-400">Amount collected (₹)</label>
                            <input id="extra-meal-amount" type="number" min="0.01" max="{{ number_format($extraMealOutstandingPaise / 100, 2, '.', '') }}" step="0.01" wire:model="extraMealAmount" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="0.00">
                            @error('extraMealAmount')
                                <p role="alert" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" @disabled($extraMealOutstandingPaise <= 0) class="rounded-lg bg-amber-700 px-3 py-2 text-sm font-medium text-white transition enabled:hover:bg-amber-800 disabled:cursor-not-allowed disabled:opacity-50" wire:loading.attr="disabled" wire:target="addExtraMealCollection">Record collection</button>
                    </form>
                </div>

                <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <h4 class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">Fee payment history</h4>
                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @forelse ($walletStudent->feePayments as $payment)
                                <div wire:key="student-fee-{{ $payment->id }}" class="flex items-center justify-between gap-3 py-3 text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">{{ $payment->created_at->format('d M Y') }}</span>
                                    <span class="font-semibold tabular-nums text-slate-900 dark:text-white">₹{{ number_format($payment->amount_paise / 100, 2) }}</span>
                                </div>
                            @empty
                                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No fee payments recorded.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        <h4 class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">Meal refund history</h4>
                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @forelse ($walletStudent->mealRefunds as $refund)
                                <div wire:key="meal-refund-{{ $refund->id }}" class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="text-sm">
                                        <p class="font-semibold tabular-nums text-slate-900 dark:text-white">₹{{ number_format($refund->amount_paise / 100, 2) }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Leave {{ $refund->leaveRequest->from_date->format('d M Y') }} – {{ $refund->leaveRequest->to_date->format('d M Y') }}
                                            · recorded {{ $refund->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    @if ($refund->status === 'transferred')
                                        <span class="inline-flex w-fit rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">Transferred {{ $refund->transferred_at->format('d M Y') }}</span>
                                    @else
                                        <button type="button" wire:click="markRefundTransferred({{ $refund->id }})" class="w-fit rounded-lg border border-emerald-700 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-950/40" wire:loading.attr="disabled" wire:target="markRefundTransferred({{ $refund->id }})">Mark as transferred</button>
                                    @endif
                                </div>
                            @empty
                                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No meal refunds recorded.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-white dark:border-amber-900 dark:bg-slate-900">
                        <h4 class="border-b border-amber-100 px-4 py-3 text-sm font-semibold text-slate-900 dark:border-amber-950 dark:text-white">Guest-meal charge history</h4>
                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @forelse ($walletStudent->extraMealCharges as $charge)
                                <div wire:key="guest-charge-{{ $charge->id }}" class="flex items-center justify-between gap-3 py-3 text-sm">
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ ucfirst($charge->booking?->menu?->meal_type ?? 'Guest meal') }} · {{ $charge->guest_count }} relative(s)</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $charge->created_at->format('d M Y') }} · ₹{{ number_format($charge->price_per_guest_paise / 100, 2) }} each</p>
                                    </div>
                                    <span class="font-semibold tabular-nums text-slate-900 dark:text-white">₹{{ number_format($charge->amount_paise / 100, 2) }}</span>
                                </div>
                            @empty
                                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No guest-meal charges recorded.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-white dark:border-emerald-900 dark:bg-slate-900">
                        <h4 class="border-b border-emerald-100 px-4 py-3 text-sm font-semibold text-slate-900 dark:border-emerald-950 dark:text-white">Guest-meal collection history</h4>
                        <div class="divide-y divide-slate-100 px-4 dark:divide-slate-800">
                            @forelse ($walletStudent->extraMealCollections as $collection)
                                <div wire:key="guest-collection-{{ $collection->id }}" class="flex items-center justify-between gap-3 py-3 text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">{{ $collection->created_at->format('d M Y') }}</span>
                                    <span class="font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">₹{{ number_format($collection->amount_paise / 100, 2) }}</span>
                                </div>
                            @empty
                                <p class="py-5 text-sm text-slate-500 dark:text-slate-400">No guest-meal collections recorded.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
