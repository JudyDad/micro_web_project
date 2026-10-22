@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $selectedCities = array_map('intval', $filters['cities'] ?? []);
@endphp
<x-layouts.app :title="__('field.meeting.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('field.meeting.title') }}</h1><p class="page-description">{{ __('field.meeting.intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('meetings.index', ['locale' => $locale]) }}">
            <div class="field"><label for="year">{{ __('field.schedule.year') }}</label><select class="input" id="year" name="year"><option value="">{{ __('field.schedule.all_years') }}</option>
                @for($y = 2018; $y <= now()->year + 2; $y++)<option value="{{ $y }}" @selected((string) ($filters['year'] ?? '') === (string) $y)>{{ $y }}</option>@endfor</select></div>
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ $ar ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('meetings.index', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        @foreach($creatable as $city)<a class="button button-secondary" href="{{ route('meetings.create', ['locale' => $locale, 'city' => $city->id]) }}">{{ __('field.meeting.new') }} · {{ $ar ? $city->name_ar : $city->name_en }}</a> @endforeach
        <x-ui.table :caption="__('field.meeting.title')"><thead><tr><th>{{ __('field.meeting.date') }}</th><th>{{ __('projects.city') }}</th><th>{{ __('field.meeting.career') }}</th><th>{{ __('field.meeting.place') }}</th><th>{{ __('field.meeting.attendance') }}</th></tr></thead><tbody>
        @forelse($rows as $row)
        <tr><td><a class="record-link" href="{{ route('meetings.show', ['locale' => $locale, 'meeting' => $row->id]) }}"><bdi>{{ substr((string) $row->c_MeetingDate, 0, 10) }}</bdi></a></td><td>{{ $ar ? $row->city_name_ar : $row->city_name_en }}</td><td>{{ $row->career_name }}</td><td>{{ $row->c_Place }}</td><td><bdi>{{ $row->c_AttendanceCount }} / {{ $row->c_InvitedCount }}</bdi></td></tr>
        @empty<tr><td colspan="5">{{ __('field.meeting.empty') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
    @if($deleted->isNotEmpty())<section class="card people-form"><h2>{{ __('field.deleted_records') }}</h2>
        @foreach($deleted as $gone)
        <form method="POST" action="{{ route('meetings.restore', ['locale' => $locale, 'meeting' => $gone->id]) }}">@csrf<bdi>{{ substr((string) $gone->c_MeetingDate, 0, 10) }}</bdi> · {{ $gone->c_Place }} <x-ui.button type="submit">{{ __('field.restore') }}</x-ui.button></form>
        @endforeach
    </section>@endif
</x-layouts.app>
