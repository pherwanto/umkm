<?php
function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    return $config;
}

function base_url(string $path = ''): string
{
    $base = rtrim(app_config()['base_url'], '/');
    $path = ltrim($path, '/');
    return $path ? $base . '/' . $path : $base . '/';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
