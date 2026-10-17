@props(['id', 'label', 'hint' => null, 'error' => null])
<div class="field">
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" {{ $attributes->merge(['type' => 'text'])->class(['input', 'input-error' => (bool) $error]) }} @if($error) aria-invalid="true" @endif @if($hint || $error) aria-describedby="{{ $id }}-help" @endif>
    @if($hint || $error)<p id="{{ $id }}-help" class="{{ $error ? 'field-error' : 'field-hint' }}">{{ $error ?: $hint }}</p>@endif
</div>
