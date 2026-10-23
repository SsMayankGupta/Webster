<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealTimeSlot extends Model
{
    protected $fillable = [
        'meal_type', 'start_time', 'end_time', 'cutoff_hours_before',
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
