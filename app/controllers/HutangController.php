<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
class HutangController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m = new TransaksiModel();
        $rows = $m->hutangAll(current_umkm_id());
        $summary = [
            'total_hutang' => (int)array_sum(array_map(static fn($r) => (float)($r['total_hutang'] ?? 0), $rows)),
            'total_bayar' => (int)array_sum(array_map(static fn($r) => (float)($r['total_bayar'] ?? 0), $rows)),
            'sisa_hutang' => (int)array_sum(array_map(static fn($r) => (float)($r['sisa_hutang'] ?? 0), $rows)),
        ];
        $this->view('hutang/index', ['title' => 'Hutang', 'rows' => $rows, 'summary' => $summary]);
    }
    public function pay(): void { require_roles('super_admin','admin_umkm'); $m=new TransaksiModel(); if(is_post()){ csrf_check(); $m->payHutang((int)$_POST['id'],(float)$_POST['nominal_bayar'],$_POST['tanggal_bayar'],$_POST['metode_pembayaran'],$_POST['keterangan'],current_umkm_id(),(int)current_user()['id']); flash('success','Pembayaran berhasil disimpan.'); } $this->redirect('index.php?page=hutang'); }
}
