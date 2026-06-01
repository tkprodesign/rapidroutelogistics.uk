<?php
// Copy this file to runtime-config.php on servers that cannot expose environment variables to PHP.
// Keep runtime-config.php out of git; the deploy workflow generates it from GitHub repo secrets.
return [
    'DATABASE_USERNAME' => '',
    'DATABASE_PASSWORD' => '',
    'DATABASE_NAME' => '',
    'DATABASE_HOST' => 'sql300.byethost18.com',
    'DATABASE_PORT' => '3306',

    'RESEND_API_KEY' => '',
    'NOREPLY_FROM_EMAIL' => 'noreply@rapidroutelogistics.uk',
    'SMTP_HOST' => 'mail.spacemail.com',
    'SMTP_PORT' => '465',
    'SMTP_SECURE' => 'ssl',

    'SHIPMENTS_EMAIL_PASSWORD' => '',
    'BILLING_EMAIL_PASSWORD' => '',
    'ADMIN_EMAIL_PASSWORD' => '',
    'SUPPORT_EMAIL_PASSWORD' => '',
    'TRACKING_EMAIL_PASSWORD' => '',
    'NOREPLY_EMAIL_PASSWORD' => '',
];
