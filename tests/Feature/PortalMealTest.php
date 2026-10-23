<?php

namespace Tests\Feature;

use App\Livewire\StudentPortal;
use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\MealConsumption;
use App\Models\Menu;
use App\Models\User;
use App\Services\MealBookingService;
use App\Services\MealLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PortalMealTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_are_opted_out_until_they_opt_in(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));

        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);
        $meal = collect($component->get('state')['days'])
            ->flatMap(fn (array $day): array => $day['meals'])
            ->firstWhere('id', $menu->id);

        $this->assertSame(false, $meal['opted_in']);
        $component
            ->assertSee('Opted out')
            ->assertSee('Opt in')
            ->assertDontSee('Dietary preference for this meal');

        $this->assertDatabaseMissing('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);
    }

    public function test_opt_in_and_out_controls_are_only_shown_for_today(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));

        $student = $this->student();
        $todayMenu = $this->menu(now()->addHours(6));
        $tomorrowMenu = $this->menu(now()->addDay()->addHours(6));

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);

        $component
            ->assertSee('wire:click="confirmMeal('.$todayMenu->id.', true)"', false)
            ->assertDontSee('wire:click="confirmMeal('.$tomorrowMenu->id.', true)"', false);
    }

    public function test_redeemed_meal_refresh_shows_consumed_status_without_change_controls(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));

        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));
        $booking = MealBooking::create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);

        $booking->update(['status' => 'consumed']);
        MealConsumption::query()->create([
            'booking_id' => $booking->id,
            'mode' => 'online',
            'consumed_at' => now(),
        ]);

        $component
            ->call('refreshMealState')
            ->assertSee('Food consumed')
            ->assertSee('Done')
            ->assertDontSee('wire:click="cancelMeal('.$menu->id.')"', false)
            ->assertDontSee('wire:click="confirmMeal('.$menu->id.', true)"', false);

        $component
            ->call('confirmMeal', $menu->id, false)
            ->assertHasErrors('meal-'.$menu->id);

        $meal = collect($component->get('state')['days'])
            ->flatMap(fn (array $day): array => $day['meals'])
            ->firstWhere('id', $menu->id);

        $this->assertSame(true, $meal['is_consumed']);
        $this->assertSame(false, $meal['can_manage']);
        $this->assertSame(false, $meal['can_cancel']);
        $this->assertDatabaseHas('meal_bookings', [
            'id' => $booking->id,
            'status' => 'consumed',
        ]);
    }

    public function test_approved_leave_disables_meal_controls_and_prevents_booking_changes(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));

        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));
        LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->toDateString(),
            'to_date' => today()->toDateString(),
            'status' => 'approved',
        ]);

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);

        $meal = collect($component->get('state')['today']['meals'])->firstWhere('id', $menu->id);
        $this->assertSame(true, $meal['is_on_approved_leave']);
        $this->assertSame(false, $meal['can_manage']);

        $component
            ->assertSee('Approved leave')
            ->assertDontSee('wire:click="confirmMeal('.$menu->id.', true)"', false)
            ->call('confirmMeal', $menu->id, true)
            ->assertHasErrors('meal-'.$menu->id)
            ->call('cancelMeal', $menu->id)
            ->assertHasErrors('meal-'.$menu->id);

        $this->assertDatabaseMissing('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
        ]);
    }

    public function test_student_can_opt_in_to_a_meal_before_the_cutoff(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));

        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('confirmMeal', $menu->id, true)
            ->assertHasNoErrors()
            ->assertSee('Opted in');

        $this->assertDatabaseHas('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
            'dietary_pref' => null,
        ]);
    }

    public function test_student_can_opt_out_before_the_cutoff(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('confirmMeal', $menu->id, true)
            ->call('cancelMeal', $menu->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'cancelled',
        ]);

        $this->assertNull(app(MealBookingService::class)->activeBooking($student));
    }

    public function test_late_confirmation_is_rejected_and_not_persisted(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(3));

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);
        $meal = collect($component->get('state')['days'])
            ->flatMap(fn (array $day): array => $day['meals'])
            ->firstWhere('id', $menu->id);

        $this->assertSame(false, $meal['opted_in']);
        $component
            ->call('confirmMeal', $menu->id, true)
            ->assertHasErrors('meal-'.$menu->id);

        $this->assertDatabaseMissing('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);
    }

    public function test_late_confirmation_endpoint_returns_http_409(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(3));

        $response = $this->actingAs($student, 'student')->postJson(
            route('portal.meals.confirm', $menu),
            ['opt_in' => true],
        );

        $response->assertStatus(409);
    }

    public function test_meal_transition_locks_headcount_and_pins_a_lane(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(2));
        $booking = MealBooking::create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);

        $menu = app(MealLifecycleService::class)->sync($menu);

        $this->assertSame('locked', $menu->status);
        $this->assertTrue($menu->is_locked);
        $this->assertSame(1, $menu->headcount_locked);
        $this->assertContains($booking->fresh()->assigned_lane, config('mess.lanes'));
    }

    public function test_confirming_a_locked_meal_is_rejected(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(2));
        $menu->update(['status' => 'locked', 'is_locked' => true]);

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('confirmMeal', $menu->id, true)
            ->assertHasErrors('meal-'.$menu->id);
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

    private function menu(\DateTimeInterface $serveAt): Menu
    {
        return Menu::create([
            'meal_type' => 'lunch',
            'serve_at' => $serveAt,
            'items' => ['Dal', 'Rice', 'Sabzi'],
            'calories' => 620,
            'protein' => 22,
            'carbs' => 80,
            'status' => 'open',
        ]);
    }
}
