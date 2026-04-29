<?php
require_once __DIR__ . '/db.php';

$current = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? settings('site_short_name', 'Tekcan Metal');
$metaDesc  = $metaDesc  ?? settings('site_description', '');
$canonical = url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));

// Ürün kategorileri (mega menu için)
try {
    $navCategories = all("SELECT slug,name,icon FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order LIMIT 12");
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
<body class="page-<?= h($current) ?>">

<!-- TOP BAR -->
<div class="topbar">
  <div class="container">
    <div class="inner">
      <div class="meta">
        <span><i>📍</i> <?= h(settings('site_address')) ?>, <?= h(settings('site_district')) ?>/<?= h(settings('site_city')) ?></span>
        <span><i>⏰</i> <?= h(settings('working_hours')) ?></span>
      </div>
      <div class="meta">
        <span><i>📞</i> <a href="<?= h(phone_link(settings('site_phone'))) ?>"><?= h(settings('site_phone')) ?></a></span>
        <span><i>✉</i> <a href="mailto:<?= h(settings('site_email')) ?>"><?= h(settings('site_email')) ?></a></span>
      </div>
    </div>
  </div>
</div>

<!-- HEADER -->
<header class="site-header" id="siteHeader">
  <div class="container">
    <div class="header-inner">
      <a href="<?= h(url('/')) ?>" class="logo" aria-label="<?= h(settings('site_short_name')) ?> Anasayfa">
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

      <ul class="main-nav">
        <li class="<?= $current==='index'?'active':'' ?>">
          <a href="<?= h(url('/')) ?>">Anasayfa</a>
        </li>
        <li class="has-sub <?= in_array($current,['hakkimizda','ekibimiz','partnerler','iban','sss'])?'active':'' ?>">
          <a href="<?= h(url('hakkimizda.php')) ?>">Kurumsal</a>
          <ul class="submenu">
            <li><a href="<?= h(url('hakkimizda.php')) ?>"><i>📌</i> Hakkımızda</a></li>
            <li><a href="<?= h(url('ekibimiz.php')) ?>"><i>👥</i> Ekibimiz</a></li>
            <li><a href="<?= h(url('partnerler.php')) ?>"><i>🤝</i> Çözüm Ortakları</a></li>
            <li><a href="<?= h(url('iban.php')) ?>"><i>🏦</i> IBAN Bilgilerimiz</a></li>
            <li><a href="<?= h(url('sss.php')) ?>"><i>❓</i> Sıkça Sorulan Sorular</a></li>
            <li><a href="<?= h(url('mail-order.php')) ?>"><i>💳</i> Mail Order Formu</a></li>
            <li><a href="<?= h(url('sadakat.php')) ?>"><i>⭐</i> Müşteri Sadakat Programı</a></li>
          </ul>
        </li>
        <li class="has-sub <?= in_array($current,['urunler','urun-detay','kategori'])?'active':'' ?>">
          <a href="<?= h(url('urunler.php')) ?>">Ürünler</a>
          <ul class="submenu">
            <?php foreach ($navCategories as $c): ?>
              <li><a href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>"><i>▸</i> <?= h($c['name']) ?></a></li>
            <?php endforeach; ?>
            <li><a href="<?= h(url('urunler.php')) ?>" style="border-top:1px solid var(--border);margin-top:6px;padding-top:12px;font-weight:600;color:var(--accent-dark)"><i>→</i> Tüm Ürünler</a></li>
          </ul>
        </li>
        <li class="has-sub <?= in_array($current,['hizmetler','hizmet'])?'active':'' ?>">
          <a href="<?= h(url('hizmetler.php')) ?>">Hizmetlerimiz</a>
          <ul class="submenu">
            <?php foreach ($navServices as $s): ?>
              <li><a href="<?= h(url('hizmet.php?slug=' . $s['slug'])) ?>"><i>⚙</i> <?= h($s['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="<?= $current==='hesaplama'?'active':'' ?>">
          <a href="<?= h(url('hesaplama.php')) ?>">Ağırlık Hesaplama</a>
        </li>
        <li class="<?= in_array($current,['galeri','blog','blog-detay'])?'active':'' ?> has-sub">
          <a href="<?= h(url('galeri.php')) ?>">Galeri</a>
          <ul class="submenu">
            <li><a href="<?= h(url('galeri.php')) ?>"><i>📷</i> Foto Galeri</a></li>
            <li><a href="<?= h(url('blog.php')) ?>"><i>📰</i> Blog</a></li>
          </ul>
        </li>
        <li class="<?= $current==='iletisim'?'active':'' ?>">
          <a href="<?= h(url('iletisim.php')) ?>">İletişim</a>
        </li>
      </ul>

      <div class="header-actions">
        <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, Tekcan Metal\'den ürün/teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="btn-cta btn-cta-red">
          <span>💬</span> Hemen Teklif Al
        </a>
        <button class="mobile-toggle" id="mobileToggle" aria-label="Menü">☰</button>
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
