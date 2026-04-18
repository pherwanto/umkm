<?php require_once __DIR__ . '/../../core/View.php'; require_once __DIR__ . '/../../core/helpers.php'; ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice <?= e($row['kode_penjualan']) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0}.wrap{max-width:920px;margin:20px auto;background:#fff;padding:30px;border-radius:16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:8px} .text-end{text-align:right}.text-center{text-align:center}.meta td{border:none;padding:3px 0}.muted{color:#555}.toolbar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.btn{display:inline-flex;align-items:center;gap:8px;border:1px solid #ccc;border-radius:10px;padding:10px 14px;text-decoration:none;color:#111;background:#fff;font-weight:700}.btn-dark{background:#111;color:#fff;border-color:#111}.btn-success{background:#22c55e;color:#fff;border-color:#22c55e}.btn-outline{background:#fff}.signatures{margin-top:60px;display:flex;justify-content:space-between;gap:16px}.sign-box{width:260px;text-align:center}.signature-line{margin-top:70px;border-top:1px solid #111;padding-top:8px}@media print{body{background:#fff}.no-print{display:none}.wrap{margin:0;max-width:none;border-radius:0;padding:0}}
</style>
</head>
<body>
<?php $returnUrl = $_GET['return'] ?? url('index.php?page=penjualan'); ?>
<div class="wrap">
  <div class="toolbar no-print">
    <button class="btn btn-dark" onclick="window.print()">Cetak</button>
    <?php if (!empty($row['telepon_pelanggan'])): ?>
      <a class="btn btn-success" target="_blank" href="<?= e(url('index.php?page=penjualan-whatsapp&id=' . $row['id'])) ?>">Kirim via WhatsApp</a>
    <?php endif; ?>
    <a class="btn btn-outline" href="<?= e($returnUrl) ?>">Transaksi Baru</a>
  </div>
  <h2 style="margin-bottom:6px">INVOICE / NOTA PENJUALAN</h2>
  <div class="muted" style="margin-bottom:18px">Dokumen transaksi penjualan UMKM</div>
  <table class="meta">
    <tr>
      <td style="width:55%"><strong><?= e($row['nama_umkm']) ?></strong><br><?= e($row['alamat_umkm']) ?><br>Telp: <?= e($row['telp_umkm']) ?></td>
      <td><strong>No:</strong> <?= e($row['kode_penjualan']) ?><br><strong>Tanggal:</strong> <?= e(date('d-m-Y', strtotime($row['tanggal']))) ?><br><strong>Pelanggan:</strong> <?= e($row['nama_pelanggan'] ?: 'Umum') ?></td>
    </tr>
  </table>
  <br>
  <table>
    <thead><tr><th width="50">No</th><th>Kode</th><th>Produk</th><th class="text-end">QTT</th><th class="text-end">Harga Jual</th><th class="text-end">Jumlah</th></tr></thead>
    <tbody>
      <?php foreach($row['items'] as $i => $it): ?>
      <tr>
        <td class="text-center"><?= $i + 1 ?></td>
        <td><?= e($it['kode_produk']) ?></td>
        <td><?= e($it['nama_produk']) ?></td>
        <td class="text-end"><?= e(number_format((float)$it['qty'], 0, ',', '.')) ?></td>
        <td class="text-end"><?= e(fmt_rp($it['harga'])) ?></td>
        <td class="text-end"><?= e(fmt_rp($it['subtotal'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <br>
  <table>
    <tr><td><strong>Subtotal</strong></td><td class="text-end" style="width:240px"><?= e(fmt_rp($row['subtotal'])) ?></td></tr>
    <tr><td><strong>Diskon</strong></td><td class="text-end"><?= e(fmt_rp($row['diskon'])) ?></td></tr>
    <?php if (!empty($row['is_pajak_enabled']) && (float)($row['pajak_nominal'] ?? 0) > 0): ?>
      <tr><td><strong>Pajak (<?= (int)($row['pajak_persen'] ?? 0) ?>%)</strong></td><td class="text-end"><?= e(fmt_rp($row['pajak_nominal'])) ?></td></tr>
    <?php endif; ?>
    <tr><td><strong>Total</strong></td><td class="text-end"><?= e(fmt_rp($row['total'])) ?></td></tr>
    <tr><td><strong>Dibayar</strong></td><td class="text-end"><?= e(fmt_rp($row['dibayar'])) ?></td></tr>
    <tr><td><strong>Sisa</strong></td><td class="text-end"><?= e(fmt_rp($row['sisa'])) ?></td></tr>
  </table>

  <div class="signatures">
    <div class="sign-box">
      <div>Penerima,</div>
      <div class="signature-line">&nbsp;</div>
    </div>
    <div class="sign-box">
      <div>Petugas,</div>
      <div class="signature-line"><?= e($row['user_nama']) ?><div class="muted">Tanda tangan / cap</div></div>
    </div>
  </div>
</div>
<script>
(function(){
  const returnUrl = <?= json_encode($returnUrl) ?>;
  const autoPrint = <?= isset($_GET['autoprint']) ? 'true' : 'false' ?>;
  if (autoPrint) {
    setTimeout(() => {
      try { window.print(); } catch(e) {}
    }, 350);
  }
  window.addEventListener('afterprint', function(){
    if (returnUrl) window.location.href = returnUrl;
  });
})();
</script>
</body>
</html>
