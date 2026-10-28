<?php

namespace Tests\Feature;

use App\Livewire\StudentPortal;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_student_email_is_validated_instantly(): void
    {
        $student = $this->student();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->email)
            ->call('validateStudentEmail')
            ->assertHasNoErrors('email')
            ->assertSet('emailStatus', 'Registered student found. Enter your password to continue.');
    }

    public function test_unknown_email_is_rejected_with_an_inline_message(): void
    {
        $component = Livewire::test(StudentPortal::class)
            ->set('email', 'nobody@college.edu')
            ->call('validateStudentEmail')
            ->assertHasErrors('email');

        $this->assertStringContainsString(
            'not registered as a student account',
            $component->errors()->first('email'),
        );
    }

    public function test_contractor_account_is_not_accepted_as_a_student(): void
    {
        User::create([
            'name' => 'Contractor',
            'college_id' => 'CON001',
            'email' => 'contractor@college.edu',
            'password' => Hash::make('password'),
            'role' => 'contractor',
        ]);

        Livewire::test(StudentPortal::class)
            ->set('email', 'contractor@college.edu')
            ->call('validateStudentEmail')
            ->assertHasErrors('email');
    }

    public function test_student_can_sign_in_with_email_without_a_page_reload(): void
    {
        $student = $this->student();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertSet('isAuthenticated', true)
            ->assertSet('showAuth', false);

        $this->assertAuthenticatedAs($student, 'student');
        $this->assertAuthenticatedAs($student);
    }

    public function test_student_can_sign_in_with_college_id(): void
    {
        $student = $this->student();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->college_id)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertSet('isAuthenticated', true);

        $this->assertAuthenticatedAs($student, 'student');
    }

    public function test_archived_student_cannot_sign_in(): void
    {
        $student = $this->student();
        $student->forceFill(['archived_at' => now()])->save();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors('email')
            ->assertSet('isAuthenticated', false);

        $this->assertGuest('student');
    }

    public function test_archived_student_cannot_continue_using_an_existing_session(): void
    {
        $student = $this->student();
        $student->forceFill(['archived_at' => now()])->save();

        $this->actingAs($student, 'student')
            ->get(route('student.portal'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status');

        $this->assertGuest('student');
    }

    public function test_archived_student_cannot_sign_in_through_the_standard_login_form(): void
    {
        $student = $this->student();
        $student->forceFill(['archived_at' => now()])->save();

        $this->post(route('login'), [
            'email' => $student->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_wrong_password_keeps_the_guest_state(): void
    {
        $student = $this->student();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('password')
            ->assertSet('isAuthenticated', false);

        Auth::guard('student')->check() ? $this->fail('Student guard should be logged out.') : $this->addToAssertionCount(1);
    }

    public function test_logout_returns_to_the_guest_showcase(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->assertSet('isAuthenticated', true)
            ->call('logout')
            ->assertSet('isAuthenticated', false)
            ->assertSet('state', []);

        $this->assertGuest('student');
    }

    public function test_password_reset_link_is_sent_to_the_college_email(): void
    {
        Notification::fake();

        $student = $this->student();

        Livewire::test(StudentPortal::class)
            ->set('email', $student->email)
            ->call('sendPasswordResetLink')
            ->assertHasNoErrors();

        Notification::assertSentTo($student, ResetPassword::class);
    }

    public function test_password_can_be_reset_inside_the_portal(): void
    {
        $student = $this->student();
        $token = Password::broker()->createToken($student);

        Livewire::test(StudentPortal::class)
            ->set('authMode', 'reset')
            ->set('resetToken', $token)
            ->set('email', $student->email)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertSet('authMode', 'login');

        $this->assertTrue(Hash::check('new-password-123', $student->fresh()->password));
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Asha Rao',
            'college_id' => 'STU001',
            'email' => 'asha@college.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now(),
            'phone' => '+919999000001',
            'phone_verified_at' => now(),
            'account_approved_at' => now(),
        ]);
    }
}
