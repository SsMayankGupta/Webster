<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtraMealCollection extends Model
{
    protected $fillable = ['student_id', 'recorded_by', 'amount_paise'];

    protected function casts(): array
    {
        return ['amount_paise' => 'integer'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('ExtraMealCollection is append-only: updates are not allowed.');
        });

        static::deleting(function (): never {
            throw new \LogicException('ExtraMealCollection is append-only: deletes are not allowed.');
        });
    }
}
