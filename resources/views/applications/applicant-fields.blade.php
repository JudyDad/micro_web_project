{{-- The applicant's own record inside the one-form intake: the people directory's fields, posted as person[...] so they
     travel with the project. Fields the desktop requires for a new applicant have no "not recorded" box. --}}
@php
    $recovering = old('_form') === 'application';
    $requiredFields = $requirements['fields'];
    $requiredReferences = $requirements['references'];
@endphp
<div class="people-fields">
@foreach($fields as $name => $field)
    @php
        $value = $recovering ? old('person.fields.'.$name) : null;
        $required = in_array($name, $requiredFields, true);
        $missing = ! $required && ($recovering ? in_array($name, old('person.null_fields', []), true) : true);
        $label = __('people.fields.'.$field['label']);
        if ($label === 'people.fields.'.$field['label']) $label = str_replace('_', ' ', $field['label']);
        $inputId = 'applicant-'.$name;
    @endphp
    <div class="field" x-data="{ missing: @js($missing) }">
        <label for="{{ $inputId }}">{{ $label }}@if($required) <span class="required-mark">*</span>@endif</label>
        @if($name === 'c_P_Sex')
            <select class="input" id="{{ $inputId }}" name="person[fields][{{ $name }}]">
                <option value="" @selected($value === null || $value === '')>{{ __('applications.select_sex') }}</option>
                @foreach(\App\Services\Applications\ApplicationService::SEX_VALUES as $sex)<option value="{{ $sex }}" @selected($value === $sex)>{{ __('people.sex.'.$sex) }}</option>@endforeach
            </select>
        @elseif($field['type'] === 'textarea')
            <textarea class="input" id="{{ $inputId }}" name="person[fields][{{ $name }}]" rows="3" maxlength="{{ $field['max'] }}" @input="missing = false">{{ $value }}</textarea>
        @else
            <input class="input" id="{{ $inputId }}" name="person[fields][{{ $name }}]" type="text" value="{{ $value }}" maxlength="{{ $field['max'] ?? 200 }}" @input="missing = false">
        @endif
        @unless($required)
        <label class="people-null"><input type="checkbox" name="person[null_fields][]" value="{{ $name }}" x-model="missing" @checked($missing)> {{ __('people.not_recorded') }}</label>
        @endunless
    </div>
@endforeach
@foreach($options as $name => $choices)
    @php
        $selectedReference = (string) ($recovering ? old('person.references.'.$name, '') : '');
        $required = in_array($name, $requiredReferences, true);
    @endphp
    <div class="field">
        <label for="{{ 'applicant-ref-'.$name }}">{{ __('people.fields.'.$name) }}@if($required) <span class="required-mark">*</span>@endif</label>
        <select class="input" id="{{ 'applicant-ref-'.$name }}" name="person[references][{{ $name }}]">
            <option value="">{{ __('people.not_recorded') }}</option>
            @if($selectedReference !== '' && ! $choices->contains(fn ($choice) => (string) $choice->id === $selectedReference))<option value="{{ $selectedReference }}" selected>{{ __('people.retained_reference') }} #{{ $selectedReference }}</option>@endif
            @foreach($choices as $choice)<option value="{{ $choice->id }}" @selected($selectedReference === (string) $choice->id)>{{ $choice->label }}</option>@endforeach
        </select>
    </div>
@endforeach
</div>
