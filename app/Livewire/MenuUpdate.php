<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Update Menu')]
class MenuUpdate extends Component
{
    public array $menus = [];

    #[Locked]
    public int $previousWeeksLoaded = 0;

    #[Locked]
    public bool $hasPreviousMenus = false;

    public function mount(): void
    {
        $this->loadMenus();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->previousWeeksLoaded = 0;
        $this->loadMenus();
    }

    public function loadMenus(): void
    {
        $today = now()->startOfDay();
        $startDate = $today->copy();

        if ($this->previousWeeksLoaded > 0) {
            $startDate = $today->copy()->startOfWeek()->subWeeks($this->previousWeeksLoaded);
        }

        $latestUpcomingMenuDate = Menu::query()
            ->where('serve_at', '>=', $today)
            ->max('serve_at');
        $endDate = $latestUpcomingMenuDate === null
            ? $today->copy()->endOfDay()
            : Carbon::parse($latestUpcomingMenuDate)->endOfDay();

        $menusByDate = Menu::query()
            ->where('serve_at', '>=', $startDate)
            ->where('serve_at', '<=', $endDate)
            ->orderBy('serve_at')
            ->get()
            ->groupBy(fn ($menu) => $menu->serve_at->format('Y-m-d'));

        $this->menus = [];
        foreach ($menusByDate as $date => $dayMenus) {
            $this->menus[$date] = [
                'breakfast' => null,
                'lunch' => null,
                'snacks' => null,
                'dinner' => null,
            ];

            foreach ($dayMenus as $menu) {
                $this->menus[$date][$menu->meal_type] = [
                    'id' => $menu->id,
                    'items' => $menu->menuItems(),
                    'protein' => $menu->protein,
                    'carbs' => $menu->carbs,
                    'calories' => $menu->calories,
                    'status' => $menu->status,
                ];
            }
        }

        $this->hasPreviousMenus = Menu::query()
            ->where('serve_at', '<', $startDate)
            ->exists();
    }

    public function loadPreviousData(): void
    {
        if (! $this->hasPreviousMenus) {
            return;
        }

        $this->previousWeeksLoaded++;
        $this->loadMenus();
    }

    public function updateMenu(int $menuId, array $data): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $menu = Menu::query()->findOrFail($menuId);
        $menu->update([
            'recipe_text' => implode(', ', $data['items']),
            'items' => $data['items'],
            'protein' => $data['protein'],
            'carbs' => $data['carbs'],
            'calories' => $data['calories'],
        ]);

        broadcast(new MenuUpdated($menu));
        $this->loadMenus();

        $this->dispatch('portal-toast', message: 'Menu updated successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.menu-update');
    }
}
