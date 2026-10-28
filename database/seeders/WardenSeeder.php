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
        $warden = (array) config('warden.initial');

        if (blank($warden['email'] ?? null) && blank($warden['password'] ?? null)) {
            return;
        }

        foreach (['name', 'phone', 'email', 'employee_id', 'hostel', 'password'] as $requiredField) {
            if (blank($warden[$requiredField] ?? null)) {
                throw new InvalidArgumentException("The initial warden configuration is missing {$requiredField}.");
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
