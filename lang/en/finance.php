<?php

return [
    'title' => 'Finance', 'back' => 'Back to the project', 'saved' => 'Saved.', 'save' => 'Save', 'none' => 'None', 'state' => 'Project state',
    'scope' => 'Donor, loan and instalments of this project. Amounts are exact and stay in the loan\'s own currency; nothing is converted or summed across currencies. Deleting loans and instalments is not available.',
    'links' => ['finance' => 'Loan and instalments', 'executive' => 'Executive file', 'purchasing' => 'Purchases (legacy screen)'],
    'nav_payments' => 'Instalments',
    'donor' => [
        'title' => 'Donor', 'donor' => 'Donor', 'group' => 'Donor group', 'change' => 'Change donor', 'add_group' => 'Add a donor group', 'group_name' => 'Group name',
        'rules' => 'A funded project (Accepted, Financed, Finished, Canceled or Closed) must have a donor; any other project cannot have one.',
    ],
    'loan' => [
        'title' => 'Loan', 'receipt' => 'Receipt number', 'amount' => 'Loan amount', 'opposite' => 'Amount in :currency', 'returned' => 'Amount to be returned',
        'payment' => 'Instalment amount', 'count' => 'Number of instalments', 'paid' => 'Paid so far', 'remaining' => 'Remaining to return', 'near_end' => 'less than one and a half instalments left',
        'remaining_clamped' => 'Remaining (not below zero)', 'remaining_count' => 'Instalments remaining', 'edit' => 'Edit the loan', 'none_yet' => 'This project has no loan yet. Saving the first loan marks the project Financed.',
        'none' => 'This project has no loan.', 'currency' => 'Currency', 'exchange_rate' => 'Exchange rate', 'date' => 'Loan date', 'keep_group' => 'Keep the current group',
        'derived' => 'The number of instalments and the repayment rate are calculated from these amounts and rounded to one decimal.', 'note' => 'Note', 'save' => 'Save the loan',
    ],
    'installments' => [
        'title' => 'Instalments', 'hint' => 'Whole currency units only. Unpaid instalments record their due date as the received date. The last paid instalment with less than one and a half instalments left finishes the project.',
        'amount' => 'Amount', 'due' => 'Due date', 'status' => 'Status', 'received' => 'Received on', 'note' => 'Note', 'paid' => 'Paid', 'not_paid' => 'Not paid', 'none' => 'No instalments recorded.',
        'save' => 'Save instalments',
    ],
    'payments' => [
        'title' => 'Instalments schedule', 'intro' => 'Unpaid instalments by due date, or paid instalments by received date, across the cities you can read.',
        'view' => 'View', 'pending' => 'Unpaid', 'paid' => 'Paid', 'year' => 'Year', 'all_years' => 'All years', 'all_groups' => 'All groups', 'months' => 'Months',
        'currency_note' => 'Totals are shown per currency; amounts in different currencies are never added together.', 'totals' => 'Totals per currency', 'count' => ':count instalment(s)',
    ],
    'executive' => [
        'title' => 'Executive file', 'scope' => 'Executive (legal) file of the project. A project has one file; files cannot be deleted here.', 'file' => 'File', 'expires' => 'Expires on',
        'none' => 'This project has no executive file.', 'new' => 'New executive file', 'number' => 'File number', 'begin' => 'Begin date', 'current' => 'Current date',
        'current_hint' => 'A new file starts with its current date equal to the begin date.', 'impound_date' => 'Impound date', 'impound_type' => 'Impound type', 'months' => 'Number of months',
    ],
    'purchasing' => [
        'title' => 'Purchases', 'scope' => 'This screen is hidden in the desktop program. It is available only to accounts granted purchasing access, so preserved purchase rows can be read and corrected. Rows cannot be deleted.',
        'name' => 'Item', 'brand' => 'Brand', 'category' => 'Category', 'quantity' => 'Quantity', 'price' => 'Price', 'invoice_date' => 'Invoice date', 'invoice_number' => 'Invoice number',
        'trader' => 'Trader', 'none' => 'No purchases recorded.',
    ],
    'visit_notices_missing' => 'This project has no visit notices. They are created with its first loan, and only when the city has monitoring-and-evaluation staff.',
    'errors' => [
        'ineligible_state' => 'Finance applies to projects that are Accepted, Financed, Finished, Canceled or Closed.',
        'ineligible_donor' => 'Choose the project donor before recording the loan.',
        'donor_required' => 'A funded project must keep its donor.', 'donor_not_allowed' => 'A donor can only be chosen for a funded project.', 'donor_missing' => 'Select a donor from this city.',
        'group_name' => 'Enter a group name of at most 50 characters.', 'group_exists' => 'This donor already has a group with that name.', 'group_missing' => 'Select a group from this city.',
        'currency' => 'Choose USD or SP.', 'rate' => 'Enter the exchange rate with at most two decimals.', 'amount' => 'Enter an amount above zero with at most two decimals.',
        'returned' => 'Enter the amount to be returned with at most two decimals.', 'payment' => 'Enter the instalment amount with at most two decimals.',
        'float' => 'This value cannot be stored exactly by this city\'s loan table; use a value with fewer significant digits.', 'date' => 'Enter a valid date (YYYY-MM-DD).',
        'note' => 'The text is too long.', 'receipt' => 'The receipt number is at most 50 characters.',
        'payment_amount' => 'Enter a whole number of currency units (negative for a refund), no larger than 16,777,216.', 'payment_due' => 'Enter a valid due date and received date (YYYY-MM-DD).',
        'exe_number' => 'Enter the file number (at most 50 characters).', 'exe_type' => 'The impound type is at most 100 characters.', 'exe_months' => 'Enter a number of months from 0 to 100.',
        'item_name' => 'Enter the item name (at most 300 characters).', 'item_text' => 'The text is too long.', 'item_quantity' => 'Enter a whole quantity.',
        'item_price' => 'Enter a price with at most two decimals.', 'item_currency' => 'Choose USD or SYP.', 'item_category' => 'Select a category from this city.',
    ],
];
