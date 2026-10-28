<?php

namespace App\Events;

use App\Models\MealBooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestPassApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly MealBooking $booking,
        public readonly string $lane
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('guest-queue'),
            new PrivateChannel('App.Models.User.'.$this->booking->student_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'student_id' => $this->booking->student_id,
            'menu_id' => $this->booking->menu_id,
            'lane' => $this->lane,
            'status' => $this->booking->status,
        ];
    }
}
