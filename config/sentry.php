<?php

return [
    'dsn' => null,

    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD')),

    'breadcrumbs' => [
        // Capture bindings on SQL queries logged in breadcrumbs
        'sql_bindings' => true,
    ],
];