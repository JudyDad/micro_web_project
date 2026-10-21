<form method="POST" action="{{ route('applications.person', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
    @include('applications.context', ['kind' => 'people'])
    @if($row)<input type="hidden" name="link_id" value="{{ $row->id }}">@endif
    @php
        $personId = old('_form') === $prefix ? old('person_id') : ($row->{$personReferenceColumn} ?? null);
        $role = old('_form') === $prefix ? old('role') : (($row->c_PersonType ?? '') === 'شريك' ? 'partner' : 'beneficiary');
    @endphp
    <div class="people-fields"><div class="field"><label for="{{ $prefix }}-person">{{ __('applications.select_person') }}</label><select class="input" id="{{ $prefix }}-person" name="person_id"><option value="">{{ __('applications.select_person') }}</option>
        @if($personId && ! $persons->contains(fn ($p) => (string) $p->id === (string) $personId))<option value="{{ $personId }}" selected>{{ __('people.retained_reference') }} #{{ $personId }}</option>@endif
        @foreach($persons as $person)<option value="{{ $person->id }}" @selected((string) $personId === (string) $person->id)>{{ $person->label }}</option>@endforeach
    </select></div><div class="field"><label for="{{ $prefix }}-role">{{ __('applications.role') }}</label><select class="input" id="{{ $prefix }}-role" name="role"><option value="beneficiary" @selected($role === 'beneficiary')>{{ __('applications.beneficiary') }}</option><option value="partner" @selected($role === 'partner')>{{ __('applications.partner') }}</option></select></div></div>
    @include('applications.fields', ['fields' => $metadata['people'], 'options' => []])
    @include('applications.actions')
</form>

