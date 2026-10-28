<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuardSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_contractor_dashboard(): void
    {
        $student = User::create(['name' => 'S', 'college_id' => 'S1', 'password' => Hash::make('password'), 'role' => 'student']);

        $response = $this->actingAs($student, 'student')->get('/contractor/dashboard');

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    public function test_contractor_cannot_access_student_dashboard(): void
    {
        $contractor = User::create(['name' => 'C', 'college_id' => 'C1', 'password' => Hash::make('password'), 'role' => 'contractor']);

        $response = $this->actingAs($contractor, 'contractor')->get('/student');

        $response->assertOk();
        $response->assertSee('Sign in to access');
        $response->assertDontSee('Today’s mess menu');
    }
}
