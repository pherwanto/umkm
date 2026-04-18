<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ProdukModel.php';
class ProdukController extends Controller {
    public function index(): void { require_roles('super_admin','admin_umkm'); $m=new ProdukModel(); $this->view('produk/index',['title'=>'Produk','rows'=>$m->all(current_umkm_id())]); }
    public function create(): void {
        require_roles('super_admin','admin_umkm'); $m=new ProdukModel();
        if(is_post()){ csrf_check(); $m->create($_POST, $_FILES, current_umkm_id()); flash('success','Produk berhasil ditambahkan.'); $this->redirect('index.php?page=produk'); }
        $this->view('produk/form',['title'=>'Tambah Produk','row'=>null,'categories'=>$m->categories(current_umkm_id()),'units'=>$m->units()]);
    }
    public function edit(): void {
        require_roles('super_admin','admin_umkm'); $m=new ProdukModel(); $id=(int)($_GET['id'] ?? 0); $row=$m->find($id,current_umkm_id()); if(!$row){ flash('error','Produk tidak ditemukan.'); $this->redirect('index.php?page=produk'); }
        if(is_post()){ csrf_check(); $m->update($id,$_POST, $_FILES, current_umkm_id()); flash('success','Produk berhasil diubah.'); $this->redirect('index.php?page=produk'); }
        $this->view('produk/form',['title'=>'Edit Produk','row'=>$row,'categories'=>$m->categories(current_umkm_id()),'units'=>$m->units()]);
    }
    public function delete(): void { require_roles('super_admin','admin_umkm'); $m=new ProdukModel(); $m->delete((int)($_GET['id'] ?? 0), current_umkm_id()); flash('success','Produk berhasil dihapus.'); $this->redirect('index.php?page=produk'); }
}
