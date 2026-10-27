<x-layouts.app :title="__('people.title')" :operational="true" :search="$search" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('people.title') }}</h1><p class="page-description">{{ __('people.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('people.index', ['locale' => app()->getLocale()]) }}">
            <div class="field"><label for="search">{{ __('people.search') }}</label><input class="input" id="search" type="search" name="search" value="{{ $search }}" maxlength="200"></div>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('people.index', ['locale' => app()->getLocale()]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <x-ui.table :caption="__('people.title')"><thead><tr><th>{{ __('people.name') }}</th><th>{{ __('people.fields.P_NationalNumber') }}</th><th>{{ __('projects.city') }}</th></tr></thead><tbody>
        @forelse($people as $person)<tr><td><a class="record-link" href="{{ route('people.show', ['locale' => app()->getLocale(), 'person' => $person->id]) }}">{{ $person->c_P_FirstName }} {{ $person->c_P_LastName }}</a></td><td><bdi>{{ $person->c_P_NationalNumber ?? '—' }}</bdi></td><td>{{ app()->isLocale('ar') ? $person->city_name_ar : $person->city_name_en }}</td></tr>
        @empty<tr><td colspan="3">{{ __('projects.no_results') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$people" /></div>
    </section>
    @if($manageCities->isNotEmpty())
    <section class="card people-form"><h2>{{ __('people.create') }}</h2><p class="field-hint">{{ __('people.create_hint') }}</p><div class="filter-actions">
        @foreach($manageCities as $city)<a class="button button-primary" href="{{ route('people.create', ['locale' => app()->getLocale(), 'city_id' => $city->id]) }}">{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</a>@endforeach
    </div></section>
    <details class="card people-form"><summary>{{ __('people.create_family') }}</summary>
        <form method="POST" action="{{ route('people.families.store', ['locale' => app()->getLocale()]) }}">@csrf
            <input type="hidden" name="_form" value="family-root">
            <input type="hidden" name="create_token" value="{{ $createToken }}">
            <div class="people-fields"><div class="field"><label for="family-city">{{ __('projects.city') }}</label><select class="input" id="family-city" name="city_id">@foreach($manageCities as $city)<option value="{{ $city->id }}" @selected(old('_form') === 'family-root' && (string) old('city_id') === (string) $city->id)>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</option>@endforeach</select></div>
            <div class="field"><label for="family-number">{{ __('people.fields.F_Number') }}</label><input class="input" id="family-number" name="fields[c_F_Number]" value="{{ old('_form') === 'family-root' ? old('fields.c_F_Number') : '' }}" maxlength="50" required></div></div>
            <label class="people-confirm"><input type="checkbox" name="confirm_duplicate" value="1"> {{ __('people.confirm_duplicate') }}</label>
            <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
        </form>
    </details>
    @endif
</x-layouts.app>
