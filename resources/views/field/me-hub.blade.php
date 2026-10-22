@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('field.me.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('field.me.title') }}</h1><p class="page-description">{{ __('field.me.scope') }}</p></div></div>
    @include('people.messages')
    @if($rehab)<section class="card"><p>{{ __('field.me.rehab') }}</p></section>
    @elseif(! $funded)<section class="card"><p>{{ __('field.me.not_funded') }}</p></section>@endif
    @forelse($people as $person)
    <section class="card people-form"><h2>{{ $person->name }} <small>({{ __('field.me.type.'.$person->type) === 'field.me.type.'.$person->type ? $person->type : __('field.me.type.'.$person->type) }})</small></h2>
        <ul>
            @forelse($person->visits as $visit)
            <li><a class="record-link" href="{{ route('me.show', ['locale' => $locale, 'application' => $applicationId, 'visit' => $visit->id]) }}">#{{ $visit->c_Number }}</a> · {{ __('field.me.kind.'.$visit->c_Kind) }} · <bdi>{{ substr((string) $visit->c_Date, 0, 10) }}</bdi> · {{ __('field.me.result') }}: <bdi>{{ $visit->c_Result }}</bdi></li>
            @empty<li>{{ __('field.me.none') }}</li>@endforelse
        </ul>
        @if($creatable)<a class="button button-secondary" href="{{ route('me.create', ['locale' => $locale, 'application' => $applicationId, 'person' => $person->id]) }}">{{ __('field.me.add', ['number' => $person->next]) }}</a>@endif
    </section>
    @empty<section class="card"><p>{{ __('field.me.no_people') }}</p></section>@endforelse
    @if($canRestore && $deleted->isNotEmpty())<section class="card people-form"><h2>{{ __('field.deleted_records') }}</h2>
        @foreach($deleted as $gone)
        <form method="POST" action="{{ route('me.restore', ['locale' => $locale, 'application' => $applicationId, 'visit' => $gone->id]) }}">@csrf<bdi>#{{ $gone->c_Number }}</bdi> · <bdi>{{ substr((string) $gone->c_Date, 0, 10) }}</bdi> <x-ui.button type="submit">{{ __('field.restore') }}</x-ui.button></form>
        @endforeach
    </section>@endif
</x-layouts.app>
