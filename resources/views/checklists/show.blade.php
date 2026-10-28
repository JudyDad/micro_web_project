@php
    // Section order of the desktop CheckList_Form: beneficiary · key people · (need score, read-only, not built) · visits ·
    // experience (professional test, professional training, management training) · team opinion · committee opinion ·
    // notes · rejection message. Member rows sit with their section, the meeting date above its members.
    $h = $header;
    $v = fn ($name) => old('fields.'.$name, $h->{$name} ?? null);
    $ref = function ($key) use ($h) {
        if (old('references.'.$key) !== null) return (string) old('references.'.$key);
        $relation = collect(\Database\Schema\OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'checklist' && $r['child_columns'] === [$key]);
        return $h && $h->{$relation['state_column']} === 'resolved' ? (string) $h->{$relation['resolved_column']} : '';
    };
    $flag = fn ($name, $options) => view('checklists.flag', ['name' => $name, 'options' => $options, 'value' => (string) $v($name), 'disabled' => ! $canManage]);
    $memberRef = collect(\Database\Schema\OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'person_checklistmembers' && $r['child_columns'] === ['Member_ID']);
    $visitorChoices = array_merge($catalog['t'], $catalog['c'], $catalog['k']);
    // A date picker unless the stored value is a historical sentinel (0000-00-00), which stays a text value until explicitly edited.
    $isDate = fn ($value) => $value === null || $value === '' || (preg_match('/\A(\d{4})-(\d{2})-(\d{2})/', (string) $value, $d) && checkdate((int) $d[2], (int) $d[3], (int) $d[1]));
    $dateInput = fn (string $name) => '<input class="input" type="'.($isDate($v($name)) ? 'date' : 'text').'" name="fields['.$name.']" value="'.e($isDate($v($name)) ? substr((string) $v($name), 0, 10) : $v($name)).'">';
    $memberRows = fn (string $location) => $members->where('c_Location', $location)->values()->concat($canManage ? [null] : []);
