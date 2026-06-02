<?php
if (!defined('COMMON_SECTIONS_GLOBALS_LOADED')) {
    define('COMMON_SECTIONS_GLOBALS_LOADED', true);

    if (!function_exists('rrl_enforce_https')) {
        function rrl_enforce_https(): void {
            if (headers_sent()) {
                return;
            }

            $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
            if (!in_array($host, ['rapidroutelogistics.uk', 'www.rapidroutelogistics.uk'], true)) {
                return;
            }

            $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
            $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
            $cfVisitor = (string)($_SERVER['HTTP_CF_VISITOR'] ?? '');
            $cfVisitorHttps = stripos($cfVisitor, '"scheme":"https"') !== false || stripos($cfVisitor, "'scheme':'https'") !== false;

            if ($https === 'on' || $https === '1' || $forwardedProto === 'https' || $cfVisitorHttps) {
                return;
            }

            $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: https://' . $host . $requestUri, true, 301);
            exit;
        }
    }

    rrl_enforce_https();

    if (!isset($GLOBALS['rrl_runtime_config']) || !is_array($GLOBALS['rrl_runtime_config'])) {
        $runtimeConfigPath = __DIR__ . '/runtime-config.php';
        $runtimeConfig = file_exists($runtimeConfigPath) ? include $runtimeConfigPath : [];
        $GLOBALS['rrl_runtime_config'] = is_array($runtimeConfig) ? $runtimeConfig : [];
    }

    if (!function_exists('rrl_env')) {
        function rrl_env(array $names, ?string $default = null): ?string {
            foreach ($names as $name) {
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
            }
            return $default;
        }
    }

    if (!function_exists('rrl_database_config')) {
        function rrl_database_config(): array {
            $databaseUrl = rrl_env(['DATABASE_URL', 'JAWSDB_URL', 'CLEARDB_DATABASE_URL']);
            $urlConfig = [];
            if ($databaseUrl !== null) {
                $parts = parse_url($databaseUrl);
                if (is_array($parts)) {
                    $urlConfig = [
                        'host' => $parts['host'] ?? null,
                        'user' => isset($parts['user']) ? rawurldecode((string)$parts['user']) : null,
                        'pass' => isset($parts['pass']) ? rawurldecode((string)$parts['pass']) : null,
                        'name' => isset($parts['path']) ? ltrim((string)$parts['path'], '/') : null,
                        'port' => isset($parts['port']) ? (int)$parts['port'] : null,
                    ];
                }
            }

            $user = rrl_env(['DATABASE_USERNAME', 'DB_USER', 'DATABASE_USER', 'MYSQL_USER'], $urlConfig['user'] ?? null);
            $nameDefault = $urlConfig['name'] ?? null;
            if (($nameDefault === null || $nameDefault === '') && $user !== null && preg_match('/^b\d+_\d+$/', $user) === 1) {
                $nameDefault = $user . '_db';
            }

            $portValue = rrl_env(['DATABASE_PORT', 'DB_PORT', 'MYSQL_PORT'], isset($urlConfig['port']) ? (string)$urlConfig['port'] : '3306');

            return [
                'host' => rrl_env(['DATABASE_HOST', 'DB_HOST', 'MYSQL_HOST'], $urlConfig['host'] ?? 'sql300.byethost18.com'),
                'user' => $user,
                'pass' => rrl_env(['DATABASE_PASSWORD', 'DB_PASS', 'DATABASE_PASS', 'MYSQL_PASSWORD'], $urlConfig['pass'] ?? null),
                'name' => rrl_env(['DATABASE_NAME', 'DB_NAME', 'MYSQL_DATABASE'], $nameDefault),
                'port' => (int)($portValue ?: 3306),
                'socket' => rrl_env(['DATABASE_SOCKET', 'DB_SOCK', 'MYSQL_UNIX_PORT']),
            ];
        }
    }

    $globalConn = null;

    if (isset($conn) && $conn instanceof mysqli && empty($conn->connect_error)) {
        $globalConn = $conn;
    } elseif (isset($dbconn) && $dbconn instanceof mysqli && empty($dbconn->connect_error)) {
        $globalConn = $dbconn;
    } else {
        $globalDbConfig = rrl_database_config();
        if (empty($globalDbConfig['user']) || $globalDbConfig['pass'] === null || empty($globalDbConfig['name'])) {
            error_log('Rapid Route Logistics database configuration is incomplete. Set DATABASE_USERNAME, DATABASE_PASSWORD, and DATABASE_NAME (or DATABASE_URL).');
            http_response_code(500);
            die('Database configuration is incomplete.');
        }

        mysqli_report(MYSQLI_REPORT_OFF);
        $globalConn = @new mysqli(
            (string)$globalDbConfig['host'],
            (string)$globalDbConfig['user'],
            (string)$globalDbConfig['pass'],
            (string)$globalDbConfig['name'],
            (int)$globalDbConfig['port'],
            $globalDbConfig['socket'] ?: null
        );
        if (!($globalConn instanceof mysqli) || !empty($globalConn->connect_error)) {
            $connectError = $globalConn instanceof mysqli ? $globalConn->connect_error : 'unknown connection error';
            error_log('Rapid Route Logistics database connection failed: ' . $connectError);
            http_response_code(500);
            die('Database connection failed. Please try again later.');
        }
        $globalConn->set_charset('utf8mb4');
    }

    $conn = $globalConn;
    $dbconn = $globalConn;

    if (!function_exists('asset_url')) {
        function asset_url(string $path): string {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $path;
            if (file_exists($filePath)) {
                $separator = (strpos($path, '?') === false) ? '?' : '&';
                return $path . $separator . 'v=' . filemtime($filePath);
            }
            return $path;
        }
    }





    if (!isset($GLOBALS['rrl_email_config']) || !is_array($GLOBALS['rrl_email_config'])) {
        $emailSecretsPath = __DIR__ . '/email-secrets.php';
        $emailSecretsData = file_exists($emailSecretsPath) ? include $emailSecretsPath : [];
        $GLOBALS['rrl_email_config'] = is_array($emailSecretsData) ? $emailSecretsData : [];
    }

    if (!function_exists('rrl_send_resend_email')) {
        function rrl_send_resend_email(array $to, string $subject, string $html, string $text = ''): array {
            $apiKey = (string)($GLOBALS['rrl_email_config']['RESEND_API_KEY'] ?? '');
            if ($apiKey === '') {
                $apiKey = rrl_env(['RESEND_API_KEY'], '');
            }
            if ($apiKey === '') {
                $error = 'Missing RESEND_API_KEY';
                error_log('rrl-email: ' . $error . ' for subject "' . $subject . '"');
                return ['ok' => false, 'error' => $error, 'http_code' => 0, 'response' => ''];
            }

            $fromEmail = rrl_env(['NOREPLY_FROM_EMAIL'], 'noreply@rapidroutelogistics.uk');
            $payload = [
                'from' => 'Rapid Route Logistics <' . $fromEmail . '>',
                'to' => array_values($to),
                'subject' => $subject,
                'html' => $html,
            ];
            if ($text !== '') {
                $payload['text'] = $text;
            }

            $ch = curl_init('https://api.resend.com/emails');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $response = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr !== '') {
                $error = 'Resend curl error: ' . $curlErr;
                error_log('rrl-email: ' . $error . ' for subject "' . $subject . '"');
                return ['ok' => false, 'error' => $error, 'http_code' => $httpCode, 'response' => (string)$response];
            }
            if ($httpCode < 200 || $httpCode >= 300) {
                $error = 'Resend rejected request (' . $httpCode . ')';
                error_log('rrl-email: ' . $error . ' for subject "' . $subject . '": ' . (string)$response);
                return ['ok' => false, 'error' => $error, 'http_code' => $httpCode, 'response' => (string)$response];
            }

            return ['ok' => true, 'http_code' => $httpCode, 'response' => (string)$response];
        }
    }

    if (!function_exists('rrl_email_brand_shell')) {
        function rrl_email_brand_shell(string $title, string $preheader, string $bodyHtml, array $options = []): string {
            $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $safePreheader = htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8');
            $year = htmlspecialchars((string)($options['year'] ?? date('Y')), ENT_QUOTES, 'UTF-8');
            $supportEmail = htmlspecialchars((string)($options['support_email'] ?? 'support@rapidroutelogistics.uk'), ENT_QUOTES, 'UTF-8');
            $logoUrl = htmlspecialchars((string)($options['logo_url'] ?? 'https://rapidroutelogistics.uk/assets/images/branding/transparent/logo-alt.png'), ENT_QUOTES, 'UTF-8');
            $siteUrl = htmlspecialchars((string)($options['site_url'] ?? 'https://rapidroutelogistics.uk/'), ENT_QUOTES, 'UTF-8');

            return '<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>' . $safeTitle . '</title>
<style>
@media only screen and (max-width:640px){.rrl-wrapper{padding:18px 12px!important}.rrl-card{width:100%!important}.rrl-header,.rrl-content,.rrl-footer{padding-left:22px!important;padding-right:22px!important}.rrl-title{font-size:28px!important;line-height:1.14!important}.rrl-button{display:block!important;text-align:center!important}.rrl-code{font-size:30px!important;letter-spacing:5px!important}}
@media (prefers-color-scheme:dark){.rrl-wrapper{background:#071318!important}.rrl-card{background:#101f27!important;border-color:#1f3d45!important}.rrl-content{background:#101f27!important}.rrl-title,.rrl-strong{color:#f7fbfb!important}.rrl-copy,.rrl-muted,.rrl-footer-copy{color:#c5d4d2!important}.rrl-panel{background:#0c1a21!important;border-color:#224a50!important}.rrl-footer{background:#0b181f!important;border-color:#224a50!important}}
</style>
</head>
<body style="margin:0;padding:0;background:#eef4f2;font-family:Arial,Helvetica,sans-serif;color:#14232b;-webkit-text-size-adjust:100%;text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;mso-hide:all;">' . $safePreheader . '</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="rrl-wrapper" style="background:#eef4f2;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="640" class="rrl-card" style="width:640px;max-width:640px;background:#ffffff;border:1px solid #dbe7e4;border-radius:18px;overflow:hidden;box-shadow:0 18px 48px rgba(20,35,43,0.12);">
<tr><td class="rrl-header" style="background:#14232b;padding:26px 34px;border-bottom:4px solid #1A9B82;">
<a href="' . $siteUrl . '" target="_blank" rel="noopener" style="text-decoration:none;display:inline-block;"><img src="' . $logoUrl . '" alt="Rapid Route Logistics" width="230" style="display:block;border:0;max-width:230px;width:230px;height:auto;color:#ffffff;font-size:18px;font-weight:bold;"></a>
</td></tr>
<tr><td class="rrl-content" style="padding:34px 40px 10px 40px;background:#ffffff;">' . $bodyHtml . '</td></tr>
<tr><td class="rrl-footer" style="background:#f6faf9;border-top:1px solid #dbe7e4;padding:22px 34px;">
<p class="rrl-footer-copy" style="margin:0 0 8px 0;font-size:12px;line-height:1.6;color:#60716f;">Need help? Contact <a href="mailto:' . $supportEmail . '" style="color:#1A9B82;text-decoration:none;font-weight:bold;">' . $supportEmail . '</a>.</p>
<p class="rrl-footer-copy" style="margin:0;font-size:11px;line-height:1.6;color:#7b8a88;">© ' . $year . ' Rapid Route Logistics. Secure delivery operations and customer support notifications. Please do not reply to this email.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
        }
    }

    if (!function_exists('rrl_email_h1')) {
        function rrl_email_h1(string $text): string {
            return '<h1 class="rrl-title" style="margin:0 0 14px 0;font-size:32px;line-height:1.18;color:#14232b;font-family:Arial,Helvetica,sans-serif;font-weight:800;letter-spacing:-0.02em;">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</h1>';
        }
    }

    if (!function_exists('rrl_email_p')) {
        function rrl_email_p(string $text): string {
            return '<p class="rrl-copy" style="margin:0 0 16px 0;font-size:15px;line-height:1.75;color:#344743;">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }

    if (!function_exists('rrl_email_button')) {
        function rrl_email_button(string $label, string $href): string {
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $safeHref = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
            return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 24px 0;"><tr><td><a class="rrl-button" href="' . $safeHref . '" target="_blank" rel="noopener" style="display:inline-block;background:#1A9B82;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:999px;font-size:14px;font-weight:800;letter-spacing:0.01em;box-shadow:0 10px 22px rgba(26,155,130,0.22);">' . $safeLabel . '</a></td></tr></table>';
        }
    }

    if (!function_exists('rrl_email_code_panel')) {
        function rrl_email_code_panel(string $code, string $hint): string {
            return '<div class="rrl-panel" style="margin:8px 0 22px 0;border:1px solid #cfe3de;border-radius:16px;padding:18px;background:#f6fbfa;"><div class="rrl-code rrl-strong" style="font-size:36px;line-height:1.1;letter-spacing:7px;font-weight:800;color:#14232b;font-family:Arial,Helvetica,sans-serif;">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</div><p class="rrl-muted" style="margin:10px 0 0 0;font-size:13px;line-height:1.6;color:#60716f;">' . htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') . '</p></div>';
        }
    }

    if (!function_exists('rrl_email_detail_panel')) {
        function rrl_email_detail_panel(array $rows): string {
            $html = '<div class="rrl-panel" style="margin:6px 0 24px 0;border:1px solid #dbe7e4;border-radius:16px;background:#f8fbfa;padding:4px 18px;">';
            foreach ($rows as $label => $value) {
                $html .= '<p class="rrl-copy" style="margin:14px 0;font-size:14px;line-height:1.55;color:#344743;"><span class="rrl-strong" style="display:inline-block;min-width:112px;color:#14232b;font-weight:800;">' . htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8') . ':</span> ' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            return $html . '</div>';
        }
    }

    // Global performance hints for Material Symbols CDN used across pages.
    if (!headers_sent()) {
        $materialSymbolsHref = "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200";
        header("Link: <https://fonts.googleapis.com>; rel=preconnect", false);
        header("Link: <https://fonts.gstatic.com>; rel=preconnect; crossorigin", false);
        header("Link: <{$materialSymbolsHref}>; rel=preload; as=style", false);
    }
}
?>
