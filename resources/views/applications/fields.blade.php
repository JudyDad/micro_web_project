@php
    $recovering = old('_form') === $prefix;
    if (isset($options['Question_ID'], $options['Answer_ID'])) {
        $options = ['Question_ID' => $options['Question_ID'], 'Answer_ID' => $options['Answer_ID']] + $options;
    }
    $initialQuestion = (string) ($recovering ? old('references.Question_ID', $row->references['Question_ID'] ?? '') : ($row->references['Question_ID'] ?? ''));
    $initialAnswer = (string) ($recovering ? old('references.Answer_ID', $row->references['Answer_ID'] ?? '') : ($row->references['Answer_ID'] ?? ''));
@endphp
<input type="hidden" name="_form" value="{{ $prefix }}">
<div class="people-fields" x-data="{ question: @js($initialQuestion), initialQuestion: @js($initialQuestion), initialAnswer: @js($initialAnswer) }">
@foreach($fields as $name => $field)
    @php
        $value = $recovering ? old('fields.'.$name, $row->{$name} ?? null) : ($row->{$name} ?? null);
        $hasRecoveredValue = $recovering && array_key_exists($name, old('fields', []));
        $missing = $hasRecoveredValue ? in_array($name, old('null_fields', []), true) : $value === null;
        $label = __('applications.fields.'.$field['label']);
        if ($label === 'applications.fields.'.$field['label']) $label = __('people.fields.'.$field['label']);
        if ($label === 'people.fields.'.$field['label']) $label = str_replace('_', ' ', $field['label']);
        $inputId = $prefix.'-'.$name;
    @endphp
    <div class="field" data-null-toggle x-data="{ missing: @js($missing) }">
        <label for="{{ $inputId }}">{{ $label }}</label>
        @if($field['type'] === 'textarea')
            <textarea class="input" id="{{ $inputId }}" name="fields[{{ $name }}]" data-null-value rows="3" maxlength="{{ $field['max'] }}" @input="missing = false">{{ $value }}</textarea>
        @else
            <input class="input" id="{{ $inputId }}" name="fields[{{ $name }}]" data-null-value type="text" value="{{ $value }}" maxlength="{{ $field['max'] }}" @input="missing = false">
        @endif
        <label class="people-null"><input type="checkbox" name="null_fields[]" value="{{ $name }}" x-model="missing" @checked($missing)> {{ __('people.not_recorded') }}</label>
    </div>
@endforeach
@foreach($options as $name => $choices)
    @php
        $selectedReference = (string) ($recovering ? old('references.'.$name, $row->references[$name] ?? '') : ($row->references[$name] ?? ''));
        $referenceLabel = __('applications.fields.'.$name);
        if ($referenceLabel === 'applications.fields.'.$name) $referenceLabel = __('people.fields.'.$name);
        if ($referenceLabel === 'people.fields.'.$name) $referenceLabel = str_replace('_', ' ', $name);
    @endphp
    <div class="field"><label for="{{ $prefix.'-ref-'.$name }}">{{ $referenceLabel }}</label>
        <select class="input" id="{{ $prefix.'-ref-'.$name }}" name="references[{{ $name }}]" @if($name === 'Question_ID') x-model="question" @change="if ($refs.answer) $refs.answer.value = ''" @elseif($name === 'Answer_ID') x-ref="answer" @endif>
            <option value="">{{ __('people.not_recorded') }}</option>
            @if($selectedReference !== '' && ! $choices->contains(fn ($choice) => (string) $choice->id === $selectedReference))<option value="{{ $selectedReference }}" selected>{{ __('people.retained_reference') }} #{{ $selectedReference }}</option>@endif
            @foreach($choices as $choice)<option value="{{ $choice->id }}" @selected($selectedReference === (string) $choice->id)
                @if($name === 'Answer_ID' && isset($choice->parent_id))
                x-bind:disabled="question !== @js((string) $choice->parent_id) && !(question === initialQuestion && initialAnswer === @js((string) $choice->id))"
                x-show="question === @js((string) $choice->parent_id) || (question === initialQuestion && initialAnswer === @js((string) $choice->id))"
                @endif
            >{{ $choice->label }}@if($name === 'Question_ID' && isset($choice->type) && $choice->type !== '') · {{ $choice->type }}@endif</option>@endforeach
        </select>
        @if($name === 'Answer_ID')<small>{{ __('applications.answer_hint') }}</small>@endif
    </div>
@endforeach
</div>
