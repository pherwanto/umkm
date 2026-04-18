<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
class PenjualanController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm','operator');
        $m=new TransaksiModel();
        $filters=[
            'q'=>trim((string)($_GET['q'] ?? '')),
            'date_from'=>trim((string)($_GET['date_from'] ?? '')),
            'date_to'=>trim((string)($_GET['date_to'] ?? '')),
        ];
        $this->view('penjualan/index',['title'=>'Penjualan','rows'=>$m->penjualanAll(current_umkm_id(), $filters),'filters'=>$filters]);
    }
    public function create(): void {
        require_roles('super_admin','admin_umkm','operator');
        $m=new TransaksiModel();
        $cfg = app_config();
        $taxCfg = $cfg['sales_tax'] ?? ['enabled' => false, 'percent' => 11];
        if(is_post()){
            csrf_check();
            $id = $m->createPenjualan($_POST,current_umkm_id(),(int)current_user()['id']);
            flash('success','Penjualan berhasil disimpan.');
            $this->redirect('index.php?page=penjualan-invoice&id=' . $id . '&autoprint=1&return=' . rawurlencode(url('index.php?page=penjualan-create')));
        }
        $this->view('penjualan/form',['title'=>'Tambah Penjualan','productCategories'=>$m->kategoriProdukAll(current_umkm_id()),'initialProducts'=>$m->searchProduk(current_umkm_id(), ''),'formAction'=>url('index.php?page=penjualan-create'),'data'=>['tanggal'=>date('Y-m-d\TH:i'),'diskon'=>0,'dibayar'=>0,'metode_pembayaran'=>'tunai','is_pajak_enabled'=>!empty($taxCfg['enabled']) ? 1 : 0,'pajak_persen'=>(float)($taxCfg['percent'] ?? 11),'items'=>[]],'isEdit'=>false]);
    }
    public function edit(): void {
        require_roles('super_admin','admin_umkm','operator');
        $cfg = app_config();
        $taxCfg = $cfg['sales_tax'] ?? ['enabled' => false, 'percent' => 11];
        $m = new TransaksiModel(); $id = (int)($_GET['id'] ?? 0); $row = $m->penjualanFind($id, current_umkm_id());
        if (!$row) { flash('error', 'Data penjualan tidak ditemukan.'); $this->redirect('index.php?page=penjualan'); }
        if (is_post()) { csrf_check(); $m->updatePenjualan($id, $_POST, current_umkm_id(), (int)current_user()['id']); flash('success', 'Penjualan berhasil diperbarui.'); $this->redirect('index.php?page=penjualan-invoice&id=' . $id); }
        $row['tanggal'] = date('Y-m-d\TH:i', strtotime($row['tanggal']));
        $row['is_pajak_enabled'] = isset($row['is_pajak_enabled']) ? (int)$row['is_pajak_enabled'] : (!empty($taxCfg['enabled']) ? 1 : 0);
        $row['pajak_persen'] = isset($row['pajak_persen']) ? (float)$row['pajak_persen'] : (float)($taxCfg['percent'] ?? 11);
        $this->view('penjualan/form',['title'=>'Edit Penjualan','productCategories'=>$m->kategoriProdukAll(current_umkm_id()),'initialProducts'=>$m->searchProduk(current_umkm_id(), ''),'formAction'=>url('index.php?page=penjualan-edit&id=' . $id),'data'=>$row,'isEdit'=>true]);
    }
    public function show(): void { require_roles('super_admin','admin_umkm','operator'); $m = new TransaksiModel(); $id = (int)($_GET['id'] ?? 0); $row = $m->penjualanDetailView($id, current_umkm_id()); if (!$row) { flash('error', 'Detail penjualan tidak ditemukan.'); $this->redirect('index.php?page=penjualan'); } $this->view('penjualan/show',['title'=>'Detail Penjualan','row'=>$row]); }
    public function history(): void {
        require_roles('super_admin','admin_umkm');
        $m = new TransaksiModel();
        $filters=['q'=>trim((string)($_GET['q'] ?? '')),'date_from'=>trim((string)($_GET['date_from'] ?? '')),'date_to'=>trim((string)($_GET['date_to'] ?? ''))];
        $rows = $m->penjualanHistory(current_umkm_id(), null, $filters);
        $this->view('penjualan/history',['title'=>'Riwayat Perubahan Penjualan','rows'=>$rows,'filters'=>$filters]);
    }
    public function productSearch(): void { require_roles('super_admin','admin_umkm','operator'); header('Content-Type: application/json; charset=utf-8'); $m = new TransaksiModel(); $kategoriId=(int)($_GET['kategori_id'] ?? 0); echo json_encode(['items' => $m->searchProduk(current_umkm_id(), (string)($_GET['q'] ?? ''), $kategoriId ?: null)], JSON_UNESCAPED_UNICODE); exit; }
    public function customerSearch(): void { require_roles('super_admin','admin_umkm','operator'); header('Content-Type: application/json; charset=utf-8'); $m = new TransaksiModel(); echo json_encode(['items' => $m->searchPelanggan(current_umkm_id(), (string)($_GET['q'] ?? ''))], JSON_UNESCAPED_UNICODE); exit; }
    public function delete(): void { require_roles('super_admin','admin_umkm'); if(!is_post()) { flash('error', 'Metode tidak diizinkan.'); $this->redirect('index.php?page=penjualan'); } csrf_check(); $m = new TransaksiModel(); $id = (int)($_POST['id'] ?? 0); $m->deletePenjualan($id, current_umkm_id(), (int)current_user()['id']); flash('success', 'Transaksi penjualan berhasil dihapus.'); $this->redirect('index.php?page=penjualan'); }
    public function invoice(): void { require_roles('super_admin','admin_umkm','operator'); $m=new TransaksiModel(); $row=$m->invoice((int)($_GET['id'] ?? 0), current_umkm_id()); if(!$row){ echo 'Invoice tidak ditemukan'; return; } include __DIR__ . '/../views/penjualan/invoice.php'; }
    public function whatsapp(): void {
        require_roles('super_admin','admin_umkm','operator');
        $m = new TransaksiModel();
        $row = $m->invoice((int)($_GET['id'] ?? 0), current_umkm_id());
        if (!$row) { flash('error', 'Invoice tidak ditemukan.'); $this->redirect('index.php?page=penjualan'); }
        $phone = clean_phone($row['telepon_pelanggan'] ?? '');
        if ($phone === '') { flash('error', 'Nomor WhatsApp pelanggan belum tersedia.'); $this->redirect('index.php?page=penjualan-invoice&id=' . (int)$row['id']); }
        $invoiceUrl = url('index.php?page=penjualan-invoice&id=' . (int)$row['id']);
        $message = "Yth. Bapak/Ibu " . ($row['nama_pelanggan'] ?: 'Pelanggan') . ",\n\nBerikut kami kirimkan invoice penjualan *" . ($row['kode_penjualan'] ?? '-') . "* tanggal " . date('d-m-Y', strtotime($row['tanggal'])) . ".\n\nTotal tagihan: " . fmt_rp($row['total']) . "\nSisa tagihan: " . fmt_rp($row['sisa']) . "\n\nLihat invoice lengkap di tautan berikut:\n{$invoiceUrl}\n\nTerima kasih.\n\nHormat kami,\n" . (current_user()['nama'] ?? 'Petugas') . "\n" . ($row['nama_umkm'] ?? 'UMKM');
        header('Location: ' . whatsapp_link($phone, $message));
        exit;
    }
}
