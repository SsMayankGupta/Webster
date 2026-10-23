<?php

namespace Tests\Feature;

use App\Livewire\ContractorHeadcount;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorHeadcountTest extends TestCase
{
    use RefreshDatabase;

    public function test_headcount_shows_only_todays_meals_and_counts_opted_in_students(): void
    {
        $this->travelTo(now()->startOfDay()->addHour());

        $contractor = $this->user('Contractor', 'contractor');
        $students = [
            $this->user('Student One', 'student'),
            $this->user('Student Two', 'student'),
            $this->user('Student Three', 'student'),
            $this->user('Student Four', 'student'),
            $this->user('Student Five', 'student'),
        ];

        $breakfast = $this->menu('breakfast', today()->setTime(7, 0));
        $lunch = $this->menu('lunch', today()->setTime(12, 0));
        $snacks = $this->menu('snacks', today()->setTime(16, 0));
        $dinner = $this->menu('dinner', today()->setTime(19, 0));
        $tomorrowBreakfast = $this->menu('breakfast', today()->addDay()->setTime(7, 0));

        MealBooking::query()->create(['student_id' => $students[0]->id, 'menu_id' => $breakfast->id, 'status' => 'booked']);
        MealBooking::query()->create(['student_id' => $students[1]->id, 'menu_id' => $breakfast->id, 'status' => 'consumed']);
        MealBooking::query()->create(['student_id' => $students[2]->id, 'menu_id' => $lunch->id, 'status' => 'cancelled']);
        MealBooking::query()->create(['student_id' => $students[3]->id, 'menu_id' => $snacks->id, 'status' => 'booked']);
        MealBooking::query()->create(['student_id' => $students[4]->id, 'menu_id' => $dinner->id, 'status' => 'booked']);
        MealBooking::query()->create(['student_id' => $students[0]->id, 'menu_id' => $tomorrowBreakfast->id, 'status' => 'booked']);

        $this->actingAs($contractor, 'contractor');

        $component = Livewire::test(ContractorHeadcount::class);

        $menus = collect($component->get('menus'))->keyBy('id');

        $this->assertSame(
            [$breakfast->id, $lunch->id, $snacks->id, $dinner->id],
            $menus->keys()->all(),
        );
        $this->assertSame(2, $menus[$breakfast->id]['opted_in_count']);
        $this->assertSame(0, $menus[$lunch->id]['opted_in_count']);
        $this->assertSame(1, $menus[$snacks->id]['opted_in_count']);
        $this->assertSame(1, $menus[$dinner->id]['opted_in_count']);

        $component
            ->assertSee('Today’s meal headcount')
            ->assertSee('Opted in')
            ->assertDontSee('Tomorrow')
            ->assertDontSee('Next 7 Days');
    }

    private function user(string $name, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'college_id' => strtoupper(str_replace(' ', '', $name)),
            'email' => strtolower(str_replace(' ', '.', $name)).'@college.edu',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function menu(string $mealType, \DateTimeInterface $serveAt): Menu
    {
        return Menu::query()->create([
            'meal_type' => $mealType,
            'serve_at' => $serveAt,
            'items' => ['Rice', 'Dal'],
        ]);
    }
}
