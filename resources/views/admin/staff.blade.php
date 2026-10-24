@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('admin.staff_title')" :operational="true">
    <a class="back-link" href="{{ route('admin.accounts.index', ['locale' => $locale]) }}">{{ __('admin.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('admin.staff_title') }}</h1><p class="page-description">{{ __('admin.staff_intro') }}</p></div></div>
    <section class="card">
        <form class="search-form project-filters" method="GET" action="{{ route('admin.staff', ['locale' => $locale]) }}">
            <div class="field"><label for="city">{{ __('admin.city') }}</label><select class="input" id="city" name="city"><option value="">{{ __('admin.all_cities') }}</option>
                @foreach($cities as $city)<option value="{{ $city->id }}" @selected((string) ($filters['city'] ?? '') === (string) $city->id)>{{ $ar ? $city->name_ar : $city->name_en }}</option>@endforeach</select></div>
            <x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button>
        </form>
    </section>
    @foreach($groups as $group)
    <section class="card"><h2>{{ $ar ? $group['city']->name_ar : $group['city']->name_en }}</h2>
        <x-ui.table :caption="__('admin.staff_title')"><thead><tr><th>{{ __('admin.staff_user') }}</th><th>{{ __('admin.staff_roles') }}</th><th>{{ __('admin.staff_tasks') }}</th><th>{{ __('admin.email') }}</th><th>{{ __('admin.status') }}</th></tr></thead><tbody>
        @forelse($group['users'] as $user)
        <tr><td><a href="{{ route('admin.accounts.show', ['locale' => $locale, 'account' => $user->id]) }}"><bdi>{{ $user->username }}</bdi></a></td><td>{{ collect($user->roles)->map(fn ($role) => __('admin.bundles.'.$role))->implode('، ') }}</td><td>{{ $user->tasks }}</td><td><bdi>{{ $user->email }}</bdi></td><td>{{ $user->disabled ? __('admin.disabled') : __('admin.active') }}</td></tr>
        @empty<tr><td colspan="5">{{ __('admin.staff_empty') }}</td></tr>@endforelse
        </tbody></x-ui.table>
    </section>
    @endforeach
</x-layouts.app>
