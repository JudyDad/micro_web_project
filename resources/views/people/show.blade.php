<x-layouts.app :title="$person->c_P_FirstName.' '.$person->c_P_LastName" :operational="true">
    <a class="back-link" href="{{ route('people.index', ['locale' => app()->getLocale()]) }}">{{ __('people.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ app()->isLocale('ar') ? $person->city_name_ar : $person->city_name_en }}</p><h1>{{ $person->c_P_FirstName }} {{ $person->c_P_LastName }}</h1></div></div>
    <p class="field-hint"><a class="record-link" href="{{ route('details.family.export', ['locale' => app()->getLocale(), 'person' => $person->id, 'format' => 'xlsx']) }}">{{ __('search.export_family') }}</a> · <a class="record-link" href="{{ route('details.family.export', ['locale' => app()->getLocale(), 'person' => $person->id, 'format' => 'csv']) }}">{{ __('reports.export_csv') }}</a></p>
    @include('people.messages')
    @if($canManage)
    <details class="card people-form" open><summary>{{ __('people.person') }}</summary>
        <form method="POST" action="{{ route('people.update', ['locale' => app()->getLocale(), 'person' => $person->id]) }}">
            @csrf @method('PUT')@include('people.version', ['formKey' => 'person'])
            @include('people.fields', ['fields' => $metadata['person'], 'options' => $options['person'], 'row' => $person, 'prefix' => 'person', 'groups' => true])
            <label class="people-confirm"><input type="checkbox" name="confirm_duplicate" value="1"> {{ __('people.confirm_duplicate') }}</label>
            <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
        </form>
    </details>
    @else
    <section class="card"><dl class="project-details">@foreach($metadata['person'] as $name => $field)<div><dt>{{ __('people.fields.'.$field['label']) }}</dt><dd class="preserve-text">{{ $person->{$name} ?? '—' }}</dd></div>@endforeach
@include('people.references', ['row' => $person])</dl></section>
    @endif
    <section class="card people-form"><h2>{{ __('people.families') }}</h2>
        @forelse($families as $membership)<div class="people-history"><strong>{{ $membership->c_F_Number ?? '—' }}</strong><dl class="project-details">@foreach($metadata['families'] as $name => $field)<div><dt>{{ __('people.fields.'.$field['label']) }}</dt><dd>{{ $membership->{$name} ?? '—' }}</dd></div>@endforeach
@include('people.references', ['row' => $membership])</dl>
            @if($canManage && $membership->familyId)
            <details><summary>{{ __('people.edit_membership') }}</summary><form method="POST" action="{{ route('people.family', ['locale' => app()->getLocale(), 'person' => $person->id]) }}">@csrf
                @include('people.version', ['formKey' => 'membership-'.$membership->id])
                <input type="hidden" name="membership_id" value="{{ $membership->id }}"><input type="hidden" name="family_id" value="{{ $membership->familyId }}">
                @include('people.provider', ['providerForm' => 'membership-'.$membership->id, 'providerId' => $membership->providerId])
                @include('people.fields', ['fields' => $metadata['families'], 'options' => $options['families'], 'row' => $membership, 'prefix' => 'membership-'.$membership->id])
                <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
            </form></details>
            @endif
        </div>@empty<p>{{ __('people.no_entries') }}</p>@endforelse
        @if($canManage)
        <form method="GET" action="{{ route('people.show', ['locale' => app()->getLocale(), 'person' => $person->id]) }}"><div class="field"><label for="family-search">{{ __('people.find_family') }}</label><input class="input" id="family-search" name="family_search" value="{{ $familySearch }}" maxlength="50"></div><x-ui.button type="submit">{{ __('projects.apply_filters') }}</x-ui.button></form>
        @if($familyChoices->isNotEmpty())
        <form method="POST" action="{{ route('people.family', ['locale' => app()->getLocale(), 'person' => $person->id]) }}">@csrf
@include('people.version', ['formKey' => 'membership'])
            <div class="field"><label for="family-choice">{{ __('people.families') }}</label><select class="input" id="family-choice" name="family_id">@foreach($familyChoices as $family)<option value="{{ $family->id }}" @selected(old('_form') === 'membership' && (string) old('family_id') === (string) $family->id)>{{ $family->c_F_Number }} · #{{ $family->id }}</option>@endforeach</select></div>
            @include('people.provider', ['providerForm' => 'membership', 'providerId' => null])
            @include('people.fields', ['fields' => $metadata['families'], 'options' => $options['families'], 'row' => null, 'prefix' => 'membership'])
            <x-ui.button type="submit">{{ __('people.link_family') }}</x-ui.button>
        </form>
        @elseif($familySearch !== '')<p>{{ __('people.no_entries') }}</p>@endif
        @endif
    </section>
    @foreach(['education', 'workexp', 'course'] as $kind)
    <section class="card people-form"><h2>{{ __('people.'.$kind) }}</h2>
        @forelse($$kind as $detail)
        <details class="people-history"><summary>{{ __('people.entry') }} {{ $loop->iteration }}</summary>
            @if($canManage)<form method="POST" action="{{ route('people.detail', ['locale' => app()->getLocale(), 'person' => $person->id, 'kind' => $kind]) }}">@csrf
@include('people.version', ['formKey' => $kind.'-'.$detail->id])<input type="hidden" name="detail_id" value="{{ $detail->id }}">
                @include('people.fields', ['fields' => $metadata[$kind], 'options' => $options[$kind], 'row' => $detail, 'prefix' => $kind.'-'.$detail->id])
                <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
            </form>@else<dl class="project-details">@foreach($metadata[$kind] as $name => $field)<div><dt>{{ __('people.fields.'.$field['label']) }}</dt><dd class="preserve-text">{{ $detail->{$name} ?? '—' }}</dd></div>@endforeach
@include('people.references', ['row' => $detail])</dl>@endif
        </details>
        @empty<p>{{ __('people.no_entries') }}</p>@endforelse
        @if($canManage)<details class="people-history"><summary>{{ __('people.add_entry') }}</summary>
            <form method="POST" action="{{ route('people.detail', ['locale' => app()->getLocale(), 'person' => $person->id, 'kind' => $kind]) }}">@csrf
@include('people.version', ['formKey' => $kind.'-new'])
                @include('people.fields', ['fields' => $metadata[$kind], 'options' => $options[$kind], 'row' => null, 'prefix' => $kind.'-new'])
                <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
            </form>
        </details>@endif
    </section>
    @endforeach
</x-layouts.app>
