<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    protected $fillable = ['jti', 'device_id', 'hmac', 'is_offline', 'scanned_at'];

    protected function casts(): array
    {
        return ['is_offline' => 'boolean', 'scanned_at' => 'datetime'];
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(QrToken::class, 'jti', 'jti');
    }
}
