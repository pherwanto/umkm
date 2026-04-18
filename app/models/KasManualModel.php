<?php
require_once __DIR__ . '/../core/BaseModel.php';
class KasManualModel extends BaseModel {
    public function all(int $umkmId, array $filters=[]): array {
        $sql = "SELECT k.*, kk.nama_kategori, u.nama AS user_nama FROM kas k JOIN kategori_kas kk ON kk.id=k.kategori_kas_id LEFT JOIN users u ON u.id=k.user_id WHERE k.umkm_id=? AND (k.referensi_tipe='manual' OR k.referensi_tipe IS NULL)";
        $params = [$umkmId];
        if (!empty($filters['q'])) {
            $sql .= " AND (kk.nama_kategori LIKE ? OR k.keterangan LIKE ? OR u.nama LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (!empty($filters['jenis'])) { $sql .= " AND k.jenis=?"; $params[] = $filters['jenis']; }
        if (!empty($filters['date_from'])) { $sql .= " AND DATE(k.tanggal) >= ?"; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= " AND DATE(k.tanggal) <= ?"; $params[] = $filters['date_to']; }
        $sql .= " ORDER BY k.tanggal DESC, k.id DESC";
        $st = $this->db->prepare($sql); $st->execute($params);
        return $st->fetchAll();
    }
    public function categories(int $umkmId, ?string $jenis = null): array {
        if ($jenis) {
            $st = $this->db->prepare("SELECT * FROM kategori_kas WHERE umkm_id=? AND jenis=? ORDER BY nama_kategori");
            $st->execute([$umkmId, $jenis]);
            return $st->fetchAll();
        }
        $st = $this->db->prepare("SELECT * FROM kategori_kas WHERE umkm_id=? ORDER BY jenis, nama_kategori");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function find(int $id, int $umkmId): ?array {
        $st = $this->db->prepare("SELECT * FROM kas WHERE id=? AND umkm_id=? AND (referensi_tipe='manual' OR referensi_tipe IS NULL) LIMIT 1");
        $st->execute([$id, $umkmId]);
        return $st->fetch() ?: null;
    }
    public function create(array $data, int $umkmId, int $userId): void {
        $tanggal = $data['tanggal'] ?: date('Y-m-d\TH:i');
        $tanggal = date('Y-m-d H:i:s', strtotime($tanggal));
        $jenis = ($data['jenis'] ?? 'masuk') === 'keluar' ? 'keluar' : 'masuk';
        $kategoriId = (int)($data['kategori_kas_id'] ?? 0);
        $this->assertCategory($kategoriId, $umkmId, $jenis);
        $nominal = (float)($data['nominal'] ?? 0);
        if ($nominal <= 0) throw new Exception('Nominal harus lebih besar dari nol.');
        $this->db->prepare("INSERT INTO kas (umkm_id,tanggal,kategori_kas_id,jenis,nominal,referensi_tipe,referensi_id,keterangan,user_id,created_at) VALUES (?,?,?,?,?,'manual',NULL,?,?,NOW())")
            ->execute([$umkmId, $tanggal, $kategoriId, $jenis, $nominal, trim((string)($data['keterangan'] ?? '')) ?: null, $userId]);
    }
    public function update(int $id, array $data, int $umkmId, int $userId): void {
        $row = $this->find($id, $umkmId);
        if (!$row) throw new Exception('Data kas manual tidak ditemukan.');
        $tanggal = $data['tanggal'] ?: date('Y-m-d\TH:i');
        $tanggal = date('Y-m-d H:i:s', strtotime($tanggal));
        $jenis = ($data['jenis'] ?? 'masuk') === 'keluar' ? 'keluar' : 'masuk';
        $kategoriId = (int)($data['kategori_kas_id'] ?? 0);
        $this->assertCategory($kategoriId, $umkmId, $jenis);
        $nominal = (float)($data['nominal'] ?? 0);
        if ($nominal <= 0) throw new Exception('Nominal harus lebih besar dari nol.');
        $this->db->prepare("UPDATE kas SET tanggal=?, kategori_kas_id=?, jenis=?, nominal=?, keterangan=?, user_id=? WHERE id=? AND umkm_id=?")
            ->execute([$tanggal, $kategoriId, $jenis, $nominal, trim((string)($data['keterangan'] ?? '')) ?: null, $userId, $id, $umkmId]);
    }
    public function delete(int $id, int $umkmId): void {
        $this->db->prepare("DELETE FROM kas WHERE id=? AND umkm_id=? AND (referensi_tipe='manual' OR referensi_tipe IS NULL)")->execute([$id, $umkmId]);
    }
    private function assertCategory(int $kategoriId, int $umkmId, string $jenis): void {
        $st = $this->db->prepare("SELECT COUNT(*) FROM kategori_kas WHERE id=? AND umkm_id=? AND jenis=?");
        $st->execute([$kategoriId, $umkmId, $jenis]);
        if (!(int)$st->fetchColumn()) throw new Exception('Kategori kas tidak valid untuk jenis transaksi yang dipilih.');
    }
}