@endphp
<x-layouts.app :title="__('lifecycle.checklist.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ app()->isLocale('ar') ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('lifecycle.checklist.title') }}</h1><p class="page-description">{{ __('lifecycle.checklist.scope') }}</p>@if($canManage && ! $needsHeaderChoice)<p><a class="button button-secondary" href="{{ route('checklists.print', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId] + ($h ? ['header' => $h->id] : [])) }}" target="_blank" rel="noopener">{{ __('lifecycle.checklist.print_open') }}</a></p>@endif</div></div>
    @include('people.messages')
    <section class="card people-form"><h2>{{ __('lifecycle.checklist.beneficiary') }}</h2>
        <p>{{ __('lifecycle.checklist.p.project_no') }}: <bdi>{{ $project->c_MP_ID }}</bdi> · {{ __('lifecycle.checklist.p.project_name') }}: {{ $project->c_MP_Name }}</p>
        @foreach($people as $person)<a class="button {{ $person->id === $personId ? '' : 'button-secondary' }}" href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $person->id]) }}">{{ $person->label }}</a> @endforeach
    </section>
    @if($needsHeaderChoice)
    <section class="card people-form"><h2>{{ __('lifecycle.checklist.choose') }}</h2><p>{{ __('lifecycle.checklist.choose_hint') }}</p>
        @foreach($headers as $candidate)<a class="button button-secondary" href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId, 'header' => $candidate->id]) }}">#{{ $candidate->id }}</a> @endforeach
    </section>
    @else
    <form method="POST" action="{{ route('checklists.save', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId]) }}">@csrf
        <input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="header_id" value="{{ $h?->id }}">

        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.key_people') }}</legend>
            @include('checklists.members', ['location' => 'k', 'slots' => $memberRows('k'), 'catalog' => $catalog, 'memberRef' => $memberRef])
        </fieldset>

        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.visits') }}</legend>
            @foreach([['c_HasHomeVisit', 'HVisit', 'c_HVisit', 'home'], ['c_HasProjectVisit', 'PVisit', 'c_PVisit', 'project']] as [$flagName, $prefix, $column, $kind])
            <h3 class="form-group-title">{{ __('lifecycle.checklist.p.'.$kind) }}</h3>
            <div class="people-fields">
            <div class="field">{!! $flag($flagName, ['1' => 'yes', '0' => 'no'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.date') }}</label>{!! $dateInput($column.'_Date') !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.time') }}</label><input class="input" name="fields[{{ $column }}_Time]" value="{{ $v($column.'_Time') }}" maxlength="50"></div>
            @foreach([1, 2] as $n)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.visitor') }} {{ $n }}</label><select class="input" name="references[{{ $prefix }}_Visitor{{ $n }}_ID]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach($visitorChoices as $member)<option value="{{ $member->id }}" @selected($ref($prefix.'_Visitor'.$n.'_ID') === (string) $member->id)>{{ $member->label }}</option>@endforeach
            </select></div>
            @endforeach
            <div class="field"><label>{{ __('lifecycle.checklist.fields.note') }}</label><input class="input" name="fields[{{ $column }}_Note]" value="{{ $v($column.'_Note') }}" maxlength="300"></div>
            </div>
            @endforeach
            <div class="people-fields"><div class="field"><label>{{ __('lifecycle.checklist.fields.visits_report') }}</label><textarea class="input" name="fields[c_VisitsReport]" rows="3" maxlength="65535">{{ $v('c_VisitsReport') }}</textarea></div></div>
        </fieldset>

        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.p.experience') }}</legend>
            <div class="people-fields">
            <div class="field">{!! $flag('c_NeedProTest', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.reason') }}</label><textarea class="input" name="fields[c_NeedProTest_Note]" rows="2" maxlength="65535">{{ $v('c_NeedProTest_Note') }}</textarea></div>
            </div>
            <h3 class="form-group-title">{{ __('lifecycle.checklist.p.pro_test') }}</h3>
            <div class="people-fields">
            <div class="field">{!! $flag('c_HasProTest', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.p.test_date') }}</label>{!! $dateInput('c_ProTest_Date') !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.examiner') }}</label><textarea class="input" name="fields[c_ProTest_Note]" rows="2" maxlength="65535">{{ $v('c_ProTest_Note') }}</textarea></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.observer') }}</label><select class="input" name="references[ProTest_User_ID]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach($visitorChoices as $member)<option value="{{ $member->id }}" @selected($ref('ProTest_User_ID') === (string) $member->id)>{{ $member->label }}</option>@endforeach
            </select></div>
            <div class="field">@php $result = (string) $v('c_ProTest_Result'); @endphp<label>{{ __('lifecycle.checklist.fields.result') }}</label><select class="input" name="fields[c_ProTest_Result]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach(['1' => __('lifecycle.checklist.result_needs'), '0' => __('lifecycle.checklist.result_good'), '-1' => __('lifecycle.checklist.na')] as $key => $label)<option value="{{ $key }}" @selected($result === (string) $key)>{{ $label }}</option>@endforeach
                @if($result !== '' && ! in_array($result, ['1', '0', '-1'], true))<option value="{{ $result }}" selected>{{ $result }}</option>@endif
            </select></div>
            </div>
            <h3 class="form-group-title">{{ __('lifecycle.checklist.p.pro_train') }}</h3>
            <div class="people-fields">
            <div class="field">{!! $flag('c_HasProTrain', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            @foreach(['c_ProTrain_Trainer' => 300, 'c_ProTrain_Place' => 300, 'c_ProTrain_BDate' => null, 'c_ProTrain_EDate' => null, 'c_ProTrain_Note' => 65535, 'c_ProTrain_Result' => 65535] as $name => $max)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.'.$name) }}</label>@if($max === null){!! $dateInput($name) !!}@else<input class="input" name="fields[{{ $name }}]" value="{{ $v($name) }}" maxlength="{{ $max }}">@endif</div>
            @endforeach
            </div>
            <h3 class="form-group-title">{{ __('lifecycle.checklist.p.mng_train') }}</h3>
            <div class="people-fields">
            <div class="field">{!! $flag('c_HasMngTrain', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            @foreach(['c_MngTrain_Trainer' => 300, 'c_MngTrain_Place' => 300, 'c_MngTrain_Date' => null, 'c_MngTrain_Result' => 65535] as $name => $max)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.'.$name) }}</label>@if($max === null){!! $dateInput($name) !!}@else<input class="input" name="fields[{{ $name }}]" value="{{ $v($name) }}" maxlength="{{ $max }}">@endif</div>
            @endforeach
            </div>
        </fieldset>

        @foreach(['t' => ['c_TeamDate', __('lifecycle.checklist.p.team_opinion')], 'c' => ['c_CommityDate', __('lifecycle.checklist.p.committee_opinion')]] as $location => [$dateField, $heading])
        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ $heading }}</legend>
            <div class="people-fields"><div class="field"><label>{{ __('lifecycle.checklist.p.meeting_date') }}</label>{!! $dateInput($dateField) !!}<small>{{ __('lifecycle.checklist.set_later') }}</small></div></div>
            @include('checklists.members', ['location' => $location, 'slots' => $memberRows($location), 'catalog' => $catalog, 'memberRef' => $memberRef])
        </fieldset>
        @endforeach

        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.p.notes') }}</legend><div class="people-fields">
            <div class="field"><label>{{ __('lifecycle.checklist.fields.notes') }}</label><textarea class="input" name="fields[c_Notes]" rows="4">{{ $v('c_Notes') }}</textarea></div>
        </div></fieldset>

        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.p.reject') }}</legend><div class="people-fields">
            <div class="field">{!! $flag('c_RejectMsg', ['1' => 'yes', '0' => 'no'])->render() !!}<small>{{ __('lifecycle.checklist.sender_hint') }}</small></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.reject_date') }}</label>{!! $dateInput('c_RejectMsgDate') !!}</div>
        </div></fieldset>

        @if($canManage)<x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>@endif
    </form>
    @endif
</x-layouts.app>
