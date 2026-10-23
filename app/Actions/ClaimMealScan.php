<?php

namespace App\Actions;

use App\Models\MealBooking;
use App\Models\MealConsumption;
use App\Models\User;
use App\Services\ApprovedLeaveService;
use App\Services\QrTokenService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ClaimMealScan
{
    public function __construct(
        private QrTokenService $qrTokenService,
        private ApprovedLeaveService $approvedLeaves,
    ) {}

    public function execute(string $token): MealConsumption
    {
        $payload = $this->qrTokenService->verify($token);

        return DB::transaction(function () use ($payload) {
            $bookingCandidate = MealBooking::query()
                ->where('id', $payload['booking_id'])
                ->first();

            if ($bookingCandidate === null) {
                throw new \RuntimeException('Invalid or expired booking.');
            }

            User::query()->whereKey($bookingCandidate->student_id)->lockForUpdate()->firstOrFail();

            $booking = MealBooking::query()
                ->with('menu')
                ->where('id', $payload['booking_id'])
                ->where('status', 'booked')
                ->lockForUpdate()
                ->first();

            if ($booking === null) {
                throw new \RuntimeException('Invalid or expired booking.');
            }

            if ($this->approvedLeaves->coversDate(
                (int) $booking->student_id,
                $booking->menu->serve_at,
            )) {
                throw new ConflictHttpException('Meal check-in is disabled during approved leave.');
            }

            try {
                $consumption = MealConsumption::query()->create([
                    'booking_id' => $booking->id,
                    'jti' => $payload['jti'],
                    'lane' => $booking->assigned_lane,
                    'mode' => 'online',
                    'device_id' => request()->header('X-Device-ID'),
                    'consumed_at' => now(),
                ]);

                $booking->update(['status' => 'consumed']);

                return $consumption;
            } catch (UniqueConstraintViolationException $e) {
                throw new ConflictHttpException('This meal has already been scanned.', 0, $e);
            }
        });
    }
}
