@php
    // Field order of the desktop ExecutiveFiles_Form: file number · impound type · impound date · begin date + notice period (5 months default) · current date · expiry (computed).
    $mineForm = old('_form') === $scope;
    $pick = fn (string $key, $stored) => $mineForm ? old($key, $stored) : $stored;
    $day = fn ($value) => substr((string) $value, 0, 10);
@endphp
<form method="POST" action="{{ route('executive.save', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">@csrf
    <input type="hidden" name="_form" value="{{ $scope }}"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="file_id" value="{{ $file?->id }}">
    <div class="people-fields">
        <div class="field"><label for="{{ $scope }}-number">{{ __('finance.executive.number') }}</label><input class="input" id="{{ $scope }}-number" name="number" value="{{ $pick('number', $file?->c_ExeF_No ?? '') }}" maxlength="50" @required($file === null)></div>
        <div class="field"><label for="{{ $scope }}-type">{{ __('finance.executive.impound_type') }}</label><input class="input" id="{{ $scope }}-type" name="impound_type" value="{{ $pick('impound_type', $file?->c_ExeF_ImpoundType ?? '') }}" maxlength="100"></div>
        <div class="field"><label for="{{ $scope }}-impound">{{ __('finance.executive.impound_date') }}</label><input class="input" id="{{ $scope }}-impound" type="date" name="impound_date" value="{{ $pick('impound_date', $day($file?->c_ExeF_ImpoundDate ?? '')) }}" required></div>
        <div class="field"><label for="{{ $scope }}-begin">{{ __('finance.executive.begin') }}</label><input class="input" id="{{ $scope }}-begin" type="date" name="begin_date" value="{{ $pick('begin_date', $day($file?->c_ExeF_BeginDate ?? '')) }}" required></div>
        <div class="field"><label for="{{ $scope }}-months">{{ __('finance.executive.months') }}</label><input class="input" id="{{ $scope }}-months" type="number" min="0" max="100" name="months" value="{{ $pick('months', $file?->c_ExeF_NumOfMonths ?? 5) }}"><small>{{ __('finance.executive.months_hint') }}</small></div>
        <div class="field"><label for="{{ $scope }}-current">{{ __('finance.executive.current') }}</label><input class="input" id="{{ $scope }}-current" type="date" name="current_date" value="{{ $pick('current_date', $day($file?->c_ExeF_CurrentDate ?? '')) }}"><small>{{ __('finance.executive.current_hint') }}</small></div>
        @if($file !== null)<div class="field"><label>{{ __('finance.executive.expires') }}</label><p class="input" aria-readonly="true"><bdi>{{ $file->expires ?? '—' }}</bdi></p></div>@endif
    </div>
    <x-ui.button type="submit">{{ __('finance.save') }}</x-ui.button>
</form>
