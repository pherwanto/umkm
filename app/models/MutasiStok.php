<?php
require_once __DIR__ . '/../core/Database.php';

class MutasiStok
{
    public static function create(array $data): bool
    {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO mutasi_stok
                (umkm_id, produk_id, tanggal, jenis_mutasi, qty_masuk, qty_keluar, stok_sebelum, stok_sesudah, referensi_tipe, referensi_id, keterangan, user_id)
                VALUES
                (:umkm_id, :produk_id, :tanggal, :jenis_mutasi, :qty_masuk, :qty_keluar, :stok_sebelum, :stok_sesudah, :referensi_tipe, :referensi_id, :keterangan, :user_id)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }
}
