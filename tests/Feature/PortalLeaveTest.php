<?php

namespace Tests\Feature;

use App\Livewire\StudentPortal;
use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\RebateLedger;
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

        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $student->id,
            'reason' => 'Home visit',
            'status' => 'pending',
        ]);
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
