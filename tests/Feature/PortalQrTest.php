<?php

namespace Tests\Feature;

use App\Exceptions\RedemptionRejectedException;
use App\Livewire\StudentPortal;
use App\Models\MealBooking;
use App\Models\Menu;
use App\Models\User;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PortalQrTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_qr_token_is_signed_with_es256_and_pinned_to_the_assigned_lane(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class)->call('refreshQrToken');

        $jwt = $component->get('qrToken');

        $this->assertNotNull($jwt);
        $this->assertSame(3, count(explode('.', $jwt)));

        $claims = app(QrTokenService::class)->verify($jwt);

        $this->assertSame($booking->id, $claims['booking_id']);
        $this->assertSame($booking->menu_id, $claims['meal_id']);
        $this->assertSame($booking->fresh()->assigned_lane, $claims['lane']);
        $this->assertSame(60, $claims['exp'] - $claims['iat']);
        $this->assertNotEmpty($component->get('qrSvg'));
    }

    public function test_qr_is_not_issued_before_the_meal_locks(): void
    {
        [$student] = $this->bookedMeal(now()->addHours(6));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('refreshQrToken')
            ->assertSet('qrSvg', null)
            ->assertSet('qrError', fn (?string $error): bool => str_contains((string) $error, 'Check-in opens'));
    }

    public function test_a_booking_can_only_be_redeemed_once(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));
        $contractor = $this->contractor();

        $jwt = $this->issueToken($student);
        $claims = app(QrTokenService::class)->verify($jwt);

        $first = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
            'device_id' => 'scanner-1',
        ]);

        $first->assertOk();
        $first->assertJsonPath('lane', $claims['lane']);
        $this->assertDatabaseHas('meal_consumptions', ['booking_id' => $booking->id]);
        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'consumed']);

        $second = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
            'device_id' => 'scanner-2',
        ]);

        $second->assertStatus(409);
    }

    public function test_scanning_at_the_wrong_lane_is_rejected(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));
        $contractor = $this->contractor();

        $jwt = $this->issueToken($student);
        $claims = app(QrTokenService::class)->verify($jwt);
        $wrongLane = $claims['lane'] === 'A' ? 'B' : 'A';

        $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $wrongLane,
            'device_id' => 'scanner-1',
        ])->assertStatus(409);

        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'booked']);
        $this->assertDatabaseMissing('meal_consumptions', ['booking_id' => $booking->id]);

        $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
            'device_id' => 'scanner-1',
        ])->assertOk();
    }

    public function test_expired_tokens_fail_verification(): void
    {
        [$student] = $this->bookedMeal(now()->addHours(2));

        $this->actingAs($student, 'student');
        $issuedAt = now();
        Carbon::setTestNow($issuedAt);

        $jwt = $this->issueToken($student);

        Carbon::setTestNow($issuedAt->copy()->addSeconds(61));

        $this->expectException(RedemptionRejectedException::class);

        app(QrTokenService::class)->verify($jwt);
    }

    private function issueToken(User $student): string
    {
        $this->actingAs($student, 'student');

        return Livewire::test(StudentPortal::class)
            ->call('refreshQrToken')
            ->get('qrToken');
    }

    /**
     * @return array{0: User, 1: MealBooking}
     */
    private function bookedMeal(\DateTimeInterface $serveAt): array
    {
        $student = User::create([
            'name' => 'Asha Rao',
            'college_id' => 'STU001',
            'email' => 'asha@college.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $menu = Menu::create([
            'meal_type' => 'lunch',
            'serve_at' => $serveAt,
            'items' => ['Dal', 'Rice'],
            'status' => 'open',
        ]);

        $booking = MealBooking::create([
            'student_id' => $student->id,
            'menu_id' => $menu->id,
            'status' => 'booked',
        ]);

        return [$student, $booking];
    }

    private function contractor(): User
    {
        return User::create([
            'name' => 'Counter Staff',
            'college_id' => 'CON001',
            'email' => 'staff@college.edu',
            'password' => Hash::make('password'),
            'role' => 'contractor',
        ]);
    }
}
