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
<html lang="<?= h(current_lang()) ?>"<?= is_rtl() ? ' dir="rtl"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($pageTitle) ?> — <?= h(settings('site_short_name')) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<meta name="keywords" content="<?= h(settings('site_keywords')) ?>">
<link rel="canonical" href="<?= h($canonical) ?>">

<!-- hreflang (v1.0.56 — i18n) -->
<?= hreflang_tags() ?>

<?php
// SEO Meta v1.0.67: Open Graph + Twitter Card zenginleştirme
$ogSiteUrl = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');

// Sayfa-spesifik OG image (ürün/blog/kategoride özel image varsa o, yoksa logo)
$ogImageDefault = is_file(__DIR__ . '/../assets/img/og-default.jpg')
    ? 'assets/img/og-default.jpg'
    : settings('logo', 'assets/img/logo.png');
$ogImage = $ogSiteUrl . '/' . ltrim(settings('og_image', $ogImageDefault), '/');
if (!empty($post['cover_image'])) {
    $ogImage = $ogSiteUrl . '/' . ltrim($post['cover_image'], '/');
} elseif (!empty($p['image'])) {
    $ogImage = $ogSiteUrl . '/' . ltrim($p['image'], '/');
} elseif (!empty($cat['image'])) {
    $ogImage = $ogSiteUrl . '/' . ltrim($cat['image'], '/');
} elseif (!empty($s['image'])) {
    $ogImage = $ogSiteUrl . '/' . ltrim($s['image'], '/');
}

// Sayfa tipini OG'ye yansıt (article için blog, product için ürün)
$ogType = 'website';
if ($current === 'blog-detay') $ogType = 'article';
elseif ($current === 'urun') $ogType = 'product';
?>
<meta property="og:title" content="<?= h($pageTitle) ?> — <?= h(settings('site_short_name')) ?>">
<meta property="og:description" content="<?= h($metaDesc) ?>">
<meta property="og:type" content="<?= h($ogType) ?>">
<meta property="og:url" content="<?= h($canonical) ?>">
<meta property="og:locale" content="<?= h(lang_locale()) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= h($pageTitle) ?>">
<meta property="og:site_name" content="<?= h(settings('site_short_name')) ?>">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($pageTitle) ?>">
<meta name="twitter:description" content="<?= h($metaDesc) ?>">
<meta name="twitter:image" content="<?= h($ogImage) ?>">
<?php $twitterHandle = settings('site_twitter_handle'); if ($twitterHandle): ?>
<meta name="twitter:site" content="<?= h($twitterHandle) ?>">
<?php endif; ?>

<!-- Article-spesifik (blog detay) -->
<?php if ($current === 'blog-detay' && !empty($post)): ?>
<meta property="article:published_time" content="<?= h(date('c', strtotime($post['published_at'] ?: 'now'))) ?>">
<meta property="article:modified_time" content="<?= h(date('c', strtotime($post['updated_at'] ?? $post['published_at'] ?? 'now'))) ?>">
<meta property="article:author" content="<?= h($post['author'] ?? 'Tekcan Metal') ?>">
<?php endif; ?>

<!-- Robots -->
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large">

<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= h(url('assets/css/style.css')) ?>?v=<?= h(TM_VERSION) ?>">

<?php if ($code = settings('analytics_code')): ?>
<?= $code ?>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════
     SCHEMA.ORG STRUCTURED DATA (v1.0.39)
     ═══════════════════════════════════════════════ -->
<?php
$schemaSiteUrl   = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
$schemaSiteName  = settings('site_short_name', 'Tekcan Metal');
$schemaSiteLogo  = $schemaSiteUrl . '/' . settings('logo', 'assets/img/logo.png');
$schemaPhone     = settings('site_phone', '+90 332 342 24 52');
$schemaEmail     = settings('site_email', 'info@tekcanmetal.com');
$schemaAddress   = settings('site_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS');
$schemaCity      = settings('site_city', 'Konya');
$schemaDistrict  = settings('site_district', 'Karatay');

