@php
    $ar = app()->isLocale('ar');
    $stateName = fn ($row) => $ar ? ($row->state_name_ar ?? $row->state_name_en) : ($row->state_name_en ?? $row->state_name_ar);
    $recovering = in_array(old('_form'), ['lifecycle-transition', 'lifecycle-reason'], true);
@endphp
<section class="card people-form" id="lifecycle"><h2>{{ __('lifecycle.title') }}</h2>
    <p>{{ __('lifecycle.current') }}: <strong>{{ $lifecycle['current'] ? ($stateName($lifecycle['current']) ?? '#'.$lifecycle['current']->id) : __('lifecycle.none') }}</strong></p>
    @if($lifecycle['canManage'])
    <details class="people-history" @if(old('_form') === 'lifecycle-transition') open @endif><summary>{{ __('lifecycle.change') }}</summary>
        <form method="POST" action="{{ route('lifecycle.transition', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
            <input type="hidden" name="_form" value="lifecycle-transition"><input type="hidden" name="version" value="{{ $lifecycle['version'] }}">
            <div class="people-fields">
                <div class="field"><label for="lifecycle-state">{{ __('lifecycle.new_state') }}</label>
                    <select class="input" id="lifecycle-state" name="state_id" required>
                        <option value="">—</option>
                        @foreach($lifecycle['targets'] as $state)<option value="{{ $state->id }}" @selected((string) old('state_id') === (string) $state->id)>{{ $ar ? ($state->c_Name_ar ?? $state->c_Name_en) : ($state->c_Name_en ?? $state->c_Name_ar) }}</option>@endforeach
                    </select><small>{{ __('lifecycle.rules') }}</small></div>
                <div class="field"><label for="lifecycle-reason">{{ __('lifecycle.reason') }}</label>
                    <select class="input" id="lifecycle-reason" name="reason_id"><option value="">{{ __('people.not_recorded') }}</option>
                        @foreach($lifecycle['reasons'] as $reason)<option value="{{ $reason->id }}" @selected((string) old('reason_id') === (string) $reason->id)>{{ $reason->c_Name }}</option>@endforeach
                    </select></div>
                <div class="field"><label for="lifecycle-note">{{ __('lifecycle.note') }}</label><textarea class="input" id="lifecycle-note" name="note" rows="3" maxlength="65535">{{ old('note') }}</textarea></div>
            </div>
            <x-ui.button type="submit">{{ __('lifecycle.change') }}</x-ui.button>
        </form>
    </details>
    @if($lifecycle['current'])
    <details class="people-history" @if(old('_form') === 'lifecycle-reason') open @endif><summary>{{ __('lifecycle.edit_current') }}</summary>
        <form method="POST" action="{{ route('lifecycle.reason', ['locale' => app()->getLocale(), 'application' => $application->id]) }}">@csrf
            <input type="hidden" name="_form" value="lifecycle-reason"><input type="hidden" name="version" value="{{ $lifecycle['version'] }}">
            <div class="people-fields">
                <div class="field"><label for="current-reason">{{ __('lifecycle.reason') }}</label>
                    <select class="input" id="current-reason" name="reason_id"><option value="">{{ __('people.not_recorded') }}</option>
                        @foreach($lifecycle['reasons'] as $reason)<option value="{{ $reason->id }}" @selected((string) ($recovering && old('_form') === 'lifecycle-reason' ? old('reason_id') : $lifecycle['current']->reason_id) === (string) $reason->id)>{{ $reason->c_Name }}</option>@endforeach
                    </select></div>
                <div class="field"><label for="current-note">{{ __('lifecycle.note') }}</label><textarea class="input" id="current-note" name="note" rows="3" maxlength="65535">{{ old('_form') === 'lifecycle-reason' ? old('note') : $lifecycle['current']->c_Note }}</textarea></div>
            </div>
            <x-ui.button type="submit">{{ __('people.save') }}</x-ui.button>
        </form>
    </details>
    @endif
    @endif
    <h3>{{ __('lifecycle.history') }}</h3>
    @if($lifecycle['history']->isEmpty())<p>{{ __('lifecycle.none') }}</p>@else
    <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('lifecycle.state') }}</th><th>{{ __('lifecycle.date') }}</th>@if($lifecycle['canManage'])<th>{{ __('lifecycle.reason') }}</th><th>{{ __('lifecycle.note') }}</th>@endif</tr></thead><tbody>
        @foreach($lifecycle['history'] as $row)
        <tr><td>{{ $stateName($row) ?? '—' }}@if($lifecycle['current'] && $lifecycle['current']->id === $row->id) · {{ __('lifecycle.current') }}@endif</td><td>{{ $row->c_Date }}</td>@if($lifecycle['canManage'])<td>{{ $row->reason_name }}</td><td>{{ $row->c_Note }}</td>@endif</tr>
        @endforeach
    </tbody></table></div>
    @endif
</section>
