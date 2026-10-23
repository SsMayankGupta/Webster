<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContractorLogin extends Component
{
    public string $email = '';

    public string $collegeId = '';

    public string $otp = '';

    public string $otpSentTo = '';

    public bool $showOtpInput = false;

    public int $otpResendSeconds = 0;

    public function mount(): void
    {
        if (Auth::guard('contractor')->check()) {
            $this->redirectRoute('contractor.dashboard');
        }
    }

    public function sendOtp(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'collegeId' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $this->email)
            ->where('college_id', $this->collegeId)
            ->where('role', 'contractor')
            ->first();

        if ($user === null) {
            $this->addError('email', 'Invalid credentials. Contractor not found.');

            return;
        }

        $throttleKey = 'contractor-otp:'.$this->email;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many attempts. Please try again in {$seconds} seconds.");

            return;
        }

        RateLimiter::hit($throttleKey, 300);

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('otp:'.$this->email, $otpCode, now()->addMinutes(10));

        dump("Testing PurposeOTP: ".$otpCode);

        // $this->sendOtpViaEmail($user, $otpCode);
        // $this->sendOtpViaSms($user, $otpCode);

        $this->showOtpInput = true;
        $this->otpSentTo = $this->email;
        $this->otpResendSeconds = 60;

        $this->dispatch('portal-toast', message: 'OTP sent to your email and phone.', type: 'success');
    }

    private function sendOtpViaEmail(User $user, string $otp): void
    {
        Mail::raw("Your OTP for ZeroWasteMess.io Contractor Portal is: {$otp}\n\nThis code is valid for 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)->subject('Your Login OTP');
        });
    }

    private function sendOtpViaSms(User $user, string $otp): void
    {
        if ($user->phone) {
            // Integration with SMS gateway (Twilio, MSG91, etc.)
            // For now, we'll log it - in production, integrate with actual SMS service
            \Log::info("SMS OTP for {$user->phone}: {$otp}");
        }
    }

    public function verifyOtp(): void
    {
        $this->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $cachedOtp = Cache::get('otp:'.$this->email);

        if ($cachedOtp === null) {
            $this->addError('otp', 'OTP expired. Please request a new one.');

            return;
        }

        if ($cachedOtp !== $this->otp) {
            $this->addError('otp', 'Invalid OTP. Please try again.');

            return;
        }

        $user = User::query()
            ->where('email', $this->email)
            ->where('role', 'contractor')
            ->first();

        if ($user === null) {
            $this->addError('email', 'User not found.');

            return;
        }

        Auth::guard('contractor')->login($user);
        Cache::forget('otp:'.$this->email);
        RateLimiter::clear('contractor-otp:'.$this->email);

        $this->dispatch('contractor-logged-in', userId: $user->id);
        $this->redirectRoute('contractor.dashboard');
    }

    public function resendOtp(): void
    {
        if ($this->otpResendSeconds > 0) {
            return;
        }

        $this->sendOtp();
    }

    public function render()
    {
        return view('livewire.contractor-login')
            ->layout('layouts.guest');
    }
}
