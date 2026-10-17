@props(['paginator'])
@if($paginator->hasPages())
<nav class="project-pagination" aria-label="{{ __('projects.pagination') }}">
    @if($paginator->onFirstPage())<span class="button button-secondary pagination-disabled" aria-disabled="true">{{ __('pagination.previous') }}</span>@else<a class="button button-secondary" rel="prev" href="{{ $paginator->previousPageUrl() }}">{{ __('pagination.previous') }}</a>@endif
    <span>{{ __('projects.page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>
    @if($paginator->hasMorePages())<a class="button button-secondary" rel="next" href="{{ $paginator->nextPageUrl() }}">{{ __('pagination.next') }}</a>@else<span class="button button-secondary pagination-disabled" aria-disabled="true">{{ __('pagination.next') }}</span>@endif
</nav>
@endif
