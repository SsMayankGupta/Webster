<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $upcoming = Menu::where('serve_at', '>=', now())->orderBy('serve_at')->limit(5)->get();
        $bookings = MealBooking::with('menu')->where('student_id', $student->id)->latest()->limit(10)->get();
        return view('student.dashboard', compact('student', 'upcoming', 'bookings'));
    }
}
