<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('ui.foundation') }} · {{ __('ui.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="{ navigationOpen: false, sidebarCollapsed: false, init() { try { this.sidebarCollapsed = localStorage.getItem('mp.sidebar.collapsed') === '1'; } catch (_) {} }, toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed; try { localStorage.setItem('mp.sidebar.collapsed', this.sidebarCollapsed ? '1' : '0'); } catch (_) {} } }" :class="{ 'sidebar-collapsed': sidebarCollapsed }" @keydown.escape.window="navigationOpen = false">
    <a class="skip-link" href="#main">{{ __('ui.skip') }}</a>
    <header class="app-header">
        <div class="header-brand"><button class="icon-button mobile-toggle" type="button" @click="navigationOpen = !navigationOpen" :aria-expanded="navigationOpen" aria-controls="navigation" aria-label="{{ __('ui.menu') }}">☰</button><button class="icon-button desktop-toggle" type="button" @click="toggleSidebar()" :aria-expanded="!sidebarCollapsed" aria-controls="navigation" aria-label="{{ __('ui.toggle_sidebar') }}">☰</button><span class="brand-mark" aria-hidden="true">MP</span><strong>{{ __('ui.brand') }}</strong></div>
        <div class="header-actions"><span class="environment-badge">{{ __('ui.preview') }}</span><a class="language-link" href="{{ route('foundation', ['locale' => app()->isLocale('ar') ? 'en' : 'ar']) }}" lang="{{ app()->isLocale('ar') ? 'en' : 'ar' }}">{{ app()->isLocale('ar') ? 'English' : 'العربية' }}</a></div>
    </header>
    <button x-cloak x-show="navigationOpen" class="sidebar-backdrop" @click="navigationOpen = false" aria-label="{{ __('ui.close_menu') }}"></button>
    <aside id="navigation" class="app-sidebar" :class="{ 'is-open': navigationOpen }">
        <p class="nav-label">{{ __('ui.workspace') }}</p>
        <nav aria-label="{{ __('ui.navigation') }}">
            <a class="nav-link is-current" href="#main" @click="navigationOpen = false" aria-current="page" aria-label="{{ __('ui.foundation') }}" title="{{ __('ui.foundation') }}"><span aria-hidden="true">◫</span><span class="nav-text">{{ __('ui.foundation') }}</span></a>
            <a class="nav-link" href="#projects" @click="navigationOpen = false" aria-label="{{ __('ui.project_preview') }}" title="{{ __('ui.project_preview') }}"><span aria-hidden="true">▤</span><span class="nav-text">{{ __('ui.project_preview') }}</span></a>
            <a class="nav-link" href="#components" @click="navigationOpen = false" aria-label="{{ __('ui.components') }}" title="{{ __('ui.components') }}"><span aria-hidden="true">◇</span><span class="nav-text">{{ __('ui.components') }}</span></a>
        </nav>
        <div class="sidebar-note"><span class="status-dot"></span><strong>{{ __('ui.local_foundation') }}</strong><p>{{ __('ui.sidebar_note') }}</p></div>
    </aside>
    <main id="main" class="app-main" tabindex="-1">{{ $slot }}<footer>{{ __('ui.asset_notice') }}</footer></main>
    @livewireScripts
</body>
</html>
