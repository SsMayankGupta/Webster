<?php

namespace Tests\Feature;

use App\Livewire\ContractorLeave;
use App\Models\LeaveRequest;
use App\Models\MealRefund;
use App\Models\RebateLedger;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\RebateService;
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
            ->call('updateLeaveEndDate', $otherLeave->id)
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

    public function test_contractor_modifies_leave_end_date_and_recalculates_append_only_rebate(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $contractor = $this->contractor('early-return@example.edu');
        $student = $this->student($contractor, 'early-return-student@example.edu');
        $leave = LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->subDays(4),
            'to_date' => today()->addDay(),
            'requested_to_date' => today()->addDay(),
            'reason' => 'Travel',
            'status' => 'pending',
        ]);
        app(RebateService::class)->approve($leave, $contractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set("returnDates.{$leave->id}", today()->subDay()->toDateString())
            ->call('updateLeaveEndDate', $leave->id)
            ->assertHasNoErrors()
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message']
                    === 'Leave end date updated. The student rebate was recalculated.',
            );

        $this->assertSame(today()->subDay()->toDateString(), $leave->fresh()->to_date->toDateString());
        $this->assertSame(today()->addDay()->toDateString(), $leave->fresh()->requested_to_date->toDateString());
        $this->assertSame(
            [30000, -10000],
            RebateLedger::query()->where('leave_request_id', $leave->id)->orderBy('id')->pluck('amount_paise')->all(),
        );
        $modification = $leave->endDateModifications()->sole();
        $this->assertSame($contractor->id, $modification->modified_by);
        $this->assertSame($contractor->name, $modification->modified_by_name);
        $this->assertSame(today()->addDay()->toDateString(), $modification->previous_to_date->toDateString());
        $this->assertSame(today()->subDay()->toDateString(), $modification->new_to_date->toDateString());
        $this->assertSame(-10000, $modification->rebate_adjustment_paise);

        Livewire::test(ContractorLeave::class)
            ->set("returnDates.{$leave->id}", today()->addDay()->toDateString())
            ->call('updateLeaveEndDate', $leave->id)
            ->assertHasNoErrors();

        $this->assertSame(today()->addDay()->toDateString(), $leave->fresh()->to_date->toDateString());
        $this->assertSame(
            [30000, -10000, 10000],
            RebateLedger::query()->where('leave_request_id', $leave->id)->orderBy('id')->pluck('amount_paise')->all(),
        );
        $modifications = $leave->endDateModifications()->orderBy('id')->get();
        $this->assertSame(today()->subDay()->toDateString(), $modifications[1]->previous_to_date->toDateString());
        $this->assertSame(today()->addDay()->toDateString(), $modifications[1]->new_to_date->toDateString());
        $this->assertSame(10000, $modifications[1]->rebate_adjustment_paise);

        $component = Livewire::test(ContractorLeave::class);
        $this->assertSame(2, count($component->get('leaves')[0]['end_date_modifications']));
        $component
            ->assertSee('End-date modification history')
            ->assertSee(today()->subDay()->format('d M Y').' → '.today()->addDay()->format('d M Y'));
    }

    public function test_contractor_can_see_end_date_option_when_local_date_is_ahead_of_utc_date(): void
    {
        $this->travelTo('2026-10-28 19:01:00 UTC');
        $contractor = $this->contractor('local-date@example.edu');
        $student = $this->student($contractor, 'local-date-student@example.edu');
        $leave = LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => '2026-10-29',
            'to_date' => '2026-10-31',
            'requested_to_date' => '2026-10-31',
            'status' => 'approved',
            'approved_by' => $contractor->id,
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->assertSee('Modify end date')
            ->call('editLeaveEndDate', $leave->id)
            ->assertSet("returnDates.{$leave->id}", '2026-10-31')
            ->assertSee('New leave end date');
    }

    public function test_contractor_cannot_extend_an_approved_leave(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $contractor = $this->contractor('return-validation@example.edu');
        $student = $this->student($contractor, 'return-validation-student@example.edu');
        $leave = $this->approvedLeave($student, $contractor);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set("returnDates.{$leave->id}", today()->subDay()->toDateString())
            ->call('updateLeaveEndDate', $leave->id)
            ->assertHasErrors("returnDates.{$leave->id}");

        $this->assertSame(today()->subDays(2)->toDateString(), $leave->fresh()->to_date->toDateString());
        $this->assertDatabaseCount('rebate_ledgers', 0);
    }

    public function test_contractor_can_extend_a_modified_leave_within_original_approval_dates(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $contractor = $this->contractor('future-return@example.edu');
        $student = $this->student($contractor, 'future-return-student@example.edu');
        $leave = LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->subDays(3),
            'to_date' => today()->subDays(2),
            'requested_to_date' => today()->addDays(2),
            'status' => 'approved',
            'approved_by' => $contractor->id,
        ]);
        RebateLedger::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $leave->id,
            'amount' => 100,
            'amount_paise' => 10000,
            'reason' => 'Initial leave rebate',
            'created_at' => now(),
        ]);
        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set("returnDates.{$leave->id}", today()->toDateString())
            ->call('updateLeaveEndDate', $leave->id)
            ->assertHasNoErrors();

        $this->assertSame(today()->toDateString(), $leave->fresh()->to_date->toDateString());
        $modification = $leave->endDateModifications()->sole();
        $this->assertSame(today()->subDays(2)->toDateString(), $modification->previous_to_date->toDateString());
        $this->assertSame(today()->toDateString(), $modification->new_to_date->toDateString());
        $this->assertSame(10000, $modification->rebate_adjustment_paise);
        $this->assertSame(
            [10000, 10000],
            RebateLedger::query()->where('leave_request_id', $leave->id)->orderBy('id')->pluck('amount_paise')->all(),
        );
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
