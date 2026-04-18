<?php
require_once __DIR__ . '/../core/BaseModel.php';
class ReferenceModel extends BaseModel {
    public function kategoriProdukAll(int $umkmId): array {
        $st = $this->db->prepare("SELECT * FROM kategori_produk WHERE umkm_id=? ORDER BY nama_kategori");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function kategoriProdukFind(int $id, int $umkmId): ?array {
        $st = $this->db->prepare("SELECT * FROM kategori_produk WHERE id=? AND umkm_id=? LIMIT 1");
        $st->execute([$id, $umkmId]);
        return $st->fetch() ?: null;
    }
    public function kategoriProdukCreate(array $data, int $umkmId): void {
        $this->db->prepare("INSERT INTO kategori_produk (umkm_id,nama_kategori,deskripsi) VALUES (?,?,?)")
            ->execute([$umkmId, trim($data['nama_kategori'] ?? ''), trim($data['deskripsi'] ?? '') ?: null]);
    }
    public function kategoriProdukUpdate(int $id, array $data, int $umkmId): void {
        $this->db->prepare("UPDATE kategori_produk SET nama_kategori=?, deskripsi=? WHERE id=? AND umkm_id=?")
            ->execute([trim($data['nama_kategori'] ?? ''), trim($data['deskripsi'] ?? '') ?: null, $id, $umkmId]);
    }
    public function kategoriProdukDelete(int $id, int $umkmId): void {
        $this->db->prepare("DELETE FROM kategori_produk WHERE id=? AND umkm_id=?")->execute([$id, $umkmId]);
    }
    public function satuanAll(): array {
        return $this->db->query("SELECT * FROM satuan ORDER BY nama_satuan")->fetchAll();
    }
    public function satuanFind(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM satuan WHERE id=? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public function satuanCreate(array $data): void {
        $this->db->prepare("INSERT INTO satuan (nama_satuan,keterangan) VALUES (?,?)")
            ->execute([trim($data['nama_satuan'] ?? ''), trim($data['keterangan'] ?? '') ?: null]);
    }
    public function satuanUpdate(int $id, array $data): void {
        $this->db->prepare("UPDATE satuan SET nama_satuan=?, keterangan=? WHERE id=?")
            ->execute([trim($data['nama_satuan'] ?? ''), trim($data['keterangan'] ?? '') ?: null, $id]);
    }
    public function satuanDelete(int $id): void {
        $this->db->prepare("DELETE FROM satuan WHERE id=?")->execute([$id]);
    }
    public function kategoriKasAll(int $umkmId): array {
        $st = $this->db->prepare("SELECT * FROM kategori_kas WHERE umkm_id=? ORDER BY jenis, nama_kategori");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function kategoriKasFind(int $id, int $umkmId): ?array {
        $st = $this->db->prepare("SELECT * FROM kategori_kas WHERE id=? AND umkm_id=? LIMIT 1");
        $st->execute([$id, $umkmId]);
        return $st->fetch() ?: null;
    }
    public function kategoriKasCreate(array $data, int $umkmId): void {
        $this->db->prepare("INSERT INTO kategori_kas (umkm_id,nama_kategori,jenis,keterangan) VALUES (?,?,?,?)")
            ->execute([$umkmId, trim($data['nama_kategori'] ?? ''), $data['jenis'] ?? 'masuk', trim($data['keterangan'] ?? '') ?: null]);
    }
    public function kategoriKasUpdate(int $id, array $data, int $umkmId): void {
        $this->db->prepare("UPDATE kategori_kas SET nama_kategori=?, jenis=?, keterangan=? WHERE id=? AND umkm_id=?")
            ->execute([trim($data['nama_kategori'] ?? ''), $data['jenis'] ?? 'masuk', trim($data['keterangan'] ?? '') ?: null, $id, $umkmId]);
    }
    public function kategoriKasDelete(int $id, int $umkmId): void {
        $this->db->prepare("DELETE FROM kategori_kas WHERE id=? AND umkm_id=?")->execute([$id, $umkmId]);
    }
}
