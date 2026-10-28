<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealRefund extends Model
{
    protected $fillable = [
        'student_id', 'leave_request_id', 'recorded_by', 'transferred_by',
        'amount_paise', 'status', 'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'transferred_at' => 'datetime',
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function transferrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
