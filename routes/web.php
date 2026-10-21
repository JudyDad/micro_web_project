<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\LifecycleController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\ProjectController;
use App\Http\Middleware\FoundationPreview;
use App\Http\Middleware\RequireCurrentAccount;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'projects.index' : 'auth.login', ['locale' => 'ar']);
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
        Route::middleware(RequireCurrentAccount::class)->group(function () {
            Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
            Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
            Route::post('/applications', [ApplicationController::class, 'store'])->block(10, 10)->name('applications.store');
            Route::get('/applications/drafts', [ApplicationController::class, 'drafts'])->name('applications.drafts');
            Route::post('/applications/drafts', [ApplicationController::class, 'saveDraft'])->block(10, 10)->name('applications.drafts.save');
            Route::get('/applications/drafts/{draft}', [ApplicationController::class, 'restoreDraft'])->where('draft', '[a-f0-9]{32}')->name('applications.drafts.restore');
            Route::delete('/applications/drafts/{draft}', [ApplicationController::class, 'discardDraft'])->where('draft', '[a-f0-9]{32}')->name('applications.drafts.discard');
            Route::get('/applications/{application}', [ApplicationController::class, 'show'])->where('application', '[1-9][0-9]{0,18}')->name('applications.show');
            Route::post('/applications/{application}', [ApplicationController::class, 'update'])->where('application', '[1-9][0-9]{0,18}')->name('applications.update');
            Route::post('/applications/{application}/lifecycle', [LifecycleController::class, 'transition'])->where('application', '[1-9][0-9]{0,18}')->block(10, 10)->name('lifecycle.transition');
            Route::post('/applications/{application}/lifecycle/reason', [LifecycleController::class, 'reason'])->where('application', '[1-9][0-9]{0,18}')->block(10, 10)->name('lifecycle.reason');
            Route::get('/applications/{application}/checklist/{person}', [ChecklistController::class, 'show'])->where(['application' => '[1-9][0-9]{0,18}', 'person' => '[1-9][0-9]{0,18}'])->name('checklists.show');
            Route::post('/applications/{application}/checklist/{person}', [ChecklistController::class, 'save'])->where(['application' => '[1-9][0-9]{0,18}', 'person' => '[1-9][0-9]{0,18}'])->block(10, 10)->name('checklists.save');
            Route::post('/applications/{application}/people', [ApplicationController::class, 'person'])->where('application', '[1-9][0-9]{0,18}')->name('applications.person');
            Route::post('/applications/{application}/complete', [ApplicationController::class, 'complete'])->where('application', '[1-9][0-9]{0,18}')->name('applications.complete');
            Route::post('/applications/{application}/people/{link}/details/{kind}', [ApplicationController::class, 'applicantDetail'])->where(['application' => '[1-9][0-9]{0,18}', 'link' => '[1-9][0-9]{0,18}', 'kind' => 'loss|illness|asset|aid|help|homepower'])->name('applications.applicant-detail');
            Route::post('/applications/{application}/details/{kind}', [ApplicationController::class, 'detail'])->where(['application' => '[1-9][0-9]{0,18}', 'kind' => 'agriculture|rehab|rehab_answers|powersource|risk|material'])->name('applications.detail');
            Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
            Route::get('/people/create', [PeopleController::class, 'create'])->name('people.create');
            Route::post('/people', [PeopleController::class, 'store'])->block(10, 10)->name('people.store');
            Route::post('/people/families', [PeopleController::class, 'storeFamily'])->block(10, 10)->name('people.families.store');
            Route::get('/people/{person}', [PeopleController::class, 'show'])->where('person', '[1-9][0-9]{0,18}')->name('people.show');
            Route::put('/people/{person}', [PeopleController::class, 'update'])->where('person', '[1-9][0-9]{0,18}')->name('people.update');
            Route::post('/people/{person}/family', [PeopleController::class, 'family'])->where('person', '[1-9][0-9]{0,18}')->name('people.family');
            Route::post('/people/{person}/details/{kind}', [PeopleController::class, 'detail'])->where(['person' => '[1-9][0-9]{0,18}', 'kind' => 'education|workexp|course'])->name('people.detail');
            Route::get('/attachments/{reference}', [AttachmentController::class, 'download'])->where('reference', '[a-f0-9]{32}')->name('attachments.download');
            Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{record}', [ProjectController::class, 'show'])->where('record', '[1-9][0-9]{0,19}')->name('projects.show');
            Route::get('/cities', fn (string $locale) => redirect()->route('projects.index', ['locale' => $locale]))->name('cities.index');
            Route::prefix('/cities/{source}')->where(['source' => '[1-5]'])->group(function () {
                Route::get('/projects', [ProjectController::class, 'legacyIndex']);
                Route::get('/projects/{record}', [ProjectController::class, 'legacyShow'])->where('record', '[1-9][0-9]{0,19}');
            });
        });
    });
});
