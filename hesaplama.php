<?php
require __DIR__ . '/includes/db.php';
$pageTitle = 'Ağırlık Hesaplama';
$metaDesc  = 'Demir-çelik ürünleri için kapsamlı ağırlık hesaplama: sac, boru, profil, lama, köşebent, yuvarlak/kare mil, HEA/HEB/IPE, NPU/NPI, T profili, Z profili, çelik hasır, nervürlü demir.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a>
      <span>›</span>
      <span>Ağırlık Hesaplama</span>
    </nav>
    <h1>Ağırlık Hesaplama</h1>
    <p class="lead">Demir-çelik ürünleri için profesyonel teorik ağırlık hesabı. 14 ürün grubu, malzeme yoğunluk kütüphanesi, çoklu kalem listesi ve özel ölçü desteği.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <!-- Üst Kontrol Çubuğu -->
    <div class="calc-toolbar">
      <div class="calc-density-block">
        <label>
          <span class="calc-label">Malzeme</span>
          <select id="calcDensity" class="calc-select">
            <optgroup label="Karbon Çelik">
              <option value="7.85" selected>Çelik (Genel) — 7,85 g/cm³</option>
              <option value="7.85">ST37 (S235JR) — 7,85</option>
              <option value="7.85">ST44 (S275JR) — 7,85</option>
              <option value="7.85">ST52 (S355JR) — 7,85</option>
              <option value="7.87">Demir / Düşük Karbon — 7,87</option>
              <option value="7.85">DKP Sac — 7,85</option>
              <option value="7.85">HRP Sac — 7,85</option>
              <option value="7.85">Galvanizli Sac — 7,85</option>
            </optgroup>
            <optgroup label="Paslanmaz">
              <option value="7.93">AISI 304 — 7,93</option>
              <option value="8.00">AISI 316 — 8,00</option>
              <option value="7.75">AISI 410 — 7,75</option>
              <option value="7.70">AISI 430 — 7,70</option>
            </optgroup>
            <optgroup label="Demir Dışı">
              <option value="2.70">Alüminyum (Saf) — 2,70</option>
              <option value="2.78">Alüminyum 5083 — 2,66</option>
              <option value="8.96">Bakır — 8,96</option>
              <option value="8.40">Pirinç (60/40) — 8,40</option>
              <option value="8.73">Bronz — 8,73</option>
              <option value="11.34">Kurşun — 11,34</option>
              <option value="7.13">Çinko — 7,13</option>
            </optgroup>
            <optgroup label="Diğer">
              <option value="custom">Özel değer gir...</option>
            </optgroup>
          </select>
        </label>
        <input type="number" id="customDensity" class="calc-input calc-custom-density" step="0.01" placeholder="g/cm³" hidden>
      </div>

      <div class="calc-print-actions">
        <button type="button" class="calc-btn-ghost" id="calcReset" title="Hesaplamayı sıfırla">↺ Sıfırla</button>
        <button type="button" class="calc-btn-ghost" id="calcPrint" title="Yazdır / PDF">🖨 Yazdır</button>
      </div>
    </div>

    <!-- Sekme Çubuğu (kategoriler) -->
    <div class="calc-tabs" role="tablist">
      <button type="button" class="calc-tab active" data-tab="sac" title="Sac (DKP, HRP, ST52, Galvanizli)">
        <span class="ico">▭</span> Sac
      </button>
      <button type="button" class="calc-tab" data-tab="boru" title="Yuvarlak boru (su, kazan, konstrüksiyon)">
        <span class="ico">○</span> Yuvarlak Boru
      </button>
      <button type="button" class="calc-tab" data-tab="profil" title="Kare ve dikdörtgen kutu profil">
        <span class="ico">□</span> Kutu Profil
      </button>
      <button type="button" class="calc-tab" data-tab="lama" title="Yatay düz lama / silme">
        <span class="ico">▬</span> Lama
      </button>
      <button type="button" class="calc-tab" data-tab="kosebent" title="L profil — eşit veya eşit olmayan kollu">
        <span class="ico">L</span> Köşebent
      </button>
      <button type="button" class="calc-tab" data-tab="mil" title="Yuvarlak dolu mil (silindirik)">
        <span class="ico">●</span> Yuvarlak Mil
      </button>
      <button type="button" class="calc-tab" data-tab="kare-mil" title="Kare dolu mil">
        <span class="ico">■</span> Kare Mil
      </button>
      <button type="button" class="calc-tab" data-tab="altigen" title="Altıgen mil — anahtar ağzı">
        <span class="ico">⬡</span> Altıgen Mil
      </button>
      <button type="button" class="calc-tab" data-tab="hea-heb" title="HEA, HEB, IPE, IPN profilleri">
        <span class="ico">I</span> H/I Profil
      </button>
      <button type="button" class="calc-tab" data-tab="npu-npi" title="NPU, NPI U/Channel profilleri">
        <span class="ico">U</span> U Profil (NPU)
      </button>
      <button type="button" class="calc-tab" data-tab="t-profil" title="T profili">
        <span class="ico">T</span> T Profil
      </button>
      <button type="button" class="calc-tab" data-tab="oval" title="Oval / eliptik profil">
        <span class="ico">⬭</span> Oval Profil
      </button>
      <button type="button" class="calc-tab" data-tab="hasir" title="Çelik hasır — Q ve R tipi">
        <span class="ico">⊞</span> Çelik Hasır
      </button>
      <button type="button" class="calc-tab" data-tab="nervurlu" title="Nervürlü inşaat demiri">
        <span class="ico">⌇</span> Nervürlü Demir
      </button>
    </div>

    <div class="calc-body">

      <!-- ═══════════════ SAC ═══════════════ -->
      <div class="calc-pane active" data-pane="sac">
        <div class="calc-pane-head">
          <h3>Sac Levha</h3>
          <p>Düz sac levha için <strong>en × boy × kalınlık × yoğunluk</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Kalınlık (mm)
            <input type="number" step="0.01" id="sac_t" value="3">
          </label>
          <label>En (mm)
            <input type="number" step="1" id="sac_w" value="1500">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="sac_l" value="3000">
          </label>
          <label>Adet
            <input type="number" step="1" id="sac_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Hızlı seçim:</span>
          <button type="button" class="preset-btn" data-set="sac_t:1.5,sac_w:1000,sac_l:2000">1,5 × 1000 × 2000</button>
          <button type="button" class="preset-btn" data-set="sac_t:2,sac_w:1250,sac_l:2500">2 × 1250 × 2500</button>
          <button type="button" class="preset-btn" data-set="sac_t:3,sac_w:1500,sac_l:3000">3 × 1500 × 3000</button>
          <button type="button" class="preset-btn" data-set="sac_t:5,sac_w:1500,sac_l:3000">5 × 1500 × 3000</button>
          <button type="button" class="preset-btn" data-set="sac_t:8,sac_w:1500,sac_l:6000">8 × 1500 × 6000</button>
          <button type="button" class="preset-btn" data-set="sac_t:10,sac_w:2000,sac_l:6000">10 × 2000 × 6000</button>
        </div>
      </div>

      <!-- ═══════════════ YUVARLAK BORU ═══════════════ -->
      <div class="calc-pane" data-pane="boru">
        <div class="calc-pane-head">
          <h3>Yuvarlak Boru (Dikişli / Dikişsiz)</h3>
          <p>Su borusu, kazan borusu, konstrüksiyon borusu için <strong>π × (D − t) × t × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Dış Çap D (mm)
            <input type="number" step="0.1" id="boru_d" value="48.3">
          </label>
          <label>Et Kalınlığı t (mm)
            <input type="number" step="0.1" id="boru_t" value="3">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="boru_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="boru_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">TS 301-2 standart ölçüler:</span>
          <button type="button" class="preset-btn" data-set="boru_d:21.3,boru_t:2.6">1/2" — 21,3×2,6</button>
          <button type="button" class="preset-btn" data-set="boru_d:26.9,boru_t:2.6">3/4" — 26,9×2,6</button>
          <button type="button" class="preset-btn" data-set="boru_d:33.7,boru_t:3.2">1" — 33,7×3,2</button>
          <button type="button" class="preset-btn" data-set="boru_d:42.4,boru_t:3.2">1¼" — 42,4×3,2</button>
          <button type="button" class="preset-btn" data-set="boru_d:48.3,boru_t:3.2">1½" — 48,3×3,2</button>
          <button type="button" class="preset-btn" data-set="boru_d:60.3,boru_t:3.6">2" — 60,3×3,6</button>
          <button type="button" class="preset-btn" data-set="boru_d:88.9,boru_t:4">3" — 88,9×4</button>
          <button type="button" class="preset-btn" data-set="boru_d:114.3,boru_t:4.5">4" — 114,3×4,5</button>
        </div>
      </div>

      <!-- ═══════════════ KUTU PROFİL ═══════════════ -->
      <div class="calc-pane" data-pane="profil">
        <div class="calc-pane-head">
          <h3>Kare / Dikdörtgen Kutu Profil</h3>
          <p>Kare profilde A=B girin. Hesap: <strong>(A·B − (A−2t)(B−2t)) × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Kenar A (mm)
            <input type="number" step="0.1" id="prof_a" value="40">
          </label>
          <label>Kenar B (mm)
            <input type="number" step="0.1" id="prof_b" value="40">
          </label>
          <label>Et Kalınlığı (mm)
            <input type="number" step="0.1" id="prof_t" value="2">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="prof_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="prof_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Yaygın ölçüler:</span>
          <button type="button" class="preset-btn" data-set="prof_a:20,prof_b:20,prof_t:1.5">20×20×1,5</button>
          <button type="button" class="preset-btn" data-set="prof_a:25,prof_b:25,prof_t:2">25×25×2</button>
          <button type="button" class="preset-btn" data-set="prof_a:30,prof_b:30,prof_t:2">30×30×2</button>
          <button type="button" class="preset-btn" data-set="prof_a:40,prof_b:40,prof_t:2">40×40×2</button>
          <button type="button" class="preset-btn" data-set="prof_a:50,prof_b:30,prof_t:2">50×30×2</button>
          <button type="button" class="preset-btn" data-set="prof_a:60,prof_b:40,prof_t:3">60×40×3</button>
          <button type="button" class="preset-btn" data-set="prof_a:80,prof_b:80,prof_t:3">80×80×3</button>
          <button type="button" class="preset-btn" data-set="prof_a:100,prof_b:100,prof_t:4">100×100×4</button>
          <button type="button" class="preset-btn" data-set="prof_a:120,prof_b:60,prof_t:4">120×60×4</button>
        </div>
      </div>

      <!-- ═══════════════ LAMA ═══════════════ -->
      <div class="calc-pane" data-pane="lama">
        <div class="calc-pane-head">
          <h3>Lama / Düz Çubuk</h3>
          <p>Yatay kesitli düz çelik. Hesap: <strong>w × t × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Genişlik (mm)
            <input type="number" step="0.1" id="lama_w" value="40">
          </label>
          <label>Kalınlık (mm)
            <input type="number" step="0.1" id="lama_t" value="5">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="lama_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="lama_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Standart ölçüler:</span>
          <button type="button" class="preset-btn" data-set="lama_w:20,lama_t:3">20×3</button>
          <button type="button" class="preset-btn" data-set="lama_w:25,lama_t:5">25×5</button>
          <button type="button" class="preset-btn" data-set="lama_w:30,lama_t:5">30×5</button>
          <button type="button" class="preset-btn" data-set="lama_w:40,lama_t:6">40×6</button>
          <button type="button" class="preset-btn" data-set="lama_w:50,lama_t:8">50×8</button>
          <button type="button" class="preset-btn" data-set="lama_w:60,lama_t:10">60×10</button>
          <button type="button" class="preset-btn" data-set="lama_w:80,lama_t:10">80×10</button>
          <button type="button" class="preset-btn" data-set="lama_w:100,lama_t:12">100×12</button>
        </div>
      </div>

      <!-- ═══════════════ KÖŞEBENT (L PROFİL) ═══════════════ -->
      <div class="calc-pane" data-pane="kosebent">
        <div class="calc-pane-head">
          <h3>Köşebent (L Profil)</h3>
          <p>Eşit veya eşit olmayan kollu L profil. Hesap: <strong>(A + B − t) × t × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Kol A (mm)
            <input type="number" step="0.1" id="kos_a" value="40">
          </label>
          <label>Kol B (mm)
            <input type="number" step="0.1" id="kos_b" value="40">
          </label>
          <label>Et Kalınlığı (mm)
            <input type="number" step="0.1" id="kos_t" value="4">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="kos_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="kos_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Eşit kollu (L):</span>
          <button type="button" class="preset-btn" data-set="kos_a:20,kos_b:20,kos_t:3">20×20×3</button>
          <button type="button" class="preset-btn" data-set="kos_a:30,kos_b:30,kos_t:3">30×30×3</button>
          <button type="button" class="preset-btn" data-set="kos_a:40,kos_b:40,kos_t:4">40×40×4</button>
          <button type="button" class="preset-btn" data-set="kos_a:50,kos_b:50,kos_t:5">50×50×5</button>
          <button type="button" class="preset-btn" data-set="kos_a:60,kos_b:60,kos_t:6">60×60×6</button>
          <button type="button" class="preset-btn" data-set="kos_a:80,kos_b:80,kos_t:8">80×80×8</button>
          <button type="button" class="preset-btn" data-set="kos_a:100,kos_b:100,kos_t:10">100×100×10</button>
        </div>
      </div>

      <!-- ═══════════════ YUVARLAK MİL ═══════════════ -->
      <div class="calc-pane" data-pane="mil">
        <div class="calc-pane-head">
          <h3>Yuvarlak Dolu Mil</h3>
          <p>Silindirik dolu çelik. Hesap: <strong>π × (D/2)² × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Çap (mm)
            <input type="number" step="0.1" id="mil_d" value="20">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="mil_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="mil_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Yaygın çaplar:</span>
          <button type="button" class="preset-btn" data-set="mil_d:8">Ø8</button>
          <button type="button" class="preset-btn" data-set="mil_d:10">Ø10</button>
          <button type="button" class="preset-btn" data-set="mil_d:12">Ø12</button>
          <button type="button" class="preset-btn" data-set="mil_d:16">Ø16</button>
          <button type="button" class="preset-btn" data-set="mil_d:20">Ø20</button>
          <button type="button" class="preset-btn" data-set="mil_d:25">Ø25</button>
          <button type="button" class="preset-btn" data-set="mil_d:30">Ø30</button>
          <button type="button" class="preset-btn" data-set="mil_d:40">Ø40</button>
          <button type="button" class="preset-btn" data-set="mil_d:50">Ø50</button>
          <button type="button" class="preset-btn" data-set="mil_d:60">Ø60</button>
          <button type="button" class="preset-btn" data-set="mil_d:80">Ø80</button>
          <button type="button" class="preset-btn" data-set="mil_d:100">Ø100</button>
        </div>
      </div>

      <!-- ═══════════════ KARE MİL ═══════════════ -->
      <div class="calc-pane" data-pane="kare-mil">
        <div class="calc-pane-head">
          <h3>Kare Dolu Mil</h3>
          <p>Kare kesitli dolu çelik. Hesap: <strong>a² × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Kenar a (mm)
            <input type="number" step="0.1" id="kare_a" value="20">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="kare_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="kare_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Standart ölçüler:</span>
          <button type="button" class="preset-btn" data-set="kare_a:8">8×8</button>
          <button type="button" class="preset-btn" data-set="kare_a:10">10×10</button>
          <button type="button" class="preset-btn" data-set="kare_a:12">12×12</button>
          <button type="button" class="preset-btn" data-set="kare_a:14">14×14</button>
          <button type="button" class="preset-btn" data-set="kare_a:16">16×16</button>
          <button type="button" class="preset-btn" data-set="kare_a:20">20×20</button>
          <button type="button" class="preset-btn" data-set="kare_a:25">25×25</button>
          <button type="button" class="preset-btn" data-set="kare_a:30">30×30</button>
          <button type="button" class="preset-btn" data-set="kare_a:40">40×40</button>
          <button type="button" class="preset-btn" data-set="kare_a:50">50×50</button>
        </div>
      </div>

      <!-- ═══════════════ ALTIGEN MİL ═══════════════ -->
      <div class="calc-pane" data-pane="altigen">
        <div class="calc-pane-head">
          <h3>Altıgen Dolu Mil (Hexagonal)</h3>
          <p>Anahtar ağzı (s) ölçüsünden. Hesap: <strong>(√3/2) × s² × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Anahtar Ağzı s (mm)
            <input type="number" step="0.1" id="hex_s" value="22">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="hex_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="hex_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Yaygın ölçüler (anahtar ağzı):</span>
          <button type="button" class="preset-btn" data-set="hex_s:13">S=13</button>
          <button type="button" class="preset-btn" data-set="hex_s:17">S=17</button>
          <button type="button" class="preset-btn" data-set="hex_s:19">S=19</button>
          <button type="button" class="preset-btn" data-set="hex_s:22">S=22</button>
          <button type="button" class="preset-btn" data-set="hex_s:24">S=24</button>
          <button type="button" class="preset-btn" data-set="hex_s:27">S=27</button>
          <button type="button" class="preset-btn" data-set="hex_s:30">S=30</button>
          <button type="button" class="preset-btn" data-set="hex_s:36">S=36</button>
          <button type="button" class="preset-btn" data-set="hex_s:41">S=41</button>
          <button type="button" class="preset-btn" data-set="hex_s:46">S=46</button>
        </div>
      </div>

      <!-- ═══════════════ HEA / HEB / IPE / IPN PROFİL ═══════════════ -->
      <div class="calc-pane" data-pane="hea-heb">
        <div class="calc-pane-head">
          <h3>HEA / HEB / IPE / IPN Profil (I Beam)</h3>
          <p>Avrupa standardı I kesitli profil. Aşağıdaki listeden seçin — kg/m değeri standart tablosundan alınır.</p>
        </div>
        <div class="calc-grid">
          <label>Profil Tipi ve Ölçü
            <select id="ibeam_size" class="calc-select-full">
              <optgroup label="HEA — Geniş Başlıklı (Hafif)">
                <option value="HEA100|16.7">HEA 100 — 16,7 kg/m</option>
                <option value="HEA120|19.9">HEA 120 — 19,9 kg/m</option>
                <option value="HEA140|24.7">HEA 140 — 24,7 kg/m</option>
                <option value="HEA160|30.4">HEA 160 — 30,4 kg/m</option>
                <option value="HEA180|35.5">HEA 180 — 35,5 kg/m</option>
                <option value="HEA200|42.3" selected>HEA 200 — 42,3 kg/m</option>
                <option value="HEA220|50.5">HEA 220 — 50,5 kg/m</option>
                <option value="HEA240|60.3">HEA 240 — 60,3 kg/m</option>
                <option value="HEA260|68.2">HEA 260 — 68,2 kg/m</option>
                <option value="HEA280|76.4">HEA 280 — 76,4 kg/m</option>
                <option value="HEA300|88.3">HEA 300 — 88,3 kg/m</option>
                <option value="HEA320|97.6">HEA 320 — 97,6 kg/m</option>
                <option value="HEA340|105">HEA 340 — 105 kg/m</option>
                <option value="HEA360|112">HEA 360 — 112 kg/m</option>
                <option value="HEA400|125">HEA 400 — 125 kg/m</option>
                <option value="HEA450|140">HEA 450 — 140 kg/m</option>
                <option value="HEA500|155">HEA 500 — 155 kg/m</option>
                <option value="HEA550|166">HEA 550 — 166 kg/m</option>
                <option value="HEA600|178">HEA 600 — 178 kg/m</option>
              </optgroup>
              <optgroup label="HEB — Geniş Başlıklı (Standart)">
                <option value="HEB100|20.4">HEB 100 — 20,4 kg/m</option>
                <option value="HEB120|26.7">HEB 120 — 26,7 kg/m</option>
                <option value="HEB140|33.7">HEB 140 — 33,7 kg/m</option>
                <option value="HEB160|42.6">HEB 160 — 42,6 kg/m</option>
                <option value="HEB180|51.2">HEB 180 — 51,2 kg/m</option>
                <option value="HEB200|61.3">HEB 200 — 61,3 kg/m</option>
                <option value="HEB220|71.5">HEB 220 — 71,5 kg/m</option>
                <option value="HEB240|83.2">HEB 240 — 83,2 kg/m</option>
                <option value="HEB260|93">HEB 260 — 93 kg/m</option>
                <option value="HEB280|103">HEB 280 — 103 kg/m</option>
                <option value="HEB300|117">HEB 300 — 117 kg/m</option>
                <option value="HEB320|127">HEB 320 — 127 kg/m</option>
                <option value="HEB340|134">HEB 340 — 134 kg/m</option>
                <option value="HEB360|142">HEB 360 — 142 kg/m</option>
                <option value="HEB400|155">HEB 400 — 155 kg/m</option>
                <option value="HEB450|171">HEB 450 — 171 kg/m</option>
                <option value="HEB500|187">HEB 500 — 187 kg/m</option>
                <option value="HEB600|212">HEB 600 — 212 kg/m</option>
              </optgroup>
              <optgroup label="IPE — Avrupa Standart">
                <option value="IPE80|6">IPE 80 — 6 kg/m</option>
                <option value="IPE100|8.1">IPE 100 — 8,1 kg/m</option>
                <option value="IPE120|10.4">IPE 120 — 10,4 kg/m</option>
                <option value="IPE140|12.9">IPE 140 — 12,9 kg/m</option>
                <option value="IPE160|15.8">IPE 160 — 15,8 kg/m</option>
                <option value="IPE180|18.8">IPE 180 — 18,8 kg/m</option>
                <option value="IPE200|22.4">IPE 200 — 22,4 kg/m</option>
                <option value="IPE220|26.2">IPE 220 — 26,2 kg/m</option>
                <option value="IPE240|30.7">IPE 240 — 30,7 kg/m</option>
                <option value="IPE270|36.1">IPE 270 — 36,1 kg/m</option>
                <option value="IPE300|42.2">IPE 300 — 42,2 kg/m</option>
                <option value="IPE330|49.1">IPE 330 — 49,1 kg/m</option>
                <option value="IPE360|57.1">IPE 360 — 57,1 kg/m</option>
                <option value="IPE400|66.3">IPE 400 — 66,3 kg/m</option>
                <option value="IPE450|77.6">IPE 450 — 77,6 kg/m</option>
                <option value="IPE500|90.7">IPE 500 — 90,7 kg/m</option>
                <option value="IPE550|106">IPE 550 — 106 kg/m</option>
                <option value="IPE600|122">IPE 600 — 122 kg/m</option>
              </optgroup>
              <optgroup label="IPN — Eski Avrupa Standart">
                <option value="IPN80|5.94">IPN 80 — 5,94 kg/m</option>
                <option value="IPN100|8.34">IPN 100 — 8,34 kg/m</option>
                <option value="IPN120|11.1">IPN 120 — 11,1 kg/m</option>
                <option value="IPN140|14.3">IPN 140 — 14,3 kg/m</option>
                <option value="IPN160|17.9">IPN 160 — 17,9 kg/m</option>
                <option value="IPN180|21.9">IPN 180 — 21,9 kg/m</option>
                <option value="IPN200|26.2">IPN 200 — 26,2 kg/m</option>
                <option value="IPN220|31.1">IPN 220 — 31,1 kg/m</option>
                <option value="IPN240|36.2">IPN 240 — 36,2 kg/m</option>
                <option value="IPN260|41.9">IPN 260 — 41,9 kg/m</option>
                <option value="IPN280|47.9">IPN 280 — 47,9 kg/m</option>
                <option value="IPN300|54.2">IPN 300 — 54,2 kg/m</option>
              </optgroup>
            </select>
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="ibeam_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="ibeam_q" value="1" min="1">
          </label>
        </div>
        <p class="calc-note">⚠ HEA/HEB/IPE/IPN profilleri için yoğunluk seçimi etkisizdir; kg/m değerleri TS EN 10025 tablosundan birebir kullanılır.</p>
      </div>

      <!-- ═══════════════ NPU / NPI PROFİL ═══════════════ -->
      <div class="calc-pane" data-pane="npu-npi">
        <div class="calc-pane-head">
          <h3>NPU / NPI Profil (U / Channel Beam)</h3>
          <p>U kesitli yapı profili. Standart kg/m tablosundan seçin.</p>
        </div>
        <div class="calc-grid">
          <label>Profil Tipi ve Ölçü
            <select id="upn_size" class="calc-select-full">
              <optgroup label="UPN / NPU — U Profil">
                <option value="UPN50|5.59">UPN 50 — 5,59 kg/m</option>
                <option value="UPN65|7.09">UPN 65 — 7,09 kg/m</option>
                <option value="UPN80|8.64">UPN 80 — 8,64 kg/m</option>
                <option value="UPN100|10.6">UPN 100 — 10,6 kg/m</option>
                <option value="UPN120|13.4">UPN 120 — 13,4 kg/m</option>
                <option value="UPN140|16">UPN 140 — 16 kg/m</option>
                <option value="UPN160|18.8" selected>UPN 160 — 18,8 kg/m</option>
                <option value="UPN180|22">UPN 180 — 22 kg/m</option>
                <option value="UPN200|25.3">UPN 200 — 25,3 kg/m</option>
                <option value="UPN220|29.4">UPN 220 — 29,4 kg/m</option>
                <option value="UPN240|33.2">UPN 240 — 33,2 kg/m</option>
                <option value="UPN260|37.9">UPN 260 — 37,9 kg/m</option>
                <option value="UPN280|41.8">UPN 280 — 41,8 kg/m</option>
                <option value="UPN300|46.2">UPN 300 — 46,2 kg/m</option>
                <option value="UPN320|59.5">UPN 320 — 59,5 kg/m</option>
                <option value="UPN350|60.6">UPN 350 — 60,6 kg/m</option>
                <option value="UPN380|63.1">UPN 380 — 63,1 kg/m</option>
                <option value="UPN400|71.8">UPN 400 — 71,8 kg/m</option>
              </optgroup>
            </select>
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="upn_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="upn_q" value="1" min="1">
          </label>
        </div>
        <p class="calc-note">⚠ UPN/NPU değerleri DIN 1026 standartına göredir.</p>
      </div>

      <!-- ═══════════════ T PROFİL ═══════════════ -->
      <div class="calc-pane" data-pane="t-profil">
        <div class="calc-pane-head">
          <h3>T Profil</h3>
          <p>T kesitli profil. Hesap: <strong>(A·t + (B−t)·t) × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Genişlik A (mm)
            <input type="number" step="0.1" id="tprof_a" value="50">
          </label>
          <label>Yükseklik B (mm)
            <input type="number" step="0.1" id="tprof_b" value="50">
          </label>
          <label>Et Kalınlığı (mm)
            <input type="number" step="0.1" id="tprof_t" value="6">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="tprof_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="tprof_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Standart T:</span>
          <button type="button" class="preset-btn" data-set="tprof_a:30,tprof_b:30,tprof_t:4">30×30×4</button>
          <button type="button" class="preset-btn" data-set="tprof_a:40,tprof_b:40,tprof_t:5">40×40×5</button>
          <button type="button" class="preset-btn" data-set="tprof_a:50,tprof_b:50,tprof_t:6">50×50×6</button>
          <button type="button" class="preset-btn" data-set="tprof_a:60,tprof_b:60,tprof_t:7">60×60×7</button>
          <button type="button" class="preset-btn" data-set="tprof_a:80,tprof_b:80,tprof_t:9">80×80×9</button>
          <button type="button" class="preset-btn" data-set="tprof_a:100,tprof_b:100,tprof_t:11">100×100×11</button>
        </div>
      </div>

      <!-- ═══════════════ OVAL PROFİL ═══════════════ -->
      <div class="calc-pane" data-pane="oval">
        <div class="calc-pane-head">
          <h3>Oval / Eliptik Profil</h3>
          <p>Eliptik kesitli içi boş profil. Hesap: <strong>π × ((a·b) − (a−t)(b−t)) × L × ρ</strong>.</p>
        </div>
        <div class="calc-grid">
          <label>Büyük Çap a (mm)
            <input type="number" step="0.1" id="oval_a" value="50">
          </label>
          <label>Küçük Çap b (mm)
            <input type="number" step="0.1" id="oval_b" value="30">
          </label>
          <label>Et Kalınlığı (mm)
            <input type="number" step="0.1" id="oval_t" value="2">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="oval_l" value="6000">
          </label>
          <label>Adet
            <input type="number" step="1" id="oval_q" value="1" min="1">
          </label>
        </div>
      </div>

      <!-- ═══════════════ ÇELİK HASIR ═══════════════ -->
      <div class="calc-pane" data-pane="hasir">
        <div class="calc-pane-head">
          <h3>Çelik Hasır (Q ve R Tipi)</h3>
          <p>Standart hasır levha (5 m × 2,15 m = 10,75 m²). kg/m² değeri tip tablosundan.</p>
        </div>
        <div class="calc-grid">
          <label>Hasır Tipi
            <select id="hasir_size" class="calc-select-full">
              <optgroup label="Q Tipi (Eşit Donatılı)">
                <option value="Q106|1.69">Q 106 — 1,69 kg/m²</option>
                <option value="Q131|2.04">Q 131 — 2,04 kg/m²</option>
                <option value="Q158|2.46">Q 158 — 2,46 kg/m²</option>
                <option value="Q188|2.93">Q 188 — 2,93 kg/m²</option>
                <option value="Q221|3.45">Q 221 — 3,45 kg/m²</option>
                <option value="Q257|4.01">Q 257 — 4,01 kg/m²</option>
                <option value="Q295|4.61">Q 295 — 4,61 kg/m²</option>
                <option value="Q335|5.24" selected>Q 335 — 5,24 kg/m²</option>
                <option value="Q378|5.92">Q 378 — 5,92 kg/m²</option>
                <option value="Q424|6.62">Q 424 — 6,62 kg/m²</option>
                <option value="Q524|8.18">Q 524 — 8,18 kg/m²</option>
                <option value="Q636|9.93">Q 636 — 9,93 kg/m²</option>
                <option value="Q758|11.83">Q 758 — 11,83 kg/m²</option>
              </optgroup>
              <optgroup label="R Tipi (Tek Yön Donatılı)">
                <option value="R131|1.91">R 131 — 1,91 kg/m²</option>
                <option value="R158|2.30">R 158 — 2,30 kg/m²</option>
                <option value="R188|2.74">R 188 — 2,74 kg/m²</option>
                <option value="R221|3.20">R 221 — 3,20 kg/m²</option>
                <option value="R257|3.74">R 257 — 3,74 kg/m²</option>
                <option value="R317|4.61">R 317 — 4,61 kg/m²</option>
                <option value="R385|5.59">R 385 — 5,59 kg/m²</option>
                <option value="R424|6.16">R 424 — 6,16 kg/m²</option>
              </optgroup>
            </select>
          </label>
          <label>Adet (5×2,15 m levha)
            <input type="number" step="1" id="hasir_q" value="1" min="1">
          </label>
        </div>
        <p class="calc-note">📐 Standart hasır levha boyutu: 5,00 × 2,15 m = 10,75 m². Q tipi her iki yönde, R tipi tek yönde donatılıdır.</p>
      </div>

      <!-- ═══════════════ NERVÜRLÜ DEMİR ═══════════════ -->
      <div class="calc-pane" data-pane="nervurlu">
        <div class="calc-pane-head">
          <h3>Nervürlü İnşaat Demiri</h3>
          <p>BÇIII-A standardına göre nervürlü inşaat demiri. Hesap: <strong>π × (D/2)² × L × ρ × 1,02</strong> (nervür payı).</p>
        </div>
        <div class="calc-grid">
          <label>Çap (mm)
            <input type="number" step="0.1" id="nerv_d" value="12">
          </label>
          <label>Boy (mm)
            <input type="number" step="1" id="nerv_l" value="12000">
          </label>
          <label>Adet
            <input type="number" step="1" id="nerv_q" value="1" min="1">
          </label>
        </div>
        <div class="calc-presets">
          <span class="preset-label">Yaygın çaplar (BÇIII-A):</span>
          <button type="button" class="preset-btn" data-set="nerv_d:8">Ø8</button>
          <button type="button" class="preset-btn" data-set="nerv_d:10">Ø10</button>
          <button type="button" class="preset-btn" data-set="nerv_d:12">Ø12</button>
          <button type="button" class="preset-btn" data-set="nerv_d:14">Ø14</button>
          <button type="button" class="preset-btn" data-set="nerv_d:16">Ø16</button>
          <button type="button" class="preset-btn" data-set="nerv_d:18">Ø18</button>
          <button type="button" class="preset-btn" data-set="nerv_d:20">Ø20</button>
          <button type="button" class="preset-btn" data-set="nerv_d:22">Ø22</button>
          <button type="button" class="preset-btn" data-set="nerv_d:25">Ø25</button>
          <button type="button" class="preset-btn" data-set="nerv_d:28">Ø28</button>
          <button type="button" class="preset-btn" data-set="nerv_d:32">Ø32</button>
        </div>
        <p class="calc-note">⚠ Nervürlü demir ağırlığı yivler nedeniyle teorik dolu çelik milin %2 fazlasıdır.</p>
      </div>

    </div><!-- /.calc-body -->

    <!-- HESAPLA + EKLE Butonları -->
    <div class="calc-actions">
      <button type="button" id="calcBtn" class="btn-calc-primary">Hesapla</button>
      <button type="button" id="addToList" class="btn-calc-secondary" hidden>+ Listeye Ekle</button>
    </div>

    <!-- SONUÇ -->
    <div id="calcResult" class="calc-result-card" hidden>
      <div class="calc-result-head">
        <span class="kicker">Sonuç</span>
        <h3 id="resTitle">Hesaplama Tamamlandı</h3>
      </div>
      <div class="calc-result-grid">
        <div class="calc-result-item">
          <span class="lbl">Birim Ağırlık</span>
          <strong id="resUnit">— kg</strong>
        </div>
        <div class="calc-result-item">
          <span class="lbl">Adet</span>
          <strong id="resQty">1</strong>
        </div>
        <div class="calc-result-item">
          <span class="lbl">kg/m</span>
          <strong id="resKgM">—</strong>
        </div>
        <div class="calc-result-item highlight">
          <span class="lbl">Toplam Ağırlık</span>
          <strong id="resTotal">— kg</strong>
        </div>
      </div>
      <p class="calc-note">⚠ Bu değerler teorik hesaplamadır; gerçek ağırlık ürün toleransına göre %1-3 farklılık gösterebilir.</p>
      <div class="calc-cta-row">
        <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, hesaplama sayfanızdan ürün için teklif almak istiyorum.')) ?>"
           class="btn-cta-red" target="_blank" rel="noopener">
          💬 Bu Ürün İçin WhatsApp'tan Teklif Al
        </a>
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-calc-ghost">
          📝 İletişim Formu ile Teklif İste
        </a>
      </div>
    </div>

    <!-- LİSTE / SEPET -->
    <div id="calcList" class="calc-list-card" hidden>
      <div class="calc-result-head">
        <span class="kicker">Hesaplama Listesi</span>
        <h3>Birden Fazla Ürün Toplamı</h3>
      </div>
      <div class="calc-list-table-wrap">
        <table class="calc-list-table">
          <thead>
            <tr>
              <th>Ürün</th>
              <th>Ölçü</th>
              <th>Adet</th>
              <th>Birim (kg)</th>
              <th>Toplam (kg)</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="calcListBody"></tbody>
          <tfoot>
            <tr>
              <td colspan="4">Genel Toplam</td>
              <td id="calcGrandTotal">0,00 kg</td>
              <td><button type="button" id="clearList" class="calc-btn-ghost calc-btn-sm">Temizle</button></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
