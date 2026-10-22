<?php

return [
    'nav_tasks' => 'Task board',
    'nav_notifications' => 'Notifications',
    'notifications' => [
        'title' => 'Notifications', 'intro' => 'Unseen notices addressed to the staff identities linked to your account. Marking one seen clears every copy of it.',
        'no_identity' => 'Your account is not linked to a staff identity in the selected cities, so it has no notices there. An administrator links identities after review.',
        'mark' => 'Seen', 'project' => 'Project', 'city' => 'City', 'about' => 'About', 'body' => 'Notice', 'date' => 'Date', 'sent_by' => 'Sent by', 'open' => 'Open',
        'finance' => 'Finance', 'visits' => 'Visits', 'empty' => 'No unseen notices.', 'mark_seen' => 'Mark selected as seen', 'marked' => ':count notice(s) marked as seen.',
        'send_title' => 'Send a message', 'send_intro' => 'Send a notice to one staff member or to everyone in a city. It appears in their notification box.', 'to' => 'To', 'everyone' => 'Everyone', 'send' => 'Send',
        'choose_city' => 'Choose city', 'sent' => 'Message sent to :count staff member(s).',
        'errors' => ['body' => 'Write the message.', 'title' => 'The subject is too long (255 bytes at most).', 'date' => 'Enter a valid date.', 'to' => 'Choose who receives the message.', 'project' => 'Enter a project number of this city.', 'project_admin' => 'Only administrators can attach a project.'],
    ],
    'tasks' => [
        'title' => 'Task board', 'intro' => 'The tasks of every project in the cities you may read. A task can be changed only by the staff member responsible for it.',
        'kind' => 'Board', 'funded' => 'Funded projects', 'applications' => 'Applications', 'search' => 'Search (number, project, person, phone)',
        'state' => 'Project state', 'donor' => 'Funder', 'type' => 'Project type', 'any' => 'Any', 'done' => 'Done', 'not_done' => 'Not done', 'task_filters' => 'Filter by task',
        'project' => 'Project', 'city' => 'City', 'beneficiary' => 'Beneficiary', 'phone' => 'Phone', 'name' => 'Project name', 'loan_date' => 'Project start',
        'not_assigned' => 'Not assigned to this project', 'mark_done' => 'Mark done', 'mark_undone' => 'Mark not done', 'saved' => 'Task updated.', 'empty' => 'No projects match.',
        'responsible' => 'Staff responsible for each task', 'responsible_none' => 'No one is assigned.', 'responsible_note' => 'Historical desktop users. A web account is responsible when it is linked to one of them.',
        'rule' => 'Why some cells have no button: you need the manage permission for the city and the responsibility for that task (or the permission to manage every task).',
    ],
];
