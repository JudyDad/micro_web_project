@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $roleName = fn ($id) => __('admin.roles.'.$id) !== 'admin.roles.'.$id ? __('admin.roles.'.$id) : __('admin.role_other', ['id' => $id]);
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
        <x-ui.table :caption="__('admin.staff_title')"><thead><tr><th>{{ __('admin.staff_user') }}</th><th>{{ __('admin.staff_role') }}</th><th>{{ __('admin.staff_flags') }}</th><th>{{ __('admin.staff_account') }}</th></tr></thead><tbody>
        @forelse($group['users'] as $user)
        <tr><td><bdi>{{ $user->username }}</bdi></td><td>{{ $roleName($user->role) }}</td><td>{{ collect($user->flags)->map(fn ($flag) => __('admin.flags.'.$flag))->implode('، ') }}</td>
            <td>@if($user->account_id)<a href="{{ route('admin.accounts.show', ['locale' => $locale, 'account' => $user->account_id]) }}"><bdi>{{ $user->account }}</bdi></a>@if($user->revoked) <small>({{ __('admin.link_revoked') }})</small>@endif @else {{ __('admin.unlinked') }} @endif</td></tr>
        @empty<tr><td colspan="4">{{ __('admin.staff_empty') }}</td></tr>@endforelse
        </tbody></x-ui.table>
    </section>
    @endforeach
</x-layouts.app>
