<?php
return [
    'company_name' => env('COMPANY_NAME', 'LASAC'),
    'company_address' => env('COMPANY_ADDRESS', ''),
    'working_days_factor' => 261,
    'working_days_per_month' => 261 / 12,
    'working_hours_per_day' => 8,
    'overtime_start_time' => '17:00',
    'overnight_credit_cap_hours' => 4,
    'midnight_cutoff' => '00:00',
    'expiry_warning_days' => 30,
    'google_allowed_domains' => array_filter(array_map('trim', explode(',', env('GOOGLE_ALLOWED_DOMAINS', 'lasaclipa.org')))),
    'admin_emails' => array_filter(array_map('trim', explode(',', strtolower(env('ADMIN_EMAILS', 'admin@lasac.com,admin@lasaclipa.org,it@lasaclipa.org'))))),
    'email_notifications' => env('MAIL_NOTIFICATIONS_ENABLED', true),
];
