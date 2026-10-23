<?php

namespace Tests\Feature;

use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_related_demo_data_idempotently(): void
    {
        $this->travelTo(Carbon::parse('2026-10-23 12:00:00'));

        $admin = User::query()->create([
            'name' => 'Existing Admin',
            'college_id' => 'ADMIN001',
            'email' => 'admin@college.edu',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->seed();

        $student = User::query()->where('college_id', 'STU0001')->firstOrFail();
        $breakfastSlot = MealTimeSlot::query()->where('meal_type', 'BREAKFAST')->firstOrFail();
        $breakfastMenu = Menu::query()
            ->where('meal_type', 'breakfast')
            ->where('meal_time_slot_id', $breakfastSlot->id)
            ->firstOrFail();

        $this->assertDatabaseCount('users', 12);
        $this->assertDatabaseCount('meal_time_slots', 4);
        $this->assertDatabaseCount('menus', 56);
        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-19 07:00:00',
        ]);
        $this->assertModelExists($admin);

        $this->seed();

        $this->assertDatabaseCount('users', 12);
        $this->assertDatabaseCount('meal_time_slots', 4);
        $this->assertDatabaseCount('menus', 56);
        $this->assertModelExists($admin);
        $this->assertSame($student->id, User::query()->where('college_id', 'STU0001')->value('id'));
        $this->assertModelExists($breakfastMenu);
    }
}
