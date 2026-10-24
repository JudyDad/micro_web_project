@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('admin.tasks_title')" :operational="true">
    <a class="back-link" href="{{ route('admin.accounts.index', ['locale' => $locale]) }}">{{ __('admin.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ __('projects.workspace') }}</p><h1>{{ __('admin.tasks_title') }}</h1><p class="page-description">{{ __('admin.tasks_intro') }}</p></div></div>
    @include('people.messages')
    <section class="card">
        <form class="search-form" method="GET" action="{{ route('admin.tasks', ['locale' => $locale]) }}">
            <div class="field"><label for="city">{{ __('admin.city') }}</label><select class="input" id="city" name="city">@foreach($cities as $c)<option value="{{ $c->id }}" @selected($c->id === $city->id)>{{ $ar ? $c->name_ar : $c->name_en }}</option>@endforeach</select></div>
            <x-ui.button type="submit" variant="secondary">{{ __('staff.notifications.choose_city') }}</x-ui.button>
        </form>
        @if($accounts->isEmpty())<div class="form-message" role="note">{{ __('admin.tasks_no_accounts') }}</div>@endif
        <form method="POST" action="{{ route('admin.tasks.save', ['locale' => $locale]) }}">@csrf
            <input type="hidden" name="city" value="{{ $city->id }}">
            @foreach(['f' => 'staff.tasks.funded', 'a' => 'staff.tasks.applications'] as $type => $label)
            <h2>{{ __($label) }}</h2>
            @foreach(collect($tasks)->where('type', $type) as $task)
            <fieldset class="city-filter"><legend>{{ $task->name !== '' ? $task->name : '#'.$task->legacy }}</legend><div class="city-filter-options">
                @foreach($accounts as $account)<label class="city-filter-option"><input type="checkbox" name="assign[{{ $task->id }}][]" value="{{ $account->id }}" @checked(in_array($account->id, $task->accounts, true))><bdi>{{ $account->username }}</bdi></label>@endforeach
            </div></fieldset>
            @endforeach
            @endforeach
            <x-ui.button type="submit">{{ __('admin.save') }}</x-ui.button>
        </form>
    </section>
</x-layouts.app>
