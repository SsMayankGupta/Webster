<?php

namespace Tests\Feature;

use App\Livewire\WardenLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class WardenLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_warden_can_sign_in_with_email(): void
    {
        $warden = $this->warden();

        Livewire::test(WardenLogin::class)
            ->set('identifier', strtoupper($warden->email))
            ->set('password', 'warden-password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('warden.dashboard'))
            ->assertSet('password', '');

        $this->assertAuthenticatedAs($warden, 'warden');
    }

    public function test_warden_can_sign_in_with_employee_id(): void
    {
        $credentials = $this->configureWarden();

        Livewire::test(WardenLogin::class)
            ->set('identifier', $credentials['employee_id'])
            ->set('password', 'warden-password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('warden.dashboard'));

        $warden = User::query()->where('email', $credentials['email'])->firstOrFail();
        $this->assertAuthenticatedAs($warden, 'warden');
        $this->assertTrue(Hash::check('warden-password', $warden->password));
    }

    public function test_second_configured_warden_can_sign_in_without_a_preexisting_database_record(): void
    {
        $this->configureWarden();
        $secondWarden = [
            'name' => 'Second Warden',
            'phone' => '+919999000022',
            'email' => 'second-warden@example.edu',
            'employee_id' => 'WARDEN002',
            'hostel' => 'Ganga',
            'registered_at' => '2026-06-13',
            'password' => 'second-warden-password',
        ];
        config()->set('warden.second', $secondWarden);

        Livewire::test(WardenLogin::class)
            ->set('identifier', $secondWarden['email'])
            ->set('password', $secondWarden['password'])
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('warden.dashboard'));

        $warden = User::query()->where('email', $secondWarden['email'])->firstOrFail();
        $this->assertSame('Second Warden', $warden->name);
        $this->assertSame('WARDEN002', $warden->employee_id);
        $this->assertAuthenticatedAs($warden, 'warden');
    }

    public function test_database_password_does_not_override_environment_credentials(): void
    {
        $warden = $this->warden();

        Livewire::test(WardenLogin::class)
            ->set('identifier', $warden->email)
            ->set('password', 'old-database-password')
            ->call('login')
            ->assertHasErrors('identifier');

        $this->assertGuest('warden');
    }

    public function test_wrong_password_does_not_authenticate_warden(): void
    {
        $warden = $this->warden();

        Livewire::test(WardenLogin::class)
            ->set('identifier', $warden->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('identifier')
            ->assertSee('These credentials do not match a warden account.');

        $this->assertGuest('warden');
    }

    public function test_non_warden_account_cannot_use_warden_sign_in(): void
    {
        $contractor = User::query()->create([
            'name' => 'Contractor',
            'email' => 'contractor@college.edu',
            'employee_id' => 'CONTRACTOR001',
            'password' => 'warden-password',
            'role' => 'contractor',
        ]);

        Livewire::test(WardenLogin::class)
            ->set('identifier', $contractor->email)
            ->set('password', 'warden-password')
            ->call('login')
            ->assertHasErrors('identifier');

        $this->assertGuest('warden');
    }

    public function test_archived_warden_cannot_sign_in(): void
    {
        $warden = $this->warden();
        $warden->forceFill(['archived_at' => now()])->save();

        Livewire::test(WardenLogin::class)
            ->set('identifier', $warden->email)
            ->set('password', 'warden-password')
            ->call('login')
            ->assertHasErrors('identifier');

        $this->assertGuest('warden');
    }

    private function warden(): User
    {
        $credentials = $this->configureWarden();

        return User::query()->create([
            'name' => $credentials['name'],
            'phone' => $credentials['phone'],
            'email' => $credentials['email'],
            'employee_id' => $credentials['employee_id'],
            'hostel' => $credentials['hostel'],
            'password' => Hash::make('old-database-password'),
            'role' => 'warden',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function configureWarden(): array
    {
        $credentials = [
            'name' => 'Warden Person',
            'phone' => '+919999000020',
            'email' => 'warden@example.edu',
            'employee_id' => 'WARDEN001',
            'hostel' => 'CV Raman',
            'registered_at' => '2026-06-12',
            'password' => 'warden-password',
        ];

        config()->set('warden.initial', $credentials);
        config()->set('warden.second', []);

        return $credentials;
    }
}
