<?php

return [
    'user_admin' => [
        'name' => 'User Administrator',
        'permissions' => [
            'accounts.manage',
            'profiles.manage',
            'audit.view'
        ],
    ],
    'csr_rep' => [
        'name' => 'CSR Representative',
        'permissions' => [
            'requests.view',
            'shortlist.manage',
            'history.view'
        ],
    ],
    'pin' => [
        'name' => 'Person In Need',
        'permissions' => [
            'requests.manage',
            'history.view'
        ],
    ],
    'platform_manager' => [
        'name' => 'Platform Manager',
        'permissions' => [
            'categories.manage',
            'reports.view'
        ],
    ],
];
