@php($ar = app()->isLocale('ar'))
<x-layouts.app :title="__('reports.title')" :operational="true" :selected-cities="$selectedCities">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('reports.title') }}</h1><p class="page-description">{{ __('reports.intro') }}</p></div><span class="version-tag">{{ __('projects.read_only_short') }}</span></div>
    <section class="card">
        @if($cities->isEmpty())
            <div class="results-empty"><span aria-hidden="true">▤</span><h2>{{ __('projects.no_access') }}</h2><p>{{ __('projects.no_cities_note') }}</p></div>
        @else
            @include('reports.filters', ['action' => route('reports.statistics', ['locale' => app()->getLocale()]), 'search' => null])
        @endif
    </section>
    @if($cities->isNotEmpty())
    @php($exportQuery = array_filter(['locale' => app()->getLocale(), 'cities' => $selectedCities] + $filters, fn ($v) => $v !== '' && $v !== []))
    <p class="field-hint"><a class="record-link" href="{{ route('reports.statistics.export', $exportQuery + ['format' => 'xlsx']) }}">{{ __('reports.export_stats_xlsx') }}</a> · <a class="record-link" href="{{ route('reports.statistics.export', $exportQuery + ['format' => 'csv']) }}">{{ __('reports.export_csv') }}</a></p>
    <section class="card" aria-labelledby="coverage-title"><h2 id="coverage-title">{{ __('reports.coverage') }}</h2><p class="field-hint">{{ __('reports.coverage_note') }}</p>
        <x-ui.table :caption="__('reports.coverage')"><thead><tr><th scope="col">{{ __('projects.city') }}</th><th scope="col">{{ __('reports.coverage_status') }}</th><th scope="col">{{ __('reports.records') }}</th></tr></thead><tbody>
        @foreach($statistics['coverage'] as $city)<tr><td>{{ $ar ? $city->name_ar : $city->name_en }}</td><td>@if(! $city->included){{ __('reports.not_included') }}@elseif($city->count === 0){{ __('reports.no_records') }}@else{{ __('reports.included') }}@endif</td><td><bdi>{{ $city->included ? number_format($city->count) : '—' }}</bdi></td></tr>@endforeach
        <tr><th scope="row">{{ __('reports.total') }}</th><td></td><td><strong><bdi>{{ number_format($statistics['total']) }}</bdi></strong></td></tr>
        </tbody></x-ui.table>
    </section>
    @if($statistics['total'] === 0)
        <section class="card"><div class="results-empty"><span aria-hidden="true">▤</span><h2>{{ __('reports.empty') }}</h2><p>{{ __('reports.empty_note') }}</p></div></section>
    @else
    <section class="card" aria-labelledby="state-title"><h2 id="state-title">{{ __('reports.by_state') }}</h2>
        <x-ui.table :caption="__('reports.by_state')"><thead><tr><th scope="col">{{ __('reports.state') }}</th>@foreach($statistics['cities'] as $cityId)@php($cityRow = $statistics['coverage']->firstWhere('id', $cityId))<th scope="col">{{ $ar ? $cityRow->name_ar : $cityRow->name_en }}</th>@endforeach<th scope="col">{{ __('reports.total') }}</th><th scope="col">{{ __('reports.share') }}</th></tr></thead><tbody>
        @foreach($statistics['byState'] as $name => $count)@php($name = (string) $name)@php($percent = round($count * 100 / $statistics['total']))<tr><th scope="row">@if($name === ''){{ __('reports.unrecorded') }}@else{{ $ar ? $statistics['labels'][$name]['ar'] : $name }}@endif</th>@foreach($statistics['cities'] as $cityId)<td><bdi>{{ number_format($statistics['matrix'][$name][$cityId] ?? 0) }}</bdi></td>@endforeach<td><strong><bdi>{{ number_format($count) }}</bdi></strong></td><td><meter min="0" max="{{ $statistics['total'] }}" value="{{ $count }}" aria-label="{{ $percent }}%">{{ $percent }}%</meter> <bdi>{{ $percent }}%</bdi></td></tr>@endforeach
        </tbody></x-ui.table>
        @if($statistics['unrecordedState'] > 0)<p class="field-hint">{{ __('reports.unrecorded_note', ['count' => number_format($statistics['unrecordedState'])]) }}</p>@endif
    </section>
    <section class="card" aria-labelledby="stage-title"><h2 id="stage-title">{{ __('reports.by_stage') }}</h2>
        <x-ui.table :caption="__('reports.by_stage')"><thead><tr><th scope="col">{{ __('reports.stage') }}</th><th scope="col">{{ __('reports.records') }}</th></tr></thead><tbody>
        @foreach(['zero', 'project'] as $stage)<tr><th scope="row">{{ __('applications.states.'.$stage) }}</th><td><bdi>{{ number_format($statistics['byStage'][$stage]) }}</bdi></td></tr>@endforeach
        @if($statistics['byStage']['other'] > 0)<tr><th scope="row">{{ __('reports.unrecorded') }}</th><td><bdi>{{ number_format($statistics['byStage']['other']) }}</bdi></td></tr>@endif
        </tbody></x-ui.table>
    </section>
    <section class="card" aria-labelledby="year-title"><h2 id="year-title">{{ __('reports.by_year') }}</h2>
        <x-ui.table :caption="__('reports.by_year')"><thead><tr><th scope="col">{{ __('reports.year') }}</th><th scope="col">{{ __('reports.records') }}</th><th scope="col">{{ __('reports.share') }}</th></tr></thead><tbody>
        @foreach($statistics['byYear'] as $year => $count)@php($percent = round($count * 100 / $statistics['total']))<tr><th scope="row"><bdi>{{ $year }}</bdi></th><td><bdi>{{ number_format($count) }}</bdi></td><td><meter min="0" max="{{ $statistics['total'] }}" value="{{ $count }}" aria-label="{{ $percent }}%">{{ $percent }}%</meter></td></tr>@endforeach
        @if($statistics['undated'] > 0)<tr><th scope="row">{{ __('reports.undated') }}</th><td><bdi>{{ number_format($statistics['undated']) }}</bdi></td><td></td></tr>@endif
        </tbody></x-ui.table>
    </section>
    @endif
    @endif
</x-layouts.app>
