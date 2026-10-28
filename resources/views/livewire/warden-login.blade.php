<main
    class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 font-sans sm:px-6"
    style="background-image: linear-gradient(110deg, rgba(15, 23, 42, .72), rgba(15, 23, 42, .38)), url('https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=2200&q=85'); background-size: cover; background-position: center;"
>
    <section class="w-full max-w-md rounded-3xl border border-white/60 bg-white/95 p-7 shadow-2xl shadow-slate-950/30 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/95 sm:p-9">
        <a href="/" class="text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400" aria-label="Back to portal chooser">← Webster portals</a>
        <div class="mt-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Restricted access</p>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Warden sign in</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Sign in to oversee your hostel and manage contractor access.</p>
        </div>
        <form wire:submit="login" class="mt-7 space-y-5">
            <div>
                <label for="warden-identifier" class="label">Email or employee ID</label>
                <input id="warden-identifier" type="text" wire:model="identifier" autocomplete="username" required autofocus class="input !py-3">
                @error('identifier') <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="warden-password" class="label">Password</label>
                <div x-data="{ visible: false }" class="relative">
                    <input id="warden-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="current-password" required class="input !py-3 pr-16">
                    <x-password-visibility-toggle />
                </div>
                @error('password') <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary w-full !py-3.5" wire:loading.attr="disabled">Sign in securely</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">This specialized portal is not listed on the public landing page.</p>
    </section>
</main>
