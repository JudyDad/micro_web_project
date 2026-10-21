<x-layouts.app :title="__('applications.title')" :operational="true" :search="$search" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('applications.title') }}</h1><p class="page-description">{{ __('applications.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">
            <div class="field"><label for="search">{{ __('applications.search') }}</label><input class="input" id="search" type="search" name="search" value="{{ $search }}" maxlength="200"></div>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <x-ui.table :caption="__('applications.title')"><thead><tr><th>{{ __('applications.fields.MP_ID') }}</th><th>{{ __('applications.fields.MP_Name') }}</th><th>{{ __('applications.state') }}</th><th>{{ __('projects.city') }}</th></tr></thead><tbody>
        @forelse($applications as $application)<tr><td><bdi>{{ $application->c_MP_ID }}</bdi></td><td><a class="record-link" href="{{ route('applications.show', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">{{ $application->c_MP_Name ?: '#'.$application->id }}</a></td><td>{{ in_array($application->c_ApplicationState, ['zero', 'project'], true) ? __('applications.states.'.$application->c_ApplicationState) : $application->c_ApplicationState }}</td><td>{{ app()->isLocale('ar') ? $application->city_name_ar : $application->city_name_en }}</td></tr>
        @empty<tr><td colspan="4">{{ __('projects.no_results') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$applications" /></div>
    </section>
    @if($manageCities->isNotEmpty())
    <section class="card people-form"><h2>{{ __('applications.create') }}</h2>
        @foreach($manageCities as $city)<div class="filter-actions"><strong>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</strong><a class="button button-primary" href="{{ route('applications.create', ['locale' => app()->getLocale(), 'city_id' => $city->id]) }}">{{ __('applications.create') }}</a><a class="button button-secondary" href="{{ route('applications.drafts', ['locale' => app()->getLocale(), 'city_id' => $city->id]) }}">{{ __('applications.drafts') }}</a></div>@endforeach
    </section>
    @endif
</x-layouts.app>
