<main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 dark:bg-slate-950">
    <section class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-9">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Restricted access</p>
        <h1 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">Warden sign in</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">This specialized portal is not listed on the public landing page.</p>
        <form wire:submit="login" class="mt-7 space-y-5">
            <div>
                <label for="warden-identifier" class="label">Email or employee ID</label>
                <input id="warden-identifier" type="text" wire:model="identifier" autocomplete="username" required class="input">
                @error('identifier') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="warden-password" class="label">Password</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="warden-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="current-password" required class="input pr-16">
                    <x-password-visibility-toggle />
                </div>
                @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">Sign in</button>
        </form>
    </section>
</main>
