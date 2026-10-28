<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'student_id', 'from_date', 'to_date', 'requested_to_date', 'reason', 'status', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'requested_to_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rebates(): HasMany
    {
        return $this->hasMany(RebateLedger::class, 'leave_request_id');
    }

    public function endDateModifications(): HasMany
    {
        return $this->hasMany(LeaveEndDateModification::class);
    }

    public function mealRefunds(): HasMany
    {
        return $this->hasMany(MealRefund::class);
    }
}
