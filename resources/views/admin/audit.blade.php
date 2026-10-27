@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $query = array_filter(['locale' => $locale, 'cities' => $selectedCities] + collect($filters)->except(['cities', 'format'])->all(), fn ($v) => $v !== '' && $v !== null && $v !== []);
@endphp
<x-layouts.app :title="__('audit.title')" :operational="true" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('audit.title') }}</h1><p class="page-description">{{ __('audit.intro') }}</p></div><span class="version-note">{{ __('search.read_only') }}</span></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('admin.audit', ['locale' => $locale]) }}">
            <div class="field"><label for="from">{{ __('audit.from') }}</label><input class="input" id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div class="field"><label for="to">{{ __('audit.to') }}</label><input class="input" id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <div class="field"><label for="event">{{ __('audit.event') }}</label><select class="input" id="event" name="event"><option value="">{{ __('reports.any') }}</option>
                @foreach($events as $name)<option value="{{ $name }}" @selected(($filters['event'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="outcome">{{ __('audit.outcome') }}</label><select class="input" id="outcome" name="outcome"><option value="">{{ __('reports.any') }}</option>
                @foreach(['succeeded', 'denied', 'failed'] as $name)<option value="{{ $name }}" @selected(($filters['outcome'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="actor">{{ __('audit.actor') }}</label><input class="input" id="actor" name="actor" maxlength="100" value="{{ $filters['actor'] ?? '' }}"></div>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))><span>{{ $ar ? $city->name_ar : $city->name_en }}</span></label>@endforeach
            </div><p class="field-hint">{{ __('projects.all_cities_hint') }}</p></fieldset>
            <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button><a class="button button-secondary" href="{{ route('admin.audit', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a></div>
        </form>
        @if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('reports.filter_error') }}</div>@endif
        <p><a class="record-link" href="{{ route('admin.audit.export', $query + ['format' => 'xlsx']) }}">{{ __('audit.export_xlsx') }}</a> · <a class="record-link" href="{{ route('admin.audit.export', $query + ['format' => 'csv']) }}">{{ __('reports.export_csv') }}</a></p>
        <p class="field-hint">{{ __('search.count', ['count' => number_format($rows->total())]) }} {{ __('audit.scope_note') }}</p>
        <x-ui.table :caption="__('audit.title')"><thead><tr>@foreach(\App\Services\Staff\AuditLogService::headings() as $heading)<th scope="col">{{ $heading }}</th>@endforeach</tr></thead><tbody>
        @forelse($rows as $line)<tr>@foreach($line as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty<tr><td colspan="9">{{ __('projects.no_results') }}</td></tr>@endforelse
        </tbody></x-ui.table>
        <div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
