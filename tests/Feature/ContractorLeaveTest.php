<?php

namespace Tests\Feature;

use App\Livewire\ContractorLeave;
use App\Models\LeaveRequest;
use App\Models\MealRefund;
use App\Models\StudentFeePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_records_fee_and_approved_leave_refund_and_tracks_transfer(): void
    {
        $contractor = $this->contractor('contractor@example.edu');
        $student = $this->student($contractor, 'student@example.edu');
        $leave = $this->approvedLeave($student, $contractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $student->id)
            ->set('feeAmount', '1250.50')
            ->call('addFeePayment')
            ->assertHasNoErrors()
            ->set('selectedLeaveId', $leave->id)
            ->set('refundAmount', '75.25')
            ->call('addMealRefund')
            ->assertHasNoErrors();

        $refund = MealRefund::query()->sole();

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $student->id)
            ->call('markRefundTransferred', $refund->id)
            ->assertHasNoErrors()
            ->assertSee('Transferred');

        $this->assertDatabaseHas('student_fee_payments', [
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 125050,
        ]);
        $this->assertDatabaseHas('meal_refunds', [
            'student_id' => $student->id,
            'leave_request_id' => $leave->id,
            'recorded_by' => $contractor->id,
            'transferred_by' => $contractor->id,
            'amount_paise' => 7525,
            'status' => 'transferred',
        ]);
        $this->assertNotNull($refund->fresh()->transferred_at);
    }

    public function test_contractor_cannot_record_or_transfer_another_contractors_financial_records(): void
    {
        $contractor = $this->contractor('owner@example.edu');
        $otherContractor = $this->contractor('other@example.edu');
        $ownStudent = $this->student($contractor, 'own@example.edu');
        $otherStudent = $this->student($otherContractor, 'other-student@example.edu');
        $otherLeave = $this->approvedLeave($otherStudent, $otherContractor);
        $refund = MealRefund::query()->create([
            'student_id' => $otherStudent->id,
            'leave_request_id' => $otherLeave->id,
            'recorded_by' => $otherContractor->id,
            'amount_paise' => 5000,
            'status' => 'pending',
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->assertDontSee($otherStudent->name)
            ->call('approveLeave', $otherLeave->id)
            ->assertNotFound();

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $otherStudent->id)
            ->set('feeAmount', '10.00')
            ->call('addFeePayment')
            ->assertNotFound();

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $ownStudent->id)
            ->set('selectedLeaveId', $otherLeave->id)
            ->set('refundAmount', '10.00')
            ->call('addMealRefund')
            ->assertNotFound();

        Livewire::test(ContractorLeave::class)
            ->call('markRefundTransferred', $refund->id)
            ->assertNotFound();

        $this->assertDatabaseCount('student_fee_payments', 0);
        $this->assertSame('pending', $refund->fresh()->status);
    }

    public function test_payment_amounts_reject_more_than_two_decimal_places(): void
    {
        $contractor = $this->contractor('validation@example.edu');
        $student = $this->student($contractor, 'validation-student@example.edu');
        $leave = $this->approvedLeave($student, $contractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $student->id)
            ->set('feeAmount', '10.001')
            ->call('addFeePayment')
            ->assertHasErrors('feeAmount')
            ->set('selectedLeaveId', $leave->id)
            ->set('refundAmount', '2.345')
            ->call('addMealRefund')
            ->assertHasErrors('refundAmount');

        $this->assertDatabaseCount('student_fee_payments', 0);
        $this->assertDatabaseCount('meal_refunds', 0);
    }

    public function test_editable_semester_dates_control_the_contractor_wallet_totals(): void
    {
        $contractor = $this->contractor('semester@example.edu');
        $student = $this->student($contractor, 'semester-student@example.edu');
        $olderPayment = StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 2000,
        ]);
        $olderPayment->forceFill(['created_at' => today()->subDays(40)])->save();
        StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'recorded_by' => $contractor->id,
            'amount_paise' => 5000,
        ])->forceFill(['created_at' => today()->subDays(5)])->save();
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set('selectedStudentId', $student->id)
            ->set('semesterName', 'Semester 1')
            ->set('semesterStart', today()->subDays(10)->toDateString())
            ->set('semesterEnd', today()->addDays(10)->toDateString())
            ->call('saveSemesterPeriod')
            ->assertHasNoErrors()
            ->assertSee('₹50.00')
            ->set('semesterStart', today()->subDays(50)->toDateString())
            ->call('saveSemesterPeriod')
            ->assertHasNoErrors()
            ->assertSee('₹70.00');

        $this->assertSame(today()->subDays(50)->toDateString(), $contractor->fresh()->semester_starts_at->toDateString());
    }

    public function test_semester_end_date_cannot_precede_the_start_date(): void
    {
        $contractor = $this->contractor('semester-validation@example.edu');
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set('semesterName', 'Semester 1')
            ->set('semesterStart', today()->addDays(10)->toDateString())
            ->set('semesterEnd', today()->toDateString())
            ->call('saveSemesterPeriod')
            ->assertHasErrors('semesterEnd');

        $this->assertNull($contractor->fresh()->semester_starts_at);
        $this->assertNull($contractor->fresh()->semester_ends_at);
    }

    private function contractor(string $email): User
    {
        return User::query()->create([
            'name' => 'Contractor',
            'email' => $email,
            'password' => 'password',
            'role' => 'contractor',
        ]);
    }

    private function student(User $contractor, string $email): User
    {
        return User::query()->create([
            'name' => 'Student '.strtoupper(strtok($email, '@')),
            'college_id' => strtoupper(strtok($email, '@')),
            'email' => $email,
            'password' => 'password',
            'role' => 'student',
            'contractor_id' => $contractor->id,
        ]);
    }

    private function approvedLeave(User $student, User $contractor): LeaveRequest
    {
        return LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->subDays(3),
            'to_date' => today()->subDays(2),
            'reason' => 'Travel',
            'status' => 'approved',
            'approved_by' => $contractor->id,
        ]);
    }
}
