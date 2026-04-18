<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack">
  <div><h1 class="page-title h3 mb-1">Kas Manual Masuk/Keluar</h1><div class="text-muted">Catat transaksi kas di luar penjualan, pembelian, piutang, dan hutang.</div></div>
  <a class="btn btn-primary" href="<?= e(url('index.php?page=kas-manual-create')) ?>"><i class="bi bi-plus-circle me-1"></i>Tambah Kas Manual</a>
</div>
<div class="card enterprise-card mb-3"><div class="card-body">
  <form class="filter-grid" method="get" action="<?= e(url('index.php')) ?>">
    <input type="hidden" name="page" value="kas-manual">
    <input class="form-control" name="q" placeholder="Cari keterangan / kategori / user" value="<?= e($filters['q'] ?? '') ?>">
    <select class="form-select" name="jenis"><option value="">Semua Jenis</option><option value="masuk" <?= ($filters['jenis'] ?? '')==='masuk'?'selected':'' ?>>Masuk</option><option value="keluar" <?= ($filters['jenis'] ?? '')==='keluar'?'selected':'' ?>>Keluar</option></select>
    <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
    <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
    <div class="mobile-stack"><button class="btn btn-primary">Filter</button><a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=kas-manual')) ?>">Reset</a></div>
  </form>
</div></div>
<div class="card enterprise-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead><tr><th width="60">No</th><th>Tanggal</th><th>Kategori</th><th>Jenis</th><th>Nominal</th><th>Keterangan</th><th>Input Oleh</th><th width="170">Aksi</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada data kas manual.</td></tr><?php else: foreach($rows as $i=>$row): ?><tr>
<td><?= $i+1 ?></td><td><?= e(date('d-m-Y H:i', strtotime($row['tanggal']))) ?></td><td><?= e($row['nama_kategori']) ?></td><td><span class="badge <?= $row['jenis']==='masuk' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= e(ucfirst($row['jenis'])) ?></span></td><td class="fw-semibold"><?= e(fmt_rp($row['nominal'])) ?></td><td><?= e($row['keterangan'] ?? '-') ?></td><td><?= e($row['user_nama'] ?? '-') ?></td>
<td><div class="d-flex gap-2 flex-wrap"><a class="btn btn-outline-primary btn-sm" href="<?= e(url('index.php?page=kas-manual-edit&id='.(int)$row['id'])) ?>">Edit</a><form method="post" action="<?= e(url('index.php?page=kas-manual-delete')) ?>" onsubmit="return confirm('Hapus transaksi kas manual ini?')"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-outline-danger btn-sm">Hapus</button></form></div></td>
</tr><?php endforeach; endif; ?>
</tbody></table></div></div></div>
