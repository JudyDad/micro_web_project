<x-layouts.app :title="__('applications.drafts')" :operational="true">
    <a class="back-link" href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><h1>{{ __('applications.drafts') }}</h1><p class="page-description">{{ __('applications.draft_notice') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form">
        @forelse($drafts as $draft)<div class="people-history"><h2>{{ __('applications.'.$draft['kind']) }}@if($draft['application_id']) · #{{ $draft['application_id'] }}@endif</h2><p>{{ __('applications.expires') }}: <bdi>{{ \Carbon\CarbonImmutable::createFromTimestamp($draft['expires_at'], 'Asia/Damascus')->format('Y-m-d H:i') }}</bdi></p>
        <a class="button button-primary" href="{{ route('applications.drafts.restore', ['locale' => app()->getLocale(), 'draft' => $draft['id'], 'city_id' => $cityId]) }}">{{ __('applications.restore') }}</a>
        <form method="POST" action="{{ route('applications.drafts.discard', ['locale' => app()->getLocale(), 'draft' => $draft['id']]) }}">@csrf @method('DELETE')<input type="hidden" name="city_id" value="{{ $cityId }}"><input type="hidden" name="draft_version" value="{{ $draft['version'] }}"><button class="button button-secondary" type="submit">{{ __('applications.discard') }}</button></form>
        </div>@empty<p>{{ __('people.no_entries') }}</p>@endforelse
    </section>
</x-layouts.app>
