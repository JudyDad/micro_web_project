@props(['variant' => 'primary', 'type' => 'button'])
<button type="{{ $type }}" {{ $attributes->class(['button', 'button-secondary' => $variant === 'secondary', 'button-primary' => $variant !== 'secondary']) }}>{{ $slot }}</button>
