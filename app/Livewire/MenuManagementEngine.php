<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Support\SafeBroadcast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuManagementEngine extends Component
{
    public string $mode = 'manual';

    public array $schedule = [];

    public array $targetMacros = [
        'protein' => 0,
        'carbs' => 0,
        'calories' => 0,
    ];

    public array $pastWeeks = [];

    public ?int $selectedWeek = null;

    public function mount(): void
    {
        $this->loadPastWeeks();
        $this->initializeSchedule();
    }

    private function loadPastWeeks(): void
    {
        $this->pastWeeks = Menu::query()
            ->where('serve_at', '<', now())
            ->selectRaw('DATE(serve_at) as date, meal_type')
            ->orderByDesc('date')
            ->limit(28)
            ->get()
            ->groupBy('date')
            ->map(fn ($group) => [
                'date' => $group->first()->date,
                'label' => Carbon::parse($group->first()->date)->format('M j, Y'),
            ])
            ->values()
            ->toArray();
    }

    private function initializeSchedule(): void
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $mealTypes = ['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER'];

        foreach ($days as $day) {
            foreach ($mealTypes as $mealType) {
                $this->schedule[$day][$mealType] = [
                    'items' => '',
                    'protein' => 0,
                    'carbs' => 0,
                    'calories' => 0,
                ];
            }
        }
    }

    public function clonePastWeek(): void
    {
        if ($this->selectedWeek === null) {
            $this->dispatch('portal-toast', message: 'Please select a week to clone.', type: 'error');

            return;
        }

        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $startDate = Carbon::parse($this->pastWeeks[$this->selectedWeek]['date']);
        $menus = Menu::query()
            ->whereDate('serve_at', '>=', $startDate)
            ->whereDate('serve_at', '<=', $startDate->copy()->addDays(6))
            ->get();

        $newStartDate = now()->startOfWeek()->addWeek();

        foreach ($menus as $menu) {
            $daysDiff = $menu->serve_at->diffInDays($startDate);
            $newServeAt = $newStartDate->copy()->addDays($daysDiff)->setTimeFrom($menu->serve_at);

            $newMenu = Menu::query()->create([
                'meal_type' => $menu->meal_type,
                'serve_at' => $newServeAt,
                'protein' => $menu->protein,
                'carbs' => $menu->carbs,
                'calories' => $menu->calories,
                'recipe_text' => $menu->recipe_text,
                'items' => $menu->items,
                'is_locked' => false,
                'status' => 'planned',
                'headcount_locked' => false,
            ]);

            SafeBroadcast::send(new MenuUpdated($newMenu));
        }

        $this->dispatch('portal-toast', message: 'Menu cloned successfully.', type: 'success');
    }

    public function generateFromMacros(): void
    {
        $this->validate([
            'targetMacros.protein' => ['required', 'numeric', 'min:0'],
            'targetMacros.carbs' => ['required', 'numeric', 'min:0'],
            'targetMacros.calories' => ['required', 'numeric', 'min:0'],
        ]);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $mealTypes = ['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER'];

        $dailyProtein = $this->targetMacros['protein'] / 7;
        $dailyCarbs = $this->targetMacros['carbs'] / 7;
        $dailyCalories = $this->targetMacros['calories'] / 7;

        foreach ($days as $day) {
            foreach ($mealTypes as $mealType) {
                $ratio = match ($mealType) {
                    'BREAKFAST' => 0.25,
                    'LUNCH' => 0.35,
                    'SNACKS' => 0.1,
                    'DINNER' => 0.3,
                };

                $this->schedule[$day][$mealType] = [
                    'items' => '',
                    'protein' => round($dailyProtein * $ratio),
                    'carbs' => round($dailyCarbs * $ratio),
                    'calories' => round($dailyCalories * $ratio),
                ];
            }
        }

        $this->dispatch('portal-toast', message: 'Schedule generated from target macros.', type: 'success');
    }

    public function saveSchedule(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $mealTypes = ['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER'];
        $timeSlots = MealTimeSlot::all()->keyBy('meal_type');

        $startDate = now()->startOfWeek()->addWeek();

        foreach ($days as $dayIndex => $day) {
            foreach ($mealTypes as $mealType) {
                $entry = $this->schedule[$day][$mealType];

                if (empty($entry['items'])) {
                    continue;
                }

                $timeSlot = $timeSlots->get($mealType);

                $serveAt = $startDate->copy()->addDays($dayIndex);
                if ($timeSlot) {
                    $serveAt->setTimeFromTimeString($timeSlot->start_time);
                }

                $menu = Menu::query()->create([
                    'meal_type' => $mealType,
                    'serve_at' => $serveAt,
                    'protein' => $entry['protein'],
                    'carbs' => $entry['carbs'],
                    'calories' => $entry['calories'],
                    'recipe_text' => $entry['items'],
                    'items' => explode(',', $entry['items']),
                    'is_locked' => false,
                    'status' => 'planned',
                    'headcount_locked' => false,
                    'meal_time_slot_id' => $timeSlot?->id,
                ]);

                SafeBroadcast::send(new MenuUpdated($menu));
            }
        }

        $this->dispatch('portal-toast', message: 'Schedule saved successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.menu-management-engine');
    }
}
