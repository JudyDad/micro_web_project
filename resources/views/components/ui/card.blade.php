@props(['title', 'eyebrow' => null])
<section {{ $attributes->class('card') }}>
    <div class="card-heading">@if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif<h2>{{ $title }}</h2></div>
    {{ $slot }}
</section>
