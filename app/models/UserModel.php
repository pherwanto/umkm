<?php
require_once __DIR__ . '/../core/BaseModel.php';
class UserModel extends BaseModel {
    public function umkmAll(): array {
        return $this->db->query("SELECT id, nama_umkm FROM umkm WHERE status='aktif' ORDER BY nama_umkm")->fetchAll();
    }
    public function roleOptions(string $currentRole, ?int $umkmId = null): array {
        if ($currentRole === 'super_admin') {
            return $this->db->query("SELECT id, nama_role, display_name, umkm_id, is_system FROM roles ORDER BY is_system DESC, COALESCE(display_name,nama_role)")->fetchAll();
        }
        $st = $this->db->prepare("SELECT id, nama_role, display_name, umkm_id, is_system FROM roles WHERE (is_system=1 AND nama_role IN ('admin_umkm','operator')) OR umkm_id=? ORDER BY is_system DESC, COALESCE(display_name,nama_role)");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function all(string $currentRole, ?int $umkmId): array {
        if ($currentRole === 'super_admin') {
            $sql = "SELECT u.*, r.nama_role, COALESCE(r.display_name, r.nama_role) AS display_role, m.nama_umkm
                    FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN umkm m ON m.id=u.umkm_id
                    ORDER BY COALESCE(m.nama_umkm,''), r.is_system DESC, COALESCE(r.display_name,r.nama_role), u.nama";
            return $this->db->query($sql)->fetchAll();
        }
        $st = $this->db->prepare("SELECT u.*, r.nama_role, COALESCE(r.display_name, r.nama_role) AS display_role, m.nama_umkm
                                  FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN umkm m ON m.id=u.umkm_id
                                  WHERE u.umkm_id=? AND r.nama_role <> 'super_admin'
                                  ORDER BY r.is_system DESC, COALESCE(r.display_name,r.nama_role), u.nama");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function find(int $id, string $currentRole, ?int $umkmId): ?array {
        if ($currentRole === 'super_admin') {
            $st = $this->db->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
            $st->execute([$id]);
        } else {
            $st = $this->db->prepare("SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=? AND u.umkm_id=? AND r.nama_role <> 'super_admin' LIMIT 1");
            $st->execute([$id, $umkmId]);
        }
        return $st->fetch() ?: null;
    }
    public function usernameExists(string $username, ?int $ignoreId = null): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE username=?" . ($ignoreId ? " AND id<>?" : "");
        $st = $this->db->prepare($sql);
        $params = [$username];
        if ($ignoreId) $params[] = $ignoreId;
        $st->execute($params);
        return (int)$st->fetchColumn() > 0;
    }
    public function create(array $data, string $currentRole, ?int $currentUmkmId): void {
        $username = trim((string)($data['username'] ?? ''));
        if ($username === '') throw new Exception('Username wajib diisi.');
        if ($this->usernameExists($username)) throw new Exception('Username sudah digunakan.');
        $password = (string)($data['password'] ?? '');
        if (strlen($password) < 6) throw new Exception('Password minimal 6 karakter.');
        $roleId = (int)($data['role_id'] ?? 0);
        $role = $this->roleRow($roleId);
        if (!$role) throw new Exception('Role tidak valid.');
        $roleName = $role['nama_role'];
        if ($currentRole !== 'super_admin' && $roleName === 'super_admin') throw new Exception('Role super admin hanya bisa dibuat oleh super admin.');
        $umkmId = $currentRole === 'super_admin' ? (($data['umkm_id'] ?? '') !== '' ? (int)$data['umkm_id'] : null) : $currentUmkmId;
        if ($roleName !== 'super_admin' && !$umkmId) throw new Exception('UMKM wajib dipilih.');
        if (!$this->isRoleAllowedForScope($role, $currentRole, $umkmId)) throw new Exception('Role tidak sesuai dengan UMKM atau tidak diizinkan.');
        $sql = "INSERT INTO users (umkm_id,role_id,nama,username,password_hash,email,telepon,status) VALUES (?,?,?,?,?,?,?,?)";
        $this->db->prepare($sql)->execute([
            $roleName === 'super_admin' ? null : $umkmId,
            $roleId,
            trim((string)($data['nama'] ?? '')),
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            trim((string)($data['email'] ?? '')) ?: null,
            trim((string)($data['telepon'] ?? '')) ?: null,
            ($data['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ]);
        if ($roleName !== 'super_admin' && $umkmId) $this->ensureDefaultPermissions($umkmId, $roleId, $roleName);
    }
    public function update(int $id, array $data, string $currentRole, ?int $currentUmkmId): void {
        $row = $this->find($id, $currentRole, $currentUmkmId);
        if (!$row) throw new Exception('User tidak ditemukan.');
        $username = trim((string)($data['username'] ?? ''));
        if ($username === '') throw new Exception('Username wajib diisi.');
        if ($this->usernameExists($username, $id)) throw new Exception('Username sudah digunakan.');
        $roleId = (int)($data['role_id'] ?? 0);
        $role = $this->roleRow($roleId);
        if (!$role) throw new Exception('Role tidak valid.');
        $roleName = $role['nama_role'];
        if ($currentRole !== 'super_admin' && $roleName === 'super_admin') throw new Exception('Role super admin hanya bisa diatur oleh super admin.');
        $umkmId = $currentRole === 'super_admin' ? (($data['umkm_id'] ?? '') !== '' ? (int)$data['umkm_id'] : null) : $currentUmkmId;
        if ($roleName !== 'super_admin' && !$umkmId) throw new Exception('UMKM wajib dipilih.');
        if (!$this->isRoleAllowedForScope($role, $currentRole, $umkmId)) throw new Exception('Role tidak sesuai dengan UMKM atau tidak diizinkan.');
        $passwordSql = '';
        $params = [
            $roleName === 'super_admin' ? null : $umkmId,
            $roleId,
            trim((string)($data['nama'] ?? '')),
            $username,
            trim((string)($data['email'] ?? '')) ?: null,
            trim((string)($data['telepon'] ?? '')) ?: null,
            ($data['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];
        $password = (string)($data['password'] ?? '');
        if ($password !== '') {
            if (strlen($password) < 6) throw new Exception('Password minimal 6 karakter.');
            $passwordSql = ', password_hash=?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        $params[] = $id;
        $sql = "UPDATE users SET umkm_id=?, role_id=?, nama=?, username=?, email=?, telepon=?, status=?{$passwordSql}, updated_at=NOW() WHERE id=?";
        $this->db->prepare($sql)->execute($params);
        if ($roleName !== 'super_admin' && $umkmId) $this->ensureDefaultPermissions($umkmId, $roleId, $roleName);
    }
    public function delete(int $id, string $currentRole, ?int $currentUmkmId, int $currentUserId): void {
        if ($id === $currentUserId) throw new Exception('User yang sedang login tidak bisa dihapus.');
        $row = $this->find($id, $currentRole, $currentUmkmId);
        if (!$row) throw new Exception('User tidak ditemukan.');
        $this->db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    }
    public function roleCards(string $currentRole, ?int $umkmId = null): array {
        if ($currentRole === 'super_admin') {
            return $this->db->query("SELECT id, nama_role, display_name, umkm_id, is_system FROM roles ORDER BY is_system DESC, COALESCE(display_name,nama_role)")->fetchAll();
        }
        $st = $this->db->prepare("SELECT id, nama_role, display_name, umkm_id, is_system FROM roles WHERE (is_system=1 AND nama_role IN ('admin_umkm','operator')) OR umkm_id=? ORDER BY is_system DESC, COALESCE(display_name,nama_role)");
        $st->execute([$umkmId]);
        return $st->fetchAll();
    }
    public function permissionsForRole(int $roleId, ?int $umkmId, string $roleName): array {
        $matrix = default_role_menu_permissions($roleName);
        if ($roleName === 'super_admin') return $matrix;
        try {
            $st = $this->db->prepare("SELECT menu_key, can_access FROM menu_permissions WHERE role_id=? AND umkm_id=?");
            $st->execute([$roleId, $umkmId]);
            foreach ($st->fetchAll() as $row) $matrix[$row['menu_key']] = (bool)$row['can_access'];
        } catch (Throwable $e) {}
        return $matrix;
    }
    public function saveRolePermissions(int $roleId, int $umkmId, array $permissions): void {
        $role = $this->roleRow($roleId);
        if (!$role || $role['nama_role'] === 'super_admin') throw new Exception('Hak akses super admin tidak dapat diubah.');
        if ((int)$role['is_system'] === 0 && (int)$role['umkm_id'] !== $umkmId) throw new Exception('Role custom tidak sesuai UMKM.');
        $roleName = $role['nama_role'];
        $allowedKeys = array_keys(menu_labels());
        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM menu_permissions WHERE role_id=? AND umkm_id=?")->execute([$roleId, $umkmId]);
            $default = default_role_menu_permissions($roleName);
            $sql = "INSERT INTO menu_permissions (umkm_id,role_id,menu_key,can_access,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())";
            $st = $this->db->prepare($sql);
            foreach ($allowedKeys as $key) {
                $can = isset($permissions[$key]) ? 1 : 0;
                if ($roleName === 'operator' && in_array($key, ['users','hak_akses','referensi','roles','umkm'], true)) $can = 0;
                if ($roleName === 'admin_umkm' && $key === 'umkm') $can = 0;
                if ((int)$role['is_system'] === 0 && $key === 'umkm') $can = 0;
                if ($roleName === 'admin_umkm' && $key === 'hak_akses') $can = isset($permissions[$key]) ? 1 : ($default[$key] ? 1 : 0);
                $st->execute([$umkmId, $roleId, $key, $can]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack(); throw $e;
        }
    }
    public function ensureDefaultPermissions(int $umkmId, int $roleId, string $roleName): void {
        if ($roleName === 'super_admin') return;
        $labels = array_keys(menu_labels());
        $default = default_role_menu_permissions($roleName);
        $sql = "INSERT IGNORE INTO menu_permissions (umkm_id,role_id,menu_key,can_access,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())";
        $st = $this->db->prepare($sql);
        foreach ($labels as $key) {
            $can = !empty($default[$key]) ? 1 : 0;
            if (!in_array($roleName, ['super_admin','admin_umkm','operator'], true) && $key === 'dashboard') $can = 1;
            if (!in_array($roleName, ['super_admin'], true) && $key === 'umkm') $can = 0;
            $st->execute([$umkmId, $roleId, $key, $can]);
        }
    }
    public function roleName(int $roleId): ?string {
        $st = $this->db->prepare("SELECT nama_role FROM roles WHERE id=? LIMIT 1");
        $st->execute([$roleId]);
        $name = $st->fetchColumn();
        return $name ? (string)$name : null;
    }
    private function roleRow(int $roleId): ?array {
        $st = $this->db->prepare("SELECT * FROM roles WHERE id=? LIMIT 1");
        $st->execute([$roleId]);
        return $st->fetch() ?: null;
    }
    private function isRoleAllowedForScope(array $role, string $currentRole, ?int $umkmId): bool {
        if ($role['nama_role'] === 'super_admin') return $currentRole === 'super_admin';
        if ((int)$role['is_system'] === 1) return true;
        return $umkmId && (int)$role['umkm_id'] === (int)$umkmId;
    }
}
