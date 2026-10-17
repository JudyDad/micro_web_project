<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(30)->by('login-ip:'.hash('sha256', (string) $request->ip())),
            Limit::perMinute(6)->by('login-identity:'.hash('sha256', json_encode([$request->input('source_id'), $request->input('username'), $request->ip()]))),
        ]);
    }
}
