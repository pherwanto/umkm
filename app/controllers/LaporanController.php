<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/LaporanModel.php';
class LaporanController extends Controller {
    private function dateFilters(): array {
        return [trim($_GET['from'] ?? ''), trim($_GET['to'] ?? '')];
    }

    private function exportExcel(string $filename, array $headers, array $rows): void {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers, "\t");
        foreach ($rows as $row) {
            fputcsv($out, $row, "\t");
        }
        fclose($out);
        exit;
    }

    public function penjualan(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel();
        [$from,$to] = $this->dateFilters();
        $rows=$m->penjualan(current_umkm_id(), $from ?: null, $to ?: null);
        $summary = [
            'total' => array_sum(array_map(static fn(array $r): float => (float)($r['total'] ?? 0), $rows)),
            'dibayar' => array_sum(array_map(static fn(array $r): float => (float)($r['dibayar'] ?? 0), $rows)),
            'sisa' => array_sum(array_map(static fn(array $r): float => (float)($r['sisa'] ?? 0), $rows)),
        ];
        $this->view('laporan/penjualan',['title'=>'Laporan Penjualan','rows'=>$rows,'from'=>$from,'to'=>$to,'summary'=>$summary]);
    }
    public function pembelian(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel();
        [$from,$to] = $this->dateFilters();
        $rows=$m->pembelian(current_umkm_id(), $from ?: null, $to ?: null);
        $summary = [
            'total' => array_sum(array_map(static fn(array $r): float => (float)($r['total'] ?? 0), $rows)),
            'dibayar' => array_sum(array_map(static fn(array $r): float => (float)($r['dibayar'] ?? 0), $rows)),
            'sisa' => array_sum(array_map(static fn(array $r): float => (float)($r['sisa'] ?? 0), $rows)),
        ];
        $this->view('laporan/pembelian',['title'=>'Laporan Pembelian','rows'=>$rows,'from'=>$from,'to'=>$to,'summary'=>$summary]);
    }
    public function kas(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel();
        [$from,$to] = $this->dateFilters();
        $rows=$m->kas(current_umkm_id(), $from ?: null, $to ?: null);
        $summary = ['masuk' => 0.0, 'keluar' => 0.0];
        foreach ($rows as $r) {
            $nominal = (float)($r['nominal'] ?? 0);
            if (($r['jenis'] ?? '') === 'masuk') $summary['masuk'] += $nominal;
            if (($r['jenis'] ?? '') === 'keluar') $summary['keluar'] += $nominal;
        }
        $summary['saldo_kas'] = $summary['masuk'] - $summary['keluar'];
        $this->view('laporan/kas',['title'=>'Laporan Kas','rows'=>$rows,'from'=>$from,'to'=>$to,'summary'=>$summary]);
    }
    public function labaRugi(): void {
        require_roles('super_admin','admin_umkm');
        $m = new LaporanModel();
        [$from,$to] = $this->dateFilters();
        $this->view('laporan/laba_rugi', [
            'title' => 'Laporan Laba Rugi',
            'from' => $from,
            'to' => $to,
            'summary' => $m->labaRugiSummary(current_umkm_id(), $from ?: null, $to ?: null),
            'expenseRows' => $m->labaRugiExpenses(current_umkm_id(), $from ?: null, $to ?: null),
            'profitRows' => has_role('super_admin') ? $m->rekapLabaRugiPerUmkm($from ?: null, $to ?: null) : [],
        ]);
    }

    public function penjualanExcel(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel(); [$from,$to] = $this->dateFilters();
        $rows=$m->penjualan(current_umkm_id(), $from ?: null, $to ?: null);
        $data=[]; foreach($rows as $r){ $data[]=[date('d-m-Y', strtotime($r['tanggal'])),$r['kode_penjualan'],$r['nama_pelanggan'] ?? '-',(float)$r['total'],(float)$r['dibayar'],(float)$r['sisa'],$r['status_pembayaran']]; }
        $this->exportExcel('laporan-penjualan.xls',['Tanggal','Kode','Pelanggan','Total','Dibayar','Sisa','Status'],$data);
    }
    public function pembelianExcel(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel(); [$from,$to] = $this->dateFilters();
        $rows=$m->pembelian(current_umkm_id(), $from ?: null, $to ?: null);
        $data=[]; foreach($rows as $r){ $data[]=[date('d-m-Y', strtotime($r['tanggal'])),$r['kode_pembelian'],$r['nama_supplier'] ?? '-',(float)$r['total'],(float)$r['dibayar'],(float)$r['sisa'],$r['status_pembayaran']]; }
        $this->exportExcel('laporan-pembelian.xls',['Tanggal','Kode','Supplier','Total','Dibayar','Sisa','Status'],$data);
    }
    public function kasExcel(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel(); [$from,$to] = $this->dateFilters();
        $rows=$m->kas(current_umkm_id(), $from ?: null, $to ?: null);
        $data=[]; foreach($rows as $r){ $data[]=[date('d-m-Y', strtotime($r['tanggal'])),$r['nama_kategori'],$r['jenis'],(float)$r['nominal'],$r['keterangan'] ?? '-']; }
        $this->exportExcel('laporan-kas.xls',['Tanggal','Kategori','Jenis','Nominal','Keterangan'],$data);
    }
    public function labaRugiExcel(): void {
        require_roles('super_admin','admin_umkm');
        $m=new LaporanModel(); [$from,$to] = $this->dateFilters();
        $sum=$m->labaRugiSummary(current_umkm_id(), $from ?: null, $to ?: null);
        $data=[
            ['Pendapatan Penjualan',(float)$sum['pendapatan_penjualan']],
            ['HPP Estimasi',(float)$sum['hpp_estimasi']],
            ['Pendapatan Lain',(float)$sum['pendapatan_lain']],
            ['Beban Operasional',(float)$sum['beban_operasional']],
            ['Laba Kotor',(float)$sum['laba_kotor']],
            ['Laba Bersih',(float)$sum['laba_bersih']],
        ];
        $this->exportExcel('laporan-laba-rugi.xls',['Komponen','Nilai'],$data);
    }

    public function renderPrint(): void {
        require_roles('super_admin','admin_umkm');
        $type = $_GET['type'] ?? 'penjualan';
        $m = new LaporanModel();
        [$from,$to] = $this->dateFilters();
        $payload = ['title'=>'Cetak Laporan','from'=>$from,'to'=>$to,'type'=>$type,'rows'=>[],'headers'=>[],'summary'=>[]];
        switch ($type) {
            case 'pembelian':
                $rawRows = $m->pembelian(current_umkm_id(), $from ?: null, $to ?: null);
                $payload['title']='Cetak Laporan Pembelian';
                $payload['headers']=['Tanggal','Kode','Supplier','Total','Dibayar','Sisa','Status'];
                foreach($rawRows as $r){ $payload['rows'][]=[date('d-m-Y', strtotime($r['tanggal'])),$r['kode_pembelian'],$r['nama_supplier'] ?? '-',fmt_rp($r['total']),fmt_rp($r['dibayar']),fmt_rp($r['sisa']),$r['status_pembayaran']]; }
                $payload['summary'] = [
                    'Total' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['total'] ?? 0), $rawRows))),
                    'Dibayar' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['dibayar'] ?? 0), $rawRows))),
                    'Sisa' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['sisa'] ?? 0), $rawRows))),
                ];
                break;
            case 'kas':
                $rawRows = $m->kas(current_umkm_id(), $from ?: null, $to ?: null);
                $payload['title']='Cetak Laporan Kas';
                $payload['headers']=['Tanggal','Kategori','Jenis','Nominal','Keterangan'];
                $kasMasuk = 0.0;
                $kasKeluar = 0.0;
                foreach($rawRows as $r){
                    $payload['rows'][]=[date('d-m-Y', strtotime($r['tanggal'])),$r['nama_kategori'],$r['jenis'],fmt_rp($r['nominal']),$r['keterangan'] ?? '-'];
                    $nominal = (float)($r['nominal'] ?? 0);
                    if (($r['jenis'] ?? '') === 'masuk') $kasMasuk += $nominal;
                    if (($r['jenis'] ?? '') === 'keluar') $kasKeluar += $nominal;
                }
                $payload['summary'] = [
                    'Kas Masuk' => fmt_rp($kasMasuk),
                    'Kas Keluar' => fmt_rp($kasKeluar),
                    'Saldo Kas (Net Cash)' => fmt_rp($kasMasuk - $kasKeluar),
                ];
                break;
            case 'laba-rugi':
                $sum = $m->labaRugiSummary(current_umkm_id(), $from ?: null, $to ?: null);
                $payload['title']='Cetak Laporan Laba Rugi';
                $payload['headers']=['Komponen','Nilai'];
                $payload['rows']=[
                    ['Pendapatan Penjualan',fmt_rp($sum['pendapatan_penjualan'])],
                    ['HPP Estimasi',fmt_rp($sum['hpp_estimasi'])],
                    ['Pendapatan Lain',fmt_rp($sum['pendapatan_lain'])],
                    ['Beban Operasional',fmt_rp($sum['beban_operasional'])],
                    ['Laba Kotor',fmt_rp($sum['laba_kotor'])],
                    ['Laba Bersih',fmt_rp($sum['laba_bersih'])],
                ];
                $payload['summary']=$sum;
                break;
            case 'penjualan':
            default:
                $rawRows = $m->penjualan(current_umkm_id(), $from ?: null, $to ?: null);
                $payload['title']='Cetak Laporan Penjualan';
                $payload['headers']=['Tanggal','Kode','Pelanggan','Total','Dibayar','Sisa','Status'];
                foreach($rawRows as $r){ $payload['rows'][]=[date('d-m-Y', strtotime($r['tanggal'])),$r['kode_penjualan'],$r['nama_pelanggan'] ?? '-',fmt_rp($r['total']),fmt_rp($r['dibayar']),fmt_rp($r['sisa']),$r['status_pembayaran']]; }
                $payload['summary'] = [
                    'Total' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['total'] ?? 0), $rawRows))),
                    'Dibayar' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['dibayar'] ?? 0), $rawRows))),
                    'Sisa' => fmt_rp(array_sum(array_map(static fn(array $r): float => (float)($r['sisa'] ?? 0), $rawRows))),
                ];
                break;
        }
        extract($payload);
        include __DIR__ . '/../views/laporan/print.php';
        exit;
    }
}
