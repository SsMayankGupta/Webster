<div class="space-y-8">
    <header>
        <h1 class="text-3xl font-bold tracking-tight">Hostel administration</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Create and manage contractors for {{ auth()->guard('warden')->user()->hostel }}.</p>
    </header>

    @if ($statusMessage)
        <p role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ $statusMessage }}</p>
    @endif
    @error('email') <p role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">{{ $message }}</p> @enderror
    @error('deletion') <p role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300">{{ $message }}</p> @enderror

    <section class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Affiliated students</p>
            <p class="mt-2 text-4xl font-bold">{{ $approvedStudentCount }}</p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Students added by your contractors.</p>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900 sm:p-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">{{ $editingContractorId ? 'Edit contractor details' : 'Add or connect a contractor' }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                @if ($editingContractorId)
                    Leave the password blank to keep it unchanged. If you set a new password, the contractor must change it after signing in.
                @else
                    A new account is created, or an unassigned legacy contractor account is connected. Contractors can sign in immediately with their email or employee ID and must change the initial password on first sign-in.
                @endif
            </p>
        </div>
        <form wire:submit="{{ $editingContractorId ? 'updateContractor' : 'addContractor' }}" class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="contractor-name" class="label">Full name</label>
                <input id="contractor-name" type="text" wire:model="contractorName" required class="input">
                @error('contractorName') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contractor-employee" class="label">Employee ID</label>
                <input id="contractor-employee" type="text" wire:model="employeeId" required class="input">
                @error('employeeId') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contractor-email" class="label">Email</label>
                <input id="contractor-email" type="email" wire:model="email" required class="input">
                @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contractor-phone" class="label">Mobile number</label>
                <input id="contractor-phone" type="tel" wire:model="phone" placeholder="+91…" required class="input">
                @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contractor-password" class="label">{{ $editingContractorId ? 'New password (optional)' : 'Initial password' }}</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="contractor-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="new-password" @if (!$editingContractorId) required @endif class="input pr-12">
                    <x-password-visibility-toggle />
                </div>
                @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contractor-password-confirmation" class="label">Confirm {{ $editingContractorId ? 'new password' : 'password' }}</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="contractor-password-confirmation" :type="visible ? 'text' : 'password'" wire:model="passwordConfirmation" autocomplete="new-password" @if (!$editingContractorId) required @endif class="input pr-12">
                    <x-password-visibility-toggle />
                </div>
                @error('passwordConfirmation') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">{{ $editingContractorId ? 'Save contractor details' : 'Create contractor account' }}</button>
                    @if ($editingContractorId)
                        <button type="button" wire:click="cancelContractorEdit" class="btn-ghost">Cancel editing</button>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
            <h2 class="text-xl font-semibold">Active contractors</h2>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($contractors as $contractor)
                <article wire:key="contractor-{{ $contractor->id }}" class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">{{ $contractor->name }} <span class="font-normal text-slate-500">· {{ $contractor->employee_id }}</span></p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $contractor->email }} · {{ $contractor->phone }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="editContractor({{ $contractor->id }})" class="btn-ghost !px-3 !py-2 text-sm">Edit</button>
                        <button type="button" wire:click="archiveContractor({{ $contractor->id }})" wire:confirm="Deactivate this contractor? Their account and records will be preserved, and they will no longer be able to sign in." class="rounded-lg px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950">Deactivate</button>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                    </div>
                </article>
            @empty
                <p class="px-6 py-10 text-center text-sm text-slate-500">No contractors have been added to this hostel.</p>
            @endforelse
        </div>
    </section>

    @if ($archivedContractors->isNotEmpty())
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <h2 class="text-xl font-semibold">Deactivated contractors</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($archivedContractors as $contractor)
                    <article wire:key="archived-contractor-{{ $contractor->id }}" class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold">{{ $contractor->name }} <span class="font-normal text-slate-500">· {{ $contractor->employee_id }}</span></p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $contractor->email }} · {{ $contractor->phone }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="restoreContractor({{ $contractor->id }})" class="btn-ghost !px-3 !py-2 text-sm">Reactivate</button>
                            <button type="button" wire:click="permanentlyDeleteContractor({{ $contractor->id }})" wire:confirm="Permanently delete this deactivated contractor account? This cannot be undone. Student accounts will remain but be unaffiliated." class="rounded-lg px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950">Permanently delete</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
