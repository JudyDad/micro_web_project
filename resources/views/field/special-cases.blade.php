@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
@endphp
<x-layouts.app :title="__('field.special.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('field.special.title') }}</h1><p class="page-description">{{ __('field.special.scope') }}</p></div></div>
    @include('people.messages')
    @foreach($cases as $case)
    <section class="card people-form" id="case-{{ $case->id }}"><h2>{{ __('field.special.case') }} <bdi>{{ substr((string) $case->c_Date, 0, 10) }}</bdi> · {{ $case->type_name ?? '—' }}</h2>
        @if($canManage)
            @include('field.special-case-form', ['scope' => 'case-'.$case->id, 'case' => $case])
        @else
            <dl class="project-details"><div><dt>{{ __('field.special.reason') }}</dt><dd>{{ $case->c_Reason }}</dd></div><div><dt>{{ __('field.special.description') }}</dt><dd>{{ $case->c_Description }}</dd></div><div><dt>{{ __('field.special.action') }}</dt><dd>{{ $case->c_Action }}</dd></div></dl>
        @endif
        @if($canDelete)
        <details><summary>{{ __('field.delete') }}</summary>
            <form method="POST" action="{{ route('special-cases.delete', ['locale' => $locale, 'application' => $applicationId, 'case' => $case->id]) }}">@csrf<input type="hidden" name="version" value="{{ $version }}"><x-ui.button type="submit">{{ __('field.delete_confirm') }}</x-ui.button></form>
        </details>
        @endif
    </section>
    @endforeach
    @if($cases->isEmpty())<section class="card"><p>{{ __('field.special.none') }}</p></section>@endif
    @if($canManage)<section class="card people-form"><h2>{{ __('field.special.new') }}</h2>@include('field.special-case-form', ['scope' => 'case-new', 'case' => null])</section>@endif
</x-layouts.app>
