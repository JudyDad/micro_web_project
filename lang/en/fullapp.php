<?php

return [
    'title' => 'The full application is not ready',
    'sections' => ['beneficiary' => 'Beneficiary', 'story' => 'Project story', 'work' => 'Work experience', 'education' => 'Education and courses', 'project' => 'Project details and budget', 'family' => 'Family', 'support' => 'Losses, health, assets, aid and energy'],
    'codes' => ['required' => 'is missing', 'choice' => 'needs a yes or no answer', 'required_rows' => 'needs at least one entry'],
    'fields' => [
        'beneficiary' => 'Beneficiary', 'contact' => 'A contact number', 'MilitaryPeriod' => 'Military service period', 'IsMilitaryInWar' => 'Whether the service was during the war',
        'FamilyNumber' => 'Family book number', 'member' => 'Family member', 'current' => 'Current work', 'education' => 'Education', 'courses' => 'Courses', 'material' => 'Budget items',
        'loss' => 'Losses', 'illness' => 'Health conditions', 'asset' => 'Assets', 'aid' => 'Aid from other bodies', 'help' => 'Help sources', 'homepower' => 'Home energy sources',
    ],
    'row' => 'entry #:id',
];
