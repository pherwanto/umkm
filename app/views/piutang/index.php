<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="page-title h3 mb-1">Piutang</h1>
    <div class="text-muted">Pantau sisa piutang, catat pelunasan, dan kirim pengantar tagihan via WhatsApp.</div>
  </div>
</div>

<div class="enterprise-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
      <tr>
        <th>Referensi</th>
        <th>Pelanggan</th>
        <th class="text-end">Total</th>
        <th class="text-end">Terbayar</th>
        <th class="text-end">Sisa</th>
        <th>Status</th>
        <th class="text-end">Aksi</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td class="fw-semibold"><?= e($r['kode_penjualan']) ?></td>
          <td>
            <div class="fw-semibold"><?= e($r['nama_pelanggan'] ?? 'Umum') ?></div>
            <div class="small text-muted"><?= e($r['telepon_pelanggan'] ?? '-') ?></div>
          </td>
          <td class="text-end"><?= e(fmt_rp($r['total_piutang'])) ?></td>
          <td class="text-end"><?= e(fmt_rp($r['total_bayar'])) ?></td>
          <td class="text-end fw-bold"><?= e(fmt_rp($r['sisa_piutang'])) ?></td>
          <td><span class="badge <?= $r['status']==='lunas' ? 'text-bg-success' : ($r['status']==='sebagian' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($r['status']) ?></span></td>
          <td class="text-end">
            <div class="d-flex gap-2 justify-content-end flex-wrap">
              <?php if ($r['status'] !== 'lunas'): ?>
                <button class="btn btn-sm btn-primary btn-piutang-pay"
                        data-id="<?= e($r['id']) ?>"
                        data-sisa="<?= e((string)$r['sisa_piutang']) ?>"
                        data-sisa-label="<?= e(fmt_rp($r['sisa_piutang'])) ?>"
                        data-referensi="<?= e($r['kode_penjualan']) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#piutangPayModal">Bayar</button>
              <?php endif; ?>
              <?php if (!empty($r['telepon_pelanggan'])): ?>
                <a class="btn btn-sm btn-success" target="_blank" href="<?= e(url('index.php?page=piutang-whatsapp&id=' . $r['id'])) ?>"><i class="bi bi-whatsapp me-1"></i>Tagih</a>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-dark" target="_blank" href="<?= e(url('index.php?page=penjualan-invoice&id=' . $r['penjualan_id'])) ?>"><i class="bi bi-receipt me-1"></i>Invoice</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada data piutang.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="piutangPayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= e(url('index.php?page=piutang-pay')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="modalPiutangId" value="">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-1">Pembayaran Piutang</h5>
            <div class="small text-muted" id="modalPiutangRef">Referensi</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="summary-box mb-3">
            <div class="d-flex justify-content-between"><span>Sisa Tagihan</span><strong id="modalPiutangSisaLabel">Rp 0</strong></div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tanggal Bayar</label>
              <input class="form-control" type="datetime-local" name="tanggal_bayar" value="<?= e(date('Y-m-d\TH:i')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nominal Bayar</label>
              <input class="form-control" type="number" step="0.01" name="nominal_bayar" id="modalPiutangNominal" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Metode</label>
              <select class="form-select" name="metode_pembayaran">
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Keterangan</label>
              <input class="form-control" name="keterangan" placeholder="Contoh: Pembayaran termin 1">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-primary">Simpan Pembayaran</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('.btn-piutang-pay');
  if (!btn) return;
  document.getElementById('modalPiutangId').value = btn.dataset.id || '';
  document.getElementById('modalPiutangRef').textContent = 'Referensi: ' + (btn.dataset.referensi || '-');
  document.getElementById('modalPiutangSisaLabel').textContent = btn.dataset.sisaLabel || 'Rp 0';
  document.getElementById('modalPiutangNominal').value = btn.dataset.sisa || '';
});
</script>