// Sosyal medya
$schemaSocial = array_filter([
    settings('site_instagram'),
    settings('site_facebook'),
    settings('site_linkedin'),
    settings('site_youtube'),
    settings('site_twitter'),
]);
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "@id": "<?= h($schemaSiteUrl) ?>/#organization",
  "name": "<?= h($schemaSiteName) ?>",
  "alternateName": "Tekcan Metal Sanayi ve Ticaret Ltd. Şti.",
  "url": "<?= h($schemaSiteUrl) ?>",
  "logo": "<?= h($schemaSiteLogo) ?>",
  "description": "<?= h(settings('site_description', 'Konya merkezli demir-çelik tedarikçisi. Sac, boru, profil, hadde ve özel çelik ürünleri.')) ?>",
  "foundingDate": "2005",
  "telephone": "<?= h($schemaPhone) ?>",
  "email": "<?= h($schemaEmail) ?>",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "<?= h($schemaAddress) ?>",
    "addressLocality": "<?= h($schemaDistrict) ?>",
    "addressRegion": "<?= h($schemaCity) ?>",
    "addressCountry": "TR"
  }<?php if ($schemaSocial): ?>,
  "sameAs": <?= json_encode(array_values($schemaSocial), JSON_UNESCAPED_UNICODE) ?>
  <?php endif; ?>
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "@id": "<?= h($schemaSiteUrl) ?>/#localbusiness",
  "name": "<?= h($schemaSiteName) ?>",
  "image": "<?= h($schemaSiteLogo) ?>",
  "url": "<?= h($schemaSiteUrl) ?>",
  "telephone": "<?= h($schemaPhone) ?>",
  "email": "<?= h($schemaEmail) ?>",
  "priceRange": "₺₺",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "<?= h($schemaAddress) ?>",
    "addressLocality": "<?= h($schemaDistrict) ?>",
    "addressRegion": "<?= h($schemaCity) ?>",
    "postalCode": "42050",
    "addressCountry": "TR"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": 37.929244,
    "longitude": 32.558043
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "08:00",
      "closes": "18:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "08:00",
      "closes": "13:00"
    }
  ],
  "areaServed": [
    {"@type": "Country", "name": "Türkiye"},
    {"@type": "AdministrativeArea", "name": "Konya"},
    {"@type": "AdministrativeArea", "name": "İstanbul"},
    {"@type": "AdministrativeArea", "name": "Ankara"},
    {"@type": "AdministrativeArea", "name": "İzmir"},
    {"@type": "AdministrativeArea", "name": "Bursa"},
    {"@type": "AdministrativeArea", "name": "Gaziantep"},
    {"@type": "AdministrativeArea", "name": "Kayseri"},
    {"@type": "Country", "name": "Irak"},
    {"@type": "Country", "name": "Suriye"},
    {"@type": "Country", "name": "Azerbaycan"},
    {"@type": "Country", "name": "Türkmenistan"}
  ],
  "knowsAbout": [
    "Demir Çelik Tedarik", "Sac Levha", "DKP Sac", "HRP Sac", "ST-52 Sac",
    "Galvanizli Sac", "Kutu Profil", "Yuvarlak Boru", "Köşebent",
    "HEA HEB Profil", "IPE Profil", "Nervürlü İnşaat Demiri", "Çelik Hasır",
    "Lazer Kesim", "Oksijen Kesim", "Dekoratif Sac"
  ]
}
</script>

<?php
// SAYFA TİPİNE GÖRE ÖZEL SCHEMA
$pageBaseName = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');

// Breadcrumb (varsa)
if (!empty($schemaBreadcrumb)) :
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": <?= json_encode($schemaBreadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
}
</script>
<?php endif; ?>

<?php
// PRODUCT schema (urun.php sayfasında)
if ($pageBaseName === 'urun' && !empty($p) && is_array($p)) :
    $prodSpecs = !empty($p['specs']) ? json_decode($p['specs'], true) : [];
    $prodImage = !empty($p['image']) ? $schemaSiteUrl . '/' . ltrim($p['image'], '/') : $schemaSiteLogo;
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "<?= h($p['name']) ?>",
  "description": "<?= h($p['short_desc'] ?: strip_tags(substr($p['description'] ?? '', 0, 200))) ?>",
  "image": "<?= h($prodImage) ?>",
  "category": "<?= h($p['cat_name'] ?? 'Demir Çelik') ?>",
  "brand": {
    "@type": "Brand",
    "name": "Tekcan Metal"
  },
  "manufacturer": {
    "@type": "Organization",
    "name": "Tekcan Metal Tedarik Ortakları"
  },
  "offers": {
    "@type": "Offer",
    "url": "<?= h($canonical) ?>",
    "priceCurrency": "TRY",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@type": "Organization",
      "name": "Tekcan Metal"
    }
  }
}
</script>
<?php endif; ?>

<?php
// FAQ schema (sss.php sayfasında)
if ($pageBaseName === 'sss' && !empty($faqs) && is_array($faqs)) :
    $faqJson = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
    foreach ($faqs as $f) {
        $faqJson['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $f['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => strip_tags($f['answer']),
            ],
        ];
    }
