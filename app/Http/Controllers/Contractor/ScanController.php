<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\MealBooking;
use App\Models\QrToken;
use App\Models\ScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ScanController extends Controller
{
    public function index()
    {
        return view('contractor.scanner');
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'token' => 'nullable|string',
            'jti' => 'nullable|string',
            'device_id' => 'nullable|string',
        ]);

        $device = $data['device_id'] ?? $request->ip();

        if (! empty($data['token'])) {
            try {
                $payload = json_decode(Crypt::decryptString($data['token']), true);
            } catch (\Throwable $e) {
                return response()->json(['ok' => false, 'message' => 'Invalid token'], 422);
            }
            $jti = $payload['jti'] ?? null;
            $exp = $payload['exp'] ?? 0;
            if (! $jti || now()->timestamp > $exp) {
                return response()->json(['ok' => false, 'message' => 'Expired token'], 422);
            }
        } else {
            $jti = $data['jti'] ?? null;
        }

        if (! $jti) {
            return response()->json(['ok' => false, 'message' => 'Missing jti'], 422);
        }

        // Atomic Redis SET-NX
        $key = "scan:redeem:{$jti}";
        $claimed = Redis::setnx($key, $device);
        if (! $claimed) {
            return response()->json(['ok' => false, 'message' => 'Already scanned'], 409);
        }
        Redis::expire($key, 120);

        try {
            DB::transaction(function () use ($jti, $device) {
                $token = QrToken::where('jti', $jti)->lockForUpdate()->first();
                if (! $token || $token->used_at) {
                    throw new \RuntimeException('Token used or missing');
                }
                if (now()->gt($token->expires_at)) {
                    throw new \RuntimeException('Token expired');
                }
                $token->update(['used_at' => now()]);
                $booking = $token->booking;
                if ($booking) {
                    $booking->update(['status' => 'consumed']);
                }
                ScanLog::create([
                    'jti' => $jti,
                    'device_id' => $device,
                    'hmac' => '',
                    'is_offline' => false,
                    'scanned_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            Redis::del($key);
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'message' => 'Redeemed']);
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
            $hmac = $scan['hmac'] ?? '';
            $scannedAt = $scan['scanned_at'] ?? now()->toDateTimeString();
            $seq = $scan['seq'] ?? 0;

            // HMAC verification using shared secret (device key). Here verify against app key.
            $secret = config('app.key');
            $payload = $jti . '|' . $device . '|' . $scannedAt . '|' . $seq;
            $expected = hash_hmac('sha256', $payload, $secret);
            if (! hash_equals($expected, $hmac)) {
                $results[] = ['jti' => $jti, 'ok' => false, 'message' => 'Bad HMAC'];
                continue;
            }

            $key = "scan:redeem:{$jti}";
            if (! Redis::setnx($key, $device)) {
                $results[] = ['jti' => $jti, 'ok' => false, 'message' => 'Duplicate'];
                continue;
            }
            Redis::expire($key, 120);

            try {
                DB::transaction(function () use ($jti, $device, $hmac, $scannedAt) {
                    $token = QrToken::where('jti', $jti)->lockForUpdate()->first();
                    if ($token && ! $token->used_at) {
                        $token->update(['used_at' => now()]);
                        if ($token->booking) {
                            $token->booking->update(['status' => 'consumed']);
                        }
                    }
                    ScanLog::create([
                        'jti' => $jti,
                        'device_id' => $device,
                        'hmac' => $hmac,
                        'is_offline' => true,
                        'scanned_at' => $scannedAt,
                    ]);
                });
                $results[] = ['jti' => $jti, 'ok' => true];
            } catch (\Throwable $e) {
                Redis::del($key);
                $results[] = ['jti' => $jti, 'ok' => false, 'message' => $e->getMessage()];
            }
        }

        return response()->json(['results' => $results]);
    }
}