</section>

<script>
(function(){
  // ═══════════ Veri tabloları ═══════════
  const TAB_NAMES = {
    'sac': 'Sac Levha',
    'boru': 'Yuvarlak Boru',
    'profil': 'Kutu Profil',
    'lama': 'Lama',
    'kosebent': 'Köşebent',
    'mil': 'Yuvarlak Mil',
    'kare-mil': 'Kare Mil',
    'altigen': 'Altıgen Mil',
    'hea-heb': 'I/H Profil',
    'npu-npi': 'U Profil',
    't-profil': 'T Profil',
    'oval': 'Oval Profil',
    'hasir': 'Çelik Hasır',
    'nervurlu': 'Nervürlü Demir'
  };

  // Yardımcılar
  function num(id){ return parseFloat(document.getElementById(id).value) || 0; }
  function fmt(v, decimals = 2){
    if (!isFinite(v)) return '—';
    return v.toLocaleString('tr-TR', { minimumFractionDigits: decimals, maximumFractionDigits: 3 });
  }
  function getDensity(){
    const sel = document.getElementById('calcDensity');
    if (sel.value === 'custom') {
      return parseFloat(document.getElementById('customDensity').value) || 7.85;
    }
    return parseFloat(sel.value) || 7.85;
  }

  // Tab değişimi
  document.querySelectorAll('.calc-tab').forEach(t => {
    t.addEventListener('click', () => {
      document.querySelectorAll('.calc-tab').forEach(x => x.classList.remove('active'));
      document.querySelectorAll('.calc-pane').forEach(p => p.classList.remove('active'));
      t.classList.add('active');
      document.querySelector('[data-pane="'+t.dataset.tab+'"]').classList.add('active');
      document.getElementById('calcResult').hidden = true;
      document.getElementById('addToList').hidden = true;
    });
  });

  // Custom density toggle
  document.getElementById('calcDensity').addEventListener('change', e => {
    const cd = document.getElementById('customDensity');
    cd.hidden = e.target.value !== 'custom';
    if (e.target.value === 'custom') cd.focus();
  });

  // Preset butonları
  document.querySelectorAll('.preset-btn').forEach(b => {
    b.addEventListener('click', () => {
      const sets = b.dataset.set.split(',');
      sets.forEach(s => {
        const [id, val] = s.split(':');
        const el = document.getElementById(id.trim());
        if (el) el.value = val.trim();
      });
      // Otomatik hesapla
      document.getElementById('calcBtn').click();
    });
  });

  // ═══════════ HESAPLAMA MOTORU ═══════════
  function calculate(){
    const tab = document.querySelector('.calc-tab.active').dataset.tab;
    const rho = getDensity();
    let unitKg = 0, qty = 1, kgPerMeter = 0, descr = '';

    if (tab === 'sac') {
      const t = num('sac_t')/10, w = num('sac_w')/10, l = num('sac_l')/10;
      qty = num('sac_q');
      unitKg = (t * w * l * rho) / 1000;
      descr = `${num('sac_t')}×${num('sac_w')}×${num('sac_l')} mm`;
    }
    else if (tab === 'boru') {
      const D = num('boru_d')/10, T = num('boru_t')/10, L = num('boru_l')/10;
      qty = num('boru_q');
      const area = Math.PI * (D - T) * T;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `Ø${num('boru_d')}×${num('boru_t')}×${num('boru_l')} mm`;
    }
    else if (tab === 'profil') {
      const a = num('prof_a')/10, b = num('prof_b')/10, t = num('prof_t')/10, L = num('prof_l')/10;
      qty = num('prof_q');
      const inner = Math.max((a - 2*t) * (b - 2*t), 0);
      const area = (a * b) - inner;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `${num('prof_a')}×${num('prof_b')}×${num('prof_t')}×${num('prof_l')} mm`;
    }
    else if (tab === 'lama') {
      const w = num('lama_w')/10, t = num('lama_t')/10, L = num('lama_l')/10;
      qty = num('lama_q');
      unitKg = (w * t * L * rho) / 1000;
      kgPerMeter = (w * t * 100 * rho) / 1000;
      descr = `${num('lama_w')}×${num('lama_t')}×${num('lama_l')} mm`;
    }
    else if (tab === 'kosebent') {
      const a = num('kos_a')/10, b = num('kos_b')/10, t = num('kos_t')/10, L = num('kos_l')/10;
      qty = num('kos_q');
      const area = (a + b - t) * t;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `${num('kos_a')}×${num('kos_b')}×${num('kos_t')}×${num('kos_l')} mm`;
    }
    else if (tab === 'mil') {
      const d = num('mil_d')/10, L = num('mil_l')/10;
      qty = num('mil_q');
      const area = Math.PI * Math.pow(d/2, 2);
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `Ø${num('mil_d')}×${num('mil_l')} mm`;
    }
    else if (tab === 'kare-mil') {
      const a = num('kare_a')/10, L = num('kare_l')/10;
      qty = num('kare_q');
      const area = a * a;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `${num('kare_a')}×${num('kare_a')}×${num('kare_l')} mm`;
    }
    else if (tab === 'altigen') {
      const s = num('hex_s')/10, L = num('hex_l')/10;
      qty = num('hex_q');
      const area = (Math.sqrt(3) / 2) * s * s;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `S=${num('hex_s')} × ${num('hex_l')} mm`;
    }
    else if (tab === 'hea-heb') {
      const sel = document.getElementById('ibeam_size').value.split('|');
      const profileName = sel[0];
      kgPerMeter = parseFloat(sel[1]);
      const L = num('ibeam_l') / 1000; // m
      qty = num('ibeam_q');
      unitKg = kgPerMeter * L;
      descr = `${profileName} × ${num('ibeam_l')} mm`;
    }
    else if (tab === 'npu-npi') {
      const sel = document.getElementById('upn_size').value.split('|');
      const profileName = sel[0];
      kgPerMeter = parseFloat(sel[1]);
      const L = num('upn_l') / 1000;
      qty = num('upn_q');
      unitKg = kgPerMeter * L;
      descr = `${profileName} × ${num('upn_l')} mm`;
    }
    else if (tab === 't-profil') {
      const a = num('tprof_a')/10, b = num('tprof_b')/10, t = num('tprof_t')/10, L = num('tprof_l')/10;
      qty = num('tprof_q');
      const area = (a * t) + ((b - t) * t);
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `T${num('tprof_a')}×${num('tprof_b')}×${num('tprof_t')}×${num('tprof_l')} mm`;
    }
    else if (tab === 'oval') {
      const a = num('oval_a')/10, b = num('oval_b')/10, t = num('oval_t')/10, L = num('oval_l')/10;
      qty = num('oval_q');
      const outer = Math.PI * (a/2) * (b/2);
      const inner = Math.PI * Math.max((a/2 - t), 0) * Math.max((b/2 - t), 0);
      const area = outer - inner;
      unitKg = (area * L * rho) / 1000;
      kgPerMeter = (area * 100 * rho) / 1000;
      descr = `Oval ${num('oval_a')}×${num('oval_b')}×${num('oval_t')}×${num('oval_l')} mm`;
    }
    else if (tab === 'hasir') {
      const sel = document.getElementById('hasir_size').value.split('|');
      const hasirName = sel[0];
      const kgPerM2 = parseFloat(sel[1]);
      qty = num('hasir_q');
      unitKg = kgPerM2 * 10.75; // standart 5×2.15 = 10.75 m²
      descr = `${hasirName} (5×2,15 m levha)`;
    }
    else if (tab === 'nervurlu') {
      const d = num('nerv_d')/10, L = num('nerv_l')/10;
      qty = num('nerv_q');
      const area = Math.PI * Math.pow(d/2, 2);
      unitKg = (area * L * rho * 1.02) / 1000; // %2 nervür payı
      kgPerMeter = (area * 100 * rho * 1.02) / 1000;
      descr = `Ø${num('nerv_d')} nervürlü × ${num('nerv_l')} mm`;
    }

    const total = unitKg * (qty || 1);

    document.getElementById('resTitle').textContent = TAB_NAMES[tab];
    document.getElementById('resUnit').textContent = fmt(unitKg) + ' kg';
    document.getElementById('resQty').textContent = qty;
    document.getElementById('resKgM').textContent = kgPerMeter > 0 ? fmt(kgPerMeter, 2) + ' kg/m' : '—';
    document.getElementById('resTotal').textContent = fmt(total) + ' kg';

    document.getElementById('calcResult').hidden = false;
    document.getElementById('addToList').hidden = false;
    document.getElementById('calcResult').scrollIntoView({behavior:'smooth', block:'nearest'});

    return { tab, descr, qty, unitKg, total };
  }

  document.getElementById('calcBtn').addEventListener('click', calculate);

  // Sıfırla
  document.getElementById('calcReset').addEventListener('click', () => {
    if (!confirm('Tüm form alanlarını sıfırlamak istediğinize emin misiniz?')) return;
    document.querySelectorAll('.calc-pane input').forEach(i => {
      if (i.type === 'number') i.value = i.defaultValue;
    });
    document.getElementById('calcResult').hidden = true;
    document.getElementById('addToList').hidden = true;
  });

  // Yazdır
  document.getElementById('calcPrint').addEventListener('click', () => window.print());

  // ═══════════ ÇOKLU ÜRÜN LİSTESİ ═══════════
  const list = [];
  const listBody = document.getElementById('calcListBody');
  const listCard = document.getElementById('calcList');
  const grandTotalEl = document.getElementById('calcGrandTotal');

  document.getElementById('addToList').addEventListener('click', () => {
    const r = calculate();
    list.push(r);
    renderList();
  });

  document.getElementById('clearList').addEventListener('click', () => {
    if (!confirm('Listeyi tamamen temizlemek istiyor musunuz?')) return;
    list.length = 0;
    renderList();
  });

  function renderList(){
    listBody.innerHTML = '';
    let grand = 0;
    list.forEach((it, i) => {
      grand += it.total;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><strong>${TAB_NAMES[it.tab]}</strong></td>
        <td>${it.descr}</td>
        <td>${it.qty}</td>
        <td>${fmt(it.unitKg)}</td>
        <td><strong>${fmt(it.total)}</strong></td>
        <td><button type="button" class="calc-btn-ghost calc-btn-sm" data-rm="${i}">×</button></td>
      `;
      listBody.appendChild(tr);
    });
    grandTotalEl.textContent = fmt(grand) + ' kg';
    listCard.hidden = list.length === 0;

    listBody.querySelectorAll('[data-rm]').forEach(b => {
      b.addEventListener('click', () => {
        list.splice(parseInt(b.dataset.rm), 1);
        renderList();
      });
    });
  }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
