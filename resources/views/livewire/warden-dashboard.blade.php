@php
    $currentWarden = auth()->guard('warden')->user();
@endphp

<div class="space-y-7 sm:space-y-9">
    <header class="relative isolate overflow-hidden rounded-3xl bg-slate-950 px-6 py-8 text-white shadow-xl shadow-slate-900/10 sm:px-9 sm:py-10 lg:px-11"
        style="background-image: linear-gradient(105deg, rgba(6, 25, 21, .94), rgba(6, 25, 21, .62), rgba(6, 25, 21, .28)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1800&q=85'); background-size: cover; background-position: center;">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 font-ui text-xs font-semibold tracking-wide text-emerald-100 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                Warden workspace
            </span>
            <h1 class="mt-5 font-display text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">Hostel administration</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80 sm:text-base">
                Welcome, {{ $currentWarden->name }}. Manage contractor access and keep {{ $currentWarden->hostel }} running smoothly.
            </p>
        </div>
    </header>

    @if ($statusMessage)
        <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 shadow-sm dark:border-emerald-900/70 dark:bg-emerald-950/60 dark:text-emerald-200">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300" aria-hidden="true">✓</span>
            <p>{{ $statusMessage }}</p>
        </div>
    @endif
    @error('email')
        <p role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 dark:border-rose-900/70 dark:bg-rose-950/60 dark:text-rose-200">{{ $message }}</p>
    @enderror
    @error('deletion')
        <p role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800 dark:border-rose-900/70 dark:bg-rose-950/60 dark:text-rose-200">{{ $message }}</p>
    @enderror

    <section aria-label="Hostel overview" class="grid gap-4 sm:grid-cols-3">
        <article class="card flex items-center gap-4 p-5 sm:p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 11H8a4 4 0 110-8h6a4 4 0 010 8z"/></svg>
            </span>
            <div>
                <p class="font-ui text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Active contractors</p>
                <p class="mt-1 font-display text-3xl font-bold text-slate-900 dark:text-white">{{ $contractors->count() }}</p>
            </div>
        </article>
        <article class="card flex items-center gap-4 p-5 sm:p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5.2-3.82M17 20H7m10 0v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2h4m10-13a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </span>
            <div>
                <p class="font-ui text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Affiliated students</p>
                <p class="mt-1 font-display text-3xl font-bold text-slate-900 dark:text-white">{{ $approvedStudentCount }}</p>
            </div>
        </article>
        <article class="card flex items-center gap-4 p-5 sm:p-6">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <p class="font-ui text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Deactivated</p>
                <p class="mt-1 font-display text-3xl font-bold text-slate-900 dark:text-white">{{ $archivedContractors->count() }}</p>
            </div>
        </article>
    </section>

    <section class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(16rem,.8fr)]">
        <div class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-5 dark:border-slate-800 sm:px-7">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="font-ui text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-400">{{ $editingContractorId ? 'Update account' : 'Team access' }}</p>
                        <h2 class="mt-1 font-display text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">{{ $editingContractorId ? 'Edit contractor details' : 'Add or connect a contractor' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            @if ($editingContractorId)
                                Leave the password blank to keep it unchanged. A new password must be changed after sign-in.
                            @else
                                Create a contractor account or connect an unassigned account. They’ll change their initial password on first sign-in.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <form wire:submit="{{ $editingContractorId ? 'updateContractor' : 'addContractor' }}" class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2 sm:p-7">
                <div>
                    <label for="contractor-name" class="label">Full name</label>
                    <input id="contractor-name" type="text" wire:model="contractorName" autocomplete="name" placeholder="e.g. Asha Rao" required class="input !py-3">
                    @error('contractorName') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-employee" class="label">Employee ID</label>
                    <input id="contractor-employee" type="text" wire:model="employeeId" autocomplete="off" placeholder="Enter employee ID" required class="input !py-3">
                    @error('employeeId') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-email" class="label">Work email</label>
                    <input id="contractor-email" type="email" wire:model="email" autocomplete="email" placeholder="name@college.edu" required class="input !py-3">
                    @error('email') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-phone" class="label">Mobile number</label>
                    <input id="contractor-phone" type="tel" wire:model="phone" autocomplete="tel" placeholder="+91…" required class="input !py-3">
                    @error('phone') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-password" class="label">{{ $editingContractorId ? 'New password (optional)' : 'Initial password' }}</label>
                    <div x-data="{ visible: false }" class="relative">
                        <input id="contractor-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="new-password" @if (!$editingContractorId) required @endif class="input !py-3 pr-12">
                        <x-password-visibility-toggle />
                    </div>
                    @error('password') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-password-confirmation" class="label">Confirm {{ $editingContractorId ? 'new password' : 'password' }}</label>
                    <div x-data="{ visible: false }" class="relative">
                        <input id="contractor-password-confirmation" :type="visible ? 'text' : 'password'" wire:model="passwordConfirmation" autocomplete="new-password" @if (!$editingContractorId) required @endif class="input !py-3 pr-12">
                        <x-password-visibility-toggle />
                    </div>
                    @error('passwordConfirmation') <p role="alert" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5 dark:border-slate-800 sm:col-span-2">
                    <button type="submit" class="btn-primary min-w-48 !py-3" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $editingContractorId ? 'Save contractor details' : 'Create contractor account' }}</span>
                        <span wire:loading>Saving…</span>
                    </button>
                    @if ($editingContractorId)
                        <button type="button" wire:click="cancelContractorEdit" class="btn-ghost !py-3">Cancel editing</button>
                    @endif
                </div>
            </form>
        </div>

        <aside class="rounded-3xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-5 dark:border-emerald-900/60 dark:from-emerald-950/60 dark:via-slate-900 dark:to-teal-950/50 sm:p-6">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-emerald-700 shadow-sm dark:bg-slate-800 dark:text-emerald-300" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <h2 class="mt-5 font-display text-xl font-bold text-slate-900 dark:text-white">A secure first sign-in</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Give the contractor their sign-in details privately. They’ll be asked to choose a new password the first time they enter the portal.</p>
            <ul class="mt-5 grid gap-3 text-sm text-slate-700 dark:text-slate-200">
                <li class="flex items-start gap-2.5"><span class="mt-0.5 font-bold text-emerald-700 dark:text-emerald-400" aria-hidden="true">✓</span><span>Use a unique initial password</span></li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 font-bold text-emerald-700 dark:text-emerald-400" aria-hidden="true">✓</span><span>Keep their email and employee ID accurate</span></li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 font-bold text-emerald-700 dark:text-emerald-400" aria-hidden="true">✓</span><span>Accounts are linked to {{ $currentWarden->hostel }}</span></li>
            </ul>
        </aside>
    </section>

    <section class="card overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div>
                <p class="font-ui text-xs font-bold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-400">Your hostel team</p>
                <h2 class="mt-1 font-display text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">Active contractors</h2>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 font-ui text-xs font-semibold text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                {{ $contractors->count() }} {{ \Illuminate\Support\Str::plural('account', $contractors->count()) }}
            </span>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($contractors as $contractor)
                <article wire:key="contractor-{{ $contractor->id }}" class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 font-display text-lg font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-200" aria-hidden="true">{{ mb_strtoupper(mb_substr($contractor->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-ui font-bold text-slate-900 dark:text-white">{{ $contractor->name }}</h3>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 font-ui text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $contractor->employee_id }}</span>
                            </div>
                            <p class="mt-1 break-all text-sm text-slate-600 dark:text-slate-300">{{ $contractor->email }}</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $contractor->phone }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 font-ui text-xs font-semibold text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active
                        </span>
                        <button type="button" wire:click="editContractor({{ $contractor->id }})" class="btn-ghost !px-3 !py-2 text-sm">Edit</button>
                        <button type="button" wire:click="archiveContractor({{ $contractor->id }})" wire:confirm="Deactivate this contractor? Their account and records will be preserved, and they will no longer be able to sign in." class="rounded-xl px-3 py-2 font-ui text-sm font-semibold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:text-rose-300 dark:hover:bg-rose-950">Deactivate</button>
                    </div>
                </article>
            @empty
                <div class="px-6 py-12 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 11H8a4 4 0 110-8h6a4 4 0 010 8z"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-bold text-slate-900 dark:text-white">No contractors yet</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create or connect your first contractor account above.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($archivedContractors->isNotEmpty())
        <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 dark:border-slate-800 sm:px-7">
                <div>
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Account history</p>
                    <h2 class="mt-1 font-display text-xl font-bold text-slate-900 dark:text-white">Deactivated contractors</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 font-ui text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $archivedContractors->count() }}</span>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($archivedContractors as $contractor)
                    <article wire:key="archived-contractor-{{ $contractor->id }}" class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <div class="min-w-0">
                            <p class="font-ui font-semibold text-slate-700 dark:text-slate-200">{{ $contractor->name }} <span class="font-normal text-slate-500">· {{ $contractor->employee_id }}</span></p>
                            <p class="mt-1 break-all text-sm text-slate-500 dark:text-slate-400">{{ $contractor->email }} · {{ $contractor->phone }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="restoreContractor({{ $contractor->id }})" class="btn-ghost !px-3 !py-2 text-sm">Reactivate</button>
                            <button type="button" wire:click="permanentlyDeleteContractor({{ $contractor->id }})" wire:confirm="Permanently delete this deactivated contractor account? This cannot be undone. Student accounts will remain but be unaffiliated." class="rounded-xl px-3 py-2 font-ui text-sm font-semibold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:text-rose-300 dark:hover:bg-rose-950">Permanently delete</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
