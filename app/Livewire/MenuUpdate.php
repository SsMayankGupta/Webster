<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuUpdate extends Component
{
    public array $menus = [];

    public string $selectedWeek = 'current';

    public function mount(): void
    {
        $this->loadMenus();
    }

    public function loadMenus(): void
    {
        $startDate = now()->startOfWeek();
        if ($this->selectedWeek === 'next') {
            $startDate->addWeek();
        }

        $menus = Menu::query()
            ->whereDate('serve_at', '>=', $startDate)
            ->whereDate('serve_at', '<=', $startDate->copy()->addDays(6))
            ->orderBy('serve_at')
            ->get()
            ->groupBy(fn ($menu) => $menu->serve_at->format('Y-m-d'));

        $this->menus = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $this->menus[$date] = [
                'breakfast' => null,
                'lunch' => null,
                'snacks' => null,
                'dinner' => null,
            ];

            if (isset($menus[$date])) {
                foreach ($menus[$date] as $menu) {
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
        }
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

    public function switchWeek(string $week): void
    {
        $this->selectedWeek = $week;
        $this->loadMenus();
    }

    public function render()
    {
        return view('livewire.menu-update')
            ->layout('layouts.contractor')
            ->title('Menu Update');
    }
}
