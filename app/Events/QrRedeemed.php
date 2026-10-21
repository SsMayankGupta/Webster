<?php

namespace App\Events;

use App\Models\MealConsumption;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QrRedeemed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $studentId,
        public MealConsumption $consumption,
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
        return 'qr.redeemed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->consumption->booking_id,
            'lane' => $this->consumption->lane,
            'mode' => $this->consumption->mode,
            'consumed_at' => $this->consumption->consumed_at->toIso8601String(),
            'message' => 'Meal check-in confirmed at the counter.',
        ];
    }
}
