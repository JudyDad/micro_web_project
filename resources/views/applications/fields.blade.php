{{-- Application-side fields (project, link row, questionnaire rows) in the paper order; see forms.fields. --}}
@include('forms.fields', [
    'fields' => $fields, 'options' => $options, 'row' => $row ?? null, 'prefix' => $prefix,
    'recovering' => old('_form') === $prefix, 'nullBoxes' => $nullBoxes ?? true, 'required' => $required ?? [], 'hidden' => $hidden ?? [],
    'groups' => $groups ?? false, 'fieldName' => 'fields', 'refName' => 'references', 'nullName' => 'null_fields[]', 'oldBase' => '', 'formMarker' => $formMarker ?? true, 'formId' => $attachTo ?? null,
])
