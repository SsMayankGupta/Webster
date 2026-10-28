<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEndDateModification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'leave_request_id',
        'modified_by',
        'modified_by_name',
        'previous_to_date',
        'new_to_date',
        'rebate_adjustment_paise',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_to_date' => 'date',
            'new_to_date' => 'date',
            'rebate_adjustment_paise' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new \LogicException('Leave end-date modification history is append-only.');
        });

        static::deleting(function (): never {
            throw new \LogicException('Leave end-date modification history is append-only.');
        });
    }
}
