<form method="POST" action="{{ route('applications.detail', ['locale' => app()->getLocale(), 'application' => $application->id, 'kind' => $kind]) }}">@csrf
    @include('applications.context')
    @if($row)<input type="hidden" name="detail_id" value="{{ $row->id }}">@endif
    @if($kind === 'rehab_answers')
    @php
        $parentId = old('_form') === $prefix ? old('parent_id') : ($row->{$headerReferenceColumn} ?? null);
    @endphp
    <div class="field"><label for="{{ $prefix }}-header">{{ __('applications.rehab_header') }}</label><select class="input" id="{{ $prefix }}-header" name="parent_id"><option value="">{{ __('applications.rehab_header') }}</option>@foreach($rehab as $header)<option value="{{ $header->id }}" @selected((string) $parentId === (string) $header->id)>#{{ $header->id }} · {{ $header->c_PropertyOwnership }} · {{ $header->c_PropertyArea }}</option>@endforeach</select></div>
    @endif
    @include('applications.fields', ['fields' => $metadata[$kind], 'options' => $options[$kind]])
    @include('applications.actions')
</form>

