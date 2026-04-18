<?php
require_once __DIR__ . '/../core/BaseModel.php';
class UmkmModel extends BaseModel {
    public function all(): array {
        return $this->db->query("SELECT * FROM umkm ORDER BY nama_umkm")->fetchAll();
    }
    public function find(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM umkm WHERE id=? LIMIT 1");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }
    public function create(array $data): void {
        $kode = trim((string)($data['kode_umkm'] ?? ''));
        $nama = trim((string)($data['nama_umkm'] ?? ''));
        if ($kode === '' || $nama === '') throw new Exception('Kode dan nama UMKM wajib diisi.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM umkm WHERE kode_umkm=?");
        $st->execute([$kode]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('Kode UMKM sudah digunakan.');
        $sql = "INSERT INTO umkm (kode_umkm,nama_umkm,nama_pemilik,alamat,telepon,email,jenis_usaha,deskripsi,status) VALUES (?,?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([
            $kode, $nama,
            trim((string)($data['nama_pemilik'] ?? '')) ?: null,
            trim((string)($data['alamat'] ?? '')) ?: null,
            trim((string)($data['telepon'] ?? '')) ?: null,
            trim((string)($data['email'] ?? '')) ?: null,
            trim((string)($data['jenis_usaha'] ?? '')) ?: null,
            trim((string)($data['deskripsi'] ?? '')) ?: null,
            ($data['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ]);
    }
    public function update(int $id, array $data): void {
        $row = $this->find($id);
        if (!$row) throw new Exception('UMKM tidak ditemukan.');
        $kode = trim((string)($data['kode_umkm'] ?? ''));
        $nama = trim((string)($data['nama_umkm'] ?? ''));
        if ($kode === '' || $nama === '') throw new Exception('Kode dan nama UMKM wajib diisi.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM umkm WHERE kode_umkm=? AND id<>?");
        $st->execute([$kode,$id]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('Kode UMKM sudah digunakan.');
        $sql = "UPDATE umkm SET kode_umkm=?, nama_umkm=?, nama_pemilik=?, alamat=?, telepon=?, email=?, jenis_usaha=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?";
        $this->db->prepare($sql)->execute([
            $kode, $nama,
            trim((string)($data['nama_pemilik'] ?? '')) ?: null,
            trim((string)($data['alamat'] ?? '')) ?: null,
            trim((string)($data['telepon'] ?? '')) ?: null,
            trim((string)($data['email'] ?? '')) ?: null,
            trim((string)($data['jenis_usaha'] ?? '')) ?: null,
            trim((string)($data['deskripsi'] ?? '')) ?: null,
            ($data['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
            $id
        ]);
    }
    public function delete(int $id): void {
        $row = $this->find($id);
        if (!$row) throw new Exception('UMKM tidak ditemukan.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM users WHERE umkm_id=?");
        $st->execute([$id]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('UMKM tidak dapat dihapus karena masih memiliki user.');
        $this->db->prepare("DELETE FROM umkm WHERE id=?")->execute([$id]);
    }
}
