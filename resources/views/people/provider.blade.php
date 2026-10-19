@php
    $selectedProvider = (string) (old('_form') === $providerForm ? old('related_person_id') : ($providerId ?? ''));
@endphp
<div class="field"><label for="provider-{{ $providerForm }}">{{ __('people.provider') }}</label>
    <select class="input" id="provider-{{ $providerForm }}" name="related_person_id">
        <option value="">{{ __('people.not_recorded') }}</option>
        @if($selectedProvider !== '' && ! $providers->contains(fn ($provider) => (string) $provider->id === $selectedProvider))<option value="{{ $selectedProvider }}" selected>{{ __('people.retained_reference') }} #{{ $selectedProvider }}</option>@endif
        @foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($selectedProvider === (string) $provider->id)>{{ $provider->label }}</option>@endforeach
    </select>
</div>
