<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
class HutangController extends Controller {
    public function index(): void { require_roles('super_admin','admin_umkm'); $m=new TransaksiModel(); $this->view('hutang/index',['title'=>'Hutang','rows'=>$m->hutangAll(current_umkm_id())]); }
    public function pay(): void { require_roles('super_admin','admin_umkm'); $m=new TransaksiModel(); if(is_post()){ csrf_check(); $m->payHutang((int)$_POST['id'],(float)$_POST['nominal_bayar'],$_POST['tanggal_bayar'],$_POST['metode_pembayaran'],$_POST['keterangan'],current_umkm_id(),(int)current_user()['id']); flash('success','Pembayaran berhasil disimpan.'); } $this->redirect('index.php?page=hutang'); }
}
