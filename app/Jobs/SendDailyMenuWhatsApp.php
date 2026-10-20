<?php

namespace App\Jobs;

use App\Models\Menu;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendDailyMenuWhatsApp implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $menus = Menu::whereDate('serve_at', today())->get();
        $token = env('WHATSAPP_TOKEN');
        $phoneId = env('WHATSAPP_PHONE_ID');
        foreach ($menus as $menu) {
            $message = "Menu: {$menu->meal_type} at {$menu->serve_at}. P:{$menu->protein} C:{$menu->carbs} Kcal:{$menu->calories}";
            if ($token && $phoneId) {
                try {
                    Http::withToken($token)->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                        'messaging_product' => 'whatsapp',
                        'to' => env('WHATSAPP_BROADCAST_TO', ''),
                        'type' => 'text',
                        'text' => ['body' => $message],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('WhatsApp send failed: ' . $e->getMessage());
                }
            } else {
                Log::info('WhatsApp (mock): ' . $message);
            }
        }
    }
}
