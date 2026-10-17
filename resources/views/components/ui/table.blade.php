@props(['caption'])
<div class="table-scroll" tabindex="0" role="region" aria-label="{{ $caption }}"><table><caption class="sr-only">{{ $caption }}</caption>{{ $slot }}</table></div>
