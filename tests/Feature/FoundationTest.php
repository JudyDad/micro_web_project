<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_root_opens_arabic_login(): void
    {
        $this->get('/')->assertRedirect('/ar/login');
    }

    public function test_both_languages_render_with_correct_direction(): void
    {
        $this->withoutVite();
        foreach (['en' => 'ltr', 'ar' => 'rtl'] as $locale => $direction) {
            $this->get("/{$locale}/foundation")
                ->assertOk()
                ->assertHeader('Content-Language', $locale)
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
                ->assertSee('dir="'.$direction.'"', false)
                ->assertSee('lang="'.$locale.'"', false);
        }
    }

    public function test_unknown_language_is_not_accepted(): void
    {
        $this->get('/fr/foundation')->assertNotFound();
    }

    public function test_preview_is_hidden_in_production_while_root_opens_login(): void
    {
        $this->app->instance('env', 'production');
        $this->get('/en/foundation')->assertNotFound();
        $this->get('/ar/foundation')->assertNotFound();
        $this->get('/')->assertRedirect('/ar/login');
    }

    public function test_business_routes_require_authentication_and_unlocalized_login_is_not_exposed(): void
    {
        $this->get('/en/projects/1')->assertRedirect('/en/login');
        $this->get('/login')->assertNotFound();
    }

    public function test_health_endpoint_remains_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
