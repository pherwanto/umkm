<?php
$formData = $data ?? [];
$isEdit = $isEdit ?? false;
$productMap = [];
foreach (($produk ?? []) as $p) {
    $productMap[$p['id']] = [
        'id' => (int)$p['id'],
        'kode_produk' => $p['kode_produk'],
        'nama_produk' => $p['nama_produk'],
        'harga_beli' => (int)round((float)$p['harga_beli']),
        'stok' => (int)round((float)$p['stok']),
    ];
}
$selectedSupplierId = (string)($formData['supplier_id'] ?? '');
$selectedSupplierName = '';
$selectedSupplierInfo = '';
foreach (($supplier ?? []) as $s) {
    if ((string)$s['id'] === $selectedSupplierId) {
        $selectedSupplierName = $s['nama_supplier'];
        $selectedSupplierInfo = trim(($s['telepon'] ?? '') . ' ' . ($s['alamat'] ?? ''));
        break;
    }
}
$initialItems = [];
foreach (($formData['items'] ?? []) as $it) {
    $initialItems[] = [
        'rowKey' => 'row_' . $it['produk_id'] . '_' . uniqid(),
        'produk_id' => (int)$it['produk_id'],
        'kode_produk' => $it['kode_produk'] ?? '',
        'nama_produk' => $it['nama_produk'] ?? '',
        'harga' => (int)round((float)$it['harga']),
        'qty' => (int)round((float)$it['qty']),
    ];
}
?>
<style>
.lookup-wrap{position:relative}
.lookup-results{position:absolute;left:0;right:0;top:calc(100% + .35rem);z-index:35;background:#fff;border:1px solid #dbe2ea;border-radius:14px;box-shadow:0 18px 40px rgba(15,23,42,.12);max-height:280px;overflow:auto;display:none}
.lookup-item{padding:.8rem .95rem;border-bottom:1px solid #eef2f7;cursor:pointer}
.lookup-item:last-child{border-bottom:none}
.lookup-item:hover,.lookup-item.active{background:#f8fafc}
.lookup-item-title{font-weight:700;color:#0f172a}
.lookup-item-meta{font-size:.84rem;color:#64748b;margin-top:.1rem}
</style>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="page-title h3 mb-1"><?= $isEdit ? 'Edit Pembelian' : 'Tambah Pembelian' ?></h1>
    <div class="text-muted">Form pembelian dibuat sederhana: cari supplier cepat, tambah item, lalu simpan dan cetak bukti pembelian.</div>
  </div>
  <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian')) ?>">Kembali</a>
</div>

<form method="post" action="<?= e($formAction ?? url('index.php?page=pembelian-create')) ?>" id="purchaseForm" class="sales-form-grid">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="supplier_id" id="supplierIdInput" value="<?= e($selectedSupplierId) ?>">

  <section class="enterprise-card p-3 p-lg-4">
    <div class="section-title mb-3">Informasi Pembelian</div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Tanggal</label>
        <input class="form-control" type="datetime-local" name="tanggal" value="<?= e($formData['tanggal'] ?? date('Y-m-d\TH:i')) ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">Supplier</label>
        <div class="lookup-wrap">
          <input class="form-control" id="supplierSearchInput" placeholder="Cari supplier..." value="<?= e($selectedSupplierName) ?>" autocomplete="off">
          <div class="lookup-results" id="supplierResults"></div>
        </div>
        <div class="small text-muted mt-1" id="supplierSelectedInfo"><?= e($selectedSupplierInfo ?: 'Ketik nama supplier untuk mencari lebih cepat.') ?></div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Jatuh Tempo</label>
        <input class="form-control" type="date" name="jatuh_tempo" value="<?= e($formData['jatuh_tempo'] ?? '') ?>">
      </div>
    </div>

    <div class="section-title mt-4 mb-3">Tambah Item</div>
    <div class="row g-3 align-items-end">
      <div class="col-lg-6">
        <label class="form-label">Produk</label>
        <select class="form-select" id="itemProduk">
          <option value="">Pilih produk</option>
          <?php foreach (($produk ?? []) as $p): ?>
            <option value="<?= e($p['id']) ?>"><?= e($p['kode_produk'] . ' - ' . $p['nama_produk']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-4">
        <label class="form-label">Qty</label>
        <input class="form-control" id="itemQty" type="number" step="1" min="1" value="1">
      </div>
      <div class="col-lg-2 col-md-3">
        <label class="form-label">Harga Beli</label>
        <input class="form-control" id="itemHarga" type="number" step="1" min="0" value="0">
      </div>
      <div class="col-lg-2 col-md-5 d-grid">
        <button type="button" class="btn btn-primary" id="btnTambahItem"><i class="bi bi-plus-circle me-1"></i>Tambah Item</button>
      </div>
    </div>

    <div class="table-responsive mt-4">
      <table class="table align-middle" id="tablePembelianItems">
        <thead>
          <tr>
            <th width="60">No</th>
            <th>Produk</th>
            <th class="text-end">Harga Beli</th>
            <th class="text-center">QTY</th>
            <th class="text-end">Jumlah</th>
            <th class="text-end" width="120">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div id="hiddenItems"></div>
  </section>

  <aside class="enterprise-card p-3 p-lg-4 action-sticky">
    <div class="section-title mb-3">Ringkasan</div>
    <div class="summary-box mb-3">
      <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><strong id="subtotalDisplay">Rp 0</strong></div>
      <div class="d-flex justify-content-between mb-2"><span class="text-muted">Diskon</span><strong id="diskonDisplay">Rp 0</strong></div>
      <div class="d-flex justify-content-between"><span class="text-muted">Total</span><strong id="totalDisplay">Rp 0</strong></div>
    </div>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Diskon</label>
        <input class="form-control" type="number" step="1" min="0" name="diskon" id="diskonInput" value="<?= e((string)round((float)($formData['diskon'] ?? 0))) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Dibayar</label>
        <input class="form-control" type="number" step="1" min="0" name="dibayar" id="dibayarInput" value="<?= e((string)round((float)($formData['dibayar'] ?? 0))) ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Metode Bayar</label>
        <select class="form-select" name="metode_pembayaran" id="metodeBayarInput">
          <option value="tunai" <?= (($formData['metode_pembayaran'] ?? 'tunai')==='tunai')?'selected':'' ?>>Tunai</option>
          <option value="transfer" <?= (($formData['metode_pembayaran'] ?? '')==='transfer')?'selected':'' ?>>Transfer</option>
          <option value="hutang" <?= (($formData['metode_pembayaran'] ?? '')==='hutang')?'selected':'' ?>>Hutang</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Keterangan</label>
        <textarea class="form-control" rows="3" name="keterangan" placeholder="Catatan pembelian"><?= e($formData['keterangan'] ?? '') ?></textarea>
      </div>
      <div class="col-12 d-grid gap-2">
        <button class="btn btn-primary btn-lg"><i class="bi bi-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pembelian' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('index.php?page=pembelian')) ?>">Batal</a>
      </div>
    </div>
  </aside>
</form>

<script>
window.pembelianProducts = <?= json_encode($productMap, JSON_UNESCAPED_UNICODE) ?>;
window.pembelianInitialItems = <?= json_encode($initialItems, JSON_UNESCAPED_UNICODE) ?>;
window.pembelianSupplierSearchUrl = <?= json_encode(url('index.php?page=pembelian-supplier-search')) ?>;
(function(){
  const products = window.pembelianProducts || {};
  const productSelect = document.getElementById('itemProduk');
  const qtyInput = document.getElementById('itemQty');
  const hargaInput = document.getElementById('itemHarga');
  const btnTambah = document.getElementById('btnTambahItem');
  const tbody = document.querySelector('#tablePembelianItems tbody');
  const hiddenItems = document.getElementById('hiddenItems');
  const subtotalDisplay = document.getElementById('subtotalDisplay');
  const diskonDisplay = document.getElementById('diskonDisplay');
  const totalDisplay = document.getElementById('totalDisplay');
  const diskonInput = document.getElementById('diskonInput');
  const dibayarInput = document.getElementById('dibayarInput');
  const metodeBayarInput = document.getElementById('metodeBayarInput');
  let dibayarAuto = <?= $isEdit ? 'false' : 'true' ?>;
  let rows = Array.isArray(window.pembelianInitialItems) ? window.pembelianInitialItems : [];

  function toInt(v){ return Math.round(Number(v || 0)); }
  function rupiah(v){ return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(v||0)); }
  function resetItemForm(){ productSelect.value=''; qtyInput.value='1'; hargaInput.value='0'; }
  function reindex(){
    hiddenItems.innerHTML='';
    tbody.innerHTML='';
    let subtotal = 0;
    if(!rows.length){ tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-5">Belum ada item pembelian.</td></tr>'; }
    rows.forEach((row, idx) => {
      row.qty = Math.max(1, toInt(row.qty));
      row.harga = Math.max(0, toInt(row.harga));
      const jumlah = row.qty * row.harga;
      subtotal += jumlah;
      tbody.insertAdjacentHTML('beforeend', `<tr>
        <td>${idx+1}</td>
        <td><div class="fw-semibold">${row.nama_produk}</div><div class="small text-muted">${row.kode_produk || '-'}</div></td>
        <td class="text-end">${rupiah(row.harga)}</td>
        <td class="text-center">${row.qty}</td>
        <td class="text-end fw-semibold">${rupiah(jumlah)}</td>
        <td class="text-end">
          <button type="button" class="btn btn-outline-primary btn-sm me-1" data-edit="${row.rowKey}"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn btn-outline-danger btn-sm" data-delete="${row.rowKey}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`);
      hiddenItems.insertAdjacentHTML('beforeend', `<input type="hidden" name="items[${idx}][produk_id]" value="${row.produk_id}"><input type="hidden" name="items[${idx}][harga]" value="${row.harga}"><input type="hidden" name="items[${idx}][qty]" value="${row.qty}">`);
    });
    subtotal = toInt(subtotal);
    const diskon = Math.max(0, toInt(diskonInput.value || 0));
    const total = Math.max(0, toInt(subtotal - diskon));
    if (dibayarAuto) {
      dibayarInput.value = String(total);
    }
    if (metodeBayarInput.value === 'hutang' && toInt(dibayarInput.value || 0) >= total && total > 0) {
      dibayarInput.value = String(Math.max(0, total - 1));
      dibayarAuto = false;
    }
    subtotalDisplay.textContent = rupiah(subtotal);
    diskonDisplay.textContent = rupiah(diskon);
    totalDisplay.textContent = rupiah(total);
  }
  productSelect.addEventListener('change', () => {
    const p = products[productSelect.value];
    hargaInput.value = p ? p.harga_beli : 0;
  });
  btnTambah.addEventListener('click', () => {
    const id = productSelect.value;
    if(!id || !products[id]){ alert('Pilih produk terlebih dahulu.'); return; }
    const qty = toInt(qtyInput.value || 0);
    const harga = toInt(hargaInput.value || 0);
    if(qty <= 0){ alert('Qty harus lebih dari 0.'); return; }
    if(harga < 0){ alert('Harga tidak valid.'); return; }
    rows.push({ rowKey: 'row_' + Date.now() + '_' + Math.floor(Math.random()*1000), produk_id: Number(id), nama_produk: products[id].nama_produk, kode_produk: products[id].kode_produk, qty: Math.max(1, qty), harga: Math.max(0, harga) });
    resetItemForm();
    reindex();
    productSelect.focus();
  });
  tbody.addEventListener('click', (e) => {
    const del = e.target.closest('[data-delete]');
    const edit = e.target.closest('[data-edit]');
    if(del){ rows = rows.filter(row => String(row.rowKey) !== String(del.dataset.delete)); reindex(); }
    if(edit){ const item = rows.find(row => String(row.rowKey) === String(edit.dataset.edit)); if(!item) return; productSelect.value = item.produk_id; qtyInput.value = item.qty; hargaInput.value = item.harga; rows = rows.filter(row => String(row.rowKey) !== String(item.rowKey)); reindex(); productSelect.focus(); }
  });
  diskonInput.addEventListener('input', reindex);
  dibayarInput.addEventListener('input', () => { dibayarAuto = false; reindex(); });
  metodeBayarInput.addEventListener('change', reindex);
  reindex();

  const supplierInput = document.getElementById('supplierSearchInput');
  const supplierResults = document.getElementById('supplierResults');
  const supplierIdInput = document.getElementById('supplierIdInput');
  const supplierInfo = document.getElementById('supplierSelectedInfo');
  let supplierTimer = null;
  function escapeHtml(str){ return String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s])); }
  function renderSupplierResults(items){
    if(!items.length){ supplierResults.innerHTML = '<div class="lookup-item"><div class="lookup-item-title">Supplier tidak ditemukan</div></div>'; supplierResults.style.display='block'; return; }
    supplierResults.innerHTML = items.map(item => `<div class="lookup-item" data-id="${item.id}" data-name="${escapeHtml(item.nama_supplier)}" data-meta="${escapeHtml(((item.telepon || '') + ' ' + (item.alamat || '')).trim())}"><div class="lookup-item-title">${escapeHtml(item.nama_supplier)}</div><div class="lookup-item-meta">${escapeHtml(((item.telepon || '-') + ' • ' + (item.alamat || '-')))}</div></div>`).join('');
    supplierResults.style.display='block';
  }
  async function searchSupplier(q=''){
    const resp = await fetch(window.pembelianSupplierSearchUrl + '&q=' + encodeURIComponent(q), {headers:{'X-Requested-With':'XMLHttpRequest'}});
    const data = await resp.json();
    renderSupplierResults(data.items || []);
  }
  supplierInput?.addEventListener('input', function(){
    supplierIdInput.value = '';
    supplierInfo.textContent = 'Ketik nama supplier untuk mencari lebih cepat.';
    clearTimeout(supplierTimer);
    supplierTimer = setTimeout(() => searchSupplier(this.value.trim()), 220);
  });
  supplierInput?.addEventListener('focus', function(){ searchSupplier(this.value.trim()); });
  supplierResults?.addEventListener('click', function(e){
    const item = e.target.closest('.lookup-item[data-id]');
    if(!item) return;
    supplierIdInput.value = item.dataset.id || '';
    supplierInput.value = item.dataset.name || '';
    supplierInfo.textContent = item.dataset.meta || '';
    supplierResults.style.display='none';
  });
  document.addEventListener('click', function(e){
    if(!e.target.closest('.lookup-wrap')) supplierResults.style.display='none';
  });
})();
</script>
