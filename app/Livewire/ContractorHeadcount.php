<?php

namespace App\Livewire;

use App\Models\MealBooking;
use App\Models\Menu;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Headcount')]
class ContractorHeadcount extends Component
{
    public array $menus = [];

    public function mount(): void
    {
        $this->loadMenus();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadMenus();
    }

    public function loadMenus(): void
    {
        $this->lockMeals();

        $menus = Menu::query()
            ->whereDate('serve_at', today())
            ->orderBy('serve_at')
            ->get();

        $this->menus = $menus->map(function (Menu $menu): array {
            $menu->opted_in_count = MealBooking::headcountForMenu(
                $menu->id,
                ['booked', 'consumed'],
            );

            return [
                'id' => $menu->id,
                'meal_type' => $menu->meal_type,
                'meal_label' => ucfirst($menu->meal_type),
                'serve_at' => $menu->serve_at->format('D, d M · g:i A'),
                'serve_date' => $menu->serve_at->format('Y-m-d'),
                'items' => $menu->menuItems(),
                'status' => $menu->status,
                'is_locked' => $menu->is_locked,
                'headcount_locked' => $menu->headcount_locked,
                'opted_in_count' => $menu->opted_in_count,
            ];
        })->values()->all();
    }

    private function lockMeals(): void
    {
        $menus = Menu::query()
            ->where('is_locked', false)
            ->where('serve_at', '<=', now()->addHours(4))
            ->get();

        foreach ($menus as $menu) {
            $count = MealBooking::headcountForMenu($menu->id);

            $menu->update([
                'is_locked' => true,
                'status' => 'locked',
                'headcount_locked' => $count,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.contractor-headcount');
    }
}
