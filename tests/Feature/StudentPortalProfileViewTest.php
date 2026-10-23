<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentPortalProfileViewTest extends TestCase
{
    public function test_profile_view_renders_a_valid_student_portal_link(): void
    {
        $html = view('livewire.student-portal.profile', [
            'state' => [
                'student' => [
                    'name' => 'Asha Rao',
                    'college_id' => 'STU001',
                    'email' => 'asha@college.edu',
                    'hostel' => 'Ganga',
                ],
                'dietary_options' => [],
                'qr' => [
                    'ttl_seconds' => 60,
                    'fingerprint' => 'test-fingerprint',
                ],
            ],
        ])->render();

        $this->assertStringContainsString('href="'.route('home').'"', $html);
        $this->assertStringContainsString('Back to student portal', $html);
    }
}
