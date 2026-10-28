<?php

namespace App\Livewire;

use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\WasteTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Dashboard')]
class ContractorDashboard extends Component
{
    public array $stats = [];

    public array $todayMenus = [];

    public array $recentWaste = [];

    #[Locked]
    public array $wardenContact = [];

    public array $wasteTypes = [
        'compost' => 'Compost',
        'biogas' => 'Biogas',
        'donation' => 'Donation',
        'sale' => 'Sale',
    ];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            return;
        }

        $warden = $contractor->warden()
            ->where('role', 'warden')
            ->whereNull('archived_at')
            ->first(['id', 'name', 'email', 'phone']);
        $this->wardenContact = $warden === null ? [] : [
            'name' => $warden->name,
            'email' => $warden->email,
            'phone' => $warden->phone,
        ];

        $todayMenus = Menu::query()
            ->whereDate('serve_at', today())
            ->get();

        $headcount = MealBooking::query()
            ->whereIn('menu_id', $todayMenus->pluck('id'))
            ->where('status', 'booked')
            ->count();

        $waste = WasteTransaction::query()
            ->where('contractor_id', $contractor->id)
            ->latest()
            ->limit(5)
            ->get();

        $this->stats = [
            'total_meals_today' => $todayMenus->count(),
            'headcount' => $headcount,
            'total_waste_reports' => WasteTransaction::query()
                ->where('contractor_id', $contractor->id)
                ->count(),
        ];

        $this->todayMenus = $todayMenus->map(fn (Menu $menu): array => [
            'id' => $menu->id,
            'meal_type' => $menu->meal_type,
            'meal_label' => ucfirst($menu->meal_type),
            'serve_at' => $menu->serve_at->format('g:i A'),
            'items' => $menu->menuItems(),
            'status' => $menu->status,
        ])->values()->all();

        $this->recentWaste = $waste->map(fn (WasteTransaction $waste): array => [
            'id' => $waste->id,
            'date' => $waste->created_at->format('d M Y'),
            'weight_kg' => $waste->weight_kg,
            'type' => $waste->type,
            'type_label' => $this->wasteTypes[$waste->type] ?? $waste->type,
            'earnings' => $waste->earnings,
        ])->values()->all();
    }

    public function render()
    {
        return view('livewire.contractor-dashboard');
    }
}
