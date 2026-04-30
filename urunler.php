<?php
require __DIR__ . '/includes/db.php';
$cat_slug = $_GET['kategori'] ?? '';
$search   = trim($_GET['q'] ?? '');

$cats = all("SELECT slug,name FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");

$where = ["p.is_active=1"];
$params = [];
if ($cat_slug) {
    $where[] = "c.slug = ?"; $params[] = $cat_slug;
}
if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.short_desc LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$whereSql = implode(' AND ', $where);

$products = all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
                 FROM tm_products p
                 LEFT JOIN tm_categories c ON c.id = p.category_id
                 WHERE $whereSql
                 ORDER BY p.is_featured DESC, p.sort_order, p.name", $params);

$activeCatName = '';
if ($cat_slug) {
    $activeCatName = (string) val("SELECT name FROM tm_categories WHERE slug=?", [$cat_slug]);
}

$totalCount = count($products);
$catCount   = count($cats);

$pageTitle = $cat_slug ? t('products.title_prefix', 'Ürünler') . ' — ' . $activeCatName : t('products.catalog_title', 'Ürün Katalogumuz');
$metaDesc  = 'Tekcan Metal ürün katalogu — sac, boru, profil, hadde, flanş, demir, panel ve daha fazlası. ' . $totalCount . ' aktif ürün, ' . $catCount . ' ana kategori.';
require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.urn-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --navy-3:#143672;
  --gold:#c9a86b;
  --gold-light:#e0c48a;
  --red:#c8102e;
  --red-dark:#a00d24;
  --paper:#fafaf7;
  --paper-2:#f3f1ec;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* HERO — ROYAL PALACE */
.urn-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;
  padding:130px 0 100px;
  position:relative;overflow:hidden;
  border-bottom:4px solid var(--gold);
}
.urn-hero::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.urn-hero::after{
  content:'TM';
  position:absolute;
  bottom:-90px;right:-30px;
  font-family:var(--serif);
  font-size:340px;font-weight:500;
  color:rgba(201,168,107,.06);
  letter-spacing:-15px;line-height:1;
  pointer-events:none;
}
.urn-hero .container{position:relative;z-index:2;text-align:center}
.urn-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:30px;
}
.urn-hero-eyebrow::before,
.urn-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.urn-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 86px);
  font-weight:500;line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 24px;color:#fff;
}
.urn-hero h1 em{font-style:italic;color:var(--gold)}
.urn-hero-lead{
  font-family:var(--sans);
  font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;margin:0 auto 40px;
}
.urn-hero-stats{
  display:flex;justify-content:center;gap:50px;flex-wrap:wrap;
  padding-top:30px;border-top:1px solid rgba(255,255,255,.12);
}
.urn-hero-stat{text-align:center}
.urn-hero-stat strong{
  display:block;
  font-family:var(--serif);font-size:40px;font-weight:500;
  color:var(--gold);line-height:1;letter-spacing:-1px;
}
.urn-hero-stat span{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-top:8px;display:block;
}

/* BREADCRUMB STRIP */
.urn-breadcrumb-strip{
  background:#fff;
  border-bottom:1px solid var(--line);
  padding:18px 0;
}
.urn-breadcrumb{
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
  font-family:var(--sans);font-size:12.5px;
}
.urn-breadcrumb a{
  color:#5a5a5a;text-decoration:none;
  transition:.15s;
  font-weight:500;
}
.urn-breadcrumb a:hover{color:var(--red)}
.urn-breadcrumb .sep{color:var(--gold)}
.urn-breadcrumb .current{
  color:var(--navy);
  font-weight:600;
  font-style:italic;
  font-family:var(--serif);
  font-size:14.5px;
}

