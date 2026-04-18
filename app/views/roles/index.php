<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title mb-1">Role Khusus / Custom Role</h1>
    <div class="text-muted">Tambahkan role baru per UMKM untuk kebutuhan akses yang lebih fleksibel.</div>
  </div>
  <a href="<?= e(url('index.php?page=roles-create')) ?>" class="btn btn-dark"><i class="bi bi-plus-lg me-1"></i>Tambah Role</a>
</div>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Nama Tampilan</th><th>Key Role</th><th>UMKM</th><th>Tipe</th><th>Deskripsi</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="fw-semibold"><?= e($r['display_name'] ?? ucwords(str_replace('_',' ',$r['nama_role']))) ?></td>
          <td><code><?= e($r['nama_role']) ?></code></td>
          <td><?= e($r['nama_umkm'] ?? 'Global') ?></td>
          <td><span class="badge <?= !empty($r['is_system']) ? 'text-bg-secondary' : 'text-bg-primary' ?>"><?= !empty($r['is_system']) ? 'System' : 'Custom' ?></span></td>
          <td><?= e($r['deskripsi'] ?? '-') ?></td>
          <td class="text-end">
            <?php if (empty($r['is_system'])): ?>
              <a class="btn btn-sm btn-outline-primary" href="<?= e(url('index.php?page=roles-edit&id='.(int)$r['id'])) ?>">Edit</a>
              <form method="post" action="<?= e(url('index.php?page=roles-delete')) ?>" class="d-inline" onsubmit="return confirm('Hapus role custom ini?');">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            <?php else: ?>
              <span class="text-muted small">Role sistem</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada role custom.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
