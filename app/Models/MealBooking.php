<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MealBooking extends Model
{
    protected $fillable = [
        'student_id', 'menu_id', 'status', 'dietary_pref',
        'assigned_lane', 'confirmed_at', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function qrToken(): HasOne
    {
        return $this->hasOne(QrToken::class, 'booking_id');
    }

    public function consumption(): HasOne
    {
        return $this->hasOne(MealConsumption::class, 'booking_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'booking_id');
    }
}
