<?php

namespace App\Services;

use App\Exceptions\MealCutoffReachedException;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MealBookingService
{
    public function __construct(private readonly MealLifecycleService $lifecycle) {}

    /**
     * Opt in or out of a meal. The menu row is locked so concurrent requests
     * cannot both pass the cutoff check.
     */
    public function confirm(User $student, Menu $menu, bool $optIn, ?string $dietaryPreference = null): MealBooking
    {
        return DB::transaction(function () use ($student, $menu, $optIn, $dietaryPreference): MealBooking {
            $lockedMenu = Menu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();

            if (! $lockedMenu->isOpenAt()) {
                throw new MealCutoffReachedException;
            }

            $booking = MealBooking::query()
                ->where('student_id', $student->id)
                ->where('menu_id', $lockedMenu->id)
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                $booking = new MealBooking([
                    'student_id' => $student->id,
                    'menu_id' => $lockedMenu->id,
                ]);
            }

            if ($booking->status === 'consumed') {
                throw new MealCutoffReachedException('This meal has already been redeemed and can no longer be changed.');
            }

            $booking->status = $optIn ? 'booked' : 'cancelled';
            $booking->dietary_pref = $dietaryPreference;
            $booking->confirmed_at = now();
            $booking->locked_at = null;
            $booking->save();

            return $booking->load('menu');
        });
    }

    public function optOut(User $student, Menu $menu): MealBooking
    {
        return $this->confirm($student, $menu, false);
    }

    /**
     * The booking a student can currently check in with.
     */
    public function activeBooking(User $student): ?MealBooking
    {
        return MealBooking::query()
            ->select('meal_bookings.*')
            ->with('menu')
            ->join('menus', 'menus.id', '=', 'meal_bookings.menu_id')
            ->where('meal_bookings.student_id', $student->id)
            ->where('meal_bookings.status', 'booked')
            ->where('menus.status', '!=', 'closed')
            ->where('menus.serve_at', '>=', now()->subHours(2))
            ->where('menus.serve_at', '<=', now()->addDays(7))
            ->orderBy('menus.serve_at')
            ->first();
    }
}
