<?php

namespace App\Support;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

class SafeBroadcast
{
    /**
     * Broadcast an event without letting an unavailable broadcaster break the request.
     */
    public static function send(object $event): void
    {
        try {
            broadcast($event);
        } catch (BroadcastException $exception) {
            Log::warning('Broadcast failed', [
                'event' => $event::class,
                'exception' => $exception::class,
            ]);
        }
    }
}
