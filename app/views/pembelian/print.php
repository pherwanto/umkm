<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f4f6f8;margin:0;padding:24px;color:#111} .sheet{max-width:980px;margin:0 auto;background:#fff;padding:28px;border:1px solid #d9dee5;border-radius:18px} .muted{color:#64748b} table{width:100%;border-collapse:collapse;margin-top:16px} th,td{border:1px solid #dbe2ea;padding:10px 12px;font-size:14px} th{background:#f8fafc;text-align:left} .text-end{text-align:right} .head{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap} .sign{display:flex;justify-content:flex-end;margin-top:64px} .sign-box{width:240px;text-align:center}.line{border-top:1px solid #111;margin-top:70px;padding-top:8px}
@media print{body{background:#fff;padding:0}.sheet{max-width:none;margin:0;border:0;border-radius:0;padding:0} .no-print{display:none}}
</style>
</head>
<body onload="window.print()">
<div class="sheet">
  <div class="no-print" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap">
    <a href="<?= e(url('index.php?page=pembelian-create')) ?>" style="display:inline-flex;align-items:center;gap:8px;border:1px solid #0d6efd;color:#0d6efd;text-decoration:none;padding:8px 12px;border-radius:10px;font-weight:700">Tambah Pembelian Lagi</a>
    <a href="<?= e(url('index.php?page=pembelian')) ?>" style="display:inline-flex;align-items:center;gap:8px;border:1px solid #9ca3af;color:#374151;text-decoration:none;padding:8px 12px;border-radius:10px;font-weight:700">Kembali ke Daftar</a>
  </div>
  <div class="head">
    <div>
      <h2 style="margin:0 0 6px">Bukti Pembelian</h2>
      <div class="muted"><?= e($row['nama_umkm']) ?></div>
      <div class="muted"><?= e($row['alamat_umkm'] ?? '-') ?> · <?= e($row['telp_umkm'] ?? '-') ?></div>
    </div>
    <div>
      <table style="margin:0;border:none"><tr><td style="border:none;padding:2px 8px">No</td><td style="border:none;padding:2px 8px">:</td><td style="border:none;padding:2px 8px"><?= e($row['kode_pembelian']) ?></td></tr><tr><td style="border:none;padding:2px 8px">Tanggal</td><td style="border:none;padding:2px 8px">:</td><td style="border:none;padding:2px 8px"><?= e(date('d-m-Y', strtotime($row['tanggal']))) ?></td></tr><tr><td style="border:none;padding:2px 8px">Petugas</td><td style="border:none;padding:2px 8px">:</td><td style="border:none;padding:2px 8px"><?= e($row['user_nama']) ?></td></tr></table>
    </div>
  </div>

  <div style="margin-top:18px">
    <strong>Supplier:</strong> <?= e($row['nama_supplier'] ?? '-') ?><br>
    <span class="muted"><?= e($row['alamat_supplier'] ?? '-') ?><?= !empty($row['telepon_supplier']) ? ' · ' . e($row['telepon_supplier']) : '' ?></span>
  </div>

  <table>
    <thead><tr><th style="width:60px">No</th><th>Produk</th><th class="text-end">Harga Beli</th><th class="text-end">Qty</th><th class="text-end">Jumlah</th></tr></thead>
    <tbody>
      <?php foreach ($row['items'] as $i => $item): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= e($item['nama_produk']) ?></td>
          <td class="text-end"><?= e(fmt_rp($item['harga'])) ?></td>
          <td class="text-end"><?= e(number_format($item['qty'], 0, ',', '.')) ?></td>
          <td class="text-end"><?= e(fmt_rp($item['subtotal'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><th colspan="4" class="text-end">Subtotal</th><th class="text-end"><?= e(fmt_rp($row['subtotal'])) ?></th></tr>
      <tr><th colspan="4" class="text-end">Diskon</th><th class="text-end"><?= e(fmt_rp($row['diskon'])) ?></th></tr>
      <tr><th colspan="4" class="text-end">Total</th><th class="text-end"><?= e(fmt_rp($row['total'])) ?></th></tr>
      <tr><th colspan="4" class="text-end">Dibayar</th><th class="text-end"><?= e(fmt_rp($row['dibayar'])) ?></th></tr>
      <tr><th colspan="4" class="text-end">Sisa / Hutang</th><th class="text-end"><?= e(fmt_rp($row['sisa'])) ?></th></tr>
    </tfoot>
  </table>

  <div class="sign">
    <div class="sign-box">
      <div><?= e(date('d-m-Y')) ?></div>
      <div class="line">Petugas / Penerima</div>
    </div>
  </div>
</div>
</body>
</html>
