<?php

namespace Tests\Feature;

use App\Livewire\StudentPortal;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use App\Services\MealLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PortalMealTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_are_opted_in_by_default_for_open_meals(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addDay());

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class);

        $this->assertDatabaseHas('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);
    }

    public function test_student_can_confirm_a_meal_with_a_dietary_preference(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addDays(2));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('confirmMeal', $menu->id, true, 'jain')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
            'dietary_pref' => 'jain',
        ]);
    }

    public function test_student_can_opt_out_before_the_cutoff(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(6));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('cancelMeal', $menu->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('meal_bookings', [
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_late_confirmation_is_rejected_and_not_persisted(): void
    {
        $student = $this->student();
        $menu = $this->menu(now()->addHours(3));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
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
