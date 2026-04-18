<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="h3 mb-1">Detail Penjualan</h1>
    <div class="muted">Telusuri isi transaksi, status pembayaran, dan riwayat perubahannya.</div>
  </div>
  <div class="d-inline-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=penjualan')) ?>">Kembali</a>
    <a class="btn btn-outline-primary" href="<?= e(url('index.php?page=penjualan-edit&id='.$row['id'])) ?>">Edit</a>
    <a class="btn btn-primary" target="_blank" href="<?= e(url('index.php?page=penjualan-invoice&id='.$row['id'])) ?>">Cetak Invoice</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-8">
    <div class="card h-100"><div class="card-body">
      <div class="row g-3">
        <div class="col-md-4"><div class="summary-box"><div class="muted small">Kode</div><div class="fw-semibold"><?= e($row['kode_penjualan']) ?></div></div></div>
        <div class="col-md-4"><div class="summary-box"><div class="muted small">Tanggal</div><div class="fw-semibold"><?= e(date('d-m-Y H:i', strtotime($row['tanggal']))) ?></div></div></div>
        <div class="col-md-4"><div class="summary-box"><div class="muted small">Operator</div><div class="fw-semibold"><?= e($row['user_nama'] ?? '-') ?></div></div></div>
        <div class="col-md-6"><div class="summary-box"><div class="muted small">Pelanggan</div><div class="fw-semibold"><?= e($row['nama_pelanggan'] ?? 'Umum') ?></div><div class="small text-secondary"><?= e($row['telepon_pelanggan'] ?? '') ?></div></div></div>
        <div class="col-md-6"><div class="summary-box"><div class="muted small">Status Pembayaran</div><div class="fw-semibold text-capitalize"><?= e(str_replace('_',' ', $row['status_pembayaran'])) ?></div><div class="small text-secondary">Metode: <?= e($row['metode_pembayaran']) ?></div></div></div>
      </div>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <div class="section-title mb-3">Ringkasan Nilai</div>
      <div class="d-flex justify-content-between py-1"><span class="muted">Subtotal</span><strong><?= e(fmt_rp($row['subtotal'])) ?></strong></div>
      <div class="d-flex justify-content-between py-1"><span class="muted">Diskon</span><strong><?= e(fmt_rp($row['diskon'])) ?></strong></div>
      <div class="d-flex justify-content-between py-1 border-top mt-2 pt-2"><span class="muted">Total</span><strong><?= e(fmt_rp($row['total'])) ?></strong></div>
      <div class="d-flex justify-content-between py-1"><span class="muted">Dibayar</span><strong><?= e(fmt_rp($row['dibayar'])) ?></strong></div>
      <div class="d-flex justify-content-between py-1"><span class="muted">Sisa</span><strong><?= e(fmt_rp($row['sisa'])) ?></strong></div>
    </div></div>
  </div>
</div>

<div class="card mb-3"><div class="card-body">
  <div class="section-title mb-3">Detail Item</div>
  <div class="table-responsive">
    <table class="table table-striped align-middle mb-0">
      <thead><tr><th>No</th><th>Produk</th><th>Kode</th><th class="text-end">Harga Jual</th><th class="text-end">QTT</th><th class="text-end">Jumlah</th></tr></thead>
      <tbody>
      <?php foreach (($row['items'] ?? []) as $i => $it): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($it['nama_produk']) ?></td>
          <td><?= e($it['kode_produk']) ?></td>
          <td class="text-end"><?= e(fmt_rp($it['harga'])) ?></td>
          <td class="text-end"><?= e(number_format((float)$it['qty'], 0, ',', '.')) ?></td>
          <td class="text-end"><?= e(fmt_rp($it['subtotal'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div></div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card h-100"><div class="card-body">
      <div class="section-title mb-3">Status Piutang</div>
      <?php if (!empty($row['piutang'])): $piutang = $row['piutang']; ?>
        <div class="d-flex justify-content-between py-1"><span class="muted">Tanggal Piutang</span><strong><?= e(date('d-m-Y', strtotime($piutang['tanggal_piutang']))) ?></strong></div>
        <div class="d-flex justify-content-between py-1"><span class="muted">Jatuh Tempo</span><strong><?= e($piutang['jatuh_tempo'] ? date('d-m-Y', strtotime($piutang['jatuh_tempo'])) : '-') ?></strong></div>
        <div class="d-flex justify-content-between py-1"><span class="muted">Total Piutang</span><strong><?= e(fmt_rp($piutang['total_piutang'])) ?></strong></div>
        <div class="d-flex justify-content-between py-1"><span class="muted">Total Bayar</span><strong><?= e(fmt_rp($piutang['total_bayar'])) ?></strong></div>
        <div class="d-flex justify-content-between py-1 border-top mt-2 pt-2"><span class="muted">Sisa Piutang</span><strong><?= e(fmt_rp($piutang['sisa_piutang'])) ?></strong></div>
        <div class="small mt-2"><span class="badge badge-soft"><?= e($piutang['status']) ?></span></div>
        <hr>
        <div class="fw-semibold mb-2">Riwayat Pembayaran Piutang</div>
        <?php if (!empty($piutang['payments'])): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Tanggal</th><th class="text-end">Nominal</th><th>Petugas</th></tr></thead>
              <tbody>
              <?php foreach ($piutang['payments'] as $pay): ?>
                <tr>
                  <td><?= e(date('d-m-Y H:i', strtotime($pay['tanggal_bayar']))) ?></td>
                  <td class="text-end"><?= e(fmt_rp($pay['nominal_bayar'])) ?></td>
                  <td><?= e($pay['user_nama']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">Belum ada pembayaran piutang.</div>
        <?php endif; ?>
      <?php else: ?>
        <div class="text-muted">Transaksi ini tidak memiliki piutang aktif.</div>
      <?php endif; ?>
    </div></div>
  </div>
  <div class="col-lg-7">
    <div class="card h-100"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3"><div class="section-title">Riwayat Perubahan Transaksi</div><a class="btn btn-sm btn-outline-secondary" href="<?= e(url('index.php?page=penjualan-history')) ?>">Lihat Semua</a></div>
      <?php if (!empty($row['logs'])): ?>
        <div class="list-group list-group-flush">
          <?php foreach ($row['logs'] as $log): ?>
            <div class="list-group-item px-0">
              <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div>
                  <div class="fw-semibold"><?= e($log['aktivitas']) ?></div>
                  <div class="small text-secondary"><?= e($log['user_nama']) ?> · <?= e(date('d-m-Y H:i', strtotime($log['created_at']))) ?></div>
                </div>
              </div>
              <div class="mt-2 small" style="white-space:pre-line"><?= e($log['keterangan']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted">Belum ada riwayat perubahan untuk transaksi ini.</div>
      <?php endif; ?>
    </div></div>
  </div>
</div>
