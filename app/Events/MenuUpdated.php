<?php

namespace App\Events;

use App\Models\Menu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Menu $menu
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('menu-updates'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'meal_type' => $this->menu->meal_type,
            'serve_at' => $this->menu->serve_at?->toIso8601String(),
            'items' => $this->menu->menuItems(),
        ];
    }
}
