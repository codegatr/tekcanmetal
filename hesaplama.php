<?php
require __DIR__ . '/includes/db.php';
$pageTitle = t('calc.title', 'Ağırlık Hesaplama');
$metaDesc  = t('calc.meta_desc', 'Demir-çelik ağırlık hesaplama. Görsel diyagramlı, canlı hesaplamalı, çoklu kalem listeli profesyonel hesap motoru.');

// SEO: HowTo + WebApplication Schema (v1.0.69)
$siteUrl = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
$calcUrl = $siteUrl . '/hesaplama.php';

require __DIR__ . '/includes/header.php';
?>

<!-- HowTo Schema — "demir çelik ağırlık nasıl hesaplanır" sorgu için -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "Demir Çelik Sac Ağırlık Hesaplama Nasıl Yapılır?",
  "description": "Sac, boru, profil, hadde gibi demir-çelik ürünlerinin ağırlığını online hesaplama aracıyla saniyeler içinde hesaplama adımları.",
  "image": "<?= h($siteUrl . '/' . settings('logo', 'assets/img/logo.png')) ?>",
  "totalTime": "PT2M",
  "estimatedCost": {
    "@type": "MonetaryAmount",
    "currency": "TRY",
    "value": "0"
  },
  "supply": [
    {"@type": "HowToSupply", "name": "Ürün boyutları (en, boy, kalınlık)"},
    {"@type": "HowToSupply", "name": "Malzeme türü (çelik, alüminyum, paslanmaz, bakır vs.)"}
  ],
  "tool": [
    {"@type": "HowToTool", "name": "Tekcan Metal Online Ağırlık Hesaplama Motoru"}
  ],
  "step": [
    {
      "@type": "HowToStep",
      "position": 1,
      "name": "Ürün tipini seçin",
      "text": "14 farklı demir-çelik ürün grubundan (sac levha, boru, profil, hadde vs.) hesaplama yapacağınız tipi seçin.",
      "url": "<?= h($calcUrl) ?>#step-1"
    },
    {
      "@type": "HowToStep",
      "position": 2,
      "name": "Malzeme yoğunluğunu seçin",
      "text": "Çelik (7.85 g/cm³), Paslanmaz 304 (7.93), Alüminyum (2.70), Bakır (8.96), Pirinç, Bronz veya özel yoğunluk girebilirsiniz. 17 hazır malzeme yoğunluğu mevcut.",
      "url": "<?= h($calcUrl) ?>#step-2"
    },
    {
      "@type": "HowToStep",
      "position": 3,
      "name": "Ürün ölçülerini girin",
      "text": "Seçtiğiniz ürün tipine göre kalınlık, en, boy, çap, profil ölçüleri ekrana gelir. Hazır ölçüler arasından da seçebilirsiniz.",
      "url": "<?= h($calcUrl) ?>#step-3"
    },
    {
      "@type": "HowToStep",
      "position": 4,
      "name": "Anlık ağırlık sonucunu görün",
      "text": "Tek parça ağırlığı, m²/m³ ağırlığı, toplam adet ağırlığı anında görünür. Listeye ekleyerek çoklu kalem hesabı yapabilir, kg fiyatı girerek toplam maliyeti görebilir, sonucu yazdırabilirsiniz.",
      "url": "<?= h($calcUrl) ?>#step-4"
    }
  ]
}
</script>

<!-- WebApplication Schema — "online metal calculator" arama için -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Tekcan Metal Ağırlık Hesaplama Motoru",
  "alternateName": ["Demir Çelik Hesaplama", "Sac Ağırlık Hesaplama", "Metal Weight Calculator"],
  "url": "<?= h($calcUrl) ?>",
  "applicationCategory": "BusinessApplication",
  "applicationSubCategory": "Engineering Calculator",
  "operatingSystem": "Web Browser",
  "browserRequirements": "Requires JavaScript",
  "isAccessibleForFree": true,
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "TRY"
  },
  "featureList": [
    "14 farklı ürün tipi (sac, boru, profil, hadde)",
    "17 malzeme yoğunluğu (çelik, paslanmaz, alüminyum, bakır)",
    "200+ hazır standart ölçü",
    "Çoklu kalem listesi",
    "₺ Maliyet hesaplama",
    "Yazdırma desteği",
    "Mobil uyumlu"
  ],
  "publisher": {
    "@type": "Organization",
    "name": "Tekcan Metal",
    "url": "<?= h($siteUrl) ?>"
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

.hes-page{
  --hp-bg:#f5f7fa;
  --hp-surface:#ffffff;
  --hp-surface-2:#fafbfd;
  --hp-border:#e3e8ef;
  --hp-border-strong:#c5cfdc;
  --hp-text:#0a0e1a;
  --hp-text-muted:#5e6470;
  --hp-text-dim:#8a93a3;
  --hp-primary:#1e4a9e;
  --hp-primary-dark:#143672;
  --hp-accent:#c8102e;
  --hp-accent-dark:#a00d24;
  --hp-success:#10803a;
  --hp-warning:#b45309;
  --hp-mono:'JetBrains Mono', ui-monospace, monospace;
  --hp-sans:'Inter', system-ui, sans-serif;
  background:var(--hp-bg);
  font-family:var(--hp-sans);
}

/* ═══ HERO ═══ */
.hes-hero{
  background:linear-gradient(135deg, #050d24 0%, #0c1e44 50%, #143672 100%);
  color:#fff;
  padding:60px 0 50px;
  position:relative;
  overflow:hidden;
  border-bottom:3px solid var(--hp-accent);
}
.hes-hero::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(
    45deg, transparent 0, transparent 20px,
    rgba(255,255,255,.02) 20px, rgba(255,255,255,.02) 21px);
  pointer-events:none;
}
.hes-hero-inner{
  display:grid;
  grid-template-columns:1fr auto;
  gap:30px;
  align-items:center;
  position:relative;z-index:2;
}
@media (max-width:800px){.hes-hero-inner{grid-template-columns:1fr;text-align:center}}
.hes-hero-text h1{
  font-family:var(--hp-sans);
  font-size:clamp(28px, 4vw, 40px);
  font-weight:700;
  letter-spacing:-.5px;
  margin:0 0 8px;
  line-height:1.15;
}
.hes-hero-text p{
  font-size:14px;
  color:rgba(255,255,255,.65);
  margin:0;
  max-width:560px;
}
.hes-hero-stats{
  display:flex;gap:32px;flex-wrap:wrap;
}
@media (max-width:800px){.hes-hero-stats{justify-content:center}}
.hes-hero-stat{text-align:left}
@media (max-width:800px){.hes-hero-stat{text-align:center}}
.hes-hero-stat strong{
  display:block;
  font-family:var(--hp-mono);
  font-size:24px;
  font-weight:700;
  color:#fff;
  line-height:1;
  margin-bottom:4px;
}
.hes-hero-stat span{
  font-size:10px;
  font-weight:700;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:rgba(255,255,255,.55);
}

/* ═══ MAIN CONTAINER ═══ */
.hes-main{
  padding:30px 0 80px;
}

/* ═══ STEP 1: PRODUCT GRID ═══ */
.hes-step{
  background:var(--hp-surface);
  border:1px solid var(--hp-border);
  margin-bottom:18px;
  overflow:hidden;
}
.hes-step-head{
  padding:20px 28px;
  background:var(--hp-surface-2);
  border-bottom:1px solid var(--hp-border);
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:18px;
  flex-wrap:wrap;
}
.hes-step-head-left{
  display:flex;align-items:center;gap:14px;
}
.hes-step-num{
  width:32px;height:32px;
  background:var(--hp-primary);
  color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--hp-mono);
  font-size:14px;font-weight:700;
}
.hes-step-head h2{
  margin:0;
  font-size:14px;
  font-weight:700;
  color:var(--hp-text);
  letter-spacing:.2px;
}
.hes-step-head h2 small{
  display:block;
  font-size:11.5px;
  font-weight:500;
  color:var(--hp-text-muted);
  margin-top:2px;
  letter-spacing:0;
}
.hes-step-body{padding:24px 28px}

