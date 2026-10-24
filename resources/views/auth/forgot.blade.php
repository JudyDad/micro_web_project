<x-layouts.guest :title="__('account.forgot_title')">
    <section class="login-card card"><div class="login-heading"><h1>{{ __('account.forgot_title') }}</h1><p>{{ __('account.forgot_intro') }}</p></div>
        @if($errors->any())<div class="form-message form-message-error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('auth.forgot.request', ['locale' => app()->getLocale()]) }}" class="login-form">@csrf
            <x-ui.input id="username" name="username" :label="__('projects.username')" :value="old('username')" autocomplete="username" required maxlength="200" autocapitalize="none" spellcheck="false" />
            <x-ui.input id="email" name="email" type="email" :label="__('account.email')" autocomplete="email" required maxlength="190" />
            <x-ui.button type="submit" class="login-submit">{{ __('account.continue') }}</x-ui.button>
        </form>
        <p><a href="{{ route('auth.login', ['locale' => app()->getLocale()]) }}">{{ __('account.back_to_login') }}</a></p>
    </section>
</x-layouts.guest>
