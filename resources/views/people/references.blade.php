@foreach($row->referenceDescriptions ?? [] as $name => $description)
<div><dt>{{ __('people.fields.'.$name) }}</dt><dd class="preserve-text">{{ $description }}</dd></div>
@endforeach
