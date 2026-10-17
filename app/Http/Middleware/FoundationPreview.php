<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FoundationPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $locale = $request->route('locale');
        abort_unless(in_array($locale, ['en', 'ar'], true), 404);
        app()->setLocale($locale);
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
