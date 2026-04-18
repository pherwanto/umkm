<?php
require_once __DIR__ . '/../core/Database.php';

class Kas
{
    public static function create(array $data): bool
    {
        $pdo = Database::getInstance();
        $sql = "INSERT INTO kas (umkm_id, tanggal, kategori_kas_id, jenis, nominal, referensi_tipe, referensi_id, keterangan, user_id)
                VALUES (:umkm_id, :tanggal, :kategori_kas_id, :jenis, :nominal, :referensi_tipe, :referensi_id, :keterangan, :user_id)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public static function summaryByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT
                COALESCE(SUM(CASE WHEN jenis='masuk' THEN nominal ELSE 0 END),0) AS total_masuk,
                COALESCE(SUM(CASE WHEN jenis='keluar' THEN nominal ELSE 0 END),0) AS total_keluar
            FROM kas WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        $row = $stmt->fetch() ?: ['total_masuk' => 0, 'total_keluar' => 0];
        $row['saldo'] = (float) $row['total_masuk'] - (float) $row['total_keluar'];
        return $row;
    }
}
