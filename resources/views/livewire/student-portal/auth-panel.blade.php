<div x-cloak>
    <div
        x-show="$wire.showAuth"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="closeAuth"
        class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    <aside
        x-show="$wire.showAuth"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col overflow-y-auto border-l border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
        role="dialog"
        aria-modal="true"
        aria-labelledby="auth-panel-title"
    >
        <div class="flex items-start justify-between gap-4 border-b border-slate-200/80 px-6 py-5 dark:border-slate-800">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                    {{ $authMode === 'reset' ? 'Choose a new password' : ($authMode === 'forgot' ? 'Password recovery' : 'Student sign in') }}
                </p>
                <h2 id="auth-panel-title" class="mt-1 font-display text-xl font-extrabold tracking-tight">
                    @if ($authMode === 'reset')
                        Set a new password
                    @elseif ($authMode === 'forgot')
                        Reset your password
                    @else
                        Welcome back
                    @endif
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    @if ($authMode === 'reset')
                        Your college email is verified. Choose a password with at least 8 characters.
                    @elseif ($authMode === 'forgot')
                        We will email a verification link to your registered college address.
                    @else
                        Use the college email or ID registered by your hostel admin.
                    @endif
                </p>
            </div>
            <button type="button" wire:click="closeAuth" class="btn-ghost !px-2 !py-2" aria-label="Close sign-in panel">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 px-6 py-6">
            @if ($authMessage)
                <div class="mb-5 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $authMessage }}</span>
                </div>
            @endif

            @error('login')
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-200">
                    {{ $message }}
                </div>
            @enderror

            @if ($authMode === 'login')
                <form wire:submit="login" class="space-y-5">
                    <div>
                        <label for="portal-email" class="label">College email or College ID</label>
                        <div class="relative">
                            <input
                                id="portal-email"
                                type="text"
                                inputmode="email"
                                autocomplete="username"
                                wire:model.live.debounce.500ms="email"
                                wire:change="validateStudentEmail"
                                class="input pr-10 @error('email') !border-rose-400 focus:!ring-rose-500/20 @enderror"
                                placeholder="you@college.edu"
                                aria-describedby="portal-email-help"
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <svg wire:loading wire:target="validateStudentEmail" class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                @if ($emailStatus)
                                    <svg wire:loading.remove wire:target="validateStudentEmail" class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </div>
                        </div>

                        <div id="portal-email-help" class="mt-1.5 min-h-[1.25rem] text-xs">
                            @error('email')
                                <p class="font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @else
                                @if ($emailStatus)
                                    <p class="font-medium text-emerald-600 dark:text-emerald-400">{{ $emailStatus }}</p>
                                @else
                                    <p class="text-slate-400">Checked live against the student database.</p>
                                @endif
                            @enderror
                        </div>
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between">
                            <label for="portal-password" class="label !mb-0">Password</label>
                            <button
                                type="button"
                                wire:click="setAuthMode('forgot')"
                                class="text-xs font-semibold text-brand-700 hover:text-brand-800 dark:text-brand-300"
                            >
                                Forgot password?
                            </button>
                        </div>
                        <div class="relative mt-1.5">
                            <input
                                id="portal-password"
                                :type="show ? 'text' : 'password'"
                                autocomplete="current-password"
                                wire:model="password"
                                class="input pr-12 @error('password') !border-rose-400 focus:!ring-rose-500/20 @enderror"
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-2 my-auto flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.97 9.97 0 012.29-3.775M6.223 6.223A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900">
                        Keep me signed in on this device
                    </label>

                    <button type="submit" class="btn-primary w-full !py-3" wire:loading.attr="disabled" wire:target="login">
                        <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="login">Sign in to portal</span>
                        <span wire:loading wire:target="login">Verifying…</span>
                    </button>

                    <p class="text-center text-xs text-slate-400">
                        Accounts are pre-registered by your hostel admin. Email verification is sent to your college address.
                    </p>
                </form>
            @elseif ($authMode === 'forgot')
                <form wire:submit="sendPasswordResetLink" class="space-y-5">
                    <div>
                        <label for="forgot-email" class="label">College email or College ID</label>
                        <input
                            id="forgot-email"
                            type="text"
                            autocomplete="username"
                            wire:model="email"
                            class="input @error('email') !border-rose-400 focus:!ring-rose-500/20 @enderror"
                            placeholder="you@college.edu"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-slate-400">
                            We send the reset link to the college email on your student record.
                        </p>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3" wire:loading.attr="disabled" wire:target="sendPasswordResetLink">
                        <span wire:loading.remove wire:target="sendPasswordResetLink">Email me a reset link</span>
                        <span wire:loading wire:target="sendPasswordResetLink">Sending…</span>
                    </button>

                    <button type="button" wire:click="setAuthMode('login')" class="btn-ghost w-full">
                        Back to sign in
                    </button>
                </form>
            @else
                <form wire:submit="resetPassword" class="space-y-5">
                    <input type="hidden" wire:model="resetToken">

                    <div>
                        <label for="reset-email" class="label">College email</label>
                        <input
                            id="reset-email"
                            type="email"
                            autocomplete="username"
                            wire:model="email"
                            class="input @error('email') !border-rose-400 focus:!ring-rose-500/20 @enderror"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label for="reset-password" class="label">New password</label>
                        <div class="relative">
                            <input
                                id="reset-password"
                                :type="show ? 'text' : 'password'"
                                autocomplete="new-password"
                                wire:model="password"
                                class="input pr-12 @error('password') !border-rose-400 focus:!ring-rose-500/20 @enderror"
                                placeholder="At least 8 characters"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-2 my-auto flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.97 9.97 0 012.29-3.775M6.223 6.223A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label for="reset-password-confirmation" class="label">Confirm new password</label>
                        <div class="relative">
                            <input
                                id="reset-password-confirmation"
                                :type="show ? 'text' : 'password'"
                                autocomplete="new-password"
                                wire:model="password_confirmation"
                                class="input pr-12"
                                placeholder="Repeat the password"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-2 my-auto flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                :aria-label="show ? 'Hide password' : 'Show password'"
                            >
                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.97 9.97 0 012.29-3.775M6.223 6.223A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.132 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full !py-3" wire:loading.attr="disabled" wire:target="resetPassword">
                        <span wire:loading.remove wire:target="resetPassword">Update password</span>
                        <span wire:loading wire:target="resetPassword">Updating…</span>
                    </button>

                    <button type="button" wire:click="setAuthMode('login')" class="btn-ghost w-full">
                        Back to sign in
                    </button>
                </form>
            @endif
        </div>

        <div class="border-t border-slate-200/80 px-6 py-4 text-[11px] text-slate-400 dark:border-slate-800">
            Protected by the student guard. Every meal, QR and leave action is re-authorised on the server.
        </div>
    </aside>
</div>
