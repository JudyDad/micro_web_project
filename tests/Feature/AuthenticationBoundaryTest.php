<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class EnforcedCsrf extends ValidateCsrfToken
{
    protected function runningUnitTests()
    {
        return false;
    }
}

class AuthenticationBoundaryTest extends TestCase
{
    public function test_login_and_logout_reject_missing_csrf_before_accessing_data(): void
    {
        $this->app->bind(ValidateCsrfToken::class, EnforcedCsrf::class);
        $this->post('/en/login', ['username' => 'demo.user', 'password' => 'synthetic'])->assertStatus(419);
        $this->post('/ar/logout')->assertStatus(419);
    }

    public function test_invalid_form_with_valid_csrf_returns_validation_errors_without_flashing_password(): void
    {
        $this->app->bind(ValidateCsrfToken::class, EnforcedCsrf::class);
        $this->withSession(['_token' => 'synthetic-csrf-test-token'])
            ->from('/en/login')->post('/en/login', ['_token' => 'synthetic-csrf-test-token', 'password' => 'Synthetic-secret'])
            ->assertRedirect('/en/login')->assertSessionHasErrors(['username']);
        $this->assertNull(session()->getOldInput('password'));
    }

    public function test_repeated_login_attempts_are_limited_before_credentials_are_evaluated(): void
    {
        app('cache')->flush();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->from('/en/login')->post('/en/login', ['username' => 'limited.example'])->assertRedirect('/en/login');
        }
        $this->post('/en/login', ['username' => 'limited.example'])->assertStatus(429)->assertHeader('Retry-After');
    }
}
