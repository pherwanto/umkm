<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/UserModel.php';
class UserController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m = new UserModel();
        $this->view('users/index', [
            'title' => 'User UMKM',
            'rows' => $m->all(current_role(), current_umkm_id()),
        ]);
    }
    public function create(): void {
        require_roles('super_admin','admin_umkm');
        $m = new UserModel();
        if (is_post()) {
            csrf_check();
            $m->create($_POST, current_role(), current_umkm_id());
            flash('success', 'User berhasil ditambahkan.');
            $this->redirect('index.php?page=users');
        }
        $this->view('users/form', [
            'title' => 'Tambah User',
            'row' => null,
            'roles' => $m->roleOptions(current_role(), current_umkm_id()),
            'umkms' => $m->umkmAll(),
        ]);
    }
    public function edit(): void {
        require_roles('super_admin','admin_umkm');
        $m = new UserModel();
        $id = (int)($_GET['id'] ?? 0);
        $row = $m->find($id, current_role(), current_umkm_id());
        if (!$row) { flash('error', 'User tidak ditemukan.'); $this->redirect('index.php?page=users'); }
        if (is_post()) {
            csrf_check();
            $m->update($id, $_POST, current_role(), current_umkm_id());
            flash('success', 'User berhasil diperbarui.');
            $this->redirect('index.php?page=users');
        }
        $this->view('users/form', [
            'title' => 'Edit User',
            'row' => $row,
            'roles' => $m->roleOptions(current_role(), current_umkm_id()),
            'umkms' => $m->umkmAll(),
        ]);
    }
    public function delete(): void {
        require_roles('super_admin','admin_umkm');
        if (!is_post()) { $this->redirect('index.php?page=users'); }
        csrf_check();
        $m = new UserModel();
        $m->delete((int)($_POST['id'] ?? 0), current_role(), current_umkm_id(), (int)current_user()['id']);
        flash('success', 'User berhasil dihapus.');
        $this->redirect('index.php?page=users');
    }
    public function roleAccess(): void {
        require_roles('super_admin','admin_umkm');
        $m = new UserModel();
        $currentRole = current_role();
        $umkmId = $currentRole === 'super_admin' ? (int)($_GET['umkm_id'] ?? $_POST['umkm_id'] ?? 0) : (int)current_umkm_id();
        if ($currentRole === 'super_admin' && $umkmId < 1) {
            $first = $m->umkmAll();
            $umkmId = $first ? (int)$first[0]['id'] : 0;
        }
        if (is_post()) {
            csrf_check();
            $roleId = (int)($_POST['role_id'] ?? 0);
            $m->saveRolePermissions($roleId, $umkmId, $_POST['permissions'] ?? []);
            flash('success', 'Hak akses menu berhasil disimpan.');
            $this->redirect('index.php?page=role-access' . ($currentRole === 'super_admin' ? '&umkm_id=' . $umkmId : ''));
        }
        $roles = $m->roleCards($currentRole, $umkmId);
        $permissions = [];
        foreach ($roles as $role) {
            $permissions[(int)$role['id']] = $m->permissionsForRole((int)$role['id'], $umkmId, $role['nama_role']);
        }
        $this->view('users/role_access', [
            'title' => 'Hak Akses per Menu',
            'roles' => $roles,
            'permissions' => $permissions,
            'umkmId' => $umkmId,
            'umkms' => $m->umkmAll(),
            'menuLabels' => menu_labels(),
        ]);
    }
}
