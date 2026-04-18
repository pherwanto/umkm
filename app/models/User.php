<?php
require_once __DIR__ . '/../core/Database.php';

class User
{
    public static function findByUsername(string $username): ?array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT u.*, r.nama_role AS role_name, m.nama_umkm
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                LEFT JOIN umkm m ON m.id = u.umkm_id
                WHERE u.username = :username AND u.status = 'aktif'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateLastLogin(int $id): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
