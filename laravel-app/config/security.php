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

    /*
    |--------------------------------------------------------------------------
    | Obscured zone page paths
    |--------------------------------------------------------------------------
    | Format: 'random-prefix/random-slug'  (no leading or trailing slash).
    | These replace /fieldOfRides, /RollerFever and /DinoAdventure.
    | Change them any time through .env (ZONE_PATH_FIELD, ZONE_PATH_ROLLER,
    | ZONE_PATH_DINO), then run: php artisan config:clear
    */
    'zone_paths' => [
        'fieldOfRides'  => env('ZONE_PATH_FIELD',  'qzmvkrfwtpxhnc/ydgs4kqe'),
        'rollerFever'   => env('ZONE_PATH_ROLLER', 'hwtnbpzrlkxqmv/ucfa7zjd'),
        'dinoAdventure' => env('ZONE_PATH_DINO',   'bdsdqhddinolakbaynjqhdhq/bxsbxbh'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Obscured sidebar page paths
    |--------------------------------------------------------------------------
    | Only the PAGE (GET) URLs are obscured. AJAX/API/form endpoints such as
    | /pos/save-transaction, /inventory/add, /attendance/store keep their URLs.
    | Format: 'random-prefix/random-slug' (no leading or trailing slash).
    | You can edit these values directly, then run: php artisan config:clear
    */
    'page_paths' => [
        'snackbar'       => 'mtqzrkvbxn/pfdh2wcy',
        'inventory'      => 'kdwpxhnzqc/ruvt8bge',
        'pos'            => 'vxjrlgtmqz/hnka5osd',
        'analytics'      => 'pnbqtwzkcx/yfme3rjv',
        'ml_forecast'    => 'wqzjhdvktn/cbxo6afl',
        'customer'       => 'zghdvlqnrx/wtcu6ipa',
        'reservations'   => 'lcxnpfzwqm/ebya9kdt',
        'attendance'     => 'ftrqzmxvhk/gjso1lnw',
        'salary'         => 'xwdhbvkpzt/mcre4qfy',
        'payroll_history'=> 'nzkcqxrftv/dpwa8hsl',
        'payslip'        => 'hjvbtmqpxz/oiyg2nkc',
        'employees'      => 'rqmfxzkdwb/tvle7ncs',
        'notifications'  => 'cvhxqzmtkd/aeyr5pbw',
    ],

    /*
    |--------------------------------------------------------------------------
    | Obscured customer app prefix (replaces /app)
    |--------------------------------------------------------------------------
    | Every customer page (dashboard, booking, waiver, my-bookings, activity,
    | receipt...) lives under this prefix. Route names (user.dashboard, ...) are
    | unchanged, so use route('user.dashboard') etc. in Blade, emails and redirects.
    | Format: 'random-prefix/random-slug' (no leading or trailing slash).
    */
    'user_prefix' => env('USER_PREFIX', 'mvqzxkhtpd/ncwa3ruy'),

];