@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $selectedCities = array_map('intval', $filters['cities'] ?? []);
@endphp
<x-layouts.app :title="__('staff.notifications.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('staff.notifications.title') }}</h1><p class="page-description">{{ __('staff.notifications.intro') }}</p></div>
        @if(app(\App\Services\Staff\NotificationInboxService::class)->sendCities(auth()->user())->isNotEmpty())<a class="button button-secondary" href="{{ route('notifications.create', ['locale' => $locale]) }}">{{ __('staff.notifications.send_title') }}</a>@endif</div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('notifications.index', ['locale' => $locale]) }}">
            <fieldset class="city-filter"><legend>{{ __('projects.city_filter') }}</legend><div class="city-filter-options">
                @foreach($cities as $city)<label class="city-filter-option"><input type="checkbox" name="cities[]" value="{{ $city->id }}" @checked(in_array((int) $city->id, $selectedCities, true))>{{ $ar ? $city->name_ar : $city->name_en }}</label>@endforeach
            </div></fieldset>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('notifications.index', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <form method="POST" action="{{ route('notifications.seen', ['locale' => $locale]) }}">@csrf
        <x-ui.table :caption="__('staff.notifications.title')"><thead><tr><th>{{ __('staff.notifications.mark') }}</th><th>{{ __('staff.notifications.project') }}</th><th>{{ __('staff.notifications.city') }}</th><th>{{ __('staff.notifications.about') }}</th><th>{{ __('staff.notifications.body') }}</th><th>{{ __('staff.notifications.date') }}</th><th>{{ __('staff.notifications.sent_by') }}</th><th>{{ __('staff.notifications.open') }}</th></tr></thead><tbody>
        @forelse($rows as $row)
        <tr><td><input type="checkbox" name="ids[]" value="{{ $row->id }}" aria-label="{{ __('staff.notifications.mark') }}: {{ $row->body }}" style="width:24px;height:24px"></td>
            <td>@if($row->project_id)<a class="record-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $row->project_id]) }}"><bdi>{{ $row->project_number }}</bdi></a>@else<bdi>{{ $row->project_number }}</bdi>@endif</td>
            <td>{{ $ar ? $row->city_name_ar : $row->city_name_en }}</td><td>{{ $row->title }}</td><td><bdi>{{ $row->amount ?? $row->body }}</bdi></td><td>{{ substr((string) $row->notice_date, 0, 10) }}</td><td>{{ $row->sender }}</td>
            <td>@if($row->project_id && $row->links['finance'])<a href="{{ route('finance.show', ['locale' => $locale, 'application' => $row->project_id]) }}">{{ __('staff.notifications.finance') }}</a> @endif
                @if($row->project_id && $row->links['visits'])<a href="{{ route('visits.hub', ['locale' => $locale, 'application' => $row->project_id]) }}">{{ __('staff.notifications.visits') }}</a>@endif</td></tr>
        @empty<tr><td colspan="8">{{ __('staff.notifications.empty') }}</td></tr>@endforelse
        </tbody></x-ui.table>
        @if($rows->count())<div class="filter-actions"><x-ui.button type="submit">{{ __('staff.notifications.mark_seen') }}</x-ui.button></div>@endif
        </form>
        <div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
