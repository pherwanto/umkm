<?php
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $secure,
    'samesite' => 'Lax',
]);
session_start();
$app = require __DIR__ . '/../app/config/app.php';
date_default_timezone_set($app['timezone']);
require_once __DIR__ . '/../app/core/helpers.php';
secure_headers();
require_once __DIR__ . '/../app/core/Controller.php';
auth_required();
authorize_current_page();
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ProdukController.php';
require_once __DIR__ . '/../app/controllers/PelangganController.php';
require_once __DIR__ . '/../app/controllers/SupplierController.php';
require_once __DIR__ . '/../app/controllers/PenjualanController.php';
require_once __DIR__ . '/../app/controllers/PembelianController.php';
require_once __DIR__ . '/../app/controllers/PiutangController.php';
require_once __DIR__ . '/../app/controllers/HutangController.php';
require_once __DIR__ . '/../app/controllers/LaporanController.php';
require_once __DIR__ . '/../app/controllers/ReferenceController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';
require_once __DIR__ . '/../app/controllers/KasController.php';
require_once __DIR__ . '/../app/controllers/UmkmController.php';
require_once __DIR__ . '/../app/controllers/RoleController.php';
$page = $_GET['page'] ?? 'login';
try {
    switch ($page) {
        case '':
        case 'login': (new AuthController())->login(); break;
        case 'forgot-password': (new AuthController())->forgotPassword(); break;
        case 'reset-password': (new AuthController())->resetPassword(); break;
        case 'change-password': (new AuthController())->changePassword(); break;
        case 'logout': (new AuthController())->logout(); break;
        case 'dashboard': (new DashboardController())->index(); break;
        case 'umkm': (new UmkmController())->index(); break;
        case 'umkm-create': (new UmkmController())->create(); break;
        case 'umkm-edit': (new UmkmController())->edit(); break;
        case 'umkm-delete': (new UmkmController())->delete(); break;
        case 'roles': (new RoleController())->index(); break;
        case 'roles-create': (new RoleController())->create(); break;
        case 'roles-edit': (new RoleController())->edit(); break;
        case 'roles-delete': (new RoleController())->delete(); break;
        case 'produk': (new ProdukController())->index(); break;
        case 'produk-create': (new ProdukController())->create(); break;
        case 'produk-edit': (new ProdukController())->edit(); break;
        case 'produk-delete': (new ProdukController())->delete(); break;
        case 'pelanggan': (new PelangganController())->index(); break;
        case 'pelanggan-create': (new PelangganController())->create(); break;
        case 'pelanggan-edit': (new PelangganController())->edit(); break;
        case 'pelanggan-delete': (new PelangganController())->delete(); break;
        case 'supplier': (new SupplierController())->index(); break;
        case 'supplier-create': (new SupplierController())->create(); break;
        case 'supplier-edit': (new SupplierController())->edit(); break;
        case 'supplier-delete': (new SupplierController())->delete(); break;
        case 'penjualan': (new PenjualanController())->index(); break;
        case 'penjualan-create': (new PenjualanController())->create(); break;
        case 'penjualan-show': (new PenjualanController())->show(); break;
        case 'penjualan-history': (new PenjualanController())->history(); break;
        case 'penjualan-edit': (new PenjualanController())->edit(); break;
        case 'penjualan-product-search': (new PenjualanController())->productSearch(); break;
        case 'penjualan-customer-search': (new PenjualanController())->customerSearch(); break;
        case 'penjualan-invoice': (new PenjualanController())->invoice(); break;
        case 'penjualan-whatsapp': (new PenjualanController())->whatsapp(); break;
        case 'penjualan-delete': (new PenjualanController())->delete(); break;
        case 'pembelian': (new PembelianController())->index(); break;
        case 'pembelian-create': (new PembelianController())->create(); break;
        case 'pembelian-show': (new PembelianController())->show(); break;
        case 'pembelian-history': (new PembelianController())->history(); break;
        case 'pembelian-supplier-search': (new PembelianController())->supplierSearch(); break;
        case 'pembelian-edit': (new PembelianController())->edit(); break;
        case 'pembelian-delete': (new PembelianController())->delete(); break;
        case 'pembelian-print': (new PembelianController())->printView(); break;
        case 'piutang': (new PiutangController())->index(); break;
        case 'piutang-pay': (new PiutangController())->pay(); break;
        case 'piutang-whatsapp': (new PiutangController())->whatsapp(); break;
        case 'hutang': (new HutangController())->index(); break;
        case 'hutang-pay': (new HutangController())->pay(); break;
        case 'laporan-penjualan': (new LaporanController())->penjualan(); break;
        case 'laporan-penjualan-excel': (new LaporanController())->penjualanExcel(); break;
        case 'laporan-pembelian': (new LaporanController())->pembelian(); break;
        case 'laporan-pembelian-excel': (new LaporanController())->pembelianExcel(); break;
        case 'laporan-kas': (new LaporanController())->kas(); break;
        case 'laporan-kas-excel': (new LaporanController())->kasExcel(); break;
        case 'laporan-laba-rugi': (new LaporanController())->labaRugi(); break;
        case 'laporan-laba-rugi-excel': (new LaporanController())->labaRugiExcel(); break;
        case 'laporan-print': (new LaporanController())->renderPrint(); break;
        case 'referensi-kategori-produk': (new ReferenceController())->kategoriProduk(); break;
        case 'referensi-kategori-produk-create': (new ReferenceController())->kategoriProdukCreate(); break;
        case 'referensi-kategori-produk-edit': (new ReferenceController())->kategoriProdukEdit(); break;
        case 'referensi-kategori-produk-delete': (new ReferenceController())->kategoriProdukDelete(); break;
        case 'referensi-satuan': (new ReferenceController())->satuan(); break;
        case 'referensi-satuan-create': (new ReferenceController())->satuanCreate(); break;
        case 'referensi-satuan-edit': (new ReferenceController())->satuanEdit(); break;
        case 'referensi-satuan-delete': (new ReferenceController())->satuanDelete(); break;
        case 'referensi-kategori-kas': (new ReferenceController())->kategoriKas(); break;
        case 'referensi-kategori-kas-create': (new ReferenceController())->kategoriKasCreate(); break;
        case 'referensi-kategori-kas-edit': (new ReferenceController())->kategoriKasEdit(); break;
        case 'referensi-kategori-kas-delete': (new ReferenceController())->kategoriKasDelete(); break;
        case 'users': (new UserController())->index(); break;
        case 'users-create': (new UserController())->create(); break;
        case 'users-edit': (new UserController())->edit(); break;
        case 'users-delete': (new UserController())->delete(); break;
        case 'role-access': (new UserController())->roleAccess(); break;
        case 'kas-manual': (new KasController())->index(); break;
        case 'kas-manual-create': (new KasController())->create(); break;
        case 'kas-manual-edit': (new KasController())->edit(); break;
        case 'kas-manual-delete': (new KasController())->delete(); break;
        default: http_response_code(404); echo '404 - Halaman tidak ditemukan';
    }
} catch (Throwable $e) {
    http_response_code(500); echo '<h3>Terjadi kesalahan sistem</h3><pre>'.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')."\n\n".htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8').'</pre>';
}
