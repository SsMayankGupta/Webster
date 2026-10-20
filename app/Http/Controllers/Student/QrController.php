<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\QrToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class QrController extends Controller
{
    public function show()
    {
        return view('student.qr');
    }

    public function token()
    {
        $student = Auth::guard('student')->user();
        $booking = MealBooking::with('menu')
            ->where('student_id', $student->id)
            ->where('status', 'booked')
            ->latest()
            ->first();

        if (! $booking) {
            return response()->json(['error' => 'No active booking'], 404);
        }

        $jti = (string) Str::uuid();
        $expires = now()->addSeconds(60);
        QrToken::create([
            'jti' => $jti,
            'booking_id' => $booking->id,
            'expires_at' => $expires,
        ]);

        $payload = [
            'jti' => $jti,
            'booking_id' => $booking->id,
            'menu_id' => $booking->menu_id,
            'exp' => $expires->timestamp,
        ];

        return response()->json([
            'jti' => $jti,
            'booking_id' => $booking->id,
            'menu_id' => $booking->menu_id,
            'exp' => $expires->timestamp,
            'token' => Crypt::encryptString(json_encode($payload)),
        ]);
    }
}
