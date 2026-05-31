<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$docRoot = __DIR__;
$filePath = $docRoot . $uri;

if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

function serve_php($path) {
    chdir(dirname($path));
    require $path;
}

if (is_dir($filePath)) {
    $index = rtrim($filePath, '/') . '/index.php';
    if (file_exists($index)) {
        serve_php($index);
        return true;
    }
}

if (file_exists($filePath . '.php')) {
    serve_php($filePath . '.php');
    return true;
}

if (file_exists($filePath . '/index.php')) {
    serve_php($filePath . '/index.php');
    return true;
}

return false;
