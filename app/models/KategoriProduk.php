<?php
require_once __DIR__ . '/../core/Database.php';

class KategoriProduk
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM kategori_produk WHERE umkm_id = :umkm_id ORDER BY nama_kategori ASC");
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }
}
