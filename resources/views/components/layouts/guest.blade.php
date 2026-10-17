@props(['title'])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>{{ $title }} · {{ __('ui.brand') }}</title>@vite(['resources/css/app.css', 'resources/js/app.js']) @livewireStyles</head>
<body class="guest-body">
    <a class="skip-link" href="#main">{{ __('ui.skip') }}</a>
    <header class="guest-header"><div class="header-brand"><img class="brand-logo" src="{{ asset('assets/brand/hope-center.png') }}" alt="{{ __('projects.organization') }}" width="38" height="38"><strong>{{ __('ui.brand') }}</strong></div><a class="language-link" href="{{ route('auth.login', ['locale' => app()->isLocale('ar') ? 'en' : 'ar']) }}" lang="{{ app()->isLocale('ar') ? 'en' : 'ar' }}">{{ app()->isLocale('ar') ? 'English' : 'العربية' }}</a></header>
    <main id="main" class="guest-main" tabindex="-1">{{ $slot }}</main>
    <footer class="guest-footer">{{ __('projects.organization') }}</footer>
    @livewireScripts
</body>
</html>
