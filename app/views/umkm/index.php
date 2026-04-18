<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title mb-1">Manajemen UMKM</h1>
    <div class="text-muted">Kelola tenant UMKM beserta identitas usahanya.</div>
  </div>
  <a href="<?= e(url('index.php?page=umkm-create')) ?>" class="btn btn-dark"><i class="bi bi-plus-lg me-1"></i>Tambah UMKM</a>
</div>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Kode</th><th>Nama UMKM</th><th>Pemilik</th><th>Kontak</th><th>Jenis Usaha</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="fw-semibold"><?= e($r['kode_umkm']) ?></td>
          <td><div class="fw-semibold"><?= e($r['nama_umkm']) ?></div><div class="text-muted small"><?= e($r['alamat'] ?? '-') ?></div></td>
          <td><?= e($r['nama_pemilik'] ?? '-') ?></td>
          <td><div><?= e($r['telepon'] ?? '-') ?></div><div class="text-muted small"><?= e($r['email'] ?? '-') ?></div></td>
          <td><?= e($r['jenis_usaha'] ?? '-') ?></td>
          <td><span class="badge <?= $r['status']==='aktif' ? 'text-bg-success':'text-bg-secondary' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('index.php?page=umkm-edit&id='.(int)$r['id'])) ?>">Edit</a>
            <form method="post" action="<?= e(url('index.php?page=umkm-delete')) ?>" class="d-inline" onsubmit="return confirm('Hapus UMKM ini? Pastikan sudah tidak ada user terkait.');">
              <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada data UMKM.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
