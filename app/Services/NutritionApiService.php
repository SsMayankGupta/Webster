<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NutritionApiService
{
    private string $appId;

    private string $appKey;

    private string $baseUrl = 'https://api.edamam.com/api/nutrition-details';

    public function __construct()
    {
        $this->appId = config('services.edamam.app_id', '');
        $this->appKey = config('services.edamam.app_key', '');
    }

    /**
     * Get nutrition data for a list of ingredients
     *
     * @param  array<string>  $ingredients
     * @return array{protein: float, carbs: float, calories: float, fat: float}
     */
    public function getNutritionForIngredients(array $ingredients): array
    {
        if (empty($this->appId) || empty($this->appKey)) {
            Log::warning('Edamam API credentials not configured');

            return $this->generateMockNutrition($ingredients);
        }

        $ingredientString = implode(', ', $ingredients);

        try {
            $response = Http::get($this->baseUrl, [
                'app_id' => $this->appId,
                'app_key' => $this->appKey,
                'ingr' => $ingredientString,
            ]);

            if ($response->failed()) {
                Log::error('Edamam API request failed', ['status' => $response->status()]);

                return $this->generateMockNutrition($ingredients);
            }

            $data = $response->json();

            if (! isset($data['totalNutrients'])) {
                Log::warning('Edamam API response missing totalNutrients');

                return $this->generateMockNutrition($ingredients);
            }

            return [
                'protein' => $data['totalNutrients']['PROCNT']['quantity'] ?? 0,
                'carbs' => $data['totalNutrients']['CHOCDF']['quantity'] ?? 0,
                'calories' => $data['totalNutrients']['ENERC_KCAL']['quantity'] ?? 0,
                'fat' => $data['totalNutrients']['FAT']['quantity'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Edamam API exception', ['message' => $e->getMessage()]);

            return $this->generateMockNutrition($ingredients);
        }
    }

    /**
     * Generate mock nutrition data when API is unavailable
     *
     * @param  array<string>  $ingredients
     * @return array{protein: float, carbs: float, calories: float, fat: float}
     */
    private function generateMockNutrition(array $ingredients): array
    {
        $count = count($ingredients);

        return [
            'protein' => round($count * 5 + rand(0, 10), 1),
            'carbs' => round($count * 15 + rand(0, 20), 1),
            'calories' => round($count * 50 + rand(0, 50), 1),
            'fat' => round($count * 3 + rand(0, 5), 1),
        ];
    }
}
