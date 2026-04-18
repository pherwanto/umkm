# 🏪 Sistem Informasi Pencatatan Transaksi UMKM Multi-UMKM

Sistem Informasi UMKM Multi-UMKM adalah aplikasi berbasis web yang dirancang untuk membantu pelaku UMKM dalam mencatat dan mengelola aktivitas usaha secara terstruktur.

Sistem ini dikembangkan dalam rangka **pengabdian kepada masyarakat** untuk mendukung digitalisasi UMKM di wilayah Cipancar Garut, khususnya pada sektor:

* ☕ Kopi
* 🌾 Pertanian
* 🏺 Kerajinan (Gerabah dan lainnya)

---

## 🎯 Tujuan Sistem

* Membantu UMKM melakukan pencatatan transaksi secara digital
* Memisahkan data antar UMKM dalam satu sistem (multi-tenant)
* Menyediakan laporan keuangan sederhana
* Meningkatkan literasi digital pelaku usaha

---

## 🚀 Fitur Utama

### 🔐 Multi-User & Multi-UMKM

* Satu sistem untuk banyak UMKM
* Data terpisah berdasarkan UMKM
* Role:

  * Super Admin
  * Admin UMKM
  * Operator

### 📦 Manajemen Produk

* Kategori produk
* Satuan (kg, pcs, liter, dll)
* Stok & stok minimum

### 💰 Transaksi

* Penjualan
* Pembelian
* Piutang (utang pelanggan)
* Hutang (utang ke supplier)

### 💳 Keuangan

* Kas masuk
* Kas keluar
* Kategori biaya

### 📊 Laporan

* Penjualan
* Pembelian
* Piutang
* Hutang
* Stok
* Kas
* Laba Rugi sederhana

### 📈 Dashboard

* Ringkasan bisnis
* Stok menipis
* Transaksi terbaru

---

## 🏗️ Teknologi

* Backend: PHP Native
* Database: MySQL / MariaDB
* Frontend: HTML, CSS, JavaScript (Bootstrap)
* Web Server: Apache / Nginx

---

## 📁 Struktur Project

```bash
/app
  /config
  /controllers
  /models
  /views
  /helpers
/public
  index.php
/routes
/storage
```

---

## ⚙️ Instalasi

### 1. Clone Repository

```bash
git clone git@github.com:pherwanto/umkm.git
cd umkm
```

---

### 2. Setup Database

1. Buat database baru:

```sql
CREATE DATABASE umkm_db;
```

2. Import file SQL:

```bash
mysql -u root -p umkm_db < umkm_multi_tenant.sql
```

---

### 3. Konfigurasi Database

Edit file:

```bash
/app/config/database.php
```

Sesuaikan:

```php
return [
    'host' => 'localhost',
    'database' => 'umkm_db',
    'username' => 'root',
    'password' => ''
];
```

---

### 4. Jalankan Aplikasi

Jika menggunakan XAMPP:

```bash
http://localhost/umkm/public
```

Jika menggunakan PHP built-in server:

```bash
php -S localhost:8000 -t public
```

---

## 🔑 Default Login (Development)

### Super Admin

```
Username: superadmin
Password: (generate manual via password_hash)
```

### Admin UMKM

```
Username: admin_umkm
Password: (generate manual)
```

⚠️ Penting:
Password pada database masih berupa placeholder.
Silakan generate dengan PHP:

```php
echo password_hash('123456', PASSWORD_DEFAULT);
```

---

## 🔄 Alur Sistem

### Penjualan

* Input transaksi
* Stok berkurang
* Kas bertambah / Piutang terbentuk

### Pembelian

* Input pembelian
* Stok bertambah
* Kas berkurang / Hutang terbentuk

### Piutang & Hutang

* Mendukung pembayaran bertahap
* Update status otomatis

---

## 📊 Struktur Database

Database terdiri dari beberapa tabel utama:

* umkm
* users
* produk
* penjualan
* pembelian
* piutang
* hutang
* kas
* mutasi_stok

---

## 🧠 Arsitektur Sistem

* Multi-tenant berbasis `umkm_id`
* Isolasi data antar UMKM
* Modular (MVC sederhana)
* Siap dikembangkan menjadi SaaS

---

## 🔐 Keamanan Dasar

* Password hashing (bcrypt)
* Session login
* Role-based access control
* Validasi input
* Prepared statement (anti SQL Injection)

---

## 📌 Roadmap Pengembangan

### Tahap 1 (MVP)

* Transaksi dasar
* Stok
* Piutang & Hutang

### Tahap 2

* Dashboard grafik
* Export PDF/Excel
* Notifikasi jatuh tempo

### Tahap 3

* Mobile friendly UI
* Integrasi marketplace
* Analitik usaha

---

## 🤝 Kontribusi

Silakan kontribusi dengan:

* Pull request
* Issue
* Saran fitur

---

## 📜 Lisensi

Project ini dibuat untuk keperluan:

* Pengabdian masyarakat
* Edukasi UMKM
* Pengembangan sistem informasi

---

## 👨‍🏫 Pengembang

Patah Herwanto
Universitas Ekuitas
Putrasoft

---

## 💡 Catatan

Sistem ini dirancang agar:

* Mudah digunakan oleh UMKM
* Tidak rumit seperti software akuntansi besar
* Fokus pada kebutuhan nyata di lapangan

---

⭐ Jika project ini bermanfaat, jangan lupa beri star di repository!
