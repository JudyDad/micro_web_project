@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $selectedCities = array_map('intval', $filters['cities'] ?? []);
    $taskFilters = $filters['tasks'] ?? [];
    $span = count($columns) + 6 + ($kind === 'f' ? 1 : 0);
@endphp
<x-layouts.app :title="__('staff.tasks.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('staff.tasks.title') }}</h1><p class="page-description">{{ __('staff.tasks.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('tasks.board', ['locale' => $locale]) }}">
            <div class="field"><label for="kind">{{ __('staff.tasks.kind') }}</label><select class="input" id="kind" name="kind">
                <option value="f" @selected($kind === 'f')>{{ __('staff.tasks.funded') }}</option><option value="a" @selected($kind === 'a')>{{ __('staff.tasks.applications') }}</option></select></div>
            <div class="field"><label for="q">{{ __('staff.tasks.search') }}</label><input class="input" id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}"></div>
            <div class="field"><label for="state">{{ __('staff.tasks.state') }}</label><select class="input" id="state" name="state"><option value="">{{ __('staff.tasks.any') }}</option>
                @foreach($options['states'] as $legacy => $name)<option value="{{ $legacy }}" @selected((string) ($filters['state'] ?? '') === (string) $legacy)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="donor">{{ __('staff.tasks.donor') }}</label><select class="input" id="donor" name="donor"><option value="">{{ __('staff.tasks.any') }}</option>
                @foreach($options['donors'] as $name)<option value="{{ $name }}" @selected(($filters['donor'] ?? '') === (string) $name)>{{ $name }}</option>@endforeach</select></div>
            <div class="field"><label for="type">{{ __('staff.tasks.type') }}</label><select class="input" id="type" name="type"><option value="">{{ __('staff.tasks.any') }}</option>
                @foreach($options['types'] as $name)<option value="{{ $name }}" @selected(($filters['type'] ?? '') === (string) $name)>{{ $name }}</option>@endforeach</select></div>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ $ar ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <details class="task-filters"><summary>{{ __('staff.tasks.task_filters') }}</summary><div class="city-filter-options">
                @foreach($columns as $legacy => $label)<div class="field"><label for="task-{{ $legacy }}">{{ $label }}</label><select class="input" id="task-{{ $legacy }}" name="tasks[{{ $legacy }}]"><option value="">{{ __('staff.tasks.any') }}</option>
                    <option value="1" @selected(($taskFilters[$legacy] ?? '') === '1')>{{ __('staff.tasks.done') }}</option><option value="0" @selected(($taskFilters[$legacy] ?? '') === '0')>{{ __('staff.tasks.not_done') }}</option></select></div>@endforeach
            </div></details>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('tasks.board', ['locale' => $locale, 'kind' => $kind]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <p><a class="record-link" href="{{ route('tasks.board.export', ['locale' => $locale, 'kind' => $kind, 'format' => 'xlsx'] + request()->except(['format', 'kind'])) }}">{{ __('staff.tasks.export_xlsx') }}</a> · <a class="record-link" href="{{ route('tasks.board.export', ['locale' => $locale, 'kind' => $kind, 'format' => 'csv'] + request()->except(['format', 'kind'])) }}">{{ __('reports.export_csv') }}</a></p>
        <p><small>{{ __('staff.tasks.rule') }}</small></p>
        <x-ui.table :caption="__('staff.tasks.title')"><thead><tr><th>{{ __('staff.tasks.project') }}</th><th>{{ __('staff.tasks.city') }}</th><th>{{ __('staff.tasks.beneficiary') }}</th><th>{{ __('staff.tasks.phone') }}</th><th>{{ __('staff.tasks.name') }}</th><th>{{ __('staff.tasks.state') }} / {{ __('staff.tasks.donor') }} / {{ __('staff.tasks.type') }}</th>
            @if($kind === 'f')<th>{{ __('staff.tasks.loan_date') }}</th>@endif
            @foreach($columns as $label)<th class="task-head">{{ $label }}</th>@endforeach</tr></thead><tbody>
        @forelse($rows as $row)
        <tr id="project-{{ $row->id }}"><td><a class="record-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $row->id]) }}"><bdi>{{ $row->c_MP_ID }}</bdi></a></td><td>{{ $ar ? $row->city_name_ar : $row->city_name_en }}</td>
            <td>{{ implode('، ', $row->people_names) }}</td><td><bdi>{{ $row->mobile }}</bdi></td><td>{{ $row->c_MP_Name }}</td>
            <td>{{ $row->state_name }}<br><small>{{ $row->donor_name }} · {{ $row->type_name }}</small></td>
            @if($kind === 'f')<td>{{ $row->loan_day }}</td>@endif
            @foreach($columns as $legacy => $label)
                @php($cell = $row->cells[$legacy])
                <td class="task-cell">@if(! $cell['assigned'])<span class="task-none" title="{{ __('staff.tasks.not_assigned') }}" aria-label="{{ __('staff.tasks.not_assigned') }}">–</span>
                    @elseif($cell['manageable'])<form method="POST" action="{{ route('tasks.toggle', ['locale' => $locale]) }}">@csrf
                        <input type="hidden" name="project" value="{{ $row->id }}"><input type="hidden" name="task" value="{{ $legacy }}"><input type="hidden" name="expected" value="{{ $cell['done'] ? 1 : 0 }}"><input type="hidden" name="done" value="{{ $cell['done'] ? 0 : 1 }}">
                        <button class="task-toggle {{ $cell['done'] ? 'task-done' : '' }}" type="submit" aria-label="{{ $label }}: {{ $cell['done'] ? __('staff.tasks.mark_undone') : __('staff.tasks.mark_done') }}" title="{{ $cell['done'] ? __('staff.tasks.mark_undone') : __('staff.tasks.mark_done') }}">{{ $cell['done'] ? '✓' : '○' }}</button></form>
                    @else<span class="{{ $cell['done'] ? 'task-done' : 'task-open' }}" role="img" aria-label="{{ $label }}: {{ $cell['done'] ? __('staff.tasks.done') : __('staff.tasks.not_done') }}">{{ $cell['done'] ? '✓' : '○' }}</span>@endif</td>
            @endforeach
        </tr>
        @empty<tr><td colspan="{{ $span }}">{{ __('staff.tasks.empty') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
    <section class="card">
        <h2>{{ __('staff.tasks.responsible') }}</h2><p><small>{{ __('staff.tasks.responsible_note') }}</small></p>
        @foreach($cities->whereIn('id', $shown) as $city)
            <h3>{{ $ar ? $city->name_ar : $city->name_en }}</h3>
            <ul>@foreach($columns as $legacy => $label)<li>{{ $label }}: @if(! empty($responsible[$city->id][$legacy])){{ implode('، ', $responsible[$city->id][$legacy]) }}@else<span>{{ __('staff.tasks.responsible_none') }}</span>@endif</li>@endforeach</ul>
        @endforeach
    </section>
</x-layouts.app>
