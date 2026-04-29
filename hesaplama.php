<?php
require __DIR__ . '/includes/db.php';
$pageTitle = 'Ağırlık Hesaplama';
$metaDesc  = 'Sac, boru, profil, lama, kösebent ve mil ağırlık hesaplama aracı. Çelik 7.85 g/cm³ yoğunluk varsayılır.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Ağırlık Hesaplama</span></nav>
    <h1>Ağırlık Hesaplama</h1>
    <p class="lead">Demir-çelik ürünleri için kg cinsinden teorik ağırlık hesabı. Varsayılan yoğunluk: <strong>çelik 7,85 g/cm³</strong>.</p>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">
    <div class="calc-tabs" role="tablist">
      <button type="button" class="calc-tab active" data-tab="sac">Sac</button>
      <button type="button" class="calc-tab" data-tab="boru">Boru</button>
      <button type="button" class="calc-tab" data-tab="profil">Kutu Profil</button>
      <button type="button" class="calc-tab" data-tab="lama">Lama</button>
      <button type="button" class="calc-tab" data-tab="kosebent">Köşebent</button>
      <button type="button" class="calc-tab" data-tab="mil">Yuvarlak Mil</button>
      <button type="button" class="calc-tab" data-tab="hasir">Hasır / Demir</button>
    </div>

    <div class="calc-density">
      <label>Yoğunluk (g/cm³):
        <select id="calcDensity">
          <option value="7.85" selected>Çelik (7,85)</option>
          <option value="7.87">Demir (7,87)</option>
          <option value="7.93">Paslanmaz (7,93)</option>
          <option value="8.92">Bakır (8,92)</option>
          <option value="2.70">Alüminyum (2,70)</option>
          <option value="8.40">Pirinç (8,40)</option>
        </select>
      </label>
    </div>

    <!-- SAC -->
    <div class="calc-pane active" data-pane="sac">
      <div class="calc-grid">
        <label>Kalınlık (mm) <input type="number" step="0.01" id="sac_t" value="3"></label>
        <label>En (mm) <input type="number" step="1" id="sac_w" value="1000"></label>
        <label>Boy (mm) <input type="number" step="1" id="sac_l" value="2000"></label>
        <label>Adet <input type="number" step="1" id="sac_q" value="1" min="1"></label>
      </div>
    </div>

    <!-- BORU -->
    <div class="calc-pane" data-pane="boru">
      <div class="calc-grid">
        <label>Dış Çap (mm) <input type="number" step="0.1" id="boru_d" value="48.3"></label>
        <label>Et Kalınlığı (mm) <input type="number" step="0.1" id="boru_t" value="3"></label>
        <label>Boy (mm) <input type="number" step="1" id="boru_l" value="6000"></label>
        <label>Adet <input type="number" step="1" id="boru_q" value="1" min="1"></label>
      </div>
    </div>

    <!-- KUTU PROFİL -->
    <div class="calc-pane" data-pane="profil">
      <div class="calc-grid">
        <label>Kenar A (mm) <input type="number" step="0.1" id="prof_a" value="40"></label>
        <label>Kenar B (mm) <input type="number" step="0.1" id="prof_b" value="40"></label>
        <label>Et Kalınlığı (mm) <input type="number" step="0.1" id="prof_t" value="2"></label>
        <label>Boy (mm) <input type="number" step="1" id="prof_l" value="6000"></label>
        <label>Adet <input type="number" step="1" id="prof_q" value="1" min="1"></label>
      </div>
      <p class="calc-note">Kare profil için A=B değerini eşit girin.</p>
    </div>

    <!-- LAMA -->
    <div class="calc-pane" data-pane="lama">
      <div class="calc-grid">
        <label>Genişlik (mm) <input type="number" step="0.1" id="lama_w" value="40"></label>
        <label>Kalınlık (mm) <input type="number" step="0.1" id="lama_t" value="5"></label>
        <label>Boy (mm) <input type="number" step="1" id="lama_l" value="6000"></label>
        <label>Adet <input type="number" step="1" id="lama_q" value="1" min="1"></label>
      </div>
    </div>

    <!-- KÖŞEBENT (eşit kollu L) -->
    <div class="calc-pane" data-pane="kosebent">
      <div class="calc-grid">
        <label>Kol A (mm) <input type="number" step="0.1" id="kos_a" value="40"></label>
        <label>Kol B (mm) <input type="number" step="0.1" id="kos_b" value="40"></label>
        <label>Et Kalınlığı (mm) <input type="number" step="0.1" id="kos_t" value="4"></label>
        <label>Boy (mm) <input type="number" step="1" id="kos_l" value="6000"></label>
        <label>Adet <input type="number" step="1" id="kos_q" value="1" min="1"></label>
      </div>
    </div>

    <!-- YUVARLAK MİL -->
    <div class="calc-pane" data-pane="mil">
      <div class="calc-grid">
        <label>Çap (mm) <input type="number" step="0.1" id="mil_d" value="20"></label>
        <label>Boy (mm) <input type="number" step="1" id="mil_l" value="6000"></label>
        <label>Adet <input type="number" step="1" id="mil_q" value="1" min="1"></label>
      </div>
    </div>

    <!-- NERVÜRLÜ DEMİR / HASIR -->
    <div class="calc-pane" data-pane="hasir">
      <div class="calc-grid">
        <label>Çap (mm) <input type="number" step="0.1" id="has_d" value="12"></label>
        <label>Boy (mm) <input type="number" step="1" id="has_l" value="12000"></label>
        <label>Adet <input type="number" step="1" id="has_q" value="1" min="1"></label>
      </div>
      <p class="calc-note">Nervürlü demir için kg/m teorik ağırlık tablosuna yakın sonuç verir.</p>
    </div>

    <button type="button" id="calcBtn" class="btn btn-primary btn-lg btn-block">Hesapla</button>

    <div id="calcResult" class="calc-result" hidden>
      <div class="calc-res-row">
        <span class="lbl">Birim Ağırlık</span>
        <strong id="resUnit">— kg</strong>
      </div>
      <div class="calc-res-row">
        <span class="lbl">Toplam Ağırlık</span>
        <strong id="resTotal" class="calc-big">— kg</strong>
      </div>
      <p class="calc-note">⚠ Bu değerler teorik hesaplamadır; gerçek ağırlık ürün toleransına göre %1-3 farklılık gösterebilir.</p>
      <a href="<?= h(whatsapp_link(settings('contact_whatsapp', '05548350226'), 'Merhaba, hesaplama sayfanızdan şu ürünü almak istiyorum:')) ?>" class="btn btn-ghost btn-block" target="_blank" rel="noopener">Hesapladığım ürün için WhatsApp'tan teklif al</a>
    </div>
  </div>
