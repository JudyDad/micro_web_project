<?php

// Shared form vocabulary: the paper form's group headings and the desktop's fixed choices.
return [
    'yes' => 'Yes', 'no' => 'No', 'not_recorded' => 'Not recorded', 'choose' => 'Select…',
    'year_hint' => 'Four-digit birth year, as on the paper form (for example 1990).',
    'amper' => ['1' => 'Ampere', '0' => 'Watt'],
    'military' => ['وحيد' => 'Only son', 'مسرح' => 'Discharged', 'عسكري' => 'Serving', 'معفى/بدل' => 'Exempt / paid', 'مؤجل' => 'Deferred'],
    'groups' => [
        'application' => ['project' => 'Project form', 'premises' => 'Project information', 'extra' => 'Additional information', 'other' => 'Other fields (not on the paper form)'],
        'people' => ['story' => 'Beneficiary story', 'work' => 'Work experience and education', 'family' => 'Beneficiary family', 'aid' => 'Aid from other sources, debts and help'],
        'person' => ['identity' => 'Applicant personal information', 'contact' => 'Contact and housing', 'military' => 'Military service', 'other' => 'Other fields (not on the paper form)'],
    ],
    'hints' => [
        'c_MP_Name' => 'Write the project (jeans sewing workshop, Arabic carpentry workshop, machine…)',
        'Career_ID' => 'Taken from the program in Arabic and English, consistently',
        'MP_Type_ID' => 'Micro project, agriculture, solar energy, rehabilitation',
        'MP_SubType_ID' => 'New project or existing project',
        'P_HomeType_ID' => 'Owned, rented, lent, key money, inherited',
        'c_P_HomeState_Note' => 'Housing details',
        'c_P_MilitaryService' => 'Only son, discharged, serving, exempt, deferred',
        'c_P_IntermidiarySide' => 'More than one priest or intermediary can be recorded in the program',
        'MP_PlaceType_ID' => 'Property ownership',
        'c_MP_Agreement1' => 'Do you object to periodic follow-up visits if the project is approved?',
    ],
];
