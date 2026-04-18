<?php
require_once __DIR__ . '/Database.php';
function app_config(): array { static $app; if (!$app) $app = require __DIR__ . '/../config/app.php'; return $app; }
function url(string $path = ''): string { $base = rtrim(app_config()['base_url'], '/'); return $base . '/' . ltrim($path, '/'); }
if (!function_exists('e')) {
    function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function current_umkm_id(): ?int { return isset($_SESSION['user']['umkm_id']) ? (int)$_SESSION['user']['umkm_id'] : null; }
function current_role(): string { return $_SESSION['user']['role'] ?? ''; }
function current_role_id(): ?int {
    if (isset($_SESSION['user']['role_id'])) return (int)$_SESSION['user']['role_id'];
    $role = current_role();
    if ($role === '') return null;
    static $cache = [];
    if (isset($cache[$role])) return $cache[$role];
    try {
        $st = Database::getConnection()->prepare("SELECT id FROM roles WHERE nama_role=? LIMIT 1");
        $st->execute([$role]);
        $id = $st->fetchColumn();
        if ($id) { $cache[$role] = (int)$id; $_SESSION['user']['role_id'] = (int)$id; return (int)$id; }
    } catch (Throwable $e) {}
    return null;
}
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function flash(string $key, ?string $message = null): ?string { if ($message !== null) { $_SESSION['_flash'][$key] = $message; return null; } $msg = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $msg; }
function old(string $key, $default='') { return $_POST[$key] ?? $default; }
function auth_required(): void {
    $page = $_GET['page'] ?? '';
    $public = ['', 'login', 'forgot-password', 'reset-password'];
    if (!in_array($page, $public, true) && empty($_SESSION['user'])) { header('Location: ' . url('index.php?page=login')); exit; }
}
function fmt_rp($n): string { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function csrf_token(): string { if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16)); return $_SESSION['_csrf']; }
function csrf_check(): void { if (is_post()) { $token = $_POST['_csrf'] ?? ''; if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) { throw new Exception('Token CSRF tidak valid.'); } } }
function has_role(string ...$roles): bool { return in_array(current_role(), $roles, true); }
function require_roles(string ...$roles): void { if (!has_role(...$roles)) { http_response_code(403); echo '<h3>Akses ditolak</h3><p>Role Anda tidak memiliki akses ke halaman ini.</p>'; exit; } }
function nav_active(array $pages): string { return in_array($_GET['page'] ?? 'dashboard', $pages, true) ? 'active' : ''; }
function login_captcha_question(): string {
    if (empty($_SESSION['_login_captcha']) || !is_array($_SESSION['_login_captcha'])) {
        $a = random_int(1, 9); $b = random_int(1, 9);
        $_SESSION['_login_captcha'] = ['q' => "$a + $b", 'a' => (string)($a + $b)];
    }
    return $_SESSION['_login_captcha']['q'];
}
function login_captcha_valid(string $answer): bool { $ok = isset($_SESSION['_login_captcha']['a']) && trim($answer) === (string)$_SESSION['_login_captcha']['a']; unset($_SESSION['_login_captcha']); return $ok; }
function refresh_login_captcha(): void { unset($_SESSION['_login_captcha']); }
function create_upload_dir(): string { $dir = app_config()['uploads_dir']; if (!is_dir($dir)) @mkdir($dir, 0775, true); return $dir; }
function secure_filename(string $name): string { return preg_replace('/[^A-Za-z0-9_\.-]/', '_', $name) ?: 'file'; }
function clean_text(?string $value, int $maxLength = 255): string { $value = trim((string)$value); $value = preg_replace('/\s+/u', ' ', $value); if (function_exists('mb_substr')) return mb_substr($value, 0, $maxLength); return substr($value, 0, $maxLength); }
function clean_phone(?string $phone): string { $phone = preg_replace('/[^0-9]/', '', (string)$phone); if ($phone === '') return ''; if (str_starts_with($phone, '0')) return '62' . substr($phone, 1); if (str_starts_with($phone, '62')) return $phone; return $phone; }
function whatsapp_link(string $phone, string $message): string { return 'https://wa.me/' . clean_phone($phone) . '?text=' . rawurlencode($message); }
function secure_headers(): void {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: https:; img-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self';");
}

function menu_labels(): array {
    return [
        'dashboard' => 'Dashboard',
        'umkm' => 'Manajemen UMKM',
        'roles' => 'Role & Akses',
        'produk' => 'Produk',
        'pelanggan' => 'Pelanggan',
        'supplier' => 'Supplier',
        'referensi' => 'Referensi',
        'users' => 'User UMKM',
        'hak_akses' => 'Hak Akses Menu',
        'penjualan' => 'Penjualan',
        'pembelian' => 'Pembelian',
        'piutang' => 'Piutang',
        'hutang' => 'Hutang',
        'kas_manual' => 'Kas Manual',
        'laporan' => 'Laporan',
    ];
}
function default_role_menu_permissions(string $role): array {
    $all = array_fill_keys(array_keys(menu_labels()), true);
    $admin = $all;
    $admin['umkm'] = false;
    $operator = [
        'dashboard' => true,
        'umkm' => false,
        'roles' => false,
        'produk' => false,
        'pelanggan' => false,
        'supplier' => false,
        'referensi' => false,
        'users' => false,
        'hak_akses' => false,
        'penjualan' => true,
        'pembelian' => false,
        'piutang' => false,
        'hutang' => false,
        'kas_manual' => false,
        'laporan' => false,
    ];
    if ($role === 'super_admin') return $all;
    if ($role === 'admin_umkm') return $admin;
    if ($role === 'operator') return $operator;
    return [
        'dashboard' => true,
        'umkm' => false,
        'roles' => false,
        'produk' => false,
        'pelanggan' => false,
        'supplier' => false,
        'referensi' => false,
        'users' => false,
        'hak_akses' => false,
        'penjualan' => false,
        'pembelian' => false,
        'piutang' => false,
        'hutang' => false,
        'kas_manual' => false,
        'laporan' => false,
    ];
}

