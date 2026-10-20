<?php

namespace App\Jobs;

use App\Models\Menu;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CalculateMenuMacros implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $menuId) {}

    public function handle(): void
    {
        $menu = Menu::find($this->menuId);
        if (! $menu) return;

        $apiKey = env('CLAUDE_API_KEY');
        $protein = 0; $carbs = 0; $calories = 0;
        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-3-5-sonnet-20241022',
                    'max_tokens' => 256,
                    'messages' => [[
                        'role' => 'user',
                        'content' => "Parse recipe and return JSON {protein,carbs,calories}: " . $menu->recipe_text,
                    ]],
                ]);
                $text = $response->json('content.0.text') ?? '';
                if (preg_match('/\{.*\}/s', $text, $m)) {
                    $json = json_decode($m[0], true);
                    $protein = $json['protein'] ?? 0;
                    $carbs = $json['carbs'] ?? 0;
                    $calories = $json['calories'] ?? 0;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        if ($protein == 0 && $carbs == 0 && $calories == 0) {
            // static fallback heuristic
            $len = strlen((string) $menu->recipe_text);
            $protein = round($len / 50, 2);
            $carbs = round($len / 20, 2);
            $calories = round($len * 2, 2);
        }

        $menu->update([
            'protein' => $protein,
            'carbs' => $carbs,
            'calories' => $calories,
        ]);
    }
}
