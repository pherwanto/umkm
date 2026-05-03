<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
class PembelianController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m=new TransaksiModel();
        $filters=[
            'q'=>trim($_GET['q'] ?? ''),
            'supplier_id'=>trim($_GET['supplier_id'] ?? ''),
            'date_from'=>trim($_GET['date_from'] ?? ''),
            'date_to'=>trim($_GET['date_to'] ?? ''),
        ];
        $rows = $m->pembelianAll(current_umkm_id(), $filters);
        $summary = [
            'total' => (int)array_sum(array_map(static fn($r) => (float)($r['total'] ?? 0), $rows)),
            'dibayar' => (int)array_sum(array_map(static fn($r) => (float)($r['dibayar'] ?? 0), $rows)),
            'sisa' => (int)array_sum(array_map(static fn($r) => (float)($r['sisa'] ?? 0), $rows)),
        ];
        $this->view('pembelian/index',[
            'title'=>'Pembelian',
            'rows'=>$rows,
            'summary'=>$summary,
            'supplierOptions'=>$m->supplierAll(current_umkm_id()),
            'filters'=>$filters,
        ]);
    }

    public function create(): void {
        require_roles('super_admin','admin_umkm');
        $m=new TransaksiModel();
        if(is_post()){
            csrf_check();
            $id = $m->createPembelian($_POST,current_umkm_id(),(int)current_user()['id']);
            flash('success','Pembelian berhasil disimpan.');
            $this->redirect('index.php?page=pembelian-print&id=' . $id);
        }
        $this->view('pembelian/form',[
            'title'=>'Tambah Pembelian',
            'produk'=>$m->produkAll(current_umkm_id()),
            'supplier'=>$m->supplierAll(current_umkm_id()),
            'formAction'=>url('index.php?page=pembelian-create'),
            'data'=>['tanggal'=>date('Y-m-d\TH:i'),'diskon'=>0,'dibayar'=>0,'metode_pembayaran'=>'tunai','items'=>[]],
            'isEdit'=>false,
        ]);
    }

    public function edit(): void {
        require_roles('super_admin','admin_umkm');
        $m=new TransaksiModel();
        $id=(int)($_GET['id'] ?? 0);
        $row=$m->pembelianFind($id, current_umkm_id());
        if(!$row){ flash('error','Data pembelian tidak ditemukan.'); $this->redirect('index.php?page=pembelian'); }
        if(is_post()){
            csrf_check();
            $m->updatePembelian($id, $_POST, current_umkm_id(), (int)current_user()['id']);
            flash('success','Pembelian berhasil diperbarui.');
            $this->redirect('index.php?page=pembelian-print&id=' . $id);
        }
        $row['tanggal'] = date('Y-m-d\TH:i', strtotime($row['tanggal']));
        $this->view('pembelian/form',[
            'title'=>'Edit Pembelian',
            'produk'=>$m->produkAll(current_umkm_id()),
            'supplier'=>$m->supplierAll(current_umkm_id()),
            'formAction'=>url('index.php?page=pembelian-edit&id=' . $id),
            'data'=>$row,
            'isEdit'=>true,
        ]);
    }

    public function delete(): void {
        require_roles('super_admin','admin_umkm');
        if(!is_post()) { flash('error', 'Metode tidak diizinkan.'); $this->redirect('index.php?page=pembelian'); }
        csrf_check();
        $m = new TransaksiModel();
        $id = (int)($_POST['id'] ?? 0);
        $m->deletePembelian($id, current_umkm_id(), (int)current_user()['id']);
        flash('success', 'Transaksi pembelian berhasil dihapus.');
        $this->redirect('index.php?page=pembelian');
    }

    public function show(): void {
        require_roles('super_admin','admin_umkm');
        $m = new TransaksiModel();
        $id = (int)($_GET['id'] ?? 0);
        $row = $m->pembelianDetailView($id, current_umkm_id());
        if (!$row) { flash('error','Detail pembelian tidak ditemukan.'); $this->redirect('index.php?page=pembelian'); }
        $this->view('pembelian/show', ['title'=>'Detail Pembelian','row'=>$row]);
    }

    public function history(): void {
        require_roles('super_admin','admin_umkm');
        $m = new TransaksiModel();
        $filters=['q'=>trim((string)($_GET['q'] ?? '')),'date_from'=>trim((string)($_GET['date_from'] ?? '')),'date_to'=>trim((string)($_GET['date_to'] ?? ''))];
        $rows = $m->pembelianHistory(current_umkm_id(), null, $filters);
        $this->view('pembelian/history',['title'=>'Riwayat Perubahan Pembelian','rows'=>$rows,'filters'=>$filters]);
    }

    public function supplierSearch(): void {
        require_roles('super_admin','admin_umkm');
        header('Content-Type: application/json; charset=utf-8');
        $m = new TransaksiModel();
        echo json_encode(['items' => $m->searchSupplier(current_umkm_id(), (string)($_GET['q'] ?? ''))], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function printView(): void {
        require_roles('super_admin','admin_umkm');
        $id=(int)($_GET['id'] ?? 0);
        $m=new TransaksiModel();
        $row=$m->pembelianFind($id, current_umkm_id());
        if(!$row){ flash('error','Data pembelian tidak ditemukan.'); $this->redirect('index.php?page=pembelian'); }
        $title='Cetak Pembelian';
        include __DIR__ . '/../views/pembelian/print.php';
    }
}
