@props(['id', 'title'])
<dialog id="{{ $id }}" {{ $attributes->class('dialog') }} aria-labelledby="{{ $id }}-title">
    <div class="dialog-heading"><h2 id="{{ $id }}-title">{{ $title }}</h2><form method="dialog"><button type="submit" class="icon-button" aria-label="{{ __('ui.close') }}">×</button></form></div>
    <div class="dialog-content">{{ $slot }}</div>
    <form method="dialog" class="dialog-actions"><x-ui.button type="submit">{{ __('ui.close') }}</x-ui.button></form>
</dialog>
