<main
    class="relative isolate flex min-h-screen items-center overflow-hidden bg-slate-950 px-4 py-10 font-sans sm:px-6 lg:px-8"
    style="background-image: linear-gradient(90deg, rgba(7, 26, 22, .82), rgba(7, 26, 22, .48)), url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=2200&q=85'); background-size: cover; background-position: center;"
>
    <div class="mx-auto grid w-full max-w-6xl items-center gap-10 lg:grid-cols-[1fr_0.85fr]">
        <section class="hidden max-w-xl text-white lg:block">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-200">Webster · Contractor workspace</p>
            <h1 class="mt-6 font-display text-5xl font-bold leading-tight tracking-tight xl:text-6xl">Good meals.<br>Less waste.</h1>
            <p class="mt-5 max-w-lg text-lg leading-8 text-white/80">Plan service, keep your team in sync, and make every meal count.</p>
            <div class="mt-8 flex items-center gap-3 text-sm text-white/80">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-emerald-200" aria-hidden="true">✓</span>
                Secure access for your hostel operations
            </div>
        </section>

        <div class="mx-auto w-full max-w-md">
            <div class="rounded-3xl border border-white/60 bg-white/95 p-7 shadow-2xl shadow-black/25 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-900/95 sm:p-9">
                <a href="/" class="text-sm font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-400" aria-label="Back to portal chooser">← Webster portals</a>
                <div class="mt-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Contractor access</p>
                    <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome back</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Sign in with the email address or employee ID provided by your warden.</p>
                </div>

                @if ($statusMessage)
                    <p role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">{{ $statusMessage }}</p>
                @endif
                <form wire:submit="login" class="mt-7 space-y-5">
                    <div>
                        <label for="contractor-identifier" class="label">Email or employee ID</label>
                        <input id="contractor-identifier" type="text" wire:model="identifier" autocomplete="username" required autofocus
                            class="input !py-3" placeholder="you@college.edu or employee ID">
                        @error('identifier') <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contractor-password" class="label">Password</label>
                        <div x-data="{ visible: false }" class="relative">
                            <input id="contractor-password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="current-password" required
                                class="input !py-3 pr-16">
                            <x-password-visibility-toggle />
                        </div>
                        @error('password') <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="btn-primary w-full !py-3.5">Sign in</button>
                </form>
                <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">Access is provided by your hostel warden.</p>
            </div>
        </div>
    </div>
</main>
