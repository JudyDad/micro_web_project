<x-layouts.app :title="__('applications.create')" :operational="true">
    <a class="back-link" href="{{ route('applications.index', ['locale' => app()->getLocale()]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><h1>{{ __('applications.create') }}</h1><p class="page-description">{{ __('applications.boundary') }}</p></div></div>
    @include('people.messages')
    @php
        // One form, as the desktop Application_New_Form: applicant, family book and project, one save.
        $recovering = old('_form') === 'application';
        $defaultMode = $canCreatePeople ? 'new' : 'existing';
        $mode = $recovering ? old('beneficiary_mode', $defaultMode) : $defaultMode;
        $mode = $canCreatePeople && $mode === 'new' ? 'new' : 'existing';
        $recoveredBeneficiary = $recovering ? (string) old('beneficiary_id', '') : '';
        $recoveredFamilyId = $recovering ? (string) old('family.id', '') : '';
        $requirements = \App\Services\Applications\ApplicationService::applicantRequirements();
    @endphp
    <section class="card people-form"><form method="POST" action="{{ route('applications.store', ['locale' => app()->getLocale()]) }}" x-data="{ mode: @js($mode) }">@csrf
        <input type="hidden" name="create_token" value="{{ $createToken }}">
        <input type="hidden" name="stage" value="preliminary">
        @include('applications.context', ['kind' => 'application', 'prefix' => 'application'])

        <fieldset class="intake-section">
            <legend>{{ __('applications.section_person') }}</legend>
            <div class="intake-mode" role="radiogroup" aria-label="{{ __('applications.beneficiary') }}">
                @if($canCreatePeople)
                <label><input type="radio" name="beneficiary_mode" value="new" x-model="mode" @checked($mode === 'new')> {{ __('applications.beneficiary_new') }}</label>
                @else
                <input type="hidden" name="beneficiary_mode" value="existing"><p class="field-hint">{{ __('applications.people_manage_needed') }}</p>
                @endif
                <label><input type="radio" name="beneficiary_mode" value="existing" x-model="mode" @checked($mode === 'existing')> {{ __('applications.beneficiary_existing') }}</label>
            </div>

            <div x-show="mode === 'existing'" @if($mode !== 'existing') x-cloak @endif>
                <div class="people-fields"><div class="field"><label for="beneficiary">{{ __('applications.beneficiary') }}</label><select class="input" id="beneficiary" name="beneficiary_id"><option value="">{{ __('applications.select_person') }}</option>
                    @if($recoveredBeneficiary !== '' && ! $persons->contains(fn ($person) => (string) $person->id === $recoveredBeneficiary))<option value="{{ $recoveredBeneficiary }}" selected>{{ __('people.retained_reference') }} #{{ $recoveredBeneficiary }}</option>@endif
                    @foreach($persons as $person)<option value="{{ $person->id }}" @selected($recoveredBeneficiary === (string) $person->id)>{{ $person->label }}</option>@endforeach</select></div></div>
                <p class="field-hint">{{ __('applications.person_limit') }} <a href="{{ route('people.index', ['locale' => app()->getLocale()]) }}">{{ __('people.title') }}</a></p>
            </div>

            @if($canCreatePeople)
            <div x-show="mode === 'new'" @if($mode !== 'new') x-cloak @endif>
                <p class="field-hint">{{ __('applications.required_hint') }}</p>
                @include('applications.applicant-fields', ['fields' => $personFields, 'options' => $personOptions, 'requirements' => $requirements])
                @if($personCandidates->isNotEmpty())
                <div class="intake-matches" role="alert">
                    <p><strong>{{ __('applications.matching_people') }}</strong></p>
                    <ul>@foreach($personCandidates as $candidate)<li><a href="{{ route('people.show', ['locale' => app()->getLocale(), 'person' => $candidate->id]) }}">{{ trim(($candidate->c_P_FirstName ?? '').' '.($candidate->c_P_LastName ?? '')) }} · #{{ $candidate->id }}</a> · {{ $candidate->c_P_NationalNumber }}</li>@endforeach</ul>
                    <label class="people-confirm"><input type="checkbox" name="person[confirm_duplicate]" value="1"> {{ __('applications.confirm_duplicate_person') }}</label>
                </div>
                @endif
            </div>
            @endif
        </fieldset>

        <fieldset class="intake-section">
            <legend>{{ __('applications.section_family') }}</legend>
            <div class="people-fields"><div class="field">
                <label for="family-number">{{ __('applications.family_number') }} <span class="required-mark" x-show="mode === 'new'">*</span></label>
                <input class="input" id="family-number" name="family[number]" type="text" maxlength="50" value="{{ $recovering ? old('family.number') : '' }}">
            </div></div>
            @if($familyCandidates->isNotEmpty())
            <div class="intake-matches" role="alert">
                <p><strong>{{ __('applications.matching_families') }}</strong></p>
                <div class="intake-choices">
                    <label><input type="radio" name="family[id]" value="" @checked($recoveredFamilyId === '')> {{ __('applications.family_separate') }}</label>
                    @foreach($familyCandidates as $candidate)
                    <label><input type="radio" name="family[id]" value="{{ $candidate->id }}" @checked($recoveredFamilyId === (string) $candidate->id)> {{ __('applications.family_reuse') }} · #{{ $candidate->id }} · {{ $candidate->c_F_Number }}</label>
                    @endforeach
                </div>
                <label class="people-confirm"><input type="checkbox" name="family[confirm_duplicate]" value="1"> {{ __('applications.confirm_duplicate_family') }}</label>
            </div>
            @elseif($recoveredFamilyId !== '')
            <input type="hidden" name="family[id]" value="{{ $recoveredFamilyId }}">
            @endif
        </fieldset>

        <fieldset class="intake-section">
            <legend>{{ __('applications.section_project') }}</legend>
            @include('applications.fields', ['fields' => $coreFields, 'options' => $coreOptions, 'row' => null, 'prefix' => 'application'])
            <details class="people-history"><summary>{{ __('applications.additional_details') }}</summary>
            @include('applications.fields', ['fields' => array_diff_key($fields, $coreFields), 'options' => array_diff_key($options, $coreOptions), 'row' => null, 'prefix' => 'application'])
            </details>
        </fieldset>

        @include('applications.actions')
    </form></section>
</x-layouts.app>
