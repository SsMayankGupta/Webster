<x-guest-layout>
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-10">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-9">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Warden portal</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">Reset your password</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Enter your registered email address and we will send you a link to create a new password.</p>

            <x-auth-session-status class="mt-4" :status="session('status')" />

            <form method="POST" action="{{ route('warden.password.email') }}" class="mt-7 space-y-5">
                @csrf
                <div>
                    <label for="email" class="label">Registered email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="input">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">Email reset link</button>
            </form>

            <a href="{{ route('warden.login') }}" class="mt-6 inline-block text-sm font-medium text-brand-700 hover:underline dark:text-brand-400">Back to warden sign in</a>
        </section>
    </main>
</x-guest-layout>
