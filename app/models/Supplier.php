<?php
require_once __DIR__ . '/../core/Database.php';

class Supplier
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM supplier WHERE umkm_id = :umkm_id ORDER BY nama_supplier ASC");
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }
}
