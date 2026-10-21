<x-layouts.app :title="$application->c_MP_Name" :operational="true">
    <a class="back-link" href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ app()->isLocale('ar') ? $application->city_name_ar : $application->city_name_en }} · {{ $application->c_MP_ID }}</p><h1>{{ $application->c_MP_Name }}</h1><p>{{ __('applications.state') }}: {{ in_array($application->c_ApplicationState, ['zero', 'project'], true) ? __('applications.states.'.$application->c_ApplicationState) : $application->c_ApplicationState }}</p><p class="page-description">{{ __('applications.boundary') }}</p></div></div>
    @include('people.messages')
    @include('lifecycle.panel')
    @if($assessees->isNotEmpty())<section class="card people-form"><h2>{{ __('lifecycle.checklist_link') }}</h2>
        @foreach($assessees as $assessee)<a class="button button-secondary" href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $application->id, 'person' => $assessee->id]) }}">{{ $assessee->label }}</a> @endforeach
    </section>@endif
    @if($financeLinks !== [])<section class="card people-form"><h2>{{ __('finance.title') }}</h2>
        @foreach(array_keys($financeLinks) as $routeName)<a class="button button-secondary" href="{{ route($routeName, ['locale' => app()->getLocale(), 'application' => $application->id]) }}">{{ __('finance.links.'.explode('.', $routeName)[0]) }}</a> @endforeach
    </section>@endif
    @if($canManage)<p><a class="button button-secondary" href="{{ route('applications.drafts', ['locale' => app()->getLocale(), 'city_id' => $cityId]) }}">{{ __('applications.drafts') }}</a></p>@endif
    <details class="card people-form" open><summary>{{ __('applications.application') }}</summary>
    @if($canManage)<form method="POST" action="{{ route('applications.update', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
        @include('applications.context', ['kind' => 'application', 'prefix' => 'application'])
        @include('applications.fields', ['fields' => $metadata['application'], 'options' => $options['application'], 'row' => $application, 'prefix' => 'application'])
        @include('applications.actions')
    </form>@else @include('applications.read', ['fields' => $metadata['application'], 'row' => $application]) @endif
    </details>
    <section class="card people-form"><h2>{{ __('applications.people') }}</h2>
        @foreach($people as $link)
        <details class="people-history" @if(old('_form') === 'people-'.$link->id || collect(['loss', 'illness', 'asset', 'aid', 'help', 'homepower'])->contains(fn ($kind) => str_starts_with((string) old('_form'), $kind.'-'.$link->id.'-'))) open @endif><summary>{{ $link->referenceDescriptions['Person_ID'] ?? '#'.$link->id }} · {{ $link->c_PersonType }}</summary>
        @if($canLinkPeople && in_array($link->c_PersonType, ['مستفيد', 'شريك'], true))
            @include('applications.person-form', ['row' => $link, 'prefix' => 'people-'.$link->id])
        @else @include('applications.read', ['fields' => $metadata['people'], 'row' => $link]) @endif
        @if($canLinkPeople && isset($applicantDetails[$link->id])) @include('applications.applicant-details') @endif
        </details>
        @endforeach
        @if($canLinkPeople)<details class="people-history" @if(old('_form') === 'people-new') open @endif><summary>{{ __('applications.link_person') }}</summary>@include('applications.person-form', ['row' => null, 'prefix' => 'people-new'])</details>@endif
    </section>
    @foreach(['agriculture', 'rehab', 'rehab_answers', 'powersource', 'risk', 'material'] as $kind)
    <section class="card people-form"><h2>{{ __('applications.'.$kind) }}</h2>
        @forelse($$kind as $detail)
        <details class="people-history" @if(old('_form') === $kind.'-'.$detail->id) open @endif><summary>{{ __('people.entry') }} #{{ $detail->id }}@if(in_array($kind, ['agriculture', 'rehab_answers'], true)) · {{ $detail->referenceDescriptions['Question_ID'] ?? '' }} — {{ $detail->referenceDescriptions['Answer_ID'] ?? '' }}@endif @if($kind === 'rehab_answers') · {{ $detail->referenceDescriptions['MicroRehab_ID'] ?? '' }}@endif</summary>
        @if($canManage) @include('applications.detail-form', ['row' => $detail, 'prefix' => $kind.'-'.$detail->id])
        @else @include('applications.read', ['fields' => $metadata[$kind], 'row' => $detail]) @endif
        </details>@empty<p>{{ __('people.no_entries') }}</p>@endforelse
        @if($canManage && ($kind !== 'rehab_answers' || $rehab->isNotEmpty()))<details class="people-history" @if(old('_form') === $kind.'-new') open @endif><summary>{{ __('people.add_entry') }}</summary>@include('applications.detail-form', ['row' => null, 'prefix' => $kind.'-new'])</details>@endif
    </section>
    @endforeach
    @if($canManage && $application->c_ApplicationState === 'zero')
    <section class="card people-form"><h2>{{ __('applications.complete') }}</h2><p>{{ __('applications.complete_hint') }}</p>
        <form method="POST" action="{{ route('applications.complete', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
            <input type="hidden" name="city_id" value="{{ $cityId }}"><input type="hidden" name="version" value="{{ old('_form') === 'complete' ? old('version', '') : $version }}"><input type="hidden" name="_form" value="complete">
            <x-ui.button type="submit">{{ __('applications.complete') }}</x-ui.button>
        </form>
    </section>
    @endif
</x-layouts.app>
