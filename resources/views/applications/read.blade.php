<dl class="project-details">
@foreach($fields as $name => $field)
@php
$label = __('applications.fields.'.$field['label']);
if ($label === 'applications.fields.'.$field['label']) $label = __('people.fields.'.$field['label']);
if ($label === 'people.fields.'.$field['label']) $label = str_replace('_', ' ', $field['label']);
@endphp
<div><dt>{{ $label }}</dt><dd class="preserve-text">{{ $row->{$name} ?? '—' }}</dd></div>
@endforeach
@foreach($row->referenceDescriptions ?? [] as $key => $description)
<div><dt>{{ __('applications.fields.'.$key) === 'applications.fields.'.$key ? str_replace('_', ' ', $key) : __('applications.fields.'.$key) }}</dt><dd>{{ $description }}</dd></div>
@endforeach
</dl>
