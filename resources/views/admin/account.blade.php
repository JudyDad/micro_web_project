@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $roleName = fn ($id) => $id === null ? '' : (__('admin.roles.'.$id) !== 'admin.roles.'.$id ? __('admin.roles.'.$id) : __('admin.role_other', ['id' => $id]));
    $cap = fn (string $capability) => __('admin.cap.'.$capability);
    $base = ['locale' => $locale, 'account' => $account->id];
@endphp
<x-layouts.app :title="__('admin.account')" :operational="true">
    <a class="back-link" href="{{ route('admin.accounts.index', ['locale' => $locale]) }}">{{ __('admin.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ __('admin.account') }}</p><h1><bdi>{{ $account->username }}</bdi></h1>
        <p class="page-description">{{ $account->disabled_at ? __('admin.disabled') : __('admin.active') }} · {{ __('admin.created') }} {{ substr((string) $account->created_at, 0, 10) }}</p></div></div>
    @include('people.messages')
    @if($self)<div class="form-message" role="note">{{ __('admin.self') }}</div>@endif
    @if(session('one_time_password'))<div class="form-message" role="alert"><strong>{{ __('admin.one_time_password') }}:</strong> <bdi dir="ltr"><code>{{ session('one_time_password') }}</code></bdi><br><small>{{ __('admin.one_time_note') }}</small></div>@endif
    <section class="card" id="email-section"><h2>{{ __('admin.email') }}</h2>
        @if($canEmail)<form method="POST" action="{{ route('admin.accounts.email', $base) }}">@csrf<div class="field"><label for="email">{{ __('admin.email') }}</label><input class="input" id="email" name="email" type="email" maxlength="190" value="{{ old('email', $account->email) }}"><small>{{ __('admin.email_hint') }}</small></div><x-ui.button type="submit">{{ __('admin.save') }}</x-ui.button></form>
        @else<p>{{ $account->email ?: __('admin.none_held') }}</p><small>{{ __('admin.email_locked') }}</small>@endif
    </section>

    @foreach($panels as $panel)
    @php($city = $panel['city'])
    <section class="card" id="city-{{ $city->id }}"><h2>{{ $ar ? $city->name_ar : $city->name_en }} — {{ __('admin.capabilities') }}</h2>
        <form method="POST" action="{{ route('admin.accounts.capabilities', $base) }}">@csrf
            <input type="hidden" name="city" value="{{ $city->id }}">
            <div class="city-filter-options">
                @foreach($panel['grantable'] as $capability)<label class="city-filter-option"><input type="checkbox" name="capabilities[]" value="{{ $capability }}" @checked(in_array($capability, $panel['active'], true)) @disabled($self)>{{ $cap($capability) }}</label>@endforeach
            </div>
            @if($panel['fixed'])<p><small>{{ __('admin.fixed') }}: {{ collect($panel['fixed'])->map($cap)->implode('، ') }}</small></p>@endif
            @unless($self)<x-ui.button type="submit">{{ __('admin.save') }}</x-ui.button>@endunless
        </form>
        <h3>{{ __('admin.roles_title') }}</h3>
        <p>@forelse($panel['held'] as $held)<span class="city-filter-option">{{ __('admin.bundles.'.$held) }}@unless($self)
            <form method="POST" action="{{ route('admin.accounts.role-revoke', $base) }}" style="display:inline">@csrf<input type="hidden" name="city" value="{{ $city->id }}"><input type="hidden" name="role" value="{{ $held }}"><button class="button button-secondary" type="submit" aria-label="{{ __('admin.revoke_role') }}: {{ __('admin.bundles.'.$held) }}">×</button></form>@endunless</span> @empty<small>{{ __('admin.no_roles') }}</small>@endforelse</p>
        @if($panel['roles'] && ! $self)
        <form method="POST" action="{{ route('admin.accounts.role', $base) }}">@csrf
            <input type="hidden" name="city" value="{{ $city->id }}">
            <div class="field"><label for="role-{{ $city->id }}">{{ __('admin.role') }}</label><select class="input" id="role-{{ $city->id }}" name="role">@foreach($panel['roles'] as $role)<option value="{{ $role }}">{{ __('admin.bundles.'.$role) }}</option>@endforeach</select></div>
            <x-ui.button type="submit" variant="secondary">{{ __('admin.apply_role') }}</x-ui.button>
        </form>
        @endif
    </section>
    @endforeach

    <section class="card" id="identities"><h2>{{ __('admin.identities_title') }}</h2>
        <x-ui.table :caption="__('admin.identities_title')"><thead><tr><th>{{ __('admin.identity') }}</th><th>{{ __('admin.city') }}</th><th>{{ __('admin.staff_role') ?? '' }}</th><th>{{ __('admin.reviewed') }}</th><th>{{ __('admin.state') }}</th><th></th></tr></thead><tbody>
        @forelse($links as $link)
        <tr><td><bdi>{{ $link->username ?? '—' }}</bdi> @if($link->credential)<small>({{ __('admin.credential_link') }})</small>@endif</td><td>{{ $link->city ? ($ar ? $link->city->name_ar : $link->city->name_en) : '' }}</td><td>{{ $roleName($link->role) }}</td><td>{{ substr((string) $link->reviewed_at, 0, 10) }}</td>
            <td>{{ $link->revoked_at ? __('admin.link_revoked') : ($link->principal_disabled_at ? __('admin.link_disabled') : __('admin.link_active')) }}</td>
            <td>@unless($link->credential && ! $link->revoked_at)<form method="POST" action="{{ route('admin.accounts.link-state', $base + ['link' => $link->id]) }}">@csrf<input type="hidden" name="revoked" value="{{ $link->revoked_at ? 0 : 1 }}"><x-ui.button type="submit" variant="secondary">{{ $link->revoked_at ? __('admin.restore') : __('admin.revoke') }}</x-ui.button></form>@endunless</td></tr>
        @empty<tr><td colspan="6">{{ __('admin.none_held') }}</td></tr>@endforelse
        </tbody></x-ui.table>
        @if($otherLinks)<p><small>{{ __('admin.other_links', ['count' => $otherLinks]) }}</small></p>@endif
        @if($candidates->isNotEmpty())
        <form method="POST" action="{{ route('admin.accounts.link', $base) }}">@csrf
            <h3>{{ __('admin.link_title') }}</h3><p><small>{{ __('admin.link_hint') }}</small></p>
            <div class="field"><label for="identity">{{ __('admin.identity') }}</label><select class="input" id="identity" name="identity" required><option value="">{{ __('admin.choose') }}</option>
                @foreach($candidates as $user)<option value="{{ $user->source }}:{{ $user->record }}">{{ $user->username }} — {{ $user->city_name ? ($ar ? $user->city_name->name_ar : $user->city_name->name_en) : '' }} — {{ $roleName($user->role) }}@if($user->same) ({{ __('admin.same_name') }})@endif</option>@endforeach</select></div>
            <x-ui.button type="submit">{{ __('admin.link') }}</x-ui.button>
        </form>
        @endif
    </section>

    @unless($self)
    <section class="card" id="status"><h2>{{ __('admin.status') }}</h2>
        <p><small>{{ __('admin.disable_note') }}</small></p>
        @if($canDisable)
        <form method="POST" action="{{ route('admin.accounts.disable', $base) }}">@csrf<input type="hidden" name="disabled" value="{{ $account->disabled_at ? 0 : 1 }}">
            <x-ui.button type="submit" variant="secondary">{{ $account->disabled_at ? __('admin.enable') : __('admin.disable') }}</x-ui.button></form>
        @else<div class="form-message" role="note">{{ __('admin.cannot_disable') }}</div>@endif
    </section>
    @endunless
</x-layouts.app>
