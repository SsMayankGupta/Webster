<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_can_see_only_their_warden_contact_details_on_the_dashboard(): void
    {
        $warden = User::query()->create([
            'name' => 'Assigned Warden',
            'email' => 'assigned-warden@college.edu',
            'phone' => '+919999000201',
            'password' => 'password',
            'role' => 'warden',
        ]);
        $otherWarden = User::query()->create([
            'name' => 'Other Warden',
            'email' => 'other-warden@college.edu',
            'password' => 'password',
            'role' => 'warden',
        ]);
        $contractor = User::query()->create([
            'name' => 'Contractor',
            'employee_id' => 'PRIVATE-EMPLOYEE-ID',
            'email' => 'contractor@college.edu',
            'password' => 'password',
            'role' => 'contractor',
            'warden_id' => $warden->id,
            'must_change_password' => false,
        ]);

        $this->actingAs($contractor, 'contractor')
            ->get(route('contractor.dashboard'))
            ->assertOk()
            ->assertSee('Need help?')
            ->assertSee('Assigned Warden')
            ->assertSee('assigned-warden@college.edu')
            ->assertSee('+919999000201')
            ->assertDontSee('Other Warden')
            ->assertDontSee('other-warden@college.edu')
            ->assertDontSee('PRIVATE-EMPLOYEE-ID');
    }

    public function test_contractor_without_an_active_warden_sees_a_help_fallback(): void
    {
        $warden = User::query()->create([
            'name' => 'Archived Warden',
            'email' => 'archived-warden@college.edu',
            'password' => 'password',
            'role' => 'warden',
        ]);
        $warden->forceFill(['archived_at' => now()])->save();
        $contractor = User::query()->create([
            'name' => 'Contractor',
            'email' => 'contractor@college.edu',
            'password' => 'password',
            'role' => 'contractor',
            'warden_id' => $warden->id,
            'must_change_password' => false,
        ]);

        $this->actingAs($contractor, 'contractor')
            ->get(route('contractor.dashboard'))
            ->assertOk()
            ->assertSee('Need help?')
            ->assertSee('Warden contact details are not available.')
            ->assertDontSee('Archived Warden')
            ->assertDontSee('archived-warden@college.edu');
    }
}
