<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_endpoints_are_not_available(): void
    {
        $this->get('/verify-email')->assertNotFound();
        $this->post('/email/verification-notification')->assertNotFound();
    }
}
