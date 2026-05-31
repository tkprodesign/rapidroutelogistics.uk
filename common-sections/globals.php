<?php
if (!defined('COMMON_SECTIONS_GLOBALS_LOADED')) {
    define('COMMON_SECTIONS_GLOBALS_LOADED', true);

    $global_db_host = getenv('DB_HOST') ?: '127.0.0.1';
    $global_db_user = getenv('DB_USER') ?: 'logistics_user';
    $global_db_pass = getenv('DB_PASS') ?: 'logistics_pass_2024';
    $global_db_name = getenv('DB_NAME') ?: 'logistics_db';
    $global_db_port = (int)(getenv('DB_PORT') ?: 3306);
    $global_db_sock = getenv('DB_SOCK') ?: '/home/runner/mysql-run/mysql.sock';

    $globalConn = null;

    if (isset($conn) && $conn instanceof mysqli && empty($conn->connect_error)) {
        $globalConn = $conn;
    } elseif (isset($dbconn) && $dbconn instanceof mysqli && empty($dbconn->connect_error)) {
        $globalConn = $dbconn;
    } else {
        $globalConn = new mysqli($global_db_host, $global_db_user, $global_db_pass, $global_db_name, $global_db_port, $global_db_sock);
        if (!empty($globalConn->connect_error)) {
            die("Connection failed: " . $globalConn->connect_error);
        }
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
