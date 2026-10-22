<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ExecutiveFileController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LifecycleController;
use App\Http\Controllers\MonitoringVisitController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SpecialCaseController;
use App\Http\Controllers\VisitController;
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
            Route::get('/payments', [FinanceController::class, 'payments'])->name('finance.payments');
            Route::get('/visit-schedule', [VisitController::class, 'schedule'])->name('visits.schedule');
            Route::prefix('/applications/{application}')->where(['application' => '[1-9][0-9]{0,18}'])->group(function () {
                Route::get('/finance', [FinanceController::class, 'show'])->name('finance.show');
                Route::post('/finance/donor', [FinanceController::class, 'donor'])->block(10, 10)->name('finance.donor');
                Route::post('/finance/groups', [FinanceController::class, 'group'])->block(10, 10)->name('finance.group');
                Route::post('/finance/loan', [FinanceController::class, 'loan'])->block(10, 10)->name('finance.loan');
                Route::post('/finance/installments', [FinanceController::class, 'installments'])->block(10, 10)->name('finance.installments');
                Route::get('/executive-files', [ExecutiveFileController::class, 'show'])->name('executive.show');
                Route::post('/executive-files', [ExecutiveFileController::class, 'save'])->block(10, 10)->name('executive.save');
                Route::get('/purchasing', [PurchasingController::class, 'show'])->name('purchasing.show');
                Route::post('/purchasing', [PurchasingController::class, 'save'])->block(10, 10)->name('purchasing.save');
                Route::get('/special-cases', [SpecialCaseController::class, 'show'])->name('special-cases.show');
                Route::post('/special-cases', [SpecialCaseController::class, 'save'])->block(10, 10)->name('special-cases.save');
                Route::post('/special-cases/{case}/delete', [SpecialCaseController::class, 'delete'])->where('case', '[1-9][0-9]{0,18}')->block(10, 10)->name('special-cases.delete');
                Route::post('/visits/{kind}/delete', [VisitController::class, 'delete'])->where('kind', '[OV]-(op|cl)')->block(10, 10)->name('visits.delete');
                Route::get('/me-visits', [MonitoringVisitController::class, 'hub'])->name('me.hub');
                Route::get('/me-visits/new/{person}', [MonitoringVisitController::class, 'create'])->where('person', '[1-9][0-9]{0,18}')->name('me.create');
                Route::get('/me-visits/{visit}', [MonitoringVisitController::class, 'show'])->where('visit', '[1-9][0-9]{0,18}')->name('me.show');
                Route::post('/me-visits', [MonitoringVisitController::class, 'save'])->block(10, 10)->name('me.save');
                Route::post('/me-visits/{visit}/delete', [MonitoringVisitController::class, 'delete'])->where('visit', '[1-9][0-9]{0,18}')->block(10, 10)->name('me.delete');
                Route::get('/visits', [VisitController::class, 'hub'])->name('visits.hub');
                Route::get('/visits/{kind}', [VisitController::class, 'show'])->where('kind', '[OV]-(op|cl)')->name('visits.show');
                Route::post('/visits/{kind}', [VisitController::class, 'save'])->where('kind', '[OV]-(op|cl)')->block(10, 10)->name('visits.save');
            });
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
