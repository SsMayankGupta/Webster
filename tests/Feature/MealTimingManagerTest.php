<?php

namespace Tests\Feature;

use App\Events\MenuUpdated;
use App\Livewire\MealTimingManager;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class MealTimingManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_can_update_meal_times_and_future_unlocked_menus(): void
    {
        $this->travelTo('2026-10-28 00:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'TIMING001',
            'email' => 'timing-contractor@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $breakfastMenu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-29 01:30:00',
            'items' => ['Oatmeal'],
            'status' => 'open',
            'is_locked' => false,
        ]);
        $lockedLunch = Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-29 06:30:00',
            'items' => ['Rice'],
            'status' => 'locked',
            'is_locked' => true,
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MealTimingManager::class)
            ->assertSet('timeSlots.breakfast.start_time', '07:00')
            ->set('timeSlots.breakfast.start_time', '08:15')
            ->set('timeSlots.breakfast.end_time', '10:00')
            ->set('timeSlots.breakfast.cutoff_hours_before', '3')
            ->call('saveTimings')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', 'Meal timings updated successfully. Future unlocked menus were adjusted to the new service times.')
            ->assertSee('Meal timings updated successfully. Future unlocked menus were adjusted to the new service times.')
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Meal timings updated successfully!',
            );

        $this->assertDatabaseHas('meal_time_slots', [
            'meal_type' => 'BREAKFAST',
            'start_time' => '08:15:00',
            'end_time' => '10:00:00',
            'cutoff_hours_before' => 3,
        ]);
        $this->assertDatabaseHas('menus', [
            'id' => $breakfastMenu->id,
            'serve_at' => '2026-10-29 02:45:00',
        ]);
        $this->assertDatabaseHas('menus', [
            'id' => $lockedLunch->id,
            'serve_at' => '2026-10-29 06:30:00',
            'meal_time_slot_id' => null,
        ]);
        $this->assertSame(
            '2026-10-28 23:45:00',
            $breakfastMenu->fresh()->cutoffAt()->toDateTimeString(),
        );
        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_contractor_must_set_end_time_after_start_time(): void
    {
        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'TIMING002',
            'email' => 'timing-contractor2@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MealTimingManager::class)
            ->set('timeSlots.breakfast.start_time', '10:00')
            ->set('timeSlots.breakfast.end_time', '09:00')
            ->call('saveTimings')
            ->assertHasErrors('timeSlots.breakfast.end_time');

        $this->assertDatabaseCount('meal_time_slots', 0);
    }

    public function test_non_contractors_cannot_change_meal_times(): void
    {
        $student = User::query()->create([
            'name' => 'Student',
            'college_id' => 'TIMING003',
            'email' => 'timing-student@college.edu',
            'password' => 'password',
            'role' => 'student',
        ]);

        $this->actingAs($student, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MealTimingManager::class)
            ->set('timeSlots.breakfast.start_time', '08:00')
            ->call('saveTimings')
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Unauthorized action.',
            );

        $this->assertDatabaseCount('meal_time_slots', 0);
    }
}
