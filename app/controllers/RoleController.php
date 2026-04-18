<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/RoleModel.php';
class RoleController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m = new RoleModel();
        $this->view('roles/index', [
            'title' => 'Role Khusus / Custom Role',
            'rows' => $m->accessibleRoles(current_role(), current_umkm_id()),
        ]);
    }
    public function create(): void {
        require_roles('super_admin','admin_umkm');
        $m = new RoleModel();
        if (is_post()) {
            csrf_check();
            $m->create($_POST, current_role(), current_umkm_id());
            flash('success', 'Role custom berhasil ditambahkan.');
            $this->redirect('index.php?page=roles');
        }
        $this->view('roles/form', [
            'title' => 'Tambah Role Custom',
            'row' => null,
            'umkms' => $m->umkmOptions(),
        ]);
    }
    public function edit(): void {
        require_roles('super_admin','admin_umkm');
        $m = new RoleModel();
        $id = (int)($_GET['id'] ?? 0);
        $row = $m->findAccessible($id, current_role(), current_umkm_id());
        if (!$row) { flash('error', 'Role tidak ditemukan.'); $this->redirect('index.php?page=roles'); }
        if (is_post()) {
            csrf_check();
            $m->update($id, $_POST, current_role(), current_umkm_id());
            flash('success', 'Role custom berhasil diperbarui.');
            $this->redirect('index.php?page=roles');
        }
        $this->view('roles/form', [
            'title' => 'Edit Role Custom',
            'row' => $row,
            'umkms' => $m->umkmOptions(),
        ]);
    }
    public function delete(): void {
        require_roles('super_admin','admin_umkm');
        if (!is_post()) $this->redirect('index.php?page=roles');
        csrf_check();
        $m = new RoleModel();
        $m->delete((int)($_POST['id'] ?? 0), current_role(), current_umkm_id());
        flash('success', 'Role custom berhasil dihapus.');
        $this->redirect('index.php?page=roles');
    }
}
