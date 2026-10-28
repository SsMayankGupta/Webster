<?php

namespace Tests\Feature;

use App\Livewire\ContractorLeave;
use App\Livewire\StudentPortal;
use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\MealRefund;
use App\Models\Menu;
use App\Models\RebateLedger;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\BillingService;
use App\Services\RebateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PortalLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_day_leave_request_is_submitted_from_the_portal(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->set('leaveFrom', now()->addDay()->toDateString())
            ->set('leaveTo', now()->addDays(3)->toDateString())
            ->set('leaveReason', 'Home visit')
            ->call('requestLeave')
            ->assertHasNoErrors();

        $leave = LeaveRequest::query()->sole();
        $this->assertSame(now()->addDays(3)->toDateString(), $leave->to_date->toDateString());
        $this->assertSame(now()->addDays(3)->toDateString(), $leave->requested_to_date->toDateString());
        $this->assertSame('Home visit', $leave->reason);
        $this->assertSame('pending', $leave->status);
    }

    public function test_end_date_must_not_precede_the_start_date(): void
    {
        $student = $this->student();

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->set('leaveFrom', now()->addDays(4)->toDateString())
            ->set('leaveTo', now()->addDay()->toDateString())
            ->call('requestLeave')
            ->assertHasErrors('leaveTo');
    }

    public function test_approval_posts_integer_paise_once_and_cancels_meals_in_range(): void
    {
        $student = $this->student();
        $contractor = $this->contractor();
        $leave = $this->leave($student, now()->addDay(), now()->addDays(3));

        $menu = Menu::create([
            'meal_type' => 'lunch',
            'serve_at' => now()->addDays(2)->setTime(13, 0),
            'items' => ['Dal', 'Rice'],
            'status' => 'open',
        ]);

        $booking = MealBooking::create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);

        $rebates = app(RebateService::class);
        $rebates->approve($leave, $contractor);
        $rebates->approve($leave->fresh(), $contractor);

        $this->assertSame('approved', $leave->fresh()->status);
        $this->assertSame(1, RebateLedger::query()->where('leave_request_id', $leave->id)->count());
        $this->assertDatabaseHas('rebate_ledgers', [
            'leave_request_id' => $leave->id,
            'amount_paise' => 15000,
        ]);
        $this->assertDatabaseHas('meal_bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_monthly_statement_subtracts_rebates_from_the_base_charge(): void
    {
        $student = $this->student();
        $leave = $this->leave($student, now()->addDay(), now()->addDays(3));

        app(RebateService::class)->approve($leave, $this->contractor());

        $statement = app(BillingService::class)->statement($student);

        $this->assertSame(300000, $statement['base_paise']);
        $this->assertSame(15000, $statement['rebate_paise']);
        $this->assertSame(285000, $statement['net_paise']);
        $this->assertCount(1, $statement['entries']);
    }

    public function test_each_approved_calendar_day_is_rebated_once_across_multiple_leave_requests(): void
    {
        $this->travelTo(now()->startOfDay());

        $student = $this->student();
        $contractor = $this->contractor();
        $firstLeave = $this->leave($student, today()->addDays(2), today()->addDays(3));
        $overlappingLeave = $this->leave($student, today()->addDays(3), today()->addDays(4));
        $rebates = app(RebateService::class);

        $rebates->approve($firstLeave, $contractor);
        $rebates->approve($overlappingLeave, $contractor);

        $this->assertDatabaseHas('rebate_ledgers', [
            'leave_request_id' => $firstLeave->id,
            'amount_paise' => 10000,
        ]);
        $this->assertDatabaseHas('rebate_ledgers', [
            'leave_request_id' => $overlappingLeave->id,
            'amount_paise' => 5000,
        ]);

        $statement = app(BillingService::class)->statement($student);
        $this->assertSame(15000, $statement['rebate_paise']);
        $this->assertSame(285000, $statement['net_paise']);
        $this->assertCount(2, $statement['entries']);
    }

    public function test_portal_state_exposes_the_latest_statement(): void
    {
        $student = $this->student();
        $leave = $this->leave($student, now()->addDay(), now()->addDays(2));

        app(RebateService::class)->approve($leave, $this->contractor());

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);

        $state = $component->get('state');

        $this->assertSame(10000, $state['billing']['rebate_paise']);
        $this->assertSame('approved', $state['leaves'][0]['status']);
        $this->assertSame(10000, $state['leaves'][0]['rebate_paise']);
        $this->assertSame(10000, $state['wallet']['approved_leave_rebate_paise']);

        $component->call('setTab', 'leave')
            ->assertSee('₹100.00 net rebate credited');
    }

    public function test_student_leave_history_shows_early_return_date_and_recalculated_rebate(): void
    {
        $this->travelTo('2026-10-28 12:00:00');
        $contractor = $this->contractor();
        $student = $this->student();
        $student->forceFill(['contractor_id' => $contractor->id])->save();
        $leave = $this->leave($student, today()->subDays(2), today()->addDays(2));
        app(RebateService::class)->approve($leave, $contractor);
        $this->actingAs($student, 'student');
        $studentPortal = Livewire::test(StudentPortal::class);
        $this->assertFalse($studentPortal->get('state')['leaves'][0]['ended_early']);

        $this->actingAs($contractor, 'contractor');

        Livewire::test(ContractorLeave::class)
            ->set("returnDates.{$leave->id}", today()->subDay()->toDateString())
            ->call('updateLeaveEndDate', $leave->id)
            ->assertHasNoErrors();

        $this->actingAs($student, 'student');

        $component = $studentPortal->call('setTab', 'leave');
        $state = $component->get('state');
        $leaveState = collect($state['leaves'])->firstWhere('id', $leave->id);

        $this->assertSame(today()->subDay()->toDateString(), $leaveState['to_date']);
        $this->assertSame(today()->addDays(2)->toDateString(), $leaveState['requested_to_date']);
        $this->assertTrue($leaveState['ended_early']);
        $this->assertSame(10000, $leaveState['rebate_paise']);
        $this->assertSame(10000, $state['billing']['rebate_paise']);
        $this->assertCount(1, $leaveState['end_date_modifications']);
        $this->assertSame($contractor->name, $leaveState['end_date_modifications'][0]['modified_by_name']);
        $this->assertSame(-15000, $leaveState['end_date_modifications'][0]['rebate_adjustment_paise']);
        $component
            ->assertSee('End date modified by contractor')
            ->assertSee('originally approved through '.today()->addDays(2)->format('d M Y'))
            ->assertSee('End-date modification history')
            ->assertSee(today()->addDays(2)->format('d M Y').' → '.today()->subDay()->format('d M Y'))
            ->assertSee('Modified by')
            ->assertSee('₹100.00 net rebate credited');
    }

    public function test_student_wallet_shows_recorded_fees_refunds_and_transfer_status(): void
    {
        $student = $this->student();
        $leave = $this->leave($student, now()->subDays(3), now()->subDays(2));
        $leave->update(['status' => 'approved']);
        StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'amount_paise' => 125050,
        ]);
        MealRefund::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $leave->id,
            'amount_paise' => 7525,
            'status' => 'pending',
        ]);
        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class)
            ->call('setTab', 'leave');
        $state = $component->get('state');

        $this->assertSame(125050, $state['wallet']['fee_paid_paise']);
        $this->assertSame(7525, $state['wallet']['meal_refund_paise']);
        $this->assertSame(7525, $state['wallet']['pending_refund_paise']);
        $this->assertSame('pending', $state['wallet']['refunds'][0]['status']);
        $component->assertSee('My wallet')
            ->assertSee('Fees submitted')
            ->assertSee('Meal refunds')
            ->assertSee('Awaiting transfer');
    }

    public function test_student_wallet_totals_follow_semester_dates_and_keep_older_history(): void
    {
        $contractor = $this->contractor();
        $student = $this->student();
        $student->forceFill(['contractor_id' => $contractor->id])->save();
        $contractor->forceFill([
            'semester_name' => 'Semester 2',
            'semester_starts_at' => today()->subDays(10),
            'semester_ends_at' => today()->addDays(10),
        ])->save();
        $oldLeave = $this->leave($student, today()->subDays(45), today()->subDays(44));
        $oldLeave->update(['status' => 'approved']);
        $currentLeave = $this->leave($student, today()->subDays(3), today()->subDays(2));
        $currentLeave->update(['status' => 'approved']);

        $oldPayment = StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'amount_paise' => 1000,
        ]);
        $oldPayment->forceFill(['created_at' => today()->subDays(40)])->save();
        StudentFeePayment::query()->create([
            'student_id' => $student->id,
            'amount_paise' => 8000,
        ]);

        RebateLedger::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $oldLeave->id,
            'amount' => 20,
            'amount_paise' => 2000,
            'reason' => 'Older leave rebate',
            'created_at' => today()->subDays(40),
        ]);
        RebateLedger::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $currentLeave->id,
            'amount' => 25,
            'amount_paise' => 2500,
            'reason' => 'Current leave rebate',
            'created_at' => now(),
        ]);
        $oldRefund = MealRefund::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $oldLeave->id,
            'amount_paise' => 500,
            'status' => 'transferred',
        ]);
        $oldRefund->forceFill(['created_at' => today()->subDays(40)])->save();
        MealRefund::query()->create([
            'student_id' => $student->id,
            'leave_request_id' => $currentLeave->id,
            'amount_paise' => 600,
            'status' => 'transferred',
        ]);
        $this->actingAs($student, 'student');

        $state = Livewire::test(StudentPortal::class)->get('state');

        $this->assertSame(
            'Semester 2 · '.today()->subDays(10)->format('d M Y').' – '.today()->addDays(10)->format('d M Y'),
            $state['wallet']['period_label'],
        );
        $this->assertSame(8000, $state['wallet']['fee_paid_paise']);
        $this->assertSame(600, $state['wallet']['meal_refund_paise']);
        $this->assertSame(600, $state['wallet']['transferred_refund_paise']);
        $this->assertSame(2500, $state['wallet']['approved_leave_rebate_paise']);
        $this->assertCount(2, $state['wallet']['fee_payments']);
        $this->assertCount(2, $state['wallet']['refunds']);
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Asha Rao',
            'college_id' => 'STU001',
            'email' => 'asha@college.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
    }

    private function contractor(): User
    {
        return User::create([
            'name' => 'Warden',
            'college_id' => 'CON001',
            'email' => 'warden@college.edu',
            'password' => Hash::make('password'),
            'role' => 'contractor',
        ]);
    }

    private function leave(User $student, \DateTimeInterface $from, \DateTimeInterface $to): LeaveRequest
    {
        return LeaveRequest::create([
            'student_id' => $student->id,
            'from_date' => $from,
            'to_date' => $to,
            'reason' => 'Home visit',
            'status' => 'pending',
        ]);
    }
}
