<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title mb-1"><?= e($title) ?></h1>
    <div class="text-muted">Lengkapi identitas UMKM binaan atau tenant baru.</div>
  </div>
  <a href="<?= e(url('index.php?page=umkm')) ?>" class="btn btn-outline-secondary">Kembali</a>
</div>
<form method="post" class="card p-4">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Kode UMKM</label><input class="form-control" name="kode_umkm" required value="<?= e($row['kode_umkm'] ?? old('kode_umkm')) ?>"></div>
    <div class="col-md-5"><label class="form-label">Nama UMKM</label><input class="form-control" name="nama_umkm" required value="<?= e($row['nama_umkm'] ?? old('nama_umkm')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Nama Pemilik</label><input class="form-control" name="nama_pemilik" value="<?= e($row['nama_pemilik'] ?? old('nama_pemilik')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Telepon</label><input class="form-control" name="telepon" value="<?= e($row['telepon'] ?? old('telepon')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($row['email'] ?? old('email')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Jenis Usaha</label><input class="form-control" name="jenis_usaha" value="<?= e($row['jenis_usaha'] ?? old('jenis_usaha')) ?>"></div>
    <div class="col-12"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat" rows="2"><?= e($row['alamat'] ?? old('alamat')) ?></textarea></div>
    <div class="col-12"><label class="form-label">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="3"><?= e($row['deskripsi'] ?? old('deskripsi')) ?></textarea></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php $st = $row['status'] ?? old('status','aktif'); ?>
        <option value="aktif" <?= $st==='aktif'?'selected':'' ?>>Aktif</option>
        <option value="nonaktif" <?= $st==='nonaktif'?'selected':'' ?>>Nonaktif</option>
      </select>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2"><button class="btn btn-dark">Simpan</button><a href="<?= e(url('index.php?page=umkm')) ?>" class="btn btn-outline-secondary">Batal</a></div>
</form>
