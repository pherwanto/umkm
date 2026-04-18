<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Kas.php';
require_once __DIR__ . '/KategoriKas.php';

class Piutang
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT p.*, pl.kode_penjualan, pl.tanggal, pl.total, c.nama_pelanggan
                FROM piutang p
                INNER JOIN penjualan pl ON pl.id = p.penjualan_id
                LEFT JOIN pelanggan c ON c.id = p.pelanggan_id
                WHERE p.umkm_id = :umkm_id
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id, int $umkmId): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM piutang WHERE id = :id AND umkm_id = :umkm_id LIMIT 1");
        $stmt->execute(['id' => $id, 'umkm_id' => $umkmId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function totalOutstanding(int $umkmId): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(sisa_piutang),0) FROM piutang WHERE umkm_id = :umkm_id AND status != 'lunas'");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (float) $stmt->fetchColumn();
    }

    public static function createFromSale(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO piutang
            (umkm_id, penjualan_id, pelanggan_id, tanggal_piutang, jatuh_tempo, total_piutang, total_bayar, sisa_piutang, status)
            VALUES (:umkm_id, :penjualan_id, :pelanggan_id, :tanggal_piutang, :jatuh_tempo, :total_piutang, :total_bayar, :sisa_piutang, :status)");
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }

    public static function registerPayment(int $id, int $umkmId, float $nominal, string $tanggal, string $metode, string $keterangan, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $piutang = self::find($id, $umkmId);
            if (!$piutang) {
                throw new RuntimeException('Data piutang tidak ditemukan.');
            }
            if ($nominal <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih besar dari 0.');
            }
            if ($nominal > (float) $piutang['sisa_piutang']) {
                throw new RuntimeException('Nominal melebihi sisa piutang.');
            }

            $stmt = $pdo->prepare("INSERT INTO pembayaran_piutang (piutang_id, tanggal_bayar, nominal_bayar, metode_pembayaran, keterangan, user_id)
                                   VALUES (:piutang_id, :tanggal_bayar, :nominal_bayar, :metode_pembayaran, :keterangan, :user_id)");
            $stmt->execute([
                'piutang_id' => $id,
                'tanggal_bayar' => $tanggal,
                'nominal_bayar' => $nominal,
                'metode_pembayaran' => $metode,
                'keterangan' => $keterangan,
                'user_id' => $userId,
            ]);

            $totalBayar = (float) $piutang['total_bayar'] + $nominal;
            $sisa = (float) $piutang['total_piutang'] - $totalBayar;
            $status = $sisa <= 0 ? 'lunas' : 'sebagian';

            $stmt = $pdo->prepare("UPDATE piutang SET total_bayar = :total_bayar, sisa_piutang = :sisa_piutang, status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                'total_bayar' => $totalBayar,
                'sisa_piutang' => max($sisa, 0),
                'status' => $status,
                'id' => $id,
            ]);

            $stmt = $pdo->prepare("UPDATE penjualan SET dibayar = :dibayar, sisa = :sisa, status_pembayaran = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                'dibayar' => $totalBayar,
                'sisa' => max($sisa, 0),
                'status' => $status === 'lunas' ? 'lunas' : 'sebagian',
                'id' => $piutang['penjualan_id'],
            ]);

            $kategoriKasId = KategoriKas::getOrCreateDefault($umkmId, 'masuk');
            Kas::create([
                'umkm_id' => $umkmId,
                'tanggal' => $tanggal,
                'kategori_kas_id' => $kategoriKasId,
                'jenis' => 'masuk',
                'nominal' => $nominal,
                'referensi_tipe' => 'pembayaran_piutang',
                'referensi_id' => $id,
                'keterangan' => $keterangan !== '' ? $keterangan : 'Pembayaran piutang',
                'user_id' => $userId,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
