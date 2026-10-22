@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('admin.title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('admin.title') }}</h1><p class="page-description">{{ __('admin.intro') }}</p></div>
        <div class="filter-actions"><a class="button button-primary" href="{{ route('admin.accounts.new', ['locale' => $locale]) }}">{{ __('admin.new') }}</a><a class="button button-secondary" href="{{ route('admin.staff', ['locale' => $locale]) }}">{{ __('admin.staff') }}</a></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('admin.accounts.index', ['locale' => $locale]) }}">
            <div class="field"><label for="q">{{ __('admin.search') }}</label><input class="input" id="q" name="q" type="search" maxlength="100" value="{{ $filters['q'] ?? '' }}"></div>
            <div class="field"><label for="city">{{ __('admin.city') }}</label><select class="input" id="city" name="city"><option value="">{{ __('admin.any') }}</option>
                @foreach($cities as $city)<option value="{{ $city->id }}" @selected((string) ($filters['city'] ?? '') === (string) $city->id)>{{ $ar ? $city->name_ar : $city->name_en }}</option>@endforeach</select></div>
            <div class="field"><label for="status">{{ __('admin.status') }}</label><select class="input" id="status" name="status"><option value="">{{ __('admin.any') }}</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('admin.active') }}</option><option value="disabled" @selected(($filters['status'] ?? '') === 'disabled')>{{ __('admin.disabled') }}</option></select></div>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
            <a class="button button-secondary" href="{{ route('admin.accounts.index', ['locale' => $locale]) }}">{{ __('projects.clear_filters') }}</a>
        </form>
        <x-ui.table :caption="__('admin.title')"><thead><tr><th>{{ __('admin.username') }}</th><th>{{ __('admin.status') }}</th><th>{{ __('admin.cities') }}</th><th>{{ __('admin.identities') }}</th><th>{{ __('admin.created') }}</th><th>{{ __('admin.open') }}</th></tr></thead><tbody>
        @forelse($rows as $row)
        <tr><td><bdi>{{ $row->username }}</bdi> @if($row->self)<small>{{ __('admin.self_mark') }}</small>@endif</td><td>{{ $row->disabled_at ? __('admin.disabled') : __('admin.active') }}</td>
            <td>@foreach($cities as $city)@if(isset($row->grants[(int) $city->id]))<span>{{ $ar ? $city->name_ar : $city->name_en }} ({{ $row->grants[(int) $city->id] }})</span> @endif @endforeach @if($row->grants === [])<span>{{ __('admin.unassigned') }}</span>@endif</td>
            <td>{{ $row->links }}</td><td>{{ substr((string) $row->created_at, 0, 10) }}</td>
            <td><a class="record-link" href="{{ route('admin.accounts.show', ['locale' => $locale, 'account' => $row->id]) }}">{{ __('admin.open') }}</a></td></tr>
        @empty<tr><td colspan="6">{{ __('admin.none') }}</td></tr>@endforelse
        </tbody></x-ui.table><div class="pagination"><x-project-pagination :paginator="$rows" /></div>
    </section>
</x-layouts.app>
