<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack"><h1 class="page-title h3 mb-0"><?= e($title) ?></h1><a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=users')) ?>">Kembali</a></div>
<div class="card enterprise-card"><div class="card-body p-4">
<form method="post">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama Lengkap</label><input class="form-control" name="nama" value="<?= e($row['nama'] ?? '') ?>" required></div>
    <div class="col-md-3"><label class="form-label">Username</label><input class="form-control" name="username" value="<?= e($row['username'] ?? '') ?>" required></div>
    <div class="col-md-3"><label class="form-label">Role</label><select class="form-select" name="role_id" required><?php foreach($roles as $role): ?><option value="<?= (int)$role['id'] ?>" <?= (int)($row['role_id'] ?? 0) === (int)$role['id'] ? 'selected' : '' ?>><?= e($role['display_name'] ?? ucwords(str_replace('_',' ', $role['nama_role']))) ?></option><?php endforeach; ?></select></div>
    <?php if (current_role() === 'super_admin'): ?>
    <div class="col-md-6"><label class="form-label">UMKM</label><select class="form-select" name="umkm_id"><option value="">- Khusus Super Admin / Tidak terkait UMKM -</option><?php foreach($umkms as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (string)($row['umkm_id'] ?? '') === (string)$u['id'] ? 'selected' : '' ?>><?= e($u['nama_umkm']) ?></option><?php endforeach; ?></select></div>
    <?php endif; ?>
    <div class="col-md-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($row['email'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Telepon</label><input class="form-control" name="telepon" value="<?= e($row['telepon'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label"><?= $row ? 'Password Baru (opsional)' : 'Password' ?></label><input type="password" class="form-control" name="password" <?= $row ? '' : 'required' ?>></div>
    <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="aktif" <?= ($row['status'] ?? 'aktif')==='aktif'?'selected':'' ?>>Aktif</option><option value="nonaktif" <?= ($row['status'] ?? '')==='nonaktif'?'selected':'' ?>>Nonaktif</option></select></div>
    <div class="col-12"><button class="btn btn-primary">Simpan User</button></div>
  </div>
</form>
</div></div>
