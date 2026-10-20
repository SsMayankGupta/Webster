<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrToken extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'jti';

    protected $fillable = ['jti', 'booking_id', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'used_at' => 'datetime'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(MealBooking::class, 'booking_id');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class, 'jti', 'jti');
    }
}
