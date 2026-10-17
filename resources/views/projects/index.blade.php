<x-layouts.app :title="__('projects.projects')" :operational="true" :synthetic="$synthetic ?? false" :search="$search" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('projects.projects') }}</h1><p class="page-description">{{ __('projects.projects_intro') }}</p></div><span class="version-tag">{{ __('projects.read_only_short') }}</span></div>
    <section class="card">
        @if($cities->isEmpty())
            <div class="results-empty"><span aria-hidden="true">▤</span><h2>{{ __('projects.no_access') }}</h2><p>{{ __('projects.no_cities_note') }}</p></div>
        @else
            <form class="search-form project-filters" method="GET" action="{{ route('projects.index', ['locale' => app()->getLocale()]) }}">
                <div class="field"><label for="search">{{ __('projects.search_label') }}</label><input class="input" id="search" name="search" type="search" value="{{ $search }}" maxlength="200" placeholder="{{ __('projects.search_placeholder') }}"></div>
                <fieldset class="city-filter" aria-describedby="city-filter-hint">
                    <legend>{{ __('projects.city_filter') }}</legend>
                    <div class="city-filter-options">
                        @foreach($cities as $city)
                            <label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((string) $city->id, array_map('strval', $selectedCities), true))><span>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</span></label>
                        @endforeach
                    </div>
                    <p id="city-filter-hint" class="field-hint">{{ __('projects.all_cities_hint') }}</p>
                </fieldset>
                <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>@if($search !== '' || count($selectedCities))<a class="button button-secondary" href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}">{{ __('projects.clear_filters') }}</a>@endif</div>
            </form>
            @if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('projects.filter_error') }}</div>@endif
            @if($projects->count())
                <x-ui.table :caption="__('projects.projects')">
                    <thead><tr><th scope="col">{{ __('projects.number') }}</th><th scope="col">{{ __('projects.name') }}</th><th scope="col">{{ __('projects.city') }}</th><th scope="col">{{ __('projects.date') }}</th><th scope="col">{{ __('projects.state') }}</th></tr></thead>
                    <tbody>@foreach($projects as $project)
                        @php($cityName = app()->isLocale('ar') ? $project->city_name_ar : $project->city_name_en)
                        <tr><td><a class="record-link" href="{{ route('projects.show', ['locale' => app()->getLocale(), 'record' => $project->record_id]) }}"><bdi>{{ $project->MP_ID }}</bdi><span class="sr-only"> · {{ $cityName }} · {{ $project->MP_Name }}</span></a></td><td class="project-name-cell">{{ $project->MP_Name }}</td><td>{{ $cityName }}</td><td><bdi>{{ $project->MP_DateOfRequest ?? '—' }}</bdi></td><td>{{ $project->ApplicationState ?? '—' }}</td></tr>
                    @endforeach</tbody>
                </x-ui.table>
                <div class="pagination"><x-project-pagination :paginator="$projects->appends(['search' => $search, 'cities' => array_values($selectedCities)])" /></div>
            @else
                <div class="results-empty"><span aria-hidden="true">▤</span><h2>{{ __('projects.no_results') }}</h2><p>{{ $search !== '' || count($selectedCities) ? __('projects.no_results_search') : __('projects.no_results_cities') }}</p></div>
            @endif
        @endif
    </section>
</x-layouts.app>
