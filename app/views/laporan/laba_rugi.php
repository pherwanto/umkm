<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title mb-1">Laporan Laba Rugi Sederhana</h1>
    <div class="text-muted">Ringkasan pendapatan, HPP estimasi, beban operasional, dan laba bersih.</div>
  </div>
</div>

<form class="card p-3 mb-3">
  <input type="hidden" name="page" value="laporan-laba-rugi">
  <div class="filter-grid">
    <div><label class="form-label">Dari</label><input type="date" class="form-control" name="from" value="<?= e($from) ?>"></div>
    <div><label class="form-label">Sampai</label><input type="date" class="form-control" name="to" value="<?= e($to) ?>"></div>
    <div class="d-flex align-items-end gap-2"><button class="btn btn-dark w-100">Tampilkan</button></div>
    <div class="d-flex align-items-end gap-2"><a class="btn btn-outline-dark w-100" target="_blank" href="<?= e(url('index.php?page=laporan-print&type=laba-rugi&from=' . urlencode($from) . '&to=' . urlencode($to))) ?>">Cetak / PDF</a></div>
  </div>
  <div class="mt-3"><a class="btn btn-outline-success" href="<?= e(url('index.php?page=laporan-laba-rugi-excel&from=' . urlencode($from) . '&to=' . urlencode($to))) ?>">Export Excel</a></div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card stat-card"><div class="stat-label">Pendapatan Penjualan</div><div class="stat-value"><?= e(fmt_rp($summary['pendapatan_penjualan'])) ?></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="stat-label">HPP Estimasi</div><div class="stat-value"><?= e(fmt_rp($summary['hpp_estimasi'])) ?></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="stat-label">Pendapatan Lain</div><div class="stat-value"><?= e(fmt_rp($summary['pendapatan_lain'])) ?></div></div></div>
  <div class="col-md-3"><div class="card stat-card"><div class="stat-label">Beban Operasional</div><div class="stat-value"><?= e(fmt_rp($summary['beban_operasional'])) ?></div></div></div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-5">
    <div class="card p-3 h-100">
      <h5 class="mb-3">Ringkasan Perhitungan</h5>
      <table class="table table-sm">
        <tr><th>Pendapatan Penjualan</th><td class="text-end"><?= e(fmt_rp($summary['pendapatan_penjualan'])) ?></td></tr>
        <tr><th>HPP Estimasi</th><td class="text-end text-danger">- <?= e(fmt_rp($summary['hpp_estimasi'])) ?></td></tr>
        <tr class="fw-semibold"><th>Laba Kotor</th><td class="text-end"><?= e(fmt_rp($summary['laba_kotor'])) ?></td></tr>
        <tr><th>Pendapatan Lain</th><td class="text-end"><?= e(fmt_rp($summary['pendapatan_lain'])) ?></td></tr>
        <tr><th>Beban Operasional</th><td class="text-end text-danger">- <?= e(fmt_rp($summary['beban_operasional'])) ?></td></tr>
        <tr class="table-light fw-bold"><th>Laba Bersih</th><td class="text-end"><?= e(fmt_rp($summary['laba_bersih'])) ?></td></tr>
      </table>
      <div class="small text-muted">Catatan: laporan ini bersifat sederhana. HPP dihitung dari harga beli produk yang tercatat saat ini.</div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card p-3 h-100">
      <h5 class="mb-3">Rincian Beban Operasional (Kas Manual Keluar)</h5>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Nominal</th></tr></thead>
          <tbody>
            <?php foreach ($expenseRows as $r): ?>
              <tr>
                <td><?= e(date('d-m-Y', strtotime($r['tanggal']))) ?></td>
                <td><?= e($r['nama_kategori']) ?></td>
                <td><?= e($r['keterangan'] ?? '-') ?></td>
                <td class="text-end"><?= e(fmt_rp($r['nominal'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$expenseRows): ?><tr><td colspan="4" class="text-center text-muted py-4">Tidak ada beban operasional pada periode ini.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php if (has_role('super_admin')): ?>
<div class="card p-3">
  <h5 class="mb-3">Rekap Laba Rugi per UMKM</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>UMKM</th><th class="text-end">Pendapatan</th><th class="text-end">HPP</th><th class="text-end">Beban</th><th class="text-end">Laba Bersih</th></tr></thead>
      <tbody>
        <?php foreach ($profitRows as $row): ?>
          <tr>
            <td class="fw-semibold"><?= e($row['nama_umkm']) ?></td>
            <td class="text-end"><?= e(fmt_rp($row['pendapatan_penjualan'])) ?></td>
            <td class="text-end"><?= e(fmt_rp($row['hpp_estimasi'])) ?></td>
            <td class="text-end"><?= e(fmt_rp($row['beban_operasional'])) ?></td>
            <td class="text-end <?= $row['laba_bersih'] < 0 ? 'text-danger' : 'text-success' ?>"><?= e(fmt_rp($row['laba_bersih'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
