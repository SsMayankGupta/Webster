<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use App\Services\NutritionApiService;
use App\Support\SafeBroadcast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Menu Builder')]
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

    #[Locked]
    public string $menuStartDate = '';

    #[Locked]
    public array $days = [];

    public array $scheduledMenuDates = [];

    public bool $scheduleCreatedSuccessfully = false;

    public int $skippedMenuSlots = 0;

    public array $mealTypes = ['breakfast', 'lunch', 'snacks', 'dinner'];

    public array $cuisineTypes = ['mixed', 'indian', 'continental', 'chinese', 'south-indian', 'north-indian'];

    public function mount(): void
    {
        $this->initializeMenuWeek();
        $this->loadPastWeeks();
        $this->initializeSchedule();
        $this->loadScheduledMenuDates();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->initializeMenuWeek();
        $this->loadPastWeeks();
        $this->schedule = [];
        $this->initializeSchedule();
        $this->loadScheduledMenuDates();
    }

    private function initializeMenuWeek(): void
    {
        $this->menuStartDate = today(config('mess.display_timezone'))->toDateString();
        $startDate = $this->menuWeekStart();
        $this->days = collect(range(0, 6))
            ->map(fn (int $offset): string => $startDate->copy()->addDays($offset)->format('l'))
            ->all();
    }

    private function menuWeekStart(): Carbon
    {
        return Carbon::parse($this->menuStartDate, config('mess.display_timezone'))->startOfDay();
    }

    private function menuWeekEnd(): Carbon
    {
        return $this->menuWeekStart()->addDays(6);
    }

    private function loadScheduledMenuDates(): void
    {
        $startDate = $this->menuWeekStart();
        $endDate = $this->menuWeekEnd()->endOfDay();

        $this->scheduledMenuDates = Menu::query()
            ->whereBetween('serve_at', [$startDate->copy()->utc(), $endDate->copy()->utc()])
            ->orderBy('serve_at')
            ->get(['serve_at'])
            ->map(fn (Menu $menu): string => $menu->serve_at->timezone(config('mess.display_timezone'))->toDateString())
            ->unique()
            ->map(fn (string $date): string => Carbon::parse($date)->format('l, F j'))
            ->values()
            ->all();
    }

    private function loadPastWeeks(): void
    {
        $dates = Menu::query()
            ->where('serve_at', '<', now())
            ->orderByDesc('serve_at')
            ->limit(112)
            ->get(['serve_at'])
            ->map(fn (Menu $menu): string => $menu->serve_at->timezone(config('mess.display_timezone'))->toDateString())
            ->unique()
            ->take(28);

        $this->pastWeeks = $dates
            ->map(fn (string $date): array => [
                'date' => $date,
                'label' => Carbon::parse($date, config('mess.display_timezone'))->format('M j, Y'),
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
        $this->scheduleCreatedSuccessfully = false;
        $this->skippedMenuSlots = 0;

        if ($this->selectedWeek === null || ! array_key_exists($this->selectedWeek, $this->pastWeeks)) {
            $this->dispatch('portal-toast', message: 'Please select a week to clone.', type: 'error');

            return;
        }

        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $startDate = Carbon::parse($this->pastWeeks[$this->selectedWeek]['date'], config('mess.display_timezone'))->startOfDay();
        $menus = Menu::query()
            ->whereBetween('serve_at', [
                $startDate->copy()->utc(),
                $startDate->copy()->addDays(6)->endOfDay()->utc(),
            ])
            ->get();

        if ($menus->isEmpty()) {
            $this->dispatch('portal-toast', message: 'No menus were found for the selected week.', type: 'error');

            return;
        }

        $newStartDate = $this->menuWeekStart();
        $timeSlots = MealTimeSlot::query()
            ->get()
            ->keyBy(fn (MealTimeSlot $slot): string => strtolower($slot->meal_type));
        $existingSlots = Menu::query()
            ->whereBetween('serve_at', [
                $newStartDate->copy()->utc(),
                $this->menuWeekEnd()->endOfDay()->utc(),
            ])
            ->get(['serve_at', 'meal_type'])
            ->mapWithKeys(fn (Menu $menu): array => [
                $menu->serve_at->timezone(config('mess.display_timezone'))->toDateString().'|'.$menu->meal_type => true,
            ]);
        $createdMenus = 0;
        $skippedMenus = 0;

        foreach ($menus as $menu) {
            $localMenuDate = $menu->serve_at->copy()->timezone(config('mess.display_timezone'));
            $daysDiff = (int) $startDate->diffInDays($localMenuDate->copy()->startOfDay());
            $timeSlot = $timeSlots->get($menu->meal_type);
            $newServeAt = $newStartDate->copy()->addDays($daysDiff);
            $newServeAt->setTimeFromTimeString($timeSlot?->start_time ?? $localMenuDate->format('H:i:s'));
            $slotKey = $newServeAt->toDateString().'|'.$menu->meal_type;
            $newServeAt->utc();

            if ($existingSlots->has($slotKey)) {
                $skippedMenus++;

                continue;
            }

            $newMenu = Menu::query()->create([
                'meal_type' => $menu->meal_type,
                'serve_at' => $newServeAt,
                'protein' => $menu->protein,
                'carbs' => $menu->carbs,
                'calories' => $menu->calories,
                'recipe_text' => $menu->recipe_text,
                'items' => $menu->items,
                'is_locked' => false,
                'status' => 'open',
                'headcount_locked' => false,
                'meal_time_slot_id' => $timeSlot?->id,
            ]);

            $existingSlots->put($slotKey, true);
            SafeBroadcast::send(new MenuUpdated($newMenu));
            $createdMenus++;
        }

        if ($createdMenus === 0) {
            $this->skippedMenuSlots = $skippedMenus;
            $this->dispatch(
                'portal-toast',
                message: 'All meals from that week already have menus in the current seven-day period.',
                type: 'info',
            );

            return;
        }

        $this->loadScheduledMenuDates();
        $this->scheduleCreatedSuccessfully = true;
        $this->skippedMenuSlots = $skippedMenus;
        $message = 'Menu cloned successfully for the current seven-day period!';
        if ($skippedMenus > 0) {
            $message .= " {$skippedMenus} existing meal slot(s) were left unchanged.";
        }
        $this->dispatch('portal-toast', message: $message, type: 'success');
    }

    public function saveSchedule(): void
    {
        $this->scheduleCreatedSuccessfully = false;
        $this->skippedMenuSlots = 0;

        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $timeSlots = MealTimeSlot::query()
            ->get()
            ->keyBy(fn (MealTimeSlot $slot): string => strtolower($slot->meal_type));
        $startDate = $this->menuWeekStart();
        $existingSlots = Menu::query()
            ->whereBetween('serve_at', [
                $startDate->copy()->utc(),
                $this->menuWeekEnd()->endOfDay()->utc(),
            ])
            ->get(['serve_at', 'meal_type'])
            ->mapWithKeys(fn (Menu $menu): array => [
                $menu->serve_at->timezone(config('mess.display_timezone'))->toDateString().'|'.$menu->meal_type => true,
            ]);
        $nutritionService = app(NutritionApiService::class);
        $createdMenus = 0;
        $skippedMenus = 0;

        foreach ($this->days as $dayIndex => $day) {
            foreach ($this->mealTypes as $mealType) {
                $entry = $this->schedule[$day][$mealType];

                if (empty($entry['items'])) {
                    continue;
                }

                $timeSlot = $timeSlots->get($mealType);

                $serveAt = $startDate->copy()->addDays($dayIndex);
                $startTime = $timeSlot?->start_time
                    ?? config("mess.default_meal_times.{$mealType}.start_time", '00:00');
                $serveAt->setTimeFromTimeString($startTime);

                $slotKey = $serveAt->toDateString().'|'.$mealType;
                if ($existingSlots->has($slotKey)) {
                    $skippedMenus++;

                    continue;
                }

                $items = array_map('trim', explode(',', $entry['items']));
                $nutritionData = $nutritionService->getNutritionForIngredients($items);
                $serveAt->utc();

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

                $existingSlots->put($slotKey, true);
                SafeBroadcast::send(new MenuUpdated($menu));
                $createdMenus++;
            }
        }

        if ($createdMenus === 0) {
            $this->skippedMenuSlots = $skippedMenus;
            $message = $skippedMenus > 0
                ? 'Those day and meal slots already have menus. Existing menus were left unchanged.'
                : 'Add at least one meal before saving the schedule.';
            $this->dispatch('portal-toast', message: $message, type: $skippedMenus > 0 ? 'info' : 'error');

            return;
        }

        $this->loadScheduledMenuDates();
        $this->scheduleCreatedSuccessfully = true;
        $this->skippedMenuSlots = $skippedMenus;
        $message = 'Schedule saved successfully!';
        if ($skippedMenus > 0) {
            $message .= " {$skippedMenus} existing meal slot(s) were left unchanged.";
        }
        $this->dispatch('portal-toast', message: $message, type: 'success');
    }

    public function render()
    {
        $menuWeekStart = $this->menuWeekStart();

        return view('livewire.contractor-menu-builder', [
            'menuWeekStart' => $menuWeekStart,
            'menuWeekEnd' => $this->menuWeekEnd(),
        ]);
    }
}
