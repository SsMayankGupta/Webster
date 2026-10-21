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
}
