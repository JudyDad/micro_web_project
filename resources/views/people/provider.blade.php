@php
    $selectedProvider = (string) (old('_form') === $providerForm ? old('related_person_id') : ($providerId ?? ''));
@endphp
<x-ui.search-select :id="'provider-'.$providerForm" name="related_person_id" :label="__('people.provider')" :url="route('people.lookup', ['locale' => app()->getLocale()])" :params="['city_id' => $person->city_id]"
    :value="$selectedProvider" :value-label="$providerLabels[(int) $selectedProvider] ?? null" :placeholder="__('people.not_recorded')" :hint="__('people.provider_search')" />
