<?php
require_once __DIR__ . '/../core/BaseModel.php';
class PelangganModel extends BaseModel {
    public function all(int $umkmId): array { $st = $this->db->prepare("SELECT * FROM pelanggan WHERE umkm_id=? ORDER BY nama_pelanggan"); $st->execute([$umkmId]); return $st->fetchAll(); }
    public function find(int $id, int $umkmId): ?array { $st = $this->db->prepare("SELECT * FROM pelanggan WHERE id=? AND umkm_id=? LIMIT 1"); $st->execute([$id,$umkmId]); return $st->fetch() ?: null; }
    public function create(array $d, int $umkmId): void {
        $sql = "INSERT INTO pelanggan (umkm_id,nama_pelanggan,alamat,telepon,jenis_pelanggan,catatan) VALUES (?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$umkmId,$d['nama'],$d['alamat'],$d['telepon'],$d['jenis'],$d['catatan']]);
    }
    public function update(int $id, array $d, int $umkmId): void {
        $sql = "UPDATE pelanggan SET nama_pelanggan=?, alamat=?, telepon=?, jenis_pelanggan=?, catatan=? WHERE id=? AND umkm_id=?";
        $this->db->prepare($sql)->execute([$d['nama'],$d['alamat'],$d['telepon'],$d['jenis'],$d['catatan'],$id,$umkmId]);
    }
    public function delete(int $id, int $umkmId): void { $this->db->prepare("DELETE FROM pelanggan WHERE id=? AND umkm_id=?")->execute([$id,$umkmId]); }
}
