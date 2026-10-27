@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $query = array_filter(['locale' => $locale, 'mode' => $mode->key(), 'cities' => $selectedCities] + collect($filters)->except(['mode', 'cities', 'format'])->all(), fn ($v) => $v !== '' && $v !== null && $v !== []);
    $uses = fn (string $name) => in_array($name, $mode->filters(), true);
@endphp
<x-layouts.app :title="__('search.title')" :operational="true" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('search.title') }}</h1><p class="page-description">{{ __('search.intro') }}</p></div><span class="version-note">{{ __('search.read_only') }}</span></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('search.reports', ['locale' => $locale]) }}">
            <div class="field"><label for="mode">{{ __('search.mode') }}</label><select class="input" id="mode" name="mode">
                @foreach($modes as $key)<option value="{{ $key }}" @selected($key === $mode->key())>{{ __('search.modes.'.$key) }}</option>@endforeach</select></div>
            @if($uses('q'))<div class="field"><label for="q">{{ __('search.q') }}</label><input class="input" id="q" name="q" maxlength="100" value="{{ $filters['q'] ?? '' }}"></div>@endif
            @if($uses('state'))
            <div class="field"><label for="state">{{ __('search.state') }}</label><select class="input" id="state" name="state"><option value="">{{ __('reports.any') }}</option>
                @foreach($options['states'] as $name)<option value="{{ $name }}" @selected(($filters['state'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="donor">{{ __('search.donor') }}</label><select class="input" id="donor" name="donor"><option value="">{{ __('reports.any') }}</option>
                @foreach($options['donors'] as $name)<option value="{{ $name }}" @selected(($filters['donor'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="group">{{ __('search.group') }}</label><select class="input" id="group" name="group"><option value="">{{ __('reports.any') }}</option>
                @foreach($options['groups'] as $name)<option value="{{ $name }}" @selected(($filters['group'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            @endif
            @if($uses('request'))
            <div class="field"><label for="request_from">{{ __('search.request_from') }}</label><input class="input" id="request_from" type="date" name="request_from" value="{{ $filters['request_from'] ?? '' }}"></div>
            <div class="field"><label for="request_to">{{ __('search.request_to') }}</label><input class="input" id="request_to" type="date" name="request_to" value="{{ $filters['request_to'] ?? '' }}"></div>
            @endif
            @if($uses('loan'))
            <div class="field"><label for="loan_from">{{ __('search.loan_from') }}</label><input class="input" id="loan_from" type="date" name="loan_from" value="{{ $filters['loan_from'] ?? '' }}"></div>
            <div class="field"><label for="loan_to">{{ __('search.loan_to') }}</label><input class="input" id="loan_to" type="date" name="loan_to" value="{{ $filters['loan_to'] ?? '' }}"></div>
            @endif
            @if($uses('classify'))
            @foreach(['fund', 'type', 'subtype', 'category', 'subcategory', 'career'] as $list)
            @if(! empty($options['classification'][$list]))
            <div class="field"><label for="{{ $list }}">{{ __('search.'.$list) }}</label><select class="input" id="{{ $list }}" name="{{ $list }}[]" multiple size="4" aria-describedby="multi-hint">
                @foreach($options['classification'][$list] as $name)<option value="{{ $name }}" @selected(in_array($name, (array) ($filters[$list] ?? []), true))>{{ $name }}</option>@endforeach</select></div>
            @endif
            @endforeach
            <div class="field"><label for="partnership">{{ __('search.partnership') }}</label><select class="input" id="partnership" name="partnership"><option value="">{{ __('reports.any') }}</option>
                @foreach(['individual', 'partnership'] as $choice)<option value="{{ $choice }}" @selected(($filters['partnership'] ?? '') === $choice)>{{ __('search.partnership_'.$choice) }}</option>@endforeach</select></div>
            <p id="multi-hint" class="field-hint">{{ __('search.multi_hint') }}</p>
            @endif
            @if($uses('funded'))
            <div class="field"><label for="funded_from">{{ __('search.funded_from') }}</label><input class="input" id="funded_from" type="number" min="0" step="any" name="funded_from" value="{{ $filters['funded_from'] ?? '' }}"></div>
            <div class="field"><label for="funded_to">{{ __('search.funded_to') }}</label><input class="input" id="funded_to" type="number" min="0" step="any" name="funded_to" value="{{ $filters['funded_to'] ?? '' }}"></div>
            @endif
            @if($uses('meeting'))
            <div class="field"><label for="meeting_from">{{ __('search.meeting_from') }}</label><input class="input" id="meeting_from" type="date" name="meeting_from" value="{{ $filters['meeting_from'] ?? '' }}"></div>
            <div class="field"><label for="meeting_to">{{ __('search.meeting_to') }}</label><input class="input" id="meeting_to" type="date" name="meeting_to" value="{{ $filters['meeting_to'] ?? '' }}"></div>
            @endif
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))><span>{{ $ar ? $city->name_ar : $city->name_en }}</span></label>@endforeach
            </div><p class="field-hint">{{ __('projects.all_cities_hint') }}</p></fieldset>
            <div class="filter-actions"><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button><a class="button button-secondary" href="{{ route('search.reports', ['locale' => $locale, 'mode' => $mode->key()]) }}">{{ __('projects.clear_filters') }}</a></div>
        </form>
        @if($errors->any())<div class="form-message form-message-error" role="alert">{{ __('reports.filter_error') }}</div>@endif
        <p><a class="record-link" href="{{ route('search.reports.export', $query + ['format' => 'xlsx']) }}">{{ __('search.export_xlsx') }}</a> · <a class="record-link" href="{{ route('search.reports.export', $query + ['format' => 'csv']) }}">{{ __('reports.export_csv') }}</a></p>
        <p class="field-hint">{{ __('search.count', ['count' => number_format($total)]) }} @if($mode->key() === 'loans') {{ __('search.loans_note') }} @endif</p>
        <x-ui.table :caption="__('search.modes.'.$mode->key())"><thead><tr>@foreach($mode->headings() as $heading)<th scope="col">{{ $heading }}</th>@endforeach</tr></thead><tbody>
        @forelse($rows as $line)<tr>@foreach($line as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty<tr><td colspan="{{ count($mode->headings()) }}">{{ __('projects.no_results') }}</td></tr>@endforelse
        </tbody></x-ui.table>
        <div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
