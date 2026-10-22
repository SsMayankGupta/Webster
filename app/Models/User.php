<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'college_id', 'email', 'password', 'role',
        'hostel', 'dietary_pref', 'dietary_tags', 'weight_log', 'mess_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'weight_log' => 'array',
            'dietary_tags' => 'array',
        ];
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
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
