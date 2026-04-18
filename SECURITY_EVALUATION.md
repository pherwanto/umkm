# Evaluasi & Hardening Keamanan Sistem UMKM

Perbaikan keamanan yang sudah ditambahkan pada patch ini:

1. Session cookie diperketat (`HttpOnly`, `SameSite=Lax`, `Secure` otomatis saat HTTPS aktif).
2. Session ID diregenerasi saat login berhasil.
3. Header keamanan dasar ditambahkan:
   - `X-Frame-Options: SAMEORIGIN`
   - `X-Content-Type-Options: nosniff`
   - `Referrer-Policy`
   - `Permissions-Policy`
   - `Content-Security-Policy`
4. Validasi unggah gambar produk diperketat:
   - MIME type dicek dengan `finfo`
   - ukuran maksimal 2 MB
   - nama file diamankan
5. Aksi hapus transaksi pembelian sekarang memakai POST + CSRF.
6. Rollback transaksi pembelian dibuat atomik menggunakan database transaction.
7. Pengiriman WhatsApp menggunakan link terenkripsi query-string standar (`wa.me`) tanpa menyimpan token sensitif.

Rekomendasi lanjutan untuk tahap berikutnya:

1. Tambahkan rate limiting login per IP / username.
2. Simpan audit log untuk reset password dan logout.
3. Pakai prepared statement di seluruh query custom baru bila ada penambahan modul berikutnya.
4. Pertimbangkan library PDF server-side jika dokumen resmi akan dipakai jangka panjang.
5. Tambahkan backup terenkripsi untuk database produksi.
6. Aktifkan HTTPS penuh bila sistem dipakai online.
