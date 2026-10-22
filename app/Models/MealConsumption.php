<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealConsumption extends Model
{
    protected $fillable = [
        'booking_id', 'jti', 'lane', 'mode', 'device_id', 'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'consumed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MealBooking::class, 'booking_id');
    }

    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \Exception('MealConsumption is append-only: updates are not allowed.');
        });

        static::deleting(function ($model) {
            throw new \Exception('MealConsumption is append-only: deletes are not allowed.');
        });
    }
}
