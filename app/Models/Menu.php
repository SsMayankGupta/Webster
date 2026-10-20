<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'meal_type', 'serve_at', 'protein', 'carbs', 'calories',
        'recipe_text', 'is_locked', 'status', 'headcount_locked',
    ];

    protected function casts(): array
    {
        return [
            'serve_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function mealBookings(): HasMany
    {
        return $this->hasMany(MealBooking::class);
    }
}
