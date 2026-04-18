<?php
function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('index.php');
    }
}

function require_role(array $roles): void
{
    $user = auth_user();
    if (!$user || !in_array($user['role_name'], $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}