</section>

<script>
(function(){
  // sekme değişimi
  document.querySelectorAll('.calc-tab').forEach(t => {
    t.addEventListener('click', () => {
      document.querySelectorAll('.calc-tab').forEach(x => x.classList.remove('active'));
      document.querySelectorAll('.calc-pane').forEach(p => p.classList.remove('active'));
      t.classList.add('active');
      document.querySelector('[data-pane="'+t.dataset.tab+'"]').classList.add('active');
      document.getElementById('calcResult').hidden = true;
    });
  });

  function num(id){ return parseFloat(document.getElementById(id).value) || 0; }
  function fmt(v){
    return v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 3 });
  }

  document.getElementById('calcBtn').addEventListener('click', () => {
    const tab = document.querySelector('.calc-tab.active').dataset.tab;
    const rho = parseFloat(document.getElementById('calcDensity').value); // g/cm³
    let unitKg = 0, qty = 1;

    if (tab === 'sac') {
      const t = num('sac_t')/10, w = num('sac_w')/10, l = num('sac_l')/10; // cm
      qty = num('sac_q');
      unitKg = (t*w*l*rho)/1000;
    }
    else if (tab === 'boru') {
      const D = num('boru_d')/10, T = num('boru_t')/10, L = num('boru_l')/10; // cm
      qty = num('boru_q');
      const area = Math.PI * (D - T) * T; // cm²  (orta çap × et × π)
      unitKg = (area * L * rho)/1000;
    }
    else if (tab === 'profil') {
      const a = num('prof_a')/10, b = num('prof_b')/10, t = num('prof_t')/10, L = num('prof_l')/10;
      qty = num('prof_q');
      const outer = a * b;
      const inner = (a - 2*t) * (b - 2*t);
      const area = outer - Math.max(inner, 0);
      unitKg = (area * L * rho)/1000;
    }
    else if (tab === 'lama') {
      const w = num('lama_w')/10, t = num('lama_t')/10, L = num('lama_l')/10;
      qty = num('lama_q');
      unitKg = (w*t*L*rho)/1000;
    }
    else if (tab === 'kosebent') {
      const a = num('kos_a')/10, b = num('kos_b')/10, t = num('kos_t')/10, L = num('kos_l')/10;
      qty = num('kos_q');
      const area = (a + b - t) * t; // L profil yaklaşık kesit
      unitKg = (area * L * rho)/1000;
    }
    else if (tab === 'mil') {
      const d = num('mil_d')/10, L = num('mil_l')/10;
      qty = num('mil_q');
      const area = Math.PI * Math.pow(d/2, 2);
      unitKg = (area * L * rho)/1000;
    }
    else if (tab === 'hasir') {
      const d = num('has_d')/10, L = num('has_l')/10;
      qty = num('has_q');
      const area = Math.PI * Math.pow(d/2, 2);
      unitKg = (area * L * rho)/1000;
    }

    const total = unitKg * (qty || 1);
    document.getElementById('resUnit').textContent  = fmt(unitKg) + ' kg';
    document.getElementById('resTotal').textContent = fmt(total)  + ' kg';
    document.getElementById('calcResult').hidden = false;
    document.getElementById('calcResult').scrollIntoView({behavior:'smooth', block:'nearest'});
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