function permission_page_map(): array {
    return [
        'dashboard' => 'dashboard',
        'umkm' => 'umkm', 'umkm-create' => 'umkm', 'umkm-edit' => 'umkm', 'umkm-delete' => 'umkm',
        'roles' => 'roles', 'roles-create' => 'roles', 'roles-edit' => 'roles', 'roles-delete' => 'roles',
        'produk' => 'produk', 'produk-create' => 'produk', 'produk-edit' => 'produk', 'produk-delete' => 'produk',
        'pelanggan' => 'pelanggan', 'pelanggan-create' => 'pelanggan', 'pelanggan-edit' => 'pelanggan', 'pelanggan-delete' => 'pelanggan',
        'supplier' => 'supplier', 'supplier-create' => 'supplier', 'supplier-edit' => 'supplier', 'supplier-delete' => 'supplier',
        'referensi-kategori-produk' => 'referensi', 'referensi-kategori-produk-create' => 'referensi', 'referensi-kategori-produk-edit' => 'referensi', 'referensi-kategori-produk-delete' => 'referensi',
        'referensi-satuan' => 'referensi', 'referensi-satuan-create' => 'referensi', 'referensi-satuan-edit' => 'referensi', 'referensi-satuan-delete' => 'referensi',
        'referensi-kategori-kas' => 'referensi', 'referensi-kategori-kas-create' => 'referensi', 'referensi-kategori-kas-edit' => 'referensi', 'referensi-kategori-kas-delete' => 'referensi',
        'users' => 'users', 'users-create' => 'users', 'users-edit' => 'users', 'users-delete' => 'users',
        'role-access' => 'hak_akses',
        'laporan-laba-rugi' => 'laporan', 'laporan-print' => 'laporan', 'laporan-penjualan-excel' => 'laporan', 'laporan-pembelian-excel' => 'laporan', 'laporan-kas-excel' => 'laporan', 'laporan-laba-rugi-excel' => 'laporan',
        'penjualan' => 'penjualan', 'penjualan-create' => 'penjualan', 'penjualan-edit' => 'penjualan', 'penjualan-delete' => 'penjualan', 'penjualan-show' => 'penjualan', 'penjualan-history' => 'penjualan', 'penjualan-product-search' => 'penjualan', 'penjualan-invoice' => 'penjualan', 'penjualan-whatsapp' => 'penjualan',
        'pembelian' => 'pembelian', 'pembelian-create' => 'pembelian', 'pembelian-edit' => 'pembelian', 'pembelian-delete' => 'pembelian', 'pembelian-print' => 'pembelian',
        'piutang' => 'piutang', 'piutang-pay' => 'piutang', 'piutang-whatsapp' => 'piutang',
        'hutang' => 'hutang', 'hutang-pay' => 'hutang',
        'kas-manual' => 'kas_manual', 'kas-manual-create' => 'kas_manual', 'kas-manual-edit' => 'kas_manual', 'kas-manual-delete' => 'kas_manual',
        'laporan-penjualan' => 'laporan', 'laporan-pembelian' => 'laporan', 'laporan-kas' => 'laporan',
    ];
}
function permission_matrix_current(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $role = current_role();
    $cache = default_role_menu_permissions($role);
    $roleId = current_role_id();
    $umkmId = current_umkm_id();
    if (!$roleId || ($role !== 'super_admin' && !$umkmId)) return $cache;
    try {
        $sql = "SELECT menu_key, can_access FROM menu_permissions WHERE role_id=? AND (umkm_id <=> ?)";
        $st = Database::getConnection()->prepare($sql);
        $st->execute([$roleId, $role === 'super_admin' ? null : $umkmId]);
        foreach ($st->fetchAll() as $row) {
            $cache[$row['menu_key']] = (bool)$row['can_access'];
        }
    } catch (Throwable $e) {
    }
    return $cache;
}
function can_menu(string $menuKey): bool {
    if (current_role() === 'super_admin') return true;
    $matrix = permission_matrix_current();
    return (bool)($matrix[$menuKey] ?? false);
}
function permission_required(string $menuKey): void {
    if (!can_menu($menuKey)) {
        http_response_code(403);
        echo '<h3>Akses ditolak</h3><p>Hak akses menu Anda tidak mengizinkan membuka halaman ini.</p>';
        exit;
    }
}
function authorize_current_page(): void {
    $page = $_GET['page'] ?? 'dashboard';
    $public = ['', 'login', 'forgot-password', 'reset-password', 'logout', 'change-password'];
    if (in_array($page, $public, true) || empty($_SESSION['user'])) return;
    $map = permission_page_map();
    $menuKey = $map[$page] ?? null;
    if ($menuKey) permission_required($menuKey);
}
