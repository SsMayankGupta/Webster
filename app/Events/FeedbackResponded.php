<?php

namespace App\Events;

use App\Models\MealFeedback;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackResponded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly MealFeedback $feedback
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('student.'.$this->feedback->student_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'feedback_id' => $this->feedback->id,
            'student_id' => $this->feedback->student_id,
            'menu_id' => $this->feedback->menu_id,
            'contractor_action' => $this->feedback->contractor_action,
            'contractor_note' => $this->feedback->contractor_note,
        ];
    }
}
