<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SupplierModel.php';
class SupplierController extends Controller {
    public function index(): void { require_roles('super_admin','admin_umkm'); $m=new SupplierModel(); $this->view('supplier/index',['title'=>'Supplier','rows'=>$m->all(current_umkm_id())]); }
    public function create(): void { require_roles('super_admin','admin_umkm'); $m=new SupplierModel(); if(is_post()){ csrf_check(); $m->create($_POST,current_umkm_id()); flash('success','Supplier berhasil ditambahkan.'); $this->redirect('index.php?page=supplier'); } $this->view('supplier/form',['title'=>'Tambah Supplier','row'=>null]); }
    public function edit(): void { require_roles('super_admin','admin_umkm'); $m=new SupplierModel(); $id=(int)($_GET['id'] ?? 0); $row=$m->find($id,current_umkm_id()); if(!$row){ flash('error','Supplier tidak ditemukan.'); $this->redirect('index.php?page=supplier'); } if(is_post()){ csrf_check(); $m->update($id,$_POST,current_umkm_id()); flash('success','Supplier berhasil diubah.'); $this->redirect('index.php?page=supplier'); } $this->view('supplier/form',['title'=>'Edit Supplier','row'=>$row]); }
    public function delete(): void { require_roles('super_admin','admin_umkm'); $m=new SupplierModel(); $m->delete((int)($_GET['id'] ?? 0), current_umkm_id()); flash('success','Supplier berhasil dihapus.'); $this->redirect('index.php?page=supplier'); }
}
