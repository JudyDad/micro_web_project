@props(['title'])
<div {{ $attributes->class('alert') }} role="note"><span class="alert-icon" aria-hidden="true">i</span><div><strong>{{ $title }}</strong><p>{{ $slot }}</p></div></div>
