<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class WardenSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'initial' => (array) config('warden.initial'),
            'second' => (array) config('warden.second'),
        ] as $wardenKey => $warden) {
            $this->seedWarden($warden, $wardenKey);
        }
    }

    /**
     * @param  array<string, mixed>  $warden
     */
    private function seedWarden(array $warden, string $wardenKey): void
    {
        $configuredFields = ['name', 'phone', 'email', 'employee_id', 'hostel', 'registered_at', 'password'];
        $hasConfiguration = collect($configuredFields)
            ->contains(fn (string $field): bool => filled($warden[$field] ?? null));

        if (! $hasConfiguration) {
            return;
        }

        foreach (['name', 'phone', 'email', 'employee_id', 'hostel', 'password'] as $requiredField) {
            if (blank($warden[$requiredField] ?? null)) {
                throw new InvalidArgumentException("The {$wardenKey} warden configuration is missing {$requiredField}.");
            }
        }

        $registeredAt = $warden['registered_at'] ?? null;
        $registeredAt = filled($registeredAt) ? now()->parse($registeredAt)->toDateString() : today()->toDateString();

        User::query()->updateOrCreate(
            ['email' => $warden['email']],
            [
                'name' => $warden['name'],
                'phone' => $warden['phone'],
                'employee_id' => $warden['employee_id'],
                'hostel' => $warden['hostel'],
                'registered_at' => $registeredAt,
                'password' => Hash::make($warden['password']),
                'role' => 'warden',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'account_approved_at' => now(),
            ],
        );
    }
}
