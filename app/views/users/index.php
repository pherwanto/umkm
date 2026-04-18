<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack">
  <div>
    <h1 class="page-title h3 mb-1">User UMKM</h1>
    <div class="text-muted">Kelola akun pengguna per UMKM dan tetapkan rolenya.</div>
  </div>
  <a class="btn btn-primary" href="<?= e(url('index.php?page=users-create')) ?>"><i class="bi bi-person-plus me-1"></i>Tambah User</a>
</div>
<div class="card enterprise-card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead><tr><th width="60">No</th><th>Nama</th><th>Username</th><th>Role</th><th>UMKM</th><th>Email</th><th>Status</th><th width="180">Aksi</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada user.</td></tr><?php else: foreach($rows as $i=>$row): ?>
<tr>
  <td><?= $i+1 ?></td>
  <td class="fw-semibold"><?= e($row['nama']) ?></td>
  <td><?= e($row['username']) ?></td>
  <td><span class="badge rounded-pill text-bg-light"><?= e($row['display_role'] ?? strtoupper(str_replace('_',' ',$row['nama_role']))) ?></span></td>
  <td><?= e($row['nama_umkm'] ?? '-') ?></td>
  <td><?= e($row['email'] ?? '-') ?></td>
  <td><span class="badge <?= ($row['status'] ?? 'aktif') === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e($row['status']) ?></span></td>
  <td>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-primary btn-sm" href="<?= e(url('index.php?page=users-edit&id='.(int)$row['id'])) ?>">Edit</a>
      <form method="post" action="<?= e(url('index.php?page=users-delete')) ?>" onsubmit="return confirm('Hapus user ini?')">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
        <button class="btn btn-outline-danger btn-sm">Hapus</button>
      </form>
    </div>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div></div></div>
