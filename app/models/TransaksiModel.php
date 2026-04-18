<?php
require_once __DIR__ . '/../core/BaseModel.php';
class TransaksiModel extends BaseModel {
    private function toIntNumber($value): int {
        return (int)round((float)$value);
    }

    private function ensurePenjualanTaxColumns(): void {
        static $ensured = false;
        if ($ensured) return;
        // Kolom pajak sudah dimigrasikan manual oleh user.
        // Sengaja no-op agar tidak menjalankan DDL saat runtime transaksi.
        $ensured = true;
    }

    public function pelangganAll(int $umkmId): array {
        $st=$this->db->prepare("SELECT * FROM pelanggan WHERE umkm_id=? ORDER BY nama_pelanggan");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }

    public function supplierAll(int $umkmId): array {
        $st=$this->db->prepare("SELECT * FROM supplier WHERE umkm_id=? ORDER BY nama_supplier");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }

    public function searchPelanggan(int $umkmId, string $q): array {
        $sql = "SELECT id, nama_pelanggan, telepon, alamat FROM pelanggan WHERE umkm_id=?";
        $params = [$umkmId];
        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (nama_pelanggan LIKE ? OR telepon LIKE ? OR alamat LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY nama_pelanggan LIMIT 20";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function searchSupplier(int $umkmId, string $q): array {
        $sql = "SELECT id, nama_supplier, telepon, alamat FROM supplier WHERE umkm_id=?";
        $params = [$umkmId];
        $q = trim($q);
        if ($q !== '') {
            $sql .= " AND (nama_supplier LIKE ? OR telepon LIKE ? OR alamat LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY nama_supplier LIMIT 20";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }


    public function kategoriProdukAll(int $umkmId): array {
        $st=$this->db->prepare("SELECT id, nama_kategori FROM kategori_produk WHERE umkm_id=? ORDER BY nama_kategori");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }

    public function produkAll(int $umkmId): array {
        $st=$this->db->prepare("SELECT id, kode_produk, nama_produk, harga_beli, harga_jual, stok FROM produk WHERE umkm_id=? AND status='aktif' ORDER BY nama_produk");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }

    public function searchProduk(int $umkmId, string $q, ?int $kategoriId = null): array {
        $q = trim($q);
        $sql = "SELECT p.id, p.kode_produk, p.barcode, p.nama_produk, p.harga_jual, p.harga_beli, p.stok, p.stok_minimum, p.gambar_produk,
                       kp.id AS kategori_id, COALESCE(kp.nama_kategori, 'Lainnya') AS nama_kategori
                FROM produk p
                LEFT JOIN kategori_produk kp ON kp.id=p.kategori_id
                WHERE p.umkm_id=? AND p.status='aktif'";
        $params = [$umkmId];
        if ($kategoriId && $kategoriId > 0) {
            $sql .= " AND p.kategori_id=?";
            $params[] = $kategoriId;
        }
        if ($q !== '') {
            $sql .= " AND (p.nama_produk LIKE ? OR p.kode_produk LIKE ? OR p.barcode LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY CASE WHEN p.barcode=? THEN 0 ELSE 1 END, p.nama_produk LIMIT 60";
        $params[] = $q;
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    private function nextCode(string $table, string $prefix, int $umkmId): string {
        $st = $this->db->prepare("SELECT COUNT(*)+1 FROM {$table} WHERE umkm_id=?");
        $st->execute([$umkmId]);
        $n=(int)$st->fetchColumn();
        return $prefix . date('Ymd') . '-' . str_pad((string)$n,4,'0',STR_PAD_LEFT);
    }

    private function normalizeDateTime(?string $value): string {
        if (!$value) return date('Y-m-d H:i:s');
        return date('Y-m-d H:i:s', strtotime($value));
    }

    private function normalizeItems(array $items): array {
        $normalized = [];
        foreach ($items as $it) {
            $pid = (int)($it['produk_id'] ?? 0);
            $qty = max(0, $this->toIntNumber($it['qty'] ?? 0));
            $harga = max(0, $this->toIntNumber($it['harga'] ?? 0));
            if ($pid < 1 || $qty <= 0) continue;
            $normalized[] = [
                'produk_id' => $pid,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $qty * $harga,
            ];
        }
        return $normalized;
    }

    private function calcPenjualanTotals(array $items, array $d): array {
        $subtotal = $this->toIntNumber(array_sum(array_column($items, 'subtotal')));
        $diskon = max(0, $this->toIntNumber($d['diskon'] ?? 0));
        $dasarPajak = max(0, $subtotal - $diskon);
        $isPajakEnabled = (string)($d['is_pajak_enabled'] ?? '0') === '1';
        $pajakPersen = max(0, min(100, $this->toIntNumber($d['pajak_persen'] ?? 0)));
        $nilaiPajak = $isPajakEnabled ? (int)ceil($dasarPajak * $pajakPersen / 100) : 0;
        $total = (int)($dasarPajak + $nilaiPajak);

        $dibayarInput = $d['dibayar'] ?? null;
        $dibayarRaw = ($dibayarInput === '' || $dibayarInput === null) ? $total : $this->toIntNumber($dibayarInput);
        $dibayar = min(max($dibayarRaw, 0), $total);
        $sisa = $total - $dibayar;
        $status = $sisa <= 0 ? 'lunas' : ($dibayar > 0 ? 'sebagian' : 'belum_bayar');
        $metode = $sisa > 0 ? 'kredit' : (($d['metode_pembayaran'] ?? 'tunai') ?: 'tunai');

        return [
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'is_pajak_enabled' => $isPajakEnabled ? 1 : 0,
            'pajak_persen' => (int)$pajakPersen,
            'nilai_pajak' => (int)$nilaiPajak,
            'total' => $total,
            'dibayar' => $dibayar,
            'sisa' => $sisa,
            'status' => $status,
            'metode' => $metode,
        ];
    }

    private function createKasEntry(int $umkmId, string $tanggal, int $kategoriId, string $jenis, float $nominal, string $referensiTipe, int $referensiId, string $keterangan, int $userId): void {
        if ($nominal <= 0) return;
        $this->db->prepare("INSERT INTO kas (umkm_id,tanggal,kategori_kas_id,jenis,nominal,referensi_tipe,referensi_id,keterangan,user_id) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$umkmId,$tanggal,$kategoriId,$jenis,$nominal,$referensiTipe,$referensiId,$keterangan,$userId]);
    }

    private function findOrCreateKasKategori(int $umkmId, string $nama, string $jenis): int {
        $st=$this->db->prepare("SELECT id FROM kategori_kas WHERE umkm_id=? AND nama_kategori=? AND jenis=? LIMIT 1");
        $st->execute([$umkmId,$nama,$jenis]);
        $id=$st->fetchColumn();
        if($id) return (int)$id;
        $this->db->prepare("INSERT INTO kategori_kas (umkm_id,nama_kategori,jenis,keterangan) VALUES (?,?,?,?)")
            ->execute([$umkmId,$nama,$jenis,$nama]);
        return (int)$this->db->lastInsertId();
    }

    private function adjustProductStock(int $umkmId, int $produkId, string $tanggal, string $jenisMutasi, float $qtyMasuk, float $qtyKeluar, string $referensiTipe, int $referensiId, string $keterangan, int $userId): void {
        $st=$this->db->prepare("SELECT stok,nama_produk FROM produk WHERE id=? AND umkm_id=? FOR UPDATE");
        $st->execute([$produkId,$umkmId]);
        $pr=$st->fetch();
        if(!$pr) throw new Exception('Produk tidak ditemukan.');
        $stokSebelum = (float)$pr['stok'];
        $stokSesudah = $stokSebelum + $qtyMasuk - $qtyKeluar;
        if ($stokSesudah < 0) {
            throw new Exception('Stok produk ' . $pr['nama_produk'] . ' tidak mencukupi.');
        }
        $this->db->prepare("UPDATE produk SET stok=? WHERE id=? AND umkm_id=?")->execute([$stokSesudah,$produkId,$umkmId]);
        $this->db->prepare("INSERT INTO mutasi_stok (umkm_id,produk_id,tanggal,jenis_mutasi,qty_masuk,qty_keluar,stok_sebelum,stok_sesudah,referensi_tipe,referensi_id,keterangan,user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$umkmId,$produkId,$tanggal,$jenisMutasi,$qtyMasuk,$qtyKeluar,$stokSebelum,$stokSesudah,$referensiTipe,$referensiId,$keterangan,$userId]);
    }

    private function logAudit(?int $umkmId, int $userId, string $aktivitas, string $tabelRef, ?int $dataRefId, string $keterangan): void {
        $this->db->prepare("INSERT INTO audit_log (umkm_id,user_id,aktivitas,tabel_ref,data_ref_id,keterangan) VALUES (?,?,?,?,?,?)")
            ->execute([$umkmId,$userId,$aktivitas,$tabelRef,$dataRefId,$keterangan]);
    }

    private function fetchPelangganName(?int $pelangganId, int $umkmId): string {
        if (!$pelangganId) return '-';
        $st = $this->db->prepare("SELECT nama_pelanggan FROM pelanggan WHERE id=? AND umkm_id=? LIMIT 1");
        $st->execute([$pelangganId,$umkmId]);
        return (string)($st->fetchColumn() ?: '-');
    }

    private function fetchSupplierName(?int $supplierId, int $umkmId): string {
        if (!$supplierId) return '-';
        $st = $this->db->prepare("SELECT nama_supplier FROM supplier WHERE id=? AND umkm_id=? LIMIT 1");
        $st->execute([$supplierId,$umkmId]);
        return (string)($st->fetchColumn() ?: '-');
    }

    private function buildItemSummary(array $items): string {
        $parts = [];
        foreach ($items as $it) {
            $nama = $it['nama_produk'] ?? $it['kode_produk'] ?? ('Produk #' . ($it['produk_id'] ?? 0));
            $qty = number_format((float)($it['qty'] ?? 0), 0, ',', '.');
            $harga = fmt_rp($it['harga'] ?? 0);
            $parts[] = $nama . ' x ' . $qty . ' @ ' . $harga;
        }
        return $parts ? implode('; ', $parts) : '-';
    }

    private function buildPenjualanSnapshot(array $header, array $items, int $umkmId): string {
        $pelangganNama = $this->fetchPelangganName(isset($header['pelanggan_id']) ? (int)$header['pelanggan_id'] : null, $umkmId);
        return sprintf(
            'Kode: %s | Tanggal: %s | Pelanggan: %s | Total: %s | Dibayar: %s | Sisa: %s | Status: %s | Item: %s',
            $header['kode_penjualan'] ?? '-',
            isset($header['tanggal']) ? date('d-m-Y H:i', strtotime((string)$header['tanggal'])) : '-',
            $pelangganNama,
            fmt_rp($header['total'] ?? 0),
            fmt_rp($header['dibayar'] ?? 0),
            fmt_rp($header['sisa'] ?? 0),
            $header['status_pembayaran'] ?? '-',
            $this->buildItemSummary($items)
        );
    }

    private function buildPembelianSnapshot(array $header, array $items, int $umkmId): string {
        $supplierNama = $this->fetchSupplierName(isset($header['supplier_id']) ? (int)$header['supplier_id'] : null, $umkmId);
        return sprintf(
            'Kode: %s | Tanggal: %s | Supplier: %s | Total: %s | Dibayar: %s | Sisa: %s | Status: %s | Item: %s',
            $header['kode_pembelian'] ?? '-',
            isset($header['tanggal']) ? date('d-m-Y H:i', strtotime((string)$header['tanggal'])) : '-',
            $supplierNama,
            fmt_rp($header['total'] ?? 0),
            fmt_rp($header['dibayar'] ?? 0),
            fmt_rp($header['sisa'] ?? 0),
            $header['status_pembayaran'] ?? '-',
            $this->buildItemSummary($items)
        );
    }


    public function penjualanAll(int $umkmId, array $filters=[]): array {
        $sql="SELECT p.*, c.nama_pelanggan, u.nama AS user_nama FROM penjualan p LEFT JOIN pelanggan c ON c.id=p.pelanggan_id JOIN users u ON u.id=p.user_id WHERE p.umkm_id=?";
        $params=[$umkmId];
        if (!empty($filters['q'])) {
            $sql .= " AND (p.kode_penjualan LIKE ? OR c.nama_pelanggan LIKE ? OR u.nama LIKE ?)";
            $like='%' . $filters['q'] . '%';
            $params[]=$like; $params[]=$like; $params[]=$like;
        }
        if (!empty($filters['date_from'])) { $sql .= " AND DATE(p.tanggal) >= ?"; $params[]=$filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND DATE(p.tanggal) <= ?"; $params[]=$filters['date_to']; }
        $sql .= " ORDER BY p.tanggal DESC, p.id DESC";
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }

    public function penjualanFind(int $id, int $umkmId): ?array {
        $sql="SELECT p.*, c.nama_pelanggan, c.alamat AS alamat_pelanggan, c.telepon AS telepon_pelanggan, u.nama AS user_nama, m.nama_umkm FROM penjualan p JOIN users u ON u.id=p.user_id JOIN umkm m ON m.id=p.umkm_id LEFT JOIN pelanggan c ON c.id=p.pelanggan_id WHERE p.id=? AND p.umkm_id=? LIMIT 1";
        $st=$this->db->prepare($sql);
        $st->execute([$id,$umkmId]);
        $head = $st->fetch();
        if (!$head) return null;
        $st2=$this->db->prepare("SELECT d.*, p.nama_produk, p.kode_produk FROM penjualan_detail d JOIN produk p ON p.id=d.produk_id WHERE d.penjualan_id=? ORDER BY d.id");
        $st2->execute([$id]);
        $head['items'] = $st2->fetchAll();
        return $head;
    }

    public function penjualanDetailView(int $id, int $umkmId): ?array {
        $row = $this->penjualanFind($id, $umkmId);
        if (!$row) return null;
        $st = $this->db->prepare("SELECT * FROM piutang WHERE penjualan_id=? AND umkm_id=? ORDER BY id DESC LIMIT 1");
        $st->execute([$id,$umkmId]);
        $piutang = $st->fetch();
        if ($piutang) {
            $st2 = $this->db->prepare("SELECT pp.*, u.nama AS user_nama FROM pembayaran_piutang pp JOIN users u ON u.id=pp.user_id WHERE pp.piutang_id=? ORDER BY pp.tanggal_bayar DESC, pp.id DESC");
            $st2->execute([$piutang['id']]);
            $piutang['payments'] = $st2->fetchAll();
        }
        $row['piutang'] = $piutang ?: null;
        $row['logs'] = $this->penjualanHistory($umkmId, $id);
        return $row;
    }

    public function penjualanHistory(int $umkmId, ?int $penjualanId = null, array $filters=[]): array {
        $sql = "SELECT a.*, u.nama AS user_nama FROM audit_log a JOIN users u ON u.id=a.user_id WHERE a.umkm_id=? AND a.tabel_ref='penjualan'";
        $params = [$umkmId];
        if ($penjualanId) { $sql .= " AND a.data_ref_id=?"; $params[] = $penjualanId; }
        if (!empty($filters['q'])) {
            $sql .= " AND (a.aktivitas LIKE ? OR a.keterangan LIKE ? OR u.nama LIKE ?)";
            $like='%' . $filters['q'] . '%';
            $params[]=$like; $params[]=$like; $params[]=$like;
        }
        if (!empty($filters['date_from'])) { $sql .= " AND DATE(a.created_at) >= ?"; $params[]=$filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND DATE(a.created_at) <= ?"; $params[]=$filters['date_to']; }
        $sql .= " ORDER BY a.created_at DESC, a.id DESC LIMIT 300";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }


    public function pembelianAll(int $umkmId, array $filters=[]): array {
        $sql="SELECT p.*, s.nama_supplier, u.nama AS user_nama FROM pembelian p LEFT JOIN supplier s ON s.id=p.supplier_id JOIN users u ON u.id=p.user_id WHERE p.umkm_id=?";
        $params=[$umkmId];
        if (!empty($filters['q'])) {
            $sql .= " AND (p.kode_pembelian LIKE ? OR s.nama_supplier LIKE ? OR u.nama LIKE ?)";
            $like='%' . $filters['q'] . '%';
            $params[]=$like; $params[]=$like; $params[]=$like;
        }
        if (!empty($filters['supplier_id'])) { $sql .= " AND p.supplier_id=?"; $params[]=(int)$filters['supplier_id']; }
        if (!empty($filters['date_from'])) { $sql .= " AND DATE(p.tanggal) >= ?"; $params[]=$filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND DATE(p.tanggal) <= ?"; $params[]=$filters['date_to']; }
        $sql .= " ORDER BY p.tanggal DESC, p.id DESC";
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }

    public function piutangAll(int $umkmId): array {
        $sql="SELECT pt.*, p.kode_penjualan, c.nama_pelanggan, c.alamat AS alamat_pelanggan, c.telepon AS telepon_pelanggan, m.nama_umkm, m.alamat AS alamat_umkm
              FROM piutang pt
              JOIN penjualan p ON p.id=pt.penjualan_id
              JOIN umkm m ON m.id=pt.umkm_id
              LEFT JOIN pelanggan c ON c.id=pt.pelanggan_id
              WHERE pt.umkm_id=? ORDER BY pt.id DESC";
        $st=$this->db->prepare($sql); $st->execute([$umkmId]); return $st->fetchAll();
    }

    public function hutangAll(int $umkmId): array {
        $sql="SELECT h.*, p.kode_pembelian, s.nama_supplier, s.telepon AS telepon_supplier FROM hutang h JOIN pembelian p ON p.id=h.pembelian_id LEFT JOIN supplier s ON s.id=h.supplier_id WHERE h.umkm_id=? ORDER BY h.id DESC";
        $st=$this->db->prepare($sql); $st->execute([$umkmId]); return $st->fetchAll();
    }

    public function invoice(int $id, int $umkmId): ?array {
        $sql="SELECT p.*, c.nama_pelanggan, c.alamat AS alamat_pelanggan, c.telepon AS telepon_pelanggan, u.nama AS user_nama, m.nama_umkm, m.alamat AS alamat_umkm, m.telepon AS telp_umkm FROM penjualan p JOIN umkm m ON m.id=p.umkm_id LEFT JOIN pelanggan c ON c.id=p.pelanggan_id JOIN users u ON u.id=p.user_id WHERE p.id=? AND p.umkm_id=? LIMIT 1";
        $st=$this->db->prepare($sql); $st->execute([$id,$umkmId]); $head=$st->fetch();
        if(!$head) return null;
        $st2=$this->db->prepare("SELECT d.*, pr.kode_produk, pr.nama_produk FROM penjualan_detail d JOIN produk pr ON pr.id=d.produk_id WHERE d.penjualan_id=? ORDER BY d.id"); $st2->execute([$id]);
        $head['items']=$st2->fetchAll(); return $head;
    }

    public function pembelianFind(int $id, int $umkmId): ?array {
        $sql="SELECT p.*, s.nama_supplier, s.alamat AS alamat_supplier, s.telepon AS telepon_supplier, u.nama AS user_nama, m.nama_umkm, m.alamat AS alamat_umkm, m.telepon AS telp_umkm FROM pembelian p JOIN umkm m ON m.id=p.umkm_id LEFT JOIN supplier s ON s.id=p.supplier_id JOIN users u ON u.id=p.user_id WHERE p.id=? AND p.umkm_id=? LIMIT 1";
        $st=$this->db->prepare($sql); $st->execute([$id,$umkmId]);
        $head=$st->fetch();
        if(!$head) return null;
        $st2=$this->db->prepare("SELECT d.*, pr.kode_produk, pr.nama_produk, pr.satuan_id FROM pembelian_detail d JOIN produk pr ON pr.id=d.produk_id WHERE d.pembelian_id=? ORDER BY d.id");
        $st2->execute([$id]);
        $head['items']=$st2->fetchAll();
        $st3=$this->db->prepare("SELECT h.*, (SELECT COALESCE(SUM(ph.nominal_bayar),0) FROM pembayaran_hutang ph WHERE ph.hutang_id=h.id) AS pembayaran_tercatat FROM hutang h WHERE h.pembelian_id=? AND h.umkm_id=? LIMIT 1");
        $st3->execute([$id,$umkmId]);
        $head['hutang']=$st3->fetch() ?: null;
        return $head;
    }

    public function pembelianDetailView(int $id, int $umkmId): ?array {
        $row = $this->pembelianFind($id, $umkmId);
        if (!$row) return null;
        if (!empty($row['hutang']['id'])) {
            $st = $this->db->prepare("SELECT ph.*, u.nama AS user_nama FROM pembayaran_hutang ph JOIN users u ON u.id=ph.user_id WHERE ph.hutang_id=? ORDER BY ph.tanggal_bayar DESC, ph.id DESC");
            $st->execute([(int)$row['hutang']['id']]);
            $row['hutang']['payments'] = $st->fetchAll();
        }
        $row['logs'] = $this->pembelianHistory($umkmId, $id);
        return $row;
    }

    public function pembelianHistory(int $umkmId, ?int $pembelianId = null, array $filters=[]): array {
        $sql = "SELECT a.*, u.nama AS user_nama FROM audit_log a JOIN users u ON u.id=a.user_id WHERE a.umkm_id=? AND a.tabel_ref='pembelian'";
        $params = [$umkmId];
        if ($pembelianId) { $sql .= " AND a.data_ref_id=?"; $params[] = $pembelianId; }
        if (!empty($filters['q'])) {
            $sql .= " AND (a.aktivitas LIKE ? OR a.keterangan LIKE ? OR u.nama LIKE ?)";
            $like='%' . $filters['q'] . '%';
            $params[]=$like; $params[]=$like; $params[]=$like;
        }
        if (!empty($filters['date_from'])) { $sql .= " AND DATE(a.created_at) >= ?"; $params[]=$filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND DATE(a.created_at) <= ?"; $params[]=$filters['date_to']; }
        $sql .= " ORDER BY a.created_at DESC, a.id DESC LIMIT 300";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }


    public function createPenjualan(array $d, int $umkmId, int $userId): int {
        $this->ensurePenjualanTaxColumns();
        $this->db->beginTransaction();
        try {
            $kode = $this->nextCode('penjualan','PJ-',$umkmId);
            $tanggal = $this->normalizeDateTime($d['tanggal'] ?? null);
            $pelangganId = ($d['pelanggan_id'] ?? '') !== '' ? (int)$d['pelanggan_id'] : null;
            $items = $this->normalizeItems($d['items'] ?? []);
            if (!$items) throw new Exception('Item penjualan wajib diisi.');
            $totals = $this->calcPenjualanTotals($items, $d);
            $subtotal = $totals['subtotal'];
            $diskon = $totals['diskon'];
            $total = $totals['total'];
            $dibayar = $totals['dibayar'];
            $sisa = $totals['sisa'];
            $status = $totals['status'];
            $metode = $totals['metode'];
            $sql = "INSERT INTO penjualan (umkm_id,kode_penjualan,tanggal,pelanggan_id,user_id,subtotal,diskon,is_pajak_enabled,pajak_persen,pajak_nominal,total,dibayar,sisa,metode_pembayaran,status_pembayaran,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $this->db->prepare($sql)->execute([$umkmId,$kode,$tanggal,$pelangganId,$userId,$subtotal,$diskon,$totals['is_pajak_enabled'],$totals['pajak_persen'],$totals['nilai_pajak'],$total,$dibayar,$sisa,$metode,$status,$d['keterangan'] ?? null]);
            $penjualanId = (int)$this->db->lastInsertId();
            foreach ($items as $it) {
                $this->db->prepare("INSERT INTO penjualan_detail (penjualan_id,produk_id,qty,harga,subtotal) VALUES (?,?,?,?,?)")
                    ->execute([$penjualanId,$it['produk_id'],$it['qty'],$it['harga'],$it['subtotal']]);
                $this->adjustProductStock($umkmId, $it['produk_id'], $tanggal, 'penjualan', 0, $it['qty'], 'penjualan', $penjualanId, 'Penjualan ' . $kode, $userId);
            }
            if($dibayar > 0) {
                $kategoriId = $this->findOrCreateKasKategori($umkmId,'Penerimaan Penjualan','masuk');
                $this->createKasEntry($umkmId,$tanggal,$kategoriId,'masuk',$dibayar,'penjualan',$penjualanId,'Pembayaran penjualan '.$kode,$userId);
            }
            if($sisa > 0) {
                $this->db->prepare("INSERT INTO piutang (umkm_id,penjualan_id,pelanggan_id,tanggal_piutang,jatuh_tempo,total_piutang,total_bayar,sisa_piutang,status) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$umkmId,$penjualanId,$pelangganId,date('Y-m-d', strtotime($tanggal)),($d['jatuh_tempo'] ?? '') ?: null,$total,$dibayar,$sisa,$status === 'sebagian' ? 'sebagian' : 'belum_lunas']);
            }
            $snapshotHeader = [
                'kode_penjualan' => $kode,
                'tanggal' => $tanggal,
                'pelanggan_id' => $pelangganId,
                'total' => $total,
                'dibayar' => $dibayar,
                'sisa' => $sisa,
                'status_pembayaran' => $status,
            ];
            $this->logAudit($umkmId, $userId, 'PENJUALAN_CREATE', 'penjualan', $penjualanId, 'Transaksi penjualan dibuat. ' . $this->buildPenjualanSnapshot($snapshotHeader, $items, $umkmId));
            $this->db->commit();
            return $penjualanId;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function updatePenjualan(int $id, array $d, int $umkmId, int $userId): void {
        $this->ensurePenjualanTaxColumns();
        $this->db->beginTransaction();
        try {
            $old = $this->penjualanFind($id, $umkmId);
            if (!$old) throw new Exception('Data penjualan tidak ditemukan.');
            $beforeSnapshot = $this->buildPenjualanSnapshot($old, $old['items'], $umkmId);
            $tanggal = $this->normalizeDateTime($d['tanggal'] ?? null);
            $pelangganId = ($d['pelanggan_id'] ?? '') !== '' ? (int)$d['pelanggan_id'] : null;
            $items = $this->normalizeItems($d['items'] ?? []);
            if (!$items) throw new Exception('Item penjualan wajib diisi.');

            foreach ($old['items'] as $oldItem) {
                $this->adjustProductStock($umkmId, (int)$oldItem['produk_id'], $tanggal, 'koreksi', (float)$oldItem['qty'], 0, 'penjualan_edit', $id, 'Rollback edit penjualan ' . $old['kode_penjualan'], $userId);
            }
            $this->db->prepare("DELETE FROM mutasi_stok WHERE umkm_id=? AND referensi_tipe IN ('penjualan','penjualan_edit') AND referensi_id=?")->execute([$umkmId,$id]);
            $this->db->prepare("DELETE FROM penjualan_detail WHERE penjualan_id=?")->execute([$id]);
            $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='penjualan' AND referensi_id=?")->execute([$umkmId,$id]);
            $piutangIds = $this->db->prepare("SELECT id FROM piutang WHERE penjualan_id=? AND umkm_id=?");
            $piutangIds->execute([$id,$umkmId]);
            $piutangIds = array_map('intval', array_column($piutangIds->fetchAll(), 'id'));
            if ($piutangIds) {
                $placeholders = implode(',', array_fill(0, count($piutangIds), '?'));
                $params = array_merge([$umkmId], $piutangIds);
                $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='piutang' AND referensi_id IN ($placeholders)")->execute($params);
                $this->db->prepare("DELETE FROM pembayaran_piutang WHERE piutang_id IN ($placeholders)")->execute($piutangIds);
            }
            $this->db->prepare("DELETE FROM piutang WHERE penjualan_id=? AND umkm_id=?")->execute([$id,$umkmId]);

            $totals = $this->calcPenjualanTotals($items, $d);
            $subtotal = $totals['subtotal'];
            $diskon = $totals['diskon'];
            $total = $totals['total'];
            $dibayar = $totals['dibayar'];
            $sisa = $totals['sisa'];
            $status = $totals['status'];
            $metode = $totals['metode'];

            $this->db->prepare("UPDATE penjualan SET tanggal=?, pelanggan_id=?, subtotal=?, diskon=?, is_pajak_enabled=?, pajak_persen=?, pajak_nominal=?, total=?, dibayar=?, sisa=?, metode_pembayaran=?, status_pembayaran=?, keterangan=?, updated_at=NOW() WHERE id=? AND umkm_id=?")
                ->execute([$tanggal,$pelangganId,$subtotal,$diskon,$totals['is_pajak_enabled'],$totals['pajak_persen'],$totals['nilai_pajak'],$total,$dibayar,$sisa,$metode,$status,$d['keterangan'] ?? null,$id,$umkmId]);

            foreach ($items as $it) {
                $this->db->prepare("INSERT INTO penjualan_detail (penjualan_id,produk_id,qty,harga,subtotal) VALUES (?,?,?,?,?)")
                    ->execute([$id,$it['produk_id'],$it['qty'],$it['harga'],$it['subtotal']]);
                $this->adjustProductStock($umkmId, $it['produk_id'], $tanggal, 'penjualan', 0, $it['qty'], 'penjualan', $id, 'Update penjualan ' . $old['kode_penjualan'], $userId);
            }
            if($dibayar > 0) {
                $kategoriId = $this->findOrCreateKasKategori($umkmId,'Penerimaan Penjualan','masuk');
                $this->createKasEntry($umkmId,$tanggal,$kategoriId,'masuk',$dibayar,'penjualan',$id,'Pembayaran penjualan '.$old['kode_penjualan'],$userId);
            }
            if($sisa > 0) {
                $this->db->prepare("INSERT INTO piutang (umkm_id,penjualan_id,pelanggan_id,tanggal_piutang,jatuh_tempo,total_piutang,total_bayar,sisa_piutang,status) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$umkmId,$id,$pelangganId,date('Y-m-d', strtotime($tanggal)),($d['jatuh_tempo'] ?? '') ?: null,$total,$dibayar,$sisa,$status === 'sebagian' ? 'sebagian' : 'belum_lunas']);
            }
            $afterSnapshot = $this->buildPenjualanSnapshot([
                'kode_penjualan' => $old['kode_penjualan'],
                'tanggal' => $tanggal,
                'pelanggan_id' => $pelangganId,
                'total' => $total,
                'dibayar' => $dibayar,
                'sisa' => $sisa,
                'status_pembayaran' => $status,
            ], $items, $umkmId);
            $this->logAudit($umkmId, $userId, 'PENJUALAN_UPDATE', 'penjualan', $id, "Sebelum: {$beforeSnapshot}
Sesudah: {$afterSnapshot}");

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }


    public function deletePenjualan(int $id, int $umkmId, int $userId): void {
        $this->db->beginTransaction();
        try {
            $old = $this->penjualanFind($id, $umkmId);
            if (!$old) throw new Exception('Data penjualan tidak ditemukan.');
            $snapshot = $this->buildPenjualanSnapshot($old, $old['items'], $umkmId);

            $rollbackTanggal = date('Y-m-d H:i:s');

            foreach ($old['items'] as $oldItem) {
                $this->adjustProductStock(
                    $umkmId,
                    (int)$oldItem['produk_id'],
                    $rollbackTanggal,
                    'koreksi',
                    (float)$oldItem['qty'],
                    0,
                    'penjualan_delete',
                    $id,
                    'Rollback hapus penjualan ' . $old['kode_penjualan'],
                    $userId
                );
            }

            $piutangIds = $this->db->prepare("SELECT id FROM piutang WHERE penjualan_id=? AND umkm_id=?");
            $piutangIds->execute([$id, $umkmId]);
            $piutangIds = array_map('intval', array_column($piutangIds->fetchAll(), 'id'));

            $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='penjualan' AND referensi_id=?")
                ->execute([$umkmId, $id]);

            if ($piutangIds) {
                $placeholders = implode(',', array_fill(0, count($piutangIds), '?'));
                $params = array_merge([$umkmId], $piutangIds);
                $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='piutang' AND referensi_id IN ($placeholders)")
                    ->execute($params);
                $this->db->prepare("DELETE FROM pembayaran_piutang WHERE piutang_id IN ($placeholders)")
                    ->execute($piutangIds);
            }

            $this->db->prepare("DELETE FROM piutang WHERE penjualan_id=? AND umkm_id=?")
                ->execute([$id, $umkmId]);
            $this->db->prepare("DELETE FROM penjualan_detail WHERE penjualan_id=?")
                ->execute([$id]);
            $this->db->prepare("DELETE FROM penjualan WHERE id=? AND umkm_id=?")
                ->execute([$id, $umkmId]);
            $this->logAudit($umkmId, $userId, 'PENJUALAN_DELETE', 'penjualan', $id, 'Transaksi penjualan dihapus. Snapshot terakhir: ' . $snapshot);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function createPembelian(array $d, int $umkmId, int $userId): int {
        $this->db->beginTransaction();
        try {
            $kode = $this->nextCode('pembelian','PB-',$umkmId);
            $tanggal = $this->normalizeDateTime($d['tanggal'] ?? null);
            $supplierId = ($d['supplier_id'] ?? '') !== '' ? (int)$d['supplier_id'] : null;
            $items = $this->normalizeItems($d['items'] ?? []);
            if(!$items) throw new Exception('Item pembelian wajib diisi.');
            $subtotal = array_sum(array_column($items, 'subtotal'));
            $diskon=max(0,$this->toIntNumber($d['diskon'] ?? 0));
            $total=max(0,$this->toIntNumber($subtotal-$diskon));
            $dibayarInput = $d['dibayar'] ?? null;
            $dibayarRaw = ($dibayarInput === '' || $dibayarInput === null) ? $total : $this->toIntNumber($dibayarInput);
            $dibayar=min(max($dibayarRaw, 0),$total);
            $sisa=$total-$dibayar;
            $status=$sisa<=0?'lunas':($dibayar>0?'sebagian':'belum_bayar');
            $metode=$sisa>0?'hutang':(($d['metode_pembayaran'] ?? 'tunai')?:'tunai');
            $sql="INSERT INTO pembelian (umkm_id,kode_pembelian,tanggal,supplier_id,user_id,subtotal,diskon,total,dibayar,sisa,metode_pembayaran,status_pembayaran,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $this->db->prepare($sql)->execute([$umkmId,$kode,$tanggal,$supplierId,$userId,$subtotal,$diskon,$total,$dibayar,$sisa,$metode,$status,clean_text($d['keterangan'] ?? '', 1000) ?: null]);
            $pembelianId=(int)$this->db->lastInsertId();
            foreach($items as $it){
                $this->db->prepare("INSERT INTO pembelian_detail (pembelian_id,produk_id,qty,harga,subtotal) VALUES (?,?,?,?,?)")->execute([$pembelianId,$it['produk_id'],$it['qty'],$it['harga'],$it['subtotal']]);
                $this->adjustProductStock($umkmId, $it['produk_id'], $tanggal, 'pembelian', $it['qty'], 0, 'pembelian', $pembelianId, 'Pembelian ' . $kode, $userId);
            }
            if($dibayar>0){
                $kategoriId=$this->findOrCreateKasKategori($umkmId,'Pembayaran Pembelian','keluar');
                $this->createKasEntry($umkmId,$tanggal,$kategoriId,'keluar',$dibayar,'pembelian',$pembelianId,'Pembayaran pembelian '.$kode,$userId);
            }
            if($sisa>0){
                $this->db->prepare("INSERT INTO hutang (umkm_id,pembelian_id,supplier_id,tanggal_hutang,jatuh_tempo,total_hutang,total_bayar,sisa_hutang,status) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$umkmId,$pembelianId,$supplierId,date('Y-m-d', strtotime($tanggal)),($d['jatuh_tempo'] ?? '') ?: null,$total,$dibayar,$sisa,$status==='sebagian'?'sebagian':'belum_lunas']);
            }
            $snapshotHeader = ['kode_pembelian'=>$kode,'tanggal'=>$tanggal,'supplier_id'=>$supplierId,'total'=>$total,'dibayar'=>$dibayar,'sisa'=>$sisa,'status_pembayaran'=>$status];
            $this->logAudit($umkmId, $userId, 'PEMBELIAN_CREATE', 'pembelian', $pembelianId, 'Transaksi pembelian dibuat. ' . $this->buildPembelianSnapshot($snapshotHeader, $items, $umkmId));
            $this->db->commit();
            return $pembelianId;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function updatePembelian(int $id, array $d, int $umkmId, int $userId): void {
        $this->db->beginTransaction();
        try {
            $old = $this->pembelianFind($id, $umkmId);
            if (!$old) throw new Exception('Data pembelian tidak ditemukan.');
            $beforeSnapshot = $this->buildPembelianSnapshot($old, $old['items'], $umkmId);
            $tanggal = $this->normalizeDateTime($d['tanggal'] ?? null);
            $supplierId = ($d['supplier_id'] ?? '') !== '' ? (int)$d['supplier_id'] : null;
            $items = $this->normalizeItems($d['items'] ?? []);
            if (!$items) throw new Exception('Item pembelian wajib diisi.');
            foreach ($old['items'] as $oldItem) {
                $this->adjustProductStock($umkmId, (int)$oldItem['produk_id'], $tanggal, 'koreksi', 0, (float)$oldItem['qty'], 'pembelian_edit', $id, 'Rollback edit pembelian ' . $old['kode_pembelian'], $userId);
            }
            $this->db->prepare("DELETE FROM mutasi_stok WHERE umkm_id=? AND referensi_tipe IN ('pembelian','pembelian_edit') AND referensi_id=?")->execute([$umkmId,$id]);
            $this->db->prepare("DELETE FROM pembelian_detail WHERE pembelian_id=?")->execute([$id]);
            $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='pembelian' AND referensi_id=?")->execute([$umkmId,$id]);
            $hutangIds = $this->db->prepare("SELECT id FROM hutang WHERE pembelian_id=? AND umkm_id=?");
            $hutangIds->execute([$id,$umkmId]);
            $hutangIds = array_map('intval', array_column($hutangIds->fetchAll(), 'id'));
            if ($hutangIds) {
                $placeholders = implode(',', array_fill(0, count($hutangIds), '?'));
                $params = array_merge([$umkmId], $hutangIds);
                $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='hutang' AND referensi_id IN ($placeholders)")->execute($params);
                $this->db->prepare("DELETE FROM pembayaran_hutang WHERE hutang_id IN ($placeholders)")->execute($hutangIds);
            }
            $this->db->prepare("DELETE FROM hutang WHERE pembelian_id=? AND umkm_id=?")->execute([$id,$umkmId]);
            $subtotal = array_sum(array_column($items, 'subtotal'));
            $diskon=max(0,$this->toIntNumber($d['diskon'] ?? 0));
            $total=max(0,$this->toIntNumber($subtotal-$diskon));
            $dibayarInput = $d['dibayar'] ?? null;
            $dibayarRaw = ($dibayarInput === '' || $dibayarInput === null) ? $total : $this->toIntNumber($dibayarInput);
            $dibayar=min(max($dibayarRaw, 0),$total);
            $sisa=$total-$dibayar;
            $status=$sisa<=0?'lunas':($dibayar>0?'sebagian':'belum_bayar');
            $metode=$sisa>0?'hutang':(($d['metode_pembayaran'] ?? 'tunai')?:'tunai');
            $this->db->prepare("UPDATE pembelian SET tanggal=?, supplier_id=?, subtotal=?, diskon=?, total=?, dibayar=?, sisa=?, metode_pembayaran=?, status_pembayaran=?, keterangan=?, updated_at=NOW() WHERE id=? AND umkm_id=?")
                ->execute([$tanggal,$supplierId,$subtotal,$diskon,$total,$dibayar,$sisa,$metode,$status,clean_text($d['keterangan'] ?? '', 1000) ?: null,$id,$umkmId]);
            foreach($items as $it){
                $this->db->prepare("INSERT INTO pembelian_detail (pembelian_id,produk_id,qty,harga,subtotal) VALUES (?,?,?,?,?)")->execute([$id,$it['produk_id'],$it['qty'],$it['harga'],$it['subtotal']]);
                $this->adjustProductStock($umkmId, $it['produk_id'], $tanggal, 'pembelian', $it['qty'], 0, 'pembelian', $id, 'Update pembelian ' . $old['kode_pembelian'], $userId);
            }
            if($dibayar>0){
                $kategoriId=$this->findOrCreateKasKategori($umkmId,'Pembayaran Pembelian','keluar');
                $this->createKasEntry($umkmId,$tanggal,$kategoriId,'keluar',$dibayar,'pembelian',$id,'Pembayaran pembelian '.$old['kode_pembelian'],$userId);
            }
            if($sisa>0){
                $this->db->prepare("INSERT INTO hutang (umkm_id,pembelian_id,supplier_id,tanggal_hutang,jatuh_tempo,total_hutang,total_bayar,sisa_hutang,status) VALUES (?,?,?,?,?,?,?,?,?)")->execute([$umkmId,$id,$supplierId,date('Y-m-d', strtotime($tanggal)),($d['jatuh_tempo'] ?? '') ?: null,$total,$dibayar,$sisa,$status==='sebagian'?'sebagian':'belum_lunas']);
            }
            $afterSnapshot = $this->buildPembelianSnapshot(['kode_pembelian'=>$old['kode_pembelian'],'tanggal'=>$tanggal,'supplier_id'=>$supplierId,'total'=>$total,'dibayar'=>$dibayar,'sisa'=>$sisa,'status_pembayaran'=>$status], $items, $umkmId);
            $this->logAudit($umkmId, $userId, 'PEMBELIAN_UPDATE', 'pembelian', $id, "Sebelum: {$beforeSnapshot}
Sesudah: {$afterSnapshot}");
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function deletePembelian(int $id, int $umkmId, int $userId): void {
        $this->db->beginTransaction();
        try {
            $old = $this->pembelianFind($id, $umkmId);
            if (!$old) throw new Exception('Data pembelian tidak ditemukan.');
            $snapshot = $this->buildPembelianSnapshot($old, $old['items'], $umkmId);
            $rollbackTanggal = date('Y-m-d H:i:s');
            foreach ($old['items'] as $oldItem) {
                $this->adjustProductStock($umkmId, (int)$oldItem['produk_id'], $rollbackTanggal, 'koreksi', 0, (float)$oldItem['qty'], 'pembelian_delete', $id, 'Rollback hapus pembelian ' . $old['kode_pembelian'], $userId);
            }
            $hutangIds = $this->db->prepare("SELECT id FROM hutang WHERE pembelian_id=? AND umkm_id=?");
            $hutangIds->execute([$id,$umkmId]);
            $hutangIds = array_map('intval', array_column($hutangIds->fetchAll(), 'id'));
            $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='pembelian' AND referensi_id=?")->execute([$umkmId, $id]);
            if ($hutangIds) {
                $placeholders = implode(',', array_fill(0, count($hutangIds), '?'));
                $params = array_merge([$umkmId], $hutangIds);
                $this->db->prepare("DELETE FROM kas WHERE umkm_id=? AND referensi_tipe='hutang' AND referensi_id IN ($placeholders)")->execute($params);
                $this->db->prepare("DELETE FROM pembayaran_hutang WHERE hutang_id IN ($placeholders)")->execute($hutangIds);
            }
            $this->db->prepare("DELETE FROM hutang WHERE pembelian_id=? AND umkm_id=?")->execute([$id,$umkmId]);
            $this->db->prepare("DELETE FROM pembelian_detail WHERE pembelian_id=?")->execute([$id]);
            $this->db->prepare("DELETE FROM pembelian WHERE id=? AND umkm_id=?")->execute([$id,$umkmId]);
            $this->logAudit($umkmId, $userId, 'PEMBELIAN_DELETE', 'pembelian', $id, 'Transaksi pembelian dihapus. Snapshot terakhir: ' . $snapshot);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function piutangWhatsappData(int $id, int $umkmId): ?array {
        $sql = "SELECT pt.*, p.kode_penjualan, c.nama_pelanggan, c.alamat AS alamat_pelanggan, c.telepon AS telepon_pelanggan,
                       m.nama_umkm, m.alamat AS alamat_umkm
                FROM piutang pt
                JOIN penjualan p ON p.id=pt.penjualan_id
                JOIN umkm m ON m.id=pt.umkm_id
                LEFT JOIN pelanggan c ON c.id=pt.pelanggan_id
                WHERE pt.id=? AND pt.umkm_id=? LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->execute([$id,$umkmId]);
        return $st->fetch() ?: null;
    }

    public function payPiutang(int $id, float $nominal, string $tanggal, string $metode, string $keterangan, int $umkmId, int $userId): void {
        $this->db->beginTransaction();
        try {
            $st=$this->db->prepare("SELECT * FROM piutang WHERE id=? AND umkm_id=? FOR UPDATE"); $st->execute([$id,$umkmId]); $row=$st->fetch(); if(!$row) throw new Exception('Piutang tidak ditemukan.'); if($nominal<=0) throw new Exception('Nominal harus lebih dari 0.'); if($nominal>(float)$row['sisa_piutang']) $nominal=(float)$row['sisa_piutang'];
            $nominal = (float)max(0, $this->toIntNumber($nominal));
            $tanggal = $this->normalizeDateTime($tanggal);
            $this->db->prepare("INSERT INTO pembayaran_piutang (piutang_id,tanggal_bayar,nominal_bayar,metode_pembayaran,keterangan,user_id) VALUES (?,?,?,?,?,?)")->execute([$id,$tanggal,$nominal,$metode,clean_text($keterangan, 500),$userId]);
            $totalBayar=(float)$row['total_bayar']+$nominal; $sisa=(float)$row['sisa_piutang']-$nominal; $status=$sisa<=0?'lunas':'sebagian';
            $this->db->prepare("UPDATE piutang SET total_bayar=?, sisa_piutang=?, status=? WHERE id=?")->execute([$totalBayar,$sisa,$status,$id]);
            $kategoriId=$this->findOrCreateKasKategori($umkmId,'Pelunasan Piutang','masuk');
            $this->createKasEntry($umkmId,$tanggal,$kategoriId,'masuk',$nominal,'piutang',$id,clean_text($keterangan, 255) ?: 'Pembayaran piutang',$userId);
            $penjualan = $this->penjualanFind((int)$row['penjualan_id'], $umkmId);
            if ($penjualan) {
                $this->logAudit($umkmId, $userId, 'PENJUALAN_PIUTANG_BAYAR', 'penjualan', (int)$row['penjualan_id'], 'Pembayaran piutang sebesar ' . fmt_rp($nominal) . ' untuk transaksi ' . $penjualan['kode_penjualan'] . '. Sisa piutang sekarang: ' . fmt_rp($sisa));
            }
            $this->db->commit();
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function payHutang(int $id, float $nominal, string $tanggal, string $metode, string $keterangan, int $umkmId, int $userId): void {
        $this->db->beginTransaction();
        try {
            $st=$this->db->prepare("SELECT * FROM hutang WHERE id=? AND umkm_id=? FOR UPDATE"); $st->execute([$id,$umkmId]); $row=$st->fetch(); if(!$row) throw new Exception('Hutang tidak ditemukan.'); if($nominal<=0) throw new Exception('Nominal harus lebih dari 0.'); if($nominal>(float)$row['sisa_hutang']) $nominal=(float)$row['sisa_hutang'];
            $nominal = (float)max(0, $this->toIntNumber($nominal));
            $tanggal = $this->normalizeDateTime($tanggal);
            $this->db->prepare("INSERT INTO pembayaran_hutang (hutang_id,tanggal_bayar,nominal_bayar,metode_pembayaran,keterangan,user_id) VALUES (?,?,?,?,?,?)")->execute([$id,$tanggal,$nominal,$metode,clean_text($keterangan, 500),$userId]);
            $totalBayar=(float)$row['total_bayar']+$nominal; $sisa=(float)$row['sisa_hutang']-$nominal; $status=$sisa<=0?'lunas':'sebagian';
            $this->db->prepare("UPDATE hutang SET total_bayar=?, sisa_hutang=?, status=? WHERE id=?")->execute([$totalBayar,$sisa,$status,$id]);
            $kategoriId=$this->findOrCreateKasKategori($umkmId,'Pembayaran Hutang','keluar');
            $this->createKasEntry($umkmId,$tanggal,$kategoriId,'keluar',$nominal,'hutang',$id,clean_text($keterangan, 255) ?: 'Pembayaran hutang',$userId);
            $pembelian = $this->pembelianFind((int)$row['pembelian_id'], $umkmId);
            if ($pembelian) {
                $this->logAudit($umkmId, $userId, 'PEMBELIAN_HUTANG_BAYAR', 'pembelian', (int)$row['pembelian_id'], 'Pembayaran hutang sebesar ' . fmt_rp($nominal) . ' untuk transaksi ' . $pembelian['kode_pembelian'] . '. Sisa hutang sekarang: ' . fmt_rp($sisa));
            }
            $this->db->commit();
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }
}
