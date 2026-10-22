@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $mine = old('_form') === 'me';
    $pick = fn (string $key, $stored) => $mine ? old($key, $stored) : $stored;
    $value = fn (string $column, $default = '') => $visit === null ? $default : ($visit->{$column} ?? $default);
@endphp
<x-layouts.app :title="__('field.me.title')" :operational="true">
    <a class="back-link" href="{{ route('me.hub', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('field.me.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }} · {{ $person->name }}</p><h1>{{ __('field.me.title') }} · {{ __('field.me.kind.'.$kind) }}</h1><p class="page-description">{{ __('field.visit.visitors_note') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form">
        <form method="POST" action="{{ route('me.save', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf
            <input type="hidden" name="_form" value="me"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="person_id" value="{{ $person->id }}"><input type="hidden" name="visit_id" value="{{ $visit?->id }}">
            <fieldset @disabled(! $canManage)>
            <div class="people-fields">
                <div class="field"><label for="number">{{ __('field.me.number') }}</label><input class="input" id="number" name="number" inputmode="numeric" maxlength="2" value="{{ $pick('number', $value('c_Number', $next)) }}" required></div>
                <div class="field"><label for="date">{{ __('field.me.date') }}</label><input class="input" id="date" type="date" name="date" value="{{ $pick('date', substr((string) $value('c_Date', now()->toDateString()), 0, 10)) }}" required></div>
                <div class="field"><label for="profit">{{ __('field.me.profit') }}</label><input class="input" id="profit" name="profit" inputmode="decimal" maxlength="40" value="{{ $pick('profit', $value('c_Profit')) }}" required></div>
            </div>
            <h2>{{ __('field.me.questions') }}</h2>
            @forelse($questions as $question)
            @php $row = $given->get($question->id); $currentAnswer = (string) $pick('a'.$question->id, $row === null ? '' : $row->{$answerRef}); @endphp
            <div class="field"><p><strong>{{ $question->name }}</strong></p>
                <div class="city-filter-options">
                    @foreach($question->answers as $answer)<label class="city-filter-option"><input type="radio" name="a{{ $question->id }}" value="{{ $answer->id }}" @checked($currentAnswer === (string) $answer->id) required>{{ $answer->name }}</label>@endforeach
                </div>
                <label class="sr-only" for="n{{ $question->id }}">{{ __('field.me.note') }}</label><textarea class="input" id="n{{ $question->id }}" name="n{{ $question->id }}" rows="1" maxlength="65535">{{ $pick('n'.$question->id, $row === null ? '' : ($row->c_Notes ?? '')) }}</textarea>
            </div>
            @empty<p>{{ __('field.me.none_questions') }}</p>@endforelse
            <div class="field"><label for="ans1">{{ __('field.me.ans1') }}</label><textarea class="input" id="ans1" name="ans1" rows="2" maxlength="65535">{{ $pick('ans1', $value('c_Ans1')) }}</textarea></div>
            <div class="field"><label for="indicators">{{ __('field.me.indicators') }}</label><textarea class="input" id="indicators" name="indicators" rows="2" maxlength="65535">{{ $pick('indicators', $value('c_Indicators')) }}</textarea></div>
            <div class="field"><label for="notes">{{ __('field.me.notes') }}</label><textarea class="input" id="notes" name="notes" rows="2" maxlength="65535">{{ $pick('notes', $value('c_Notes')) }}</textarea></div>
            </fieldset>
            @if($visit !== null)<p>{{ __('field.me.result') }}: <strong><bdi>{{ $mark }}</bdi></strong> · <small>{{ __('field.visit.created_by') }}: <bdi>{{ $visit->c_Created_By }}</bdi> · {{ __('field.visit.edited_by') }}: <bdi>{{ $visit->c_Edited_By }}</bdi></small></p>@endif
            @if($canManage)<x-ui.button type="submit">{{ __('field.save') }}</x-ui.button>@endif
        </form>
        @if($canDelete)
        <details><summary>{{ __('field.delete') }}</summary>
            <form method="POST" action="{{ route('me.delete', ['locale' => $locale, 'application' => $applicationId, 'visit' => $visit->id]) }}">@csrf<input type="hidden" name="version" value="{{ $version }}"><x-ui.button type="submit">{{ __('field.delete_confirm') }}</x-ui.button></form>
        </details>
        @endif
    </section>
</x-layouts.app>
