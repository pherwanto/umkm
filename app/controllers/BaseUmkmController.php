<?php

class BaseUmkmController
{
    protected function ensureUmkmUser(): array
    {
        require_login();
        $user = auth_user();
        if (empty($user['umkm_id'])) {
            flash('error', 'Akun ini tidak terhubung ke UMKM tertentu.');
            redirect('index.php?page=dashboard');
        }
        return $user;
    }
}