/* CATEGORY FILTER NAV */
.urn-catnav-section{
  background:#fff;
  border-bottom:1px solid var(--line);
  padding:0;
  position:sticky;
  top:80px;
  z-index:30;
  box-shadow:0 2px 12px rgba(5,13,36,.04);
}
.urn-catnav{
  display:flex;
  gap:0;
  overflow-x:auto;
  scrollbar-width:none;
}
.urn-catnav::-webkit-scrollbar{display:none}
.urn-catnav-item{
  flex-shrink:0;
  text-decoration:none;color:inherit;
  padding:18px 24px;
  border-right:1px solid var(--line);
  transition:.18s;
  position:relative;
  font-family:var(--sans);
  font-size:12px;
  font-weight:600;
  letter-spacing:1px;
  text-transform:uppercase;
  color:#5a5a5a;
  white-space:nowrap;
}
.urn-catnav-item:hover{
  background:var(--paper);
  color:var(--navy);
}
.urn-catnav-item.active{
  background:var(--paper);
  color:var(--navy);
}
.urn-catnav-item.active::after{
  content:'';
  position:absolute;
  bottom:0;left:0;right:0;
  height:3px;
  background:var(--red);
}
.urn-catnav-item.special{
  background:var(--navy);
  color:#fff;
}
.urn-catnav-item.special:hover{
  background:var(--navy-2);
  color:#fff;
}

/* SEARCH STRIP */
.urn-search-strip{
  background:var(--paper);
  border-bottom:1px solid var(--line);
  padding:24px 0;
}
.urn-search-form{
  display:flex;
  gap:10px;
  align-items:center;
  max-width:760px;
  margin:0 auto;
  flex-wrap:wrap;
}
.urn-search-input{
  flex:1;
  min-width:240px;
  padding:14px 18px;
  font-family:var(--sans);
  font-size:14.5px;
  border:1.5px solid var(--line);
  background:#fff;
  color:var(--navy);
  transition:.15s;
}
.urn-search-input:focus{
  outline:0;
  border-color:var(--gold);
  box-shadow:0 0 0 3px rgba(201,168,107,.18);
}
.urn-search-btn{
  padding:14px 28px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  background:var(--navy);
  color:#fff;
  border:0;cursor:pointer;
  transition:.18s;
}
.urn-search-btn:hover{
  background:var(--gold);color:var(--navy);
}
.urn-search-clear{
  padding:14px 22px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:var(--red);
  background:transparent;
  border:1.5px solid var(--red);
  text-decoration:none;
  transition:.18s;
}
.urn-search-clear:hover{
  background:var(--red);color:#fff;
}

/* RESULTS BAR */
.urn-results-bar{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:40px;
  padding-bottom:24px;
  border-bottom:2px solid var(--navy);
  flex-wrap:wrap;gap:14px;
}
.urn-results-text{
  font-family:var(--serif);
  font-size:18px;font-style:italic;
  color:#5a5a5a;
}
.urn-results-text strong{
  font-family:var(--serif);
  font-size:32px;font-style:normal;
  color:var(--red);
  font-weight:600;
  margin-right:6px;
  letter-spacing:-.5px;
}
.urn-results-cat-info{
  font-family:var(--sans);
  font-size:12px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);
}

/* MAIN */
.urn-main{
  padding:70px 0 90px;
}

/* PRODUCT GRID — ROYAL CARDS */
.urn-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
  background:#fff;
  border:1px solid var(--line);
}
@media (max-width:1100px){.urn-grid{grid-template-columns:repeat(3,1fr)}}
@media (max-width:800px){.urn-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:480px){.urn-grid{grid-template-columns:1fr}}

.urn-card{
  position:relative;
  background:#fff;
  text-decoration:none;
  color:inherit;
  display:flex;
  flex-direction:column;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  transition:.3s;
  overflow:hidden;
  min-height:420px;
}
.urn-card:hover{
  background:var(--paper);
  z-index:2;
  box-shadow:0 18px 40px rgba(5,13,36,.12);
  transform:translateY(-4px);
}

.urn-card-img{
  position:relative;
  width:100%;
  height:240px;
  overflow:hidden;
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
}
.urn-card-img img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.6s ease;
}
.urn-card:hover .urn-card-img img{
  transform:scale(1.06);
}
.urn-card-placeholder{
  position:absolute;
  inset:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);
  font-size:80px;
  font-weight:500;
  color:rgba(201,168,107,.18);
}

.urn-card-num{
  position:absolute;
  top:14px;left:18px;
  font-family:var(--serif);
  font-size:12.5px;
  font-style:italic;
  font-weight:600;
  color:var(--gold);
  background:rgba(5,13,36,.65);
  padding:5px 12px;
  border:1px solid var(--gold);
  backdrop-filter:blur(6px);
  z-index:2;
}
.urn-card-badge{
  position:absolute;
  top:14px;right:14px;
  font-family:var(--sans);
  font-size:9.5px;font-weight:700;
  letter-spacing:1.2px;text-transform:uppercase;
  background:var(--red);
  color:#fff;
  padding:5px 10px;
  z-index:2;
}

