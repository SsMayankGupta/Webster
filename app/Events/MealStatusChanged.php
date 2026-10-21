<?php

namespace App\Events;

use App\Models\Menu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MealStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Menu $menu) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('meals')];
    }

    public function broadcastAs(): string
    {
        return 'meal.status';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'meal_type' => $this->menu->meal_type,
            'status' => $this->menu->status,
            'is_locked' => (bool) $this->menu->is_locked,
            'headcount' => $this->menu->headcount_locked,
            'cutoff_at' => $this->menu->cutoffAt()->toIso8601String(),
            'serve_at' => $this->menu->serve_at->toIso8601String(),
        ];
    }
}
