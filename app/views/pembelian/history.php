<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h1 class="page-title h3 mb-1">Riwayat Perubahan Pembelian</h1>
    <div class="text-muted">Telusuri perubahan, pembayaran hutang, dan penghapusan transaksi pembelian.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian')) ?>">Kembali</a>
</div>

<form class="enterprise-card p-3 mb-3">
  <input type="hidden" name="page" value="pembelian-history">
  <div class="filter-grid">
    <div>
      <label class="form-label">Cari cepat</label>
      <input class="form-control" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Aktivitas / keterangan / petugas">
    </div>
    <div>
      <label class="form-label">Dari Tanggal</label>
      <input class="form-control" type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
    </div>
    <div>
      <label class="form-label">Sampai Tanggal</label>
      <input class="form-control" type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
    </div>
  </div>
  <div class="mobile-stack mt-3">
    <button class="btn btn-dark">Filter</button>
    <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian-history')) ?>">Reset</a>
  </div>
</form>

<div class="enterprise-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Waktu</th><th>Aktivitas</th><th>Petugas</th><th>Keterangan</th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= e(date('d-m-Y H:i', strtotime($r['created_at']))) ?></td>
          <td><span class="fw-semibold"><?= e($r['aktivitas']) ?></span></td>
          <td><?= e($r['user_nama']) ?></td>
          <td style="white-space:pre-line"><?= e($r['keterangan']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="4" class="text-center text-muted py-5">Belum ada riwayat perubahan pembelian.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
