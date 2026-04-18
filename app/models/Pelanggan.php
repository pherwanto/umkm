<?php
require_once __DIR__ . '/../core/Database.php';

class Pelanggan
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE umkm_id = :umkm_id ORDER BY nama_pelanggan ASC");
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }
}