?>
<script type="application/ld+json">
<?= json_encode($faqJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
<?php endif; ?>

<?php
// BlogPosting schema artık blog-detay.php tarafından yönetiliyor (v1.0.66+)
// Burada handle edilmemeli — duplicate'ı önler
?>

<!-- WebSite Schema with SearchAction (sitelinks search box için) -->
<?php if ($current === 'index'): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "@id": "<?= h($schemaSiteUrl) ?>/#website",
  "url": "<?= h($schemaSiteUrl) ?>/",
  "name": "<?= h($schemaSiteName) ?>",
  "description": "<?= h(settings('site_description', '')) ?>",
  "publisher": {"@id": "<?= h($schemaSiteUrl) ?>/#organization"},
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "<?= h($schemaSiteUrl) ?>/urunler.php?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  },
  "inLanguage": ["tr", "en", "ar", "ru"]
}
</script>
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
          <a href="<?= h(url_lang('urunler.php')) ?>"><?= h(t('header.menu.products', 'Ürün Gruplarımız')) ?></a>

          <!-- MEGA PANEL — Endüstriyel sade B2B liste -->
          <div class="mega-panel mega-panel-list">
            <div class="container">
              <div class="mega-list-grid">
                <?php foreach ($navCategories as $c): ?>
                  <a href="<?= h(url_lang('kategori.php?slug=' . $c['slug'])) ?>" class="mega-list-item">
                    <div class="mega-list-row">
                      <span class="mega-list-name"><?= h(tr_field($c, 'name')) ?></span>
                      <span class="mega-list-arrow">›</span>
                    </div>
                    <?php if (!empty($c['short_desc'])): ?>
                      <span class="mega-list-desc"><?= h(tr_field($c, 'short_desc')) ?></span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
              <div class="mega-list-foot">
                <a href="<?= h(url_lang('urunler.php')) ?>" class="mega-list-allbtn">
                  <?= h(t('label.show_all_products', 'Tüm ürün kataloğunu görüntüle')) ?> <span>→</span>
                </a>
              </div>
            </div>
          </div>
        </li>
        <li class="has-sub <?= in_array($current,['hizmetler','hizmet'])?'active':'' ?>">
          <a href="<?= h(url_lang('hizmetler.php')) ?>"><?= h(t('header.menu.services', 'Hizmetlerimiz')) ?></a>
          <ul class="submenu">
            <?php foreach ($navServices as $s): ?>
              <li><a href="<?= h(url_lang('hizmet.php?slug=' . $s['slug'])) ?>"><?= h(tr_field($s, 'title')) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="<?= $current==='hesaplama'?'active':'' ?>">
          <a href="<?= h(url_lang('hesaplama.php')) ?>"><?= h(t('header.menu.calculator', 'Ağırlık Hesaplama')) ?></a>
        </li>
      </ul>

      <!-- Merkez Logo -->
      <a href="<?= h(url_lang('/')) ?>" class="header-logo" aria-label="<?= h(settings('site_short_name')) ?> <?= h(t('header.menu.home', 'Anasayfa')) ?>">
        <?php $logoFile = settings('logo', 'assets/img/logo.png'); ?>
        <?php if ($logoFile && file_exists(__DIR__ . '/../' . $logoFile)): ?>
          <img src="<?= h(url($logoFile)) ?>" alt="Tekcan Metal" class="logo-img">
        <?php else: ?>
          <span class="logo-mark">T</span>
          <span class="logo-text">
            <span class="logo-name">Tekcan Metal</span>
            <span class="logo-sub"><?= h(t('header.tagline', 'Demir adına Herşey')) ?></span>
          </span>
        <?php endif; ?>
      </a>

      <!-- Sağ Nav -->
      <ul class="main-nav main-nav-right">
        <li class="has-sub <?= in_array($current,['hakkimizda','partnerler','iban','sss','mail-order','sadakat'])?'active':'' ?>">
          <a href="<?= h(url_lang('hakkimizda.php')) ?>"><?= h(t('header.menu.corporate', 'Kurumsal')) ?></a>
          <ul class="submenu submenu-right">
            <li><a href="<?= h(url_lang('hakkimizda.php')) ?>"><?= h(t('header.menu.about', 'Hakkımızda')) ?></a></li>
            <li><a href="<?= h(url_lang('partnerler.php')) ?>"><?= h(t('header.menu.partners', 'Çözüm Ortakları')) ?></a></li>
            <li><a href="<?= h(url_lang('iban.php')) ?>"><?= h(t('header.menu.iban', 'IBAN Bilgilerimiz')) ?></a></li>
            <li><a href="<?= h(url_lang('sss.php')) ?>"><?= h(t('header.menu.faq', 'Sıkça Sorulan Sorular')) ?></a></li>
            <li><a href="<?= h(url_lang('mail-order.php')) ?>"><?= h(t('header.menu.mail_order', 'Mail Order Formu')) ?></a></li>
            <li><a href="<?= h(url_lang('sadakat.php')) ?>"><?= h(t('header.menu.loyalty', 'Sadakat Programı')) ?></a></li>
          </ul>
        </li>
        <li class="has-sub <?= in_array($current,['galeri','blog','blog-detay'])?'active':'' ?>">
          <a href="<?= h(url_lang('blog.php')) ?>"><?= h(t('header.menu.news', 'Haberler & Basın')) ?></a>
          <ul class="submenu submenu-right">
            <li><a href="<?= h(url_lang('blog.php')) ?>"><?= h(t('header.menu.blog', "Tekcan'dan Haberler")) ?></a></li>
            <li><a href="<?= h(url_lang('galeri.php')) ?>"><?= h(t('header.menu.gallery', 'Foto Galeri')) ?></a></li>
          </ul>
        </li>
        <li class="<?= $current==='iletisim'?'active':'' ?>">
          <a href="<?= h(url_lang('iletisim.php')) ?>"><?= h(t('header.menu.contact', 'İletişim')) ?></a>
        </li>
      </ul>

      <!-- Sağ ucu: Dil seçici (4 dil dropdown) -->
      <div class="header-actions">
        <div class="lang-switch" tabindex="0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="12" cy="12" r="10"/>
            <line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
          </svg>
          <span><?= h(strtoupper(current_lang())) ?></span>
          <svg class="lang-caret" width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 5l3 3 3-3"/>
          </svg>

          <ul class="lang-menu">
            <?php foreach (I18N_LANGUAGES as $code => $info): ?>
              <li class="<?= $code === current_lang() ? 'active' : '' ?>">
                <a href="<?= h(lang_switch_url($code)) ?>" hreflang="<?= h($code) ?>" lang="<?= h($code) ?>">
                  <span class="lang-flag"><?= $info['flag'] ?></span>
                  <span class="lang-code"><?= h(strtoupper($code)) ?></span>
                  <span class="lang-name"><?= h($info['native']) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
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
          <span class="logo-sub"><?= h(t('header.menu_label', 'Menü')) ?></span>
        </span>
      <?php endif; ?>
    </a>
    <button class="offcanvas-close" id="ocClose">×</button>
  </div>
  <nav class="offcanvas-nav">
    <a href="<?= h(url_lang('/')) ?>" class="parent <?= $current==='index'?'active':'' ?>">🏠 <?= h(t('header.menu.home', 'Anasayfa')) ?></a>
    <a href="<?= h(url_lang('hakkimizda.php')) ?>" class="parent <?= $current==='hakkimizda'?'active':'' ?>">📌 <?= h(t('header.menu.about', 'Hakkımızda')) ?></a>
    <a href="<?= h(url_lang('partnerler.php')) ?>" class="child">→ <?= h(t('header.menu.partners', 'Çözüm Ortakları')) ?></a>
    <a href="<?= h(url_lang('iban.php')) ?>" class="child">→ <?= h(t('header.menu.iban', 'IBAN Bilgilerimiz')) ?></a>
    <a href="<?= h(url_lang('sss.php')) ?>" class="child">→ <?= h(t('header.menu.faq', 'Sıkça Sorulan Sorular')) ?></a>
    <a href="<?= h(url_lang('urunler.php')) ?>" class="parent <?= $current==='urunler'?'active':'' ?>">🏗️ <?= h(t('label.products', 'Ürünler')) ?></a>
    <?php foreach ($navCategories as $c): ?>
      <a href="<?= h(url_lang('kategori.php?slug=' . $c['slug'])) ?>" class="child">→ <?= h(tr_field($c, 'name')) ?></a>
    <?php endforeach; ?>
    <a href="<?= h(url_lang('hizmetler.php')) ?>" class="parent <?= $current==='hizmetler'?'active':'' ?>">⚙️ <?= h(t('header.menu.services', 'Hizmetlerimiz')) ?></a>
    <?php foreach ($navServices as $s): ?>
      <a href="<?= h(url_lang('hizmet.php?slug=' . $s['slug'])) ?>" class="child">→ <?= h(tr_field($s, 'title')) ?></a>
    <?php endforeach; ?>
    <a href="<?= h(url_lang('hesaplama.php')) ?>" class="parent <?= $current==='hesaplama'?'active':'' ?>">📐 <?= h(t('header.menu.calculator', 'Ağırlık Hesaplama')) ?></a>
    <a href="<?= h(url_lang('galeri.php')) ?>" class="parent <?= $current==='galeri'?'active':'' ?>">📷 <?= h(t('header.menu.gallery', 'Foto Galeri')) ?></a>
    <a href="<?= h(url_lang('blog.php')) ?>" class="parent <?= $current==='blog'?'active':'' ?>">📰 <?= h(t('header.menu.blog', "Tekcan'dan Haberler")) ?></a>
    <a href="<?= h(url_lang('iletisim.php')) ?>" class="parent <?= $current==='iletisim'?'active':'' ?>">📞 <?= h(t('header.menu.contact', 'İletişim')) ?></a>
    <a href="<?= h(url_lang('mail-order.php')) ?>" class="parent">💳 <?= h(t('header.menu.mail_order', 'Mail Order Formu')) ?></a>
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
