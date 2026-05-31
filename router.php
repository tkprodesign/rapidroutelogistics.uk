<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$docRoot = __DIR__;
$filePath = $docRoot . $uri;

if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

if (is_dir($filePath)) {
    $index = rtrim($filePath, '/') . '/index.php';
    if (file_exists($index)) {
        require $index;
        return true;
    }
}

if (file_exists($filePath . '.php')) {
    require $filePath . '.php';
    return true;
}

if (file_exists($filePath . '/index.php')) {
    require $filePath . '/index.php';
    return true;
}

return false;
