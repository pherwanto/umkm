<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title mb-1"><?= e($title) ?></h1>
    <div class="text-muted">Role custom dapat dipakai untuk kasir, gudang, supervisor, atau admin terbatas.</div>
  </div>
  <a href="<?= e(url('index.php?page=roles')) ?>" class="btn btn-outline-secondary">Kembali</a>
</div>
<form method="post" class="card p-4">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <?php if (current_role()==='super_admin'): ?>
    <div class="col-md-4">
      <label class="form-label">UMKM</label>
      <select class="form-select" name="umkm_id" <?= $row ? 'disabled' : '' ?> required>
        <option value="">- Pilih UMKM -</option>
        <?php $selectedUmkm = (string)($row['umkm_id'] ?? old('umkm_id')); foreach ($umkms as $u): ?>
          <option value="<?= (int)$u['id'] ?>" <?= $selectedUmkm===(string)$u['id']?'selected':'' ?>><?= e($u['nama_umkm']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if ($row): ?><input type="hidden" name="umkm_id" value="<?= (int)$row['umkm_id'] ?>"><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="col-md-6"><label class="form-label">Nama Role</label><input class="form-control" name="nama_role" required value="<?= e($row['display_name'] ?? old('nama_role')) ?>" placeholder="Contoh: Supervisor Outlet"></div>
    <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="3"><?= e($row['deskripsi'] ?? old('deskripsi')) ?></textarea></div>
  </div>
  <div class="mt-4 alert alert-light border">Setelah role dibuat, atur menu yang boleh diakses melalui halaman <strong>Hak Akses per Menu</strong>.</div>
  <div class="d-flex gap-2"><button class="btn btn-dark">Simpan</button><a href="<?= e(url('index.php?page=roles')) ?>" class="btn btn-outline-secondary">Batal</a></div>
</form>
