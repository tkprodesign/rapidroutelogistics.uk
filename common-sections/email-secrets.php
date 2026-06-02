<?php
if (!isset($GLOBALS['rrl_runtime_config']) || !is_array($GLOBALS['rrl_runtime_config'])) {
    $runtimeConfigPath = __DIR__ . '/runtime-config.php';
    $runtimeConfig = file_exists($runtimeConfigPath) ? include $runtimeConfigPath : [];
    $GLOBALS['rrl_runtime_config'] = is_array($runtimeConfig) ? $runtimeConfig : [];
}

if (!function_exists('rrl_email_env')) {
    function rrl_email_env(string $name, string $default = ''): string {
        $value = getenv($name);
        if ($value !== false && trim((string)$value) !== '') {
            return trim((string)$value);
        }
        if (isset($_ENV[$name]) && trim((string)$_ENV[$name]) !== '') {
            return trim((string)$_ENV[$name]);
        }
        if (isset($_SERVER[$name]) && trim((string)$_SERVER[$name]) !== '') {
            return trim((string)$_SERVER[$name]);
        }
        if (isset($GLOBALS['rrl_runtime_config'][$name]) && trim((string)$GLOBALS['rrl_runtime_config'][$name]) !== '') {
            return trim((string)$GLOBALS['rrl_runtime_config'][$name]);
        }
        return $default;
    }
}

return [
    'RESEND_API_KEY' => rrl_email_env('RESEND_API_KEY'),
    'NOREPLY_FROM_EMAIL' => rrl_email_env('NOREPLY_FROM_EMAIL', 'noreply@rapidroutelogistics.uk'),
    'SMTP_HOST' => rrl_email_env('SMTP_HOST', 'mail.spacemail.com'),
    'SMTP_PORT' => rrl_email_env('SMTP_PORT', '465'),
    'SMTP_SECURE' => rrl_email_env('SMTP_SECURE', 'ssl'),

    // Mailbox passwords are intentionally read from environment / repo secrets.
    'SHIPMENTS_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
    'BILLING_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
    'ADMIN_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
    'SUPPORT_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
    'TRACKING_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
    'NOREPLY_EMAIL_PASSWORD' => ';,js%RxY8GSynZJ',
];
?>
