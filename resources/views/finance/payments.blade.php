@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $selectedCities = array_map('intval', $filters['cities'] ?? []);
    $selectedMonths = array_map('intval', $filters['months'] ?? []);
    $paidView = ($filters['state'] ?? 'pending') === 'paid';
@endphp
<x-layouts.app :title="__('finance.payments.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('finance.payments.title') }}</h1><p class="page-description">{{ __('finance.payments.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form" id="open-project">
        <h2>{{ __('finance.payments.open_project') }}</h2>
        <div class="people-fields">
            <x-ui.search-select id="open-project-search" name="project" :label="__('finance.payments.open_project_label')" :url="route('finance.projects.lookup', ['locale' => $locale])"
                :placeholder="__('finance.payments.open_project_placeholder')" :hint="__('finance.payments.open_project_hint')" :navigate="true" />
        </div>
    </section>
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('finance.payments', ['locale' => $locale]) }}">
            <div class="field"><label for="state">{{ __('finance.payments.view') }}</label><select class="input" id="state" name="state">
                <option value="pending" @selected(! $paidView)>{{ __('finance.payments.pending') }}</option><option value="paid" @selected($paidView)>{{ __('finance.payments.paid') }}</option></select></div>
            <div class="field"><label for="year">{{ __('finance.payments.year') }}</label><select class="input" id="year" name="year"><option value="">{{ __('finance.payments.all_years') }}</option>
                @for($y = 2018; $y <= now()->year + 2; $y++)<option value="{{ $y }}" @selected((string) ($filters['year'] ?? '') === (string) $y)>{{ $y }}</option>@endfor</select></div>
            <div class="field"><label for="group">{{ __('finance.donor.group') }}</label><select class="input" id="group" name="group"><option value="">{{ __('finance.payments.all_groups') }}</option>
                @foreach($groups as $name)<option value="{{ $name }}" @selected(($filters['group'] ?? '') === $name)>{{ $name }}</option>@endforeach</select></div>
            <fieldset class="city-filter"><legend>{{ __('finance.payments.months') }}</legend><div class="city-filter-options">
                @for($m = 1; $m <= 12; $m++)<label class="city-filter-option"><input type="checkbox" name="months[]" value="{{ $m }}" @checked(in_array($m, $selectedMonths, true))>{{ $m }}</label>@endfor
            </div></fieldset>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ $ar ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('finance.payments', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <p><a class="record-link" href="{{ route('finance.payments.export', ['locale' => $locale, 'format' => 'xlsx'] + request()->except('format')) }}">{{ __('reports.export_xlsx') }}</a> · <a class="record-link" href="{{ route('finance.payments.export', ['locale' => $locale, 'format' => 'csv'] + request()->except('format')) }}">{{ __('reports.export_csv') }}</a></p>
        <p><small>{{ __('finance.payments.currency_note') }}</small></p>
        <ul class="finance-totals" aria-label="{{ __('finance.payments.totals') }}">
            @forelse($totals as $total)<li><strong>{{ $total->currency ?? '—' }}</strong>: <bdi>{{ $total->total }}</bdi> ({{ __('finance.payments.count', ['count' => $total->payments]) }})</li>@empty<li>{{ __('projects.no_results') }}</li>@endforelse
        </ul>
        <x-ui.table :caption="__('finance.payments.title')"><thead><tr><th>{{ __('applications.fields.MP_ID') }}</th><th>{{ __('projects.city') }}</th><th>{{ __('applications.beneficiary') }}</th><th>{{ __('applications.partner') }}</th><th>{{ __('finance.loan.currency') }}</th><th>{{ __('finance.installments.amount') }}</th><th>{{ __('finance.installments.due') }}</th><th>{{ __('finance.installments.received') }}</th><th>{{ __('finance.donor.group') }}</th><th>{{ __('finance.installments.note') }}</th></tr></thead><tbody>
        @forelse($rows as $row)
        <tr><td><a class="record-link" href="{{ route('finance.show', ['locale' => $locale, 'application' => $row->project_id]) }}"><bdi>{{ $row->project_number }}</bdi></a></td><td>{{ $ar ? $row->city_name_ar : $row->city_name_en }}</td><td>{{ $row->beneficiary }}</td><td>{{ $row->partner }}</td><td>{{ $row->currency }}</td><td><bdi>{{ $row->amount }}</bdi></td><td>{{ substr((string) $row->c_Pay_DueDate, 0, 10) }}</td><td>{{ substr((string) $row->c_Pay_RecievedOnDate, 0, 10) }}</td><td>{{ $row->group_name }}</td><td>{{ $row->c_Pay_Note }}</td></tr>
        @empty<tr><td colspan="10">{{ __('projects.no_results') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
