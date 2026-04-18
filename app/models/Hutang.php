<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Kas.php';
require_once __DIR__ . '/KategoriKas.php';

class Hutang
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT h.*, pb.kode_pembelian, pb.tanggal, pb.total, s.nama_supplier
                FROM hutang h
                INNER JOIN pembelian pb ON pb.id = h.pembelian_id
                LEFT JOIN supplier s ON s.id = h.supplier_id
                WHERE h.umkm_id = :umkm_id
                ORDER BY h.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id, int $umkmId): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM hutang WHERE id = :id AND umkm_id = :umkm_id LIMIT 1");
        $stmt->execute(['id' => $id, 'umkm_id' => $umkmId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function totalOutstanding(int $umkmId): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(sisa_hutang),0) FROM hutang WHERE umkm_id = :umkm_id AND status != 'lunas'");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (float) $stmt->fetchColumn();
    }

    public static function createFromPurchase(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO hutang
            (umkm_id, pembelian_id, supplier_id, tanggal_hutang, jatuh_tempo, total_hutang, total_bayar, sisa_hutang, status)
            VALUES (:umkm_id, :pembelian_id, :supplier_id, :tanggal_hutang, :jatuh_tempo, :total_hutang, :total_bayar, :sisa_hutang, :status)");
        $stmt->execute($data);
        return (int) $pdo->lastInsertId();
    }

    public static function registerPayment(int $id, int $umkmId, float $nominal, string $tanggal, string $metode, string $keterangan, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $hutang = self::find($id, $umkmId);
            if (!$hutang) {
                throw new RuntimeException('Data hutang tidak ditemukan.');
            }
            if ($nominal <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih besar dari 0.');
            }
            if ($nominal > (float) $hutang['sisa_hutang']) {
                throw new RuntimeException('Nominal melebihi sisa hutang.');
            }

            $stmt = $pdo->prepare("INSERT INTO pembayaran_hutang (hutang_id, tanggal_bayar, nominal_bayar, metode_pembayaran, keterangan, user_id)
                                   VALUES (:hutang_id, :tanggal_bayar, :nominal_bayar, :metode_pembayaran, :keterangan, :user_id)");
            $stmt->execute([
                'hutang_id' => $id,
                'tanggal_bayar' => $tanggal,
                'nominal_bayar' => $nominal,
                'metode_pembayaran' => $metode,
                'keterangan' => $keterangan,
                'user_id' => $userId,
            ]);

            $totalBayar = (float) $hutang['total_bayar'] + $nominal;
            $sisa = (float) $hutang['total_hutang'] - $totalBayar;
            $status = $sisa <= 0 ? 'lunas' : 'sebagian';

            $stmt = $pdo->prepare("UPDATE hutang SET total_bayar = :total_bayar, sisa_hutang = :sisa_hutang, status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                'total_bayar' => $totalBayar,
                'sisa_hutang' => max($sisa, 0),
                'status' => $status,
                'id' => $id,
            ]);

            $stmt = $pdo->prepare("UPDATE pembelian SET dibayar = :dibayar, sisa = :sisa, status_pembayaran = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                'dibayar' => $totalBayar,
                'sisa' => max($sisa, 0),
                'status' => $status === 'lunas' ? 'lunas' : 'sebagian',
                'id' => $hutang['pembelian_id'],
            ]);

            $kategoriKasId = KategoriKas::getOrCreateDefault($umkmId, 'keluar');
            Kas::create([
                'umkm_id' => $umkmId,
                'tanggal' => $tanggal,
                'kategori_kas_id' => $kategoriKasId,
                'jenis' => 'keluar',
                'nominal' => $nominal,
                'referensi_tipe' => 'pembayaran_hutang',
                'referensi_id' => $id,
                'keterangan' => $keterangan !== '' ? $keterangan : 'Pembayaran hutang',
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
