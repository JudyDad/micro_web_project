{{-- People-directory fields (person, membership, education, work, course) in the paper order; see forms.fields. --}}
@include('forms.fields', [
    'fields' => $fields, 'options' => $options ?? [], 'row' => $row ?? null, 'prefix' => $prefix ?? 'person',
    'recovering' => old('_form') === ($prefix ?? 'person'), 'nullBoxes' => $nullBoxes ?? true, 'required' => $required ?? [], 'hidden' => $hidden ?? [],
    'groups' => $groups ?? false, 'fieldName' => 'fields', 'refName' => 'references', 'nullName' => 'null_fields[]', 'oldBase' => '', 'formMarker' => true, 'formId' => $attachTo ?? null,
])
