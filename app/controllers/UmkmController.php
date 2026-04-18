<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/UmkmModel.php';
class UmkmController extends Controller {
    public function index(): void {
        require_roles('super_admin');
        $m = new UmkmModel();
        $this->view('umkm/index', ['title' => 'Manajemen UMKM', 'rows' => $m->all()]);
    }
    public function create(): void {
        require_roles('super_admin');
        $m = new UmkmModel();
        if (is_post()) {
            csrf_check();
            $m->create($_POST);
            flash('success', 'UMKM berhasil ditambahkan.');
            $this->redirect('index.php?page=umkm');
        }
        $this->view('umkm/form', ['title' => 'Tambah UMKM', 'row' => null]);
    }
    public function edit(): void {
        require_roles('super_admin');
        $m = new UmkmModel();
        $id = (int)($_GET['id'] ?? 0);
        $row = $m->find($id);
        if (!$row) { flash('error', 'UMKM tidak ditemukan.'); $this->redirect('index.php?page=umkm'); }
        if (is_post()) {
            csrf_check();
            $m->update($id, $_POST);
            flash('success', 'UMKM berhasil diperbarui.');
            $this->redirect('index.php?page=umkm');
        }
        $this->view('umkm/form', ['title' => 'Edit UMKM', 'row' => $row]);
    }
    public function delete(): void {
        require_roles('super_admin');
        if (!is_post()) $this->redirect('index.php?page=umkm');
        csrf_check();
        $m = new UmkmModel();
        $m->delete((int)($_POST['id'] ?? 0));
        flash('success', 'UMKM berhasil dihapus.');
        $this->redirect('index.php?page=umkm');
    }
}
