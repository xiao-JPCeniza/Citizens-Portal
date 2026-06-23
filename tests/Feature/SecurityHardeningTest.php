<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_security_headers_are_set_on_web_responses(): void
    {
        $response = $this->get(route('welcome'));

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');

        $this->assertStringContainsString(
            "default-src 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_http_requests_are_redirected_to_https_when_enabled(): void
    {
        config()->set('app.force_https', true);

        $response = $this->withServerVariables([
            'HTTP_HOST' => 'localhost',
            'HTTPS' => 'off',
        ])->get('http://localhost/');

        $response->assertStatus(301);

        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://', $location);
    }
}
