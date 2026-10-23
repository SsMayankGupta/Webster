<?php

namespace Tests\Feature;

use App\Events\MenuUpdated;
use App\Livewire\ContractorMenuBuilder;
use App\Models\Menu;
use App\Models\User;
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
}