.hes-products{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));
  gap:10px;
}
.hes-product{
  background:var(--hp-surface);
  border:1.5px solid var(--hp-border);
  padding:18px 14px;
  text-align:center;
  cursor:pointer;
  transition:.18s;
  position:relative;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
}
.hes-product:hover{
  border-color:var(--hp-primary);
  transform:translateY(-2px);
  box-shadow:0 6px 16px rgba(30,74,158,.1);
}
.hes-product.active{
  background:var(--hp-primary);
  border-color:var(--hp-primary);
  color:#fff;
}
.hes-product.active::before{
  content:'✓';
  position:absolute;top:6px;right:8px;
  font-size:11px;font-weight:700;
  color:#fff;
  background:var(--hp-accent);
  width:18px;height:18px;
  display:flex;align-items:center;justify-content:center;
  border-radius:50%;
}
.hes-product-icon{
  width:48px;height:48px;
  display:flex;align-items:center;justify-content:center;
  color:var(--hp-primary);
  transition:.2s;
}
.hes-product.active .hes-product-icon{color:#fff}
.hes-product-icon svg{width:42px;height:42px}
.hes-product-name{
  font-size:12.5px;
  font-weight:600;
  color:var(--hp-text);
  line-height:1.25;
}
.hes-product.active .hes-product-name{color:#fff}

/* ═══ STEP 2: WORKBENCH (input + diagram + result) ═══ */
.hes-bench{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:0;
  border-bottom:1px solid var(--hp-border);
}
@media (max-width:900px){.hes-bench{grid-template-columns:1fr}}

/* Sol panel: inputs */
.hes-inputs{
  padding:30px 32px;
  border-right:1px solid var(--hp-border);
}
@media (max-width:900px){.hes-inputs{border-right:0;border-bottom:1px solid var(--hp-border)}}
.hes-inputs-head{margin-bottom:24px}
.hes-inputs-head h3{
  font-size:18px;
  font-weight:700;
  margin:0 0 4px;
  color:var(--hp-text);
}
.hes-inputs-head p{
  font-size:12.5px;
  color:var(--hp-text-muted);
  margin:0;
  font-family:var(--hp-mono);
}

.hes-fields{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
}
.hes-fields.cols-1{grid-template-columns:1fr}
.hes-fields.cols-3{grid-template-columns:1fr 1fr 1fr}
.hes-field{display:flex;flex-direction:column;gap:5px}
.hes-field-full{grid-column:1/-1}
.hes-field label{
  font-size:11px;
  font-weight:700;
  letter-spacing:1.2px;
  text-transform:uppercase;
  color:var(--hp-text-muted);
}
.hes-field-input-wrap{
  position:relative;
}
.hes-field input,
.hes-field select{
  width:100%;
  padding:12px 50px 12px 14px;
  font-family:var(--hp-mono);
  font-size:15px;
  font-weight:600;
  color:var(--hp-text);
  background:var(--hp-surface-2);
  border:1.5px solid var(--hp-border);
  transition:.15s;
  border-radius:0;
  -webkit-appearance:none;
  -moz-appearance:textfield;
}
.hes-field select{
  padding-right:14px;
  font-family:var(--hp-sans);
  font-size:13.5px;
  -webkit-appearance:menulist;
}
.hes-field input::-webkit-outer-spin-button,
.hes-field input::-webkit-inner-spin-button{
  -webkit-appearance:none;margin:0;
}
.hes-field input:focus,
.hes-field select:focus{
  outline:0;
  border-color:var(--hp-primary);
  background:#fff;
  box-shadow:0 0 0 3px rgba(30,74,158,.12);
}
.hes-field-unit{
  position:absolute;
  right:14px;top:50%;transform:translateY(-50%);
  font-size:11px;
  font-weight:600;
  color:var(--hp-text-dim);
  pointer-events:none;
  font-family:var(--hp-mono);
}

.hes-presets{
  margin-top:18px;
  padding-top:18px;
  border-top:1px dashed var(--hp-border);
}
.hes-presets-label{
  font-size:11px;
  font-weight:700;
  letter-spacing:1.2px;
  text-transform:uppercase;
  color:var(--hp-text-muted);
  margin-bottom:10px;
}
.hes-presets-list{
  display:flex;flex-wrap:wrap;gap:6px;
}
.hes-preset{
  padding:6px 11px;
  font-family:var(--hp-mono);
  font-size:11.5px;
  font-weight:600;
  background:var(--hp-surface-2);
  border:1px solid var(--hp-border);
  color:var(--hp-text);
  cursor:pointer;
  transition:.15s;
}
.hes-preset:hover{
  background:var(--hp-primary);
  color:#fff;
  border-color:var(--hp-primary);
}

/* Sağ panel: diagram + result */
.hes-visual{
  background:var(--hp-surface-2);
  display:flex;
  flex-direction:column;
}

.hes-diagram{
  background:#fff;
  padding:30px 32px;
  flex:1;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:280px;
  position:relative;
  overflow:hidden;
}
.hes-diagram::before{
  content:'';position:absolute;
  inset:0;
  background-image:
    linear-gradient(to right, rgba(30,74,158,.03) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(30,74,158,.03) 1px, transparent 1px);
  background-size:20px 20px;
  pointer-events:none;
}
.hes-diagram-svg{
  width:100%;
  max-width:340px;
  height:auto;
  position:relative;z-index:2;
}
.hes-diagram-caption{
  margin-top:18px;
  font-size:11px;
  font-weight:600;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:var(--hp-text-muted);
  text-align:center;
  position:relative;z-index:2;
}

.hes-formula{
  background:#0a0e1a;
  color:#9be4bb;
  padding:14px 32px;
  font-family:var(--hp-mono);
  font-size:12px;
  border-top:1px solid #1f2937;
  overflow-x:auto;
}
.hes-formula::before{
  content:'⨏ ';color:#fbbf24;
}

/* ═══ RESULT BAR ═══ */
.hes-result{
  background:linear-gradient(135deg, var(--hp-primary) 0%, var(--hp-primary-dark) 100%);
  color:#fff;
  padding:22px 28px;
  display:grid;
  grid-template-columns:repeat(4, 1fr) auto;
  gap:24px;
  align-items:center;
  position:relative;
  overflow:hidden;
}
@media (max-width:900px){
  .hes-result{grid-template-columns:repeat(2, 1fr);gap:18px}
}
@media (max-width:500px){
  .hes-result{grid-template-columns:1fr;text-align:center}
}
.hes-result::before{
  content:'';position:absolute;
  inset:0;
  background-image:radial-gradient(circle at 90% 50%, rgba(200,16,46,.2) 0%, transparent 60%);
  pointer-events:none;
}
.hes-result-item{
  position:relative;z-index:2;
}
.hes-result-label{
  font-size:10px;
  font-weight:700;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:rgba(255,255,255,.6);
  margin-bottom:6px;
}
.hes-result-value{
  font-family:var(--hp-mono);
  font-size:22px;
  font-weight:700;
  color:#fff;
  line-height:1;
  letter-spacing:-.3px;
}
.hes-result-value.big{
  font-size:32px;
  color:#fbbf24;
}
.hes-result-actions{
  display:flex;gap:8px;
  position:relative;z-index:2;
}
@media (max-width:500px){.hes-result-actions{justify-content:center}}
.hes-btn{
  padding:11px 18px;
  font-size:11.5px;
  font-weight:700;
  letter-spacing:1.2px;
  text-transform:uppercase;
  border:0;
  cursor:pointer;
  transition:.18s;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  gap:6px;
}
.hes-btn-add{
  background:#fbbf24;
  color:#0a0e1a;
}
.hes-btn-add:hover{
  background:#f59e0b;
  transform:translateY(-1px);
}
.hes-btn-quote{
  background:var(--hp-accent);
  color:#fff;
}
.hes-btn-quote:hover{
  background:var(--hp-accent-dark);
  transform:translateY(-1px);
}

/* ═══ MATERIAL SELECTOR ═══ */
.hes-material{
  background:var(--hp-surface);
  padding:14px 28px;
  border-bottom:1px solid var(--hp-border);
  display:flex;
  align-items:center;
  gap:18px;
  flex-wrap:wrap;
}
.hes-material-label{
  font-size:11px;
  font-weight:700;
  letter-spacing:1.2px;
  text-transform:uppercase;
  color:var(--hp-text-muted);
}
.hes-material-pills{
  display:flex;
  gap:6px;
  flex-wrap:wrap;
}
.hes-material-pill{
  padding:7px 14px;
  font-size:12px;
  font-weight:600;
  background:var(--hp-surface-2);
  border:1px solid var(--hp-border);
  color:var(--hp-text);
  cursor:pointer;
  transition:.15s;
  font-family:var(--hp-mono);
}
.hes-material-pill:hover{
  border-color:var(--hp-primary);
}
.hes-material-pill.active{
  background:var(--hp-primary);
  color:#fff;
  border-color:var(--hp-primary);
}
.hes-material-custom{
  display:none;
  align-items:center;gap:6px;
}
.hes-material-custom.show{display:inline-flex}
.hes-material-custom input{
  width:80px;
  padding:7px 8px;
  font-family:var(--hp-mono);
  font-size:12px;
  border:1px solid var(--hp-border-strong);
  background:#fff;
}

/* ═══ COMPARISON MODE ═══ */
.hes-compare{
  background:var(--hp-surface);
  border:1px solid var(--hp-border);
  padding:24px 28px;
  margin-top:18px;
  display:none;
}
.hes-compare.show{display:block}
.hes-compare-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:18px;
}
.hes-compare-head h3{
  font-size:14px;font-weight:700;margin:0;
  letter-spacing:.2px;
}
.hes-compare-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
  gap:12px;
}
.hes-compare-card{
  padding:16px 18px;
  background:var(--hp-surface-2);
  border:1px solid var(--hp-border);
}
.hes-compare-card h4{
  font-size:12px;
  font-weight:600;
  margin:0 0 8px;
  color:var(--hp-text-muted);
}
.hes-compare-card-value{
  font-family:var(--hp-mono);
  font-size:20px;
  font-weight:700;
  color:var(--hp-text);
  letter-spacing:-.2px;
}
.hes-compare-card-density{
  font-size:11px;
  color:var(--hp-text-dim);
  margin-top:4px;
  font-family:var(--hp-mono);
}

/* ═══ COST CALCULATOR ═══ */
.hes-cost{
  background:#fefce8;
  border:1px solid #fde68a;
  padding:18px 24px;
  margin-top:18px;
  display:none;
  align-items:center;
  gap:18px;
  flex-wrap:wrap;
}
.hes-cost.show{display:flex}
.hes-cost-icon{
  width:42px;height:42px;
  background:#fbbf24;color:#0a0e1a;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;flex-shrink:0;
}
.hes-cost-input{
  display:flex;
  align-items:center;
  gap:10px;
  flex:1;
  min-width:200px;
}
.hes-cost-input label{
  font-size:12px;font-weight:600;color:var(--hp-text);
  white-space:nowrap;
}
.hes-cost-input input{
  width:120px;
  padding:8px 12px;
  font-family:var(--hp-mono);
  font-size:14px;font-weight:600;
  border:1px solid #d4a418;
  background:#fff;
}
.hes-cost-result{
  font-family:var(--hp-mono);
  font-size:18px;
  font-weight:700;
  color:#92400e;
  margin-left:auto;
}

