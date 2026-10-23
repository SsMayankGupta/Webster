<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-4">
    <div class="w-full max-w-md">
        <div class="rounded-3xl bg-white shadow-2xl p-8">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Contractor Portal</h1>
                <p class="mt-2 text-sm text-slate-500">ZeroWasteMess.io - Secure OTP Login</p>
            </div>

            @if (!$showOtpInput)
                <form wire:submit="sendOtp" class="space-y-6">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email Address</label>
                        <div class="relative">
                            <input type="email" wire:model.live="email"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 pl-11 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition"
                                placeholder="contractor@zerowastemess.io">
                            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">College ID</label>
                        <div class="relative">
                            <input type="text" wire:model.live="collegeId"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 pl-11 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition"
                                placeholder="CONTRACTOR001">
                            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v6m-4 0h4"/>
                            </svg>
                        </div>
                        @error('collegeId')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:shadow-xl hover:shadow-indigo-500/40 disabled:opacity-50">
                        <span wire:loading>
                            <svg class="mx-auto h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove>Send OTP Code</span>
                    </button>
                </form>
            @else
                <div class="space-y-6">
                    <div class="rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 p-4 text-center border border-indigo-100">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-indigo-800 font-medium">
                                OTP sent to <span class="font-bold">{{ $otpSentTo }}</span>
                            </p>
                        </div>
                    </div>

                    <form wire:submit="verifyOtp" class="space-y-4">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Enter OTP Code</label>
                            <input type="text" wire:model.live="otp" maxlength="6"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3.5 text-center text-2xl tracking-[0.5em] font-bold focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition"
                                placeholder="000000">
                            @error('otp')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:shadow-xl hover:shadow-indigo-500/40 disabled:opacity-50">
                            <span wire:loading>
                                <svg class="mx-auto h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove>Verify & Login</span>
                        </button>
                    </form>

                    <button wire:click="resendOtp" wire:loading.attr="disabled"
                        @if ($otpResendSeconds > 0) disabled @endif
                        class="w-full rounded-xl border-2 border-slate-200 px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-50">
                        @if ($otpResendSeconds > 0)
                            Resend in {{ $otpResendSeconds }}s
                        @else
                            Resend OTP Code
                        @endif
                    </button>

                    <button wire:click="$set('showOtpInput', false)"
                        class="w-full text-sm text-slate-500 hover:text-slate-700 transition">
                        ← Back to Login
                    </button>
                </div>
            @endif

            <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                <a href="/" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
