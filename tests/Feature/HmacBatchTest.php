<?php

namespace Tests\Feature;

use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\QrToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class HmacBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_verify_accepts_valid_hmac_and_rejects_bad(): void
    {
        $student = User::create(['name' => 'S', 'college_id' => 'S1', 'password' => Hash::make('password'), 'role' => 'student']);
        $contractor = User::create(['name' => 'C', 'college_id' => 'C1', 'password' => Hash::make('password'), 'role' => 'contractor']);
        $menu = Menu::create(['meal_type' => 'lunch', 'serve_at' => now()->addHours(6), 'recipe_text' => 'x', 'status' => 'open']);
        $booking = MealBooking::create(['student_id' => $student->id, 'menu_id' => $menu->id, 'status' => 'booked']);

        $jti = (string) Str::uuid();
        QrToken::create(['jti' => $jti, 'booking_id' => $booking->id, 'expires_at' => now()->addMinutes(5)]);

        $device = 'dev1';
        $scannedAt = now()->toDateTimeString();
        $seq = 1;
        $secret = config('app.key');
        $payload = "{$jti}|{$device}|{$scannedAt}|{$seq}";
        $hmac = hash_hmac('sha256', $payload, $secret);

        $valid = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/batch-verify', [
            'scans' => [[
                'jti' => $jti,
                'device_id' => $device,
                'scanned_at' => $scannedAt,
                'seq' => $seq,
                'hmac' => $hmac,
            ]],
        ]);
        $valid->assertOk();
        $valid->assertJsonPath('results.0.ok', true);

        // bad hmac
        $jti2 = (string) Str::uuid();
        QrToken::create(['jti' => $jti2, 'booking_id' => $booking->id, 'expires_at' => now()->addMinutes(5)]);
        $bad = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/batch-verify', [
            'scans' => [[
                'jti' => $jti2,
                'device_id' => $device,
                'scanned_at' => $scannedAt,
                'seq' => $seq,
                'hmac' => 'deadbeef',
            ]],
        ]);
        $bad->assertOk();
        $bad->assertJsonPath('results.0.ok', false);
    }
}
