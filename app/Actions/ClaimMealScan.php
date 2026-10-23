<?php

namespace App\Actions;

use App\Models\MealBooking;
use App\Models\MealConsumption;
use App\Services\QrTokenService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ClaimMealScan
{
    public function __construct(
        private QrTokenService $qrTokenService
    ) {}

    public function execute(string $token): MealConsumption
    {
        $payload = $this->qrTokenService->verify($token);

        return DB::transaction(function () use ($payload) {
            $booking = MealBooking::query()
                ->where('id', $payload['booking_id'])
                ->where('status', 'booked')
                ->lockForUpdate()
                ->first();

            if ($booking === null) {
                throw new \RuntimeException('Invalid or expired booking.');
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
