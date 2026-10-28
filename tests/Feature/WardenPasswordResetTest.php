<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\WardenResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class WardenPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_warden_reset_form_remains_available_but_login_has_no_forgot_password_link(): void
    {
        $this->get(route('warden.login'))
            ->assertOk()
            ->assertDontSee(route('warden.password.request'));

        $response = $this->get(route('warden.password.request'));

        $response->assertOk()
            ->assertSee('Reset your password')
            ->assertSee(route('warden.password.email'));
    }

    public function test_reset_link_request_requires_a_valid_email_address(): void
    {
        $response = $this->from(route('warden.password.request'))
            ->post(route('warden.password.email'), ['email' => 'not-an-email']);

        $response->assertRedirect(route('warden.password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_reset_link_is_sent_to_the_registered_warden_email(): void
    {
        Notification::fake();
        $warden = User::factory()->create(['role' => 'warden']);

        $response = $this->post(route('warden.password.email'), [
            'email' => $warden->email,
        ]);

        $response->assertRedirect()
            ->assertSessionHas('status');
        Notification::assertSentTo($warden, WardenResetPassword::class);
    }

    public function test_reset_request_does_not_disclose_non_warden_emails(): void
    {
        Notification::fake();
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->from(route('warden.password.request'))
            ->post(route('warden.password.email'), ['email' => $student->email]);

        $response->assertRedirect(route('warden.password.request'))
            ->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_warden_password_can_be_reset_with_a_valid_token(): void
    {
        $warden = User::factory()->create(['role' => 'warden']);
        $token = Password::broker()->createToken($warden);

        $response = $this->post(route('warden.password.store'), [
            'token' => $token,
            'email' => $warden->email,
            'password' => 'new-warden-password-123',
            'password_confirmation' => 'new-warden-password-123',
        ]);

        $response->assertRedirect(route('warden.login'))
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-warden-password-123', $warden->fresh()->password));
    }

    public function test_non_warden_token_cannot_reset_an_account_password(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $token = Password::broker()->createToken($student);
        $resetUrl = route('warden.password.reset', [
            'token' => $token,
            'email' => $student->email,
        ]);

        $response = $this->from($resetUrl)->post(route('warden.password.store'), [
            'token' => $token,
            'email' => $student->email,
            'password' => 'new-student-password-123',
            'password_confirmation' => 'new-student-password-123',
        ]);

        $response->assertRedirect($resetUrl)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $student->fresh()->password));
    }

    public function test_reset_notification_links_to_the_warden_reset_form(): void
    {
        Notification::fake();
        $warden = User::factory()->create(['role' => 'warden']);

        $this->post(route('warden.password.email'), ['email' => $warden->email]);

        Notification::assertSentTo($warden, WardenResetPassword::class, function (WardenResetPassword $notification) use ($warden): bool {
            $resetUrl = $notification->toMail($warden)->actionUrl;
            $expectedUrl = route('warden.password.reset', [
                'token' => $notification->token,
                'email' => $warden->email,
            ]);

            $this->assertSame($expectedUrl, $resetUrl);
            $this->get($resetUrl)->assertOk()->assertSee('Create a new password');

            return true;
        });
    }
}
