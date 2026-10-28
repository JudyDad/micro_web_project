@props([
    'id', 'name', 'label', 'url',
    'params' => [], 'value' => '', 'valueLabel' => null, 'placeholder' => null, 'hint' => null, 'error' => null,
    'navigate' => false, 'autocomplete' => 'off',
])
@php
    // A combobox that searches the server as the user types (no fixed option list). The chosen id travels in the hidden
    // input `$name`; with `navigate`, choosing a match opens its `url` instead.
    $value = (string) ($value ?? '');
    $initialLabel = $value !== '' ? (string) ($valueLabel ?? (__('people.retained_reference').' #'.$value)) : '';
@endphp
<div class="field search-select" x-data="searchSelect({{ \Illuminate\Support\Js::from(['url' => $url, 'params' => (object) $params, 'value' => $value, 'label' => $initialLabel, 'navigate' => (bool) $navigate]) }})" @click.outside="close()">
    <label for="{{ $id }}">{{ $label }}</label>
    @if(! $navigate)<input type="hidden" name="{{ $name }}" :value="selectedId">@endif
    <input id="{{ $id }}" type="text" role="combobox" aria-autocomplete="list" aria-haspopup="listbox" :aria-expanded="open ? 'true' : 'false'" aria-controls="{{ $id }}-list"
        :aria-activedescendant="active >= 0 ? '{{ $id }}-option-' + active : ''" autocomplete="{{ $autocomplete }}" spellcheck="false"
        placeholder="{{ $placeholder ?? __('ui.search_select.placeholder') }}" {{ $attributes->class(['input', 'input-error' => (bool) $error]) }}
        x-ref="input" x-model="query" @focus="show()" @input.debounce.250ms="search()" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
        @keydown.enter="pick($event)" @keydown.escape.prevent="close()" @if($error) aria-invalid="true" @endif @if($hint || $error) aria-describedby="{{ $id }}-help" @endif>
    <ul id="{{ $id }}-list" class="search-select-list" role="listbox" x-show="open" x-cloak>
        <template x-if="loading"><li class="search-select-status" aria-live="polite">{{ __('ui.search_select.searching') }}</li></template>
        <template x-if="! loading && failed"><li class="search-select-status" role="alert">{{ __('ui.search_select.failed') }}</li></template>
        <template x-if="! loading && ! failed && results.length === 0"><li class="search-select-status">{{ __('ui.search_select.no_results') }}</li></template>
        <template x-for="(item, index) in results" :key="item.id">
            <li :id="'{{ $id }}-option-' + index" role="option" :aria-selected="String(item.id) === String(selectedId) ? 'true' : 'false'" class="search-select-option"
                :class="{ 'is-active': index === active }" @mousedown.prevent="choose(item)" @mousemove="active = index" x-text="item.label"></li>
        </template>
    </ul>
    <p class="search-select-selected" x-show="selectedId !== '' && ! navigate" x-cloak><span aria-hidden="true">✓</span> <span x-text="selectedLabel"></span> · <button type="button" class="link-button" @click="clear()">{{ __('ui.search_select.clear') }}</button></p>
    @if($hint || $error)<p id="{{ $id }}-help" class="{{ $error ? 'field-error' : 'field-hint' }}">{{ $error ?: $hint }}</p>@endif
</div>
