<?php

namespace App\Models;

use App\Notifications\WardenResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'college_id', 'email', 'email_verified_at', 'password', 'role',
        'hostel', 'dietary_pref', 'dietary_tags', 'weight_log', 'mess_id',
        'phone', 'employee_id', 'registered_at', 'phone_verified_at',
        'account_approved_at', 'warden_id', 'contractor_id', 'semester_name',
        'semester_starts_at', 'semester_ends_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'account_approved_at' => 'datetime',
            'archived_at' => 'datetime',
            'must_change_password' => 'boolean',
            'registered_at' => 'date',
            'semester_starts_at' => 'date',
            'semester_ends_at' => 'date',
            'password' => 'hashed',
            'weight_log' => 'array',
            'dietary_tags' => 'array',
        ];
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isWarden(): bool
    {
        return $this->role === 'warden';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify($this->isWarden()
            ? new WardenResetPassword($token)
            : new ResetPassword($token));
    }

    public function warden(): BelongsTo
    {
        return $this->belongsTo(self::class, 'warden_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'contractor_id');
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(self::class, 'warden_id')->where('role', 'contractor');
    }

    public function students(): HasMany
    {
        return $this->hasMany(self::class, 'contractor_id')->where('role', 'student');
    }

    public function mealBookings()
    {
        return $this->hasMany(MealBooking::class, 'student_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'student_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'student_id');
    }

    public function rebateLedgers()
    {
        return $this->hasMany(RebateLedger::class, 'student_id');
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(StudentFeePayment::class, 'student_id');
    }

    public function mealRefunds(): HasMany
    {
        return $this->hasMany(MealRefund::class, 'student_id');
    }

    public function extraMealCharges(): HasMany
    {
        return $this->hasMany(ExtraMealCharge::class, 'student_id');
    }

    public function extraMealCollections(): HasMany
    {
        return $this->hasMany(ExtraMealCollection::class, 'student_id');
    }

    public function wasteTransactions()
    {
        return $this->hasMany(WasteTransaction::class, 'contractor_id');
    }

    public function approvedLeaves()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function mealFeedbacks()
    {
        return $this->hasMany(MealFeedback::class, 'student_id');
    }

    public function notices()
    {
        return $this->hasMany(Notice::class, 'contractor_id');
    }

    public function primaryGuestBookings()
    {
        return $this->hasMany(MealBooking::class, 'primary_student_id');
    }
}
