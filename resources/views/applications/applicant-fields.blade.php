{{-- The applicant's own record inside the one-form intake: the people directory's fields in the paper order, posted as
     person[...] so they travel with the project. Required fields have no "not recorded" box; the preliminary form hides
     what the desktop hides (PaperOrder::preliminaryHidden). --}}
@include('forms.fields', [
    'fields' => $fields, 'options' => $options, 'row' => null, 'prefix' => 'applicant',
    'recovering' => old('_form') === 'application', 'nullBoxes' => false,
    'required' => array_merge($requirements['fields'], $requirements['references']),
    'hidden' => $hidden ?? array_merge(\App\Services\Applications\PaperOrder::preliminaryHidden('person'), \App\Services\Applications\PaperOrder::groups('person')['other']),
    'groups' => true, 'fieldName' => 'person[fields]', 'refName' => 'person[references]', 'nullName' => 'person[null_fields][]', 'oldBase' => 'person.', 'formMarker' => false,
])
