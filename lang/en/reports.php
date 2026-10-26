<?php

return [
    'nav' => 'Statistics', 'title' => 'Application statistics',
    'intro' => 'Counts of application records across your permitted cities. Counts only: amounts are not totalled because the requested amount carries no currency.',
    'stage' => 'Application stage', 'state' => 'Lifecycle status', 'any' => 'Any', 'date_from' => 'Requested from', 'date_to' => 'Requested to',
    'filter_error' => 'Check your filters and try again. Dates must be valid and the end date cannot be before the start date.',
    'coverage' => 'Coverage', 'coverage_note' => 'Only cities your account may view are included. A city you leave unchecked is shown as not included.',
    'coverage_status' => 'Status', 'included' => 'Included', 'not_included' => 'Not included', 'no_records' => 'Included, no matching records',
    'records' => 'Records', 'total' => 'Total', 'share' => 'Share',
    'by_state' => 'By lifecycle status and city', 'by_stage' => 'By application stage', 'by_year' => 'By request year',
    'unrecorded' => 'No status recorded', 'unrecorded_note' => ':count records have no recorded lifecycle status.',
    'year' => 'Year', 'undated' => 'No request date',
    'export_xlsx' => 'Export all matching rows (Excel)', 'export_csv' => 'CSV', 'export_stats_xlsx' => 'Export these statistics (Excel)',
    'empty' => 'No matching applications', 'empty_note' => 'Change the filters, or check that records have been published for your cities.',
];
