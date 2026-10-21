<?php

namespace App\Events;

use App\Models\Menu;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MealCutoffWarning implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $studentId,
        public Menu $menu,
        public int $minutesRemaining,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->studentId)];
    }

    public function broadcastAs(): string
    {
        return 'meal.cutoff_warning';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'meal_type' => $this->menu->meal_type,
            'minutes_remaining' => $this->minutesRemaining,
            'cutoff_at' => $this->menu->cutoffAt()->toIso8601String(),
            'message' => sprintf(
                '%s locks in %d minute%s. Confirm or cancel now to avoid a wasted meal.',
                ucfirst($this->menu->meal_type),
                $this->minutesRemaining,
                $this->minutesRemaining === 1 ? '' : 's',
            ),
        ];
    }
}
