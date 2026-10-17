<x-layouts.guest :title="__('projects.sign_in')">
    <section class="login-card card"><div class="login-heading"><p class="eyebrow">{{ __('projects.welcome') }}</p><h1>{{ __('projects.sign_in') }}</h1><p>{{ __('projects.login_intro') }}</p></div>
        @if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('projects.login_error') }}</div>@endif
        @if(session('status'))<div class="form-message" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('auth.attempt', ['locale' => app()->getLocale()]) }}" class="login-form">
            @csrf
            <div class="field"><label for="source_id">{{ __('projects.city') }}</label><select class="input" id="source_id" name="source_id" required><option value="">{{ __('projects.choose_city') }}</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected((string) old('source_id') === (string) $city->id)>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</option>@endforeach</select></div>
            <x-ui.input id="username" name="username" :label="__('projects.username')" :value="old('username')" autocomplete="username" required maxlength="200" autocapitalize="none" spellcheck="false" />
            <x-ui.input id="password" name="password" type="password" :label="__('projects.password')" autocomplete="current-password" required />
            <x-ui.button type="submit" class="login-submit">{{ __('projects.sign_in') }}</x-ui.button>
        </form>
    </section>
</x-layouts.guest>
