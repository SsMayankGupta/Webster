<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function cancel(MealBooking $booking)
    {
        $student = Auth::guard('student')->user();
        abort_unless($booking->student_id === $student->id, 403);

        $menu = $booking->menu;
        if (! now()->addHours(4)->lte($menu->serve_at)) {
            return back()->withErrors(['Cancellation cutoff (4 hours) has passed.']);
        }
        if ($booking->status !== 'booked') {
            return back()->withErrors(['Booking not cancellable.']);
        }
        $booking->update(['status' => 'cancelled']);
        return back()->with('status', 'Meal cancelled.');
    }
}
