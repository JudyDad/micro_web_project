@props(['title' => null, 'operational' => false, 'synthetic' => false, 'search' => '', 'selectedCities' => []])
@php
    $otherLocale = app()->isLocale('ar') ? 'en' : 'ar';
    $languageFilters = [];
    if (request()->routeIs('projects.index', 'people.index', 'applications.index')) {
        $languageFilters = ['search' => $search ?? '', 'cities' => array_values($selectedCities ?? [])];
    }
    if (request()->routeIs('applications.index', 'reports.statistics')) {
        $languageFilters += array_filter(request()->only(['stage', 'state', 'date_from', 'date_to']), fn ($value) => is_string($value) && $value !== '');
        $languageFilters['cities'] ??= array_values(array_filter((array) request('cities'), 'is_scalar'));
    }
    if (request()->routeIs('applications.create', 'applications.drafts')) {
        $languageFilters['city_id'] = request('city_id');
    }
    $languageUrl = route(request()->route()->getName(), array_merge($languageFilters, request()->route()->parameters(), ['locale' => $otherLocale]));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? __('ui.foundation') }} · {{ __('ui.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    x-data="{
        navigationOpen: false,
        sidebarCollapsed: false,
        init() { try { this.sidebarCollapsed = localStorage.getItem('mp.sidebar.collapsed') === '1'; } catch (_) {} },
        toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed; try { localStorage.setItem('mp.sidebar.collapsed', this.sidebarCollapsed ? '1' : '0'); } catch (_) {} },
        toggleNavigation() {
            if (this.navigationOpen) { this.closeNavigation(); return; }
            this.navigationOpen = true;
            this.$nextTick(() => this.$refs.navigation.querySelector('a').focus());
        },
        closeNavigation(restoreFocus = true) {
            const wasOpen = this.navigationOpen;
            this.navigationOpen = false;
            if (wasOpen && restoreFocus) this.$refs.mobileToggle.focus();
        }
    }"
    :class="{ 'sidebar-collapsed': sidebarCollapsed, 'navigation-open': navigationOpen }"
    @keydown.escape.window="closeNavigation()"
    @resize.window.debounce.100ms="closeNavigation(false)"
>
    <a class="skip-link" href="#main">{{ __('ui.skip') }}</a>
    <header class="app-header">
        <div class="header-brand"><button x-ref="mobileToggle" class="icon-button mobile-toggle" type="button" @click="toggleNavigation()" :aria-expanded="navigationOpen" aria-controls="navigation" aria-label="{{ __('ui.menu') }}">☰</button><button class="icon-button desktop-toggle" type="button" @click="toggleSidebar()" :aria-expanded="!sidebarCollapsed" aria-controls="navigation" aria-label="{{ __('ui.toggle_sidebar') }}">☰</button><img class="brand-logo" src="{{ asset('assets/brand/hope-center.png') }}" alt="{{ __('projects.organization') }}" width="38" height="38"><strong>{{ __('ui.brand') }}</strong></div>
        <div class="header-actions">@unless($operational)<span class="environment-badge">{{ __('ui.preview') }}</span>@endunless
            @if($operational && auth()->check() && app(\App\Services\Staff\NotificationInboxService::class)->cities(auth()->user())->isNotEmpty())
                @php($unseen = app(\App\Services\Staff\NotificationInboxService::class)->count(auth()->user()))
                <a @class(['icon-button header-icon', 'is-current' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index', ['locale' => app()->getLocale()]) }}" aria-label="{{ __('staff.nav_notifications') }}@if($unseen > 0) ({{ $unseen }})@endif" title="{{ __('staff.nav_notifications') }}"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>@if($unseen > 0)<span class="header-badge">{{ $unseen > 99 ? '99+' : $unseen }}</span>@endif</a>
            @endif
            <div class="header-menu" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                <button type="button" class="icon-button header-icon" @click="open = !open" :aria-expanded="open" aria-haspopup="true" aria-label="{{ __('ui.language') }}" title="{{ __('ui.language') }}"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/></svg></button>
                <div class="header-dropdown" x-cloak x-show="open"><a class="header-dropdown-item" href="{{ $languageUrl }}" lang="{{ $otherLocale }}">{{ app()->isLocale('ar') ? 'English' : 'العربية' }}</a><span class="header-dropdown-item is-current" aria-current="true">{{ app()->isLocale('ar') ? 'العربية' : 'English' }}</span></div>
            </div>
            @if($operational && auth()->check())
            <div class="header-menu" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                <button type="button" class="icon-button header-icon" @click="open = !open" :aria-expanded="open" aria-haspopup="true" aria-label="{{ __('ui.account') }}" title="{{ __('ui.account') }}"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></button>
                <div class="header-dropdown" x-cloak x-show="open">
                    <a @class(['header-dropdown-item', 'is-current' => request()->routeIs('account.password*')]) href="{{ route('account.password', ['locale' => app()->getLocale()]) }}">{{ __('account.menu') }}</a>
                    <form method="POST" action="{{ route('auth.logout', ['locale' => app()->getLocale()]) }}">@csrf<button class="header-dropdown-item" type="submit">{{ __('projects.logout') }}</button></form>
                </div>
            </div>
            @endif
        </div>
    </header>
    <button x-cloak x-show="navigationOpen" class="sidebar-backdrop" tabindex="-1" @click="closeNavigation()" aria-label="{{ __('ui.close_menu') }}"></button>
    <aside x-ref="navigation" id="navigation" class="app-sidebar" :class="{ 'is-open': navigationOpen }">
        <p class="nav-label">{{ __('ui.workspace') }}</p>
        <nav aria-label="{{ __('ui.navigation') }}">
            @if($operational)
                <a @class(['nav-link', 'is-current' => request()->routeIs('applications.*')]) href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">▧</span><span class="nav-text">{{ __('applications.title') }}</span></a>
                @if(auth()->check() && app(\App\Services\AccountAccess::class)->cities(auth()->user(), 'applications.view')->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('reports.*')]) href="{{ route('reports.statistics', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">▥</span><span class="nav-text">{{ __('reports.nav') }}</span></a>@endif
                <a @class(['nav-link', 'is-current' => request()->routeIs('people.*')]) href="{{ route('people.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">♧</span><span class="nav-text">{{ __('people.title') }}</span></a>
                @if(auth()->check() && app(\App\Services\Field\ProfessionMeetingService::class)->cities(auth()->user())->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('meetings.*')]) href="{{ route('meetings.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">◍</span><span class="nav-text">{{ __('field.nav_meetings') }}</span></a>@endif
                @if(auth()->check() && app(\App\Services\Field\VisitScheduleService::class)->cities(auth()->user())->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('visits.schedule')]) href="{{ route('visits.schedule', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">◔</span><span class="nav-text">{{ __('field.nav_schedule') }}</span></a>@endif
                @if(auth()->check() && app(\App\Services\Finance\PaymentScheduleService::class)->cities(auth()->user())->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('finance.*')]) href="{{ route('finance.payments', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">◈</span><span class="nav-text">{{ __('finance.nav_payments') }}</span></a>@endif
                @if(auth()->check() && app(\App\Services\Staff\NotificationInboxService::class)->cities(auth()->user())->isNotEmpty())@php($unseen = app(\App\Services\Staff\NotificationInboxService::class)->count(auth()->user()))<a @class(['nav-link', 'is-current' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">♢</span><span class="nav-text">{{ __('staff.nav_notifications') }}@if($unseen > 0) <strong>({{ $unseen }})</strong>@endif</span></a>@endif
                @if(auth()->check() && app(\App\Services\Staff\AccountAdminService::class)->cities(auth()->user())->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('admin.*')]) href="{{ route('admin.accounts.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">♜</span><span class="nav-text">{{ __('admin.nav') }}</span></a>@endif
                @if(auth()->check() && app(\App\Services\Staff\TaskBoardService::class)->cities(auth()->user())->isNotEmpty())<a @class(['nav-link', 'is-current' => request()->routeIs('tasks.*')]) href="{{ route('tasks.board', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">☑</span><span class="nav-text">{{ __('staff.nav_tasks') }}</span></a>@endif
                <a @class(['nav-link', 'is-current' => request()->routeIs('projects.*')]) href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}" @if(request()->routeIs('projects.index')) aria-current="page" @endif aria-label="{{ __('projects.projects') }}" title="{{ __('projects.projects') }}"><span aria-hidden="true">▤</span><span class="nav-text">{{ __('projects.projects') }}</span></a>
            @else
            <a class="nav-link is-current" href="#main" @click="closeNavigation(false)" aria-current="page" aria-label="{{ __('ui.foundation') }}" title="{{ __('ui.foundation') }}"><span aria-hidden="true">◫</span><span class="nav-text">{{ __('ui.foundation') }}</span></a>
            <a class="nav-link" href="#projects" @click="closeNavigation(false)" aria-label="{{ __('ui.project_preview') }}" title="{{ __('ui.project_preview') }}"><span aria-hidden="true">▤</span><span class="nav-text">{{ __('ui.project_preview') }}</span></a>
            <a class="nav-link" href="#components" @click="closeNavigation(false)" aria-label="{{ __('ui.components') }}" title="{{ __('ui.components') }}"><span aria-hidden="true">◇</span><span class="nav-text">{{ __('ui.components') }}</span></a>
            @endif
        </nav>
        <div class="sidebar-note"><span class="status-dot"></span><strong>{{ $operational ? __('projects.workspace') : __('ui.local_foundation') }}</strong><p>{{ $operational ? __('projects.read_only') : __('ui.sidebar_note') }}</p></div>
    </aside>
    <main id="main" class="app-main" tabindex="-1" :inert="navigationOpen">@if($synthetic)<div class="synthetic-notice" role="note">{{ __('projects.synthetic') }}</div>@endif{{ $slot }}<footer>{{ $operational ? __('projects.organization') : __('ui.asset_notice') }}</footer></main>
    @livewireScripts
</body>
</html>

