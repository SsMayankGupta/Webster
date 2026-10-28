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
        config()->set('warden.second', []);

        app(WardenSeeder::class)->run();

        $warden = User::query()->where('email', 'warden@college.edu')->firstOrFail();
        $this->assertSame('Warden Person', $warden->name);
        $this->assertSame('2026-06-12', $warden->registered_at->toDateString());
        $this->assertSame('CV Raman', $warden->hostel);
        $this->assertTrue(Hash::check('strong-initial-password', $warden->password));
        $this->assertNotSame('strong-initial-password', $warden->password);
    }

    public function test_initial_and_second_wardens_are_seeded_with_separate_details(): void
    {
        config()->set('warden.initial', [
            'name' => 'First Warden',
            'phone' => '+919999000021',
            'email' => 'first-warden@college.edu',
            'employee_id' => 'WARDEN001',
            'hostel' => 'CV Raman',
            'registered_at' => '2026-06-12',
            'password' => 'strong-first-password',
        ]);
        config()->set('warden.second', [
            'name' => 'Second Warden',
            'phone' => '+919999000022',
            'email' => 'second-warden@college.edu',
            'employee_id' => 'WARDEN002',
            'hostel' => 'Ganga',
            'registered_at' => '2026-06-13',
            'password' => 'strong-second-password',
        ]);

        app(WardenSeeder::class)->run();

        $firstWarden = User::query()->where('email', 'first-warden@college.edu')->firstOrFail();
        $secondWarden = User::query()->where('email', 'second-warden@college.edu')->firstOrFail();

        $this->assertSame('warden', $firstWarden->role);
        $this->assertSame('WARDEN001', $firstWarden->employee_id);
        $this->assertSame('CV Raman', $firstWarden->hostel);
        $this->assertTrue(Hash::check('strong-first-password', $firstWarden->password));
        $this->assertSame('warden', $secondWarden->role);
        $this->assertSame('WARDEN002', $secondWarden->employee_id);
        $this->assertSame('Ganga', $secondWarden->hostel);
        $this->assertTrue(Hash::check('strong-second-password', $secondWarden->password));
    }

    public function test_missing_second_warden_configuration_keeps_single_warden_seeding_compatible(): void
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
        config()->set('warden.second', [
            'name' => null,
            'phone' => null,
            'email' => null,
            'employee_id' => null,
            'hostel' => null,
            'registered_at' => null,
            'password' => null,
        ]);

        app(WardenSeeder::class)->run();

        $this->assertSame(1, User::query()->where('role', 'warden')->count());
    }

    public function test_partially_configured_second_warden_is_reported_as_invalid(): void
    {
        config()->set('warden.initial', []);
        config()->set('warden.second', [
            'name' => 'Second Warden',
            'email' => 'second-warden@college.edu',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The second warden configuration is missing phone.');

        app(WardenSeeder::class)->run();
    }
}
