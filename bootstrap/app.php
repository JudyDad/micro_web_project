<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->trimStrings(except: ['username', 'password']);
        $middleware->redirectGuestsTo(fn (Request $request) => route('auth.login', ['locale' => in_array($request->route('locale'), ['en', 'ar'], true) ? $request->route('locale') : 'ar']));
        $middleware->redirectUsersTo(fn (Request $request) => route('projects.index', ['locale' => in_array($request->route('locale'), ['en', 'ar'], true) ? $request->route('locale') : 'ar']));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
