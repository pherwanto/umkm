<?php
$formData = $data ?? [];
$selectedItems = $formData['items'] ?? [];
$productCategories = $productCategories ?? [];
$initialProducts = $initialProducts ?? [];
?>
<style>
.sales-topbar{display:grid;grid-template-columns:repeat(12,1fr);gap:1rem;margin-bottom:1rem}
.sales-topbar-card{grid-column:1/-1}
.sales-topbar-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:.9rem;align-items:end}
.sales-topbar-grid .field{grid-column:span 3}
.sales-topbar-grid .field-wide{grid-column:span 6}
.sales-pos-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(330px,.95fr);gap:1.25rem}
.catalog-toolbar{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap}
.catalog-search{position:relative;flex:1 1 320px}
.catalog-search .form-control,.catalog-toolbar .form-control{height:48px;border-radius:14px}
.catalog-search .form-control{padding-left:2.7rem}
.catalog-search .bi{position:absolute;left:1rem;top:50%;transform:translateY(-50%);opacity:.58}
.category-chips{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.95rem}
.category-chip{border:1px solid #d8dee8;background:#fff;color:#334155;padding:.58rem 1rem;border-radius:999px;font-weight:600;font-size:.9rem;cursor:pointer;transition:.2s ease}
.category-chip.active,.category-chip:hover{background:#0d6efd;color:#fff;border-color:#0d6efd;box-shadow:0 8px 24px rgba(13,110,253,.16)}
.product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(165px,1fr));gap:1rem;margin-top:1rem}
.product-card{position:relative;border:1px solid #e2e8f0;border-radius:18px;background:#fff;padding:.9rem;cursor:pointer;transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease;min-height:178px;display:flex;flex-direction:column;text-align:left}
.product-card:hover{transform:translateY(-2px);border-color:rgba(13,110,253,.55);box-shadow:0 14px 24px rgba(15,23,42,.08)}
.product-card.disabled{opacity:.58;cursor:not-allowed;background:#f8fafc}
.product-card-image{width:54px;height:54px;border-radius:14px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:.85rem;border:1px solid #e2e8f0}
.product-card-image img{width:100%;height:100%;object-fit:cover}
.product-card-title{font-weight:800;line-height:1.25;margin-bottom:.3rem;color:#0f172a}
.product-card-price{color:#0d6efd;font-weight:800;font-size:1.04rem;line-height:1.2}
.product-card-stock{font-size:.84rem;color:#64748b;margin-top:auto;padding-top:.5rem}
.stock-pill{position:absolute;top:.8rem;right:.8rem;padding:.22rem .56rem;border-radius:999px;font-size:.72rem;font-weight:800}
.stock-empty{background:#fee2e2;color:#991b1b}.stock-low{background:#fef3c7;color:#92400e}.stock-ok{background:#dcfce7;color:#166534}
.product-grid-empty{padding:2rem;border:1px dashed #cbd5e1;border-radius:16px;text-align:center;color:#64748b;background:#f8fafc}
.cart-card{position:sticky;top:1rem}
.cart-header{display:flex;justify-content:space-between;align-items:center;gap:.8rem;margin-bottom:1rem}
.cart-badge{background:rgba(13,110,253,.1);color:#0d6efd;border-radius:999px;padding:.22rem .78rem;font-weight:800;font-size:.84rem}
.cart-list{display:flex;flex-direction:column;gap:.9rem;max-height:50vh;overflow:auto;padding-right:.15rem}
.cart-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.75rem;padding:.9rem 0;border-bottom:1px solid #edf2f7}
.cart-item:last-child{border-bottom:none}
.cart-item-title{font-weight:800;line-height:1.25;margin-bottom:.12rem}
.cart-item-meta{font-size:.88rem;color:#64748b}
.cart-qty{display:flex;align-items:center;gap:.38rem}
.cart-qty button{width:34px;height:34px;border:none;border-radius:999px;background:#eef2ff;color:#1d4ed8;font-size:1.05rem;font-weight:800}
.cart-qty button:hover{background:#dbeafe}
.cart-qty input{width:58px;text-align:center;border-radius:12px;border:1px solid #dbe2ea;background:#fff;color:#0f172a;height:36px;padding:.2rem}
.cart-amount{display:flex;align-items:flex-start;gap:.8rem;justify-content:flex-end;white-space:nowrap}
.cart-remove{width:34px;height:34px;border:none;border-radius:999px;background:#eef2ff;color:#1d4ed8;font-size:1.05rem;font-weight:800}
.cart-remove:hover{background:#dbeafe}
.cart-summary{margin-top:1rem;padding-top:1rem;border-top:1px solid #edf2f7}
.summary-row{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.34rem 0}
.summary-total{font-size:1.12rem;font-weight:900}
.summary-inline-input{display:flex;align-items:center;gap:.6rem;justify-content:flex-end}
.summary-inline-input input,.summary-inline-input select{max-width:130px;text-align:right}
.checkout-mobile-bar{display:none}
.checkout-mobile-bar .btn{height:52px;border-radius:16px;font-weight:800}
.checkout-helper{font-size:.84rem;color:#64748b}
.lookup-wrap{position:relative}.lookup-results{position:absolute;left:0;right:0;top:calc(100% + .35rem);z-index:35;background:#fff;border:1px solid #dbe2ea;border-radius:14px;box-shadow:0 18px 40px rgba(15,23,42,.12);max-height:280px;overflow:auto;display:none}.lookup-item{padding:.8rem .95rem;border-bottom:1px solid #eef2f7;cursor:pointer}.lookup-item:last-child{border-bottom:none}.lookup-item:hover,.lookup-item.active{background:#f8fafc}.lookup-item-title{font-weight:700;color:#0f172a}.lookup-item-meta{font-size:.84rem;color:#64748b;margin-top:.1rem}
@media (max-width: 1199.98px){.sales-topbar-grid .field{grid-column:span 4}.sales-topbar-grid .field-wide{grid-column:span 8}}
@media (max-width: 991.98px){.sales-pos-layout{grid-template-columns:1fr}.cart-card{position:static}.cart-list{max-height:none}.sales-topbar-grid .field,.sales-topbar-grid .field-wide{grid-column:span 6}.checkout-mobile-bar{display:block;position:sticky;bottom:0;z-index:30;background:rgba(248,250,252,.98);backdrop-filter:blur(10px);padding:.85rem;border-top:1px solid #e2e8f0;margin:1rem -1rem -1rem}.cart-card .btn-checkout-desktop{display:none}}
@media (max-width:575.98px){.sales-topbar-grid .field,.sales-topbar-grid .field-wide{grid-column:1/-1}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.product-card{min-height:158px;padding:.82rem}.cart-item{grid-template-columns:1fr}.cart-amount{justify-content:space-between;align-items:center}}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 mobile-stack">
  <div>
    <h1 class="page-title h3 mb-1"><?= $isEdit ? 'Edit Penjualan' : 'Tambah Penjualan' ?></h1>
    <div class="text-muted">Mode kasir cepat: klik produk untuk masuk ke keranjang, atur QTT dengan tombol minus/plus, dan gunakan keyboard hanya saat jumlah besar.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=penjualan')) ?>">Kembali</a>
</div>

<form method="post" action="<?= e($formAction) ?>" id="salesForm">
  <?php if (!$isEdit): ?><input type="hidden" name="_return_url" value="<?= e(url('index.php?page=penjualan-create')) ?>"><?php endif; ?>
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

  <div class="sales-topbar">
    <div class="card enterprise-card sales-topbar-card">
      <div class="card-body p-3 p-lg-4">
        <div class="sales-topbar-grid">
          <div class="field">
            <label class="form-label">Tanggal</label>
            <input class="form-control" type="datetime-local" name="tanggal" value="<?= e($formData['tanggal'] ?? date('Y-m-d\TH:i')) ?>" required>
          </div>
          <div class="field">
            <label class="form-label">Pelanggan</label>
            <div class="lookup-wrap">
              <input type="hidden" name="pelanggan_id" id="customerIdInput" value="<?= e((string)($formData['pelanggan_id'] ?? '')) ?>">
              <input class="form-control" id="customerSearchInput" placeholder="Cari pelanggan..." autocomplete="off" value="<?= e($formData['nama_pelanggan'] ?? '') ?>">
              <div class="lookup-results" id="customerResults"></div>
            </div>
            <div class="small text-muted mt-1" id="customerSelectedInfo"><?= e(($formData['telepon_pelanggan'] ?? '') ?: 'Kosongkan untuk penjualan umum.') ?></div>
          </div>
          <div class="field">
            <label class="form-label">Jatuh Tempo</label>
            <input class="form-control" type="date" name="jatuh_tempo" value="<?= e($formData['jatuh_tempo'] ?? '') ?>">
          </div>
          <div class="field-wide">
            <label class="form-label">Keterangan</label>
            <input class="form-control" name="keterangan" value="<?= e($formData['keterangan'] ?? '') ?>" placeholder="Catatan transaksi atau informasi tambahan">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="sales-pos-layout">
    <div class="card enterprise-card">
      <div class="card-body p-4">
        <div class="section-title mb-3">Pilih Produk</div>
        <div class="catalog-toolbar">
          <div class="catalog-search">
            <i class="bi bi-search"></i>
            <input type="text" id="productSearch" class="form-control" placeholder="Cari produk..." autocomplete="off">
          </div>
          <input type="text" id="barcodeInput" class="form-control" placeholder="Scan barcode" style="max-width:220px" autocomplete="off">
        </div>
        <div class="category-chips" id="categoryChips">
          <button type="button" class="category-chip active" data-category-id="0">Semua</button>
          <?php foreach($productCategories as $cat): ?>
            <button type="button" class="category-chip" data-category-id="<?= (int)$cat['id'] ?>"><?= e($cat['nama_kategori']) ?></button>
          <?php endforeach; ?>
        </div>
        <div id="productGrid" class="product-grid"></div>
      </div>
    </div>

    <div class="card enterprise-card cart-card">
      <div class="card-body p-4">
        <div class="cart-header">
          <div>
            <div class="section-title mb-1">Keranjang</div>
            <div class="checkout-helper">Metode bayar dan nominal dibayar dikelola langsung dari panel keranjang.</div>
          </div>
          <div class="cart-badge" id="cartCount">0 item</div>
        </div>

        <div class="cart-list" id="cartList"></div>

        <div class="cart-summary">
          <div class="summary-row"><span>Subtotal</span><strong id="subtotalDisplay">Rp 0</strong></div>
          <div class="summary-row">
            <span>Diskon</span>
            <div class="summary-inline-input"><input type="number" step="1" min="0" name="diskon" id="diskonInput" class="form-control form-control-sm" value="<?= e((string)round((float)($formData['diskon'] ?? 0))) ?>"></div>
          </div>
          <div class="summary-row">
            <span>Pajak</span>
            <div class="summary-inline-input">
              <input type="hidden" name="is_pajak_enabled" value="0">
              <input class="form-check-input mt-0" type="checkbox" name="is_pajak_enabled" id="isPajakEnabled" value="1" <?= !empty($formData['is_pajak_enabled']) ? 'checked' : '' ?>>
              <input type="number" step="1" min="0" max="100" name="pajak_persen" id="pajakPersenInput" class="form-control form-control-sm" value="<?= e((string)round((float)($formData['pajak_persen'] ?? 0))) ?>" style="max-width:86px">
              <span class="small text-muted">%</span>
            </div>
          </div>
          <div class="summary-row"><span>Nilai Pajak</span><strong id="pajakDisplay">Rp 0</strong></div>
          <div class="summary-row"><span>Total</span><strong id="totalDisplay">Rp 0</strong></div>
          <div class="summary-row">
            <span>Metode Bayar</span>
            <div class="summary-inline-input"><select class="form-select form-select-sm" name="metode_pembayaran" id="metodeBayarInput"><option value="tunai" <?= ($formData['metode_pembayaran'] ?? 'tunai') === 'tunai' ? 'selected' : '' ?>>Tunai</option><option value="transfer" <?= ($formData['metode_pembayaran'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option><option value="kredit" <?= ($formData['metode_pembayaran'] ?? '') === 'kredit' ? 'selected' : '' ?>>Kredit</option></select></div>
          </div>
          <div class="summary-row">
            <span>Dibayar</span>
            <div class="summary-inline-input"><input type="number" step="1" min="0" name="dibayar" id="dibayarInput" class="form-control form-control-sm" value="<?= e((string)round((float)($formData['dibayar'] ?? 0))) ?>"></div>
          </div>
          <div class="summary-row"><span>Nilai Dibayar</span><strong id="dibayarDisplay">Rp 0</strong></div>
          <div class="summary-row summary-total"><span>Sisa</span><span id="sisaDisplay">Rp 0</span></div>
          <button type="submit" class="btn btn-success btn-checkout-desktop w-100 mt-3"><i class="bi bi-save me-1"></i>Simpan Transaksi</button>
        </div>
      </div>
      <div class="checkout-mobile-bar">
        <div class="d-flex justify-content-between align-items-center mb-2"><strong id="mobileTotalLabel">Rp 0</strong><span class="text-muted small" id="mobileCartCount">0 item</span></div>
        <button type="submit" class="btn btn-success w-100"><i class="bi bi-bag-check me-1"></i>Checkout</button>
      </div>
    </div>
  </div>

  <div id="hiddenItems"></div>
</form>

<script>
window.penjualanInitialItems = <?= json_encode(array_map(function($item){ return ['produk_id'=>(int)$item['produk_id'], 'kode_produk'=>$item['kode_produk'] ?? '', 'nama_produk'=>$item['nama_produk'] ?? '', 'harga'=>(int)round((float)$item['harga']), 'qty'=>(int)round((float)$item['qty']), 'barcode'=>$item['barcode'] ?? '', 'gambar_produk'=>$item['gambar_produk'] ?? '', 'nama_kategori'=>$item['nama_kategori'] ?? '', 'stok_minimum'=>(int)round((float)($item['stok_minimum'] ?? 0)), 'stok'=>(int)round((float)($item['stok'] ?? 0))]; }, $selectedItems), JSON_UNESCAPED_UNICODE) ?>;
window.penjualanInitialProducts = <?= json_encode($initialProducts, JSON_UNESCAPED_UNICODE) ?>;
window.penjualanSearchUrl = <?= json_encode(url('index.php?page=penjualan-product-search')) ?>;
window.penjualanBaseUrl = <?= json_encode(url('')) ?>;
window.penjualanCustomerSearchUrl = <?= json_encode(url('index.php?page=penjualan-customer-search')) ?>;
</script>
<script>
(function(){
  const initialItems = window.penjualanInitialItems || [];
  const initialProducts = window.penjualanInitialProducts || [];
  const searchUrl = window.penjualanSearchUrl;
  const baseUrl = window.penjualanBaseUrl;
  const productGrid = document.getElementById('productGrid');
  const cartList = document.getElementById('cartList');
  const hiddenItems = document.getElementById('hiddenItems');
  const productSearch = document.getElementById('productSearch');
  const barcodeInput = document.getElementById('barcodeInput');
  const categoryChips = document.getElementById('categoryChips');
  const subtotalDisplay = document.getElementById('subtotalDisplay');
  const pajakDisplay = document.getElementById('pajakDisplay');
  const totalDisplay = document.getElementById('totalDisplay');
  const dibayarDisplay = document.getElementById('dibayarDisplay');
  const sisaDisplay = document.getElementById('sisaDisplay');
  const cartCount = document.getElementById('cartCount');
  const mobileCartCount = document.getElementById('mobileCartCount');
  const mobileTotalLabel = document.getElementById('mobileTotalLabel');
  const diskonInput = document.getElementById('diskonInput');
  const isPajakEnabled = document.getElementById('isPajakEnabled');
  const pajakPersenInput = document.getElementById('pajakPersenInput');
  const dibayarInput = document.getElementById('dibayarInput');
  const metodeBayarInput = document.getElementById('metodeBayarInput');
  let dibayarAuto = <?= $isEdit ? 'false' : 'true' ?>;

  let products = initialProducts;
  let activeCategoryId = 0;
  let items = initialItems.map((item, i) => ({...item, rowKey: Date.now() + i}));
  let timer = null;

  function toInt(value){ return Math.round(Number(value || 0)); }
  function formatRupiah(number){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(number||0)); }
  function escapeHtml(str){ return String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s])); }
  function productImageUrl(product){
    if(product.gambar_produk){ const clean = String(product.gambar_produk).replace(/^\/+/, ''); return baseUrl.replace(/\/$/, '') + '/' + clean; }
    return '';
  }
  function productIcon(product){
    const name = String(product.nama_produk || '').toLowerCase();
    if(name.includes('kopi')) return 'â˜•'; if(name.includes('air') || name.includes('mineral') || name.includes('teh')) return 'ðŸ’§'; if(name.includes('roti') || name.includes('mie') || name.includes('snack')) return 'ðŸž'; if(name.includes('sabun') || name.includes('shampo') || name.includes('pasta')) return 'ðŸ§´'; if(name.includes('tisu')) return 'ðŸ§»'; if(name.includes('pulpen') || name.includes('buku')) return 'ðŸ“'; return 'ðŸ“¦';
  }
  function getStockState(product){
    const stok = toInt(product.stok || 0); const minimum = toInt(product.stok_minimum || 0);
    if(stok <= 0) return {label:'Habis', css:'stock-empty'};
    if(minimum > 0 ? stok <= minimum : stok <= 5) return {label:'Menipis', css:'stock-low'};
    return {label:'Tersedia', css:'stock-ok'};
  }
  function findCartItem(productId){ return items.find(item => Number(item.produk_id) === Number(productId)); }
  function addToCart(product){ if(toInt(product.stok || 0) <= 0) return; const existing = findCartItem(product.id); if(existing){ existing.qty = toInt(existing.qty) + 1; } else { items.push({ rowKey: Date.now() + Math.floor(Math.random()*1000), produk_id:Number(product.id), kode_produk:product.kode_produk||'', nama_produk:product.nama_produk||'', harga:Math.max(0, toInt(product.harga_jual||0)), qty:1, barcode:product.barcode||'', gambar_produk:product.gambar_produk||'', nama_kategori:product.nama_kategori||'', stok:toInt(product.stok||0), stok_minimum:toInt(product.stok_minimum||0) }); } renderCart(); }
  function updateQty(rowKey, nextQty){ items = items.reduce((acc, item) => { if(String(item.rowKey) !== String(rowKey)) { acc.push(item); return acc; } const qty = toInt(nextQty || 0); if(qty > 0){ item.qty = qty; acc.push(item); } return acc; }, []); renderCart(); }
  function removeItem(rowKey){ items = items.filter(item => String(item.rowKey) !== String(rowKey)); renderCart(); }
  function renderProducts(){ if(!products.length){ productGrid.innerHTML = '<div class="product-grid-empty">Produk tidak ditemukan.</div>'; return; } productGrid.innerHTML = products.map(product => { const imgUrl = productImageUrl(product); const imgHtml = imgUrl ? `<img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(product.nama_produk)}">` : `<span style="font-size:1.6rem">${productIcon(product)}</span>`; const stockState = getStockState(product); const disabled = toInt(product.stok || 0) <= 0; return `<button type="button" class="product-card ${disabled ? 'disabled' : ''}" data-product='${JSON.stringify(product).replace(/'/g,'&#39;')}' ${disabled ? 'disabled' : ''}><span class="stock-pill ${stockState.css}">${stockState.label}</span><div class="product-card-image">${imgHtml}</div><div class="product-card-title">${escapeHtml(product.nama_produk)}</div><div class="product-card-price">${formatRupiah(product.harga_jual)}</div><div class="product-card-stock">Stok: ${toInt(product.stok || 0)}</div></button>`; }).join(''); }
  function renderCart(){ hiddenItems.innerHTML=''; if(!items.length){ cartList.innerHTML='<div class="text-muted text-center py-4">Belum ada produk di keranjang.</div>'; } else { cartList.innerHTML = items.map(item => { item.harga = Math.max(0, toInt(item.harga)); const jumlah = item.harga * Math.max(1, toInt(item.qty)); return `<div class="cart-item"><div><div class="cart-item-title">${escapeHtml(item.nama_produk)}</div><div class="cart-item-meta">${formatRupiah(item.harga)} / pcs</div></div><div class="cart-amount"><div><div class="cart-qty"><button type="button" class="btn-qty-minus" data-row-key="${item.rowKey}">-</button><input type="number" min="1" step="1" class="qty-input" data-row-key="${item.rowKey}" value="${Math.max(1, toInt(item.qty))}"><button type="button" class="btn-qty-plus" data-row-key="${item.rowKey}">+</button></div><div class="text-end mt-2 fw-bold">${formatRupiah(jumlah)}</div></div><button type="button" class="cart-remove" data-row-key="${item.rowKey}" title="Hapus item">-</button></div></div>`; }).join(''); }
    let subtotal = 0; items.forEach((item,index)=>{ item.harga = Math.max(0, toInt(item.harga)); item.qty = Math.max(1, toInt(item.qty)); subtotal += item.harga * item.qty; hiddenItems.insertAdjacentHTML('beforeend', `<input type="hidden" name="items[${index}][produk_id]" value="${item.produk_id}"><input type="hidden" name="items[${index}][harga]" value="${item.harga}"><input type="hidden" name="items[${index}][qty]" value="${item.qty}">`); });
    subtotal = toInt(subtotal); const diskon = Math.max(0, toInt(diskonInput.value || 0));
    const dasarPajak = Math.max(0, subtotal - diskon);
    const pajakAktif = !!isPajakEnabled?.checked;
    const pajakPersen = Math.max(0, Math.min(100, toInt(pajakPersenInput?.value || 0)));
    if (pajakPersenInput) { pajakPersenInput.value = String(pajakPersen); pajakPersenInput.disabled = !pajakAktif; }
    const nilaiPajak = pajakAktif ? Math.ceil(dasarPajak * pajakPersen / 100) : 0;
    const total = toInt(dasarPajak + nilaiPajak);
    const metode = metodeBayarInput.value || 'tunai';

    if(dibayarAuto){
      dibayarInput.value = String(total);
    }

    let dibayar = Math.min(Math.max(toInt(dibayarInput.value || 0), 0), total);
    if(metode === 'kredit' && dibayar >= total && total > 0){
      dibayar = Math.max(total - 1, 0);
      dibayarInput.value = String(dibayar);
      dibayarAuto = false;
    }

    const sisa = Math.max(0, total - dibayar);
    subtotalDisplay.textContent = formatRupiah(subtotal);
    pajakDisplay.textContent = formatRupiah(nilaiPajak);
    totalDisplay.textContent = formatRupiah(total);
    dibayarDisplay.textContent = formatRupiah(dibayar);
    sisaDisplay.textContent = formatRupiah(sisa);
    cartCount.textContent = `${items.length} item`;
    mobileCartCount.textContent = `${items.length} item`;
    mobileTotalLabel.textContent = formatRupiah(total);
  }
  async function fetchProducts(q='', kategoriId=activeCategoryId){ const url = `${searchUrl}&q=${encodeURIComponent(q)}&kategori_id=${encodeURIComponent(kategoriId || 0)}`; const response = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}}); const data = await response.json(); products = data.items || []; renderProducts(); }

  productSearch.addEventListener('input', function(){ clearTimeout(timer); const q=this.value.trim(); timer=setTimeout(()=>fetchProducts(q, activeCategoryId), 220); });
  barcodeInput.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); const q=this.value.trim(); if(!q) return; fetch(`${searchUrl}&q=${encodeURIComponent(q)}&kategori_id=0`, {headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(data=>{ const found=(data.items||[])[0]; if(found){ addToCart(found); this.value=''; } }); } });
  categoryChips.addEventListener('click', function(e){ const chip=e.target.closest('[data-category-id]'); if(!chip) return; activeCategoryId = Number(chip.dataset.categoryId || 0); categoryChips.querySelectorAll('.category-chip').forEach(el => el.classList.toggle('active', el===chip)); fetchProducts(productSearch.value.trim(), activeCategoryId); });
  productGrid.addEventListener('click', function(e){ const card = e.target.closest('[data-product]'); if(!card || card.disabled) return; const product = JSON.parse(card.getAttribute('data-product').replace(/&#39;/g, "'")); addToCart(product); });
  cartList.addEventListener('click', function(e){ const minus=e.target.closest('.btn-qty-minus'); const plus=e.target.closest('.btn-qty-plus'); const remove=e.target.closest('.cart-remove'); if(minus){ const item=items.find(x => String(x.rowKey) === String(minus.dataset.rowKey)); if(item) updateQty(item.rowKey, toInt(item.qty)-1); } if(plus){ const item=items.find(x => String(x.rowKey) === String(plus.dataset.rowKey)); if(item) updateQty(item.rowKey, toInt(item.qty)+1); } if(remove){ removeItem(remove.dataset.rowKey); } });
  cartList.addEventListener('input', function(e){ const qty=e.target.closest('.qty-input'); if(!qty) return; updateQty(qty.dataset.rowKey, qty.value); });
  diskonInput.addEventListener('input', renderCart);
  isPajakEnabled?.addEventListener('change', renderCart);
  pajakPersenInput?.addEventListener('input', renderCart);
  dibayarInput.addEventListener('input', function(){ dibayarAuto = false; renderCart(); });
  metodeBayarInput.addEventListener('change', renderCart);
  renderProducts(); renderCart();

  const customerInput = document.getElementById('customerSearchInput');
  const customerResults = document.getElementById('customerResults');
  const customerIdInput = document.getElementById('customerIdInput');
  const customerInfo = document.getElementById('customerSelectedInfo');
  let customerTimer = null;
  function renderCustomerResults(items){
    if(!items.length){ customerResults.innerHTML='<div class="lookup-item"><div class="lookup-item-title">Pelanggan tidak ditemukan</div></div>'; customerResults.style.display='block'; return; }
    customerResults.innerHTML = items.map(item => `<div class="lookup-item" data-id="${item.id}" data-name="${escapeHtml(item.nama_pelanggan)}" data-meta="${escapeHtml(((item.telepon || '') + ' ' + (item.alamat || '')).trim())}"><div class="lookup-item-title">${escapeHtml(item.nama_pelanggan)}</div><div class="lookup-item-meta">${escapeHtml(((item.telepon || '-') + ' • ' + (item.alamat || '-')))}</div></div>`).join('');
    customerResults.style.display='block';
  }
  async function searchCustomer(q=''){
    const resp = await fetch(window.penjualanCustomerSearchUrl + '&q=' + encodeURIComponent(q), {headers:{'X-Requested-With':'XMLHttpRequest'}});
    const data = await resp.json();
    renderCustomerResults(data.items || []);
  }
  customerInput?.addEventListener('input', function(){
    customerIdInput.value = '';
    customerInfo.textContent = 'Kosongkan untuk penjualan umum.';
    clearTimeout(customerTimer);
    customerTimer = setTimeout(() => searchCustomer(this.value.trim()), 220);
  });
  customerInput?.addEventListener('focus', function(){ searchCustomer(this.value.trim()); });
  customerResults?.addEventListener('click', function(e){
    const item = e.target.closest('.lookup-item[data-id]');
    if(!item) return;
    customerIdInput.value = item.dataset.id || '';
    customerInput.value = item.dataset.name || '';
    customerInfo.textContent = item.dataset.meta || '';
    customerResults.style.display='none';
  });
  document.addEventListener('click', function(e){
    if(!e.target.closest('.lookup-wrap')) customerResults.style.display='none';
  });
})();
</script>


