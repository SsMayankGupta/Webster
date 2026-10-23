<?php

namespace App\Actions;

use App\Models\MealBooking;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CancelMealBooking
{
    public function execute(MealBooking $booking): MealBooking
    {
        return DB::transaction(function () use ($booking) {
            $menu = Menu::query()
                ->where('id', $booking->menu_id)
                ->sharedLock()
                ->first();

            if ($menu === null) {
                throw new \RuntimeException('Menu not found.');
            }

            $lockAt = $menu->serve_at->subHours((int) config('mess.cutoff_hours'));

            $isPastCutoff = DB::selectOne(
                'SELECT NOW() > ? as is_past',
                [$lockAt]
            )->is_past;

            if ($isPastCutoff) {
                throw new ConflictHttpException('Meal cutoff has been reached. Cancellation is no longer allowed.');
            }

            $booking->update(['status' => 'cancelled']);

            return $booking->fresh();
        });
    }
}
