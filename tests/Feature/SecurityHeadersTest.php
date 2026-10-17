<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_success_redirect_and_denial_responses_restrict_framing_and_content_sniffing(): void
    {
        $this->withoutVite();
        foreach (['/', '/en/foundation', '/ar/foundation', '/fr/foundation', '/login'] as $path) {
            $this->get($path)
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'");
        }
    }

    public function test_https_production_response_uses_hsts_without_enabling_it_on_local_http(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
        $this->app->instance('env', 'production');
        $this->get('https://localhost/up')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->get('https://localhost/en/foundation')->assertNotFound()->assertHeader('X-Frame-Options', 'DENY');
    }
}
