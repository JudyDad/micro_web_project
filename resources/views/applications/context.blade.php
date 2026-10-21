<input type="hidden" name="city_id" value="{{ $cityId }}">
<input type="hidden" name="kind" value="{{ $kind }}">
@if(isset($application))
<input type="hidden" name="application_id" value="{{ $application->id }}">
<input type="hidden" name="version" value="{{ old('_form') === $prefix ? old('version', '') : $version }}">
@endif
@if(old('_form') === $prefix && old('draft_id'))
<input type="hidden" name="draft_id" value="{{ old('draft_id') }}">
<input type="hidden" name="draft_version" value="{{ old('draft_version') }}">
@endif

