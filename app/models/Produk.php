<?php
require_once __DIR__ . '/../core/Database.php';

class Produk
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT p.*, kp.nama_kategori, s.nama_satuan
                FROM produk p
                INNER JOIN kategori_produk kp ON kp.id = p.kategori_id
                INNER JOIN satuan s ON s.id = p.satuan_id
                WHERE p.umkm_id = :umkm_id
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }



    public static function activeByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, kode_produk, nama_produk, harga_beli, harga_jual, stok FROM produk WHERE umkm_id = :umkm_id AND status = 'aktif' ORDER BY nama_produk ASC");
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id, int $umkmId): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id AND umkm_id = :umkm_id LIMIT 1");
        $stmt->execute(['id' => $id, 'umkm_id' => $umkmId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): bool
    {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO produk
                (umkm_id, kategori_id, satuan_id, kode_produk, nama_produk, harga_beli, harga_jual, stok, stok_minimum, deskripsi, status)
                VALUES
                (:umkm_id, :kategori_id, :satuan_id, :kode_produk, :nama_produk, :harga_beli, :harga_jual, :stok, :stok_minimum, :deskripsi, :status)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public static function update(int $id, int $umkmId, array $data): bool
    {
        $pdo = Database::getInstance();
        $data['id'] = $id;
        $data['umkm_id'] = $umkmId;
        $sql = "UPDATE produk SET
                    kategori_id = :kategori_id,
                    satuan_id = :satuan_id,
                    kode_produk = :kode_produk,
                    nama_produk = :nama_produk,
                    harga_beli = :harga_beli,
                    harga_jual = :harga_jual,
                    stok = :stok,
                    stok_minimum = :stok_minimum,
                    deskripsi = :deskripsi,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id AND umkm_id = :umkm_id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete(int $id, int $umkmId): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id = :id AND umkm_id = :umkm_id");
        return $stmt->execute(['id' => $id, 'umkm_id' => $umkmId]);
    }

    public static function countByUmkm(int $umkmId): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (int) $stmt->fetchColumn();
    }

    public static function stockLowCount(int $umkmId): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE umkm_id = :umkm_id AND stok <= stok_minimum");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (int) $stmt->fetchColumn();
    }
}
