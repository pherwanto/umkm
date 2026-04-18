<?php
require_once __DIR__ . '/../core/BaseModel.php';
class AuthModel extends BaseModel {
    public function findByUsername(string $username): ?array {
        $sql = "SELECT u.*, r.nama_role, m.nama_umkm FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN umkm m ON m.id=u.umkm_id WHERE u.username=? AND u.status='aktif' LIMIT 1";
        $st = $this->db->prepare($sql); $st->execute([$username]);
        return $st->fetch() ?: null;
    }
    public function findByEmail(string $email): ?array {
        $sql = "SELECT u.*, r.nama_role, m.nama_umkm FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN umkm m ON m.id=u.umkm_id WHERE u.email=? AND u.status='aktif' LIMIT 1";
        $st=$this->db->prepare($sql); $st->execute([$email]);
        return $st->fetch() ?: null;
    }
    public function updateLastLogin(int $id): void { $st=$this->db->prepare("UPDATE users SET last_login=NOW() WHERE id=?"); $st->execute([$id]); }
    public function saveResetToken(int $id, string $token, string $expires): void {
        $st=$this->db->prepare("UPDATE users SET reset_token=?, reset_token_expires_at=? WHERE id=?"); $st->execute([$token,$expires,$id]);
    }
    public function findByResetToken(string $token): ?array {
        $st=$this->db->prepare("SELECT * FROM users WHERE reset_token=? AND reset_token_expires_at >= NOW() LIMIT 1"); $st->execute([$token]);
        return $st->fetch() ?: null;
    }
    public function updatePassword(int $id, string $hash): void {
        $st=$this->db->prepare("UPDATE users SET password_hash=?, reset_token=NULL, reset_token_expires_at=NULL WHERE id=?"); $st->execute([$hash,$id]);
    }
}
