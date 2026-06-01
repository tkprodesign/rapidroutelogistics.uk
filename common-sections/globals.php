<?php
if (!defined('COMMON_SECTIONS_GLOBALS_LOADED')) {
    define('COMMON_SECTIONS_GLOBALS_LOADED', true);

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

    // Global performance hints for Material Symbols CDN used across pages.
    if (!headers_sent()) {
        $materialSymbolsHref = "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200";
        header("Link: <https://fonts.googleapis.com>; rel=preconnect", false);
        header("Link: <https://fonts.gstatic.com>; rel=preconnect; crossorigin", false);
        header("Link: <{$materialSymbolsHref}>; rel=preload; as=style", false);
    }
}
?>
