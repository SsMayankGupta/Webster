<main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 font-sans dark:bg-slate-950">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <a href="/" class="font-display text-xl font-bold text-slate-900 dark:text-white">
                ZeroWasteMess<span class="text-brand-600 dark:text-brand-400">.io</span>
            </a>
            <h1 class="mt-8 font-display text-2xl font-bold text-slate-900 dark:text-white">Contractor sign in</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Use the email address or employee ID provided by your warden.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            @if ($statusMessage)
                <p role="status" class="mb-5 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">{{ $statusMessage }}</p>
            @endif
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="contractor-identifier" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email or employee ID</label>
                    <input id="contractor-identifier" type="text" wire:model="identifier" autocomplete="username" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                        placeholder="you@college.edu or employee ID">
                    @error('identifier') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contractor-password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                    <div x-data="{ visible: false }" class="relative">
                        <input id="contractor-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="current-password" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 pr-16 text-sm text-slate-900 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <x-password-visibility-toggle />
                    </div>
                    @error('password') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" class="w-full rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-60">Sign in</button>
            </form>
        </div>
    </div>
</main>
