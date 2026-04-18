<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/KategoriKas.php';
require_once __DIR__ . '/Kas.php';
require_once __DIR__ . '/MutasiStok.php';
require_once __DIR__ . '/Hutang.php';

class Pembelian
{
    public static function allByUmkm(int $umkmId): array
    {
        $pdo = Database::getInstance();
        $sql = "SELECT p.*, s.nama_supplier, u.nama AS nama_user
                FROM pembelian p
                LEFT JOIN supplier s ON s.id = p.supplier_id
                INNER JOIN users u ON u.id = p.user_id
                WHERE p.umkm_id = :umkm_id
                ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['umkm_id' => $umkmId]);
        return $stmt->fetchAll();
    }

    public static function totalPurchase(int $umkmId): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM pembelian WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        return (float) $stmt->fetchColumn();
    }

    public static function nextCode(int $umkmId): string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pembelian WHERE umkm_id = :umkm_id");
        $stmt->execute(['umkm_id' => $umkmId]);
        $next = (int) $stmt->fetchColumn() + 1;
        return 'BELI-' . date('Ymd') . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function createTransaction(int $umkmId, int $userId, array $header, array $items): int
    {
        if ($items === []) {
            throw new RuntimeException('Item pembelian wajib diisi.');
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
                    throw new RuntimeException('Qty dan harga item pembelian tidak valid.');
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
            $metode = $sisa > 0 ? 'hutang' : ($header['metode_pembayaran'] ?: 'tunai');

            $stmt = $pdo->prepare("INSERT INTO pembelian
                (umkm_id, kode_pembelian, tanggal, supplier_id, user_id, subtotal, diskon, total, dibayar, sisa, metode_pembayaran, status_pembayaran, keterangan)
                VALUES
                (:umkm_id, :kode_pembelian, :tanggal, :supplier_id, :user_id, :subtotal, :diskon, :total, :dibayar, :sisa, :metode_pembayaran, :status_pembayaran, :keterangan)");
            $stmt->execute([
                'umkm_id' => $umkmId,
                'kode_pembelian' => $header['kode_pembelian'],
                'tanggal' => $header['tanggal'],
                'supplier_id' => $header['supplier_id'] ?: null,
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
            $pembelianId = (int) $pdo->lastInsertId();

            foreach ($preparedItems as $item) {
                $stmt = $pdo->prepare("INSERT INTO pembelian_detail (pembelian_id, produk_id, qty, harga, subtotal)
                                       VALUES (:pembelian_id, :produk_id, :qty, :harga, :subtotal)");
                $stmt->execute([
                    'pembelian_id' => $pembelianId,
                    'produk_id' => $item['produk']['id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stokSebelum = (float) $item['produk']['stok'];
                $stokSesudah = $stokSebelum + $item['qty'];
                $stmt = $pdo->prepare("UPDATE produk SET stok = :stok, harga_beli = :harga_beli WHERE id = :id AND umkm_id = :umkm_id");
                $stmt->execute([
                    'stok' => $stokSesudah,
                    'harga_beli' => $item['harga'],
                    'id' => $item['produk']['id'],
                    'umkm_id' => $umkmId,
                ]);

                MutasiStok::create([
                    'umkm_id' => $umkmId,
                    'produk_id' => $item['produk']['id'],
                    'tanggal' => $header['tanggal'],
                    'jenis_mutasi' => 'pembelian',
                    'qty_masuk' => $item['qty'],
                    'qty_keluar' => 0,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'referensi_tipe' => 'pembelian',
                    'referensi_id' => $pembelianId,
                    'keterangan' => 'Transaksi pembelian ' . $header['kode_pembelian'],
                    'user_id' => $userId,
                ]);
            }

            if ($dibayar > 0) {
                $kategoriKasId = KategoriKas::getOrCreateDefault($umkmId, 'keluar');
                Kas::create([
                    'umkm_id' => $umkmId,
                    'tanggal' => $header['tanggal'],
                    'kategori_kas_id' => $kategoriKasId,
                    'jenis' => 'keluar',
                    'nominal' => $dibayar,
                    'referensi_tipe' => 'pembelian',
                    'referensi_id' => $pembelianId,
                    'keterangan' => 'Pembayaran pembelian ' . $header['kode_pembelian'],
                    'user_id' => $userId,
                ]);
            }

            if ($sisa > 0) {
                Hutang::createFromPurchase([
                    'umkm_id' => $umkmId,
                    'pembelian_id' => $pembelianId,
                    'supplier_id' => $header['supplier_id'] ?: null,
                    'tanggal_hutang' => substr($header['tanggal'], 0, 10),
                    'jatuh_tempo' => $header['jatuh_tempo'] ?: null,
                    'total_hutang' => $total,
                    'total_bayar' => $dibayar,
                    'sisa_hutang' => $sisa,
                    'status' => $dibayar > 0 ? 'sebagian' : 'belum_lunas',
                ]);
            }

            $pdo->commit();
            return $pembelianId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
