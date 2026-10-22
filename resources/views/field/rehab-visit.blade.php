@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $mine = old('_form') === 'rehab';
    $pick = fn (string $key, $stored) => $mine ? old($key, $stored) : $stored;
    $domains = \App\Services\Field\RehabVisitService::DOMAINS;
@endphp
<x-layouts.app :title="__('field.rehab.title')" :operational="true">
    <a class="back-link" href="{{ route('visits.hub', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.visit.title') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('field.rehab.title') }}</h1><p class="page-description">{{ __('field.visit.visitors_note') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form">
        <form method="POST" action="{{ route('rehab.save', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf
            <input type="hidden" name="_form" value="rehab"><input type="hidden" name="version" value="{{ $version }}">
            <fieldset @disabled(! $canManage)>
            <div class="field"><label for="date">{{ __('field.visit.date') }}</label><input class="input" id="date" type="date" name="date" value="{{ $pick('date', substr((string) ($visit?->c_Date ?? now()->toDateString()), 0, 10)) }}" required></div>
            @foreach(\App\Services\Field\RehabVisitService::QUESTIONS as $n => $type)
            @if($n === 1)<p><strong>{{ __('field.rehab.section.1') }}</strong></p>@elseif($n === 5)<p><strong>{{ __('field.rehab.section.2') }}</strong></p>@elseif($n === 13)<p><strong>{{ __('field.rehab.section.3') }}</strong></p>@elseif($n === 14)<p><strong>{{ __('field.rehab.section.4') }}</strong></p>@elseif($n === 15)<p><strong>{{ __('field.rehab.section.5') }}</strong></p>@endif
            @php
                $stored = $answers === null ? null : (int) ($answers->{'c_Ans'.$n.'_Value'} ?? -1);
                $current = (string) $pick('q'.$n.'_value', $stored === null ? '' : ($stored === -1 && $type === 'rating_na' ? 'na' : ($stored === -1 ? '' : $stored)));
            @endphp
            <div class="field"><p>{{ __('field.rehab.q.'.$n) }}</p>
                <div class="city-filter-options">
                    @foreach($domains[$type] as $value => $label)@php $posted = $value === -1 ? 'na' : (string) $value; @endphp<label class="city-filter-option"><input type="radio" name="q{{ $n }}_value" value="{{ $posted }}" @checked($current === $posted) required>{{ __('field.rehab.choice.'.$label) }}</label>@endforeach
                </div>
                <label class="sr-only" for="q{{ $n }}_text">{{ __('field.visit.answer') }}</label><textarea class="input" id="q{{ $n }}_text" name="q{{ $n }}_text" rows="1" maxlength="65535">{{ $pick('q'.$n.'_text', $answers->{'c_Ans'.$n} ?? '') }}</textarea>
            </div>
            @endforeach
            </fieldset>
            @if($visit !== null)<p><small>{{ __('field.visit.created_by') }}: <bdi>{{ $visit->c_Created_By }}</bdi> · {{ __('field.visit.edited_by') }}: <bdi>{{ $visit->c_Edited_By }}</bdi></small></p>@endif
            @if($canManage)<x-ui.button type="submit">{{ __('field.save') }}</x-ui.button>@endif
        </form>
        @if($canDelete)
        <details><summary>{{ __('field.delete') }}</summary>
            <form method="POST" action="{{ route('rehab.delete', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf<input type="hidden" name="version" value="{{ $version }}"><x-ui.button type="submit">{{ __('field.delete_confirm') }}</x-ui.button></form>
        </details>
        @endif
    </section>
</x-layouts.app>
