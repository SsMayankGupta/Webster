<?php

namespace App\Http\Controllers\Contractor;

use App\Exceptions\RedemptionRejectedException;
use App\Http\Controllers\Controller;
use App\Models\QrToken;
use App\Models\ScanLog;
use App\Services\QrRedemptionService;
use App\Services\QrTokenService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(
        private readonly QrTokenService $tokens,
        private readonly QrRedemptionService $redemptions,
    ) {}

    public function index()
    {
        return view('contractor.scanner', [
            'lanes' => config('mess.lanes'),
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'token' => 'nullable|string',
            'jti' => 'nullable|string',
            'lane' => 'nullable|string|max:8',
            'device_id' => 'nullable|string|max:64',
        ]);

        $device = $data['device_id'] ?? $request->ip();
        $lane = isset($data['lane']) ? strtoupper($data['lane']) : null;

        $bookingId = null;
        $jti = $data['jti'] ?? null;

        if (filled($data['token'] ?? null)) {
            $payload = $this->tokens->verify($data['token']);
            $bookingId = (int) $payload['booking_id'];
            $jti = (string) $payload['jti'];
            $tokenLane = $payload['lane'] ?? null;

            if ($lane !== null && $tokenLane !== null && strcasecmp($lane, (string) $tokenLane) !== 0) {
                throw new RedemptionRejectedException(
                    "This token is pinned to lane {$tokenLane}. You are scanning at lane {$lane}.",
                    409,
                );
            }
        } elseif (filled($jti)) {
            $token = QrToken::query()->with('booking')->where('jti', $jti)->first();

            if (! $token) {
                throw new RedemptionRejectedException('Unknown check-in token.', 422);
            }

            if ($token->used_at !== null || $token->expires_at->isPast()) {
                throw new RedemptionRejectedException('This check-in token has expired or was already used.', 409);
            }

            $bookingId = $token->booking_id;
        }

        if ($bookingId === null) {
            throw new RedemptionRejectedException('Missing check-in token.', 422);
        }

        $consumption = $this->redemptions->redeem($bookingId, $jti, $lane, $device);

        return response()->json([
            'ok' => true,
            'message' => 'Redeemed at lane '.$consumption->lane,
            'booking_id' => $consumption->booking_id,
            'lane' => $consumption->lane,
            'consumed_at' => $consumption->consumed_at->toIso8601String(),
        ]);
    }

    public function batchVerify(Request $request)
    {
        $data = $request->validate([
            'scans' => 'required|array',
        ]);

        $results = [];

        foreach ($data['scans'] as $scan) {
            $jti = $scan['jti'] ?? null;
            $device = $scan['device_id'] ?? 'offline';
            $lane = isset($scan['lane']) ? strtoupper((string) $scan['lane']) : null;
            $hmac = $scan['hmac'] ?? '';
            $scannedAt = $scan['scanned_at'] ?? now()->toDateTimeString();
            $seq = $scan['seq'] ?? 0;

            $secret = (string) config('app.key');
            $payload = $jti.'|'.$device.'|'.$scannedAt.'|'.$seq;
            $expected = hash_hmac('sha256', $payload, $secret);

            if (! hash_equals($expected, (string) $hmac)) {
                $results[] = ['jti' => $jti, 'ok' => false, 'message' => 'Bad HMAC'];

                continue;
            }

            $token = QrToken::query()->where('jti', $jti)->first();

            if (! $token) {
                $results[] = ['jti' => $jti, 'ok' => false, 'message' => 'Unknown token'];

                continue;
            }

            try {
                $consumption = $this->redemptions->redeem(
                    $token->booking_id,
                    (string) $jti,
                    $lane,
                    (string) $device,
                    true,
                );

                ScanLog::query()->create([
                    'jti' => $jti,
                    'device_id' => $device,
                    'hmac' => $hmac,
                    'is_offline' => true,
                    'scanned_at' => $scannedAt,
                ]);

                $results[] = [
                    'jti' => $jti,
                    'ok' => true,
                    'lane' => $consumption->lane,
                ];
            } catch (RedemptionRejectedException $exception) {
                $results[] = [
                    'jti' => $jti,
                    'ok' => false,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
