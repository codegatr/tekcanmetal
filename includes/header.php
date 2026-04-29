<?php
require_once __DIR__ . '/db.php';

$current = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? settings('site_short_name', 'Tekcan Metal');
$metaDesc  = $metaDesc  ?? settings('site_description', '');
$canonical = url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));

// Ürün kategorileri (mega menu için) — sade B2B liste
try {
    $navCategories = all("
        SELECT slug, name, short_desc
        FROM tm_categories
        WHERE is_active=1 AND parent_id IS NULL
        ORDER BY sort_order
        LIMIT 12
    ");
    $navServices   = all("SELECT slug,title,icon FROM tm_services WHERE is_active=1 ORDER BY sort_order LIMIT 8");
} catch (Throwable $e) {
    $navCategories = []; $navServices = [];
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($pageTitle) ?> — <?= h(settings('site_short_name')) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<meta name="keywords" content="<?= h(settings('site_keywords')) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">

<meta property="og:title" content="<?= h($pageTitle) ?> — <?= h(settings('site_short_name')) ?>">
<meta property="og:description" content="<?= h($metaDesc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:locale" content="tr_TR">

<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= h(url('assets/css/style.css')) ?>?v=<?= h(TM_VERSION) ?>">

<?php if ($code = settings('analytics_code')): ?>
<?= $code ?>
<?php endif; ?>
</head>
<body class="page-<?= h($current) ?> <?= $current === 'index' ? 'home-page' : 'inner-page' ?>">

<!-- TOP BAR (sade, sadece iletişim) -->
<div class="topbar">
  <div class="container">
    <div class="inner">
      <div class="meta">
        <span><i>📍</i> <?= h(settings('site_address')) ?></span>
      </div>
      <div class="meta">
        <span><i>⏰</i> <?= h(settings('working_hours')) ?></span>
        <span><i>📞</i> <a href="<?= h(phone_link(settings('site_phone'))) ?>"><?= h(settings('site_phone')) ?></a></span>
        <span><i>✉</i> <a href="mailto:<?= h(settings('site_email')) ?>"><?= h(settings('site_email')) ?></a></span>
      </div>
    </div>
  </div>
</div>

<!-- HEADER — Limak tarzı: center logo, sol-sağ nav -->
<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="header-inner">

      <!-- Sol: Anasayfa ikonu -->
      <a href="<?= h(url('/')) ?>" class="header-home" aria-label="Anasayfa">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
      </a>

      <!-- Sol Nav -->
      <ul class="main-nav main-nav-left">
        <li class="has-mega <?= in_array($current,['urunler','urun-detay','kategori'])?'active':'' ?>">
          <a href="<?= h(url('urunler.php')) ?>">Ürün Gruplarımız</a>

          <!-- MEGA PANEL — Endüstriyel sade B2B liste -->
          <div class="mega-panel mega-panel-list">
            <div class="container">
              <div class="mega-list-grid">
                <?php foreach ($navCategories as $c): ?>
                  <a href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>" class="mega-list-item">
                    <div class="mega-list-row">
                      <span class="mega-list-name"><?= h($c['name']) ?></span>
                      <span class="mega-list-arrow">›</span>
                    </div>
                    <?php if (!empty($c['short_desc'])): ?>
                      <span class="mega-list-desc"><?= h($c['short_desc']) ?></span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
              <div class="mega-list-foot">
                <a href="<?= h(url('urunler.php')) ?>" class="mega-list-allbtn">
                  Tüm ürün kataloğunu görüntüle <span>→</span>
                </a>
              </div>
            </div>
          </div>
        </li>
        <li class="has-sub <?= in_array($current,['hizmetler','hizmet'])?'active':'' ?>">
          <a href="<?= h(url('hizmetler.php')) ?>">Hizmetlerimiz</a>
          <ul class="submenu">
            <?php foreach ($navServices as $s): ?>
              <li><a href="<?= h(url('hizmet.php?slug=' . $s['slug'])) ?>"><?= h($s['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="<?= $current==='hesaplama'?'active':'' ?>">
          <a href="<?= h(url('hesaplama.php')) ?>">Ağırlık Hesaplama</a>
        </li>
      </ul>

      <!-- Merkez Logo -->
      <a href="<?= h(url('/')) ?>" class="header-logo" aria-label="<?= h(settings('site_short_name')) ?> Anasayfa">
        <?php $logoFile = settings('logo', 'assets/img/logo.png'); ?>
        <?php if ($logoFile && file_exists(__DIR__ . '/../' . $logoFile)): ?>
          <img src="<?= h(url($logoFile)) ?>" alt="Tekcan Metal" class="logo-img">
        <?php else: ?>
          <span class="logo-mark">T</span>
          <span class="logo-text">
            <span class="logo-name">Tekcan Metal</span>
            <span class="logo-sub">Demir adına Herşey</span>
          </span>
        <?php endif; ?>
      </a>

      <!-- Sağ Nav -->
      <ul class="main-nav main-nav-right">
        <li class="has-sub <?= in_array($current,['hakkimizda','ekibimiz','partnerler','iban','sss','mail-order','sadakat'])?'active':'' ?>">
          <a href="<?= h(url('hakkimizda.php')) ?>">Kurumsal</a>
          <ul class="submenu submenu-right">
            <li><a href="<?= h(url('hakkimizda.php')) ?>">Hakkımızda</a></li>
            <li><a href="<?= h(url('ekibimiz.php')) ?>">Ekibimiz</a></li>
            <li><a href="<?= h(url('partnerler.php')) ?>">Çözüm Ortakları</a></li>
            <li><a href="<?= h(url('iban.php')) ?>">IBAN Bilgilerimiz</a></li>
            <li><a href="<?= h(url('sss.php')) ?>">Sıkça Sorulan Sorular</a></li>
            <li><a href="<?= h(url('mail-order.php')) ?>">Mail Order Formu</a></li>
            <li><a href="<?= h(url('sadakat.php')) ?>">Sadakat Programı</a></li>
          </ul>
        </li>
        <li class="has-sub <?= in_array($current,['galeri','blog','blog-detay'])?'active':'' ?>">
          <a href="<?= h(url('blog.php')) ?>">Haberler &amp; Basın</a>
          <ul class="submenu submenu-right">
            <li><a href="<?= h(url('blog.php')) ?>">Tekcan'dan Haberler</a></li>
            <li><a href="<?= h(url('galeri.php')) ?>">Foto Galeri</a></li>
          </ul>
        </li>
        <li class="<?= $current==='iletisim'?'active':'' ?>">
          <a href="<?= h(url('iletisim.php')) ?>">İletişim</a>
        </li>
      </ul>

      <!-- Sağ ucu: Dil seçici (Limak tarzı) -->
      <div class="header-actions">
        <div class="lang-switch" tabindex="0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
          <span>TR</span>
        </div>
        <button class="mobile-toggle" id="mobileToggle" aria-label="Menü">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- MOBILE OFFCANVAS -->
<div class="offcanvas-backdrop" id="ocBackdrop"></div>
<aside class="offcanvas" id="offcanvas">
  <div class="offcanvas-head">
    <a href="<?= h(url('/')) ?>" class="logo">
      <?php $logoFile2 = settings('logo', 'assets/img/logo.png'); ?>
      <?php if ($logoFile2 && file_exists(__DIR__ . '/../' . $logoFile2)): ?>
        <img src="<?= h(url($logoFile2)) ?>" alt="Tekcan Metal" class="logo-img">
      <?php else: ?>
        <span class="logo-mark">T</span>
        <span class="logo-text">
          <span class="logo-name">Tekcan Metal</span>
          <span class="logo-sub">Menü</span>
        </span>
      <?php endif; ?>
    </a>
    <button class="offcanvas-close" id="ocClose">×</button>
  </div>
  <nav class="offcanvas-nav">
    <a href="<?= h(url('/')) ?>" class="parent <?= $current==='index'?'active':'' ?>">🏠 Anasayfa</a>
    <a href="<?= h(url('hakkimizda.php')) ?>" class="parent <?= $current==='hakkimizda'?'active':'' ?>">📌 Hakkımızda</a>
    <a href="<?= h(url('ekibimiz.php')) ?>" class="child">→ Ekibimiz</a>
    <a href="<?= h(url('partnerler.php')) ?>" class="child">→ Çözüm Ortakları</a>
    <a href="<?= h(url('iban.php')) ?>" class="child">→ IBAN Bilgilerimiz</a>
    <a href="<?= h(url('sss.php')) ?>" class="child">→ Sıkça Sorulan Sorular</a>
    <a href="<?= h(url('urunler.php')) ?>" class="parent <?= $current==='urunler'?'active':'' ?>">🏗️ Ürünler</a>
    <?php foreach ($navCategories as $c): ?>
      <a href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>" class="child">→ <?= h($c['name']) ?></a>
    <?php endforeach; ?>
    <a href="<?= h(url('hizmetler.php')) ?>" class="parent <?= $current==='hizmetler'?'active':'' ?>">⚙️ Hizmetlerimiz</a>
    <?php foreach ($navServices as $s): ?>
      <a href="<?= h(url('hizmet.php?slug=' . $s['slug'])) ?>" class="child">→ <?= h($s['title']) ?></a>
    <?php endforeach; ?>
    <a href="<?= h(url('hesaplama.php')) ?>" class="parent <?= $current==='hesaplama'?'active':'' ?>">📐 Ağırlık Hesaplama</a>
    <a href="<?= h(url('galeri.php')) ?>" class="parent <?= $current==='galeri'?'active':'' ?>">📷 Foto Galeri</a>
    <a href="<?= h(url('blog.php')) ?>" class="parent <?= $current==='blog'?'active':'' ?>">📰 Blog</a>
    <a href="<?= h(url('iletisim.php')) ?>" class="parent <?= $current==='iletisim'?'active':'' ?>">📞 İletişim</a>
    <a href="<?= h(url('mail-order.php')) ?>" class="parent">💳 Mail Order Formu</a>
  </nav>
</aside>

<main>
<?php
// Flash mesajları
foreach (flash_get() as $f):
?>
<div class="container" style="margin-top:20px">
  <div class="alert alert-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
</div>
<?php endforeach; ?>
