@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $selectedCities = array_map('intval', $filters['cities'] ?? []);
    $selectedMonths = array_map('intval', $filters['months'] ?? []);
@endphp
<x-layouts.app :title="__('field.schedule.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('field.schedule.title') }}</h1><p class="page-description">{{ __('field.schedule.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('visits.schedule', ['locale' => $locale]) }}">
            <div class="field"><label for="year">{{ __('field.schedule.year') }}</label><select class="input" id="year" name="year"><option value="">{{ __('field.schedule.all_years') }}</option>
                @for($y = 2018; $y <= now()->year + 2; $y++)<option value="{{ $y }}" @selected((string) ($filters['year'] ?? '') === (string) $y)>{{ $y }}</option>@endfor</select></div>
            <div class="field"><label for="kind">{{ __('field.schedule.kind') }}</label><select class="input" id="kind" name="kind"><option value="">{{ __('field.schedule.all_kinds') }}</option>
                <option value="V" @selected(($filters['kind'] ?? '') === 'V')>{{ __('field.schedule.vehicles') }}</option><option value="O" @selected(($filters['kind'] ?? '') === 'O')>{{ __('field.schedule.others') }}</option></select></div>
            <div class="field"><label for="number">{{ __('field.schedule.number') }}</label><select class="input" id="number" name="number"><option value="">{{ __('field.schedule.any_number') }}</option>
                @foreach(['Opening', 'M1', 'M2', 'M3', 'M4', 'M5', 'Closing'] as $number)<option value="{{ $number }}" @selected(($filters['number'] ?? '') === $number)>{{ $number }}</option>@endforeach</select></div>
            <div class="field"><label for="seen">{{ __('field.schedule.seen') }}</label><select class="input" id="seen" name="seen"><option value="">{{ __('field.schedule.any') }}</option>
                <option value="1" @selected(($filters['seen'] ?? '') === '1')>{{ __('field.schedule.seen_yes') }}</option><option value="0" @selected(($filters['seen'] ?? '') === '0')>{{ __('field.schedule.seen_no') }}</option></select></div>
            <fieldset class="city-filter"><legend>{{ __('field.schedule.months') }}</legend><div class="city-filter-options">
                @for($m = 1; $m <= 12; $m++)<label class="city-filter-option"><input type="checkbox" name="months[]" value="{{ $m }}" @checked(in_array($m, $selectedMonths, true))>{{ $m }}</label>@endfor
            </div></fieldset>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ $ar ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('visits.schedule', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <p><a class="record-link" href="{{ route('visits.schedule.export', ['locale' => $locale, 'format' => 'xlsx'] + request()->except('format')) }}">{{ __('field.schedule.export_xlsx') }}</a> · <a class="record-link" href="{{ route('visits.schedule.export', ['locale' => $locale, 'format' => 'csv'] + request()->except('format')) }}">{{ __('reports.export_csv') }}</a></p>
        <x-ui.table :caption="__('field.schedule.title')"><thead><tr><th>{{ __('field.schedule.project') }}</th><th>{{ __('field.schedule.city') }}</th><th>{{ __('field.schedule.beneficiary') }}</th><th>{{ __('field.schedule.visit') }}</th><th>{{ __('field.schedule.date') }}</th><th>{{ __('field.schedule.status') }}</th><th>{{ __('field.schedule.note') }}</th><th>{{ __('field.schedule.actions') }}</th></tr></thead><tbody>
        @forelse($rows as $row)
        @php($mine = in_array((int) $row->city_id, $manageCities, true))
        <tr><td>@if($row->project_id)<a class="record-link" href="{{ route('visits.hub', ['locale' => $locale, 'application' => $row->project_id]) }}"><bdi>{{ $row->project_number }}</bdi></a> {{ $row->project_name }}@else<bdi>{{ $row->project_number }}</bdi>@endif</td><td>{{ $ar ? $row->city_name_ar : $row->city_name_en }}</td><td>{{ $row->title }}</td><td><bdi>{{ $row->body }}</bdi></td>
            <td>{{ substr((string) $row->notice_date, 0, 10) }}@if($row->original_date)<br><small>{{ __('field.schedule.original') }}: {{ substr((string) $row->original_date, 0, 10) }}@if($row->change_reason) — {{ $row->change_reason }}@endif</small>@endif</td>
            <td>{{ $row->visit_status === 'done' ? __('field.schedule.done') : ($row->visit_status === 'na' ? __('field.schedule.na') : __('field.schedule.not_found')) }}</td>
            <td>@if($mine)<form method="POST" action="{{ route('visits.schedule.note', ['locale' => $locale, 'notice' => $row->id]) }}">@csrf<label class="sr-only" for="note-{{ $row->id }}">{{ __('field.schedule.note') }}</label><input class="input" id="note-{{ $row->id }}" name="note" maxlength="65535" value="{{ $row->note }}"><x-ui.button type="submit" variant="secondary">{{ __('field.save') }}</x-ui.button></form>@else{{ $row->note }}@endif</td>
            <td>@if($mine)<form method="POST" action="{{ route('visits.schedule.seen', ['locale' => $locale, 'notice' => $row->id]) }}">@csrf<input type="hidden" name="seen" value="{{ (int) $row->seen === 1 ? 0 : 1 }}"><x-ui.button type="submit" variant="secondary">{{ (int) $row->seen === 1 ? __('field.schedule.restore') : __('field.schedule.hide') }}</x-ui.button></form>
                @if($row->kind === 'visit' && (int) $row->seen === 0)<details><summary>{{ __('field.schedule.reschedule') }}</summary>
                    <form method="POST" action="{{ route('visits.schedule.reschedule', ['locale' => $locale, 'notice' => $row->id]) }}">@csrf
                        <div class="field"><label for="date-{{ $row->id }}">{{ __('field.schedule.new_date') }}</label><input class="input" id="date-{{ $row->id }}" name="date" type="date" required value="{{ substr((string) $row->notice_date, 0, 10) }}"></div>
                        <div class="field"><label for="reason-{{ $row->id }}">{{ __('field.schedule.reason') }}</label><input class="input" id="reason-{{ $row->id }}" name="reason" maxlength="1000" required></div>
                        <label class="city-filter-option"><input type="checkbox" name="shift" value="1">{{ __('field.schedule.shift_later') }}</label>
                        <x-ui.button type="submit">{{ __('field.schedule.reschedule') }}</x-ui.button>
                    </form></details>@endif
            @endif</td></tr>
        @empty<tr><td colspan="8">{{ __('field.schedule.empty') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
