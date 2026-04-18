<?php $isSuper = ($mode ?? 'umkm') === 'super_admin'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 mobile-stack">
  <div>
    <h1 class="page-title h3 mb-1"><?= $isSuper ? 'Dashboard Super Admin Multi-UMKM' : 'Dashboard UMKM' ?></h1>
    <div class="text-muted">
      <?php if ($isSuper): ?>
        Pantau performa seluruh UMKM binaan dalam satu layar, termasuk ringkasan kas, piutang, hutang, dan rekap laba rugi per UMKM.
      <?php else: ?>
        Role aktif: <strong><?= e(strtoupper(str_replace('_',' ', current_role()))) ?></strong>. Halaman dan aksi yang tampil sudah disesuaikan dengan hak akses pengguna.
      <?php endif; ?>
    </div>
  </div>
  <?php if (has_role('super_admin','admin_umkm','operator')): ?><a class="btn btn-primary" href="<?= e(url('index.php?page=penjualan-create')) ?>">Transaksi Penjualan Baru</a><?php endif; ?>
</div>

<div class="row g-3 mb-4">
  <?php $cards=[['Penjualan',$sum['penjualan'],'bi-receipt'],['Pembelian',$sum['pembelian'],'bi-bag'],['Piutang',$sum['piutang'],'bi-wallet2'],['Hutang',$sum['hutang'],'bi-cash'],['Saldo Kas',$saldo,'bi-bank']]; foreach($cards as $c): ?>
    <div class="col-12 col-sm-6 col-xl"><div class="enterprise-card stat-card h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="stat-label"><?= e($c[0]) ?></div><div class="stat-value"><?= e(fmt_rp($c[1])) ?></div></div><span class="badge rounded-pill text-bg-light"><i class="bi <?= e($c[2]) ?>"></i></span></div></div></div>
  <?php endforeach; ?>
</div>

<?php if ($isSuper): ?>
<form class="enterprise-card p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <div class="section-title mb-1">Filter Rekap Laba Rugi per UMKM</div>
      <div class="small text-muted">Gunakan periode untuk membandingkan performa setiap UMKM binaan.</div>
    </div>
  </div>
  <div class="filter-grid">
    <div><label class="form-label">Dari</label><input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>"></div>
    <div><label class="form-label">Sampai</label><input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>"></div>
    <div class="d-flex align-items-end gap-2"><button class="btn btn-dark w-100">Tampilkan</button></div>
    <div class="d-flex align-items-end"><a href="<?= e(url('index.php?page=dashboard')) ?>" class="btn btn-outline-secondary w-100">Reset</a></div>
  </div>
</form>

<div class="row g-3">
  <div class="col-12 col-xxl-7">
    <div class="enterprise-card h-100">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="fw-bold mb-1">Ringkasan Multi-UMKM</h5>
            <div class="text-muted small">Total transaksi dan posisi kas masing-masing UMKM.</div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>UMKM</th><th class="text-end">Penjualan</th><th class="text-end">Pembelian</th><th class="text-end">Piutang</th><th class="text-end">Hutang</th><th class="text-end">Saldo Kas</th></tr></thead>
            <tbody>
              <?php foreach (($umkmRows ?? []) as $row): ?>
                <tr>
                  <td class="fw-semibold"><?= e($row['nama_umkm']) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['total_penjualan'])) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['total_pembelian'])) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['total_piutang'])) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['total_hutang'])) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['saldo_kas'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-xxl-5">
    <div class="enterprise-card h-100">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Rekap Laba Rugi per UMKM</h5>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>UMKM</th><th class="text-end">Laba Kotor</th><th class="text-end">Laba Bersih</th></tr></thead>
            <tbody>
              <?php foreach (($profitRows ?? []) as $row): ?>
                <tr>
                  <td class="fw-semibold"><?= e($row['nama_umkm']) ?></td>
                  <td class="text-end"><?= e(fmt_rp($row['laba_kotor'])) ?></td>
                  <td class="text-end <?= $row['laba_bersih'] < 0 ? 'text-danger' : 'text-success' ?>"><?= e(fmt_rp($row['laba_bersih'])) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($profitRows)): ?><tr><td colspan="3" class="text-center text-muted py-4">Belum ada data laba rugi per UMKM.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="row g-3">
  <div class="col-lg-8"><div class="enterprise-card h-100"><div class="card-body p-4"><h5 class="fw-bold mb-2">Ringkasan Sistem</h5><p class="mb-0 text-muted">Starter project ini sudah mencakup login dengan captcha, hak akses role, modul penjualan dengan AJAX product search, dukungan barcode scanner, upload gambar produk, audit log penjualan, invoice, pengelolaan pembelian, laporan, serta alur reset password berbasis SMTP.</p></div></div></div>
  <div class="col-lg-4"><div class="enterprise-card h-100"><div class="card-body p-4"><h6 class="fw-bold">Akses sesuai role</h6><ul class="mb-0 text-muted small"><li>Super Admin: akses penuh ke data master, transaksi, laporan, audit.</li><li>Admin UMKM: akses data UMKM, transaksi, laporan, audit.</li><li>Operator: fokus pada dashboard, penjualan, detail invoice, dan edit transaksi penjualan.</li></ul></div></div></div>
</div>
<?php endif; ?>
