<?php
require_once __DIR__ . '/../core/BaseModel.php';
class LaporanModel extends BaseModel {
    public function penjualan(int $umkmId, ?string $from, ?string $to): array {
        $where = " WHERE p.umkm_id=? "; $params=[$umkmId];
        if($from){$where .= " AND DATE(p.tanggal) >= ?"; $params[]=$from;} if($to){$where .= " AND DATE(p.tanggal) <= ?"; $params[]=$to;}
        $sql="SELECT p.tanggal,p.kode_penjualan,p.total,p.dibayar,p.sisa,p.status_pembayaran,c.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan c ON c.id=p.pelanggan_id {$where} ORDER BY p.tanggal DESC";
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    public function pembelian(int $umkmId, ?string $from, ?string $to): array {
        $where = " WHERE p.umkm_id=? "; $params=[$umkmId];
        if($from){$where .= " AND DATE(p.tanggal) >= ?"; $params[]=$from;} if($to){$where .= " AND DATE(p.tanggal) <= ?"; $params[]=$to;}
        $sql="SELECT p.tanggal,p.kode_pembelian,p.total,p.dibayar,p.sisa,p.status_pembayaran,s.nama_supplier FROM pembelian p LEFT JOIN supplier s ON s.id=p.supplier_id {$where} ORDER BY p.tanggal DESC";
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    public function kas(int $umkmId, ?string $from, ?string $to): array {
        $where = " WHERE k.umkm_id=? "; $params=[$umkmId];
        if($from){$where .= " AND DATE(k.tanggal) >= ?"; $params[]=$from;} if($to){$where .= " AND DATE(k.tanggal) <= ?"; $params[]=$to;}
        $sql="SELECT k.tanggal,k.jenis,k.nominal,k.keterangan,kk.nama_kategori FROM kas k JOIN kategori_kas kk ON kk.id=k.kategori_kas_id {$where} ORDER BY k.tanggal DESC";
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    public function labaRugiSummary(int $umkmId, ?string $from, ?string $to): array {
        $where = " WHERE j.umkm_id=? "; $params = [$umkmId];
        if ($from) { $where .= " AND DATE(j.tanggal) >= ?"; $params[] = $from; }
        if ($to) { $where .= " AND DATE(j.tanggal) <= ?"; $params[] = $to; }

        $st = $this->db->prepare("SELECT COALESCE(SUM(j.total),0) FROM penjualan j {$where}");
        $st->execute($params);
        $pendapatanPenjualan = (float)$st->fetchColumn();

        $hppSql = "SELECT COALESCE(SUM(d.qty * COALESCE(p.harga_beli,0)),0)
                   FROM penjualan_detail d
                   JOIN penjualan j ON j.id=d.penjualan_id
                   JOIN produk p ON p.id=d.produk_id {$where}";
        $st = $this->db->prepare($hppSql);
        $st->execute($params);
        $hppEstimasi = (float)$st->fetchColumn();

        $whereKas = " WHERE k.umkm_id=? AND k.referensi_tipe='manual' ";
        $paramsKas = [$umkmId];
        if ($from) { $whereKas .= " AND DATE(k.tanggal) >= ?"; $paramsKas[] = $from; }
        if ($to) { $whereKas .= " AND DATE(k.tanggal) <= ?"; $paramsKas[] = $to; }

        $st = $this->db->prepare("SELECT COALESCE(SUM(k.nominal),0) FROM kas k {$whereKas} AND k.jenis='masuk'");
        $st->execute($paramsKas);
        $pendapatanLain = (float)$st->fetchColumn();

        $st = $this->db->prepare("SELECT COALESCE(SUM(k.nominal),0) FROM kas k {$whereKas} AND k.jenis='keluar'");
        $st->execute($paramsKas);
        $bebanOperasional = (float)$st->fetchColumn();

        $labaKotor = $pendapatanPenjualan - $hppEstimasi;
        $labaBersih = $labaKotor + $pendapatanLain - $bebanOperasional;

        return [
            'pendapatan_penjualan' => $pendapatanPenjualan,
            'hpp_estimasi' => $hppEstimasi,
            'pendapatan_lain' => $pendapatanLain,
            'beban_operasional' => $bebanOperasional,
            'laba_kotor' => $labaKotor,
            'laba_bersih' => $labaBersih,
        ];
    }
    public function labaRugiExpenses(int $umkmId, ?string $from, ?string $to): array {
        $where = " WHERE k.umkm_id=? AND k.referensi_tipe='manual' AND k.jenis='keluar' "; $params=[$umkmId];
        if ($from) { $where .= " AND DATE(k.tanggal) >= ?"; $params[] = $from; }
        if ($to) { $where .= " AND DATE(k.tanggal) <= ?"; $params[] = $to; }
        $sql = "SELECT k.tanggal,k.nominal,k.keterangan,kk.nama_kategori
                FROM kas k JOIN kategori_kas kk ON kk.id=k.kategori_kas_id {$where}
                ORDER BY k.tanggal DESC";
        $st = $this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }

    public function rekapLabaRugiPerUmkm(?string $from, ?string $to): array {
        $rows = $this->db->query("SELECT id, nama_umkm FROM umkm ORDER BY nama_umkm")->fetchAll();
        $result = [];
        foreach ($rows as $umkm) {
            $summary = $this->labaRugiSummary((int)$umkm['id'], $from, $to);
            $result[] = [
                'umkm_id' => (int)$umkm['id'],
                'nama_umkm' => $umkm['nama_umkm'],
                'pendapatan_penjualan' => $summary['pendapatan_penjualan'],
                'hpp_estimasi' => $summary['hpp_estimasi'],
                'pendapatan_lain' => $summary['pendapatan_lain'],
                'beban_operasional' => $summary['beban_operasional'],
                'laba_kotor' => $summary['laba_kotor'],
                'laba_bersih' => $summary['laba_bersih'],
            ];
        }
        return $result;
    }
}
