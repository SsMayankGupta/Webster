<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class WardenLogin extends Component
{
    public string $identifier = '';

    public string $password = '';

    public function login(): void
    {
        $validated = $this->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $key = 'warden-login:'.strtolower($validated['identifier']).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'identifier' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        $warden = User::query()
            ->where(function ($query) use ($validated): void {
                $query->where('email', $validated['identifier'])
                    ->orWhere('employee_id', $validated['identifier']);
            })
            ->where('role', 'warden')
            ->first();

        if ($warden === null || ! Hash::check($validated['password'], $warden->password)) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['identifier' => 'These credentials do not match a warden account.']);
        }

        RateLimiter::clear($key);
        Auth::guard('warden')->login($warden);
        session()->regenerate();

        $this->redirectRoute('warden.dashboard');
    }

    public function render()
    {
        return view('livewire.warden-login');
    }
}
