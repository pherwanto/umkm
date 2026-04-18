<h1 class="h3 mb-3"><?= e($title) ?></h1><div class="card shadow-sm border-0"><div class="card-body"><form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nama</label><input class="form-control" name="nama" value="<?= e($row['nama_pelanggan'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">Telepon</label><input class="form-control" name="telepon" value="<?= e($row['telepon'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Jenis</label><input class="form-control" name="jenis" value="<?= e($row['jenis_pelanggan'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Alamat</label><input class="form-control" name="alamat" value="<?= e($row['alamat'] ?? '') ?>"></div>
<div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="catatan" rows="3"><?= e($row['catatan'] ?? '') ?></textarea></div>
</div><div class="mt-3"><button class="btn btn-primary">Simpan</button> <a class="btn btn-secondary" href="<?= e(url('index.php?page=pelanggan')) ?>">Kembali</a></div></form></div></div>
