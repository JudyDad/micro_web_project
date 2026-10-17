<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Middleware\FoundationPreview;
use App\Http\Middleware\RequireCurrentPrincipal;
use App\Http\Middleware\RequireProjectAccess;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'cities.index' : 'auth.login', ['locale' => 'ar']);
});

Route::view('/{locale}/foundation', 'foundation')
    ->whereIn('locale', ['en', 'ar'])
    ->middleware(FoundationPreview::class)
    ->name('foundation');

Route::prefix('{locale}')->where(['locale' => 'en|ar'])->middleware(SetLocale::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'show'])->name('auth.login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('auth.attempt');
    });
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::middleware(RequireCurrentPrincipal::class)->group(function () {
            Route::get('/cities', [ProjectController::class, 'cities'])->name('cities.index');
            Route::prefix('/cities/{source}')->where(['source' => '[1-5]'])->middleware(RequireProjectAccess::class)->group(function () {
                Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
                Route::get('/projects/{record}', [ProjectController::class, 'show'])->where('record', '[1-9][0-9]{0,19}')->name('projects.show');
            });
        });
    });
});
