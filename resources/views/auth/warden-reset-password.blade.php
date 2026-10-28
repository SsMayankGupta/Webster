<x-guest-layout>
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-10">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-9">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-400">Warden portal</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">Create a new password</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Choose a new password for your warden account.</p>

            <form method="POST" action="{{ route('warden.password.store') }}" class="mt-7 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="label">Registered email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" autocomplete="username" required class="input">
                    @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label">New password</label>
                    <div x-data="{ visible: false }" class="relative">
                        <input id="password" name="password" :type="visible ? 'text' : 'password'" autocomplete="new-password" required class="input pr-16">
                        <x-password-visibility-toggle />
                    </div>
                    @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm new password</label>
                    <div x-data="{ visible: false }" class="relative">
                        <input id="password_confirmation" name="password_confirmation" :type="visible ? 'text' : 'password'" autocomplete="new-password" required class="input pr-16">
                        <x-password-visibility-toggle />
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full">Reset password</button>
            </form>
        </section>
    </main>
</x-guest-layout>