/* ═══ CART/LIST ═══ */
.hes-cart{
  background:var(--hp-surface);
  border:1px solid var(--hp-border);
  margin-top:18px;
  display:none;
}
.hes-cart.show{display:block}
.hes-cart-head{
  padding:18px 28px;
  background:var(--hp-text);
  color:#fff;
  display:flex;justify-content:space-between;align-items:center;
}
.hes-cart-head h3{
  margin:0;font-size:14px;font-weight:700;letter-spacing:.2px;
}
.hes-cart-head span{
  font-family:var(--hp-mono);
  font-size:13px;color:#fbbf24;font-weight:700;
}
.hes-cart-table{
  width:100%;border-collapse:collapse;font-size:13px;
}
.hes-cart-table th,
.hes-cart-table td{
  padding:12px 18px;text-align:left;
  border-bottom:1px solid var(--hp-border);
}
.hes-cart-table th{
  background:var(--hp-surface-2);
  font-size:10.5px;
  font-weight:700;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:var(--hp-text-muted);
}
.hes-cart-table td{
  font-family:var(--hp-mono);
}
.hes-cart-table td.product{font-family:var(--hp-sans);font-weight:600}
.hes-cart-table td.actions{text-align:center;width:60px}
.hes-cart-rm{
  background:transparent;border:1px solid var(--hp-border);
  color:var(--hp-accent);padding:4px 10px;cursor:pointer;
  font-size:13px;font-weight:700;
}
.hes-cart-rm:hover{background:var(--hp-accent);color:#fff;border-color:var(--hp-accent)}
.hes-cart-foot{
  padding:18px 28px;
  background:var(--hp-surface-2);
  border-top:1px solid var(--hp-border);
  display:flex;justify-content:space-between;align-items:center;
  flex-wrap:wrap;gap:12px;
}
.hes-cart-grand{
  font-family:var(--hp-mono);
  font-size:24px;font-weight:700;
  color:var(--hp-text);
}
.hes-cart-actions{display:flex;gap:8px;flex-wrap:wrap}

/* ═══ FAB (mobile sticky action) ═══ */
.hes-fab{
  display:none;
  position:fixed;
  bottom:80px;right:14px;
  background:var(--hp-accent);
  color:#fff;
  padding:14px 20px;
  font-size:12px;
  font-weight:700;
  letter-spacing:1px;
  text-transform:uppercase;
  text-decoration:none;
  z-index:50;
  box-shadow:0 12px 28px rgba(200,16,46,.4);
  border:0;cursor:pointer;
}
@media (max-width:768px){
  .hes-fab.show{display:inline-flex;align-items:center;gap:6px}
}

/* ═══ TIP BOX ═══ */
.hes-tip{
  background:#eff6ff;
  border-left:3px solid var(--hp-primary);
  padding:14px 18px;
  margin-top:14px;
  font-size:12.5px;
  color:var(--hp-text-muted);
  line-height:1.55;
}
.hes-tip strong{color:var(--hp-primary)}

/* PRINT */
@media print{
  .site-header,.site-footer,.mobile-bottomnav,.hes-fab,.hes-presets,.hes-product,
  .hes-product:not(.active),.hes-step-num,.hes-result-actions,
  .hes-cart-actions,.hes-cart-rm,.hes-material-pill:not(.active),.hes-cost{display:none !important}
  .hes-page{background:#fff !important}
  .hes-step{border:1px solid #999 !important;page-break-inside:avoid}
}
</style>

<div class="hes-page">

  <!-- HERO -->
  <section class="hes-hero">
    <div class="container">
      <div class="hes-hero-inner">
        <div class="hes-hero-text">
          <h1>📐 <?= h(t('calc.engine_title', 'Ağırlık Hesaplama Motoru')) ?></h1>
          <p><?= h(t('calc.hero_lead', '14 ürün grubu, 17 malzeme yoğunluğu, görsel diyagramlı canlı hesap, çoklu kalem listesi ve maliyet hesabı — hepsi tek ekranda.')) ?></p>
        </div>
        <div class="hes-hero-stats">
          <div class="hes-hero-stat"><strong>14</strong><span><?= h(t('calc.product_type', 'Ürün Tipi')) ?></span></div>
          <div class="hes-hero-stat"><strong>17</strong><span><?= h(t('calc.material', 'Malzeme')) ?></span></div>
          <div class="hes-hero-stat"><strong>200+</strong><span><?= h(t('calc.preset_size', 'Hazır Ölçü')) ?></span></div>
          <div class="hes-hero-stat"><strong>%99.7</strong><span><?= h(t('services.principle_precision', 'Hassasiyet')) ?></span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="hes-main">
    <div class="container">

      <!-- ═══ STEP 1: PRODUCT SELECT ═══ -->
      <div class="hes-step">
        <div class="hes-step-head">
          <div class="hes-step-head-left">
            <div class="hes-step-num">1</div>
            <h2><?= h(t('calc.step1_title', 'Ürün Tipi Seçin')) ?> <small><?= h(t('calc.step1_subtitle', '14 farklı demir-çelik ürün grubu')) ?></small></h2>
          </div>
        </div>
        <div class="hes-step-body">
          <div class="hes-products" id="hesProducts">
            <!-- JS render eder -->
          </div>
        </div>
      </div>

      <!-- ═══ MATERIAL SELECTOR ═══ -->
      <div class="hes-step">
        <div class="hes-material">
          <span class="hes-material-label"><?= h(t('calc.material', 'Malzeme')) ?>:</span>
          <div class="hes-material-pills" id="hesMaterials">
            <button type="button" class="hes-material-pill active" data-density="7.85"><?= h(t('calc.mat_steel', 'Çelik')) ?></button>
            <button type="button" class="hes-material-pill" data-density="7.85">DKP/HRP</button>
            <button type="button" class="hes-material-pill" data-density="7.85">ST52</button>
            <button type="button" class="hes-material-pill" data-density="7.93">304 <?= h(t('calc.mat_stainless', 'Paslanmaz')) ?></button>
            <button type="button" class="hes-material-pill" data-density="8.00">316 <?= h(t('calc.mat_stainless', 'Paslanmaz')) ?></button>
            <button type="button" class="hes-material-pill" data-density="2.70"><?= h(t('calc.mat_aluminum', 'Alüminyum')) ?></button>
            <button type="button" class="hes-material-pill" data-density="8.96"><?= h(t('calc.mat_copper', 'Bakır')) ?></button>
            <button type="button" class="hes-material-pill" data-density="8.40"><?= h(t('calc.mat_brass', 'Pirinç')) ?></button>
            <button type="button" class="hes-material-pill" data-density="8.73"><?= h(t('calc.mat_bronze', 'Bronz')) ?></button>
            <button type="button" class="hes-material-pill" data-density="custom"><?= h(t('calc.mat_custom', 'Özel')) ?>...</button>
          </div>
          <div class="hes-material-custom" id="hesMatCustomWrap">
            <input type="number" step="0.01" id="hesMatCustom" placeholder="g/cm³" value="7.85">
            <span style="font-family:var(--hp-mono);font-size:11px;color:var(--hp-text-muted)">g/cm³</span>
          </div>
        </div>

        <!-- ═══ STEP 2: WORKBENCH ═══ -->
        <div class="hes-bench">

          <!-- Sol: Inputs -->
          <div class="hes-inputs">
            <div class="hes-inputs-head">
              <h3 id="hesProductTitle">Sac Levha</h3>
              <p id="hesProductFormula">en × boy × kalınlık × yoğunluk</p>
            </div>
            <div id="hesFields">
              <!-- JS doldurur -->
            </div>
            <div class="hes-presets" id="hesPresetsWrap">
              <div class="hes-presets-label">Hızlı Seçim:</div>
              <div class="hes-presets-list" id="hesPresets"></div>
            </div>
            <div class="hes-tip" id="hesTip" style="display:none"></div>
          </div>

          <!-- Sağ: Diagram + Formula -->
          <div class="hes-visual">
            <div class="hes-diagram">
              <div class="hes-diagram-svg" id="hesDiagram">
                <!-- JS render eder -->
              </div>
              <div class="hes-diagram-caption" id="hesDiagramCaption">Canlı Diyagram</div>
            </div>
            <div class="hes-formula" id="hesFormulaText">
              W = en × boy × kalınlık × ρ
            </div>
          </div>

        </div>

        <!-- LIVE RESULT -->
        <div class="hes-result">
          <div class="hes-result-item">
            <div class="hes-result-label">Birim Ağırlık</div>
            <div class="hes-result-value" id="hesUnitKg">— kg</div>
          </div>
          <div class="hes-result-item">
            <div class="hes-result-label">Adet</div>
            <div class="hes-result-value" id="hesQty">1</div>
          </div>
          <div class="hes-result-item">
            <div class="hes-result-label">kg / metre</div>
            <div class="hes-result-value" id="hesKgM">—</div>
          </div>
          <div class="hes-result-item">
            <div class="hes-result-label">Toplam</div>
            <div class="hes-result-value big" id="hesTotal">— kg</div>
          </div>
          <div class="hes-result-actions">
            <button type="button" class="hes-btn hes-btn-add" id="hesAddBtn">+ <?= h(t('calc.add_to_list', 'Listeye Ekle')) ?></button>
            <a class="hes-btn hes-btn-quote" id="hesQuoteBtn" target="_blank" rel="noopener" href="#">💬 Teklif Al</a>
          </div>
        </div>

      </div>

      <!-- ═══ COMPARISON MODE ═══ -->
      <div class="hes-step" style="display:none" id="hesCompareWrap">
        <div class="hes-step-head">
          <div class="hes-step-head-left">
            <div class="hes-step-num">⇄</div>
            <h2><?= h(t('calc.material_comparison', 'Malzeme Karşılaştırma')) ?> <small><?= h(t('calc.compare_subtitle', 'Aynı ölçü farklı malzemelerde nasıl ağırlık verir?')) ?></small></h2>
          </div>
          <button type="button" class="hes-btn" style="background:var(--hp-text);color:#fff" id="hesCompareToggle"><?= h(t('btn.close', 'Kapat')) ?></button>
        </div>
        <div class="hes-step-body">
          <div class="hes-compare-grid" id="hesCompareGrid"></div>
        </div>
      </div>

      <!-- ═══ COST CALCULATOR ═══ -->
      <div class="hes-cost" id="hesCostBox">
        <div class="hes-cost-icon">₺</div>
        <div class="hes-cost-input">
          <label><?= h(t('calc.kg_price', 'kg fiyatı')) ?>:</label>
          <input type="number" step="0.01" id="hesCostInput" placeholder="0.00" value="35">
          <span style="font-family:var(--hp-mono);font-size:12px;color:#92400e">₺/kg</span>
        </div>
        <div class="hes-cost-result" id="hesCostResult">— ₺</div>
      </div>

      <!-- TOOLBAR -->
      <div style="display:flex;gap:8px;justify-content:space-between;margin-top:18px;flex-wrap:wrap">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button type="button" class="hes-btn" style="background:var(--hp-surface);color:var(--hp-text);border:1px solid var(--hp-border)" id="hesCompareBtn">⇄ <?= h(t('calc.compare', 'Karşılaştır')) ?></button>
          <button type="button" class="hes-btn" style="background:var(--hp-surface);color:var(--hp-text);border:1px solid var(--hp-border)" id="hesCostBtn">₺ <?= h(t('calc.cost_calc', 'Maliyet Hesabı')) ?></button>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button type="button" class="hes-btn" style="background:var(--hp-surface);color:var(--hp-text);border:1px solid var(--hp-border)" id="hesPrintBtn">🖨 <?= h(t('btn.print', 'Yazdır')) ?></button>
          <button type="button" class="hes-btn" style="background:var(--hp-surface);color:var(--hp-accent);border:1px solid var(--hp-border)" id="hesResetBtn">↺ <?= h(t('calc.reset', 'Sıfırla')) ?></button>
        </div>
      </div>

      <!-- ═══ CART LIST ═══ -->
      <div class="hes-cart" id="hesCart">
        <div class="hes-cart-head">
          <h3>📋 <?= h(t('calc.calc_list', 'Hesaplama Listesi')) ?></h3>
          <span id="hesCartCount">0 kalem</span>
        </div>
        <div style="overflow-x:auto">
          <table class="hes-cart-table">
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
            <tbody id="hesCartBody"></tbody>
          </table>
        </div>
        <div class="hes-cart-foot">
          <div>
            <div style="font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--hp-text-muted);margin-bottom:4px">Genel Toplam</div>
            <div class="hes-cart-grand" id="hesCartGrand">0,00 kg</div>
          </div>
          <div class="hes-cart-actions">
            <a class="hes-btn hes-btn-quote" id="hesCartQuote" target="_blank" rel="noopener" href="#">💬 Listeyi WhatsApp'a Gönder</a>
            <button type="button" class="hes-btn" style="background:var(--hp-text);color:#fff" id="hesCartClear"><?= h(t('calc.clear_list', 'Listeyi Temizle')) ?></button>
          </div>
        </div>
      </div>

    </div>
  </section>

</div>

<a href="#hesAddBtn" class="hes-fab" id="hesFab">📋 Listeye Ekle</a>

<script>
(function(){
'use strict';

// ═══════════════════════════════════════════════════════
// PRODUCT DEFINITIONS — ürün katalogu
// ═══════════════════════════════════════════════════════
const PRODUCTS = {
  'sac': {
    name: 'Sac Levha', short: 'Sac',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="6" y="14" width="36" height="20" /><line x1="6" y1="14" x2="10" y2="10"/><line x1="42" y1="14" x2="46" y2="10"/><line x1="46" y1="10" x2="46" y2="30"/><line x1="46" y1="30" x2="42" y2="34"/></svg>`,
    formula: 'en × boy × kalınlık × ρ',
    formulaText: 'W (kg) = (kalınlık × en × boy × ρ) / 1.000.000',
    fields: [
      {id:'sac_t', label:'Kalınlık', unit:'mm', value:3, step:0.1},
      {id:'sac_w', label:'En', unit:'mm', value:1500, step:1},
      {id:'sac_l', label:'Boy', unit:'mm', value:3000, step:1},
      {id:'sac_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [
      ['1,5×1000×2000', {sac_t:1.5, sac_w:1000, sac_l:2000}],
      ['2×1250×2500', {sac_t:2, sac_w:1250, sac_l:2500}],
      ['3×1500×3000', {sac_t:3, sac_w:1500, sac_l:3000}],
      ['5×1500×3000', {sac_t:5, sac_w:1500, sac_l:3000}],
      ['8×1500×6000', {sac_t:8, sac_w:1500, sac_l:6000}],
      ['10×2000×6000', {sac_t:10, sac_w:2000, sac_l:6000}]
    ],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <!-- 3D plate -->
        <polygon points="60,80 240,80 280,50 100,50" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <rect x="60" y="80" width="180" height="100" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <polygon points="240,80 240,180 280,150 280,50" fill="#9aa9bc" stroke="#1e4a9e" stroke-width="1.5"/>
        <!-- Dimensions -->
        <line x1="60" y1="195" x2="240" y2="195" stroke="#c8102e" stroke-width="1"/>
        <line x1="60" y1="190" x2="60" y2="200" stroke="#c8102e" stroke-width="1.5"/>
        <line x1="240" y1="190" x2="240" y2="200" stroke="#c8102e" stroke-width="1.5"/>
        <text x="150" y="212" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.sac_w || 0} mm</text>
        <line x1="45" y1="80" x2="45" y2="180" stroke="#c8102e" stroke-width="1"/>
        <line x1="40" y1="80" x2="50" y2="80" stroke="#c8102e" stroke-width="1.5"/>
        <line x1="40" y1="180" x2="50" y2="180" stroke="#c8102e" stroke-width="1.5"/>
        <text x="35" y="135" text-anchor="middle" transform="rotate(-90, 35, 135)" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.sac_l || 0} mm</text>
        <line x1="280" y1="40" x2="280" y2="50" stroke="#c8102e" stroke-width="1"/>
        <text x="295" y="42" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">↕${v.sac_t || 0}</text>
      </svg>`,
    calc: (v, rho) => {
      const t = (v.sac_t||0)/10, w = (v.sac_w||0)/10, l = (v.sac_l||0)/10, q = v.sac_q||1;
      const unit = (t*w*l*rho)/1000;
      return {unit, qty:q, kgPerM:0, descr:`${v.sac_t}×${v.sac_w}×${v.sac_l} mm`};
    }
  },

  'boru': {
    name: 'Yuvarlak Boru', short: 'Yuv. Boru',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="24" cy="24" r="14"/><circle cx="24" cy="24" r="10"/></svg>`,
    formula: 'π × (D − t) × t × L × ρ',
    formulaText: 'W (kg/m) = π × (D − t) × t × ρ / 1.000',
    fields: [
      {id:'boru_d', label:'Dış Çap (D)', unit:'mm', value:48.3, step:0.1},
      {id:'boru_t', label:'Et Kalınlığı', unit:'mm', value:3, step:0.1},
      {id:'boru_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'boru_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [
      ['1/2" — 21,3×2,6', {boru_d:21.3, boru_t:2.6}],
      ['3/4" — 26,9×2,6', {boru_d:26.9, boru_t:2.6}],
      ['1" — 33,7×3,2', {boru_d:33.7, boru_t:3.2}],
      ['1¼" — 42,4×3,2', {boru_d:42.4, boru_t:3.2}],
      ['1½" — 48,3×3,2', {boru_d:48.3, boru_t:3.2}],
      ['2" — 60,3×3,6', {boru_d:60.3, boru_t:3.6}],
      ['3" — 88,9×4', {boru_d:88.9, boru_t:4}],
      ['4" — 114,3×4,5', {boru_d:114.3, boru_t:4.5}]
    ],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <!-- Pipe perspective -->
        <ellipse cx="80" cy="110" rx="40" ry="60" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="80" cy="110" rx="30" ry="48" fill="#0a0e1a" stroke="#1e4a9e" stroke-width="1.5"/>
        <path d="M80,50 L240,50 A40,60 0 0 1 240,170 L80,170" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="240" cy="110" rx="40" ry="60" fill="#9aa9bc" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="240" cy="110" rx="30" ry="48" fill="#1a2434" stroke="#1e4a9e" stroke-width="1.5"/>
        <!-- Labels -->
        <line x1="40" y1="50" x2="40" y2="170" stroke="#c8102e" stroke-width="1"/>
        <line x1="35" y1="50" x2="45" y2="50" stroke="#c8102e" stroke-width="1.5"/>
        <line x1="35" y1="170" x2="45" y2="170" stroke="#c8102e" stroke-width="1.5"/>
        <text x="30" y="115" text-anchor="middle" transform="rotate(-90, 30, 115)" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">Ø${v.boru_d || 0}</text>
        <text x="160" y="195" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.boru_l || 0} mm</text>
        <line x1="80" y1="195" x2="240" y2="195" stroke="#c8102e" stroke-width="1"/>
        <text x="80" y="40" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">t=${v.boru_t || 0}</text>
      </svg>`,
    calc: (v, rho) => {
      const D = (v.boru_d||0)/10, T = (v.boru_t||0)/10, L = (v.boru_l||0)/10, q = v.boru_q||1;
      const area = Math.PI * (D - T) * T;
      const unit = (area * L * rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`Ø${v.boru_d}×${v.boru_t}×${v.boru_l} mm`};
    }
  },

  'profil': {
    name: 'Kutu Profil', short: 'Kutu',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="10" y="10" width="28" height="28"/><rect x="14" y="14" width="20" height="20"/></svg>`,
    formula: '(A·B − (A−2t)(B−2t)) × L × ρ',
    formulaText: 'W (kg/m) = (A·B − (A−2t)(B−2t)) × ρ / 1.000',
    fields: [
      {id:'prof_a', label:'Kenar A', unit:'mm', value:40, step:0.1},
      {id:'prof_b', label:'Kenar B', unit:'mm', value:40, step:0.1},
      {id:'prof_t', label:'Et Kalınlığı', unit:'mm', value:2, step:0.1},
      {id:'prof_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'prof_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [
      ['20×20×1,5', {prof_a:20, prof_b:20, prof_t:1.5}],
      ['25×25×2', {prof_a:25, prof_b:25, prof_t:2}],
      ['30×30×2', {prof_a:30, prof_b:30, prof_t:2}],
      ['40×40×2', {prof_a:40, prof_b:40, prof_t:2}],
      ['50×30×2', {prof_a:50, prof_b:30, prof_t:2}],
      ['60×40×3', {prof_a:60, prof_b:40, prof_t:3}],
      ['80×80×3', {prof_a:80, prof_b:80, prof_t:3}],
      ['100×100×4', {prof_a:100, prof_b:100, prof_t:4}],
      ['120×60×4', {prof_a:120, prof_b:60, prof_t:4}]
    ],
    diagram: (v) => {
      const a = Math.min(Math.max(v.prof_a/2, 30), 90);
      const b = Math.min(Math.max(v.prof_b/2, 30), 90);
      return `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <!-- 3D box profile -->
        <rect x="${160-a}" y="${110-b}" width="${a*2}" height="${b*2}" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <rect x="${165-a}" y="${105-b}" width="${a*2-10}" height="${b*2-10}" fill="#0a0e1a"/>
        <line x1="${160+a}" y1="${110-b}" x2="${190+a}" y2="${80-b}" stroke="#1e4a9e" stroke-width="1.5"/>
        <line x1="${160+a}" y1="${110+b}" x2="${190+a}" y2="${80+b}" stroke="#1e4a9e" stroke-width="1.5"/>
        <line x1="${160-a}" y1="${110-b}" x2="${190-a}" y2="${80-b}" stroke="#1e4a9e" stroke-width="1.5"/>
        <line x1="${190-a}" y1="${80-b}" x2="${190+a}" y2="${80-b}" stroke="#1e4a9e" stroke-width="1.5"/>
        <line x1="${190+a}" y1="${80-b}" x2="${190+a}" y2="${80+b}" stroke="#1e4a9e" stroke-width="1.5"/>
        <!-- Labels -->
        <text x="160" y="${130+b+15}" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">A=${v.prof_a || 0}</text>
        <text x="${135-a}" y="115" text-anchor="end" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">B=${v.prof_b || 0}</text>
        <text x="${190+a+10}" y="${80-b-5}" font-family="JetBrains Mono" font-size="9" fill="#1e4a9e" font-weight="700">t=${v.prof_t || 0}</text>
      </svg>`;
    },
    calc: (v, rho) => {
      const a = (v.prof_a||0)/10, b = (v.prof_b||0)/10, t = (v.prof_t||0)/10;
      const L = (v.prof_l||0)/10, q = v.prof_q||1;
      const inner = Math.max((a-2*t)*(b-2*t), 0);
      const area = a*b - inner;
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`${v.prof_a}×${v.prof_b}×${v.prof_t}×${v.prof_l} mm`};
    }
  },

  'lama': {
    name: 'Lama / Düz Çubuk', short: 'Lama',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="8" y="20" width="32" height="8"/></svg>`,
    formula: 'w × t × L × ρ',
    formulaText: 'W (kg/m) = w × t × ρ / 1.000',
    fields: [
      {id:'lama_w', label:'Genişlik', unit:'mm', value:40, step:0.1},
      {id:'lama_t', label:'Kalınlık', unit:'mm', value:5, step:0.1},
      {id:'lama_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'lama_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['20×3',{lama_w:20,lama_t:3}],['25×5',{lama_w:25,lama_t:5}],['30×5',{lama_w:30,lama_t:5}],['40×6',{lama_w:40,lama_t:6}],['50×8',{lama_w:50,lama_t:8}],['60×10',{lama_w:60,lama_t:10}],['80×10',{lama_w:80,lama_t:10}],['100×12',{lama_w:100,lama_t:12}]],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="40" y="100" width="240" height="20" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <polygon points="40,100 60,80 300,80 280,100" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <polygon points="280,100 280,120 300,100 300,80" fill="#9aa9bc" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="140" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">w=${v.lama_w || 0}</text>
        <text x="320" y="115" text-anchor="end" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">t=${v.lama_t || 0}</text>
        <text x="160" y="180" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">L=${v.lama_l || 0} mm</text>
      </svg>`,
    calc: (v, rho) => {
      const w = (v.lama_w||0)/10, t = (v.lama_t||0)/10, L = (v.lama_l||0)/10, q = v.lama_q||1;
      const unit = (w*t*L*rho)/1000;
      return {unit, qty:q, kgPerM:(w*t*100*rho)/1000, descr:`${v.lama_w}×${v.lama_t}×${v.lama_l} mm`};
    }
  },

  'kosebent': {
    name: 'Köşebent (L)', short: 'Köşebent',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="10,10 18,10 18,30 38,30 38,38 10,38"/></svg>`,
    formula: '(A + B − t) × t × L × ρ',
    formulaText: 'W (kg/m) = (A + B − t) × t × ρ / 1.000',
    fields: [
      {id:'kos_a', label:'Kol A', unit:'mm', value:40, step:0.1},
      {id:'kos_b', label:'Kol B', unit:'mm', value:40, step:0.1},
      {id:'kos_t', label:'Et Kalınlığı', unit:'mm', value:4, step:0.1},
      {id:'kos_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'kos_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['20×20×3',{kos_a:20,kos_b:20,kos_t:3}],['30×30×3',{kos_a:30,kos_b:30,kos_t:3}],['40×40×4',{kos_a:40,kos_b:40,kos_t:4}],['50×50×5',{kos_a:50,kos_b:50,kos_t:5}],['60×60×6',{kos_a:60,kos_b:60,kos_t:6}],['80×80×8',{kos_a:80,kos_b:80,kos_t:8}],['100×100×10',{kos_a:100,kos_b:100,kos_t:10}]],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <polygon points="100,40 130,40 130,170 250,170 250,200 100,200" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="115" y="115" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">A=${v.kos_a || 0}</text>
        <text x="190" y="195" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">B=${v.kos_b || 0}</text>
        <text x="265" y="180" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">t=${v.kos_t || 0}</text>
      </svg>`,
    calc: (v, rho) => {
      const a = (v.kos_a||0)/10, b = (v.kos_b||0)/10, t = (v.kos_t||0)/10, L = (v.kos_l||0)/10, q = v.kos_q||1;
      const area = (a + b - t) * t;
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`${v.kos_a}×${v.kos_b}×${v.kos_t}×${v.kos_l} mm`};
    }
  },

  'mil': {
    name: 'Yuvarlak Mil', short: 'Yuv. Mil',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="24" cy="24" r="14" fill="currentColor" fill-opacity=".15"/></svg>`,
    formula: 'π × (D/2)² × L × ρ',
    formulaText: 'W (kg/m) = π × (D/2)² × ρ / 1.000',
    fields: [
      {id:'mil_d', label:'Çap', unit:'mm', value:20, step:0.1},
      {id:'mil_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'mil_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['Ø8',{mil_d:8}],['Ø10',{mil_d:10}],['Ø12',{mil_d:12}],['Ø16',{mil_d:16}],['Ø20',{mil_d:20}],['Ø25',{mil_d:25}],['Ø30',{mil_d:30}],['Ø40',{mil_d:40}],['Ø50',{mil_d:50}],['Ø60',{mil_d:60}],['Ø80',{mil_d:80}],['Ø100',{mil_d:100}]],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="80" cy="110" rx="35" ry="55" fill="#9aa9bc" stroke="#1e4a9e" stroke-width="1.5"/>
        <path d="M80,55 L240,55 A35,55 0 0 1 240,165 L80,165" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="240" cy="110" rx="35" ry="55" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="80" y="190" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">Ø${v.mil_d || 0}</text>
        <text x="160" y="195" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">L=${v.mil_l || 0} mm</text>
      </svg>`,
    calc: (v, rho) => {
      const d = (v.mil_d||0)/10, L = (v.mil_l||0)/10, q = v.mil_q||1;
      const area = Math.PI * Math.pow(d/2, 2);
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`Ø${v.mil_d}×${v.mil_l} mm`};
    }
  },

  'kare-mil': {
    name: 'Kare Mil', short: 'Kare',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="14" y="14" width="20" height="20" fill="currentColor" fill-opacity=".15"/></svg>`,
    formula: 'a² × L × ρ',
    formulaText: 'W (kg/m) = a² × ρ / 1.000',
    fields: [
      {id:'kare_a', label:'Kenar', unit:'mm', value:20, step:0.1},
      {id:'kare_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'kare_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['8×8',{kare_a:8}],['10×10',{kare_a:10}],['12×12',{kare_a:12}],['16×16',{kare_a:16}],['20×20',{kare_a:20}],['25×25',{kare_a:25}],['30×30',{kare_a:30}],['40×40',{kare_a:40}],['50×50',{kare_a:50}]],
    diagram: (v) => {
      const sz = Math.min(Math.max(v.kare_a, 30), 80);
      return `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="${160-sz}" y="${110-sz}" width="${sz*2}" height="${sz*2}" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="${130+sz+10}" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">a=${v.kare_a || 0}</text>
      </svg>`;
    },
    calc: (v, rho) => {
      const a = (v.kare_a||0)/10, L = (v.kare_l||0)/10, q = v.kare_q||1;
      const area = a * a;
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`${v.kare_a}×${v.kare_a}×${v.kare_l} mm`};
    }
  },

  'altigen': {
    name: 'Altıgen Mil', short: 'Altıgen',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="24,8 38,16 38,32 24,40 10,32 10,16" fill="currentColor" fill-opacity=".15"/></svg>`,
    formula: '(√3/2) × s² × L × ρ',
    formulaText: 'W (kg/m) = (√3/2) × s² × ρ / 1.000',
    fields: [
      {id:'hex_s', label:'Anahtar Ağzı (s)', unit:'mm', value:22, step:0.1},
      {id:'hex_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'hex_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['S=13',{hex_s:13}],['S=17',{hex_s:17}],['S=19',{hex_s:19}],['S=22',{hex_s:22}],['S=24',{hex_s:24}],['S=27',{hex_s:27}],['S=30',{hex_s:30}],['S=36',{hex_s:36}],['S=46',{hex_s:46}]],
    diagram: (v) => {
      const r = Math.min(Math.max(v.hex_s/1.5, 30), 80);
      return `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <polygon points="${160},${110-r} ${160+r*0.866},${110-r/2} ${160+r*0.866},${110+r/2} ${160},${110+r} ${160-r*0.866},${110+r/2} ${160-r*0.866},${110-r/2}" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <line x1="${160-r*0.866}" y1="${110+r/2+15}" x2="${160+r*0.866}" y2="${110+r/2+15}" stroke="#c8102e" stroke-width="1"/>
        <text x="160" y="${110+r/2+30}" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">s=${v.hex_s || 0}</text>
      </svg>`;
    },
    calc: (v, rho) => {
      const s = (v.hex_s||0)/10, L = (v.hex_l||0)/10, q = v.hex_q||1;
      const area = (Math.sqrt(3)/2) * s * s;
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`S=${v.hex_s}×${v.hex_l} mm`};
    }
  },

  'hea-heb': {
    name: 'I/H Profil (HEA/HEB/IPE)', short: 'I/H',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="10" y="8" width="28" height="6"/><rect x="22" y="14" width="4" height="20"/><rect x="10" y="34" width="28" height="6"/></svg>`,
    formula: 'kg/m × L (TS EN 10025 tablosundan)',
    formulaText: 'Standart kg/m değeri ile boy çarpılır',
    fields: [
      {id:'ibeam_size', label:'Profil', type:'select', value:'HEA200|42.3', options:[
        {label:'HEA — Geniş Başlıklı Hafif', items:[
          ['HEA100|16.7','HEA 100 — 16,7 kg/m'],['HEA120|19.9','HEA 120 — 19,9 kg/m'],
          ['HEA140|24.7','HEA 140 — 24,7'],['HEA160|30.4','HEA 160 — 30,4'],
          ['HEA180|35.5','HEA 180 — 35,5'],['HEA200|42.3','HEA 200 — 42,3'],
          ['HEA220|50.5','HEA 220 — 50,5'],['HEA240|60.3','HEA 240 — 60,3'],
          ['HEA260|68.2','HEA 260 — 68,2'],['HEA280|76.4','HEA 280 — 76,4'],
          ['HEA300|88.3','HEA 300 — 88,3'],['HEA400|125','HEA 400 — 125'],
          ['HEA500|155','HEA 500 — 155'],['HEA600|178','HEA 600 — 178']
        ]},
        {label:'HEB — Geniş Başlıklı Standart', items:[
          ['HEB100|20.4','HEB 100 — 20,4'],['HEB120|26.7','HEB 120 — 26,7'],
          ['HEB140|33.7','HEB 140 — 33,7'],['HEB160|42.6','HEB 160 — 42,6'],
          ['HEB180|51.2','HEB 180 — 51,2'],['HEB200|61.3','HEB 200 — 61,3'],
          ['HEB240|83.2','HEB 240 — 83,2'],['HEB300|117','HEB 300 — 117'],
          ['HEB400|155','HEB 400 — 155'],['HEB500|187','HEB 500 — 187'],
          ['HEB600|212','HEB 600 — 212']
        ]},
        {label:'IPE — Avrupa Standart', items:[
          ['IPE80|6','IPE 80 — 6'],['IPE100|8.1','IPE 100 — 8,1'],
          ['IPE120|10.4','IPE 120 — 10,4'],['IPE140|12.9','IPE 140 — 12,9'],
          ['IPE160|15.8','IPE 160 — 15,8'],['IPE180|18.8','IPE 180 — 18,8'],
          ['IPE200|22.4','IPE 200 — 22,4'],['IPE240|30.7','IPE 240 — 30,7'],
          ['IPE300|42.2','IPE 300 — 42,2'],['IPE400|66.3','IPE 400 — 66,3'],
          ['IPE500|90.7','IPE 500 — 90,7'],['IPE600|122','IPE 600 — 122']
        ]}
      ]},
      {id:'ibeam_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'ibeam_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="100" y="40" width="120" height="20" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <rect x="150" y="60" width="20" height="100" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <rect x="100" y="160" width="120" height="20" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="210" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${(v.ibeam_size||'').split('|')[0]}</text>
        <text x="160" y="30" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">${(v.ibeam_size||'').split('|')[1] || '—'} kg/m</text>
      </svg>`,
    calc: (v, rho) => {
      const sel = (v.ibeam_size||'').split('|');
      const kgM = parseFloat(sel[1]) || 0;
      const L = (v.ibeam_l||0)/1000;
      const q = v.ibeam_q||1;
      const unit = kgM * L;
      return {unit, qty:q, kgPerM:kgM, descr:`${sel[0]}×${v.ibeam_l} mm`, ignoreDensity:true};
    }
  },

  'npu-npi': {
    name: 'U Profil (NPU)', short: 'U Profil',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 8 v32 h24 v-32 v6 h-18 v22 h12 v-22"/></svg>`,
    formula: 'kg/m × L (DIN 1026 tablosundan)',
    formulaText: 'Standart UPN kg/m değeri ile boy çarpılır',
    fields: [
      {id:'upn_size', label:'Profil', type:'select', value:'UPN160|18.8', options:[
        {label:'UPN / NPU — U Profil', items:[
          ['UPN50|5.59','UPN 50 — 5,59'],['UPN65|7.09','UPN 65 — 7,09'],
          ['UPN80|8.64','UPN 80 — 8,64'],['UPN100|10.6','UPN 100 — 10,6'],
          ['UPN120|13.4','UPN 120 — 13,4'],['UPN140|16','UPN 140 — 16'],
          ['UPN160|18.8','UPN 160 — 18,8'],['UPN180|22','UPN 180 — 22'],
          ['UPN200|25.3','UPN 200 — 25,3'],['UPN240|33.2','UPN 240 — 33,2'],
          ['UPN300|46.2','UPN 300 — 46,2'],['UPN400|71.8','UPN 400 — 71,8']
        ]}
      ]},
      {id:'upn_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'upn_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <path d="M100,40 L120,40 L120,160 L200,160 L200,40 L220,40 L220,180 L100,180 Z" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="210" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${(v.upn_size||'').split('|')[0]}</text>
        <text x="160" y="30" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">${(v.upn_size||'').split('|')[1] || '—'} kg/m</text>
      </svg>`,
    calc: (v, rho) => {
      const sel = (v.upn_size||'').split('|');
      const kgM = parseFloat(sel[1]) || 0;
      const L = (v.upn_l||0)/1000;
      const q = v.upn_q||1;
      return {unit:kgM*L, qty:q, kgPerM:kgM, descr:`${sel[0]}×${v.upn_l} mm`, ignoreDensity:true};
    }
  },

  't-profil': {
    name: 'T Profil', short: 'T',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="8" y="8" width="32" height="6"/><rect x="22" y="14" width="4" height="26"/></svg>`,
    formula: '(A·t + (B−t)·t) × L × ρ',
    formulaText: 'W (kg/m) = (A·t + (B−t)·t) × ρ / 1.000',
    fields: [
      {id:'tprof_a', label:'Genişlik A', unit:'mm', value:50, step:0.1},
      {id:'tprof_b', label:'Yükseklik B', unit:'mm', value:50, step:0.1},
      {id:'tprof_t', label:'Et Kalınlığı', unit:'mm', value:6, step:0.1},
      {id:'tprof_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'tprof_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['30×30×4',{tprof_a:30,tprof_b:30,tprof_t:4}],['40×40×5',{tprof_a:40,tprof_b:40,tprof_t:5}],['50×50×6',{tprof_a:50,tprof_b:50,tprof_t:6}],['60×60×7',{tprof_a:60,tprof_b:60,tprof_t:7}],['80×80×9',{tprof_a:80,tprof_b:80,tprof_t:9}],['100×100×11',{tprof_a:100,tprof_b:100,tprof_t:11}]],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="100" y="40" width="120" height="20" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <rect x="150" y="60" width="20" height="120" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="35" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">A=${v.tprof_a || 0}</text>
        <text x="100" y="125" text-anchor="end" font-family="JetBrains Mono" font-size="10" fill="#c8102e" font-weight="700">B=${v.tprof_b || 0}</text>
      </svg>`,
    calc: (v, rho) => {
      const a = (v.tprof_a||0)/10, b = (v.tprof_b||0)/10, t = (v.tprof_t||0)/10, L = (v.tprof_l||0)/10, q = v.tprof_q||1;
      const area = (a*t) + ((b-t)*t);
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`T${v.tprof_a}×${v.tprof_b}×${v.tprof_t}×${v.tprof_l} mm`};
    }
  },

  'oval': {
    name: 'Oval / Eliptik', short: 'Oval',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><ellipse cx="24" cy="24" rx="16" ry="10"/><ellipse cx="24" cy="24" rx="12" ry="6"/></svg>`,
    formula: 'π × ((a·b) − (a−t)(b−t)) × L × ρ',
    formulaText: 'Eliptik içi boş profil',
    fields: [
      {id:'oval_a', label:'Büyük Çap a', unit:'mm', value:50, step:0.1},
      {id:'oval_b', label:'Küçük Çap b', unit:'mm', value:30, step:0.1},
      {id:'oval_t', label:'Et Kalınlığı', unit:'mm', value:2, step:0.1},
      {id:'oval_l', label:'Boy', unit:'mm', value:6000, step:1},
      {id:'oval_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['50×30×2',{oval_a:50,oval_b:30,oval_t:2}],['60×40×2',{oval_a:60,oval_b:40,oval_t:2}],['80×40×3',{oval_a:80,oval_b:40,oval_t:3}]],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="160" cy="110" rx="80" ry="40" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="160" cy="110" rx="68" ry="28" fill="#fafbfd" stroke="#1e4a9e" stroke-width="1.5"/>
        <text x="160" y="170" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.oval_a||0}×${v.oval_b||0}×${v.oval_t||0}</text>
      </svg>`,
    calc: (v, rho) => {
      const a = (v.oval_a||0)/10, b = (v.oval_b||0)/10, t = (v.oval_t||0)/10, L = (v.oval_l||0)/10, q = v.oval_q||1;
      const outer = Math.PI * (a/2) * (b/2);
      const inner = Math.PI * Math.max(a/2-t,0) * Math.max(b/2-t,0);
      const area = outer - inner;
      const unit = (area*L*rho)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho)/1000, descr:`Oval ${v.oval_a}×${v.oval_b}×${v.oval_t}×${v.oval_l} mm`};
    }
  },

  'hasir': {
    name: 'Çelik Hasır', short: 'Hasır',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><line x1="10" y1="14" x2="38" y2="14"/><line x1="10" y1="24" x2="38" y2="24"/><line x1="10" y1="34" x2="38" y2="34"/><line x1="14" y1="10" x2="14" y2="38"/><line x1="24" y1="10" x2="24" y2="38"/><line x1="34" y1="10" x2="34" y2="38"/></svg>`,
    formula: 'kg/m² × 10,75 (5×2,15 m levha)',
    formulaText: 'Standart 5,00 × 2,15 m hasır levha — kg/m² × m²',
    fields: [
      {id:'hasir_size', label:'Hasır Tipi', type:'select', value:'Q335|5.24', options:[
        {label:'Q Tipi (Eşit Donatılı)', items:[
          ['Q106|1.69','Q 106 — 1,69 kg/m²'],['Q131|2.04','Q 131 — 2,04'],
          ['Q158|2.46','Q 158 — 2,46'],['Q188|2.93','Q 188 — 2,93'],
          ['Q221|3.45','Q 221 — 3,45'],['Q257|4.01','Q 257 — 4,01'],
          ['Q295|4.61','Q 295 — 4,61'],['Q335|5.24','Q 335 — 5,24'],
          ['Q378|5.92','Q 378 — 5,92'],['Q424|6.62','Q 424 — 6,62'],
          ['Q524|8.18','Q 524 — 8,18'],['Q636|9.93','Q 636 — 9,93']
        ]},
        {label:'R Tipi (Tek Yön)', items:[
          ['R131|1.91','R 131 — 1,91'],['R158|2.30','R 158 — 2,30'],
          ['R188|2.74','R 188 — 2,74'],['R221|3.20','R 221 — 3,20'],
          ['R257|3.74','R 257 — 3,74'],['R317|4.61','R 317 — 4,61']
        ]}
      ]},
      {id:'hasir_q', label:'Levha Adedi', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [],
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <g stroke="#1e4a9e" stroke-width="1.2">
          ${[40,80,120,160,200,240,280].map(x=>`<line x1="${x}" y1="40" x2="${x}" y2="180"/>`).join('')}
          ${[40,70,100,130,160,180].map(y=>`<line x1="40" y1="${y}" x2="280" y2="${y}"/>`).join('')}
        </g>
        <text x="160" y="210" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${(v.hasir_size||'').split('|')[0]} — 5×2,15 m</text>
      </svg>`,
    tip: '📐 Standart hasır levha boyutu: 5,00 × 2,15 m = 10,75 m². Q tipi her iki yönde, R tipi tek yönde donatılıdır.',
    calc: (v, rho) => {
      const sel = (v.hasir_size||'').split('|');
      const kgM2 = parseFloat(sel[1])||0;
      const q = v.hasir_q||1;
      const unit = kgM2 * 10.75;
      return {unit, qty:q, kgPerM:0, descr:`${sel[0]} (5×2,15 m levha)`, ignoreDensity:true};
    }
  },

  'nervurlu': {
    name: 'Nervürlü Demir', short: 'Nervürlü',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M10 24 Q12 20 14 24 Q16 28 18 24 Q20 20 22 24 Q24 28 26 24 Q28 20 30 24 Q32 28 34 24 Q36 20 38 24"/></svg>`,
    formula: 'π × (D/2)² × L × ρ × 1,02',
    formulaText: 'Yivler için %2 ek pay',
    fields: [
      {id:'nerv_d', label:'Çap', unit:'mm', value:12, step:0.1},
      {id:'nerv_l', label:'Boy', unit:'mm', value:12000, step:1},
      {id:'nerv_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [['Ø8',{nerv_d:8}],['Ø10',{nerv_d:10}],['Ø12',{nerv_d:12}],['Ø14',{nerv_d:14}],['Ø16',{nerv_d:16}],['Ø18',{nerv_d:18}],['Ø20',{nerv_d:20}],['Ø22',{nerv_d:22}],['Ø25',{nerv_d:25}],['Ø28',{nerv_d:28}],['Ø32',{nerv_d:32}]],
    tip: '⚠ Nervürlü demir ağırlığı yivler nedeniyle teorik dolu çelik milin %2 fazlasıdır. BÇIII-A standardına göredir.',
    diagram: (v) => `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <ellipse cx="80" cy="110" rx="22" ry="48" fill="#9aa9bc" stroke="#1e4a9e" stroke-width="1.5"/>
        <path d="M80,62 L240,62 A22,48 0 0 1 240,158 L80,158" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1.5"/>
        <ellipse cx="240" cy="110" rx="22" ry="48" fill="#dde3eb" stroke="#1e4a9e" stroke-width="1.5"/>
        <g stroke="#1e4a9e" stroke-width="1.2" fill="none">
          ${[100,120,140,160,180,200,220].map(x=>`<path d="M${x},65 Q${x+5},55 ${x+10},65 M${x},155 Q${x+5},165 ${x+10},155"/>`).join('')}
        </g>
        <text x="160" y="195" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">Ø${v.nerv_d || 0}×${v.nerv_l || 0} mm</text>
      </svg>`,
    calc: (v, rho) => {
      const d = (v.nerv_d||0)/10, L = (v.nerv_l||0)/10, q = v.nerv_q||1;
      const area = Math.PI * Math.pow(d/2, 2);
      const unit = (area*L*rho*1.02)/1000;
      return {unit, qty:q, kgPerM:(area*100*rho*1.02)/1000, descr:`Ø${v.nerv_d} nervürlü × ${v.nerv_l} mm`};
    }
  },

  'genisletilmis': {
    name: 'Genişletilmiş Sac (ÖVL)', short: 'Genişletilmiş',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M8 16 L14 12 L20 16 L14 20 Z M20 16 L26 12 L32 16 L26 20 Z M32 16 L38 12 L44 16 L38 20 Z M14 24 L20 20 L26 24 L20 28 Z M26 24 L32 20 L38 24 L32 28 Z M8 32 L14 28 L20 32 L14 36 Z M20 32 L26 28 L32 32 L26 36 Z M32 32 L38 28 L44 32 L38 36 Z"/></svg>`,
    formula: 'düz_sac_ağırlığı × (1 − boşluk_oranı/100)',
    formulaText: 'W (kg) = (kalınlık × en × boy × ρ) / 1.000.000 × (1 − boşluk%/100)',
    fields: [
      {id:'gen_mesh', label:'Göz Aralığı (LWD × SWD)', type:'select', value:'62×20|70', options:[
        {label:'Standart Göz Aralıkları (yaklaşık boşluk oranlarıyla)', items:[
          ['22×10|70','22×10 mm — yaklaşık %70 boşluk (ince fitre, akustik)'],
          ['30×12|72','30×12 mm — yaklaşık %72 boşluk (filtre, dekoratif)'],
          ['43×13|75','43×13 mm — yaklaşık %75 boşluk (orta dekoratif/filtre)'],
          ['62×20|70','62×20 mm — yaklaşık %70 boşluk (en yaygın, cephe)'],
          ['76×24|65','76×24 mm — yaklaşık %65 boşluk (yürüme yolu)'],
          ['100×40|60','100×40 mm — yaklaşık %60 boşluk (ağır sanayi)'],
          ['125×50|55','125×50 mm — yaklaşık %55 boşluk (büyük göz)'],
          ['custom|0','Özel — Boşluk Oranını manuel gir']
        ]}
      ]},
      {id:'gen_open_pct', label:'Boşluk Oranı (özel için)', unit:'%', value:0, step:1, min:0, max:90},
      {id:'gen_t', label:'Ham Sac Kalınlığı', unit:'mm', value:2, step:0.1},
      {id:'gen_w', label:'En', unit:'mm', value:1000, step:1},
      {id:'gen_l', label:'Boy', unit:'mm', value:2000, step:1},
      {id:'gen_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [
      ['2 mm · 1000×2000', {gen_t:2, gen_w:1000, gen_l:2000}],
      ['3 mm · 1250×2500', {gen_t:3, gen_w:1250, gen_l:2500}],
      ['4 mm · 1500×3000', {gen_t:4, gen_w:1500, gen_l:3000}]
    ],
    tip: '⚠ Genişletilmiş sac (ÖVL/expanded metal) ağırlığı, ham sacın boşluk oranı kadar azaltılmasıyla hesaplanır. Standart göz aralıklarının boşluk oranları yaklaşıktır — kataloğa göre değişir. Tam doğru hesap için "Özel" seçip Boşluk Oranı (%) alanına gerçek değeri girin. Kaynak: plmesh, dkpsac.com.tr.',
    diagram: (v) => {
      const meshLabel = (v.gen_mesh||'').split('|')[0] || '62×20';
      // 3x2 ızgara genişletilmiş sac diyagramı (paralelogram/diamond pattern)
      const diamonds = [];
      for (let row = 0; row < 4; row++) {
        for (let col = 0; col < 5; col++) {
          const cx = 80 + col*40;
          const cy = 70 + row*30;
          const offX = (row % 2) * 20; // şahmati offset
          diamonds.push(`<polygon points="${cx+offX-15},${cy} ${cx+offX},${cy-12} ${cx+offX+15},${cy} ${cx+offX},${cy+12}" fill="#bcc7d6" stroke="#1e4a9e" stroke-width="1"/>`);
        }
      }
      return `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="60" y="50" width="200" height="120" fill="#f0f3f8" stroke="#1e4a9e" stroke-width="1.5"/>
        ${diamonds.join('')}
        <line x1="60" y1="185" x2="260" y2="185" stroke="#c8102e" stroke-width="1"/>
        <line x1="60" y1="180" x2="60" y2="190" stroke="#c8102e" stroke-width="1.5"/>
        <line x1="260" y1="180" x2="260" y2="190" stroke="#c8102e" stroke-width="1.5"/>
        <text x="160" y="200" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.gen_w || 0} × ${v.gen_l || 0} mm</text>
        <text x="160" y="40" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">Göz: ${meshLabel} mm · Kalınlık: ${v.gen_t || 0} mm</text>
      </svg>`;
    },
    calc: (v, rho) => {
      const sel = (v.gen_mesh||'62×20|70').split('|');
      const meshLabel = sel[0];
      const presetOpen = parseFloat(sel[1]) || 0;
      const customOpen = parseFloat(v.gen_open_pct) || 0;
      // Eğer "Özel" seçildiyse (presetOpen=0) veya kullanıcı manual değer girdiyse: customOpen kullan
      // Aksi halde preset kullan
      const openPct = (presetOpen === 0 || customOpen > 0) ? customOpen : presetOpen;
      const t = (v.gen_t||0)/10, w = (v.gen_w||0)/10, l = (v.gen_l||0)/10, q = v.gen_q||1;
      const flatWeight = (t*w*l*rho)/1000;
      const unit = flatWeight * (1 - Math.min(openPct, 90)/100);
      return {
        unit,
        qty:q,
        kgPerM:0,
        descr:`Göz ${meshLabel} mm · ${v.gen_t}×${v.gen_w}×${v.gen_l} mm · Boşluk %${openPct.toFixed(1)} · Düz ağırlığı: ${flatWeight.toFixed(2)} kg`
      };
    }
  },

  'delikli': {
    name: 'Delikli Sac (Perfore)', short: 'Delikli',
    icon: `<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="6" y="8" width="36" height="32" rx="1"/><circle cx="14" cy="16" r="2.2"/><circle cx="24" cy="16" r="2.2"/><circle cx="34" cy="16" r="2.2"/><circle cx="19" cy="24" r="2.2"/><circle cx="29" cy="24" r="2.2"/><circle cx="14" cy="32" r="2.2"/><circle cx="24" cy="32" r="2.2"/><circle cx="34" cy="32" r="2.2"/></svg>`,
    formula: 'düz_sac × (1 − açıklık%/100); açıklık% = K × (d/p)²',
    formulaText: 'W (kg) = (kalınlık × en × boy × ρ) / 1.000.000 × (1 − açıklık%/100)',
    fields: [
      {id:'del_pattern', label:'Delik Tipi ve Diziliş', type:'select', value:'round60|90.7', options:[
        {label:'Yuvarlak Delikler (en yaygın)', items:[
          ['round60|90.7','Yuvarlak — 60° şahmati (RV) — en yaygın'],
          ['round90|78.5','Yuvarlak — 90° kare dizilim (RU)']
        ]},
        {label:'Kare ve Slot Delikler', items:[
          ['square90|100','Kare delik — 90° kare dizilim'],
          ['square60|115.5','Kare delik — 60° şahmati'],
          ['slot|0','Slot/Yarık — açıklık manuel']
        ]},
        {label:'Standart Yuvarlak Preset Kombinasyonlar', items:[
          ['preset_5_8|35.4','Ø5 / p8 mm — %35,4 (filtre, dekoratif)'],
          ['preset_8_12|40.3','Ø8 / p12 mm — %40,3 (HVAC, endüstri)'],
          ['preset_10_15|40.3','Ø10 / p15 mm — %40,3 (havalandırma)'],
          ['preset_15_22|42.1','Ø15 / p22 mm — %42,1 (cephe, eleme)'],
          ['preset_20_28|46.2','Ø20 / p28 mm — %46,2 (estetik panel)']
        ]},
        {label:'Manuel Giriş', items:[
          ['custom|0','Özel — Açıklık Oranı manuel gir']
        ]}
      ]},
      {id:'del_d', label:'Delik Ölçüsü (Ø çap veya kenar)', unit:'mm', value:8, step:0.1},
      {id:'del_p', label:'Pitch (delik merkezleri arası)', unit:'mm', value:12, step:0.1},
      {id:'del_open_pct', label:'Açıklık Oranı (preset/özel için)', unit:'%', value:0, step:1, min:0, max:90},
      {id:'del_t', label:'Sac Kalınlığı', unit:'mm', value:2, step:0.1},
      {id:'del_w', label:'En', unit:'mm', value:1000, step:1},
      {id:'del_l', label:'Boy', unit:'mm', value:2000, step:1},
      {id:'del_q', label:'Adet', unit:'ad', value:1, step:1, min:1}
    ],
    presets: [
      ['Ø5/p8 · 1×2 m', {del_pattern:'round60|90.7', del_d:5, del_p:8, del_t:1.5, del_w:1000, del_l:2000}],
      ['Ø8/p12 · 1×2 m', {del_pattern:'round60|90.7', del_d:8, del_p:12, del_t:2, del_w:1000, del_l:2000}],
      ['Ø10/p15 · 1.25×2.5 m', {del_pattern:'round60|90.7', del_d:10, del_p:15, del_t:3, del_w:1250, del_l:2500}]
    ],
    tip: '⚠ Endüstri formülü: Yuvarlak delik 60° şahmati için Açıklık% = 90,7 × (d/p)², 90° kare için 78,5 × (d/p)², kare delik için 100 × (s/p)². Slot/yarık veya özel patterns için "Açıklık Oranı"nı manuel girin (preset_* seçenekleri sabit yüzde kullanır). Kaynak: LPS Lamiere Perforate, Hendrick Corp endüstri standartları.',
    diagram: (v) => {
      const sel = (v.del_pattern || 'round60|90.7').split('|');
      const pattern = sel[0];
      const d = parseFloat(v.del_d) || 8;
      const p = parseFloat(v.del_p) || 12;
      // Diyagram için scale: pitch'e göre normalize et — 6×4 ızgara
      const scale = Math.max(20, Math.min(40, 240/p));
      const dotR = Math.max(2, (d/p) * scale * 0.5);
      const stepX = scale;
      const stepY = scale * 0.866; // 60° için
      const isSquarePattern = pattern.startsWith('square');
      const isStaggered60 = pattern.endsWith('60');
      const isSquareHole = pattern.startsWith('square');
      const dots = [];
      for (let row = 0; row < 5; row++) {
        for (let col = 0; col < 7; col++) {
          const cx = 70 + col*stepX + (isStaggered60 && row%2 ? stepX/2 : 0);
          const cy = 60 + row*(isStaggered60 ? stepY : stepX);
          if (cx > 280 || cy > 175) continue;
          if (isSquareHole) {
            dots.push(`<rect x="${cx-dotR}" y="${cy-dotR}" width="${dotR*2}" height="${dotR*2}" fill="#143672" stroke="#0c1e44" stroke-width="0.5"/>`);
          } else {
            dots.push(`<circle cx="${cx}" cy="${cy}" r="${dotR}" fill="#143672" stroke="#0c1e44" stroke-width="0.5"/>`);
          }
        }
      }
      const patternName = pattern.startsWith('preset')
        ? sel[0].replace('preset_','Ø').replace('_','/p') + ' mm'
        : (pattern === 'round60' ? 'Yuvarlak 60° şahmati'
          : pattern === 'round90' ? 'Yuvarlak 90° kare'
          : pattern === 'square90' ? 'Kare 90°'
          : pattern === 'square60' ? 'Kare 60° şahmati'
          : pattern === 'slot' ? 'Slot/Yarık'
          : 'Özel');
      return `
      <svg viewBox="0 0 320 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="60" y="50" width="200" height="130" fill="#fafaf7" stroke="#1e4a9e" stroke-width="1.5"/>
        ${dots.join('')}
        <line x1="60" y1="195" x2="260" y2="195" stroke="#c8102e" stroke-width="1"/>
        <line x1="60" y1="190" x2="60" y2="200" stroke="#c8102e" stroke-width="1.5"/>
        <line x1="260" y1="190" x2="260" y2="200" stroke="#c8102e" stroke-width="1.5"/>
        <text x="160" y="210" text-anchor="middle" font-family="JetBrains Mono" font-size="11" fill="#c8102e" font-weight="700">${v.del_w || 0} × ${v.del_l || 0} mm</text>
        <text x="160" y="40" text-anchor="middle" font-family="JetBrains Mono" font-size="10" fill="#1e4a9e" font-weight="700">${patternName} · Ø${d}/p${p} · t=${v.del_t||0} mm</text>
      </svg>`;
    },
    calc: (v, rho) => {
      const sel = (v.del_pattern||'round60|90.7').split('|');
      const patternKey = sel[0];
      const presetOpen = parseFloat(sel[1]) || 0;
      const customOpen = parseFloat(v.del_open_pct) || 0;
      const d = parseFloat(v.del_d) || 0;
      const p = parseFloat(v.del_p) || 0;

      let openPct = 0;
      let calcSource = '';

      if (patternKey.startsWith('preset_')) {
        // Preset değer kullan
        openPct = presetOpen;
        calcSource = 'preset';
      } else if (patternKey === 'custom' || patternKey === 'slot') {
        // Manuel açıklık girişi
        openPct = customOpen;
        calcSource = 'manuel';
      } else if (customOpen > 0) {
        // Pattern seçilmiş ama kullanıcı manuel override etmiş
        openPct = customOpen;
        calcSource = 'manuel-override';
      } else if (d > 0 && p > 0 && presetOpen > 0) {
        // d/p formülü: %OA = K × (d/p)²
        openPct = presetOpen * Math.pow(d/p, 2);
        calcSource = 'formül';
      } else {
        openPct = 0;
      }

      openPct = Math.min(Math.max(openPct, 0), 90); // güvenlik [0..90]

      const t = (v.del_t||0)/10, w = (v.del_w||0)/10, l = (v.del_l||0)/10, q = v.del_q||1;
      const flatWeight = (t*w*l*rho)/1000;
      const unit = flatWeight * (1 - openPct/100);

      const patternName = patternKey === 'round60' ? 'Yuvarlak 60° şahmati'
        : patternKey === 'round90' ? 'Yuvarlak 90° kare'
        : patternKey === 'square90' ? 'Kare 90°'
        : patternKey === 'square60' ? 'Kare 60° şahmati'
        : patternKey === 'slot' ? 'Slot/Yarık'
        : patternKey.startsWith('preset_') ? patternKey.replace('preset_','Ø').replace('_','/p')+' mm'
        : 'Özel';

      return {
        unit,
        qty:q,
        kgPerM:0,
        descr:`${patternName} · Ø${d}/p${p} mm · ${v.del_t}×${v.del_w}×${v.del_l} mm · Açıklık %${openPct.toFixed(1)} (${calcSource}) · Düz ağırlığı: ${flatWeight.toFixed(2)} kg`
      };
    }
  }
};

// ═══════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════
let activeProductKey = 'sac';
let activeDensity = 7.85;
let cart = [];
let lastResult = null;

// ═══════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════
function $(id){ return document.getElementById(id); }
function fmt(v, decimals=2){
  if (!isFinite(v) || v === 0) return '—';
  return v.toLocaleString('tr-TR', {minimumFractionDigits:decimals, maximumFractionDigits:3});
}
function getValues(){
  const product = PRODUCTS[activeProductKey];
  const v = {};
  product.fields.forEach(f => {
    const el = $(f.id);
    if (!el) return;
    if (f.type === 'select') v[f.id] = el.value;
    else v[f.id] = parseFloat(el.value) || 0;
  });
  return v;
}

// ═══════════════════════════════════════════════════════
// RENDER PRODUCTS GRID
// ═══════════════════════════════════════════════════════
function renderProducts(){
  const grid = $('hesProducts');
  grid.innerHTML = Object.entries(PRODUCTS).map(([key, p]) => `
    <div class="hes-product ${key === activeProductKey ? 'active' : ''}" data-key="${key}">
      <div class="hes-product-icon">${p.icon}</div>
      <div class="hes-product-name">${p.short}</div>
    </div>
  `).join('');
  grid.querySelectorAll('.hes-product').forEach(el => {
    el.addEventListener('click', () => {
      activeProductKey = el.dataset.key;
      grid.querySelectorAll('.hes-product').forEach(x => x.classList.remove('active'));
      el.classList.add('active');
      renderInputs();
      compute();
    });
  });
}

// ═══════════════════════════════════════════════════════
// RENDER INPUT FIELDS
// ═══════════════════════════════════════════════════════
function renderInputs(){
  const product = PRODUCTS[activeProductKey];
  $('hesProductTitle').textContent = product.name;
  $('hesProductFormula').textContent = product.formula;
  $('hesFormulaText').textContent = product.formulaText;

  const fields = $('hesFields');
  const cols = product.fields.find(f => f.type === 'select') ? 'cols-1' : (product.fields.length === 3 ? 'cols-3' : '');
  fields.className = 'hes-fields ' + cols;

  fields.innerHTML = product.fields.map(f => {
    if (f.type === 'select') {
      const opts = f.options.map(g => `
        <optgroup label="${g.label}">
          ${g.items.map(([val, lbl]) => `<option value="${val}" ${val === f.value ? 'selected' : ''}>${lbl}</option>`).join('')}
        </optgroup>
      `).join('');
      return `
        <div class="hes-field hes-field-full">
          <label>${f.label}</label>
          <div class="hes-field-input-wrap">
            <select id="${f.id}">${opts}</select>
          </div>
        </div>`;
    }
    return `
      <div class="hes-field">
        <label>${f.label}</label>
        <div class="hes-field-input-wrap">
          <input type="number" id="${f.id}" value="${f.value}" step="${f.step}" ${f.min !== undefined ? 'min="'+f.min+'"' : ''}>
          <span class="hes-field-unit">${f.unit}</span>
        </div>
      </div>`;
  }).join('');

  // Listen for changes
  product.fields.forEach(f => {
    const el = $(f.id);
    if (el) {
      el.addEventListener('input', compute);
      el.addEventListener('change', compute);
    }
  });

  // Render presets
  const presetsWrap = $('hesPresetsWrap');
  if (product.presets && product.presets.length > 0) {
    presetsWrap.style.display = 'block';
    $('hesPresets').innerHTML = product.presets.map(([label, vals]) => {
      const dataAttr = Object.entries(vals).map(([k,v]) => `${k}:${v}`).join('|');
      return `<button type="button" class="hes-preset" data-set="${dataAttr}">${label}</button>`;
    }).join('');
    $('hesPresets').querySelectorAll('.hes-preset').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.dataset.set.split('|').forEach(s => {
          const [id, val] = s.split(':');
          const el = $(id);
          if (el) el.value = val;
        });
        compute();
      });
    });
  } else {
    presetsWrap.style.display = 'none';
  }

  // Tip box
  const tipEl = $('hesTip');
  if (product.tip) {
    tipEl.style.display = 'block';
    tipEl.innerHTML = product.tip;
  } else {
    tipEl.style.display = 'none';
  }
}

// ═══════════════════════════════════════════════════════
// COMPUTE — runs on every input change
// ═══════════════════════════════════════════════════════
function compute(){
  const product = PRODUCTS[activeProductKey];
  const v = getValues();
  const rho = activeDensity;
  const result = product.calc(v, rho);
  result.tab = activeProductKey;
  result.tabName = product.name;
  result.total = result.unit * (result.qty || 1);

  // Update result bar
  $('hesUnitKg').textContent = fmt(result.unit) + ' kg';
  $('hesQty').textContent = result.qty || 0;
  $('hesKgM').textContent = result.kgPerM > 0 ? fmt(result.kgPerM, 2) + ' kg/m' : '—';
  $('hesTotal').textContent = fmt(result.total) + ' kg';

  // Update diagram
  const diagSvg = product.diagram(v);
  $('hesDiagram').innerHTML = diagSvg;
  $('hesDiagramCaption').textContent = product.name;

  // Update WhatsApp link
  const phoneNumber = '<?= h(settings('site_whatsapp', '905320652400')) ?>';
  const wm = encodeURIComponent(`Merhaba, ${product.name} (${result.descr}) için fiyat almak istiyorum. Tahmini ağırlık: ${fmt(result.total)} kg.`);
  $('hesQuoteBtn').href = `https://wa.me/${phoneNumber}?text=${wm}`;

  // Update comparison if visible
  if ($('hesCompareWrap').style.display === 'block') {
    renderCompare();
  }

  // Update cost if visible
  if ($('hesCostBox').classList.contains('show')) {
    updateCost();
  }

  lastResult = result;
}

// ═══════════════════════════════════════════════════════
// MATERIAL PILLS
// ═══════════════════════════════════════════════════════
function setupMaterials(){
  const pills = document.querySelectorAll('.hes-material-pill');
  pills.forEach(p => {
    p.addEventListener('click', () => {
      pills.forEach(x => x.classList.remove('active'));
      p.classList.add('active');
      const d = p.dataset.density;
      const customWrap = $('hesMatCustomWrap');
      if (d === 'custom') {
        customWrap.classList.add('show');
        activeDensity = parseFloat($('hesMatCustom').value) || 7.85;
      } else {
        customWrap.classList.remove('show');
        activeDensity = parseFloat(d);
      }
      compute();
    });
  });
  $('hesMatCustom').addEventListener('input', () => {
    activeDensity = parseFloat($('hesMatCustom').value) || 7.85;
    compute();
  });
}

// ═══════════════════════════════════════════════════════
// COMPARISON
// ═══════════════════════════════════════════════════════
function renderCompare(){
  const product = PRODUCTS[activeProductKey];
  const v = getValues();
  const materials = [
    {name:'Çelik (Standart)', density:7.85},
    {name:'304 Paslanmaz', density:7.93},
    {name:'316 Paslanmaz', density:8.00},
    {name:'Alüminyum', density:2.70},
    {name:'Bakır', density:8.96},
    {name:'Pirinç', density:8.40},
    {name:'Bronz', density:8.73}
  ];
  const grid = $('hesCompareGrid');
  grid.innerHTML = materials.map(m => {
    const r = product.calc(v, m.density);
    if (r.ignoreDensity) {
      return `<div class="hes-compare-card">
        <h4>${m.name}</h4>
        <div class="hes-compare-card-value">${fmt(r.unit * (r.qty||1))} kg</div>
        <div class="hes-compare-card-density">profil tablosu (yoğunluk etkisiz)</div>
      </div>`;
    }
    const total = r.unit * (r.qty||1);
    return `<div class="hes-compare-card">
      <h4>${m.name}</h4>
      <div class="hes-compare-card-value">${fmt(total)} kg</div>
      <div class="hes-compare-card-density">ρ = ${m.density} g/cm³</div>
    </div>`;
  }).join('');
}

$('hesCompareBtn').addEventListener('click', () => {
  const w = $('hesCompareWrap');
  const visible = w.style.display === 'block';
  w.style.display = visible ? 'none' : 'block';
  if (!visible) renderCompare();
});
$('hesCompareToggle').addEventListener('click', () => {
  $('hesCompareWrap').style.display = 'none';
});

// ═══════════════════════════════════════════════════════
// COST CALCULATOR
// ═══════════════════════════════════════════════════════
function updateCost(){
  if (!lastResult) return;
  const price = parseFloat($('hesCostInput').value) || 0;
  const cost = lastResult.total * price;
  $('hesCostResult').textContent = cost.toLocaleString('tr-TR', {style:'currency', currency:'TRY'});
}
$('hesCostBtn').addEventListener('click', () => {
  const box = $('hesCostBox');
  box.classList.toggle('show');
  if (box.classList.contains('show')) updateCost();
});
$('hesCostInput').addEventListener('input', updateCost);

// ═══════════════════════════════════════════════════════
// CART
// ═══════════════════════════════════════════════════════
function renderCart(){
  const tbody = $('hesCartBody');
  tbody.innerHTML = cart.map((it, i) => `
    <tr>
      <td class="product">${it.tabName}</td>
      <td>${it.descr}</td>
      <td>${it.qty}</td>
      <td>${fmt(it.unit)}</td>
      <td><strong>${fmt(it.total)}</strong></td>
      <td class="actions"><button type="button" class="hes-cart-rm" data-rm="${i}">×</button></td>
    </tr>
  `).join('');

  const grand = cart.reduce((s,it) => s + it.total, 0);
  $('hesCartGrand').textContent = fmt(grand) + ' kg';
  $('hesCartCount').textContent = cart.length + ' kalem · ' + fmt(grand) + ' kg';
  $('hesCart').classList.toggle('show', cart.length > 0);

  // WhatsApp link
  const phoneNumber = '<?= h(settings('site_whatsapp', '905320652400')) ?>';
  const lines = cart.map((it,i) => `${i+1}. ${it.tabName}: ${it.descr} · ${it.qty} adet · ${fmt(it.total)} kg`).join('\n');
  const msg = encodeURIComponent(`Merhaba, aşağıdaki kalemler için fiyat almak istiyorum:\n\n${lines}\n\nGenel Toplam: ${fmt(grand)} kg`);
  $('hesCartQuote').href = `https://wa.me/${phoneNumber}?text=${msg}`;

  // Remove handlers
  tbody.querySelectorAll('[data-rm]').forEach(b => {
    b.addEventListener('click', () => {
      cart.splice(parseInt(b.dataset.rm), 1);
      renderCart();
    });
  });
}
$('hesAddBtn').addEventListener('click', () => {
  if (!lastResult || lastResult.unit === 0) {
    alert('Önce geçerli bir hesaplama yapın');
    return;
  }
  cart.push({...lastResult});
  renderCart();
  $('hesCart').scrollIntoView({behavior:'smooth', block:'nearest'});
});
$('hesCartClear').addEventListener('click', () => {
  if (!confirm('Listeyi tamamen silmek istiyor musunuz?')) return;
  cart = [];
  renderCart();
});

// ═══════════════════════════════════════════════════════
// MISC
// ═══════════════════════════════════════════════════════
$('hesPrintBtn').addEventListener('click', () => window.print());
$('hesResetBtn').addEventListener('click', () => {
  if (!confirm('Tüm değerleri sıfırlamak istiyor musunuz?')) return;
  renderInputs();
  compute();
});

// ═══════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════
renderProducts();
renderInputs();
setupMaterials();
compute();

})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
