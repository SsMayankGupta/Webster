<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_student_registration_is_not_available(): void
    {
        $this->get('/student/register')->assertNotFound();
    }

    public function test_generic_registration_endpoint_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
