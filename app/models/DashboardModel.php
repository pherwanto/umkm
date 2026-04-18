<?php
require_once __DIR__ . '/../core/BaseModel.php';
class DashboardModel extends BaseModel {
    private function scalar(string $sql, array $params=[]): float { $st=$this->db->prepare($sql); $st->execute($params); return (float)($st->fetchColumn() ?: 0); }
    public function summary(int $umkmId): array {
        return [
            'penjualan' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM penjualan WHERE umkm_id=?", [$umkmId]),
            'pembelian' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM pembelian WHERE umkm_id=?", [$umkmId]),
            'piutang' => $this->scalar("SELECT COALESCE(SUM(sisa_piutang),0) FROM piutang WHERE umkm_id=? AND status<>'lunas'", [$umkmId]),
            'hutang' => $this->scalar("SELECT COALESCE(SUM(sisa_hutang),0) FROM hutang WHERE umkm_id=? AND status<>'lunas'", [$umkmId]),
            'kas_masuk' => $this->scalar("SELECT COALESCE(SUM(nominal),0) FROM kas WHERE umkm_id=? AND jenis='masuk'", [$umkmId]),
            'kas_keluar' => $this->scalar("SELECT COALESCE(SUM(nominal),0) FROM kas WHERE umkm_id=? AND jenis='keluar'", [$umkmId]),
        ];
    }
    public function summaryGlobal(): array {
        return [
            'penjualan' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM penjualan"),
            'pembelian' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM pembelian"),
            'piutang' => $this->scalar("SELECT COALESCE(SUM(sisa_piutang),0) FROM piutang WHERE status<>'lunas'"),
            'hutang' => $this->scalar("SELECT COALESCE(SUM(sisa_hutang),0) FROM hutang WHERE status<>'lunas'"),
            'kas_masuk' => $this->scalar("SELECT COALESCE(SUM(nominal),0) FROM kas WHERE jenis='masuk'"),
            'kas_keluar' => $this->scalar("SELECT COALESCE(SUM(nominal),0) FROM kas WHERE jenis='keluar'"),
        ];
    }
    public function umkmSummaryRows(): array {
        $sql = "SELECT u.id, u.nama_umkm,
                COALESCE((SELECT SUM(p.total) FROM penjualan p WHERE p.umkm_id=u.id),0) AS total_penjualan,
                COALESCE((SELECT SUM(pb.total) FROM pembelian pb WHERE pb.umkm_id=u.id),0) AS total_pembelian,
                COALESCE((SELECT SUM(pt.sisa_piutang) FROM piutang pt WHERE pt.umkm_id=u.id AND pt.status<>'lunas'),0) AS total_piutang,
                COALESCE((SELECT SUM(h.sisa_hutang) FROM hutang h WHERE h.umkm_id=u.id AND h.status<>'lunas'),0) AS total_hutang,
                COALESCE((SELECT SUM(k.nominal) FROM kas k WHERE k.umkm_id=u.id AND k.jenis='masuk'),0) -
                COALESCE((SELECT SUM(k2.nominal) FROM kas k2 WHERE k2.umkm_id=u.id AND k2.jenis='keluar'),0) AS saldo_kas
                FROM umkm u ORDER BY u.nama_umkm";
        return $this->db->query($sql)->fetchAll();
    }
}
