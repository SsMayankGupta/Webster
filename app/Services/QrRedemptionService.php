<?php

namespace App\Services;

use App\Events\QrRedeemed;
use App\Exceptions\RedemptionRejectedException;
use App\Models\MealBooking;
use App\Models\MealConsumption;
use App\Models\QrToken;
use App\Models\User;
use App\Support\SafeBroadcast;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class QrRedemptionService
{
    public function __construct(private readonly ApprovedLeaveService $approvedLeaves) {}

    /**
     * Redeem a meal exactly once. The UNIQUE(booking_id) index on the
     * consumption ledger serialises concurrent scans.
     */
    public function redeem(
        int $bookingId,
        ?string $jti = null,
        ?string $lane = null,
        ?string $deviceId = null,
        bool $offline = false,
    ): MealConsumption {
        try {
            $consumption = DB::transaction(function () use ($bookingId, $jti, $lane, $deviceId, $offline): MealConsumption {
                $bookingCandidate = MealBooking::query()
                    ->with('menu')
                    ->find($bookingId);

                if (! $bookingCandidate) {
                    throw new RedemptionRejectedException('Booking not found.', 404);
                }

                User::query()->whereKey($bookingCandidate->student_id)->lockForUpdate()->firstOrFail();

                $booking = MealBooking::query()
                    ->with('menu')
                    ->lockForUpdate()
                    ->find($bookingId);

                if (! $booking) {
                    throw new RedemptionRejectedException('Booking not found.', 404);
                }

                if ($booking->status === 'consumed' || $booking->consumption()->exists()) {
                    throw new RedemptionRejectedException('This meal has already been redeemed.', 409);
                }

                if ($booking->status !== 'booked') {
                    throw new RedemptionRejectedException('This booking is not active.', 409);
                }

                if ($this->approvedLeaves->coversDate(
                    (int) $booking->student_id,
                    $booking->menu->serve_at,
                )) {
                    throw new RedemptionRejectedException('Meal check-in is disabled during approved leave.', 409);
                }

                if ($booking->menu->status === 'closed') {
                    throw new RedemptionRejectedException('The counter for this meal is closed.', 409);
                }

                if ($lane !== null && $booking->assigned_lane !== null
                    && strcasecmp($lane, $booking->assigned_lane) !== 0) {
                    throw new RedemptionRejectedException(
                        "Wrong counter lane. This student is assigned to lane {$booking->assigned_lane}.",
                        409,
                    );
                }

                $consumption = MealConsumption::query()->create([
                    'booking_id' => $booking->id,
                    'jti' => $jti,
                    'lane' => $booking->assigned_lane ?? $lane,
                    'mode' => $offline ? 'offline' : 'online',
                    'device_id' => $deviceId,
                    'consumed_at' => now(),
                ]);

                $booking->update(['status' => 'consumed']);

                if ($jti !== null) {
                    QrToken::query()->where('jti', $jti)->update(['used_at' => now()]);
                }

                return $consumption;
            });
        } catch (UniqueConstraintViolationException) {
            throw new RedemptionRejectedException('This meal has already been redeemed.', 409);
        }

        SafeBroadcast::send(new QrRedeemed(
            (int) $consumption->booking->student_id,
            $consumption,
        ));

        return $consumption;
    }
}
