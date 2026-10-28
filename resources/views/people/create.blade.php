<x-layouts.app :title="__('people.create')" :operational="true">
    <a class="back-link" href="{{ route('people.index', ['locale' => app()->getLocale()]) }}">{{ __('people.back') }}</a>
    <div class="page-heading"><div><h1>{{ __('people.create') }}</h1><p>{{ __('people.duplicate_hint') }}</p></div></div>
    @include('people.messages')
    <form class="card people-form" method="POST" action="{{ route('people.store', ['locale' => app()->getLocale()]) }}">
        @csrf<input type="hidden" name="city_id" value="{{ $cityId }}"><input type="hidden" name="create_token" value="{{ $createToken }}">
        @include('people.fields', ['row' => null, 'prefix' => 'new', 'groups' => true])
        <label class="people-confirm"><input type="checkbox" name="confirm_duplicate" value="1"> {{ __('people.confirm_duplicate') }}</label>
        <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
    </form>
</x-layouts.app>
