@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('staff.notifications.send_title')" :operational="true">
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('staff.notifications.send_title') }}</h1><p class="page-description">{{ __('staff.notifications.send_intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        @if($cities->count() > 1)
        <form class="search-form" method="GET" action="{{ route('notifications.create', ['locale' => $locale]) }}">
            <div class="field"><label for="city-pick">{{ __('staff.notifications.city') }}</label><select class="input" id="city-pick" name="city">@foreach($cities as $c)<option value="{{ $c->id }}" @selected($c->id === $city)>{{ $ar ? $c->name_ar : $c->name_en }}</option>@endforeach</select></div>
            <x-ui.button type="submit" variant="secondary">{{ __('staff.notifications.choose_city') }}</x-ui.button>
        </form>
        @endif
        <form method="POST" action="{{ route('notifications.send', ['locale' => $locale]) }}">@csrf
            <input type="hidden" name="city" value="{{ $city }}">
            <div class="field"><label for="to">{{ __('staff.notifications.to') }}</label><select class="input" id="to" name="to"><option value="all" @selected(old('to', 'all') === 'all')>{{ __('staff.notifications.everyone') }}</option>
                @foreach($recipients as $user)<option value="{{ $user->record_id }}" @selected((string) old('to') === (string) $user->record_id)>{{ $user->UserName }}</option>@endforeach</select></div>
            <div class="field"><label for="date">{{ __('staff.notifications.date') }}</label><input class="input" id="date" name="date" type="date" value="{{ old('date', now()->toDateString()) }}"></div>
            <div class="field"><label for="title">{{ __('staff.notifications.about') }}</label><input class="input" id="title" name="title" maxlength="255" value="{{ old('title') }}"></div>
            <div class="field"><label for="body">{{ __('staff.notifications.body') }}</label><textarea class="input" id="body" name="body" rows="6" required>{{ old('body') }}</textarea></div>
            @if($admin)<div class="field"><label for="project">{{ __('staff.notifications.project') }}</label><input class="input" id="project" name="project" inputmode="numeric" maxlength="19" value="{{ old('project') }}"></div>@endif
            <x-ui.button type="submit">{{ __('staff.notifications.send') }}</x-ui.button>
        </form>
    </section>
</x-layouts.app>
