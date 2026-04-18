<?php require_once __DIR__ . '/../../core/View.php'; $user=current_user(); ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? $appName) ?> - <?= e($appName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--bg:#f3f6fb;--card:#fff;--line:#e5e7eb;--text:#0f172a;--muted:#64748b;--accent:#111827;--soft:#eef2ff;--primary:#111827}
html,body{min-height:100%}body{background:linear-gradient(180deg,#f8fafc 0%,#eef3f8 100%);color:var(--text);font-size:.95rem}.enterprise-card,.card{background:rgba(255,255,255,.94);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.85);border-radius:22px;box-shadow:0 14px 40px rgba(15,23,42,.08)}.brand-badge{width:70px;height:70px;border-radius:18px;background:linear-gradient(135deg,#111827,#374151);color:#fff;display:grid;place-items:center;font-weight:800;letter-spacing:.08em}.navbar-enterprise{background:rgba(17,24,39,.92)!important;backdrop-filter:blur(10px);box-shadow:0 10px 30px rgba(15,23,42,.18)}.navbar .nav-link{border-radius:12px;padding:.6rem .9rem}.navbar .nav-link.active,.navbar .nav-link:hover{background:rgba(255,255,255,.11)}.table> :not(caption)>*>*{vertical-align:middle}.table thead th{background:#f8fafc;color:#334155;white-space:nowrap}.page-shell{padding-top:1.25rem;padding-bottom:2rem}.page-title{font-weight:800;letter-spacing:-.02em}.stat-card{padding:1rem 1.1rem}.stat-label{font-size:.82rem;color:var(--muted)}.stat-value{font-size:1.2rem;font-weight:800}.badge-soft{background:var(--soft);color:#3730a3}.product-search-results{position:absolute;z-index:1050;left:0;right:0;top:100%;max-height:260px;overflow:auto;border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.12)}.product-search-wrap{position:relative}.summary-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:1rem}.action-sticky{position:sticky;top:1rem}.sales-form-grid{display:grid;grid-template-columns:1.7fr 1fr;gap:1rem}.invoice-wrap{max-width:1000px;margin:20px auto;background:#fff;padding:28px;border-radius:24px;border:1px solid #e2e8f0}.invoice-sign{margin-top:60px;display:flex;justify-content:flex-end}.invoice-sign-box{width:260px;text-align:center}.signature-line{margin-top:70px;border-top:1px solid #111;padding-top:8px}.product-thumb{width:48px;height:48px;border-radius:12px;object-fit:cover;background:#f1f5f9;border:1px solid #e5e7eb}.auth-card{overflow:hidden}.table-responsive{border-radius:18px}input.form-control,select.form-select,textarea.form-control{border-radius:14px;padding:.72rem .95rem;border-color:#dbe2ea}.btn{border-radius:14px;padding:.62rem 1rem;font-weight:600}.btn-sm{border-radius:12px}.filter-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem}.mobile-stack{display:flex;gap:.5rem;flex-wrap:wrap}.section-title{font-size:1rem;font-weight:800;letter-spacing:-.01em}.dropdown-menu{border-radius:18px;padding:.65rem}.dropdown-item{border-radius:12px;padding:.55rem .8rem}.menu-section-title{font-size:.7rem;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;padding:.35rem .8rem}
@media (max-width:1199px){.sales-form-grid{grid-template-columns:1fr}.action-sticky{position:static}}
@media (max-width:767px){.page-shell{padding-top:1rem}.filter-grid{grid-template-columns:1fr 1fr}.invoice-wrap{padding:18px}.hide-mobile{display:none}}
@media (max-width:575px){.filter-grid{grid-template-columns:1fr}.navbar-brand span{display:none}}
</style>
</head>
<body>
<?php if ($user): ?>
<nav class="navbar navbar-expand-xl navbar-dark navbar-enterprise sticky-top">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= e(url('index.php?page=dashboard')) ?>"><span class="brand-badge" style="width:40px;height:40px;border-radius:12px;font-size:.8rem">UM</span><span>UMKM Enterprise</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto gap-1 my-3 my-xl-0">
        <?php if (can_menu('dashboard')): ?>
        <li class="nav-item"><a class="nav-link <?= nav_active(['dashboard']) ?>" href="<?= e(url('index.php?page=dashboard')) ?>"><i class="bi bi-grid-1x2 me-1"></i>Dashboard</a></li>
        <?php endif; ?>
        <?php if (can_menu('umkm')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['umkm','umkm-create','umkm-edit']) ?>" href="<?= e(url('index.php?page=umkm')) ?>"><i class="bi bi-buildings me-1"></i>UMKM</a></li><?php endif; ?>
        <?php if (can_menu('produk')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['produk','produk-create','produk-edit']) ?>" href="<?= e(url('index.php?page=produk')) ?>"><i class="bi bi-box-seam me-1"></i>Produk</a></li><?php endif; ?>
        <?php if (can_menu('pelanggan')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['pelanggan','pelanggan-create','pelanggan-edit']) ?>" href="<?= e(url('index.php?page=pelanggan')) ?>"><i class="bi bi-people me-1"></i>Pelanggan</a></li><?php endif; ?>
        <?php if (can_menu('supplier')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['supplier','supplier-create','supplier-edit']) ?>" href="<?= e(url('index.php?page=supplier')) ?>"><i class="bi bi-truck me-1"></i>Supplier</a></li><?php endif; ?>
        <?php if (can_menu('penjualan')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['penjualan','penjualan-create','penjualan-edit','penjualan-show','penjualan-history']) ?>" href="<?= e(url('index.php?page=penjualan')) ?>"><i class="bi bi-receipt-cutoff me-1"></i>Penjualan</a></li><?php endif; ?>
        <?php if (can_menu('pembelian')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['pembelian','pembelian-create']) ?>" href="<?= e(url('index.php?page=pembelian')) ?>"><i class="bi bi-bag-check me-1"></i>Pembelian</a></li><?php endif; ?>
        <?php if (can_menu('piutang')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['piutang']) ?>" href="<?= e(url('index.php?page=piutang')) ?>"><i class="bi bi-wallet2 me-1"></i>Piutang</a></li><?php endif; ?>
        <?php if (can_menu('hutang')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['hutang']) ?>" href="<?= e(url('index.php?page=hutang')) ?>"><i class="bi bi-cash-coin me-1"></i>Hutang</a></li><?php endif; ?>
        <?php if (can_menu('kas_manual')): ?><li class="nav-item"><a class="nav-link <?= nav_active(['kas-manual','kas-manual-create','kas-manual-edit']) ?>" href="<?= e(url('index.php?page=kas-manual')) ?>"><i class="bi bi-journal-text me-1"></i>Kas Manual</a></li><?php endif; ?>
        <?php if (can_menu('laporan')): ?>
        <li class="nav-item dropdown"><a class="nav-link dropdown-toggle <?= nav_active(['laporan-penjualan','laporan-pembelian','laporan-kas','laporan-laba-rugi']) ?>" data-bs-toggle="dropdown" href="#"><i class="bi bi-bar-chart-line me-1"></i>Laporan</a>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=laporan-penjualan')) ?>">Laporan Penjualan</a></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=laporan-pembelian')) ?>">Laporan Pembelian</a></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=laporan-kas')) ?>">Laporan Kas</a></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=laporan-laba-rugi')) ?>">Laporan Laba Rugi</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if (can_menu('referensi') || can_menu('users') || can_menu('hak_akses') || can_menu('roles')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= nav_active(['referensi-kategori-produk','referensi-kategori-produk-create','referensi-kategori-produk-edit','referensi-satuan','referensi-satuan-create','referensi-satuan-edit','referensi-kategori-kas','referensi-kategori-kas-create','referensi-kategori-kas-edit','users','users-create','users-edit','role-access','roles','roles-create','roles-edit']) ?>" data-bs-toggle="dropdown" href="#"><i class="bi bi-sliders2 me-1"></i>Setting</a>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <?php if (can_menu('referensi')): ?>
            <li><h6 class="dropdown-header">Referensi</h6></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=referensi-kategori-produk')) ?>">Kategori Produk</a></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=referensi-satuan')) ?>">Satuan</a></li>
            <li><a class="dropdown-item" href="<?= e(url('index.php?page=referensi-kategori-kas')) ?>">Kategori Kas</a></li>
            <?php endif; ?>
            <?php if (can_menu('users') || can_menu('hak_akses') || can_menu('roles') || can_menu('umkm')): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
            <?php if (can_menu('users') || can_menu('roles') || can_menu('hak_akses') || can_menu('umkm')): ?>
            <li><h6 class="dropdown-header">User & Akses</h6></li>
            <?php endif; ?>
            <?php if (can_menu('users')): ?><li><a class="dropdown-item" href="<?= e(url('index.php?page=users')) ?>">User UMKM</a></li><?php endif; ?>
            <?php if (can_menu('roles')): ?><li><a class="dropdown-item" href="<?= e(url('index.php?page=roles')) ?>">Role Khusus / Custom Role</a></li><?php endif; ?>
            <?php if (can_menu('hak_akses')): ?><li><a class="dropdown-item" href="<?= e(url('index.php?page=role-access')) ?>">Hak Akses per Menu</a></li><?php endif; ?>
            <?php if (can_menu('umkm')): ?><li><a class="dropdown-item" href="<?= e(url('index.php?page=umkm')) ?>">Manajemen UMKM</a></li><?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge rounded-pill text-bg-light px-3 py-2"><?= e(strtoupper(str_replace('_',' ', current_role()))) ?></span>
        <span class="text-white-50 small"><?= e($user['nama'] ?? $user['username'] ?? 'User') ?> · <?= e($user['nama_umkm'] ?? 'UMKM') ?></span>
        <a class="btn btn-outline-light btn-sm" href="<?= e(url('index.php?page=change-password')) ?>"><i class="bi bi-key"></i></a>
        <a class="btn btn-light btn-sm" href="<?= e(url('index.php?page=logout')) ?>">Logout</a>
      </div>
    </div>
  </div>
</nav>
<div class="container-fluid px-3 px-lg-4 page-shell">
<?php else: ?>
<div class="container-fluid px-3 px-lg-4">
<?php endif; ?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success border-0 enterprise-card"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert alert-danger border-0 enterprise-card"><?= e($msg) ?></div><?php endif; ?>
