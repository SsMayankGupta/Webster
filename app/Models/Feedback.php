<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $fillable = ['booking_id', 'rating', 'comment'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MealBooking::class, 'booking_id');
    }
}
