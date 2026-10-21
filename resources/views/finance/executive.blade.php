@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $form = old('_form');
    $day = fn ($value) => substr((string) $value, 0, 10);
@endphp
<x-layouts.app :title="__('finance.executive.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('finance.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('finance.executive.title') }}</h1><p class="page-description">{{ __('finance.executive.scope') }}</p></div></div>
    @include('people.messages')
    @foreach($files as $file)
    <section class="card people-form" id="file-{{ $file->id }}"><h2>{{ __('finance.executive.file') }} <bdi>{{ $file->c_ExeF_No }}</bdi></h2>
        <p>{{ __('finance.executive.expires') }}: <strong><bdi>{{ $file->expires ?? '—' }}</bdi></strong></p>
        @if($canManage)
            @include('finance.executive-form', ['scope' => 'file-'.$file->id, 'file' => $file])
        @else
            <dl class="project-details"><div><dt>{{ __('finance.executive.begin') }}</dt><dd>{{ $day($file->c_ExeF_BeginDate) }}</dd></div><div><dt>{{ __('finance.executive.current') }}</dt><dd>{{ $day($file->c_ExeF_CurrentDate) }}</dd></div><div><dt>{{ __('finance.executive.impound_date') }}</dt><dd>{{ $day($file->c_ExeF_ImpoundDate) }}</dd></div><div><dt>{{ __('finance.executive.impound_type') }}</dt><dd>{{ $file->c_ExeF_ImpoundType }}</dd></div><div><dt>{{ __('finance.executive.months') }}</dt><dd>{{ $file->c_ExeF_NumOfMonths }}</dd></div></dl>
        @endif
    </section>
    @endforeach
    @if($files->isEmpty() && ! $canCreate)<section class="card"><p>{{ __('finance.executive.none') }}</p></section>@endif
    @if($canCreate)<section class="card people-form"><h2>{{ __('finance.executive.new') }}</h2>@include('finance.executive-form', ['scope' => 'file-new', 'file' => null])</section>@endif
</x-layouts.app>
