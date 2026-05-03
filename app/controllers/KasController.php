<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/KasManualModel.php';
class KasController extends Controller {
    public function index(): void {
        require_roles('super_admin','admin_umkm');
        $m = new KasManualModel();
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'jenis' => trim((string)($_GET['jenis'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
        ];
        $rows = $m->all(current_umkm_id(), $filters);
        $saldoAkhir = 0.0;
        foreach ($rows as $r) {
            $delta = ($r['jenis'] ?? '') === 'masuk' ? (float)($r['nominal'] ?? 0) : -(float)($r['nominal'] ?? 0);
            $saldoAkhir += $delta;
        }
        foreach ($rows as $i => $r) {
            $delta = ($r['jenis'] ?? '') === 'masuk' ? (float)($r['nominal'] ?? 0) : -(float)($r['nominal'] ?? 0);
            $rows[$i]['saldo_akhir'] = $saldoAkhir;
            $rows[$i]['saldo_awal'] = $saldoAkhir - $delta;
            $saldoAkhir = $rows[$i]['saldo_awal'];
        }
        $saldoAwalPeriode = $rows ? (float)$rows[count($rows) - 1]['saldo_awal'] : 0.0;
        $saldoAkhirPeriode = $rows ? (float)$rows[0]['saldo_akhir'] : 0.0;
        $this->view('kas_manual/index', [
            'title' => 'Kas Manual Masuk/Keluar',
            'rows' => $rows,
            'filters' => $filters,
            'saldo_awal_periode' => $saldoAwalPeriode,
            'saldo_akhir_periode' => $saldoAkhirPeriode,
        ]);
    }
    public function create(): void {
        require_roles('super_admin','admin_umkm');
        $m = new KasManualModel();
        if (is_post()) {
            csrf_check();
            $m->create($_POST, current_umkm_id(), (int)current_user()['id']);
            flash('success', 'Kas manual berhasil ditambahkan.');
            $this->redirect('index.php?page=kas-manual');
        }
        $this->view('kas_manual/form', [
            'title' => 'Tambah Kas Manual',
            'row' => ['tanggal' => date('Y-m-d\TH:i'), 'jenis' => 'masuk'],
            'categories' => $m->categories(current_umkm_id()),
        ]);
    }
    public function edit(): void {
        require_roles('super_admin','admin_umkm');
        $m = new KasManualModel();
        $id = (int)($_GET['id'] ?? 0);
        $row = $m->find($id, current_umkm_id());
        if (!$row) { flash('error', 'Data kas manual tidak ditemukan.'); $this->redirect('index.php?page=kas-manual'); }
        if (is_post()) {
            csrf_check();
            $m->update($id, $_POST, current_umkm_id(), (int)current_user()['id']);
            flash('success', 'Kas manual berhasil diperbarui.');
            $this->redirect('index.php?page=kas-manual');
        }
        $row['tanggal'] = date('Y-m-d\TH:i', strtotime($row['tanggal']));
        $this->view('kas_manual/form', [
            'title' => 'Edit Kas Manual',
            'row' => $row,
            'categories' => $m->categories(current_umkm_id()),
        ]);
    }
    public function delete(): void {
        require_roles('super_admin','admin_umkm');
        if (!is_post()) { $this->redirect('index.php?page=kas-manual'); }
        csrf_check();
        $m = new KasManualModel();
        $m->delete((int)($_POST['id'] ?? 0), current_umkm_id());
        flash('success', 'Kas manual berhasil dihapus.');
        $this->redirect('index.php?page=kas-manual');
    }
}
