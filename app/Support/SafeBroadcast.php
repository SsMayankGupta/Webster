<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

class SafeBroadcast
{
    /**
     * Broadcast an event without letting a missing Reverb server break the request.
     */
    public static function send(object $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $exception) {
            Log::warning('Broadcast failed', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
