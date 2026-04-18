<?php
require_once __DIR__ . '/../core/Database.php';

class KategoriKas
{
    public static function getOrCreateDefault(int $umkmId, string $jenis): int
    {
        $pdo = Database::getInstance();
        $nama = $jenis === 'masuk' ? 'Kas Masuk Otomatis' : 'Kas Keluar Otomatis';

        $stmt = $pdo->prepare("SELECT id FROM kategori_kas WHERE umkm_id = :umkm_id AND jenis = :jenis AND nama_kategori = :nama LIMIT 1");
        $stmt->execute([
            'umkm_id' => $umkmId,
            'jenis' => $jenis,
            'nama' => $nama,
        ]);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int) $id;
        }

        $stmt = $pdo->prepare("INSERT INTO kategori_kas (umkm_id, nama_kategori, jenis, keterangan) VALUES (:umkm_id, :nama, :jenis, :keterangan)");
        $stmt->execute([
            'umkm_id' => $umkmId,
            'nama' => $nama,
            'jenis' => $jenis,
            'keterangan' => 'Dibuat otomatis oleh sistem',
        ]);

        return (int) $pdo->lastInsertId();
    }
}
