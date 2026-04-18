<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack"><h1 class="page-title h3 mb-0"><?= e($title) ?></h1><a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=kas-manual')) ?>">Kembali</a></div>
<div class="card enterprise-card"><div class="card-body p-4">
<form method="post">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Tanggal</label><input type="datetime-local" class="form-control" name="tanggal" value="<?= e($row['tanggal'] ?? date('Y-m-d\TH:i')) ?>" required></div>
    <div class="col-md-3"><label class="form-label">Jenis</label><select class="form-select" name="jenis" id="jenis-kas"><option value="masuk" <?= ($row['jenis'] ?? 'masuk')==='masuk'?'selected':'' ?>>Kas Masuk</option><option value="keluar" <?= ($row['jenis'] ?? '')==='keluar'?'selected':'' ?>>Kas Keluar</option></select></div>
    <div class="col-md-5"><label class="form-label">Kategori Kas</label><select class="form-select" name="kategori_kas_id" id="kategori-kas-select" required><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" data-jenis="<?= e($cat['jenis']) ?>" <?= (int)($row['kategori_kas_id'] ?? 0)===(int)$cat['id']?'selected':'' ?>><?= e($cat['nama_kategori']) ?> (<?= e($cat['jenis']) ?>)</option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Nominal</label><input type="number" min="0" step="0.01" class="form-control" name="nominal" value="<?= e($row['nominal'] ?? '') ?>" required></div>
    <div class="col-md-8"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" value="<?= e($row['keterangan'] ?? '') ?>"></div>
    <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
  </div>
</form>
</div></div>
<script>
(function(){
  const jenis=document.getElementById('jenis-kas');
  const kategori=document.getElementById('kategori-kas-select');
  function filterKategori(){
    const val=jenis.value;
    let firstVisible=null;
    [...kategori.options].forEach(opt=>{
      const show=opt.dataset.jenis===val;
      opt.hidden=!show;
      if(show && !firstVisible) firstVisible=opt;
    });
    if(kategori.selectedOptions.length===0 || kategori.selectedOptions[0].hidden){ if(firstVisible) kategori.value=firstVisible.value; }
  }
  jenis.addEventListener('change', filterKategori); filterKategori();
})();
</script>
