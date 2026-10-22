<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RebateLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'leave_request_id', 'amount', 'amount_paise', 'reason', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_paise' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \Exception('RebateLedger is append-only: updates are not allowed.');
        });

        static::deleting(function ($model) {
            throw new \Exception('RebateLedger is append-only: deletes are not allowed.');
        });
    }
}
