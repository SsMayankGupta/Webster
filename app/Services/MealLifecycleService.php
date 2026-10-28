<?php

namespace App\Services;

use App\Events\MealCutoffWarning;
use App\Events\MealStatusChanged;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Support\SafeBroadcast;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class MealLifecycleService
{
    /**
     * Derive the authoritative meal state from the server clock.
     */
    public function stateFor(Menu $menu, ?CarbonImmutable $now = null): string
    {
        $now ??= CarbonImmutable::now();
        $serveAt = CarbonImmutable::instance($menu->serve_at);
        $endTime = $menu->mealTimeSlot?->end_time
            ?? config("mess.default_meal_times.{$menu->meal_type}.end_time");
        $endAt = $endTime === null
            ? $serveAt->addHours(3)
            : $serveAt
                ->timezone(config('mess.display_timezone'))
                ->setTimeFromTimeString($endTime)
                ->utc();

        if ($menu->status === 'closed' || $now->gte($endAt)) {
            return 'closed';
        }

        if ($now->gte($serveAt)) {
            return 'serving';
        }

        if ($menu->is_locked || $now->gte($menu->cutoffAt())) {
            return 'locked';
        }

        return 'open';
    }

    /**
     * Persist a state transition, freeze the headcount and pin lanes.
     */
    public function sync(Menu $menu, ?CarbonImmutable $now = null, bool $broadcast = true): Menu
    {
        $state = $this->stateFor($menu, $now);

        if ($menu->status === $state) {
            return $menu;
        }

        $menu->status = $state;
        $menu->is_locked = in_array($state, ['locked', 'serving', 'closed'], true);

        if ($state === 'locked') {
            $menu->headcount_locked = MealBooking::headcountForMenu($menu->id);

            $this->assignLanes($menu);
        }

        $menu->save();

        if ($broadcast) {
            SafeBroadcast::send(new MealStatusChanged($menu));
        }

        return $menu;
    }

    /**
     * Deterministically pin every booked student to a counter lane.
     */
    public function assignLanes(Menu $menu): void
    {
        $lanes = array_values((array) config('mess.lanes'));

        if ($lanes === []) {
            return;
        }

        $bookings = MealBooking::query()
            ->where('menu_id', $menu->id)
            ->where('status', 'booked')
            ->orderBy('id')
            ->get();

        foreach ($bookings as $index => $booking) {
            $booking->forceFill([
                'assigned_lane' => $lanes[$index % count($lanes)],
                'locked_at' => $booking->locked_at ?? now(),
            ])->save();
        }
    }

    /**
     * Sync every meal that is close enough to change state.
     */
    public function syncAll(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $changed = 0;

        Menu::query()
            ->where('status', '!=', 'closed')
            ->where('serve_at', '<=', $now->addDay())
            ->orderBy('serve_at')
            ->each(function (Menu $menu) use ($now, &$changed): void {
                $before = $menu->status;
                $this->sync($menu, $now);

                if ($menu->status !== $before) {
                    $changed++;
                }
            });

        return $changed;
    }

    /**
     * Push a private warning to every student booked for a meal closing soon.
     */
    public function sendCutoffWarnings(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $sent = 0;

        $menus = Menu::query()
            ->where('is_locked', false)
            ->where('serve_at', '>', $now)
            ->get()
            ->filter(function (Menu $menu) use ($now): bool {
                $cutoff = $menu->cutoffAt();

                return $cutoff->gte($now) && $cutoff->lte($now->addMinutes(30));
            });

        foreach ($menus as $menu) {
            if (! Cache::add("meal-cutoff-warning:{$menu->id}", true, 3600)) {
                continue;
            }

            $minutes = max(1, (int) ceil($now->diffInMinutes($menu->cutoffAt())));

            MealBooking::query()
                ->where('menu_id', $menu->id)
                ->where('status', 'booked')
                ->select('student_id')
                ->chunk(200, function ($bookings) use ($menu, $minutes, &$sent): void {
                    foreach ($bookings as $booking) {
                        SafeBroadcast::send(new MealCutoffWarning($booking->student_id, $menu, $minutes));
                        $sent++;
                    }
                });
        }

        return $sent;
    }
}
