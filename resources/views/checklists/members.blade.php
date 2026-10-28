{{-- Member rows of one checklist section (k = key people, t = team, c = committee), as the desktop grids: name/member · position/role · opinion · reason. --}}
@foreach($slots as $row)
@php $i = $location.'-'.($row?->id ?? 'new'); $stored = $row && $row->{$memberRef['state_column']} === 'resolved' ? (string) $row->{$memberRef['resolved_column']} : ''; @endphp
<div class="people-fields member-row">
    <input type="hidden" name="members[{{ $i }}][id]" value="{{ $row?->id }}"><input type="hidden" name="members[{{ $i }}][location]" value="{{ $location }}">
    <div class="field"><label>{{ __('lifecycle.checklist.fields.member') }}</label><select class="input" name="members[{{ $i }}][member_id]"><option value="">{{ $row ? __('people.not_recorded') : __('lifecycle.checklist.add_member') }}</option>
        @if($stored !== '' && ! collect($catalog[$location])->contains(fn ($m) => (string) $m->id === $stored))<option value="{{ $stored }}" selected>{{ __('people.retained_reference') }} #{{ $stored }}</option>@endif
        @foreach($catalog[$location] as $member)<option value="{{ $member->id }}" @selected($stored === (string) $member->id)>{{ $member->label }}</option>@endforeach
    </select></div>
    @if($location !== 'k')
    <div class="field"><label>{{ __('lifecycle.checklist.fields.opinion') }}</label><select class="input" name="members[{{ $i }}][opinion]">
        @foreach(['-1' => __('lifecycle.checklist.na'), '1' => __('lifecycle.checklist.for'), '0' => __('lifecycle.checklist.against')] as $key => $label)<option value="{{ $key }}" @selected((string) ($row?->c_Opinion ?? '-1') === (string) $key)>{{ $label }}</option>@endforeach
    </select></div>
    <div class="field"><label>{{ __('lifecycle.checklist.p.reason') }}</label><textarea class="input" name="members[{{ $i }}][notes]" rows="2" maxlength="65535">{{ $row?->c_Notes }}</textarea></div>
    @else
    <div class="field"><label>{{ __('lifecycle.checklist.fields.person_name') }}</label><input class="input" name="members[{{ $i }}][name_notes]" value="{{ $row?->c_NameNotes }}" maxlength="400"></div>
    <div class="field"><label>{{ __('lifecycle.checklist.fields.role') }}</label><input class="input" name="members[{{ $i }}][role_notes]" value="{{ $row?->c_RoleNotes }}" maxlength="500"></div>
    <div class="field"><label>{{ __('lifecycle.checklist.p.opinion') }}</label><textarea class="input" name="members[{{ $i }}][notes]" rows="2" maxlength="65535">{{ $row?->c_Notes }}</textarea></div>
    @endif
</div>
@endforeach
