<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_locked_feature_showcase(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Zero-waste campus dining');
        $response->assertSee('Sign in to access');
        $response->assertSee('Weekly Meal Schedule');
        $response->assertSee('Atomic Pre-Cutoff Cancellations');
        $response->assertSee('Cryptographic 60s QR Check-In');
    }

    public function test_authenticated_student_sees_dashboard_inside_the_same_page(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student, 'student')->get('/');

        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee('Today’s mess menu');
        $response->assertSee('Seven-day schedule');
        $response->assertDontSee('Sign in to access');
    }

    public function test_scanner_public_key_is_available_for_offline_verification(): void
    {
        $response = $this->get(route('portal.qr.public-key'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/x-pem-file');
        $response->assertSee('BEGIN PUBLIC KEY');
    }

    private function student(): User
    {
        return User::create([
            'name' => 'Asha Rao',
            'college_id' => 'STU001',
            'email' => 'asha@college.edu',
            'password' => Hash::make('password'),
            'role' => 'student',
            'hostel' => 'Ganga',
        ]);
    }
}
