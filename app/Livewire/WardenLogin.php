<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
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

        $identifier = trim($validated['identifier']);
        $key = 'warden-login:'.Str::lower($identifier).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'identifier' => 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        $configuredWarden = $this->configuredWarden($identifier, $validated['password']);
        $warden = $configuredWarden === null
            ? null
            : $this->resolveWardenAccount($configuredWarden);

        if ($warden === null) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['identifier' => 'These credentials do not match a warden account.']);
        }

        RateLimiter::clear($key);
        Auth::guard('warden')->login($warden);
        $this->password = '';
        session()->regenerate();

        $this->redirectRoute('warden.dashboard');
    }

    /**
     * @return array<string, string>|null
     */
    private function configuredWarden(string $identifier, string $password): ?array
    {
        foreach (['initial', 'second'] as $wardenKey) {
            $warden = (array) config("warden.{$wardenKey}", []);
            $email = trim((string) ($warden['email'] ?? ''));
            $employeeId = trim((string) ($warden['employee_id'] ?? ''));
            $configuredPassword = (string) ($warden['password'] ?? '');

            if ($email === '' || $configuredPassword === '') {
                continue;
            }

            $identifierMatches = hash_equals(Str::lower($email), Str::lower($identifier))
                || ($employeeId !== '' && hash_equals($employeeId, $identifier));

            if (! $identifierMatches || ! hash_equals($configuredPassword, $password)) {
                continue;
            }

            foreach (['name', 'phone', 'employee_id', 'hostel'] as $requiredField) {
                if (blank($warden[$requiredField] ?? null)) {
                    throw new InvalidArgumentException("The {$wardenKey} warden configuration is missing {$requiredField}.");
                }
            }

            return [
                'name' => (string) $warden['name'],
                'phone' => (string) $warden['phone'],
                'email' => $email,
                'employee_id' => $employeeId,
                'hostel' => (string) $warden['hostel'],
                'registered_at' => trim((string) ($warden['registered_at'] ?? '')),
                'password' => $configuredPassword,
            ];
        }

        return null;
    }

    /**
     * Resolve the authenticated principal after environment credentials match.
     * Laravel's session guard needs a persisted model, but its stored password
     * is not used to approve the sign-in.
     *
     * @param  array<string, string>  $credentials
     */
    private function resolveWardenAccount(array $credentials): ?User
    {
        $accounts = User::query()
            ->where(function ($query) use ($credentials): void {
                $query->whereRaw('LOWER(email) = ?', [Str::lower($credentials['email'])])
                    ->orWhere('employee_id', $credentials['employee_id']);
            })
            ->get();

        if ($accounts->count() > 1) {
            return null;
        }

        $warden = $accounts->first();

        if ($warden !== null && ($warden->role !== 'warden' || $warden->archived_at !== null)) {
            return null;
        }

        $registeredAt = $credentials['registered_at'] !== ''
            ? now()->parse($credentials['registered_at'])->toDateString()
            : today()->toDateString();

        $warden ??= new User;
        $warden->fill([
            'name' => $credentials['name'],
            'phone' => $credentials['phone'],
            'email' => $credentials['email'],
            'employee_id' => $credentials['employee_id'],
            'hostel' => $credentials['hostel'],
            'registered_at' => $registeredAt,
            'password' => $credentials['password'],
            'role' => 'warden',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'account_approved_at' => now(),
        ])->save();

        return $warden;
    }

    public function render()
    {
        return view('livewire.warden-login');
    }
}
