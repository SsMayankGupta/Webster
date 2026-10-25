<?php

namespace Tests\Feature;

use App\Livewire\MenuUpdate;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class MenuUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_upcoming_menu_weeks_are_shown_without_loading_past_menus(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR005',
            'email' => 'contractor5@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-24 12:00:00',
            'items' => ['Older current-week meal'],
            'status' => 'open',
        ]);

        $nextWeekMenu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-26 07:00:00',
            'items' => ['Oatmeal'],
            'status' => 'open',
        ]);

        $laterWeekMenu = Menu::query()->create([
            'meal_type' => 'dinner',
            'serve_at' => '2026-11-03 19:00:00',
            'items' => ['Vegetable curry'],
            'status' => 'open',
        ]);

        Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-18 12:00:00',
            'items' => ['Old menu'],
            'status' => 'open',
        ]);

        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MenuUpdate::class)
            ->assertSet('menus.2026-10-24.lunch.items', ['Older current-week meal'])
            ->assertSet('menus.2026-10-26.breakfast.id', $nextWeekMenu->id)
            ->assertSet('menus.2026-10-26.breakfast.items', ['Oatmeal'])
            ->assertSet('menus.2026-11-03.dinner.id', $laterWeekMenu->id)
            ->assertDontSee('Sunday, October 18')
            ->assertSee('Week of Oct 26 – Nov 1')
            ->assertSee('Week of Nov 2 – Nov 8')
            ->assertSee('Load previous menu data')
            ->assertSee('Update menu')
            ->assertSee('Update meal');
    }

    public function test_previous_menu_data_loads_one_week_at_a_time_without_hiding_upcoming_menus(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR006',
            'email' => 'contractor6@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        $pastMenu = Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-18 12:00:00',
            'items' => ['Previous Sunday meal'],
            'status' => 'open',
        ]);

        $upcomingMenu = Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-24 12:00:00',
            'items' => ['Rice'],
            'status' => 'open',
        ]);

        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MenuUpdate::class)
            ->assertSet('previousWeeksLoaded', 0)
            ->assertSet('hasPreviousMenus', true)
            ->assertSet('menus.2026-10-24.lunch.id', $upcomingMenu->id)
            ->assertDontSee('Previous Sunday meal')
            ->call('loadPreviousData')
            ->assertSet('previousWeeksLoaded', 1)
            ->assertSet('hasPreviousMenus', false)
            ->assertSet('menus.2026-10-18.lunch.id', $pastMenu->id)
            ->assertSet('menus.2026-10-18.lunch.items', ['Previous Sunday meal'])
            ->assertSet('menus.2026-10-24.lunch.id', $upcomingMenu->id)
            ->assertDontSee('Load previous menu data')
            ->assertSee('Update menu');
    }
}
