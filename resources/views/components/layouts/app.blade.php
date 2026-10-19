@props(['title' => null, 'operational' => false, 'synthetic' => false, 'search' => '', 'selectedCities' => []])
@php
    $otherLocale = app()->isLocale('ar') ? 'en' : 'ar';
    $languageFilters = [];
    if (request()->routeIs('projects.index', 'people.index')) {
        $languageFilters = ['search' => $search ?? '', 'cities' => array_values($selectedCities ?? [])];
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
        <div class="header-actions">@unless($operational)<span class="environment-badge">{{ __('ui.preview') }}</span>@endunless<a class="language-link" href="{{ $languageUrl }}" lang="{{ $otherLocale }}">{{ app()->isLocale('ar') ? 'English' : 'العربية' }}</a></div>
    </header>
    <button x-cloak x-show="navigationOpen" class="sidebar-backdrop" tabindex="-1" @click="closeNavigation()" aria-label="{{ __('ui.close_menu') }}"></button>
    <aside x-ref="navigation" id="navigation" class="app-sidebar" :class="{ 'is-open': navigationOpen }">
        <p class="nav-label">{{ __('ui.workspace') }}</p>
        <nav aria-label="{{ __('ui.navigation') }}">
            @if($operational)
                <a @class(['nav-link', 'is-current' => request()->routeIs('people.*')]) href="{{ route('people.index', ['locale' => app()->getLocale()]) }}"><span aria-hidden="true">♧</span><span class="nav-text">{{ __('people.title') }}</span></a>
                <a @class(['nav-link', 'is-current' => request()->routeIs('projects.*')]) href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}" @if(request()->routeIs('projects.index')) aria-current="page" @endif aria-label="{{ __('projects.projects') }}" title="{{ __('projects.projects') }}"><span aria-hidden="true">▤</span><span class="nav-text">{{ __('projects.projects') }}</span></a>
                <form method="POST" action="{{ route('auth.logout', ['locale' => app()->getLocale()]) }}">@csrf<button class="nav-link nav-button" type="submit" aria-label="{{ __('projects.logout') }}" title="{{ __('projects.logout') }}"><span aria-hidden="true">↪</span><span class="nav-text">{{ __('projects.logout') }}</span></button></form>
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
