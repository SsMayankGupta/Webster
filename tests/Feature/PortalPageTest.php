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
        $response = $this->get('/student');

        $response->assertOk();
        $response->assertSee('Eat well.');
        $response->assertSee('Sign in to access');
        $response->assertSee('Weekly Meal Schedule');
        $response->assertSee('Atomic Pre-Cutoff Cancellations');
        $response->assertSee('Cryptographic 60s QR Check-In');
    }

    public function test_authenticated_student_sees_dashboard_inside_the_same_page(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student, 'student')->get('/student');

        $response->assertOk();
        $response->assertSee($student->name);
        $response->assertSee('Today’s mess menu');
        $response->assertSee('Next cutoff');
        $response->assertDontSee('Sign in to access');
    }

    public function test_authenticated_student_can_see_only_their_assigned_contractor_and_warden_contact_details(): void
    {
        $warden = User::query()->create([
            'name' => 'Warden Contact',
            'email' => 'warden-contact@college.edu',
            'phone' => '+919999000101',
            'password' => 'password',
            'role' => 'warden',
        ]);
        $contractor = User::query()->create([
            'name' => 'Assigned Contractor',
            'employee_id' => 'PRIVATE-CONTRACTOR-ID',
            'email' => 'assigned-contractor@college.edu',
            'phone' => '+919999000102',
            'password' => 'password',
            'role' => 'contractor',
            'warden_id' => $warden->id,
            'hostel' => 'Ganga',
        ]);
        $otherWarden = User::query()->create([
            'name' => 'Other Warden',
            'email' => 'other-warden@college.edu',
            'password' => 'password',
            'role' => 'warden',
        ]);
        User::query()->create([
            'name' => 'Other Contractor',
            'email' => 'other-contractor@college.edu',
            'password' => 'password',
            'role' => 'contractor',
            'warden_id' => $otherWarden->id,
            'hostel' => 'Other Hostel',
        ]);
        $student = $this->student();
        $student->forceFill(['contractor_id' => $contractor->id])->save();

        $this->actingAs($student, 'student')
            ->get('/student')
            ->assertOk()
            ->assertSee('Need help?')
            ->assertSee('Assigned Contractor')
            ->assertSee('assigned-contractor@college.edu')
            ->assertSee('+919999000102')
            ->assertSee('Warden Contact')
            ->assertSee('warden-contact@college.edu')
            ->assertSee('+919999000101')
            ->assertDontSee('Other Contractor')
            ->assertDontSee('other-contractor@college.edu')
            ->assertDontSee('Other Warden')
            ->assertDontSee('PRIVATE-CONTRACTOR-ID');
    }

    public function test_scanner_public_key_is_available_for_offline_verification(): void
    {
        $response = $this->get(route('portal.qr.public-key'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/x-pem-file');
        $response->assertSee('BEGIN PUBLIC KEY');
    }

    public function test_home_page_only_offers_student_and_contractor_portals(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Choose your portal')
            ->assertSee(route('student.portal'))
            ->assertSee(route('contractor.login'))
            ->assertDontSee(route('warden.login'));
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
            'phone' => '+919999000002',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'account_approved_at' => now(),
        ]);
    }
}
