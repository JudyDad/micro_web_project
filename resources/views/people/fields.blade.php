@php
    $recovering = old('_form') === ($prefix ?? 'person');
@endphp
<input type="hidden" name="_form" value="{{ $prefix ?? 'person' }}">
<div class="people-fields">
@foreach($fields as $name => $field)
    @php
        $value = $recovering ? old('fields.'.$name) : ($row->{$name} ?? null);
        $missing = $recovering ? in_array($name, old('null_fields', []), true) : $value === null;
        $label = __('people.fields.'.$field['label']);
        if ($label === 'people.fields.'.$field['label']) $label = str_replace('_', ' ', $field['label']);
        $inputId = ($prefix ?? 'person').'-'.$name;
    @endphp
    <div class="field" x-data="{ missing: @js($missing) }">
        <label for="{{ $inputId }}">{{ $label }}</label>
        @if($name === 'c_P_Sex')
            {{-- The desktop's two-value list; a historical value outside it is kept as its own option until changed. --}}
            <select class="input" id="{{ $inputId }}" name="fields[{{ $name }}]" @change="missing = false">
                <option value="" @selected($value === null || $value === '')>{{ __('people.not_recorded') }}</option>
                @if($value !== null && $value !== '' && ! in_array($value, \App\Services\Applications\ApplicationService::SEX_VALUES, true))<option value="{{ $value }}" selected>{{ __('people.retained_reference') }}: {{ $value }}</option>@endif
                @foreach(\App\Services\Applications\ApplicationService::SEX_VALUES as $sex)<option value="{{ $sex }}" @selected($value === $sex)>{{ __('people.sex.'.$sex) }}</option>@endforeach
            </select>
        @elseif($field['type'] === 'textarea')
            <textarea class="input" id="{{ $inputId }}" name="fields[{{ $name }}]" rows="3" maxlength="{{ $field['max'] }}" @input="missing = false">{{ $value }}</textarea>
        @else
            {{-- Text preserves historical sentinel dates and numeric strings until explicitly edited. --}}
            <input class="input" id="{{ $inputId }}" name="fields[{{ $name }}]" type="text" value="{{ $value }}" maxlength="{{ $field['max'] ?? 200 }}" @input="missing = false">
        @endif
        <label class="people-null"><input type="checkbox" name="null_fields[]" value="{{ $name }}" x-model="missing" @checked($missing)> {{ __('people.not_recorded') }}</label>
    </div>
@endforeach
@foreach($options as $name => $choices)
    @php
        $selectedReference = (string) ($recovering ? old('references.'.$name) : ($row->references[$name] ?? ''));
    @endphp
    <div class="field">
        <label for="{{ ($prefix ?? 'person').'-ref-'.$name }}">{{ __('people.fields.'.$name) }}</label>
        <select class="input" id="{{ ($prefix ?? 'person').'-ref-'.$name }}" name="references[{{ $name }}]">
            <option value="">{{ __('people.not_recorded') }}</option>
            @if($selectedReference !== '' && ! $choices->contains(fn ($choice) => (string) $choice->id === $selectedReference))<option value="{{ $selectedReference }}" selected>{{ __('people.retained_reference') }} #{{ $selectedReference }}</option>@endif
            @foreach($choices as $choice)<option value="{{ $choice->id }}" @selected($selectedReference === (string) $choice->id)>{{ $choice->label }}</option>@endforeach
        </select>
    </div>
@endforeach
</div>
