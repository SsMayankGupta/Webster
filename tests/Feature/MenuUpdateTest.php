<?php

namespace Tests\Feature;

use App\Events\MenuUpdated;
use App\Livewire\MenuUpdate;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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

    public function test_contractor_can_update_a_meal_from_the_bound_form_values(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR008',
            'email' => 'contractor8@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $menu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-29 07:00:00',
            'items' => ['Oatmeal'],
            'recipe_text' => 'Oatmeal',
            'protein' => 5,
            'carbs' => 20,
            'calories' => 120,
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MenuUpdate::class)
            ->assertSee('wire:click="updateMenu('.$menu->id.', \'2026-10-29\', \'breakfast\')"', false)
            ->assertSee('wire:target="updateMenu('.$menu->id.', \'2026-10-29\', \'breakfast\')"', false)
            ->assertDontSee('wire:target="updateMenu"', false)
            ->set('menus.2026-10-29.breakfast.items', 'Poha, Tea')
            ->set('menus.2026-10-29.breakfast.protein', '8')
            ->set('menus.2026-10-29.breakfast.carbs', '35')
            ->set('menus.2026-10-29.breakfast.calories', '240')
            ->call('updateMenu', $menu->id, '2026-10-29', 'breakfast')
            ->assertHasNoErrors()
            ->assertSet('menus.2026-10-29.breakfast.items', ['Poha', 'Tea'])
            ->assertSet('statusMessage', 'Meal updated successfully.')
            ->assertSee('Meal updated successfully.')
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Menu updated successfully!',
            );

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'recipe_text' => 'Poha, Tea',
            'items' => json_encode(['Poha', 'Tea']),
            'protein' => 8,
            'carbs' => 35,
            'calories' => 240,
        ]);
        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_update_rejects_a_menu_id_that_does_not_match_the_selected_date_and_meal(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR009',
            'email' => 'contractor9@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $menu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-29 07:00:00',
            'items' => ['Oatmeal'],
            'recipe_text' => 'Oatmeal',
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MenuUpdate::class)
            ->set('menus.2026-10-29.breakfast.items', 'Changed')
            ->call('updateMenu', $menu->id, '2026-10-29', 'lunch')
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => str_contains($params['message'], 'no longer available'),
            );

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'recipe_text' => 'Oatmeal',
        ]);
        Event::assertNotDispatched(MenuUpdated::class);
    }

    public function test_update_rejects_invalid_nutrition_values_without_changing_the_meal(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR010',
            'email' => 'contractor10@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $menu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-29 07:00:00',
            'items' => ['Oatmeal'],
            'recipe_text' => 'Oatmeal',
            'protein' => 5,
            'carbs' => 20,
            'calories' => 120,
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(MenuUpdate::class)
            ->set('menus.2026-10-29.breakfast.items', 'Changed')
            ->set('menus.2026-10-29.breakfast.protein', '-1')
            ->set('menus.2026-10-29.breakfast.carbs', '30')
            ->set('menus.2026-10-29.breakfast.calories', '200')
            ->call('updateMenu', $menu->id, '2026-10-29', 'breakfast')
            ->assertHasErrors('menus.2026-10-29.breakfast.protein');

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'recipe_text' => 'Oatmeal',
            'protein' => 5,
        ]);
        Event::assertNotDispatched(MenuUpdated::class);
    }
}
