<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/LaporanModel.php';
class DashboardController extends Controller {
    public function index(): void {
        $m = new DashboardModel();
        $lap = new LaporanModel();
        if (has_role('super_admin')) {
            $global = $m->summaryGlobal();
            $saldo = $global['kas_masuk'] - $global['kas_keluar'];
            $this->view('dashboard/index', [
                'title' => 'Dashboard',
                'mode' => 'super_admin',
                'sum' => $global,
                'saldo' => $saldo,
                'umkmRows' => $m->umkmSummaryRows(),
                'profitRows' => $lap->rekapLabaRugiPerUmkm($_GET['from'] ?? null, $_GET['to'] ?? null),
                'from' => $_GET['from'] ?? '',
                'to' => $_GET['to'] ?? '',
            ]);
            return;
        }
        $sum = $m->summary((int)current_umkm_id());
        $saldo = $sum['kas_masuk'] - $sum['kas_keluar'];
        $this->view('dashboard/index', ['title' => 'Dashboard', 'mode'=>'umkm', 'sum' => $sum, 'saldo' => $saldo]);
    }
}