.urn-card-body{
  padding:24px 24px 22px;
  display:flex;
  flex-direction:column;
  flex:1;
  position:relative;
}
.urn-card-body::before{
  content:'';
  position:absolute;
  top:0;left:24px;
  width:30px;height:3px;
  background:var(--red);
  transition:width .3s ease;
}
.urn-card:hover .urn-card-body::before{width:60px}

.urn-card-cat{
  font-family:var(--sans);
  font-size:10px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--gold);
  margin-bottom:8px;
}
.urn-card-name{
  font-family:var(--serif);
  font-size:22px;
  font-weight:600;
  letter-spacing:-.2px;
  line-height:1.2;
  color:var(--navy);
  margin:0 0 10px;
}
.urn-card-desc{
  font-family:var(--sans);
  font-size:13px;
  line-height:1.6;
  color:#5a5a5a;
  margin:0 0 18px;
  flex:1;
  display:-webkit-box;
  -webkit-line-clamp:3;
  -webkit-box-orient:vertical;
  overflow:hidden;
}
.urn-card-cta{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:1.8px;
  text-transform:uppercase;
  color:var(--red);
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding-top:14px;
  border-top:1px solid var(--line);
  transition:.2s;
}
.urn-card-cta span{transition:transform .2s}
.urn-card:hover .urn-card-cta{color:var(--navy)}
.urn-card:hover .urn-card-cta span{transform:translateX(6px)}

/* EMPTY STATE */
.urn-empty{
  text-align:center;
  padding:80px 30px;
  background:#fff;
  border:1px solid var(--line);
}
.urn-empty-icon{
  font-size:60px;
  margin-bottom:20px;
  opacity:.4;
}
.urn-empty h3{
  font-family:var(--serif);
  font-size:32px;font-weight:600;font-style:italic;
  color:var(--navy);
  margin:0 0 12px;
}
.urn-empty p{
  font-family:var(--sans);
  font-size:15px;line-height:1.6;
  color:#5a5a5a;
  margin:0 0 24px;
  max-width:480px;
  margin-left:auto;margin-right:auto;
}
.urn-empty-btn{
  display:inline-block;
  padding:14px 32px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  background:var(--navy);
  color:#fff;
  text-decoration:none;
  transition:.2s;
}
.urn-empty-btn:hover{
  background:var(--gold);
  color:var(--navy);
}

/* CTA */
.urn-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  padding:80px 0;
  position:relative;overflow:hidden;
  border-top:4px solid var(--gold);
}
.urn-cta::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(circle at 80% 50%, rgba(201,168,107,.1) 0%, transparent 60%);
  pointer-events:none;
}
.urn-cta-inner{
  position:relative;z-index:2;
  display:grid;
  grid-template-columns:1.4fr 1fr;
  gap:50px;
  align-items:center;
}
@media (max-width:900px){.urn-cta-inner{grid-template-columns:1fr;text-align:center}}
.urn-cta-eyebrow{
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:14px;
  display:inline-flex;align-items:center;gap:14px;
}
.urn-cta-eyebrow::before{
  content:'';width:30px;height:1px;background:var(--gold);
}
@media (max-width:900px){.urn-cta-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--gold);
}}
.urn-cta h2{
  font-family:var(--serif);
  font-size:clamp(32px, 4vw, 46px);
  font-weight:500;font-style:italic;
  letter-spacing:-.5px;line-height:1.15;
  margin:0 0 14px;color:#fff;
}
.urn-cta h2 strong{font-style:normal;color:var(--gold);font-weight:600}
.urn-cta-lead{
  font-family:var(--sans);
  font-size:14.5px;line-height:1.7;
  color:rgba(255,255,255,.75);
  margin:0;
}
.urn-cta-actions{
  display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end;
}
@media (max-width:900px){.urn-cta-actions{justify-content:center}}
.urn-cta-btn{
  display:inline-block;
  padding:16px 32px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;
  transition:.2s;border:1px solid transparent;
}
.urn-cta-btn-primary{
  background:var(--red);color:#fff;border-color:var(--red);
}
.urn-cta-btn-primary:hover{
  background:var(--red-dark);
  transform:translateY(-2px);
  box-shadow:0 10px 22px rgba(200,16,46,.4);
}
.urn-cta-btn-ghost{
  background:transparent;color:var(--gold);border-color:var(--gold);
}
.urn-cta-btn-ghost:hover{
  background:var(--gold);color:var(--navy);
  transform:translateY(-2px);
}

