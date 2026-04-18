-- =========================================================
-- SQL Schema Aplikasi UMKM Multi-UMKM
-- Target: MySQL 8.x / MariaDB 10.5+
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS mutasi_stok;
DROP TABLE IF EXISTS kas;
DROP TABLE IF EXISTS kategori_kas;
DROP TABLE IF EXISTS pembayaran_hutang;
DROP TABLE IF EXISTS hutang;
DROP TABLE IF EXISTS pembayaran_piutang;
DROP TABLE IF EXISTS piutang;
DROP TABLE IF EXISTS pembelian_detail;
DROP TABLE IF EXISTS pembelian;
DROP TABLE IF EXISTS penjualan_detail;
DROP TABLE IF EXISTS penjualan;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS satuan;
DROP TABLE IF EXISTS kategori_produk;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS umkm;

CREATE TABLE umkm (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_umkm VARCHAR(30) NOT NULL UNIQUE,
    nama_umkm VARCHAR(150) NOT NULL,
    nama_pemilik VARCHAR(150) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    jenis_usaha VARCHAR(100) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    deskripsi VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED DEFAULT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori_produk (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_kategori_produk_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_kategori_produk_umkm (umkm_id, nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE satuan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_satuan VARCHAR(50) NOT NULL UNIQUE,
    keterangan VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE produk (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    kategori_id BIGINT UNSIGNED NOT NULL,
    satuan_id BIGINT UNSIGNED NOT NULL,
    kode_produk VARCHAR(50) NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga_beli DECIMAL(18,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(18,2) NOT NULL DEFAULT 0,
    stok DECIMAL(18,2) NOT NULL DEFAULT 0,
    stok_minimum DECIMAL(18,2) NOT NULL DEFAULT 0,
    deskripsi TEXT DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_produk_umkm_kode (umkm_id, kode_produk),
    KEY idx_produk_umkm (umkm_id),
    KEY idx_produk_kategori (kategori_id),
    CONSTRAINT fk_produk_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_produk_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_produk(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_produk_satuan FOREIGN KEY (satuan_id) REFERENCES satuan(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pelanggan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    nama_pelanggan VARCHAR(150) NOT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    jenis_pelanggan VARCHAR(50) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pelanggan_umkm (umkm_id),
    CONSTRAINT fk_pelanggan_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE supplier (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    nama_supplier VARCHAR(150) NOT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(30) DEFAULT NULL,
    jenis_supplier VARCHAR(50) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_supplier_umkm (umkm_id),
    CONSTRAINT fk_supplier_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penjualan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    kode_penjualan VARCHAR(50) NOT NULL,
    tanggal DATETIME NOT NULL,
    pelanggan_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    diskon DECIMAL(18,2) NOT NULL DEFAULT 0,
    is_pajak_enabled TINYINT(1) NOT NULL DEFAULT 0,
    pajak_persen INT NOT NULL DEFAULT 0,
    pajak_nominal BIGINT NOT NULL DEFAULT 0,
    total DECIMAL(18,2) NOT NULL DEFAULT 0,
    dibayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    sisa DECIMAL(18,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('tunai','transfer','kredit') NOT NULL DEFAULT 'tunai',
    status_pembayaran ENUM('lunas','sebagian','belum_bayar') NOT NULL DEFAULT 'lunas',
    keterangan TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_penjualan_umkm_kode (umkm_id, kode_penjualan),
    KEY idx_penjualan_tanggal (tanggal),
    KEY idx_penjualan_umkm (umkm_id),
    CONSTRAINT fk_penjualan_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_penjualan_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_penjualan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penjualan_detail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    penjualan_id BIGINT UNSIGNED NOT NULL,
    produk_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(18,2) NOT NULL DEFAULT 0,
    harga DECIMAL(18,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    KEY idx_penjualan_detail_penjualan (penjualan_id),
    KEY idx_penjualan_detail_produk (produk_id),
    CONSTRAINT fk_penjualan_detail_penjualan FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_penjualan_detail_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembelian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    kode_pembelian VARCHAR(50) NOT NULL,
    tanggal DATETIME NOT NULL,
    supplier_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    diskon DECIMAL(18,2) NOT NULL DEFAULT 0,
    total DECIMAL(18,2) NOT NULL DEFAULT 0,
    dibayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    sisa DECIMAL(18,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('tunai','transfer','hutang') NOT NULL DEFAULT 'tunai',
    status_pembayaran ENUM('lunas','sebagian','belum_bayar') NOT NULL DEFAULT 'lunas',
    keterangan TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pembelian_umkm_kode (umkm_id, kode_pembelian),
    KEY idx_pembelian_tanggal (tanggal),
    KEY idx_pembelian_umkm (umkm_id),
    CONSTRAINT fk_pembelian_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pembelian_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_pembelian_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembelian_detail (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pembelian_id BIGINT UNSIGNED NOT NULL,
    produk_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(18,2) NOT NULL DEFAULT 0,
    harga DECIMAL(18,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
    KEY idx_pembelian_detail_pembelian (pembelian_id),
    KEY idx_pembelian_detail_produk (produk_id),
    CONSTRAINT fk_pembelian_detail_pembelian FOREIGN KEY (pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pembelian_detail_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE piutang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    penjualan_id BIGINT UNSIGNED NOT NULL,
    pelanggan_id BIGINT UNSIGNED DEFAULT NULL,
    tanggal_piutang DATE NOT NULL,
    jatuh_tempo DATE DEFAULT NULL,
    total_piutang DECIMAL(18,2) NOT NULL DEFAULT 0,
    total_bayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    sisa_piutang DECIMAL(18,2) NOT NULL DEFAULT 0,
    status ENUM('belum_lunas','sebagian','lunas') NOT NULL DEFAULT 'belum_lunas',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_piutang_penjualan (penjualan_id),
    KEY idx_piutang_umkm (umkm_id),
    KEY idx_piutang_jatuh_tempo (jatuh_tempo),
    CONSTRAINT fk_piutang_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_piutang_penjualan FOREIGN KEY (penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_piutang_pelanggan FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembayaran_piutang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    piutang_id BIGINT UNSIGNED NOT NULL,
    tanggal_bayar DATETIME NOT NULL,
    nominal_bayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('tunai','transfer') NOT NULL DEFAULT 'tunai',
    keterangan TEXT DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pembayaran_piutang_piutang (piutang_id),
    CONSTRAINT fk_pembayaran_piutang_piutang FOREIGN KEY (piutang_id) REFERENCES piutang(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pembayaran_piutang_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hutang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    pembelian_id BIGINT UNSIGNED NOT NULL,
    supplier_id BIGINT UNSIGNED DEFAULT NULL,
    tanggal_hutang DATE NOT NULL,
    jatuh_tempo DATE DEFAULT NULL,
    total_hutang DECIMAL(18,2) NOT NULL DEFAULT 0,
    total_bayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    sisa_hutang DECIMAL(18,2) NOT NULL DEFAULT 0,
    status ENUM('belum_lunas','sebagian','lunas') NOT NULL DEFAULT 'belum_lunas',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_hutang_pembelian (pembelian_id),
    KEY idx_hutang_umkm (umkm_id),
    KEY idx_hutang_jatuh_tempo (jatuh_tempo),
    CONSTRAINT fk_hutang_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hutang_pembelian FOREIGN KEY (pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hutang_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembayaran_hutang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hutang_id BIGINT UNSIGNED NOT NULL,
    tanggal_bayar DATETIME NOT NULL,
    nominal_bayar DECIMAL(18,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('tunai','transfer') NOT NULL DEFAULT 'tunai',
    keterangan TEXT DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pembayaran_hutang_hutang (hutang_id),
    CONSTRAINT fk_pembayaran_hutang_hutang FOREIGN KEY (hutang_id) REFERENCES hutang(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pembayaran_hutang_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kategori_kas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    nama_kategori VARCHAR(100) NOT NULL,
    jenis ENUM('masuk','keluar') NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_kategori_kas_umkm (umkm_id),
    UNIQUE KEY uk_kategori_kas_umkm (umkm_id, nama_kategori, jenis),
    CONSTRAINT fk_kategori_kas_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    tanggal DATETIME NOT NULL,
    kategori_kas_id BIGINT UNSIGNED NOT NULL,
    jenis ENUM('masuk','keluar') NOT NULL,
    nominal DECIMAL(18,2) NOT NULL DEFAULT 0,
    referensi_tipe VARCHAR(50) DEFAULT NULL,
    referensi_id BIGINT UNSIGNED DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_kas_umkm (umkm_id),
    KEY idx_kas_tanggal (tanggal),
    CONSTRAINT fk_kas_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_kas_kategori FOREIGN KEY (kategori_kas_id) REFERENCES kategori_kas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_kas_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mutasi_stok (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED NOT NULL,
    produk_id BIGINT UNSIGNED NOT NULL,
    tanggal DATETIME NOT NULL,
    jenis_mutasi ENUM('pembelian','penjualan','penyesuaian','retur','koreksi') NOT NULL,
    qty_masuk DECIMAL(18,2) NOT NULL DEFAULT 0,
    qty_keluar DECIMAL(18,2) NOT NULL DEFAULT 0,
    stok_sebelum DECIMAL(18,2) NOT NULL DEFAULT 0,
    stok_sesudah DECIMAL(18,2) NOT NULL DEFAULT 0,
    referensi_tipe VARCHAR(50) DEFAULT NULL,
    referensi_id BIGINT UNSIGNED DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_mutasi_stok_umkm (umkm_id),
    KEY idx_mutasi_stok_produk (produk_id),
    KEY idx_mutasi_stok_tanggal (tanggal),
    CONSTRAINT fk_mutasi_stok_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_mutasi_stok_produk FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_mutasi_stok_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    umkm_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    aktivitas VARCHAR(150) NOT NULL,
    tabel_ref VARCHAR(100) DEFAULT NULL,
    data_ref_id BIGINT UNSIGNED DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_log_umkm (umkm_id),
    KEY idx_audit_log_user (user_id),
    CONSTRAINT fk_audit_log_umkm FOREIGN KEY (umkm_id) REFERENCES umkm(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_audit_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DATA AWAL / SEEDER MINIMAL
-- =========================================================
INSERT INTO roles (nama_role, deskripsi) VALUES
('super_admin', 'Akses penuh seluruh sistem'),
('admin_umkm', 'Admin/pemilik UMKM'),
('operator', 'Operator/kasir UMKM');

INSERT INTO satuan (nama_satuan, keterangan) VALUES
('pcs', 'Pieces / buah'),
('kg', 'Kilogram'),
('gram', 'Gram'),
('pack', 'Kemasan/pack'),
('liter', 'Liter');

INSERT INTO umkm (kode_umkm, nama_umkm, nama_pemilik, alamat, telepon, jenis_usaha, status) VALUES
('UMKM001', 'Kopi Cipancar Sejahtera', 'Ketua UMKM', 'Cipancar, Garut', '081234567890', 'Kopi dan produk pertanian', 'aktif');

-- Password default di bawah ini hanya placeholder contoh.
-- Silakan ganti dengan hash password hasil password_hash() dari PHP.
INSERT INTO users (umkm_id, role_id, nama, username, password_hash, email, telepon, status) VALUES
(NULL, 1, 'Super Administrator', 'superadmin', 'CHANGE_ME_HASH_SUPERADMIN', 'admin@example.com', '081200000001', 'aktif'),
(1, 2, 'Admin UMKM Cipancar', 'admin_cipancar', 'CHANGE_ME_HASH_ADMIN_UMKM', 'umkm@example.com', '081200000002', 'aktif');

INSERT INTO kategori_produk (umkm_id, nama_kategori, deskripsi) VALUES
(1, 'Kopi', 'Produk kopi'),
(1, 'Pertanian', 'Produk hasil pertanian'),
(1, 'Kerajinan', 'Produk kerajinan dan gerabah');

INSERT INTO kategori_kas (umkm_id, nama_kategori, jenis, keterangan) VALUES
(1, 'Penjualan Tunai', 'masuk', 'Kas masuk dari penjualan tunai'),
(1, 'Pelunasan Piutang', 'masuk', 'Kas masuk dari pembayaran piutang'),
(1, 'Pembelian Tunai', 'keluar', 'Kas keluar untuk pembelian tunai'),
(1, 'Operasional', 'keluar', 'Biaya operasional umum');

SET FOREIGN_KEY_CHECKS = 1;
