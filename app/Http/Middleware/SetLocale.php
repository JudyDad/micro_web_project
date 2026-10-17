<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        abort_unless(in_array($locale, ['en', 'ar'], true), 404);
        app()->setLocale($locale);
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
