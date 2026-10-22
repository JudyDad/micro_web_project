@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $mine = old('_form') === 'meeting';
    $pick = fn (string $key, $stored) => $mine ? old($key, $stored) : $stored;
    $questions = $catalog['questions']->groupBy('section');
    $rowsOf = function (string $key, array $blank, int $extra, array $stored) use ($pick) {
        $rows = $pick($key, $stored);
        $rows = is_array($rows) ? array_values($rows) : [];
        for ($i = 0; $i < $extra; $i++) {
            $rows[] = $blank;
        }

        return $rows;
    };
    $defaultChallenges = $meeting === null ? array_map(fn ($name) => ['name' => $name, 'count' => '', 'solution' => ''], $defaultChallenges) : [];
@endphp
<x-layouts.app :title="__('field.meeting.title')" :operational="true">
    <a class="back-link" href="{{ route('meetings.index', ['locale' => $locale]) }}">{{ __('field.meeting.title') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $city->name_ar : $city->name_en }}</p><h1>{{ __('field.meeting.title') }}</h1><p class="page-description">{{ __('field.meeting.scope') }}</p></div></div>
    @include('people.messages')
    <form method="POST" action="{{ route('meetings.save', ['locale' => $locale]) }}">@csrf
        <input type="hidden" name="_form" value="meeting"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="meeting_id" value="{{ $meeting?->id }}"><input type="hidden" name="city_id" value="{{ $city->id }}">
        <fieldset @disabled(! $canManage)>
        <section class="card people-form"><h2>{{ __('field.meeting.header') }}</h2>
            <div class="people-fields">
                <div class="field"><label for="career_id">{{ __('field.meeting.career') }}</label><select class="input" id="career_id" name="career_id" required><option value="">—</option>
                    @foreach($careers as $career)<option value="{{ $career->id }}" @selected((string) $pick('career_id', $values['career_id']) === (string) $career->id)>{{ $career->c_Name }}</option>@endforeach</select></div>
                <div class="field"><label for="date">{{ __('field.meeting.date') }}</label><input class="input" id="date" type="date" name="date" value="{{ $pick('date', $values['date']) }}" required></div>
                <div class="field"><label for="place">{{ __('field.meeting.place') }}</label><input class="input" id="place" name="place" maxlength="200" value="{{ $pick('place', $values['place']) }}" required></div>
                <div class="field"><label for="invited">{{ __('field.meeting.invited') }}</label><input class="input" id="invited" name="invited" inputmode="numeric" value="{{ $pick('invited', $values['invited']) }}" required></div>
                <div class="field"><label for="attendance">{{ __('field.meeting.attendance') }}</label><input class="input" id="attendance" name="attendance" inputmode="numeric" value="{{ $pick('attendance', $values['attendance']) }}" required></div>
            </div>
            <p><small>{{ __('field.meeting.totals_hint') }}</small></p>
        </section>

        @foreach($catalog['sections'] as $section)
        @continue(! isset($questions[$section->id]))
        <section class="card people-form"><h2>{{ $section->c_Name_Ar }}</h2>
            @foreach($questions[$section->id] as $question)
            @php $type = (string) $question->c_Answer_Type; @endphp
            <div class="field" id="question-{{ $question->id }}"><p><strong>{{ $question->c_Number }}) {{ $question->c_Text_Ar }}</strong></p>
                @if($type === 'option_number')
                    @foreach($question->options as $option)
                    <div class="field"><label for="oc-{{ $option->id }}">{{ $option->c_Text_Ar }}</label><input class="input" id="oc-{{ $option->id }}" name="oc[{{ $option->id }}]" inputmode="numeric" value="{{ $pick('oc.'.$option->id, $values['oc'][$option->id] ?? '') }}">
                        @if((int) $option->c_Is_Other === 1 || (int) $option->c_Has_Details === 1)<input class="input" name="ot[{{ $option->id }}]" aria-label="{{ __('field.meeting.details') }}" placeholder="{{ __('field.meeting.details') }}" value="{{ $pick('ot.'.$option->id, $values['ot'][$option->id] ?? '') }}">@endif</div>
                    @endforeach
                @elseif($type === 'multi_option')
                    @foreach($question->options as $option)
                    <div class="field"><label class="city-filter-option"><input type="checkbox" name="os[{{ $option->id }}]" value="1" @checked((string) $pick('os.'.$option->id, $values['os'][$option->id] ?? '') === '1')>{{ $option->c_Text_Ar }}</label>
                        @if((int) $option->c_Is_Other === 1 || (int) $option->c_Has_Details === 1)<input class="input" name="ot[{{ $option->id }}]" aria-label="{{ __('field.meeting.details') }}" placeholder="{{ __('field.meeting.details') }}" value="{{ $pick('ot.'.$option->id, $values['ot'][$option->id] ?? '') }}">@endif</div>
                    @endforeach
                @elseif($type === 'text')
                    <textarea class="input" name="tx[{{ $question->id }}]" rows="3" maxlength="65535" aria-label="{{ $question->c_Number }}">{{ $pick('tx.'.$question->id, $values['tx'][$question->id] ?? '') }}</textarea>
                @elseif($type === 'competition')
                    @foreach($levels as $order => $level)
                    <div class="field"><label for="cc-{{ $order }}">{{ $level }}</label><input class="input" id="cc-{{ $order }}" name="cc[{{ $order }}]" inputmode="numeric" value="{{ $pick('cc.'.$order, $values['cc'][$order] ?? '') }}">
                        <input class="input" name="cr[{{ $order }}]" aria-label="{{ __('field.meeting.reason') }}" placeholder="{{ __('field.meeting.reason') }}" value="{{ $pick('cr.'.$order, $values['cr'][$order] ?? '') }}"></div>
                    @endforeach
                @elseif($type === 'season_table')
                    @foreach($rowsOf('season', ['count' => '', 'from' => '', 'to' => '', 'reason' => ''], 3, $values['season']) as $i => $row)
                    <div class="people-fields"><input class="input" name="season[{{ $i }}][count]" aria-label="{{ __('field.meeting.count') }}" placeholder="{{ __('field.meeting.count') }}" value="{{ $row['count'] ?? '' }}"><input class="input" name="season[{{ $i }}][from]" aria-label="{{ __('field.meeting.from') }}" placeholder="{{ __('field.meeting.from') }}" value="{{ $row['from'] ?? '' }}"><input class="input" name="season[{{ $i }}][to]" aria-label="{{ __('field.meeting.to') }}" placeholder="{{ __('field.meeting.to') }}" value="{{ $row['to'] ?? '' }}"><input class="input" name="season[{{ $i }}][reason]" aria-label="{{ __('field.meeting.reason') }}" placeholder="{{ __('field.meeting.reason') }}" value="{{ $row['reason'] ?? '' }}"></div>
                    @endforeach
                @elseif($type === 'brand_table')
                    <textarea class="input" name="brands" rows="4" maxlength="65535" aria-label="{{ $question->c_Number }}" placeholder="{{ __('field.meeting.brands_hint') }}">{{ $pick('brands', $values['brands']) }}</textarea>
                @elseif($type === 'equipment_table')
                    @foreach($rowsOf('equipment', ['spec' => '', 'items' => ''], 3, $values['equipment']) as $i => $row)
                    <div class="people-fields"><input class="input" name="equipment[{{ $i }}][spec]" aria-label="{{ __('field.meeting.spec') }}" placeholder="{{ __('field.meeting.spec') }}" value="{{ $row['spec'] ?? '' }}"><input class="input" name="equipment[{{ $i }}][items]" aria-label="{{ __('field.meeting.items') }}" placeholder="{{ __('field.meeting.items') }}" value="{{ $row['items'] ?? '' }}"></div>
                    @endforeach
                @elseif($type === 'challenge_table')
                    @foreach($rowsOf('challenge', ['name' => '', 'count' => '', 'solution' => ''], 2, $values['challenge'] ?: $defaultChallenges) as $i => $row)
                    <div class="people-fields"><input class="input" name="challenge[{{ $i }}][name]" aria-label="{{ __('field.meeting.challenge') }}" placeholder="{{ __('field.meeting.challenge') }}" value="{{ $row['name'] ?? '' }}"><input class="input" name="challenge[{{ $i }}][count]" aria-label="{{ __('field.meeting.count') }}" placeholder="{{ __('field.meeting.count') }}" value="{{ $row['count'] ?? '' }}"><input class="input" name="challenge[{{ $i }}][solution]" aria-label="{{ __('field.meeting.solution') }}" placeholder="{{ __('field.meeting.solution') }}" value="{{ $row['solution'] ?? '' }}"></div>
                    @endforeach
                @elseif($type === 'participant_table')
                    @foreach($rowsOf('part.'.$question->id, ['project' => '', 'name' => '', 'speciality' => ''], 2, $values['part'][$question->id] ?? []) as $i => $row)
                    <div class="people-fields"><input class="input" name="part[{{ $question->id }}][{{ $i }}][project]" aria-label="{{ __('field.meeting.project') }}" placeholder="{{ __('field.meeting.project') }}" value="{{ $row['project'] ?? '' }}"><input class="input" name="part[{{ $question->id }}][{{ $i }}][name]" aria-label="{{ __('field.meeting.name') }}" placeholder="{{ __('field.meeting.name') }}" value="{{ $row['name'] ?? '' }}"><input class="input" name="part[{{ $question->id }}][{{ $i }}][speciality]" aria-label="{{ __('field.meeting.speciality') }}" placeholder="{{ __('field.meeting.speciality') }}" value="{{ $row['speciality'] ?? '' }}"></div>
                    @endforeach
                @endif
            </div>
            @endforeach
        </section>
        @endforeach

        <section class="card people-form"><h2>{{ __('field.meeting.minutes') }}</h2>
            <p><strong>{{ __('field.meeting.attendees') }}</strong> <small>{{ __('field.meeting.attendees_hint') }}</small></p>
            @foreach($rowsOf('attendees', ['project' => '', 'name' => '', 'speciality' => ''], 3, $values['attendees']) as $i => $row)
            <div class="people-fields"><input class="input" name="attendees[{{ $i }}][project]" aria-label="{{ __('field.meeting.project') }}" placeholder="{{ __('field.meeting.project') }}" value="{{ $row['project'] ?? '' }}"><input class="input" name="attendees[{{ $i }}][name]" aria-label="{{ __('field.meeting.name') }}" placeholder="{{ __('field.meeting.name') }}" value="{{ $row['name'] ?? '' }}"><input class="input" name="attendees[{{ $i }}][speciality]" aria-label="{{ __('field.meeting.speciality') }}" placeholder="{{ __('field.meeting.speciality') }}" value="{{ $row['speciality'] ?? '' }}"></div>
            @endforeach
            @foreach($catalog['sections'] as $section)
            <div class="field"><label for="minute-{{ $section->id }}">{{ $section->c_Name_Ar }}</label><textarea class="input" id="minute-{{ $section->id }}" name="minutes[{{ $section->id }}]" rows="4" maxlength="65535">{{ $pick('minutes.'.$section->id, $values['minutes'][$section->id] ?? '') }}</textarea></div>
            @endforeach
        </section>
        </fieldset>
        @if($canManage)<x-ui.button type="submit">{{ __('field.save') }}</x-ui.button>@endif
    </form>
    @if($canDelete)
    <details><summary>{{ __('field.delete') }}</summary>
        <form method="POST" action="{{ route('meetings.delete', ['locale' => $locale, 'meeting' => $meeting->id]) }}">@csrf<input type="hidden" name="version" value="{{ $version }}"><x-ui.button type="submit">{{ __('field.delete_confirm') }}</x-ui.button></form>
    </details>
    @endif
</x-layouts.app>
