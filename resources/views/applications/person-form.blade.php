<form method="POST" action="{{ route('applications.person', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
    @include('applications.context', ['kind' => 'people'])
    @if($row)<input type="hidden" name="link_id" value="{{ $row->id }}">@endif
    @php
        $personId = old('_form') === $prefix ? old('person_id') : ($row->{$personReferenceColumn} ?? null);
        $role = old('_form') === $prefix ? old('role') : (($row->c_PersonType ?? '') === 'شريك' ? 'partner' : 'beneficiary');
    @endphp
    <div class="people-fields">
        <x-ui.search-select :id="$prefix.'-person'" name="person_id" :label="__('applications.select_person')" :url="route('people.lookup', ['locale' => app()->getLocale()])" :params="['city_id' => $cityId]"
            :value="$personId" :value-label="$personLabels[(int) $personId] ?? null" :hint="__('applications.person_search')" :error="old('_form') === $prefix ? $errors->first('person_id') : null" />
    <div class="field"><label for="{{ $prefix }}-role">{{ __('applications.role') }}</label><select class="input" id="{{ $prefix }}-role" name="role"><option value="beneficiary" @selected($role === 'beneficiary')>{{ __('applications.beneficiary') }}</option><option value="partner" @selected($role === 'partner')>{{ __('applications.partner') }}</option></select></div></div>
    @include('applications.fields', ['fields' => $metadata['people'], 'options' => []])
    @include('applications.actions')
</form>

