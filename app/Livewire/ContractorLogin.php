<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ContractorLogin extends Component
{
    public string $identifier = '';

    public string $password = '';

    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->statusMessage = session('status');

        if (Auth::guard('contractor')->check()) {
            $contractor = Auth::guard('contractor')->user();
            $this->redirectRoute($contractor->must_change_password ? 'password.first-login' : 'contractor.dashboard');
        }
    }

    public function login(): void
    {
        $validated = $this->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $throttleKey = 'contractor-login:'.strtolower($validated['identifier']).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'identifier' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        $contractor = User::query()
            ->where(function ($query) use ($validated): void {
                $query->where('email', $validated['identifier'])
                    ->orWhere('employee_id', $validated['identifier']);
            })
            ->where('role', 'contractor')
            ->whereNull('archived_at')
            ->first();

        if ($contractor === null || ! Hash::check($validated['password'], $contractor->password)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match a contractor account.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        Auth::guard('contractor')->login($contractor);
        Auth::guard('web')->login($contractor);
        session()->regenerate();

        $this->redirectRoute($contractor->must_change_password ? 'password.first-login' : 'contractor.dashboard');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.contractor-login');
    }
}
