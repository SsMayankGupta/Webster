<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Services\MealBookingService;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(private readonly MealBookingService $bookings) {}

    public function cancel(MealBooking $booking)
    {
        $student = Auth::guard('student')->user();

        abort_unless($booking->student_id === $student->id, 403);

        $this->bookings->optOut($student, $booking->menu);

        return back()->with('status', 'Meal cancelled. The kitchen headcount has been updated.');
    }
}
