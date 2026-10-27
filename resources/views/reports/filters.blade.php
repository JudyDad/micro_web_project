@props(['action', 'cities', 'selectedCities', 'filters', 'stateOptions', 'search' => null])
@php
    $ar = app()->isLocale('ar');
    $permitted = $cities->pluck('id')->map(fn ($id) => (int) $id)->all();
    $scope = $selectedCities === [] ? $permitted : array_values(array_intersect($selectedCities, $permitted));
    $multi = ['state' => $stateOptions] + app(\App\Services\Reports\ApplicationFilters::class)->options($scope);
    $order = ['state', 'fund_type', 'project_type', 'sub_type', 'partnership', 'donor', 'donor_group', 'category', 'sub_category', 'career', 'marital_status', 'parish', 'home_street', 'project_street', 'age', 'gender'];
@endphp
<form class="search-form project-filters" method="GET" action="{{ $action }}">
    @if($search !== null)<div class="field"><label for="search">{{ __('applications.search') }}</label><input class="input" id="search" type="search" name="search" value="{{ $search }}" maxlength="200"></div>@endif
    <div class="field"><label for="stage">{{ __('reports.stage') }}</label><select class="input" id="stage" name="stage"><option value="">{{ __('reports.any') }}</option>@foreach(['zero', 'project'] as $stage)<option value="{{ $stage }}" @selected($filters['stage'] === $stage)>{{ __('applications.states.'.$stage) }}</option>@endforeach</select></div>
    @foreach($order as $key)
        @php $selected = (array) ($filters[$key] ?? []); $options = $multi[$key] ?? []; @endphp
        <div class="field multi-field">
            <details class="multi" @if($selected !== []) open @endif>
                <summary class="input"><span>{{ __('reports.multi.'.$key) }}</span>@if($selected !== [])<b class="multi-count">{{ count($selected) }}</b>@endif</summary>
                <div class="multi-options">
                    @forelse($options as $name => $option)<label class="multi-option"><input type="checkbox" name="{{ $key }}[]" value="{{ $name }}" @checked(in_array((string) $name, $selected, true))><span>{{ $ar ? $option['ar'] : $option['en'] }}</span></label>@empty<span class="field-hint">—</span>@endforelse
                </div>
            </details>
        </div>
    @endforeach
    <div class="field"><label for="date_from">{{ __('reports.date_from') }}</label><input class="input" id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div>
    <div class="field"><label for="date_to">{{ __('reports.date_to') }}</label><input class="input" id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div>
    <div class="field"><label for="fund_from">{{ __('reports.fund_from') }}</label><input class="input" id="fund_from" type="date" name="fund_from" value="{{ $filters['fund_from'] ?? '' }}"></div>
    <div class="field"><label for="fund_to">{{ __('reports.fund_to') }}</label><input class="input" id="fund_to" type="date" name="fund_to" value="{{ $filters['fund_to'] ?? '' }}"></div>
    <div class="field"><label for="amount_from">{{ __('reports.amount_from') }}</label><input class="input" id="amount_from" type="number" min="0" step="any" name="amount_from" value="{{ $filters['amount_from'] ?? '' }}"></div>
    <div class="field"><label for="amount_to">{{ __('reports.amount_to') }}</label><input class="input" id="amount_to" type="number" min="0" step="any" name="amount_to" value="{{ $filters['amount_to'] ?? '' }}"></div>
    <div class="field"><label for="fund_amount_from">{{ __('reports.fund_amount_from') }}</label><input class="input" id="fund_amount_from" type="number" min="0" step="any" name="fund_amount_from" value="{{ $filters['fund_amount_from'] ?? '' }}"></div>
    <div class="field"><label for="fund_amount_to">{{ __('reports.fund_amount_to') }}</label><input class="input" id="fund_amount_to" type="number" min="0" step="any" name="fund_amount_to" value="{{ $filters['fund_amount_to'] ?? '' }}"></div>
    <fieldset class="city-filter" aria-describedby="city-filter-hint"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
        @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))><span>{{ app()->isLocale('ar') ? $city->name_ar : $city->name_en }}</span></label>@endforeach
    </div><p id="city-filter-hint" class="field-hint">{{ __('projects.all_cities_hint') }}</p></fieldset>
    <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button><a class="button button-secondary" href="{{ $action }}">{{ __('projects.clear_filters') }}</a></div>
</form>
@if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('reports.filter_error') }}</div>@endif
