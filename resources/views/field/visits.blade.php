@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('field.visit.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('field.visit.title') }}</h1><p class="page-description">{{ __('field.visit.scope') }}</p></div></div>
    @include('people.messages')
    @if($rehab)<section class="card people-form"><p>{{ __('field.visit.rehab_note') }}</p>
        @if($rehabVisit !== null || $canRehab)<a class="button button-secondary" href="{{ route('rehab.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.rehab.title') }}</a>@endif</section>@endif
    <section class="card"><h2>{{ __('field.visit.recorded') }}</h2>
        <ul>
            @forelse($visits as $visit)
            @if((string) $visit->c_Kind === \App\Services\Field\RehabVisitService::KIND)
            <li><a class="record-link" href="{{ route('rehab.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.rehab.title') }}</a> · <bdi>{{ substr((string) $visit->c_Date, 0, 10) }}</bdi></li>
            @elseif(\App\Services\Field\VisitKinds::has((string) $visit->c_Kind))
            <li><a class="record-link" href="{{ route('visits.show', ['locale' => $locale, 'application' => $applicationId, 'kind' => $visit->c_Kind]) }}">{{ __('field.visit.kind.'.$visit->c_Kind) }}</a> · <bdi>{{ substr((string) $visit->c_Date, 0, 10) }}</bdi></li>
            @else
            <li>{{ $visit->c_Kind }} · <bdi>{{ substr((string) $visit->c_Date, 0, 10) }}</bdi></li>
            @endif
            @empty<li>{{ __('field.visit.none') }}</li>@endforelse
        </ul>
    </section>
    @if($canRestore && $deleted->isNotEmpty())<section class="card people-form"><h2>{{ __('field.deleted_records') }}</h2>
        @foreach($deleted as $gone)
        <form method="POST" action="{{ route('visits.restore', ['locale' => $locale, 'application' => $applicationId, 'visit' => $gone->id]) }}">@csrf<bdi>{{ $gone->c_Kind }}</bdi> · <bdi>{{ substr((string) $gone->c_Date, 0, 10) }}</bdi> <x-ui.button type="submit">{{ __('field.restore') }}</x-ui.button></form>
        @endforeach
    </section>@endif
    @if($available !== [])<section class="card people-form"><h2>{{ __('field.visit.record') }}</h2>
        @foreach($available as $kind)<a class="button button-secondary" href="{{ route('visits.show', ['locale' => $locale, 'application' => $applicationId, 'kind' => $kind]) }}">{{ __('field.visit.kind.'.$kind) }}</a> @endforeach
    </section>@endif
</x-layouts.app>
