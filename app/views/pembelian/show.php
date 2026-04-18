<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="page-title h3 mb-1">Detail Pembelian</h1>
    <div class="text-muted">Lihat item pembelian, status hutang, histori pembayaran hutang, dan audit perubahan transaksi.</div>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian-history')) ?>">Riwayat Perubahan</a>
    <a class="btn btn-outline-dark" target="_blank" href="<?= e(url('index.php?page=pembelian-print&id=' . $row['id'])) ?>">Print</a>
    <a class="btn btn-outline-primary" href="<?= e(url('index.php?page=pembelian-edit&id=' . $row['id'])) ?>">Edit</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="enterprise-card p-4 mb-3">
      <div class="section-title mb-3">Informasi Pembelian</div>
      <div class="row g-3">
        <div class="col-md-6"><div class="text-muted small">Kode</div><div class="fw-semibold"><?= e($row['kode_pembelian']) ?></div></div>
        <div class="col-md-6"><div class="text-muted small">Tanggal</div><div class="fw-semibold"><?= e(date('d-m-Y H:i', strtotime($row['tanggal']))) ?></div></div>
        <div class="col-md-6"><div class="text-muted small">Supplier</div><div class="fw-semibold"><?= e($row['nama_supplier'] ?? '-') ?></div></div>
        <div class="col-md-6"><div class="text-muted small">Petugas</div><div class="fw-semibold"><?= e($row['user_nama'] ?? '-') ?></div></div>
        <div class="col-12"><div class="text-muted small">Keterangan</div><div><?= e($row['keterangan'] ?: '-') ?></div></div>
      </div>
    </div>

    <div class="enterprise-card p-0 overflow-hidden">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>No</th><th>Produk</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Jumlah</th></tr></thead>
          <tbody>
          <?php foreach(($row['items'] ?? []) as $i => $it): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><div class="fw-semibold"><?= e($it['nama_produk']) ?></div><div class="small text-muted"><?= e($it['kode_produk']) ?></div></td>
              <td class="text-end"><?= e(fmt_rp($it['harga'])) ?></td>
              <td class="text-center"><?= e(number_format((float)$it['qty'], 0, ',', '.')) ?></td>
              <td class="text-end fw-semibold"><?= e(fmt_rp($it['subtotal'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($row['items'])): ?><tr><td colspan="5" class="text-center text-muted py-5">Tidak ada item pembelian.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="enterprise-card p-4 mb-3">
      <div class="section-title mb-3">Ringkasan</div>
      <div class="summary-row"><span>Subtotal</span><strong><?= e(fmt_rp($row['subtotal'])) ?></strong></div>
      <div class="summary-row"><span>Diskon</span><strong><?= e(fmt_rp($row['diskon'])) ?></strong></div>
      <div class="summary-row summary-total"><span>Total</span><strong><?= e(fmt_rp($row['total'])) ?></strong></div>
      <div class="summary-row"><span>Dibayar</span><strong><?= e(fmt_rp($row['dibayar'])) ?></strong></div>
      <div class="summary-row"><span>Sisa</span><strong><?= e(fmt_rp($row['sisa'])) ?></strong></div>
      <div class="summary-row"><span>Status</span><span class="badge <?= $row['status_pembayaran']==='lunas' ? 'text-bg-success' : ($row['status_pembayaran']==='sebagian' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($row['status_pembayaran']) ?></span></div>
    </div>

    <div class="enterprise-card p-4 mb-3">
      <div class="section-title mb-3">Status Hutang</div>
      <?php if (!empty($row['hutang'])): ?>
        <div class="summary-row"><span>Total Hutang</span><strong><?= e(fmt_rp($row['hutang']['total_hutang'])) ?></strong></div>
        <div class="summary-row"><span>Terbayar</span><strong><?= e(fmt_rp($row['hutang']['total_bayar'])) ?></strong></div>
        <div class="summary-row"><span>Sisa</span><strong><?= e(fmt_rp($row['hutang']['sisa_hutang'])) ?></strong></div>
        <div class="summary-row"><span>Status</span><span class="badge <?= $row['hutang']['status']==='lunas' ? 'text-bg-success' : ($row['hutang']['status']==='sebagian' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($row['hutang']['status']) ?></span></div>
      <?php else: ?>
        <div class="text-muted">Transaksi ini tidak memiliki hutang.</div>
      <?php endif; ?>
    </div>

    <div class="enterprise-card p-4">
      <div class="section-title mb-3">Riwayat Audit</div>
      <?php if (!empty($row['logs'])): ?>
        <div class="timeline-clean">
          <?php foreach($row['logs'] as $log): ?>
            <div class="mb-3 pb-3 border-bottom">
              <div class="d-flex justify-content-between gap-2 flex-wrap">
                <div class="fw-semibold"><?= e($log['aktivitas']) ?></div>
                <div class="small text-muted"><?= e(date('d-m-Y H:i', strtotime($log['created_at']))) ?></div>
              </div>
              <div class="small text-muted mb-1">Oleh <?= e($log['user_nama']) ?></div>
              <div style="white-space:pre-line"><?= e($log['keterangan']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted">Belum ada riwayat audit.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($row['hutang']['payments'])): ?>
  <div class="enterprise-card p-0 overflow-hidden mt-3">
    <div class="p-4 pb-0"><div class="section-title">Riwayat Pembayaran Hutang</div></div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead><tr><th>Tanggal</th><th class="text-end">Nominal</th><th>Metode</th><th>Keterangan</th><th>Petugas</th></tr></thead>
        <tbody>
        <?php foreach($row['hutang']['payments'] as $pay): ?>
          <tr>
            <td><?= e(date('d-m-Y H:i', strtotime($pay['tanggal_bayar']))) ?></td>
            <td class="text-end"><?= e(fmt_rp($pay['nominal_bayar'])) ?></td>
            <td><?= e($pay['metode_pembayaran']) ?></td>
            <td><?= e($pay['keterangan'] ?: '-') ?></td>
            <td><?= e($pay['user_nama']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
