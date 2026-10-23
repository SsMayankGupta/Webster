<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Services\NutritionApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ContractorMenuBuilder extends Component
{
    public string $mode = 'ai';

    public array $schedule = [];

    public array $aiPreferences = [
        'target_calories' => 2000,
        'protein_ratio' => 30,
        'carbs_ratio' => 50,
        'fat_ratio' => 20,
        'vegetarian' => false,
        'cuisine_type' => 'mixed',
    ];

    public array $pastWeeks = [];

    public ?int $selectedWeek = null;

    public array $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public array $mealTypes = ['breakfast', 'lunch', 'snacks', 'dinner'];

    public array $cuisineTypes = ['mixed', 'indian', 'continental', 'chinese', 'south-indian', 'north-indian'];

    public function mount(): void
    {
        $this->loadPastWeeks();
        $this->initializeSchedule();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
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
        foreach ($this->days as $day) {
            foreach ($this->mealTypes as $mealType) {
                $this->schedule[$day][$mealType] = [
                    'items' => '',
                    'protein' => 0,
                    'carbs' => 0,
                    'calories' => 0,
                ];
            }
        }
    }

    public function generateAIMenu(): void
    {
        $this->validate([
            'aiPreferences.target_calories' => ['required', 'numeric', 'min:1200', 'max:4000'],
            'aiPreferences.protein_ratio' => ['required', 'numeric', 'min:10', 'max:50'],
            'aiPreferences.carbs_ratio' => ['required', 'numeric', 'min:10', 'max:70'],
            'aiPreferences.fat_ratio' => ['required', 'numeric', 'min:5', 'max:40'],
        ]);

        $totalRatio = $this->aiPreferences['protein_ratio'] + $this->aiPreferences['carbs_ratio'] + $this->aiPreferences['fat_ratio'];
        if ($totalRatio !== 100) {
            $this->dispatch('portal-toast', message: 'Macros ratios must sum to 100%', type: 'error');

            return;
        }

        $dailyCalories = $this->aiPreferences['target_calories'];
        $dailyProtein = ($dailyCalories * $this->aiPreferences['protein_ratio'] / 100) / 4;
        $dailyCarbs = ($dailyCalories * $this->aiPreferences['carbs_ratio'] / 100) / 4;
        $dailyFat = ($dailyCalories * $this->aiPreferences['fat_ratio'] / 100) / 9;

        $mealRatios = [
            'breakfast' => 0.25,
            'lunch' => 0.35,
            'snacks' => 0.1,
            'dinner' => 0.3,
        ];

        $cuisineItems = $this->getCuisineItems($this->aiPreferences['cuisine_type'], $this->aiPreferences['vegetarian']);
        $nutritionService = app(NutritionApiService::class);

        foreach ($this->days as $day) {
            foreach ($this->mealTypes as $mealType) {
                $ratio = $mealRatios[$mealType];
                $mealCalories = $dailyCalories * $ratio;

                $items = $this->generateMealItems($mealType, $cuisineItems, $this->aiPreferences['vegetarian']);

                $nutritionData = $nutritionService->getNutritionForIngredients($items);

                $this->schedule[$day][$mealType] = [
                    'items' => implode(', ', $items),
                    'protein' => round($nutritionData['protein'], 1),
                    'carbs' => round($nutritionData['carbs'], 1),
                    'calories' => round($nutritionData['calories'], 1),
                ];
            }
        }

        $this->dispatch('portal-toast', message: 'AI menu generated with nutrition data!', type: 'success');
    }

    private function getCuisineItems(string $cuisine, bool $vegetarian): array
    {
        $indian = $vegetarian
            ? ['Paneer Butter Masala', 'Dal Tadka', 'Vegetable Biryani', 'Roti', 'Rasam', 'Sambar', 'Mixed Veg Curry']
            : ['Butter Chicken', 'Chicken Biryani', 'Mutton Curry', 'Fish Curry', 'Roti', 'Naan', 'Dal Makhani'];

        $continental = $vegetarian
            ? ['Pasta Alfredo', 'Grilled Vegetables', 'Caesar Salad', 'Garlic Bread', 'Soup', 'Risotto']
            : ['Grilled Chicken', 'Steak', 'Fish & Chips', 'Pasta Carbonara', 'Caesar Salad', 'Garlic Bread'];

        $chinese = $vegetarian
            ? ['Vegetable Fried Rice', 'Hakka Noodles', 'Spring Rolls', 'Manchurian', 'Veg Momos']
            : ['Chicken Fried Rice', 'Hakka Noodles', 'Spring Rolls', 'Chilli Chicken', 'Kung Pao Chicken'];

        $southIndian = ['Idli', 'Dosa', 'Sambar', 'Coconut Chutney', 'Upma', 'Pongal', 'Filter Coffee'];
        $northIndian = ['Paratha', 'Aloo Paratha', 'Paneer Tikka', 'Dal Makhani', 'Roti', 'Butter Chicken'];
        $mixed = array_merge($indian, $continental, ['Salad', 'Soup', 'Juice']);

        return match ($cuisine) {
            'indian' => $indian,
            'continental' => $continental,
            'chinese' => $chinese,
            'south-indian' => $southIndian,
            'north-indian' => $northIndian,
            default => $mixed,
        };
    }

    private function generateMealItems(string $mealType, array $cuisineItems, bool $vegetarian): array
    {
        $items = match ($mealType) {
            'breakfast' => array_slice($cuisineItems, 0, 3),
            'lunch' => array_slice($cuisineItems, 2, 4),
            'snacks' => array_slice($cuisineItems, 4, 2),
            'dinner' => array_slice($cuisineItems, 3, 4),
        };

        return array_values(array_filter($items));
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

            broadcast(new MenuUpdated($newMenu));
        }

        $this->dispatch('portal-toast', message: 'Menu cloned successfully for next week!', type: 'success');
    }

    public function saveSchedule(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $timeSlots = MealTimeSlot::all()->keyBy('meal_type');
        $startDate = now()->startOfWeek()->addWeek();
        $nutritionService = app(NutritionApiService::class);

        foreach ($this->days as $dayIndex => $day) {
            foreach ($this->mealTypes as $mealType) {
                $entry = $this->schedule[$day][$mealType];

                if (empty($entry['items'])) {
                    continue;
                }

                $timeSlot = $timeSlots->get($mealType);

                $serveAt = $startDate->copy()->addDays($dayIndex);
                if ($timeSlot) {
                    $serveAt->setTimeFromTimeString($timeSlot->start_time);
                }

                $items = array_map('trim', explode(',', $entry['items']));
                $nutritionData = $nutritionService->getNutritionForIngredients($items);

                $menu = Menu::query()->create([
                    'meal_type' => $mealType,
                    'serve_at' => $serveAt,
                    'protein' => $nutritionData['protein'],
                    'carbs' => $nutritionData['carbs'],
                    'calories' => $nutritionData['calories'],
                    'recipe_text' => $entry['items'],
                    'items' => $items,
                    'is_locked' => false,
                    'status' => 'open',
                    'headcount_locked' => false,
                    'meal_time_slot_id' => $timeSlot?->id,
                ]);

                broadcast(new MenuUpdated($menu));
            }
        }

        $this->dispatch('portal-toast', message: 'Schedule saved successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.contractor-menu-builder')
            ->layout('layouts.contractor')
            ->title('Menu Builder');
    }
}
