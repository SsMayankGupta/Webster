<?php

namespace App\Livewire;

use App\Models\MealTimeSlot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MealTimingManager extends Component
{
    public array $timeSlots = [];

    public function mount(): void
    {
        $this->loadTimeSlots();
    }

    private function loadTimeSlots(): void
    {
        $slots = MealTimeSlot::all();
        foreach ($slots as $slot) {
            $this->timeSlots[$slot->meal_type] = [
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

        $this->validate([
            'timeSlots.*.start_time' => ['required', 'date_format:H:i'],
            'timeSlots.*.end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'timeSlots.*.cutoff_hours_before' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        foreach ($this->timeSlots as $mealType => $timing) {
            MealTimeSlot::query()->updateOrCreate(
                ['meal_type' => $mealType],
                [
                    'start_time' => $timing['start_time'].':00',
                    'end_time' => $timing['end_time'].':00',
                    'cutoff_hours_before' => $timing['cutoff_hours_before'],
                ]
            );
        }

        $this->dispatch('portal-toast', message: 'Meal timings updated successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.meal-timing-manager')
            ->layout('layouts.contractor')
            ->title('Meal Timing Management');
    }
}
