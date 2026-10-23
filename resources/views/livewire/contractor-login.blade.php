<main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 font-sans dark:bg-slate-950">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <a href="/" class="font-display text-xl font-bold text-slate-900 dark:text-white">
                ZeroWasteMess<span class="text-brand-600 dark:text-brand-400">.io</span>
            </a>
            <h1 class="mt-8 font-display text-2xl font-bold text-slate-900 dark:text-white">
                @if (!$showOtpInput)
                    Contractor sign in
                @else
                    Verify your email
                @endif
            </h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                @if (!$showOtpInput)
                    Enter your details to receive a one-time sign-in code.
                @else
                    Enter the code sent to your email address.
                @endif
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            @if (!$showOtpInput)
                <form wire:submit="sendOtp" class="space-y-5">
                    <div>
                        <label for="contractor-email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Email address</label>
                        <input id="contractor-email" type="email" wire:model.live="email" autocomplete="email" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="you@college.edu" @error('email') aria-invalid="true" aria-describedby="contractor-email-error" @enderror>
                        @error('email')
                            <p id="contractor-email-error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contractor-college-id" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">College ID</label>
                        <input id="contractor-college-id" type="text" wire:model.live="collegeId" autocomplete="off" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-900 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="Enter your college ID" @error('collegeId') aria-invalid="true" aria-describedby="contractor-college-id-error" @enderror>
                        @error('collegeId')
                            <p id="contractor-college-id-error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="sendOtp"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:cursor-wait disabled:opacity-60">
                        <svg wire:loading wire:target="sendOtp" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="sendOtp">Send sign-in code</span>
                    </button>
                </form>
            @else
                <div class="mb-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    Code sent to <span class="font-medium text-slate-900 dark:text-white">{{ $otpSentTo }}</span>
                </div>

                <form wire:submit="verifyOtp" class="space-y-5">
                    <div>
                        <label for="contractor-otp" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">One-time code</label>
                        <input id="contractor-otp" type="text" wire:model.live="otp" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-center font-mono text-xl tracking-[0.4em] text-slate-900 outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                            placeholder="000000" @error('otp') aria-invalid="true" aria-describedby="contractor-otp-error" @enderror>
                        @error('otp')
                            <p id="contractor-otp-error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="verifyOtp"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900 disabled:cursor-wait disabled:opacity-60">
                        <svg wire:loading wire:target="verifyOtp" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="verifyOtp">Verify and sign in</span>
                    </button>
                </form>

                <div class="mt-5 flex items-center justify-between gap-4 border-t border-slate-100 pt-5 dark:border-slate-800">
                    <button type="button" wire:click="resendOtp" wire:loading.attr="disabled" wire:target="resendOtp"
                        @if ($otpResendSeconds > 0) disabled @endif
                        class="text-sm font-medium text-brand-700 hover:text-brand-800 disabled:cursor-not-allowed disabled:text-slate-400 dark:text-brand-400 dark:hover:text-brand-300 dark:disabled:text-slate-600">
                        @if ($otpResendSeconds > 0)
                            Resend in {{ $otpResendSeconds }}s
                        @else
                            Resend code
                        @endif
                    </button>
                    <button type="button" wire:click="$set('showOtpInput', false)"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                        Change details
                    </button>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-slate-500 hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400">Back to home</a>
        </div>
    </div>
</main>
