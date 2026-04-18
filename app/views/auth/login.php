<style>
.login-shell{min-height:100vh;display:grid;grid-template-columns:1.1fr .9fr}
.login-info{background:linear-gradient(135deg,#0f4fcf 0%,#2f7af6 48%,#5aa2ff 100%);color:#fff;padding:2.2rem 2rem;display:flex;align-items:center}
.login-info .inner{max-width:720px}
.login-logo{max-width:220px;background:#fff;border-radius:12px;padding:.55rem .8rem;margin-bottom:1.3rem}
.login-info h2{font-weight:800;letter-spacing:-.02em;margin-bottom:1rem}
.login-info p,.login-info li{color:rgba(255,255,255,.92)}
.login-info hr{border-color:rgba(255,255,255,.3);margin:1.15rem 0}
.login-form-wrap{display:flex;align-items:center;justify-content:center;padding:1.2rem}
.login-form-card{width:100%;max-width:480px}
@media (max-width:991.98px){.login-shell{grid-template-columns:1fr}.login-info{padding:1.4rem 1.1rem}.login-form-wrap{padding:1rem}}
</style>

<div class="login-shell">
  <section class="login-info">
    <div class="inner">
      <img class="login-logo" src="https://ekuitas.ac.id/storage/asset/ekuitas-h.png" alt="Logo Universitas Ekuitas Indonesia">
      <h2>Sistem Informasi Pencatatan Transaksi UMKM</h2>
      <p>
        Aplikasi berbasis web untuk membantu pelaku UMKM mencatat dan mengelola transaksi usaha secara
        <strong>mudah, rapi, dan terstruktur</strong>.
      </p>
      <p>
        Dikembangkan dalam program pengabdian masyarakat oleh Universitas Ekuitas Indonesia, sistem ini mendukung
        digitalisasi UMKM di berbagai sektor seperti kuliner, pertanian, kerajinan, dan perdagangan.
      </p>
      <hr>
      <h5 class="fw-bold mb-2">Manfaat Utama</h5>
      <ul class="mb-0 ps-3">
        <li> Mencatat kas masuk &amp; penjualan</li>
        <li> Mencatat pengeluaran &amp; biaya operasional</li>
        <li> Menyajikan laporan kas otomatis</li>
        <li> Data aman &amp; terpisah (multi-UMKM)</li>
        <li> Membantu pengambilan keputusan usaha</li>
      </ul>
      <hr>
      <p class="mb-0"><em>💡 Kelola keuangan usaha Anda dengan lebih profesional dan berbasis digital.</em></p>
    </div>
  </section>

  <section class="login-form-wrap">
    <div class="card auth-card enterprise-card login-form-card">
      <div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4">
          <div class="brand-badge mx-auto mb-3">UMKM</div>
          <h2 class="fw-bold mb-1">Masuk ke Sistem</h2>
          <p class="text-muted mb-0">Silakan login untuk mengakses dashboard transaksi UMKM.</p>
        </div>
        <form method="post" autocomplete="off">
          <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
          <div class="mb-3"><label class="form-label">Username</label><input class="form-control form-control-lg" name="username" required autofocus value="<?= e(old('username')) ?>"></div>
          <div class="mb-3"><label class="form-label">Password</label><input class="form-control form-control-lg" type="password" name="password" required></div>
          <div class="mb-3">
            <label class="form-label">Captcha: berapa hasil <?= e(login_captcha_question()) ?> ?</label>
            <input class="form-control form-control-lg" name="captcha" inputmode="numeric" required>
          </div>
          <button class="btn btn-dark btn-lg w-100">Masuk</button>
        </form>
        <div class="d-flex justify-content-between align-items-center mt-3 small">
          <a href="<?= e(url('index.php?page=forgot-password')) ?>" class="text-decoration-none">Lupa password?</a>
          <span class="text-muted">Demo: superadmin / 123456</span>
        </div>
      </div>
    </div>
  </section>
</div>
