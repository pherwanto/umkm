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
    $base = rtrim((string)app_config()['base_url'], '/');
    $scriptDir = str_replace('\\', '/', (string)dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (($scriptDir === '/' || $scriptDir === '.') && ($base === '/public' || str_ends_with($base, '/public'))) {
        $base = preg_replace('#/public$#', '', $base) ?: '';
    }
    $path = ltrim($path, '/');
    if ($base === '') return '/' . $path;
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
