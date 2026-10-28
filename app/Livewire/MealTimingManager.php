<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\MealTimeSlot;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class MealTimingManager extends Component
{
    private const MEAL_TYPES = [
        'breakfast' => 'BREAKFAST',
        'lunch' => 'LUNCH',
        'snacks' => 'SNACKS',
        'dinner' => 'DINNER',
    ];

    public array $timeSlots = [];

    public ?string $statusMessage = null;

    public function mount(): void
    {
        $this->loadTimeSlots();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->loadTimeSlots();
    }

    private function loadTimeSlots(): void
    {
        $this->timeSlots = (array) config('mess.default_meal_times');

        $slots = MealTimeSlot::query()->get();
        foreach ($slots as $slot) {
            $mealType = strtolower($slot->meal_type);
            if (! array_key_exists($mealType, self::MEAL_TYPES)) {
                continue;
            }

            $this->timeSlots[$mealType] = [
                'start_time' => substr($slot->start_time, 0, 5),
                'end_time' => substr($slot->end_time, 0, 5),
                'cutoff_hours_before' => $slot->cutoff_hours_before,
            ];
        }
    }

    public function saveTimings(): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        $rules = [];
        foreach (array_keys(self::MEAL_TYPES) as $mealType) {
            $rules["timeSlots.{$mealType}.start_time"] = ['required', 'date_format:H:i'];
            $rules["timeSlots.{$mealType}.end_time"] = ['required', 'date_format:H:i'];
            $rules["timeSlots.{$mealType}.cutoff_hours_before"] = ['required', 'integer', 'min:1', 'max:24'];
        }

        $validated = $this->validate($rules);
        foreach (array_keys(self::MEAL_TYPES) as $mealType) {
            if ($validated['timeSlots'][$mealType]['end_time'] <= $validated['timeSlots'][$mealType]['start_time']) {
                throw ValidationException::withMessages([
                    "timeSlots.{$mealType}.end_time" => 'The end time must be after the start time.',
                ]);
            }
        }

        $updatedMenus = DB::transaction(function () use ($validated): array {
            $updatedMenus = [];

            foreach (self::MEAL_TYPES as $key => $mealType) {
                $timing = $validated['timeSlots'][$key];
                $slot = MealTimeSlot::query()->updateOrCreate(
                    ['meal_type' => $mealType],
                    [
                        'start_time' => $timing['start_time'].':00',
                        'end_time' => $timing['end_time'].':00',
                        'cutoff_hours_before' => $timing['cutoff_hours_before'],
                    ],
                );

                $menus = Menu::query()
                    ->where('meal_type', $key)
                    ->where('serve_at', '>', now())
                    ->where('is_locked', false)
                    ->whereNotIn('status', ['locked', 'serving', 'closed'])
                    ->get();

                foreach ($menus as $menu) {
                    $localServeAt = $menu->serve_at
                        ->copy()
                        ->timezone(config('mess.display_timezone'))
                        ->setTimeFromTimeString($timing['start_time']);
                    $serveAt = $localServeAt->copy()->utc();

                    if ($serveAt->lte(now())) {
                        continue;
                    }

                    $menu->forceFill([
                        'serve_at' => $serveAt,
                        'meal_time_slot_id' => $slot->id,
                    ])->save();
                    $updatedMenus[] = $menu;
                }
            }

            return $updatedMenus;
        });

        foreach ($updatedMenus as $menu) {
            broadcast(new MenuUpdated($menu));
        }

        $this->loadTimeSlots();
        $this->statusMessage = 'Meal timings updated successfully. Future unlocked menus were adjusted to the new service times.';
        $this->dispatch('portal-toast', message: 'Meal timings updated successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.meal-timing-manager')
            ->layout('layouts.contractor')
            ->title('Meal Timing Management');
    }
}
