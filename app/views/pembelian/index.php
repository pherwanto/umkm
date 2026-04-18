<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title h3 mb-1">Pembelian</h1>
    <div class="text-muted">Catat pembelian, filter supplier dan tanggal, lalu cetak atau kelola ulang transaksi dengan aman.</div>
  </div>
  <a class="btn btn-primary" href="<?= e(url('index.php?page=pembelian-create')) ?>"><i class="bi bi-plus-circle me-1"></i>Tambah Pembelian</a>
</div>

<form class="enterprise-card p-3 mb-3">
  <input type="hidden" name="page" value="pembelian">
  <div class="filter-grid">
    <div>
      <label class="form-label">Cari cepat</label>
      <input class="form-control" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Kode pembelian / supplier / petugas">
    </div>
    <div>
      <label class="form-label">Supplier</label>
      <select class="form-select" name="supplier_id">
        <option value="">Semua supplier</option>
        <?php foreach ($supplierOptions as $s): ?>
          <option value="<?= e($s['id']) ?>" <?= (string)($filters['supplier_id'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['nama_supplier']) ?></option>
        <?php endforeach; ?>
      </select>
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
    <button class="btn btn-dark"><i class="bi bi-funnel me-1"></i>Filter</button>
    <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian')) ?>">Reset</a>
  </div>
</form>

<div class="enterprise-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
      <tr>
        <th>Kode</th>
        <th>Tanggal</th>
        <th>Supplier</th>
        <th class="text-end">Total</th>
        <th class="text-end">Dibayar</th>
        <th class="text-end">Sisa</th>
        <th>Status</th>
        <th class="text-end">Aksi</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td class="fw-semibold"><?= e($r['kode_pembelian']) ?></td>
          <td><?= e(date('d-m-Y H:i', strtotime($r['tanggal']))) ?></td>
          <td><?= e($r['nama_supplier'] ?? '-') ?></td>
          <td class="text-end"><?= e(fmt_rp($r['total'])) ?></td>
          <td class="text-end"><?= e(fmt_rp($r['dibayar'])) ?></td>
          <td class="text-end"><?= e(fmt_rp($r['sisa'])) ?></td>
          <td><span class="badge <?= $r['status_pembayaran']==='lunas' ? 'text-bg-success' : ($r['status_pembayaran']==='sebagian' ? 'text-bg-warning' : 'text-bg-secondary') ?>"><?= e($r['status_pembayaran']) ?></span></td>
          <td class="text-end">
            <div class="d-flex gap-2 justify-content-end flex-wrap">
              <a class="btn btn-outline-dark btn-sm" href="<?= e(url('index.php?page=pembelian-show&id=' . $r['id'])) ?>"><i class="bi bi-eye"></i> Detail</a>
              <a class="btn btn-outline-primary btn-sm" href="<?= e(url('index.php?page=pembelian-edit&id=' . $r['id'])) ?>"><i class="bi bi-pencil"></i> Edit</a>
              <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('index.php?page=pembelian-history&q=' . rawurlencode($r['kode_pembelian']))) ?>"><i class="bi bi-clock-history"></i> Riwayat</a>
              <a class="btn btn-outline-dark btn-sm" target="_blank" href="<?= e(url('index.php?page=pembelian-print&id=' . $r['id'])) ?>"><i class="bi bi-printer"></i> Print</a>
              <form method="post" action="<?= e(url('index.php?page=pembelian-delete')) ?>" onsubmit="return confirm('Hapus transaksi pembelian ini? Stok, kas, dan hutang terkait akan di-rollback.');" class="d-inline">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-5">Belum ada transaksi pembelian.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
