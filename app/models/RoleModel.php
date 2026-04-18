<?php
require_once __DIR__ . '/../core/BaseModel.php';
class RoleModel extends BaseModel {
    public function accessibleRoles(string $currentRole, ?int $umkmId): array {
        if ($currentRole === 'super_admin') {
            $sql = "SELECT r.*, u.nama_umkm FROM roles r LEFT JOIN umkm u ON u.id=r.umkm_id ORDER BY r.is_system DESC, COALESCE(u.nama_umkm,''), r.nama_role";
            return $this->db->query($sql)->fetchAll();
        }
        $st = $this->db->prepare("SELECT r.*, u.nama_umkm FROM roles r LEFT JOIN umkm u ON u.id=r.umkm_id WHERE (r.is_system=1 AND r.nama_role IN ('admin_umkm','operator')) OR r.umkm_id=? ORDER BY r.is_system DESC, r.nama_role");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function findAccessible(int $id, string $currentRole, ?int $umkmId): ?array {
        if ($currentRole === 'super_admin') {
            $st = $this->db->prepare("SELECT * FROM roles WHERE id=? LIMIT 1");
            $st->execute([$id]);
        } else {
            $st = $this->db->prepare("SELECT * FROM roles WHERE id=? AND is_system=0 AND umkm_id=? LIMIT 1");
            $st->execute([$id,$umkmId]);
        }
        return $st->fetch() ?: null;
    }
    public function umkmOptions(): array {
        return $this->db->query("SELECT id, nama_umkm FROM umkm WHERE status='aktif' ORDER BY nama_umkm")->fetchAll();
    }
    public function create(array $data, string $currentRole, ?int $currentUmkmId): int {
        $name = trim((string)($data['nama_role'] ?? ''));
        if ($name === '') throw new Exception('Nama role wajib diisi.');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
        $slug = trim($slug, '_');
        if ($slug === '') throw new Exception('Nama role tidak valid.');
        if (in_array($slug, ['super_admin','admin_umkm','operator'], true)) throw new Exception('Nama role sudah menjadi role sistem.');
        $umkmId = $currentRole === 'super_admin' ? (int)($data['umkm_id'] ?? 0) : (int)$currentUmkmId;
        if ($umkmId < 1) throw new Exception('UMKM wajib dipilih.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE nama_role=? AND umkm_id=?");
        $st->execute([$slug, $umkmId]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('Role custom dengan nama tersebut sudah ada di UMKM ini.');
        $sql = "INSERT INTO roles (umkm_id, is_system, nama_role, display_name, deskripsi) VALUES (?,?,?,?,?)";
        $this->db->prepare($sql)->execute([$umkmId, 0, $slug, $name, trim((string)($data['deskripsi'] ?? '')) ?: null]);
        $roleId = (int)$this->db->lastInsertId();
        $this->seedPermissions($roleId, $umkmId);
        return $roleId;
    }
    public function update(int $id, array $data, string $currentRole, ?int $currentUmkmId): void {
        $row = $this->findAccessible($id, $currentRole, $currentUmkmId);
        if (!$row) throw new Exception('Role tidak ditemukan atau tidak dapat diubah.');
        $name = trim((string)($data['nama_role'] ?? ''));
        if ($name === '') throw new Exception('Nama role wajib diisi.');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
        $slug = trim($slug, '_');
        if ($slug === '') throw new Exception('Nama role tidak valid.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM roles WHERE nama_role=? AND umkm_id=? AND id<>?");
        $st->execute([$slug, (int)$row['umkm_id'], $id]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('Role custom dengan nama tersebut sudah ada di UMKM ini.');
        $sql = "UPDATE roles SET nama_role=?, display_name=?, deskripsi=? WHERE id=?";
        $this->db->prepare($sql)->execute([$slug, $name, trim((string)($data['deskripsi'] ?? '')) ?: null, $id]);
    }
    public function delete(int $id, string $currentRole, ?int $currentUmkmId): void {
        $row = $this->findAccessible($id, $currentRole, $currentUmkmId);
        if (!$row) throw new Exception('Role tidak ditemukan atau tidak dapat dihapus.');
        $st = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role_id=?");
        $st->execute([$id]);
        if ((int)$st->fetchColumn() > 0) throw new Exception('Role tidak dapat dihapus karena masih dipakai user.');
        $this->db->prepare("DELETE FROM roles WHERE id=?")->execute([$id]);
    }
    public function seedPermissions(int $roleId, int $umkmId): void {
        $sql = "INSERT IGNORE INTO menu_permissions (umkm_id, role_id, menu_key, can_access, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())";
        $st = $this->db->prepare($sql);
        foreach (array_keys(menu_labels()) as $key) {
            $st->execute([$umkmId, $roleId, $key, $key === 'dashboard' ? 1 : 0]);
        }
    }
}
