<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraMealCharge extends Model
{
    protected $fillable = [
        'booking_id', 'student_id', 'approved_by', 'guest_count',
        'price_per_guest_paise', 'amount_paise',
    ];

    protected function casts(): array
    {
        return [
            'guest_count' => 'integer',
            'price_per_guest_paise' => 'integer',
            'amount_paise' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MealBooking::class, 'booking_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('ExtraMealCharge is append-only: updates are not allowed.');
        });

        static::deleting(function (): never {
            throw new \LogicException('ExtraMealCharge is append-only: deletes are not allowed.');
        });
    }
}
