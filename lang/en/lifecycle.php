<?php

return [
    'title' => 'Project state', 'current' => 'Current', 'none' => 'No state recorded', 'change' => 'Change state', 'new_state' => 'New state',
    'rules' => 'The preliminary state is automatic. Waiting follows the full application, and Financed is set by finance.',
    'reason' => 'Reason', 'note' => 'Note', 'edit_current' => 'Edit reason and note of the current state', 'history' => 'State history', 'state' => 'State', 'date' => 'Date',
    'saved' => 'Project state saved.', 'checklist_saved' => 'Checklist saved.', 'checklist_link' => 'Assessment checklist',
    'refusals' => [
        'on_hold' => 'The preliminary state is assigned automatically and cannot be chosen.',
        'same_state' => 'The project is already in this state.',
        'waiting_from_preliminary' => 'A preliminary project moves to Waiting when its full application is submitted.',
        'financed_from_accepted' => 'An accepted project becomes financed when finance records its loan.',
        'accept_capability' => 'Your account cannot accept projects in this city.',
    ],
    'checklist' => [
        'title' => 'Assessment checklist', 'scope' => 'The checklist belongs to the beneficiary and is shared by all of their projects. Member rows are added or edited here; deleting a checklist is not available.',
        'beneficiary' => 'Beneficiary', 'choose' => 'Choose a checklist', 'choose_hint' => 'This person has several checklists. Open one to edit it.',
        'visits' => 'Visits', 'experience' => 'Professional test and training', 'reviews' => 'Team, committee and rejection message',
        'key_people' => 'Key people', 'team' => 'Team members', 'committee' => 'Committee members',
        'yes' => 'Yes', 'no' => 'No', 'na' => 'Not applicable', 'result_needs' => 'Needs training', 'result_good' => 'Good, not needed', 'for' => 'For', 'against' => 'Against',
        'set_later' => 'Leave empty when it is set later.', 'add_member' => 'Add a member…', 'print' => 'Print', 'printed' => 'Printed', 'none_saved' => 'No checklist has been saved for this beneficiary yet.', 'print_open' => 'Printable copy', 'sender_hint' => 'A rejection message needs a recorded sender; senders cannot be chosen here yet.',
        'p' => ['info' => 'Beneficiary information', 'name' => 'Beneficiary name', 'project_no' => 'Project No.', 'project_name' => 'Project name', 'city' => 'City', 'visit' => 'Visits', 'visit_type' => 'Visit type', 'visit_date' => 'Visit date', 'visit_time' => 'Time', 'home' => 'Home visit', 'project' => 'Project visit', 'experience' => 'Experience', 'pro_test' => 'Professional test', 'pro_train' => 'Professional training', 'mng_train' => 'Management training', 'test_date' => 'Test date', 'trainer' => 'Trainer', 'place' => 'Provider', 'period' => 'Training period', 'date' => 'Training date', 'result' => 'Result', 'needs_test' => 'Professional test needed', 'team_opinion' => 'Team opinion', 'committee_opinion' => 'Committee opinion', 'meeting_date' => 'Meeting date', 'name_col' => 'Name', 'position' => 'Position', 'role' => 'Role', 'opinion' => 'Opinion', 'reason' => 'Reason', 'signature' => 'Signature', 'notes' => 'Notes', 'reject' => 'Rejection message', 'date_col' => 'Date'],
        'fields' => [
            'date' => 'Date (YYYY-MM-DD)', 'time' => 'Time', 'note' => 'Notes', 'visitor' => 'Visitor', 'visits_report' => 'Visits report', 'observer' => 'Observer', 'examiner' => 'Examiner', 'result' => 'Result',
            'c_HasHomeVisit' => 'Home visit done', 'c_HasProjectVisit' => 'Project visit done', 'c_NeedProTest' => 'Professional test needed', 'c_HasProTest' => 'Professional test done',
            'c_HasProTrain' => 'Professional training', 'c_HasMngTrain' => 'Management training', 'c_RejectMsg' => 'Rejection message sent',
            'c_ProTrain_Trainer' => 'Professional trainer', 'c_ProTrain_Place' => 'Professional training place', 'c_ProTrain_BDate' => 'Training start (YYYY-MM-DD)', 'c_ProTrain_EDate' => 'Training end (YYYY-MM-DD)',
            'c_ProTrain_Note' => 'Professional training notes', 'c_ProTrain_Result' => 'Professional training result',
            'c_MngTrain_Trainer' => 'Management trainer', 'c_MngTrain_Place' => 'Management training place', 'c_MngTrain_Date' => 'Management training date (YYYY-MM-DD)', 'c_MngTrain_Result' => 'Management training result',
            'team_date' => 'Team date (YYYY-MM-DD)', 'committee_date' => 'Committee date (YYYY-MM-DD)', 'reject_date' => 'Rejection message date (YYYY-MM-DD)', 'notes' => 'Checklist notes',
            'member' => 'Member', 'opinion' => 'Opinion', 'person_name' => 'Name', 'role' => 'Role',
        ],
    ],
];
