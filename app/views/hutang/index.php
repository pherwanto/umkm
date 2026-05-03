<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="page-title h3 mb-1">Hutang</h1>
    <div class="text-muted">Pantau hutang pembelian, catat pembayaran, dan telusuri pembelian terkait dengan lebih rapi.</div>
  </div>
</div>

<div class="enterprise-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Referensi</th>
          <th>Supplier</th>
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
          <td class="fw-semibold"><?= e($r['kode_pembelian']) ?></td>
          <td>
            <div class="fw-semibold"><?= e($r['nama_supplier'] ?? '-') ?></div>
            <div class="small text-muted"><?= e($r['telepon_supplier'] ?? '-') ?></div>
          </td>
          <td class="text-end"><?= e(fmt_rp($r['total_hutang'])) ?></td>
          <td class="text-end"><?= e(fmt_rp($r['total_bayar'])) ?></td>
          <td class="text-end fw-bold"><?= e(fmt_rp($r['sisa_hutang'])) ?></td>
          <td><span class="badge <?= $r['status']==='lunas' ? 'text-bg-success' : ($r['status']==='sebagian' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($r['status']) ?></span></td>
          <td class="text-end">
            <div class="d-flex gap-2 justify-content-end flex-wrap">
              <?php if($r['status'] !== 'lunas'): ?>
                <button class="btn btn-sm btn-primary btn-hutang-pay"
                        data-id="<?= e($r['id']) ?>"
                        data-sisa="<?= e((string)$r['sisa_hutang']) ?>"
                        data-sisa-label="<?= e(fmt_rp($r['sisa_hutang'])) ?>"
                        data-referensi="<?= e($r['kode_pembelian']) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#hutangPayModal">Bayar</button>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-dark" href="<?= e(url('index.php?page=pembelian-show&id=' . $r['pembelian_id'])) ?>"><i class="bi bi-eye me-1"></i>Detail</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">Belum ada data hutang.</td></tr>
      <?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="table-light fw-semibold">
          <td colspan="2">Summary Halaman Aktif</td>
          <td class="text-end"><?= e(fmt_rp((int)($summary['total_hutang'] ?? 0))) ?></td>
          <td class="text-end"><?= e(fmt_rp((int)($summary['total_bayar'] ?? 0))) ?></td>
          <td class="text-end"><?= e(fmt_rp((int)($summary['sisa_hutang'] ?? 0))) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="modal fade" id="hutangPayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <form method="post" action="<?= e(url('index.php?page=hutang-pay')) ?>">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="modalHutangId" value="">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-1">Pembayaran Hutang</h5>
            <div class="small text-muted" id="modalHutangRef">Referensi</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="summary-box mb-3">
            <div class="d-flex justify-content-between"><span>Sisa Hutang</span><strong id="modalHutangSisaLabel">Rp 0</strong></div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tanggal Bayar</label>
              <input class="form-control" type="datetime-local" name="tanggal_bayar" value="<?= e(date('Y-m-d\TH:i')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nominal Bayar</label>
              <input class="form-control" type="number" step="0.01" name="nominal_bayar" id="modalHutangNominal" required>
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
  const btn = e.target.closest('.btn-hutang-pay');
  if (!btn) return;
  document.getElementById('modalHutangId').value = btn.dataset.id || '';
  document.getElementById('modalHutangRef').textContent = 'Referensi: ' + (btn.dataset.referensi || '-');
  document.getElementById('modalHutangSisaLabel').textContent = btn.dataset.sisaLabel || 'Rp 0';
  document.getElementById('modalHutangNominal').value = btn.dataset.sisa || '';
});
</script>
