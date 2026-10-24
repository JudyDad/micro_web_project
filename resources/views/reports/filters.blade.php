@props(['action', 'cities', 'selectedCities', 'filters', 'stateOptions', 'search' => null])
<form class="search-form project-filters" method="GET" action="{{ $action }}">
    @if($search !== null)<div class="field"><label for="search">{{ __('applications.search') }}</label><input class="input" id="search" type="search" name="search" value="{{ $search }}" maxlength="200"></div>@endif
    <div class="field"><label for="stage">{{ __('reports.stage') }}</label><select class="input" id="stage" name="stage"><option value="">{{ __('reports.any') }}</option>@foreach(['zero', 'project'] as $stage)<option value="{{ $stage }}" @selected($filters['stage'] === $stage)>{{ __('applications.states.'.$stage) }}</option>@endforeach</select></div>
    <div class="field"><label for="state">{{ __('reports.state') }}</label><select class="input" id="state" name="state"><option value="">{{ __('reports.any') }}</option>@foreach($stateOptions as $name => $option)<option value="{{ $name }}" @selected($filters['state'] === (string) $name)>{{ app()->isLocale('ar') ? $option['ar'] : $option['en'] }}</option>@endforeach</select></div>
    <div class="field"><label for="date_from">{{ __('reports.date_from') }}</label><input class="input" id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div>
    <div class="field"><label for="date_to">{{ __('reports.date_to') }}</label><input class="input" id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div>
    <fieldset class="city-filter" aria-describedby="city-filter-hint"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
        @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))><span>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</span></label>@endforeach
    </div><p id="city-filter-hint" class="field-hint">{{ __('projects.all_cities_hint') }}</p></fieldset>
    <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button><a class="button button-secondary" href="{{ $action }}">{{ __('projects.clear_filters') }}</a></div>
</form>
@if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('reports.filter_error') }}</div>@endif
