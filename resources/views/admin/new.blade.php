@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $roleName = fn ($id) => __('admin.roles.'.$id) !== 'admin.roles.'.$id ? __('admin.roles.'.$id) : __('admin.role_other', ['id' => $id]);
    $involved = $groups->flatMap(fn ($group) => $group->identities)->pluck('city')->unique()->values();
@endphp
<x-layouts.app :title="__('admin.new_title')" :operational="true">
    <a class="back-link" href="{{ route('admin.accounts.index', ['locale' => $locale]) }}">{{ __('admin.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('admin.new_title') }}</h1><p class="page-description">{{ __('admin.new_intro') }}</p></div></div>
    @include('people.messages')
    @if($groups->isEmpty())
    <section class="card"><p>{{ __('admin.no_candidates') }}</p></section>
    @else
    <form method="POST" action="{{ route('admin.accounts.store', ['locale' => $locale]) }}">@csrf
        <section class="card">
            <p><small>{{ __('admin.new_intro') }}</small></p>
            @foreach($groups as $group)
            <fieldset class="city-filter"><legend><bdi>{{ $group->username }}</bdi>@if($group->identities->count() > 1) — {{ __('admin.duplicates') }}@endif</legend>
                @foreach($group->identities as $user)
                <div class="city-filter-options">
                    <label class="city-filter-option"><input type="checkbox" name="identities[]" value="{{ $user->source }}:{{ $user->record }}" @checked(in_array($user->source.':'.$user->record, (array) old('identities'), true))>{{ __('admin.include') }}: {{ $user->city_name ? ($ar ? $user->city_name->name_ar : $user->city_name->name_en) : '' }} — {{ $roleName($user->role) }}</label>
                    <label class="city-filter-option"><input type="radio" name="credential" value="{{ $user->source }}:{{ $user->record }}" @checked(old('credential') === $user->source.':'.$user->record)>{{ __('admin.credential') }}</label>
                </div>
                @endforeach
            </fieldset>
            @endforeach
            <div class="field"><label for="username">{{ __('admin.account_username') }}</label><input class="input" id="username" name="username" maxlength="200" value="{{ old('username') }}"></div>
            @foreach($cities as $city)
            @if($involved->contains((int) $city->id))
            <div class="field"><label for="role-{{ $city->id }}">{{ __('admin.role') }} — {{ $ar ? $city->name_ar : $city->name_en }}</label><select class="input" id="role-{{ $city->id }}" name="roles[{{ $city->id }}]"><option value="">{{ __('admin.role_none') }}</option>
                @foreach($roles as $role)<option value="{{ $role }}" @selected(old('roles.'.$city->id) === $role)>{{ __('admin.bundles.'.$role) }}</option>@endforeach</select></div>
            @endif
            @endforeach
            <label class="city-filter-option"><input type="checkbox" name="confirm" value="1" required>{{ __('admin.confirm') }}</label>
            <x-ui.button type="submit">{{ __('admin.create') }}</x-ui.button>
        </section>
    </form>
    @endif
</x-layouts.app>
