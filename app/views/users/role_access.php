<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack">
  <div>
    <h1 class="page-title h3 mb-1">Hak Akses per Menu</h1>
    <div class="text-muted">Atur menu yang boleh diakses setiap role pada UMKM terkait.</div>
  </div>
</div>
<?php if (current_role() === 'super_admin'): ?>
<div class="card enterprise-card mb-3"><div class="card-body">
  <form class="row g-3 align-items-end" method="get" action="<?= e(url('index.php')) ?>">
    <input type="hidden" name="page" value="role-access">
    <div class="col-md-6"><label class="form-label">Pilih UMKM</label><select name="umkm_id" class="form-select"><?php foreach($umkms as $u): ?><option value="<?= (int)$u['id'] ?>" <?= (int)$umkmId === (int)$u['id'] ? 'selected' : '' ?>><?= e($u['nama_umkm']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><button class="btn btn-outline-primary">Tampilkan</button></div>
  </form>
</div></div>
<?php endif; ?>
<div class="row g-3">
<?php foreach($roles as $role): if($role['nama_role']==='super_admin') continue; ?>
  <div class="col-12 col-xl-6">
    <div class="card enterprise-card h-100"><div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div><h2 class="h5 mb-1"><?= e($role['display_name'] ?? strtoupper(str_replace('_',' ',$role['nama_role']))) ?></h2><div class="text-muted small"><?= e($role['deskripsi'] ?? '') ?></div></div>
      </div>
      <form method="post">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="umkm_id" value="<?= (int)$umkmId ?>">
        <input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
        <div class="row g-2">
          <?php foreach($menuLabels as $key => $label): if($key==='hak_akses' && $role['nama_role']==='operator') continue; ?>
            <div class="col-md-6">
              <label class="form-check border rounded-4 px-3 py-2 w-100 bg-light-subtle">
                <input class="form-check-input me-2" type="checkbox" name="permissions[<?= e($key) ?>]" value="1" <?= !empty($permissions[(int)$role['id']][$key]) ? 'checked' : '' ?>>
                <span class="form-check-label"><?= e($label) ?></span>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-3"><button class="btn btn-primary">Simpan Hak Akses</button></div>
      </form>
    </div></div>
  </div>
<?php endforeach; ?>
</div>
