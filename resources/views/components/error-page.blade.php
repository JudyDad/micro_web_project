@props(['status'])
@php
    $locale = request()->route('locale');
    if (in_array($locale, ['en', 'ar'], true)) {
        app()->setLocale($locale);
    }
@endphp
<x-layouts.guest :title="__('projects.error_'.$status.'_title')">
    <section class="login-card card">
        <div class="login-heading"><p class="eyebrow">{{ $status }}</p><h1>{{ __('projects.error_'.$status.'_title') }}</h1><p>{{ __('projects.error_'.$status.'_message') }}</p></div>
        <a class="button button-primary login-submit" href="{{ route('auth.login', ['locale' => app()->isLocale('ar') ? 'ar' : 'en']) }}">{{ __('projects.back_sign_in') }}</a>
    </section>
</x-layouts.guest>
