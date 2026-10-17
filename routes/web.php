<?php

use App\Http\Middleware\FoundationPreview;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    abort_unless(app()->environment(['local', 'testing']), 404);

    return redirect()->route('foundation', ['locale' => 'ar']);
});

Route::view('/{locale}/foundation', 'foundation')
    ->whereIn('locale', ['en', 'ar'])
    ->middleware(FoundationPreview::class)
    ->name('foundation');