@media (max-width:768px){
  .urn-catnav-section{position:relative;top:0}
  .urn-catnav-item{padding:14px 16px;font-size:11px}
  .urn-card{min-height:380px}
  .urn-card-img{height:200px}
  .urn-card-name{font-size:20px}
}
</style>

<div class="urn-page">

  <!-- HERO -->
  <section class="urn-hero">
    <div class="container">
      <div class="urn-hero-eyebrow">Ürün Katalogumuz</div>
      <?php if ($cat_slug && $activeCatName): ?>
        <h1><em><?= h($activeCatName) ?></em><br><?= h(t('products.products_label', 'Ürünleri')) ?></h1>
        <p class="urn-hero-lead">
          <?= h($activeCatName) ?> kategorisindeki tüm ürünlerimiz, kalite ve fiyat avantajıyla stoğumuzda hazır.
        </p>
      <?php elseif ($search): ?>
        <h1><?= h(t('products.search', 'Arama')) ?>: <em>"<?= h($search) ?>"</em></h1>
        <p class="urn-hero-lead">
          Arama sonuçlarınız aşağıdadır.
        </p>
      <?php else: ?>
        <h1><?= t('products.hero_h1', 'Çeliğin <em>Sonsuz Yelpazesi</em><br>Tek Adreste') ?></h1>
        <p class="urn-hero-lead">
          Sac, boru, profil, hadde, flanş, demir ve panel ürünlerimizin tüm yelpazesi. Türkiye'nin lider üreticilerinden tedarik ettiğimiz, sertifikalı ve stok hazır ürün katalogumuz.
        </p>
      <?php endif; ?>

      <div class="urn-hero-stats">
        <div class="urn-hero-stat"><strong><?= $totalCount ?></strong><span>Aktif Ürün</span></div>
        <div class="urn-hero-stat"><strong><?= $catCount ?></strong><span>Ana Kategori</span></div>
        <div class="urn-hero-stat"><strong>%100</strong><span>Sertifikalı</span></div>
        <div class="urn-hero-stat"><strong>81 İl</strong><span>Sevkiyat</span></div>
      </div>
    </div>
  </section>

  <!-- BREADCRUMB -->
  <section class="urn-breadcrumb-strip">
    <div class="container">
      <nav class="urn-breadcrumb">
        <a href="<?= h(url('')) ?>">Anasayfa</a>
        <span class="sep">›</span>
        <?php if ($cat_slug || $search): ?>
          <a href="<?= h(url('urunler.php')) ?>">Ürünler</a>
          <span class="sep">›</span>
        <?php endif; ?>
        <span class="current">
          <?php if ($cat_slug && $activeCatName): ?>
            <?= h($activeCatName) ?>
          <?php elseif ($search): ?>
            "<?= h($search) ?>"
          <?php else: ?>
            Tüm Ürünler
          <?php endif; ?>
        </span>
      </nav>
    </div>
  </section>

  <!-- CATEGORY NAV -->
  <nav class="urn-catnav-section">
    <div class="urn-catnav">
      <a href="<?= h(url('urunler.php')) ?>" class="urn-catnav-item <?= !$cat_slug && !$search ? 'active' : '' ?>">
        ★ Tüm Ürünler
      </a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= h(url('urunler.php?kategori=' . $c['slug'])) ?>"
           class="urn-catnav-item <?= $cat_slug === $c['slug'] ? 'active' : '' ?>">
          <?= h($c['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <!-- SEARCH STRIP -->
  <section class="urn-search-strip">
    <div class="container">
      <form method="get" class="urn-search-form">
        <input type="search" name="q" class="urn-search-input"
               placeholder="🔍 Ürün adı veya açıklamasına göre ara..."
               value="<?= h($search) ?>">
        <?php if ($cat_slug): ?>
          <input type="hidden" name="kategori" value="<?= h($cat_slug) ?>">
        <?php endif; ?>
        <button type="submit" class="urn-search-btn">Ara</button>
        <?php if ($cat_slug || $search): ?>
          <a href="<?= h(url('urunler.php')) ?>" class="urn-search-clear">✕ Temizle</a>
        <?php endif; ?>
      </form>
    </div>
  </section>

  <!-- MAIN -->
  <section class="urn-main">
    <div class="container">

      <?php if ($products): ?>
      <!-- RESULTS BAR -->
      <div class="urn-results-bar">
        <div class="urn-results-text">
          <strong><?= $totalCount ?></strong> ürün listeleniyor
        </div>
        <?php if ($cat_slug && $activeCatName): ?>
          <div class="urn-results-cat-info"><?= h($activeCatName) ?> kategorisinde</div>
        <?php elseif ($search): ?>
          <div class="urn-results-cat-info">"<?= h($search) ?>" araması için</div>
        <?php else: ?>
          <div class="urn-results-cat-info">Tüm Kategoriler · Stok Hazır</div>
        <?php endif; ?>
      </div>

      <!-- PRODUCT GRID -->
      <div class="urn-grid">
        <?php foreach ($products as $i => $p): ?>
        <a class="urn-card" href="<?= h(url('urun.php?slug=' . urlencode($p['slug']))) ?>">
          <div class="urn-card-img">
            <span class="urn-card-num"><?= str_pad($i+1, 3, '0', STR_PAD_LEFT) ?></span>
            <?php if (!empty($p['is_featured'])): ?>
              <span class="urn-card-badge">★ Öne Çıkan</span>
            <?php endif; ?>
            <?php if (!empty($p['image'])): ?>
              <img src="<?= h(img_url($p['image'])) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="urn-card-placeholder"><?= h(mb_strtoupper(mb_substr($p['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <?php endif; ?>
          </div>
          <div class="urn-card-body">
            <?php if (!empty($p['cat_name'])): ?>
              <div class="urn-card-cat"><?= h($p['cat_name']) ?></div>
            <?php endif; ?>
            <h3 class="urn-card-name"><?= h(tr_field($p, 'title') ?: $p['name']) ?></h3>
            <?php if (!empty($p['short_desc'])): ?>
              <p class="urn-card-desc"><?= h($p['short_desc']) ?></p>
            <?php else: ?>
              <p class="urn-card-desc">&nbsp;</p>
            <?php endif; ?>
            <span class="urn-card-cta">Detayları Gör <span>→</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php else: ?>
      <!-- EMPTY STATE -->
      <div class="urn-empty">
        <div class="urn-empty-icon">🔍</div>
        <h3><?= h(t('products.not_found', 'Aradığınız ürün bulunamadı')) ?></h3>
        <p>Bu kriterlere uygun ürün bulunamadı. Farklı bir kategori seçebilir veya tüm ürünleri görüntüleyebilirsiniz. Stoğumuzda olmayan ürünler için bizimle iletişime geçin — üretici partnerlerimizden 24-72 saat içinde tedarik edebiliriz.</p>
        <a href="<?= h(url('urunler.php')) ?>" class="urn-empty-btn">Tüm Ürünleri Göster</a>
      </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- CTA -->
  <section class="urn-cta">
    <div class="container">
      <div class="urn-cta-inner">
        <div>
          <div class="urn-cta-eyebrow">Aradığınızı bulamadınız mı?</div>
          <h2><?= t('products.cta_h2', 'Stoğumuzda <strong>olmayan ürün</strong> için<br>tedarik desteği') ?></h2>
          <p class="urn-cta-lead">Tedarik ortağımız üretici fabrikalardan, sipariş üzerine 24-72 saat içinde özel ürün temin edebiliyoruz. Aradığınızı söyleyin, biz bulalım.</p>
        </div>
        <div class="urn-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="urn-cta-btn urn-cta-btn-primary">Teklif İste</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ürün aramak için size ulaştım.')) ?>" target="_blank" rel="noopener" class="urn-cta-btn urn-cta-btn-ghost">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
