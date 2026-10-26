@php
    $h = $header;
    $v = fn ($name) => old('fields.'.$name, $h->{$name} ?? null);
    $ref = function ($key) use ($h) {
        if (old('references.'.$key) !== null) return (string) old('references.'.$key);
        $relation = collect(\Database\Schema\OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'checklist' && $r['child_columns'] === [$key]);
        return $h && $h->{$relation['state_column']} === 'resolved' ? (string) $h->{$relation['resolved_column']} : '';
    };
    $flag = fn ($name, $options) => view('checklists.flag', ['name' => $name, 'options' => $options, 'value' => (string) $v($name), 'disabled' => ! $canManage]);
    $memberOptions = fn ($location) => $catalog[$location];
    $locations = ['k' => __('lifecycle.checklist.key_people'), 't' => __('lifecycle.checklist.team'), 'c' => __('lifecycle.checklist.committee')];
    $memberRef = collect(\Database\Schema\OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'person_checklistmembers' && $r['child_columns'] === ['Member_ID']);
@endphp
<x-layouts.app :title="__('lifecycle.checklist.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ app()->isLocale('ar') ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('lifecycle.checklist.title') }}</h1><p class="page-description">{{ __('lifecycle.checklist.scope') }}</p>@if($canManage && ! $needsHeaderChoice)<p><a class="button button-secondary" href="{{ route('checklists.print', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId] + ($h ? ['header' => $h->id] : [])) }}" target="_blank" rel="noopener">{{ __('lifecycle.checklist.print_open') }}</a></p>@endif</div></div>
    @include('people.messages')
    <section class="card people-form"><h2>{{ __('lifecycle.checklist.beneficiary') }}</h2>
        @foreach($people as $person)<a class="button {{ $person->id === $personId ? '' : 'button-secondary' }}" href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $person->id]) }}">{{ $person->label }}</a> @endforeach
    </section>
    @if($needsHeaderChoice)
    <section class="card people-form"><h2>{{ __('lifecycle.checklist.choose') }}</h2><p>{{ __('lifecycle.checklist.choose_hint') }}</p>
        @foreach($headers as $candidate)<a class="button button-secondary" href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId, 'header' => $candidate->id]) }}">#{{ $candidate->id }}</a> @endforeach
    </section>
    @else
    <form method="POST" action="{{ route('checklists.save', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId]) }}">@csrf
        <input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="header_id" value="{{ $h?->id }}">
        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.visits') }}</legend><div class="people-fields">
            @foreach([['c_HasHomeVisit', 'HVisit', 'c_HVisit'], ['c_HasProjectVisit', 'PVisit', 'c_PVisit']] as [$flagName, $prefix, $column])
            <div class="field">{!! $flag($flagName, ['1' => 'yes', '0' => 'no'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.date') }}</label><input class="input" name="fields[{{ $column }}_Date]" value="{{ $v($column.'_Date') }}" placeholder="YYYY-MM-DD"></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.time') }}</label><input class="input" name="fields[{{ $column }}_Time]" value="{{ $v($column.'_Time') }}" maxlength="50"></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.note') }}</label><input class="input" name="fields[{{ $column }}_Note]" value="{{ $v($column.'_Note') }}" maxlength="300"></div>
            @foreach([1, 2] as $n)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.visitor') }} {{ $n }}</label><select class="input" name="references[{{ $prefix }}_Visitor{{ $n }}_ID]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach(array_merge($catalog['t'], $catalog['c'], $catalog['k']) as $member)<option value="{{ $member->id }}" @selected($ref($prefix.'_Visitor'.$n.'_ID') === (string) $member->id)>{{ $member->label }}</option>@endforeach
            </select></div>
            @endforeach
            @endforeach
            <div class="field"><label>{{ __('lifecycle.checklist.fields.visits_report') }}</label><textarea class="input" name="fields[c_VisitsReport]" rows="3" maxlength="65535">{{ $v('c_VisitsReport') }}</textarea></div>
        </div></fieldset>
        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.experience') }}</legend><div class="people-fields">
            <div class="field">{!! $flag('c_NeedProTest', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.note') }}</label><textarea class="input" name="fields[c_NeedProTest_Note]" rows="2" maxlength="65535">{{ $v('c_NeedProTest_Note') }}</textarea></div>
            <div class="field">{!! $flag('c_HasProTest', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.date') }}</label><input class="input" name="fields[c_ProTest_Date]" value="{{ $v('c_ProTest_Date') }}" placeholder="YYYY-MM-DD"></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.note') }}</label><textarea class="input" name="fields[c_ProTest_Note]" rows="2" maxlength="65535">{{ $v('c_ProTest_Note') }}</textarea></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.examiner') }}</label><select class="input" name="references[ProTest_User_ID]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach(array_merge($catalog['t'], $catalog['c'], $catalog['k']) as $member)<option value="{{ $member->id }}" @selected($ref('ProTest_User_ID') === (string) $member->id)>{{ $member->label }}</option>@endforeach
            </select></div>
            <div class="field">@php $result = (string) $v('c_ProTest_Result'); @endphp<label>{{ __('lifecycle.checklist.fields.result') }}</label><select class="input" name="fields[c_ProTest_Result]"><option value="">{{ __('people.not_recorded') }}</option>
                @foreach(['1' => __('lifecycle.checklist.result_needs'), '0' => __('lifecycle.checklist.result_good'), '-1' => __('lifecycle.checklist.na')] as $key => $label)<option value="{{ $key }}" @selected($result === (string) $key)>{{ $label }}</option>@endforeach
                @if($result !== '' && ! in_array($result, ['1', '0', '-1'], true))<option value="{{ $result }}" selected>{{ $result }}</option>@endif
            </select></div>
            <div class="field">{!! $flag('c_HasProTrain', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            @foreach(['c_ProTrain_Trainer' => 300, 'c_ProTrain_Place' => 300, 'c_ProTrain_BDate' => 10, 'c_ProTrain_EDate' => 10, 'c_ProTrain_Note' => 65535, 'c_ProTrain_Result' => 65535] as $name => $max)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.'.$name) }}</label><input class="input" name="fields[{{ $name }}]" value="{{ $v($name) }}" maxlength="{{ $max }}"></div>
            @endforeach
            <div class="field">{!! $flag('c_HasMngTrain', ['1' => 'yes', '0' => 'no', '-1' => 'na'])->render() !!}</div>
            @foreach(['c_MngTrain_Trainer' => 300, 'c_MngTrain_Place' => 300, 'c_MngTrain_Date' => 10, 'c_MngTrain_Result' => 65535] as $name => $max)
            <div class="field"><label>{{ __('lifecycle.checklist.fields.'.$name) }}</label><input class="input" name="fields[{{ $name }}]" value="{{ $v($name) }}" maxlength="{{ $max }}"></div>
            @endforeach
        </div></fieldset>
        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ __('lifecycle.checklist.reviews') }}</legend><div class="people-fields">
            <div class="field"><label>{{ __('lifecycle.checklist.fields.team_date') }}</label><input class="input" name="fields[c_TeamDate]" value="{{ $v('c_TeamDate') }}" placeholder="YYYY-MM-DD"></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.committee_date') }}</label><input class="input" name="fields[c_CommityDate]" value="{{ $v('c_CommityDate') }}" placeholder="YYYY-MM-DD"></div>
            <div class="field">{!! $flag('c_RejectMsg', ['1' => 'yes', '0' => 'no'])->render() !!}<small>{{ __('lifecycle.checklist.sender_hint') }}</small></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.reject_date') }}</label><input class="input" name="fields[c_RejectMsgDate]" value="{{ $v('c_RejectMsgDate') }}" placeholder="YYYY-MM-DD"></div>
            <div class="field"><label>{{ __('lifecycle.checklist.fields.notes') }}</label><textarea class="input" name="fields[c_Notes]" rows="4">{{ $v('c_Notes') }}</textarea></div>
        </div></fieldset>
        @foreach($locations as $location => $heading)
        <fieldset class="card people-form" @disabled(! $canManage)><legend>{{ $heading }}</legend>
            @php $rows = $members->where('c_Location', $location)->values(); $slots = $rows->concat($canManage ? [null] : []); @endphp
            @foreach($slots as $row)
            @php $i = $location.'-'.($row?->id ?? 'new'); $stored = $row && $row->{$memberRef['state_column']} === 'resolved' ? (string) $row->{$memberRef['resolved_column']} : ''; @endphp
            <div class="people-fields" style="border-bottom:1px solid var(--border,#ddd);padding-bottom:.75rem;margin-bottom:.75rem">
                <input type="hidden" name="members[{{ $i }}][id]" value="{{ $row?->id }}"><input type="hidden" name="members[{{ $i }}][location]" value="{{ $location }}">
                <div class="field"><label>{{ __('lifecycle.checklist.fields.member') }}</label><select class="input" name="members[{{ $i }}][member_id]"><option value="">{{ $row ? __('people.not_recorded') : __('lifecycle.checklist.add_member') }}</option>
                    @if($stored !== '' && ! collect($catalog[$location])->contains(fn ($m) => (string) $m->id === $stored))<option value="{{ $stored }}" selected>{{ __('people.retained_reference') }} #{{ $stored }}</option>@endif
                    @foreach($catalog[$location] as $member)<option value="{{ $member->id }}" @selected($stored === (string) $member->id)>{{ $member->label }}</option>@endforeach
                </select></div>
                @if($location !== 'k')
                <div class="field"><label>{{ __('lifecycle.checklist.fields.opinion') }}</label><select class="input" name="members[{{ $i }}][opinion]">
                    @foreach(['-1' => __('lifecycle.checklist.na'), '1' => __('lifecycle.checklist.for'), '0' => __('lifecycle.checklist.against')] as $key => $label)<option value="{{ $key }}" @selected((string) ($row?->c_Opinion ?? '-1') === (string) $key)>{{ $label }}</option>@endforeach
                </select></div>
                @else
                <div class="field"><label>{{ __('lifecycle.checklist.fields.person_name') }}</label><input class="input" name="members[{{ $i }}][name_notes]" value="{{ $row?->c_NameNotes }}" maxlength="400"></div>
                <div class="field"><label>{{ __('lifecycle.checklist.fields.role') }}</label><input class="input" name="members[{{ $i }}][role_notes]" value="{{ $row?->c_RoleNotes }}" maxlength="500"></div>
                @endif
                <div class="field"><label>{{ __('lifecycle.checklist.fields.note') }}</label><textarea class="input" name="members[{{ $i }}][notes]" rows="2" maxlength="65535">{{ $row?->c_Notes }}</textarea></div>
            </div>
            @endforeach
        </fieldset>
        @endforeach
        @if($canManage)<x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>@endif
    </form>
    @endif
</x-layouts.app>
