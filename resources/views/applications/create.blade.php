<x-layouts.app :title="__('applications.create')" :operational="true">
    <a class="back-link" href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><h1>{{ __('applications.create') }}</h1><p class="page-description">{{ __('applications.boundary') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form"><form method="POST" action="{{ route('applications.store', ['locale' => app()->getLocale()]) }}">@csrf
        <input type="hidden" name="create_token" value="{{ $createToken }}">
        <input type="hidden" name="stage" value="preliminary">
        @include('applications.context', ['kind' => 'application', 'prefix' => 'application'])
        <div class="field"><label for="beneficiary">{{ __('applications.beneficiary') }}</label><select class="input" id="beneficiary" name="beneficiary_id"><option value="">{{ __('applications.select_person') }}</option>
            @if(old('beneficiary_id') && ! $persons->contains(fn ($person) => (string) $person->id === (string) old('beneficiary_id')))<option value="{{ old('beneficiary_id') }}" selected>{{ __('people.retained_reference') }} #{{ old('beneficiary_id') }}</option>@endif
            @foreach($persons as $person)<option value="{{ $person->id }}" @selected((string) old('beneficiary_id') === (string) $person->id)>{{ $person->label }}</option>@endforeach</select></div>
        <p>{{ __('applications.person_limit') }} <a href="{{ route('people.index', ['locale' => app()->getLocale()]) }}">{{ __('people.title') }}</a></p>
        @include('applications.fields', ['fields' => $coreFields, 'options' => $coreOptions, 'row' => null, 'prefix' => 'application'])
        <details class="people-history"><summary>{{ __('applications.additional_details') }}</summary>
        @include('applications.fields', ['fields' => array_diff_key($fields, $coreFields), 'options' => array_diff_key($options, $coreOptions), 'row' => null, 'prefix' => 'application'])
        </details>
        @include('applications.actions')
    </form></section>
</x-layouts.app>

