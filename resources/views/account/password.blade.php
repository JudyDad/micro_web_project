<x-layouts.app :title="__('account.change_title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('account.change_title') }}</h1><p class="page-description">{{ __('account.change_intro', ['min' => \App\Services\AccountPasswords::MIN_LENGTH]) }}</p></div></div>
    @include('people.messages')
    @if($forced)<div class="form-message" role="note">{{ __('account.first_login') }}</div>@endif
    <section class="card">
        <form method="POST" action="{{ route('account.password.update', ['locale' => app()->getLocale()]) }}">@csrf
            <x-ui.input id="password" name="password" type="password" :label="__('account.new_password')" autocomplete="new-password" required />
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" :label="__('account.confirm_password')" autocomplete="new-password" required />
            <x-ui.button type="submit">{{ __('account.change') }}</x-ui.button>
        </form>
    </section>
</x-layouts.app>
