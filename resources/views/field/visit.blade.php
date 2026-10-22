@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $w = 'field.visit.'.$wording;
    $mine = old('_form') === $kind;
    $pick = fn (string $key, $stored) => $mine ? old($key, $stored) : $stored;
    $stored = fn (?object $row, string $column, $default = '') => $row === null ? $default : ($row->{$column} ?? $default);
@endphp
<x-layouts.app :title="__('field.visit.kind.'.$kind)" :operational="true">
    <a class="back-link" href="{{ route('visits.hub', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.visit.title') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('field.visit.kind.'.$kind) }}</h1><p class="page-description">{{ __('field.visit.visitors_note') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form">
        <form method="POST" action="{{ route('visits.save', ['locale' => $locale, 'application' => $applicationId, 'kind' => $kind]) }}">@csrf
            <input type="hidden" name="_form" value="{{ $kind }}"><input type="hidden" name="version" value="{{ $version }}">
            <fieldset @disabled(! $canManage)>
            <div class="people-fields">
                <div class="field"><label for="date">{{ __('field.visit.date') }}</label><input class="input" id="date" type="date" name="date" value="{{ $pick('date', substr((string) $stored($visit, 'c_Date', now()->toDateString()), 0, 10)) }}" required></div>
                @if($definition['vehicle'])<div class="field"><label for="vehicle">{{ __('field.visit.vehicle') }}</label><input class="input" id="vehicle" name="vehicle" maxlength="50" value="{{ $pick('vehicle', $stored($visit, 'c_VehicleNumber')) }}" required></div>@endif
                @if($definition['place'])
                <div class="field"><label for="street_id">{{ __('field.visit.street') }}</label><select class="input" id="street_id" name="street_id"><option value="">{{ __('field.visit.no_street') }}</option>
                    @foreach($streets as $street)<option value="{{ $street->id }}" @selected((int) $pick('street_id', $streetId) === (int) $street->id)>{{ $street->c_Name }}</option>@endforeach</select></div>
                <div class="field"><label for="place_note">{{ __('field.visit.place_note') }}</label><input class="input" id="place_note" name="place_note" maxlength="255" value="{{ $pick('place_note', $placeNote) }}"></div>
                @endif
            </div>
            @foreach($definition['questions'] as $n => $domain)
            <div class="field"><p><strong>{{ __($w.'.q.'.$n) }}</strong></p>
                @if($domain !== null)
                @php $current = (string) $pick('q'.$n.'_value', $stored($visit, 'c_Ans'.$n.'_Value', -1)); @endphp
                <div class="city-filter-options">
                    <label class="city-filter-option"><input type="radio" name="q{{ $n }}_value" value="" @checked($current === '' || $current === '-1')>{{ __('field.visit.unanswered') }}</label>
                    @foreach($domain as $value => $label)<label class="city-filter-option"><input type="radio" name="q{{ $n }}_value" value="{{ $value }}" @checked($current === (string) $value)>{{ __('field.visit.choice.'.$label) }}</label>@endforeach
                </div>
                @endif
                <label class="sr-only" for="q{{ $n }}_text">{{ __('field.visit.answer') }}</label><textarea class="input" id="q{{ $n }}_text" name="q{{ $n }}_text" rows="2" maxlength="65535">{{ $pick('q'.$n.'_text', $stored($visit, 'c_Ans'.$n)) }}</textarea>
            </div>
            @endforeach
            <h2>{{ __('field.visit.office_title') }}</h2>
            <p><strong>{{ __($w.'.office_intro') }}</strong></p>
            @for($n = 1; $n <= $definition['office']; $n++)
            @php $currentOffice = (string) $pick('office'.$n.'_value', $stored($office, 'c_Ans'.$n.'_Value', -1)); @endphp
            <div class="field"><p>{{ __($w.'.office.'.$n) }}</p>
                <div class="city-filter-options">
                    <label class="city-filter-option"><input type="radio" name="office{{ $n }}_value" value="" @checked($currentOffice === '' || $currentOffice === '-1')>{{ __('field.visit.unanswered') }}</label>
                    @foreach(\App\Services\Field\VisitKinds::RATING as $value => $label)<label class="city-filter-option"><input type="radio" name="office{{ $n }}_value" value="{{ $value }}" @checked($currentOffice === (string) $value)>{{ __('field.visit.rating.'.$label) }}</label>@endforeach
                </div>
                <label class="sr-only" for="office{{ $n }}_text">{{ __('field.visit.answer') }}</label><textarea class="input" id="office{{ $n }}_text" name="office{{ $n }}_text" rows="1" maxlength="65535">{{ $pick('office'.$n.'_text', $stored($office, 'c_Ans'.$n)) }}</textarea>
            </div>
            @endfor
            @if($definition['other_comments'])<div class="field"><label for="other_comments">{{ __($w.'.other_comments') }}</label><textarea class="input" id="other_comments" name="other_comments" rows="2" maxlength="65535">{{ $pick('other_comments', $stored($office, 'c_OtherComments')) }}</textarea></div>@endif
            <div class="field"><label for="indicators">{{ __($w.'.indicators') }}</label><textarea class="input" id="indicators" name="indicators" rows="2" maxlength="65535">{{ $pick('indicators', $stored($visit, 'c_Indicators')) }}</textarea></div>
            </fieldset>
            @if($visit !== null)<p><small>{{ __('field.visit.created_by') }}: <bdi>{{ $visit->c_Created_By }}</bdi> · {{ __('field.visit.edited_by') }}: <bdi>{{ $visit->c_Edited_By }}</bdi></small></p>@endif
            @if($canManage)<x-ui.button type="submit">{{ __('field.save') }}</x-ui.button>@endif
        </form>
        @if($canDelete)
        <details><summary>{{ __('field.delete') }}</summary>
            <form method="POST" action="{{ route('visits.delete', ['locale' => $locale, 'application' => $applicationId, 'kind' => $kind]) }}">@csrf<input type="hidden" name="version" value="{{ $version }}"><x-ui.button type="submit">{{ __('field.delete_confirm') }}</x-ui.button></form>
        </details>
        @endif
    </section>
</x-layouts.app>
