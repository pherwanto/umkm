<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.ls-root{
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 480px;
  font-family:'DM Sans',sans-serif;
  background:#09090b;
  color:#fff;
}

/* ── LEFT PANEL ── */
.ls-left{
  position:relative;
  overflow:hidden;
  padding:3rem;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}

.ls-left-bg{
  position:absolute;
  inset:0;
  background:
    radial-gradient(ellipse 60% 50% at 20% 30%, rgba(20,80,200,.35) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 80% 70%, rgba(6,182,212,.15) 0%, transparent 65%),
    linear-gradient(160deg,#0a0f1e 0%,#060a14 55%,#080d1c 100%);
  z-index:0;
}

/* Fine grid overlay */
.ls-left-bg::after{
  content:'';
  position:absolute;
  inset:0;
  background-image:
    linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
  background-size:40px 40px;
}

.ls-top{position:relative;z-index:1}
.ls-logo-wrap{display:flex;align-items:center;gap:.75rem;margin-bottom:3.5rem}
.ls-logo-img{height:36px;filter:brightness(0) invert(1) drop-shadow(0 0 12px rgba(99,179,255,.4))}
.ls-logo-img-ykp{height:36px;filter:drop-shadow(0 0 10px rgba(255,255,255,.2))}
.ls-logo-img-bjb{height:36px;filter:drop-shadow(0 0 10px rgba(255,255,255,.2))}
.ls-wordmark{font-family:'Playfair Display',serif;font-size:1.05rem;letter-spacing:.04em;color:rgba(255,255,255,.7)}

.ls-headline{
  font-family:'Playfair Display',serif;
  font-size:clamp(2rem,3.5vw,2.8rem);
  font-weight:600;
  line-height:1.2;
  letter-spacing:-.01em;
  margin-bottom:1.25rem;
  color:#fff;
}

.ls-headline em{
  font-style:normal;
  background:linear-gradient(90deg,#60a5fa,#38bdf8,#67e8f9);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}

.ls-subtext{
  font-size:.95rem;
  font-weight:300;
  line-height:1.7;
  color:rgba(255,255,255,.55);
  max-width:460px;
  margin-bottom:2.5rem;
}

/* Stats row */
.ls-stats{
  display:flex;
  gap:2rem;
  margin-bottom:3rem;
}
.ls-stat-item{}
.ls-stat-num{
  font-family:'Playfair Display',serif;
  font-size:1.6rem;
  font-weight:500;
  color:#fff;
  letter-spacing:-.02em;
}
.ls-stat-label{
  font-size:.75rem;
  font-weight:400;
  color:rgba(255,255,255,.4);
  text-transform:uppercase;
  letter-spacing:.08em;
  margin-top:.1rem;
}

/* Feature list */
.ls-features{
  list-style:none;
  display:flex;
  flex-direction:column;
  gap:.85rem;
  position:relative;
  z-index:1;
}
.ls-features li{
  display:flex;
  align-items:center;
  gap:.75rem;
  font-size:.875rem;
  font-weight:300;
  color:rgba(255,255,255,.65);
  letter-spacing:.01em;
}
.ls-feat-dot{
  width:6px;height:6px;
  border-radius:50%;
  background:linear-gradient(135deg,#60a5fa,#38bdf8);
  flex-shrink:0;
  box-shadow:0 0 8px rgba(96,165,250,.5);
}

/* Bottom badge */
.ls-bottom{position:relative;z-index:1}
.ls-univ-badge{
  display:inline-flex;
  align-items:center;
  gap:.6rem;
  border:1px solid rgba(255,255,255,.1);
  border-radius:100px;
  padding:.4rem .9rem .4rem .5rem;
  backdrop-filter:blur(12px);
  background:rgba(255,255,255,.04);
}
.ls-univ-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px #22c55e}
.ls-univ-text{font-size:.78rem;color:rgba(255,255,255,.5);font-weight:300;letter-spacing:.02em}

/* Decorative orb */
.ls-orb{
  position:absolute;
  width:320px;height:320px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(56,189,248,.06) 0%,transparent 70%);
  border:1px solid rgba(56,189,248,.06);
  bottom:-80px;right:-80px;
  z-index:0;
  pointer-events:none;
}
.ls-orb2{
  width:180px;height:180px;
  border:1px solid rgba(96,165,250,.05);
  background:transparent;
  top:10%;left:55%;
}

/* ── RIGHT PANEL ── */
.ls-right{
  background:#ffffff;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:2.5rem 2rem;
}

.ls-card{width:100%;max-width:380px}

.ls-card-header{margin-bottom:2rem}
.ls-tag{
  display:inline-block;
  font-size:.7rem;
  font-weight:500;
  letter-spacing:.12em;
  text-transform:uppercase;
  color:#1e40af;
  background:#eff6ff;
  border:1px solid #bfdbfe;
  border-radius:100px;
  padding:.25rem .75rem;
  margin-bottom:1.1rem;
}
.ls-card-title{
  font-family:'Playfair Display',serif;
  font-size:1.75rem;
  font-weight:600;
  color:#09090b;
  line-height:1.2;
  margin-bottom:.5rem;
}
.ls-card-sub{
  font-size:.875rem;
  color:#71717a;
  font-weight:300;
  line-height:1.6;
}

/* Form */
.ls-form{display:flex;flex-direction:column;gap:1.1rem}
.ls-field{}
.ls-label{
  display:block;
  font-size:.8rem;
  font-weight:500;
  color:#3f3f46;
  letter-spacing:.02em;
  margin-bottom:.4rem;
}
.ls-input{
  width:100%;
  height:44px;
  padding:0 1rem;
  border:1.5px solid #e4e4e7;
  border-radius:8px;
  font-family:'DM Sans',sans-serif;
  font-size:.9rem;
  color:#09090b;
  background:#fafafa;
  outline:none;
  transition:border-color .2s,box-shadow .2s,background .2s;
}
.ls-input:focus{
  border-color:#3b82f6;
  background:#fff;
  box-shadow:0 0 0 3px rgba(59,130,246,.12);
}
.ls-input::placeholder{color:#a1a1aa}

/* Captcha field */
.ls-captcha-row{display:flex;gap:.6rem;align-items:flex-end}
.ls-captcha-row .ls-field{flex:1}
.ls-captcha-box{
  height:44px;
  min-width:80px;
  padding:0 .75rem;
  border:1.5px solid #e4e4e7;
  border-radius:8px;
  background:#f4f4f5;
  font-family:'DM Sans',sans-serif;
  font-size:.85rem;
  font-weight:500;
  color:#3f3f46;
  display:flex;
  align-items:center;
  justify-content:center;
  white-space:nowrap;
  letter-spacing:.03em;
  flex-shrink:0;
}

/* Submit button */
.ls-btn{
  width:100%;
  height:46px;
  background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);
  color:#fff;
  border:none;
  border-radius:8px;
  font-family:'DM Sans',sans-serif;
  font-size:.95rem;
  font-weight:500;
  letter-spacing:.02em;
  cursor:pointer;
  transition:opacity .2s, transform .15s, box-shadow .2s;
  box-shadow:0 2px 12px rgba(29,78,216,.35);
  margin-top:.25rem;
}
.ls-btn:hover{opacity:.93;transform:translateY(-1px);box-shadow:0 4px 18px rgba(29,78,216,.4)}
.ls-btn:active{transform:translateY(0);opacity:1}

/* Footer row */
.ls-footer-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-top:1.25rem;
}
.ls-link{
  font-size:.8rem;
  color:#3b82f6;
  text-decoration:none;
  font-weight:400;
}
.ls-link:hover{text-decoration:underline}
.ls-demo{
  font-size:.75rem;
  color:#a1a1aa;
  background:#f4f4f5;
  border-radius:6px;
  padding:.25rem .6rem;
  letter-spacing:.01em;
}
.ls-demo strong{color:#71717a;font-weight:500}

/* Divider */
.ls-divider{
  display:flex;
  align-items:center;
  gap:.75rem;
  margin:.25rem 0;
}
.ls-divider-line{flex:1;height:1px;background:#e4e4e7}
.ls-divider-text{font-size:.75rem;color:#a1a1aa;font-weight:400;white-space:nowrap}

/* Responsive */
@media(max-width:900px){
  .ls-root{grid-template-columns:1fr}
  .ls-left{min-height:40vh;padding:2rem 1.5rem}
  .ls-right{padding:2rem 1.25rem;min-height:60vh}
  .ls-orb,.ls-orb2{display:none}
  .ls-stats{gap:1.25rem}
}
@media(max-width:480px){
  .ls-right{padding:1.5rem 1rem}
  .ls-card{max-width:100%}
  .ls-card-title{font-size:1.5rem}
}
</style>

<div class="ls-root">

  <!-- ── LEFT: Brand panel ── -->
  <section class="ls-left">
    <div class="ls-left-bg"></div>
    <div class="ls-orb"></div>
    <div class="ls-orb ls-orb2"></div>

    <div class="ls-top">
      <div class="ls-logo-wrap">
        <img class="ls-logo-img" src="https://ekuitas.ac.id/storage/asset/ekuitas-h.png" alt="Universitas Ekuitas Indonesia">
        <img class="ls-logo-img-ykp" src="https://ekuitas.ac.id/storage/asset/logo%20YKP%20Mitra.png" alt="Logo YKP Mitra">
        <img class="ls-logo-img-bjb" src="https://ekuitas.ac.id/storage/asset/bank-bjb.png" alt="Logo Bank BJB">
      </div>

      <h1 class="ls-headline">
        Pencatatan Transaksi<br>
        UMKM yang <em>Cerdas &amp;<br>Terstruktur</em>
      </h1>

      <p class="ls-subtext">
        Platform digital pengabdian masyarakat Universitas Ekuitas Indonesia.
        Bantu pelaku UMKM mengelola keuangan usaha secara profesional dan berbasis data.
      </p>

      <div class="ls-stats">
        <div class="ls-stat-item">
          <div class="ls-stat-num">Multi</div>
          <div class="ls-stat-label">UMKM Tenant</div>
        </div>
        <div class="ls-stat-item">
          <div class="ls-stat-num">Real‑time</div>
          <div class="ls-stat-label">Laporan Kas</div>
        </div>
        <div class="ls-stat-item">
          <div class="ls-stat-num">100%</div>
          <div class="ls-stat-label">Data Terpisah</div>
        </div>
      </div>

      <ul class="ls-features">
        <li><span class="ls-feat-dot"></span>Catat kas masuk, penjualan &amp; pengeluaran</li>
        <li><span class="ls-feat-dot"></span>Laporan keuangan otomatis &amp; analitik usaha</li>
        <li><span class="ls-feat-dot"></span>Isolasi data per UMKM — aman &amp; privat</li>
        <li><span class="ls-feat-dot"></span>Mendukung sektor kuliner, pertanian &amp; perdagangan</li>
        <li><span class="ls-feat-dot"></span>Antarmuka sederhana, tanpa keahlian akuntansi</li>
      </ul>
    </div>

    <div class="ls-bottom">
      <div class="ls-univ-badge">
        <span class="ls-univ-dot"></span>
        <span class="ls-univ-text">Program Pengabdian Masyarakat · Universitas Ekuitas Indonesia</span>
      </div>
    </div>
  </section>

  <!-- ── RIGHT: Login form ── -->
  <section class="ls-right">
    <div class="ls-card">
      <div class="ls-card-header">
        <span class="ls-tag">Secure Login</span>
        <h2 class="ls-card-title">Masuk ke Sistem</h2>
        <p class="ls-card-sub">Masukkan kredensial Anda untuk mengakses dashboard transaksi.</p>
      </div>

      <form class="ls-form" method="post" autocomplete="off">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <div class="ls-field">
          <label class="ls-label" for="ls-username">Username</label>
          <input class="ls-input" id="ls-username" name="username" type="text"
                 placeholder="Masukkan username" required autofocus
                 value="<?= e(old('username')) ?>">
        </div>

        <div class="ls-field">
          <label class="ls-label" for="ls-password">Password</label>
          <input class="ls-input" id="ls-password" name="password" type="password"
                 placeholder="••••••••" required>
        </div>

        <div class="ls-field">
          <label class="ls-label">Verifikasi Keamanan</label>
          <div class="ls-captcha-row">
            <div class="ls-field" style="margin:0">
              <input class="ls-input" name="captcha" type="text" inputmode="numeric"
                     placeholder="Jawaban Anda" required>
            </div>
            <div class="ls-captcha-box">
              <?= e(login_captcha_question()) ?> = ?
            </div>
          </div>
        </div>

        <button class="ls-btn" type="submit">Masuk ke Dashboard</button>
      </form>

      <div class="ls-footer-row">
        <a class="ls-link" href="<?= e(url('index.php?page=forgot-password')) ?>">Lupa password?</a>
        <span class="ls-demo"></span>
      </div>
    </div>
  </section>

</div>
