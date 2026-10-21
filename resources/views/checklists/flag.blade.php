<label for="flag-{{ $name }}">{{ __('lifecycle.checklist.fields.'.$name) }}</label>
<select class="input" id="flag-{{ $name }}" name="fields[{{ $name }}]" @disabled($disabled)>
    <option value="">{{ __('people.not_recorded') }}</option>
    @foreach($options as $key => $label)<option value="{{ $key }}" @selected($value === (string) $key)>{{ __('lifecycle.checklist.'.$label) }}</option>@endforeach
    @if($value !== '' && ! array_key_exists($value, $options))<option value="{{ $value }}" selected>{{ $value }}</option>@endif
</select>
