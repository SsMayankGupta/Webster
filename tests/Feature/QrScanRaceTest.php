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

class QrScanRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrent_scan_only_one_succeeds(): void
    {
        $student = User::create(['name' => 'S', 'college_id' => 'S1', 'password' => Hash::make('password'), 'role' => 'student']);
        $contractor = User::create(['name' => 'C', 'college_id' => 'C1', 'password' => Hash::make('password'), 'role' => 'contractor']);
        $menu = Menu::create(['meal_type' => 'lunch', 'serve_at' => now()->addHours(6), 'recipe_text' => 'x', 'status' => 'open']);
        $booking = MealBooking::create(['student_id' => $student->id, 'menu_id' => $menu->id, 'status' => 'booked']);

        $jti = (string) Str::uuid();
        QrToken::create(['jti' => $jti, 'booking_id' => $booking->id, 'expires_at' => now()->addMinutes(5)]);

        $r1 = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', ['jti' => $jti, 'device_id' => 'dev1']);
        $r2 = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', ['jti' => $jti, 'device_id' => 'dev2']);

        $statuses = [$r1->getStatusCode(), $r2->getStatusCode()];
        $this->assertContains(200, $statuses);
        $this->assertContains(409, $statuses);
    }
}
