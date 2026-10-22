<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Menu extends Model
{
    protected $fillable = [
        'meal_type', 'serve_at', 'protein', 'carbs', 'calories',
        'recipe_text', 'items', 'is_locked', 'status', 'headcount_locked',
        'meal_time_slot_id',
    ];

    protected function casts(): array
    {
        return [
            'serve_at' => 'datetime',
            'items' => 'array',
            'is_locked' => 'boolean',
        ];
    }

    public function mealBookings(): HasMany
    {
        return $this->hasMany(MealBooking::class);
    }

    public function mealTimeSlot(): BelongsTo
    {
        return $this->belongsTo(MealTimeSlot::class, 'meal_time_slot_id');
    }

    public function headcountLedger(): HasOne
    {
        return $this->hasOne(HeadcountLedger::class);
    }

    public function forecast(): HasOne
    {
        return $this->hasOne(Forecast::class);
    }

    public function mealFeedbacks(): HasMany
    {
        return $this->hasMany(MealFeedback::class, 'menu_id');
    }

    /**
     * The instant at which confirmations and cancellations freeze.
     */
    public function cutoffAt(): CarbonImmutable
    {
        return CarbonImmutable::instance($this->serve_at)
            ->subHours((int) config('mess.cutoff_hours'));
    }

    public function isOpenAt(?CarbonImmutable $moment = null): bool
    {
        return ($moment ?? CarbonImmutable::now())->lt($this->cutoffAt());
    }

    /**
     * Menu items, falling back to the free-text recipe for legacy rows.
     *
     * @return list<string>
     */
    public function menuItems(): array
    {
        $items = $this->items;

        if (is_array($items) && $items !== []) {
            return array_values(array_filter(array_map('strval', $items)));
        }

        if (filled($this->recipe_text)) {
            return array_values(array_filter(array_map('trim', explode(',', $this->recipe_text))));
        }

        return [];
    }
}
