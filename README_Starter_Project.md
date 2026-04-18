# Sistem UMKM Multi-UMKM v4

Starter project PHP native untuk digitalisasi UMKM kopi, pertanian, dan kerajinan.

## Fitur baru di versi ini
- Filter tanggal dan pencarian cepat pada daftar penjualan dan audit log
- Hak akses role:
  - `super_admin`
  - `admin_umkm`
  - `operator`
- Tampilan responsif bergaya enterprise untuk desktop, tablet, dan smartphone
- Dukungan barcode scanner pada form penjualan
- Upload gambar produk
- Login dengan captcha matematika
- Lupa password dan reset password berbasis SMTP
- Ganti password dari dalam aplikasi

## Kredensial demo
- superadmin / 123456
- adminkopi / 123456
- operator / 123456

## Instalasi cepat
1. Buat database MariaDB/MySQL: `umkm_db`
2. Import `starter_umkm.sql`
3. Letakkan folder project di web root
4. Sesuaikan `app/config/database.php`
5. Sesuaikan `app/config/app.php` terutama:
   - `base_url`
   - konfigurasi `smtp`
6. Akses:
   - `http://localhost/umkm_starter_project_v4/public/`

## Catatan SMTP
Reset password membutuhkan akun SMTP yang valid. Contoh konfigurasi ada di `app/config/app.php`.
Untuk Gmail gunakan App Password, bukan password biasa.

## Catatan upload gambar
Gambar produk akan disimpan ke folder `public/uploads`.

## Role akses
- Super Admin: akses penuh
- Admin UMKM: data master, transaksi, laporan, audit
- Operator: dashboard, penjualan, detail transaksi, invoice, edit penjualan


## Patch v5
- CRUD referensi: kategori produk, satuan, kategori kas
- Menu Setting > Referensi
- Form penjualan: layout dua kolom, kartu produk dengan gambar asli, indikator stok, checkout fixed di mobile


## Patch v6

Fitur tambahan pada versi ini:
- CRUD User UMKM
- Manajemen hak akses per menu
- Kas manual masuk/keluar
- Tabel `menu_permissions` pada database

> Setelah upgrade, lakukan import ulang `starter_umkm.sql` atau tambahkan tabel `menu_permissions` secara manual.


## Update v7
- CRUD Manajemen UMKM
- Role khusus/custom role per UMKM
- Laporan laba rugi sederhana
