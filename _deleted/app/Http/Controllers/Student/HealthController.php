<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\ScanLog;
use Illuminate\Support\Facades\Auth;

class HealthController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $bookings = MealBooking::with('menu')->where('student_id', $student->id)->get();
        $totalProtein = $bookings->sum(fn ($b) => $b->menu->protein ?? 0);
        $totalCarbs = $bookings->sum(fn ($b) => $b->menu->carbs ?? 0);
        $totalCalories = $bookings->sum(fn ($b) => $b->menu->calories ?? 0);
        $weightLog = $student->weight_log ?? [];
        return view('student.health', compact('student', 'bookings', 'totalProtein', 'totalCarbs', 'totalCalories', 'weightLog'));
    }
}
