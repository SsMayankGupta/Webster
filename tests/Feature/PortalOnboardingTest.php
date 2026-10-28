<?php

namespace Tests\Feature;

use App\Livewire\ContractorLogin;
use App\Livewire\ContractorStudents;
use App\Livewire\StudentPortal;
use App\Livewire\WardenDashboard;
use App\Models\User;
use App\Models\WasteTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PortalOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_warden_can_create_a_contractor_who_can_sign_in_immediately_with_email(): void
    {
        $warden = $this->warden();
        $this->actingAs($warden, 'warden');

        Livewire::test(WardenDashboard::class)
            ->set('contractorName', 'Contractor Person')
            ->set('employeeId', 'EMP100')
            ->set('email', 'contractor@college.edu')
            ->set('phone', '+919999000012')
            ->set('password', 'initial-password')
            ->set('passwordConfirmation', 'initial-password')
            ->call('addContractor')
            ->assertHasNoErrors()
            ->assertSee('Share the initial password');

        $contractor = User::query()->where('employee_id', 'EMP100')->firstOrFail();
        $this->assertSame($warden->id, $contractor->warden_id);
        $this->assertNotNull($contractor->account_approved_at);
        $this->assertTrue($contractor->must_change_password);
        $this->assertTrue(Hash::check('initial-password', $contractor->password));

        Livewire::test(ContractorLogin::class)
            ->set('identifier', 'contractor@college.edu')
            ->set('password', 'initial-password')
            ->call('login')
            ->assertRedirect(route('password.first-login'));

        $this->assertAuthenticatedAs($contractor, 'contractor');
        $this->assertAuthenticatedAs($contractor, 'web');
    }

    public function test_warden_can_edit_archive_and_permanently_delete_only_their_own_contractors(): void
    {
        $warden = $this->warden();
        $contractor = $this->contractor([
            'employee_id' => 'EMP201',
            'email' => 'edit-contractor@example.edu',
            'phone' => '+919999000015',
            'warden_id' => $warden->id,
        ]);
        $student = User::query()->create([
            'name' => 'Affiliated Student',
            'college_id' => 'STU201',
            'email' => 'affiliated@example.edu',
            'password' => 'student-password',
            'role' => 'student',
            'contractor_id' => $contractor->id,
        ]);

        $this->actingAs($warden, 'warden');

        Livewire::test(WardenDashboard::class)
            ->call('editContractor', $contractor->id)
            ->set('contractorName', 'Updated Contractor')
            ->set('email', 'updated-contractor@example.edu')
            ->call('updateContractor')
            ->assertHasNoErrors();

        $this->assertSame('Updated Contractor', $contractor->fresh()->name);
        $this->assertSame('updated-contractor@example.edu', $contractor->fresh()->email);
        $this->assertTrue(Hash::check('initial-password', $contractor->fresh()->password));

        Livewire::test(WardenDashboard::class)
            ->call('archiveContractor', $contractor->id)
            ->assertHasNoErrors();

        $this->assertNotNull($contractor->fresh()->archived_at);
        $this->assertSame($contractor->id, $student->fresh()->contractor_id);

        Livewire::test(WardenDashboard::class)
            ->call('permanentlyDeleteContractor', $contractor->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($contractor);
        $this->assertNull($student->fresh()->contractor_id);

        $otherWarden = User::query()->create([
            'name' => 'Other Warden',
            'employee_id' => 'WARDEN002',
            'email' => 'other-warden@example.edu',
            'password' => 'warden-password',
            'role' => 'warden',
        ]);
        $otherContractor = User::query()->create([
            'name' => 'Other Contractor',
            'employee_id' => 'EMP202',
            'email' => 'other-contractor@example.edu',
            'password' => 'initial-password',
            'role' => 'contractor',
            'warden_id' => $otherWarden->id,
        ]);

        Livewire::test(WardenDashboard::class)
            ->call('archiveContractor', $otherContractor->id)
            ->assertNotFound();
    }

    public function test_contractor_with_operational_history_can_be_archived_but_not_permanently_deleted(): void
    {
        $warden = $this->warden();
        $contractor = $this->contractor(['warden_id' => $warden->id]);
        $transaction = WasteTransaction::query()->create([
            'contractor_id' => $contractor->id,
            'weight_kg' => 2.5,
            'type' => 'compost',
            'earnings' => 10,
        ]);
        $this->actingAs($warden, 'warden');

        Livewire::test(WardenDashboard::class)
            ->call('archiveContractor', $contractor->id)
            ->call('permanentlyDeleteContractor', $contractor->id)
            ->assertHasErrors('deletion');

        $this->assertModelExists($contractor->fresh());
        $this->assertModelExists($transaction);
        $this->assertNotNull($contractor->fresh()->archived_at);
    }

    public function test_contractor_can_sign_in_with_employee_id_without_contact_verification(): void
    {
        $contractor = $this->contractor([
            'employee_id' => 'EMP200',
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'account_approved_at' => null,
            'must_change_password' => false,
        ]);

        Livewire::test(ContractorLogin::class)
            ->set('identifier', 'EMP200')
            ->set('password', 'initial-password')
            ->call('login')
            ->assertRedirect(route('contractor.dashboard'));

        $this->assertAuthenticatedAs($contractor, 'contractor');
    }

    public function test_archived_contractor_cannot_sign_in(): void
    {
        $contractor = $this->contractor();
        $contractor->forceFill(['archived_at' => now()])->save();

        Livewire::test(ContractorLogin::class)
            ->set('identifier', $contractor->employee_id)
            ->set('password', 'initial-password')
            ->call('login')
            ->assertHasErrors('identifier');

        $this->assertGuest('contractor');
        $this->assertGuest();
    }

    public function test_contractor_can_create_a_student_with_a_one_time_initial_password(): void
    {
        $contractor = $this->contractor(['must_change_password' => false]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->set('name', 'Asha Rao')
            ->set('collegeId', 'STU1001')
            ->set('email', 'asha@college.edu')
            ->set('phone', '')
            ->set('password', 'student-initial-password')
            ->set('passwordConfirmation', 'student-initial-password')
            ->call('addStudent')
            ->assertHasNoErrors()
            ->assertSee('Share the initial password');

        $student = User::query()->where('college_id', 'STU1001')->firstOrFail();
        $this->assertSame($contractor->id, $student->contractor_id);
        $this->assertNotNull($student->account_approved_at);
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check('student-initial-password', $student->password));
    }

    public function test_student_can_sign_in_with_student_id_without_approval_or_contact_verification(): void
    {
        $student = User::query()->create([
            'name' => 'Asha Rao',
            'college_id' => 'STU2001',
            'email' => 'asha@college.edu',
            'password' => 'student-initial-password',
            'role' => 'student',
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'account_approved_at' => null,
        ]);
        $student->forceFill(['must_change_password' => true])->save();

        Livewire::test(StudentPortal::class)
            ->set('email', 'STU2001')
            ->set('password', 'student-initial-password')
            ->call('login')
            ->assertRedirect(route('password.first-login'));

        $this->assertAuthenticatedAs($student, 'student');
    }

    public function test_contractor_and_student_must_change_their_initial_password_once(): void
    {
        $student = User::query()->create([
            'name' => 'Asha Rao',
            'college_id' => 'STU3001',
            'email' => 'asha-change@college.edu',
            'password' => 'student-initial-password',
            'role' => 'student',
        ]);
        $student->forceFill(['must_change_password' => true])->save();
        $this->actingAs($student, 'web');

        $this->get(route('password.first-login'))->assertOk()
            ->assertSee('Update the initial password');

        $this->put(route('password.first-login.update'), [
            'password' => 'personal-password-123',
            'password_confirmation' => 'personal-password-123',
        ])->assertRedirect(route('student.portal'));

        $this->assertFalse($student->fresh()->must_change_password);
        $this->assertTrue(Hash::check('personal-password-123', $student->fresh()->password));
        Auth::logout();

        $contractor = $this->contractor(['must_change_password' => true]);
        $this->actingAs($contractor, 'web');
        $this->put(route('password.first-login.update'), [
            'password' => 'contractor-personal-password',
            'password_confirmation' => 'contractor-personal-password',
        ])->assertRedirect(route('contractor.dashboard'));

        $this->assertFalse($contractor->fresh()->must_change_password);
        $this->assertTrue(Hash::check('contractor-personal-password', $contractor->fresh()->password));
    }

    public function test_password_change_gate_blocks_contractor_dashboard_until_initial_password_is_changed(): void
    {
        $contractor = $this->contractor(['must_change_password' => true]);

        $this->actingAs($contractor, 'contractor')
            ->get(route('contractor.dashboard'))
            ->assertRedirect(route('password.first-login'));
    }

    private function warden(): User
    {
        return User::query()->create([
            'name' => 'Warden',
            'employee_id' => 'WARDEN001',
            'email' => 'warden@example.edu',
            'password' => 'warden-password',
            'role' => 'warden',
            'hostel' => 'CV Raman',
        ]);
    }

    private function contractor(array $attributes = []): User
    {
        $wardenId = $attributes['warden_id'] ?? $this->warden()->id;

        $user = User::query()->create(array_merge([
            'name' => 'Contractor',
            'employee_id' => 'EMP001',
            'email' => 'contractor@example.edu',
            'phone' => '+919999000014',
            'password' => 'initial-password',
            'role' => 'contractor',
            'hostel' => 'CV Raman',
            'warden_id' => $wardenId,
            'account_approved_at' => now(),
        ], $attributes));
        $user->forceFill(['must_change_password' => $attributes['must_change_password'] ?? false])->save();

        return $user;
    }
}
