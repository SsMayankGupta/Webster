<?php

namespace Tests\Feature;

use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CancellationTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::create([
            'name' => 'S', 'college_id' => 'S1', 'password' => Hash::make('password'), 'role' => 'student',
        ]);
    }

    public function test_cancel_allowed_when_more_than_4_hours(): void
    {
        $student = $this->student();
        $menu = Menu::create(['meal_type' => 'lunch', 'serve_at' => now()->addHours(6), 'recipe_text' => 'x', 'status' => 'open']);
        $booking = MealBooking::create(['student_id' => $student->id, 'menu_id' => $menu->id, 'status' => 'booked']);

        $response = $this->actingAs($student, 'student')
            ->post(route('student.bookings.cancel', $booking));

        $response->assertRedirect();
        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_cancel_rejected_when_less_than_4_hours(): void
    {
        $student = $this->student();
        $menu = Menu::create(['meal_type' => 'lunch', 'serve_at' => now()->addHours(2), 'recipe_text' => 'x', 'status' => 'open']);
        $booking = MealBooking::create(['student_id' => $student->id, 'menu_id' => $menu->id, 'status' => 'booked']);

        $response = $this->actingAs($student, 'student')
            ->post(route('student.bookings.cancel', $booking));

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'booked']);
    }
}
