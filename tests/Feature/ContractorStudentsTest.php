<?php

namespace Tests\Feature;

use App\Livewire\ContractorStudents;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\StudentFeePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorStudentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_can_add_a_student_to_their_hostel(): void
    {
        $contractor = $this->contractor('CON001');
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->set('name', 'Student One')
            ->set('collegeId', 'STU001')
            ->set('email', 'student@example.edu')
            ->set('phone', '+919999000020')
            ->set('password', 'initial-password')
            ->set('passwordConfirmation', 'initial-password')
            ->call('addStudent')
            ->assertHasNoErrors();

        $student = User::query()->where('college_id', 'STU001')->firstOrFail();
        $this->assertSame($contractor->id, $student->contractor_id);
        $this->assertSame($contractor->hostel, $student->hostel);
        $this->assertNotNull($student->account_approved_at);
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check('initial-password', $student->password));
    }

    public function test_contractor_can_edit_archive_restore_and_permanently_delete_their_students(): void
    {
        $contractor = $this->contractor('CON006');
        $student = $this->student($contractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->call('editStudent', $student->id)
            ->set('name', 'Updated Student')
            ->set('collegeId', 'STU-CON006-UPDATED')
            ->call('updateStudent')
            ->assertHasNoErrors();

        $this->assertSame('Updated Student', $student->fresh()->name);
        $this->assertSame('STU-CON006-UPDATED', $student->fresh()->college_id);
        $this->assertTrue(Hash::check('password', $student->fresh()->password));

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->assertHasNoErrors();

        $this->assertNotNull($student->fresh()->archived_at);

        Livewire::test(ContractorStudents::class)
            ->call('restoreStudent', $student->id)
            ->assertHasNoErrors();

        $this->assertNull($student->fresh()->archived_at);

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->call('permanentlyDeleteStudent', $student->id)
            ->assertHasNoErrors();

        $this->assertModelMissing($student);
    }

    public function test_contractor_cannot_edit_or_archive_another_contractors_student(): void
    {
        $contractor = $this->contractor('CON007');
        $otherContractor = $this->contractor('CON008');
        $student = $this->student($otherContractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->call('editStudent', $student->id)
            ->assertNotFound();

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->assertNotFound();

        $this->assertModelExists($student);
    }

    public function test_student_with_portal_history_can_be_archived_but_not_permanently_deleted(): void
    {
        $contractor = $this->contractor('CON009');
        $student = $this->student($contractor);
        $menu = Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => now()->addDay(),
            'recipe_text' => 'Test menu',
            'status' => 'open',
        ]);
        $booking = MealBooking::query()->create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->call('permanentlyDeleteStudent', $student->id)
            ->assertHasErrors('deletion');

        $this->assertModelExists($student->fresh());
        $this->assertModelExists($booking);
        $this->assertNotNull($student->fresh()->archived_at);
    }

    public function test_student_with_fee_payment_history_cannot_be_permanently_deleted(): void
    {
        $contractor = $this->contractor('CON011');
        $student = $this->student($contractor);
        StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 10000,
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->call('archiveStudent', $student->id)
            ->call('permanentlyDeleteStudent', $student->id)
            ->assertHasErrors('deletion');

        $this->assertModelExists($student->fresh());
        $this->assertNotNull($student->fresh()->archived_at);
    }

    public function test_contractor_cannot_see_another_contractors_students(): void
    {
        $contractor = $this->contractor('CON002');
        $otherContractor = $this->contractor('CON003');
        $student = $this->student($otherContractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorStudents::class)
            ->assertDontSee($student->name);
    }

    public function test_student_cannot_open_contractor_student_management(): void
    {
        $contractor = $this->contractor('CON004');
        $student = $this->student($contractor);

        $this->actingAs($student, 'contractor')
            ->get(route('contractor.students.index'))
            ->assertForbidden();
    }

    public function test_archived_contractor_is_logged_out_of_management_pages(): void
    {
        $contractor = $this->contractor('CON010');
        $contractor->forceFill(['archived_at' => now()])->save();

        $this->actingAs($contractor, 'contractor')
            ->get(route('contractor.students.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status');

        $this->assertGuest('contractor');
    }

    public function test_contractor_does_not_need_a_separate_approval_to_manage_students(): void
    {
        $contractor = $this->contractor('CON005');
        $contractor->forceFill(['account_approved_at' => null])->save();
        $this->actingAs($contractor, 'contractor');

        $this->get(route('contractor.students.index'))->assertOk();
    }

    private function contractor(string $employeeId): User
    {
        $warden = User::query()->create([
            'name' => 'Warden',
            'email' => strtolower($employeeId).'@warden.edu',
            'password' => 'password',
            'role' => 'warden',
        ]);

        return User::query()->create([
            'name' => 'Contractor '.$employeeId,
            'employee_id' => $employeeId,
            'email' => strtolower($employeeId).'@college.edu',
            'password' => 'password',
            'role' => 'contractor',
            'warden_id' => $warden->id,
            'hostel' => 'CV Raman',
            'must_change_password' => false,
        ]);
    }

    private function student(User $contractor): User
    {
        return User::query()->create([
            'name' => 'Student '.$contractor->employee_id,
            'college_id' => 'STU'.$contractor->employee_id,
            'email' => 'student-'.$contractor->employee_id.'@college.edu',
            'password' => 'password',
            'role' => 'student',
            'contractor_id' => $contractor->id,
            'hostel' => $contractor->hostel,
        ]);
    }
}
