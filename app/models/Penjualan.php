<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/KategoriKas.php';
require_once __DIR__ . '/Kas.php';
require_once __DIR__ . '/MutasiStok.php';
require_once __DIR__ . '/Piutang.php';

class Penjualan
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT p.*, c.nama_pelanggan, u.nama AS nama_user
                FROM penjualan p
                LEFT JOIN pelanggan c ON c.id = p.pelanggan_id
                INNER JOIN users u ON u.id = p.user_id
                WHERE p.umkm_id = :umkm_id
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }

    public static function totalSales(int $umkmId): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM penjualan WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (float) $stmt->fetchColumn();
    }

    public static function nextCode(int $umkmId): string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM penjualan WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        $next = (int) $stmt->fetchColumn() + 1;
        return 'JUAL-' . date('Ymd') . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function createTransaction(int $umkmId, int $userId, array $header, array $items): int
    {
        if ($items === []) {
            throw new RuntimeException('Item penjualan wajib diisi.');
        }
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $subtotal = 0.0;
            $preparedItems = [];
            foreach ($items as $item) {
                $stmt = $pdo->prepare("SELECT id, nama_produk, stok FROM produk WHERE id = :id AND umkm_id = :umkm_id LIMIT 1");
                $stmt->execute(['id' => $item['produk_id'], 'umkm_id' => $umkmId]);
                $produk = $stmt->fetch();
                if (!$produk) {
                    throw new RuntimeException('Produk tidak ditemukan pada UMKM ini.');
                }
                $qty = (float) $item['qty'];
                $harga = (float) $item['harga'];
                if ($qty <= 0 || $harga < 0) {
                    throw new RuntimeException('Qty dan harga item penjualan tidak valid.');
                }
                if ($qty > (float) $produk['stok']) {
                    throw new RuntimeException('Stok produk ' . $produk['nama_produk'] . ' tidak mencukupi.');
                }
                $lineSubtotal = $qty * $harga;
                $subtotal += $lineSubtotal;
                $preparedItems[] = [
                    'produk' => $produk,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $diskon = (float) ($header['diskon'] ?? 0);
            $dibayar = (float) ($header['dibayar'] ?? 0);
            $total = max($subtotal - $diskon, 0);
            if ($dibayar > $total) {
                throw new RuntimeException('Nominal dibayar tidak boleh melebihi total transaksi.');
            }
            $sisa = $total - $dibayar;
            $status = $sisa <= 0 ? 'lunas' : ($dibayar > 0 ? 'sebagian' : 'belum_bayar');
            $metode = $sisa > 0 ? 'kredit' : ($header['metode_pembayaran'] ?: 'tunai');

            $stmt = $pdo->prepare("INSERT INTO penjualan
                (umkm_id, kode_penjualan, tanggal, pelanggan_id, user_id, subtotal, diskon, total, dibayar, sisa, metode_pembayaran, status_pembayaran, keterangan)
                VALUES
                (:umkm_id, :kode_penjualan, :tanggal, :pelanggan_id, :user_id, :subtotal, :diskon, :total, :dibayar, :sisa, :metode_pembayaran, :status_pembayaran, :keterangan)");
            $stmt->execute([
                'umkm_id' => $umkmId,
                'kode_penjualan' => $header['kode_penjualan'],
                'tanggal' => $header['tanggal'],
                'pelanggan_id' => $header['pelanggan_id'] ?: null,
                'user_id' => $userId,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'total' => $total,
                'dibayar' => $dibayar,
                'sisa' => $sisa,
                'metode_pembayaran' => $metode,
                'status_pembayaran' => $status,
                'keterangan' => $header['keterangan'],
            ]);
            $penjualanId = (int) $pdo->lastInsertId();

            foreach ($preparedItems as $item) {
                $stmt = $pdo->prepare("INSERT INTO penjualan_detail (penjualan_id, produk_id, qty, harga, subtotal)
                                       VALUES (:penjualan_id, :produk_id, :qty, :harga, :subtotal)");
                $stmt->execute([
                    'penjualan_id' => $penjualanId,
                    'produk_id' => $item['produk']['id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stokSebelum = (float) $item['produk']['stok'];
                $stokSesudah = $stokSebelum - $item['qty'];
                $stmt = $pdo->prepare("UPDATE produk SET stok = :stok WHERE id = :id AND umkm_id = :umkm_id");
                $stmt->execute([
                    'stok' => $stokSesudah,
                    'id' => $item['produk']['id'],
                    'umkm_id' => $umkmId,
                ]);

                MutasiStok::create([
                    'umkm_id' => $umkmId,
                    'produk_id' => $item['produk']['id'],
                    'tanggal' => $header['tanggal'],
                    'jenis_mutasi' => 'penjualan',
                    'qty_masuk' => 0,
                    'qty_keluar' => $item['qty'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'penjualan',
                    'referensi_id' => $penjualanId,
                    'keterangan' => 'Transaksi penjualan ' . $header['kode_penjualan'],
                    'user_id' => $userId,
                ]);
            }

            if ($dibayar > 0) {
                $kategoriKasId = KategoriKas::getOrCreateDefault($umkmId, 'masuk');
                Kas::create([
                    'umkm_id' => $umkmId,
                    'tanggal' => $header['tanggal'],
                    'kategori_kas_id' => $kategoriKasId,
                    'jenis' => 'masuk',
                    'nominal' => $dibayar,
                    'referensi_tipe' => 'penjualan',
                    'referensi_id' => $penjualanId,
                    'keterangan' => 'Penerimaan penjualan ' . $header['kode_penjualan'],
                    'user_id' => $userId,
                ]);
            }

            if ($sisa > 0) {
                Piutang::createFromSale([
                    'umkm_id' => $umkmId,
                    'penjualan_id' => $penjualanId,
                    'pelanggan_id' => $header['pelanggan_id'] ?: null,
                    'tanggal_piutang' => substr($header['tanggal'], 0, 10),
                    'jatuh_tempo' => $header['jatuh_tempo'] ?: null,
                    'total_piutang' => $total,
                    'total_bayar' => $dibayar,
                    'sisa_piutang' => $sisa,
                    'status' => $dibayar > 0 ? 'sebagian' : 'belum_lunas',
                ]);
            }

            $pdo->commit();
            return $penjualanId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
