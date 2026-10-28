<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\WardenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WardenSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_warden_is_seeded_with_a_hashed_password_and_registration_date(): void
    {
        config()->set('warden.initial', [
            'name' => 'Warden Person',
            'phone' => '+919999000020',
            'email' => 'warden@college.edu',
            'employee_id' => 'WARDEN001',
            'hostel' => 'CV Raman',
            'registered_at' => '2026-06-12',
            'password' => 'strong-initial-password',
        ]);

        app(WardenSeeder::class)->run();

        $warden = User::query()->where('role', 'warden')->firstOrFail();
        $this->assertSame('Warden Person', $warden->name);
        $this->assertSame('2026-06-12', $warden->registered_at->toDateString());
        $this->assertSame('CV Raman', $warden->hostel);
        $this->assertTrue(Hash::check('strong-initial-password', $warden->password));
        $this->assertNotSame('strong-initial-password', $warden->password);
    }
}
