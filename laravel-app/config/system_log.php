<?php

/*
 | Baseline / threshold settings para sa system log.
 | Ayusin ang mga numero ayon sa normal na takbo ng system mo.
 */
return [
    // Blade layout na i-e-extend ng logs page
    'layout' => 'layouts.sidebar',

    // Mga role na pwedeng tumingin ng logs (galing sa session('role'))
    'admin_roles' => ['admin'],

    // Mga session key ng login system mo. Palitan kung iba ang pangalan.
    'session_keys' => [
        'id' => 'user_id',
        'email' => 'email',
        'name' => 'fullname',
        'role' => 'role',
    ],

    // Brute force: ilang failed login sa loob ng X minuto (per IP)
    'failed_login_threshold' => 5,
    'failed_login_window_minutes' => 10,

    // Request spike: ilang request per minute per IP bago i-flag
    'requests_per_minute_threshold' => 120,

    // Bulk delete: ilang delete ng iisang user sa loob ng X minuto
    'bulk_delete_threshold' => 10,
    'bulk_delete_window_minutes' => 5,

    // Off-hours login (24h format, local time ng app timezone)
    'off_hours_start' => 0,   // 12:00 AM
    'off_hours_end' => 5,     // 5:00 AM

    // Column sa users table na nagtatakda ng role (para sa UserObserver)
    'role_column' => 'role',

    // Mga prefix na lalaktawan kapag hinuhulaan ang module
    'module_skip_prefixes' => ['admin'],

    // Mapa ng unang bahagi ng route name / URL -> pangalan ng module sa logs
    'module_map' => [
        'inventory' => 'inventory',
        'pos' => 'pos',
        'reservations' => 'booking',
        'visitor-summary' => 'booking',
        'employees' => 'manpower',
        'attendance' => 'manpower',
        'salary' => 'manpower',
        'payroll-history' => 'manpower',
        'accounts' => 'accounts',
        'cms' => 'website',
        'ml-forecast' => 'sales_forecast',
        'fieldOfRides' => 'sales_forecast',
        'RollerFever' => 'sales_forecast',
        'DinoAdventure' => 'sales_forecast',
        'notifications' => 'notifications',
        'system-logs' => 'system',
    ],

    // Mga field na HINDI dapat ilagay sa log ng LogsActivity trait
    'hidden_fields' => ['password', 'remember_token', 'updated_at', 'created_at'],
    // ── POS (cashier) audit ──
    'pos_discount_anomaly_percent'  => 25, // manual discount na 25% ng gross pataas = anomaly
    'pos_discount_repeat_threshold' => 5,  // 5 manual discount ng iisang cashier sa loob ng window = anomaly
    'pos_reprint_anomaly_count'     => 3,  // ika-3 reprint ng iisang invoice pataas = anomaly
    'pos_repeat_window_minutes'     => 60,
    'pos_repeat_threshold'          => 3,  // 3 void sa loob ng window = anomaly
    'pos_override_ttl_minutes'      => 15, // gaano katagal valid ang manager override
    // Ilang araw itatago ang logs (gamitin sa prune schedule)
    'retention_days' => 90,
];