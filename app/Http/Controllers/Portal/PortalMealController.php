<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Services\MealBookingService;
use Illuminate\Http\Request;

class PortalMealController extends Controller
{
    public function __construct(private readonly MealBookingService $bookings) {}

    public function confirm(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'opt_in' => ['required', 'boolean'],
            'dietary_pref' => ['nullable', 'string', 'in:'.implode(',', array_keys((array) config('mess.dietary_tags')))],
        ]);

        /** @var User $student */
        $student = $request->user('student');

        $booking = $this->bookings->confirm(
            $student,
            $menu,
            (bool) $data['opt_in'],
            $data['dietary_pref'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'booking' => [
                'id' => $booking->id,
                'menu_id' => $booking->menu_id,
                'status' => $booking->status,
                'dietary_pref' => $booking->dietary_pref,
            ],
        ]);
    }

    public function cancel(Request $request, Menu $menu)
    {
        /** @var User $student */
        $student = $request->user('student');

        $booking = $this->bookings->optOut($student, $menu);

        return response()->json([
            'ok' => true,
            'booking' => [
                'id' => $booking->id,
                'menu_id' => $booking->menu_id,
                'status' => $booking->status,
            ],
        ]);
    }
}
