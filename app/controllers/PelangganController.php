<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PelangganModel.php';
class PelangganController extends Controller {
    public function index(): void { require_roles('super_admin','admin_umkm'); $m=new PelangganModel(); $this->view('pelanggan/index',['title'=>'Pelanggan','rows'=>$m->all(current_umkm_id())]); }
    public function create(): void { require_roles('super_admin','admin_umkm'); $m=new PelangganModel(); if(is_post()){ csrf_check(); $m->create($_POST,current_umkm_id()); flash('success','Pelanggan berhasil ditambahkan.'); $this->redirect('index.php?page=pelanggan'); } $this->view('pelanggan/form',['title'=>'Tambah Pelanggan','row'=>null]); }
    public function edit(): void { require_roles('super_admin','admin_umkm'); $m=new PelangganModel(); $id=(int)($_GET['id'] ?? 0); $row=$m->find($id,current_umkm_id()); if(!$row){ flash('error','Pelanggan tidak ditemukan.'); $this->redirect('index.php?page=pelanggan'); } if(is_post()){ csrf_check(); $m->update($id,$_POST,current_umkm_id()); flash('success','Pelanggan berhasil diubah.'); $this->redirect('index.php?page=pelanggan'); } $this->view('pelanggan/form',['title'=>'Edit Pelanggan','row'=>$row]); }
    public function delete(): void { require_roles('super_admin','admin_umkm'); $m=new PelangganModel(); $m->delete((int)($_GET['id'] ?? 0), current_umkm_id()); flash('success','Pelanggan berhasil dihapus.'); $this->redirect('index.php?page=pelanggan'); }
}
