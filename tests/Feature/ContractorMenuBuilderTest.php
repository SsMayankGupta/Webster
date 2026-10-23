<?php

namespace Tests\Feature;

use App\Events\MenuUpdated;
use App\Livewire\ContractorMenuBuilder;
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

    public function test_cloning_a_past_week_creates_menus_with_valid_open_status(): void
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
                fn (string $event, array $params): bool => $params['message'] === 'Menu cloned successfully for next week!',
            );

        $this->assertDatabaseCount('menus', 2);
        $clonedMenu = Menu::query()->latest('id')->firstOrFail();

        $this->assertSame('breakfast', $clonedMenu->meal_type);
        $this->assertSame('open', $clonedMenu->status);
        $this->assertSame(['Idli', 'Sambar'], $clonedMenu->items);
        $this->assertModelExists($pastMenu);

        Event::assertDispatched(MenuUpdated::class);
    }

    public function test_existing_next_week_menus_show_their_database_dates_and_edit_link(): void
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
            ->assertSee('Menus are already scheduled for next week.')
            ->assertSee('Any menu created here—AI-generated, cloned, or entered manually—will be scheduled from')
            ->assertSee('Monday, October 26')
            ->assertSee('Sunday, November 1')
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
            ->assertSee('Any menu created here—AI-generated, cloned, or entered manually—will be scheduled from')
            ->assertSee('Monday, October 26')
            ->assertSee('Sunday, November 1')
            ->assertSee(route('contractor.menu-update'))
            ->assertDispatched(
                'portal-toast',
                fn (string $event, array $params): bool => $params['message'] === 'Schedule saved successfully!',
            );

        $this->assertDatabaseHas('menus', [
            'meal_type' => 'breakfast',
            'serve_at' => '2026-10-26 00:00:00',
            'recipe_text' => 'Oatmeal',
            'status' => 'open',
        ]);

        Event::assertDispatched(MenuUpdated::class);
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
