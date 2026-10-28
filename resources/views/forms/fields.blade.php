{{--
    One ordered field renderer for every form kind, in the paper form's order (PaperOrder). Parameters:
    $fields, $options     metadata and choices (as the services return them)
    $row                  stored row or null; $prefix: id prefix; $recovering: whether old() input belongs to this form
    $fieldName/$refName/$nullName/$oldBase   input name roots, e.g. 'fields' / 'references' / 'null_fields[]' / ''
    $nullBoxes            show the "not recorded" box (edit forms); false on create forms, where a blank box is not recorded
    $required, $hidden    keys marked with * / keys left out (the desktop's preliminary form hides them)
    $groups               render the kind's group headings
--}}
@php
    use App\Services\Applications\PaperOrder;
    $nullBoxes ??= true; $required ??= []; $hidden ??= []; $groups ??= false; $oldBase ??= ''; $recovering ??= false;
    $fieldName ??= 'fields'; $refName ??= 'references'; $nullName ??= 'null_fields[]';
    $keys = array_keys($fields + $options);
    $kind = PaperOrder::kindOf($keys);
    $ordered = $kind ? array_keys(PaperOrder::sort($kind, $fields + $options)) : $keys;
    $ordered = array_values(array_diff($ordered, $hidden));
    $grouped = [];
    if ($groups && $kind) {
        foreach (PaperOrder::groups($kind) as $group => $groupKeys) {
            $members = array_values(array_intersect($groupKeys, $ordered));
            if ($members !== []) $grouped[$group] = $members;
        }
        $rest = array_values(array_diff($ordered, ...array_values($grouped) ?: [[]]));
        if ($rest !== []) $grouped['other'] = array_merge($grouped['other'] ?? [], $rest);
    } else {
        $grouped = ['' => $ordered];
    }
    $labelOf = function (string $key) {
        $base = str_starts_with($key, 'c_') ? substr($key, 2) : $key;
        foreach (['applications', 'people'] as $ns) {
            if (__($ns.'.fields.'.$base) !== $ns.'.fields.'.$base) return __($ns.'.fields.'.$base);
        }
        return str_replace('_', ' ', $base);
    };
    $hint = fn (string $key) => __('forms.hints.'.$key) !== 'forms.hints.'.$key ? __('forms.hints.'.$key) : null;
    $oldValue = fn (string $key, $stored) => $recovering ? old($oldBase.'fields.'.$key, $stored) : $stored;
    $wasNulled = fn (string $key) => $recovering && in_array($key, old($oldBase.'null_fields', []), true);
    $initialRefs = [];
    foreach ($options as $name => $choices) {
        $initialRefs[$name] = (string) ($recovering ? old($oldBase.'references.'.$name, $row->references[$name] ?? '') : ($row->references[$name] ?? ''));
    }
    $cascades = array_intersect_key(PaperOrder::CASCADES, $options);
    $childOf = []; foreach ($cascades as $child => [$parent]) { if (isset($options[$parent])) $childOf[$parent][] = $child; }
    $inputName = fn (string $root, string $key) => $root.'['.$key.']';
    $formMarker ??= true;
    // With $formId the controls belong to a form element elsewhere on the page (the paper form), so grid forms can sit between them.
    $formAttr = isset($formId) ? ' form="'.e($formId).'"' : '';
@endphp
@if($formMarker)<input type="hidden" name="_form" value="{{ $prefix }}">@endif
<div class="people-fields" x-data="{ sel: @js($initialRefs), initial: @js($initialRefs) }">
@foreach($grouped as $group => $groupKeys)
    @if($group !== '' && $kind)<h3 class="form-group-title">{{ __('forms.groups.'.$kind.'.'.$group) }}</h3>@endif
    @foreach($groupKeys as $key)
        @php
            $inputId = $prefix.'-'.$key; $isRequired = in_array($key, $required, true); $label = $labelOf($key); $fieldHint = $hint($key);
        @endphp
        @if(isset($fields[$key]))
            @php
                $field = $fields[$key]; $stored = $row->{$key} ?? null; $value = $oldValue($key, $stored);
                $missing = $isRequired ? false : ($recovering && array_key_exists($key, old($oldBase.'fields', [])) ? $wasNulled($key) : ($value === null));
                $name = $inputName($fieldName, $key); $showNull = $nullBoxes && ! $isRequired;
            @endphp
            <div class="field" @if($showNull) data-null-toggle @endif x-data="{ missing: @js($missing) }">
                <label @if(! PaperOrder::isFlag($key) && $key !== 'c_MP_AmperType') for="{{ $inputId }}" @endif>{{ $label }}@if($isRequired) <span class="required-mark">*</span>@endif</label>
                @if(PaperOrder::isFlag($key))
                    <div class="choice-row" role="radiogroup" aria-label="{{ $label }}">
                        @foreach(['1' => __('forms.yes'), '0' => __('forms.no')] as $flagValue => $flagLabel)
                        <label class="choice"><input type="radio"{!! $formAttr !!} name="{{ $name }}" value="{{ $flagValue }}" data-null-value @checked((string) $value === $flagValue) @change="missing = false"> {{ $flagLabel }}</label>
                        @endforeach
                        @unless($showNull)<label class="choice"><input type="radio"{!! $formAttr !!} name="{{ $name }}" value="" @checked($value === null || $value === '')> {{ __('forms.not_recorded') }}</label>@endunless
                    </div>
                @elseif($key === 'c_MP_AmperType')
                    <div class="choice-row" role="radiogroup" aria-label="{{ $label }}">
                        @foreach(PaperOrder::AMPER_TYPES as $amperValue => $amperKey)
                        <label class="choice"><input type="radio"{!! $formAttr !!} name="{{ $name }}" value="{{ $amperValue }}" data-null-value @checked((string) $value === (string) $amperValue) @change="missing = false"> {{ __('forms.amper.'.$amperValue) }}</label>
                        @endforeach
                        @unless($showNull)<label class="choice"><input type="radio"{!! $formAttr !!} name="{{ $name }}" value="" @checked($value === null || $value === '')> {{ __('forms.not_recorded') }}</label>@endunless
                    </div>
                @elseif($key === 'c_P_Sex')
                    <select class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value @change="missing = false">
                        <option value="" @selected($value === null || $value === '')>{{ $isRequired ? __('applications.select_sex') : __('people.not_recorded') }}</option>
                        @if($value !== null && $value !== '' && ! in_array($value, \App\Services\Applications\ApplicationService::SEX_VALUES, true))<option value="{{ $value }}" selected>{{ __('people.retained_reference') }}: {{ $value }}</option>@endif
                        @foreach(\App\Services\Applications\ApplicationService::SEX_VALUES as $sex)<option value="{{ $sex }}" @selected($value === $sex)>{{ __('people.sex.'.$sex) }}</option>@endforeach
                    </select>
                @elseif($key === 'c_P_MilitaryService')
                    <select class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value @change="missing = false">
                        <option value="" @selected($value === null || $value === '')>{{ __('people.not_recorded') }}</option>
                        @if($value !== null && $value !== '' && ! in_array($value, PaperOrder::MILITARY_VALUES, true))<option value="{{ $value }}" selected>{{ __('people.retained_reference') }}: {{ $value }}</option>@endif
                        @foreach(PaperOrder::MILITARY_VALUES as $service)<option value="{{ $service }}" @selected($value === $service)>{{ __('forms.military.'.$service) }}</option>@endforeach
                    </select>
                @elseif(in_array($key, PaperOrder::YEAR_DATES, true))
                    @php $yearValue = preg_match('/\A(\d{4})-01-01/', (string) $value, $m) ? $m[1] : (string) $value; @endphp
                    <input class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value type="text" inputmode="numeric" value="{{ $yearValue }}" maxlength="10" placeholder="YYYY" @input="missing = false">
                    <p class="field-hint">{{ __('forms.year_hint') }}</p>
                @elseif($field['type'] === 'date')
                    {{-- A date picker, unless the stored value is a historical sentinel (0000-00-00) that must survive until explicitly edited. --}}
                    @php $isoDate = $value === null || $value === '' || (preg_match('/\A(\d{4})-(\d{2})-(\d{2})/', (string) $value, $d) && checkdate((int) $d[2], (int) $d[3], (int) $d[1])); @endphp
                    <input class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value type="{{ $isoDate ? 'date' : 'text' }}" value="{{ $isoDate ? substr((string) $value, 0, 10) : $value }}" @input="missing = false">
                @elseif($field['type'] === 'textarea')
                    <textarea class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value rows="3" maxlength="{{ $field['max'] }}" @input="missing = false">{{ $value }}</textarea>
                @else
                    <input class="input"{!! $formAttr !!} id="{{ $inputId }}" name="{{ $name }}" data-null-value type="text" @if($field['type'] === 'number') inputmode="decimal" @endif value="{{ $value }}" maxlength="{{ $field['max'] ?? 200 }}" @input="missing = false">
                @endif
                @if($fieldHint)<p class="field-hint">{{ $fieldHint }}</p>@endif
                @if($showNull)<label class="people-null"><input type="checkbox"{!! $formAttr !!} name="{{ $nullName }}" value="{{ $key }}" x-model="missing" @checked($missing)> {{ __('people.not_recorded') }}</label>@endif
            </div>
        @elseif(isset($options[$key]))
            @php $choices = $options[$key]; $selected = $initialRefs[$key]; $parent = $cascades[$key][0] ?? null; $parent = $parent !== null && isset($options[$parent]) ? $parent : null; @endphp
            <div class="field"><label for="{{ $prefix.'-ref-'.$key }}">{{ $label }}@if($isRequired) <span class="required-mark">*</span>@endif</label>
                <select class="input"{!! $formAttr !!} id="{{ $prefix.'-ref-'.$key }}" name="{{ $inputName($refName, $key) }}" x-model="sel['{{ $key }}']"
                    @if(isset($childOf[$key])) @change="@foreach($childOf[$key] as $child) sel['{{ $child }}'] = ''; @endforeach" @endif>
                    <option value="">{{ $isRequired ? __('forms.choose') : __('people.not_recorded') }}</option>
                    @if($selected !== '' && ! $choices->contains(fn ($choice) => (string) $choice->id === $selected))<option value="{{ $selected }}" selected>{{ __('people.retained_reference') }} #{{ $selected }}</option>@endif
                    @foreach($choices as $choice)<option value="{{ $choice->id }}" @selected($selected === (string) $choice->id)
                        @if($parent !== null && isset($choice->parent_id))
                        x-bind:disabled="sel['{{ $parent }}'] !== @js((string) $choice->parent_id) && !(sel['{{ $parent }}'] === initial['{{ $parent }}'] && initial['{{ $key }}'] === @js((string) $choice->id))"
                        x-show="sel['{{ $parent }}'] === @js((string) $choice->parent_id) || (sel['{{ $parent }}'] === initial['{{ $parent }}'] && initial['{{ $key }}'] === @js((string) $choice->id))"
                        @endif
                    >{{ $choice->label }}@if($key === 'Question_ID' && isset($choice->type) && $choice->type !== '') · {{ $choice->type }}@endif</option>@endforeach
                </select>
                @if($key === 'Answer_ID')<small>{{ __('applications.answer_hint') }}</small>@elseif($fieldHint)<p class="field-hint">{{ $fieldHint }}</p>@endif
            </div>
        @endif
    @endforeach
@endforeach
</div>
