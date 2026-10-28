<?php

namespace Tests\Feature;

use App\Events\FeedbackResponded;
use App\Events\GuestPassApproved;
use App\Events\GuestPassRequested;
use App\Events\QrRedeemed;
use App\Exceptions\RedemptionRejectedException;
use App\Livewire\StudentPortal;
use App\Models\LeaveRequest;
use App\Models\MealBooking;
use App\Models\MealFeedback;
use App\Models\Menu;
use App\Models\User;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
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

    public function test_student_portal_uses_the_selected_pusher_websocket_configuration(): void
    {
        [$student] = $this->bookedMeal(now()->addHours(2));
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.driver' => 'pusher',
            'broadcasting.connections.pusher.key' => 'public-app-key',
            'broadcasting.connections.pusher.options.cluster' => 'ap2',
        ]);

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->assertSet('state.broadcasting.key', 'public-app-key')
            ->assertSet('state.broadcasting.host', 'ws-ap2.pusher.com')
            ->assertSet('state.broadcasting.port', 443)
            ->assertSet('state.broadcasting.scheme', 'https')
            ->assertSet('state.broadcasting.private_channel', 'private-App.Models.User.'.$student->id);
    }

    public function test_student_can_authorize_only_their_own_private_broadcast_channel(): void
    {
        [$student] = $this->bookedMeal(now()->addHours(2));
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.driver' => 'pusher',
            'broadcasting.connections.pusher.key' => 'public-app-key',
            'broadcasting.connections.pusher.secret' => 'private-app-secret',
            'broadcasting.connections.pusher.app_id' => 'app-id',
        ]);
        Broadcast::purge('pusher');
        Broadcast::channel(
            'App.Models.User.{id}',
            fn ($user, $id): bool => $user->isStudent() && (int) $user->id === (int) $id,
            ['guards' => ['student']],
        );

        $this->actingAs($student, 'student')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-App.Models.User.'.$student->id,
            ])
            ->assertOk()
            ->assertJsonStructure(['auth']);

        $this->actingAs($student, 'student')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-App.Models.User.999999',
            ])
            ->assertForbidden();
    }

    public function test_broadcast_channels_are_limited_to_their_student_or_contractor_audience(): void
    {
        $student = User::create([
            'name' => 'Asha Rao',
            'college_id' => 'STU-BROADCAST',
            'email' => 'broadcast-student@college.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
        $contractor = $this->contractor();
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.driver' => 'pusher',
            'broadcasting.connections.pusher.key' => 'public-app-key',
            'broadcasting.connections.pusher.secret' => 'private-app-secret',
            'broadcasting.connections.pusher.app_id' => 'app-id',
        ]);
        Broadcast::purge('pusher');
        Broadcast::channel(
            'App.Models.User.{id}',
            fn ($user, $id): bool => $user->isStudent() && (int) $user->id === (int) $id,
            ['guards' => ['student']],
        );
        Broadcast::channel('guest-queue', fn ($user): bool => $user->role === 'contractor', ['guards' => ['contractor']]);

        $this->actingAs($student, 'student')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-App.Models.User.'.$student->id,
            ])
            ->assertOk();

        $this->actingAs($student, 'student')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-guest-queue',
            ])
            ->assertForbidden();

        Auth::guard('student')->logout();
        Auth::guard('contractor')->logout();

        $this->actingAs($contractor, 'contractor')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-guest-queue',
            ])
            ->assertOk();

        $this->actingAs($contractor, 'contractor')
            ->postJson('/broadcasting/auth', [
                'socket_id' => '123.456',
                'channel_name' => 'private-App.Models.User.'.$student->id,
            ])
            ->assertForbidden();
    }

    public function test_guest_pass_and_feedback_events_use_private_channels(): void
    {
        $booking = new MealBooking;
        $booking->student_id = 42;
        $feedback = new MealFeedback;
        $feedback->student_id = 42;

        $this->assertSame(
            ['private-guest-queue', 'private-App.Models.User.42'],
            array_map('strval', (new GuestPassApproved($booking, '1'))->broadcastOn()),
        );
        $this->assertSame(
            ['private-guest-queue'],
            array_map('strval', (new GuestPassRequested($booking))->broadcastOn()),
        );
        $this->assertSame(
            ['private-App.Models.User.42'],
            array_map('strval', (new FeedbackResponded($feedback))->broadcastOn()),
        );
    }

    public function test_guest_cannot_authorize_a_student_private_broadcast_channel(): void
    {
        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-App.Models.User.1',
        ])->assertUnauthorized();
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

    public function test_qr_is_not_issued_for_a_booking_during_approved_leave(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));
        [$student] = $this->bookedMeal(now()->addHours(2));
        LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->toDateString(),
            'to_date' => today()->toDateString(),
            'status' => 'approved',
        ]);

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->call('refreshQrToken')
            ->assertSet('qrSvg', null)
            ->assertSet('qrError', 'QR check-in is disabled for approved leave dates; no other active meal booking is available.');
    }

    public function test_previously_issued_qr_cannot_be_redeemed_during_approved_leave(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(8));
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));
        $jwt = $this->issueToken($student);
        $claims = app(QrTokenService::class)->verify($jwt);

        LeaveRequest::query()->create([
            'student_id' => $student->id,
            'from_date' => today()->toDateString(),
            'to_date' => today()->toDateString(),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->contractor(), 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
        ]);

        $response
            ->assertConflict()
            ->assertJsonPath('message', 'Meal check-in is disabled during approved leave.');

        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'booked']);
        $this->assertDatabaseMissing('meal_consumptions', ['booking_id' => $booking->id]);
    }

    public function test_a_booking_can_only_be_redeemed_once(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));
        $contractor = $this->contractor();

        $jwt = $this->issueToken($student);
        $claims = app(QrTokenService::class)->verify($jwt);

        Event::fake([QrRedeemed::class]);

        $first = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
            'device_id' => 'scanner-1',
        ]);

        $first->assertOk();
        $first->assertJsonPath('lane', $claims['lane']);
        $this->assertDatabaseHas('meal_consumptions', ['booking_id' => $booking->id]);
        $this->assertDatabaseHas('meal_bookings', ['id' => $booking->id, 'status' => 'consumed']);
        Event::assertDispatched(
            QrRedeemed::class,
            fn (QrRedeemed $event): bool => $event->studentId === $student->id
                && $event->consumption->booking_id === $booking->id,
        );

        $second = $this->actingAs($contractor, 'contractor')->postJson('/api/v1/scans/redeem', [
            'token' => $jwt,
            'lane' => $claims['lane'],
            'device_id' => 'scanner-2',
        ]);

        $second->assertStatus(409);
    }

    public function test_student_portal_refreshes_after_a_valid_redemption_event(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));
        $booking->update(['status' => 'consumed']);
        $booking->consumption()->create([
            'mode' => 'online',
            'consumed_at' => now(),
        ]);

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->dispatch('qr-redeemed', bookingId: $booking->id)
            ->assertSet('state.active_booking', null)
            ->assertSet('qrSvg', null)
            ->assertSet('qrError', 'Meal checked in at the counter. Enjoy your meal!');
    }

    public function test_student_portal_ignores_redemption_events_for_unconsumed_bookings(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));

        $this->actingAs($student, 'student');

        Livewire::test(StudentPortal::class)
            ->dispatch('qr-redeemed', bookingId: $booking->id)
            ->assertSet('state.active_booking.booking_id', $booking->id)
            ->assertSet('qrError', null);
    }

    public function test_qr_refresh_reconciles_portal_state_after_a_missed_realtime_event(): void
    {
        [$student, $booking] = $this->bookedMeal(now()->addHours(2));

        $this->actingAs($student, 'student');

        $component = Livewire::test(StudentPortal::class);

        $booking->update(['status' => 'consumed']);
        $booking->consumption()->create([
            'mode' => 'online',
            'consumed_at' => now(),
        ]);

        $component
            ->call('refreshQrToken')
            ->assertSet('state.active_booking', null)
            ->assertSet('qrSvg', null);
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
