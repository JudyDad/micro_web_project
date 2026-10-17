<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoundationTest extends TestCase
{
    public function test_root_opens_arabic_foundation(): void
    {
        $this->get('/')->assertRedirect('/ar/foundation');
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

    public function test_preview_and_root_are_unavailable_in_production(): void
    {
        $this->app->instance('env', 'production');
        $this->get('/en/foundation')->assertNotFound();
        $this->get('/ar/foundation')->assertNotFound();
        $this->get('/')->assertNotFound();
    }

    public function test_business_and_authentication_routes_are_not_exposed(): void
    {
        $this->get('/en/projects/1')->assertNotFound();
        $this->get('/login')->assertNotFound();
    }

    public function test_health_endpoint_remains_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
