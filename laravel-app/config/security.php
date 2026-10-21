<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Secure (obscured) URL prefix
    |--------------------------------------------------------------------------
    | Used by routes/web.php for the admin-only / sensitive areas
    | (CMS, System Logs, Accounts, My Account).
    |
    | Set it in .env, e.g.:   SECURE_PREFIX=ctrl-x7k2q9m4
    | Then run:               php artisan config:clear
    |
    | Default is 'admin' so nothing breaks until you set the .env value.
    | Use letters, numbers, and dashes only. No slashes.
    */
    'secure_prefix' => trim(env('SECURE_PREFIX', 'admin'), '/'),

];