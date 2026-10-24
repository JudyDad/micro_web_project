<x-layouts.guest :title="__('account.reset_title')">
    <section class="login-card card"><div class="login-heading"><h1>{{ __('account.reset_title') }}</h1>
        @if($valid)<p>{{ __('account.change_intro', ['min' => \App\Services\AccountPasswords::MIN_LENGTH]) }}</p>@endif</div>
        @if($errors->any())<div class="form-message form-message-error" role="alert">{{ $errors->first() }}</div>@endif
        @if($valid)
        <form method="POST" action="{{ route('auth.reset.store', ['locale' => app()->getLocale(), 'token' => $token]) }}" class="login-form">@csrf
            <x-ui.input id="password" name="password" type="password" :label="__('account.new_password')" autocomplete="new-password" required />
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" :label="__('account.confirm_password')" autocomplete="new-password" required />
            <x-ui.button type="submit" class="login-submit">{{ __('account.change') }}</x-ui.button>
        </form>
        @else
        <div class="form-message form-message-error" role="alert">{{ __('account.expired') }}</div>
        <p><a href="{{ route('auth.forgot', ['locale' => app()->getLocale()]) }}">{{ __('account.forgot_title') }}</a></p>
        @endif
    </section>
</x-layouts.guest>
