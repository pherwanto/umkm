<?php
require_once __DIR__ . '/../core/BaseModel.php';
class ProdukModel extends BaseModel {
    public function all(int $umkmId): array {
        $sql = "SELECT p.id, p.kode_produk, p.barcode, p.gambar_produk, p.nama_produk, p.harga_beli, p.harga_jual, p.stok, p.stok_minimum, p.status, k.nama_kategori, s.nama_satuan, p.kategori_id, p.satuan_id, p.deskripsi FROM produk p JOIN kategori_produk k ON k.id=p.kategori_id JOIN satuan s ON s.id=p.satuan_id WHERE p.umkm_id=? ORDER BY p.nama_produk";
        $st = $this->db->prepare($sql); $st->execute([$umkmId]); return $st->fetchAll();
    }
    public function find(int $id, int $umkmId): ?array { $st = $this->db->prepare("SELECT * FROM produk WHERE id=? AND umkm_id=? LIMIT 1"); $st->execute([$id,$umkmId]); return $st->fetch() ?: null; }
    public function categories(int $umkmId): array { $st = $this->db->prepare("SELECT * FROM kategori_produk WHERE umkm_id=? ORDER BY nama_kategori"); $st->execute([$umkmId]); return $st->fetchAll(); }
    public function units(): array { return $this->db->query("SELECT * FROM satuan ORDER BY nama_satuan")->fetchAll(); }
    private function uploadImage(array $file, ?string $old=null): ?string {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return $old;
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) throw new Exception('Ukuran gambar maksimal 2 MB.');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) throw new Exception('Format gambar harus JPG, PNG, atau WEBP.');
        $ext = $allowed[$mime];
        $dir = create_upload_dir();
        $name = secure_filename('produk_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext);
        $target = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) throw new Exception('Gagal mengunggah gambar produk.');
        @chmod($target, 0644);
        if ($old && is_file($dir . '/' . basename($old))) @unlink($dir . '/' . basename($old));
        return 'uploads/' . $name;
    }
    public function create(array $d, array $files, int $umkmId): void {
        $gambar = $this->uploadImage($files['gambar_produk'] ?? []);
        $sql = "INSERT INTO produk (umkm_id,kategori_id,satuan_id,kode_produk,barcode,nama_produk,harga_beli,harga_jual,stok,stok_minimum,gambar_produk,deskripsi,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$umkmId,$d['kategori_id'],$d['satuan_id'],clean_text($d['kode_produk'] ?? '', 50),clean_text($d['barcode'] ?? '', 100) ?: null,clean_text($d['nama_produk'] ?? '', 150),(float)($d['harga_beli'] ?? 0),(float)($d['harga_jual'] ?? 0),(float)($d['stok'] ?? 0),(float)($d['stok_minimum'] ?? 0),$gambar,clean_text($d['deskripsi'] ?? '', 1000),$d['status']]);
    }
    public function update(int $id, array $d, array $files, int $umkmId): void {
        $current = $this->find($id, $umkmId); if (!$current) throw new Exception('Produk tidak ditemukan.');
        $gambar = $this->uploadImage($files['gambar_produk'] ?? [], $current['gambar_produk'] ?? null);
        $sql = "UPDATE produk SET kategori_id=?, satuan_id=?, kode_produk=?, barcode=?, nama_produk=?, harga_beli=?, harga_jual=?, stok=?, stok_minimum=?, gambar_produk=?, deskripsi=?, status=? WHERE id=? AND umkm_id=?";
        $this->db->prepare($sql)->execute([$d['kategori_id'],$d['satuan_id'],clean_text($d['kode_produk'] ?? '', 50),clean_text($d['barcode'] ?? '', 100) ?: null,clean_text($d['nama_produk'] ?? '', 150),(float)($d['harga_beli'] ?? 0),(float)($d['harga_jual'] ?? 0),(float)($d['stok'] ?? 0),(float)($d['stok_minimum'] ?? 0),$gambar,clean_text($d['deskripsi'] ?? '', 1000),$d['status'],$id,$umkmId]);
    }
    public function delete(int $id, int $umkmId): void { $row = $this->find($id,$umkmId); if ($row && !empty($row['gambar_produk'])) { $path = create_upload_dir() . '/' . basename($row['gambar_produk']); if (is_file($path)) @unlink($path); } $this->db->prepare("DELETE FROM produk WHERE id=? AND umkm_id=?")->execute([$id,$umkmId]); }
}
