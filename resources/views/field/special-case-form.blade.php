@php
    $mineForm = old('_form') === $scope;
    $pick = fn (string $key, $stored) => $mineForm ? old($key, $stored) : $stored;
    $selectedType = $pick('type_id', $case?->type_id ?? $types->first()?->id);
@endphp
<form method="POST" action="{{ route('special-cases.save', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">@csrf
    <input type="hidden" name="_form" value="{{ $scope }}"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="case_id" value="{{ $case?->id }}">
    <div class="people-fields">
        <div class="field"><label for="{{ $scope }}-date">{{ __('field.special.date') }}</label><input class="input" id="{{ $scope }}-date" type="date" name="date" value="{{ $pick('date', substr((string) ($case?->c_Date ?? now()->toDateString()), 0, 10)) }}" required></div>
        <div class="field"><label for="{{ $scope }}-type">{{ __('field.special.type') }}</label><select class="input" id="{{ $scope }}-type" name="type_id" required>@foreach($types as $type)<option value="{{ $type->id }}" @selected((int) $selectedType === (int) $type->id)>{{ app()->isLocale('ar') ? $type->c_Name : ($type->c_Name_en ?: $type->c_Name) }}</option>@endforeach</select></div>
        <div class="field"><label for="{{ $scope }}-reason">{{ __('field.special.reason') }}</label><textarea class="input" id="{{ $scope }}-reason" name="reason" rows="2" maxlength="65535" required>{{ $pick('reason', $case?->c_Reason ?? '') }}</textarea></div>
        <div class="field"><label for="{{ $scope }}-description">{{ __('field.special.description') }}</label><textarea class="input" id="{{ $scope }}-description" name="description" rows="3" maxlength="65535">{{ $pick('description', $case?->c_Description ?? '') }}</textarea></div>
        <div class="field"><label for="{{ $scope }}-action">{{ __('field.special.action') }}</label><textarea class="input" id="{{ $scope }}-action" name="action" rows="3" maxlength="65535">{{ $pick('action', $case?->c_Action ?? '') }}</textarea></div>
    </div>
    <x-ui.button type="submit">{{ __('field.save') }}</x-ui.button>
</form>
