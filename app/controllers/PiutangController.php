<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/TransaksiModel.php';
class PiutangController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m=new TransaksiModel();
        $rows = $m->piutangAll(current_umkm_id());
        $summary = [
            'total_piutang' => (int)array_sum(array_map(static fn($r) => (float)($r['total_piutang'] ?? 0), $rows)),
            'total_bayar' => (int)array_sum(array_map(static fn($r) => (float)($r['total_bayar'] ?? 0), $rows)),
            'sisa_piutang' => (int)array_sum(array_map(static fn($r) => (float)($r['sisa_piutang'] ?? 0), $rows)),
        ];
        $this->view('piutang/index',['title'=>'Piutang','rows'=>$rows,'summary'=>$summary]);
    }

    public function pay(): void {
        require_roles('super_admin','admin_umkm');
        $m=new TransaksiModel();
        if(is_post()){
            csrf_check();
            $m->payPiutang((int)$_POST['id'],(float)$_POST['nominal_bayar'],$_POST['tanggal_bayar'],$_POST['metode_pembayaran'],$_POST['keterangan'],current_umkm_id(),(int)current_user()['id']);
            flash('success','Pembayaran berhasil disimpan.');
        }
        $this->redirect('index.php?page=piutang');
    }

    public function whatsapp(): void {
        require_roles('super_admin','admin_umkm');
        $id = (int)($_GET['id'] ?? 0);
        $m = new TransaksiModel();
        $row = $m->piutangWhatsappData($id, current_umkm_id());
        if (!$row) { flash('error', 'Data piutang tidak ditemukan.'); $this->redirect('index.php?page=piutang'); }
        $phone = clean_phone($row['telepon_pelanggan'] ?? '');
        if ($phone === '') { flash('error', 'Nomor WhatsApp pelanggan belum tersedia.'); $this->redirect('index.php?page=piutang'); }
        $invoiceUrl = url('index.php?page=penjualan-invoice&id=' . (int)$row['penjualan_id']);
        $tanggalSurat = date('d F Y');
        $bulanRomawi = ['01'=>'I','02'=>'II','03'=>'III','04'=>'IV','05'=>'V','06'=>'VI','07'=>'VII','08'=>'VIII','09'=>'IX','10'=>'X','11'=>'XI','12'=>'XII'][date('m')] ?? 'I';
        $nomor = sprintf('%03d/FIN/%s/%s', $row['id'], $bulanRomawi, date('Y'));
        $message = "Nomor: {$nomor}\nTanggal: {$tanggalSurat}\n\nKepada Yth,\nBapak/Ibu " . ($row['nama_pelanggan'] ?: 'Pelanggan') . "\n" . ($row['alamat_pelanggan'] ?: '-') . "\n\nPerihal: Pengantar Tagihan\n\nDengan hormat,\n\nBersama surat ini kami sampaikan tagihan atas produk yang telah diberikan. Untuk rincian lengkap, invoice dapat dilihat melalui tautan berikut: {$invoiceUrl}\n\nNilai tagihan saat ini: " . fmt_rp($row['sisa_piutang']) . "\n\nKami mohon agar pembayaran dapat dilakukan sesuai dengan ketentuan yang berlaku.\n\nAtas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.\n\nHormat kami,\n" . (current_user()['nama'] ?? 'Petugas') . "\nBagian Keuangan\n" . ($row['nama_umkm'] ?? 'UMKM');
        header('Location: ' . whatsapp_link($phone, $message));
        exit;
    }
}
