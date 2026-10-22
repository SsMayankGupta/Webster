<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeadcountLedger extends Model
{
    protected $fillable = [
        'menu_id', 'opt_in_count', 'locked_count', 'guest_count', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \Exception('HeadcountLedger is append-only: updates are not allowed.');
        });

        static::deleting(function ($model) {
            throw new \Exception('HeadcountLedger is append-only: deletes are not allowed.');
        });
    }
}
