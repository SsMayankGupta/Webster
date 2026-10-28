<?php

namespace Tests\Feature;

use App\Events\MenuUpdated;
use App\Livewire\ContractorMenuBuilder;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Models\User;
use App\Services\NutritionApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorMenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_contractor_page_redirects_to_contractor_login(): void
    {
        $this->get(route('contractor.menu-builder'))
            ->assertRedirect(route('contractor.login'));
    }

    public function test_cloning_a_past_week_creates_menus_in_the_current_seven_day_period(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR001',
            'email' => 'contractor@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        $pastMenu = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-12 07:00:00',
            'items' => ['Idli', 'Sambar'],
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        $component = Livewire::test(ContractorMenuBuilder::class);

        $this->assertCount(1, $component->get('pastWeeks'));

        $component
            ->set('selectedWeek', 0)
            ->call('clonePastWeek')
            ->assertHasNoErrors()
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Menu cloned successfully for the current seven-day period!',
            );

        $this->assertDatabaseCount('menus', 2);
        $clonedMenu = Menu::query()->latest('id')->firstOrFail();

        $this->assertSame('breakfast', $clonedMenu->meal_type);
        $this->assertSame('2026-10-23', $clonedMenu->serve_at->toDateString());
        $this->assertSame('open', $clonedMenu->status);
        $this->assertSame(['Idli', 'Sambar'], $clonedMenu->items);
        $this->assertModelExists($pastMenu);

        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_cloning_a_past_week_skips_existing_day_and_meal_slots(): void
    {
        $this->travelTo('2026-10-28 06:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR007',
            'email' => 'contractor7@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-12 07:00:00',
            'items' => ['Past breakfast'],
            'status' => 'open',
        ]);
        Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-13 12:00:00',
            'items' => ['Past lunch'],
            'status' => 'open',
        ]);
        Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-28 07:00:00',
            'items' => ['Existing breakfast'],
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->set('selectedWeek', 1)
            ->call('clonePastWeek')
            ->assertSet('scheduleCreatedSuccessfully', true)
            ->assertSet('skippedMenuSlots', 1)
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Menu cloned successfully for the current seven-day period! 1 existing meal slot(s) were left unchanged.',
            );

        $this->assertDatabaseCount('menus', 4);
        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-28 07:00:00',
            'items' => json_encode(['Existing breakfast']),
        ]);
        $this->assertDatabaseHas('menus', [
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-29 12:00:00',
            'items' => json_encode(['Past lunch']),
        ]);
        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_existing_menus_in_the_seven_day_period_are_reported_without_blocking_additions(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR002',
            'email' => 'contractor2@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-28 12:00:00',
            'items' => ['Rice'],
            'status' => 'open',
        ]);

        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->assertSee('Some meals are already scheduled in this seven-day period.')
            ->assertSee('Menus entered here—AI-generated, cloned, or manual—are scheduled from today through six days from today:')
            ->assertSee('Friday, October 23')
            ->assertSee('Thursday, October 29')
            ->assertSee('Wednesday, October 28')
            ->assertSee(route('contractor.menu-update'));
    }

    public function test_saving_a_menu_shows_success_and_scheduled_date_notice(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR003',
            'email' => 'contractor3@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        $this->mock(NutritionApiService::class, function ($mock): void {
            $mock->shouldReceive('getNutritionForIngredients')
                ->once()
                ->with(['Oatmeal'])
                ->andReturn([
                    'protein' => 5.0,
                    'carbs' => 20.0,
                    'calories' => 120.0,
                    'fat' => 2.0,
                ]);
        });

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->set('schedule.Monday.breakfast.items', 'Oatmeal')
            ->call('saveSchedule')
            ->assertSet('scheduleCreatedSuccessfully', true)
            ->assertSee('Menu created successfully.')
            ->assertSee('Menus entered here—AI-generated, cloned, or manual—are scheduled from today through six days from today:')
            ->assertSee('Friday, October 23')
            ->assertSee('Thursday, October 29')
            ->assertSee(route('contractor.menu-update'))
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Schedule saved successfully!',
            );

        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-26 01:30:00',
            'recipe_text' => 'Oatmeal',
            'status' => 'open',
        ]);

        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_saving_a_menu_uses_the_configured_local_meal_time(): void
    {
        $this->travelTo('2026-10-23 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR008',
            'email' => 'contractor8@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $slot = MealTimeSlot::query()->create([
            'meal_type' => 'BREAKFAST',
            'start_time' => '08:15:00',
            'end_time' => '10:00:00',
            'cutoff_hours_before' => 3,
        ]);

        $this->mock(NutritionApiService::class, function ($mock): void {
            $mock->shouldReceive('getNutritionForIngredients')
                ->once()
                ->with(['Oatmeal'])
                ->andReturn(['protein' => 5.0, 'carbs' => 20.0, 'calories' => 120.0, 'fat' => 2.0]);
        });

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->set('schedule.Monday.breakfast.items', 'Oatmeal')
            ->call('saveSchedule')
            ->assertSet('scheduleCreatedSuccessfully', true);

        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-26 02:45:00',
            'meal_time_slot_id' => $slot->id,
        ]);
        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_saving_a_schedule_adds_missing_meal_slots_and_preserves_existing_menus(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR005',
            'email' => 'contractor5@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        $existingBreakfast = Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-28 07:00:00',
            'items' => ['Existing breakfast'],
            'recipe_text' => 'Existing breakfast',
            'status' => 'open',
        ]);
        Menu::query()->create([
            'meal_type' => 'lunch',
            'serve_at' => '2026-10-29 12:00:00',
            'items' => ['Existing lunch'],
            'recipe_text' => 'Existing lunch',
            'status' => 'open',
        ]);

        $this->mock(NutritionApiService::class, function ($mock): void {
            $mock->shouldReceive('getNutritionForIngredients')
                ->once()
                ->with(['Toast'])
                ->andReturn(['protein' => 3.0, 'carbs' => 12.0, 'calories' => 90.0, 'fat' => 1.0]);
        });

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->set('schedule.Wednesday.breakfast.items', 'Replacement breakfast')
            ->set('schedule.Thursday.breakfast.items', 'Toast')
            ->call('saveSchedule')
            ->assertSet('scheduleCreatedSuccessfully', true)
            ->assertSee('1 meal slot(s) already had menus and were left unchanged.')
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Schedule saved successfully! 1 existing meal slot(s) were left unchanged.',
            );

        $this->assertDatabaseCount('menus', 3);
        $this->assertDatabaseHas('menus', [
            'id' => $existingBreakfast->id,
            'recipe_text' => 'Existing breakfast',
        ]);
        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-29 01:30:00',
            'recipe_text' => 'Toast',
        ]);
        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_saving_only_existing_meal_slots_does_not_create_duplicates(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR006',
            'email' => 'contractor6@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);
        Menu::query()->create([
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-28 07:00:00',
            'items' => ['Existing breakfast'],
            'recipe_text' => 'Existing breakfast',
            'status' => 'open',
        ]);

        Event::fake([MenuUpdated::class]);
        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->set('schedule.Wednesday.breakfast.items', 'Another breakfast')
            ->call('saveSchedule')
            ->assertSet('scheduleCreatedSuccessfully', false)
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Those day and meal slots already have menus. Existing menus were left unchanged.',
            );

        $this->assertDatabaseCount('menus', 1);
        Event::assertNotDispatched(MenuUpdated::class);
    }

    public function test_saving_an_empty_schedule_does_not_show_a_success_notice(): void
    {
        $contractor = User::query()->create([
            'name' => 'Contractor',
            'college_id' => 'CONTRACTOR004',
            'email' => 'contractor4@college.edu',
            'password' => 'password',
            'role' => 'contractor',
        ]);

        $this->actingAs($contractor, 'contractor');
        Auth::shouldUse('contractor');

        Livewire::test(ContractorMenuBuilder::class)
            ->call('saveSchedule')
            ->assertSet('scheduleCreatedSuccessfully', false)
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Add at least one meal before saving the schedule.',
            );

        $this->assertDatabaseCount('menus', 0);
    }
}
