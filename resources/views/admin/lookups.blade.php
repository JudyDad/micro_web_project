@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $query = array_filter(['locale' => $locale, 'cities' => $selectedCities], fn ($v) => $v !== [] && $v !== null);
@endphp
<x-layouts.app :title="__('lookups.title')" :operational="true" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('lookups.title') }}</h1><p class="page-description">{{ __('lookups.intro') }}</p></div><span class="version-note">{{ __('search.read_only') }}</span></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('lookups.index', ['locale' => $locale]) }}">
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))><span>{{ $ar ? $city->name_ar : $city->name_en }}</span></label>@endforeach
            </div><p class="field-hint">{{ __('projects.all_cities_hint') }}</p></fieldset>
            <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button><a class="button button-secondary" href="{{ route('lookups.index', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a></div>
        </form>
        <x-ui.table :caption="__('lookups.title')"><thead><tr><th scope="col">{{ __('lookups.table') }}</th><th scope="col">{{ __('lookups.rows') }}</th><th scope="col">{{ __('lookups.export') }}</th></tr></thead><tbody>
        @foreach($lookups as $lookup)<tr><td>{{ __('lookups.names.'.$lookup['table']) }}</td><td><bdi>{{ number_format($lookup['count']) }}</bdi></td>
            <td><a class="record-link" href="{{ route('lookups.export', ['table' => $lookup['table']] + $query + ['format' => 'xlsx']) }}">Excel</a> · <a class="record-link" href="{{ route('lookups.export', ['table' => $lookup['table']] + $query + ['format' => 'csv']) }}">CSV</a></td></tr>@endforeach
        </tbody></x-ui.table>
    </section>
</x-layouts.app>
