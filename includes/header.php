<?php
require_once __DIR__ . '/db.php';

$current = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? settings('site_short_name', 'Tekcan Metal');
$metaDesc  = $metaDesc  ?? settings('site_description', '');
$canonical = url(ltrim($_SERVER['REQUEST_URI'] ?? '/', '/'));

// Ürün kategorileri (mega menu için) — image, açıklama ve ürün sayısı dahil
try {
    $navCategories = all("
        SELECT c.slug, c.name, c.icon, c.image, c.short_desc,
               (SELECT COUNT(*) FROM tm_products p WHERE p.category_id=c.id AND p.is_active=1) AS product_count
        FROM tm_categories c
        WHERE c.is_active=1 AND c.parent_id IS NULL
        ORDER BY c.sort_order
        LIMIT 12
    ");
    $navServices   = all("SELECT slug,title,icon,image FROM tm_services WHERE is_active=1 ORDER BY sort_order LIMIT 8");

    // Her kategori için ilk 4 ürün (mega panel preview için)
    $navCategoryProducts = [];
    foreach ($navCategories as $c) {
        $cId = (int)val("SELECT id FROM tm_categories WHERE slug=?", [$c['slug']]);
        if ($cId) {
            $navCategoryProducts[$c['slug']] = all(
                "SELECT slug, name FROM tm_products WHERE category_id=? AND is_active=1 ORDER BY is_featured DESC, sort_order LIMIT 5",
                [$cId]
            );
        }
    }
} catch (Throwable $e) {
    $navCategories = []; $navServices = []; $navCategoryProducts = [];
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

          <!-- MEGA PANEL — Kurumsal Limak Holding tarzı (görsel showcase + ürün önizleme) -->
          <div class="mega-panel" id="megaPanel">
            <div class="mega-panel-inner">

              <!-- ÜST BAŞLIK ŞERIDI -->
              <div class="mega-header">
                <div class="container">
                  <div class="mega-header-row">
                    <div>
                      <span class="mega-kicker">Ürün Gruplarımız</span>
                      <h2>Demir-çelik tedariğinde<br>uçtan uca çözüm</h2>
                    </div>
                    <a href="<?= h(url('urunler.php')) ?>" class="mega-header-link">
                      Tüm Ürünleri Gör <span>→</span>
                    </a>
                  </div>
                </div>
              </div>

              <!-- ANA İÇERİK -->
              <div class="container">
                <div class="mega-content">

                  <!-- SOL: Kategori navigasyonu -->
                  <div class="mega-cats-col">
                    <div class="mega-cats-list">
                      <?php
                      $iconMap = [
                        'sac'           => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="6" width="26" height="3" rx=".5"/><rect x="3" y="14" width="26" height="3" rx=".5"/><rect x="3" y="22" width="26" height="3" rx=".5"/></svg>',
                        'boru'          => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="16" cy="16" r="12"/><circle cx="16" cy="16" r="6.5"/><line x1="16" y1="4" x2="16" y2="9.5"/><line x1="16" y1="22.5" x2="16" y2="28"/></svg>',
                        'profil'        => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="4" y="4" width="24" height="24" rx="1"/><rect x="10" y="10" width="12" height="12" rx="1"/></svg>',
                        'hadde'         => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 9h26M3 16h26M3 23h26"/><path d="M9 5v22M23 5v22"/></svg>',
                        'flans-dirsek'  => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M4 16h7a4 4 0 0 1 4 4v8"/><circle cx="16" cy="16" r="4"/><circle cx="4" cy="16" r="2"/><circle cx="15" cy="28" r="2"/><circle cx="22" cy="6" r="3"/><line x1="22" y1="9" x2="22" y2="13"/></svg>',
                        'petek-kiris'   => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M3 16h26"/><path d="M6 11l3-5M14 11l-3-5M22 11l-3-5M30 11l-3-5M3 16l3 11M11 16l3 11M19 16l3 11M27 16l3 11"/></svg>',
                        'panel'         => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="4" y="4" width="24" height="24"/><path d="M4 12h24M4 20h24M12 4v24M20 4v24"/></svg>',
                        'insaat-demiri' => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M6 4v24M11 4v24M16 4v24M21 4v24M26 4v24"/><path d="M4 9h24M4 17h24M4 25h24" stroke-dasharray="2 3" opacity=".5"/></svg>',
                        'osb-levha'     => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="4" y="4" width="24" height="24"/><circle cx="9" cy="9" r="1.2" fill="currentColor"/><circle cx="20" cy="11" r="1.2" fill="currentColor"/><circle cx="13" cy="17" r="1.2" fill="currentColor"/><circle cx="22" cy="21" r="1.2" fill="currentColor"/><circle cx="9" cy="22" r="1.2" fill="currentColor"/></svg>',
                      ];
                      ?>
                      <?php foreach ($navCategories as $idx => $c): ?>
                        <a href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>"
                           class="mega-cat-row<?= $idx === 0 ? ' active' : '' ?>"
                           data-cat-slug="<?= h($c['slug']) ?>"
                           data-cat-name="<?= h($c['name']) ?>"
                           data-cat-desc="<?= h($c['short_desc']) ?>"
                           data-cat-image="<?= h(!empty($c['image']) ? img_url($c['image']) : '') ?>"
                           data-cat-count="<?= (int)$c['product_count'] ?>"
                           data-cat-products='<?= h(json_encode($navCategoryProducts[$c['slug']] ?? [], JSON_UNESCAPED_UNICODE)) ?>'>
                          <div class="mega-cat-icon">
                            <?= $iconMap[$c['slug']] ?? '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="16" cy="16" r="12"/></svg>' ?>
                          </div>
                          <div class="mega-cat-info">
                            <h4><?= h($c['name']) ?></h4>
                            <span class="mega-cat-meta"><?= (int)$c['product_count'] ?> ürün</span>
                          </div>
                          <span class="mega-cat-arrow">→</span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <!-- SAĞ: Görsel + Ürün Önizleme (dinamik, hover'a göre değişir) -->
                  <div class="mega-preview-col" id="megaPreview">
                    <?php $first = $navCategories[0] ?? null; ?>
                    <?php if ($first): ?>
                      <div class="mega-preview-img" id="megaPreviewImg"
                           style="<?= !empty($first['image']) ? 'background-image:url(\''.h(img_url($first['image'])).'\')' : '' ?>">
                        <div class="mega-preview-overlay">
                          <span class="mega-preview-kicker">Kategori</span>
                          <h3 id="megaPreviewTitle"><?= h($first['name']) ?></h3>
                          <p id="megaPreviewDesc"><?= h($first['short_desc']) ?></p>
                        </div>
                      </div>

                      <div class="mega-preview-products">
                        <span class="mega-products-label">Bu kategoride öne çıkan ürünler</span>
                        <div class="mega-products-list" id="megaPreviewProducts">
                          <?php foreach (($navCategoryProducts[$first['slug']] ?? []) as $p): ?>
                            <a href="<?= h(url('urun.php?slug=' . $p['slug'])) ?>" class="mega-product-pill">
                              <?= h($p['name']) ?>
                            </a>
                          <?php endforeach; ?>
                        </div>
                        <a href="<?= h(url('kategori.php?slug=' . $first['slug'])) ?>" class="mega-preview-cta" id="megaPreviewCta">
                          Kategoriyi Görüntüle <span>→</span>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>

                </div>

                <!-- ALT ŞERIT: 3 hizmet kartı (zenginleştirilmiş, gerçek arka plan görselleri) -->
                <div class="mega-bottom-strip">
                  <a href="<?= h(url('urunler.php')) ?>" class="mega-bottom-card"
                     style="background-image:linear-gradient(rgba(15,32,80,.55), rgba(15,32,80,.85)), url('<?= h(url('uploads/categories/sac.jpg')) ?>')">
                    <div class="mega-bottom-content">
                      <span class="mega-bottom-icon">📦</span>
                      <h4>Tüm Ürün Kataloğu</h4>
                      <p>9 ana grupta 50+ çeşit ürün</p>
                      <span class="mega-bottom-arrow">İncele →</span>
                    </div>
                  </a>
                  <a href="<?= h(url('hizmet.php?slug=lazer-kesim')) ?>" class="mega-bottom-card"
                     style="background-image:linear-gradient(rgba(15,32,80,.55), rgba(15,32,80,.85)), url('<?= h(url('uploads/services/lazer-kesim.jpg')) ?>')">
                    <div class="mega-bottom-content">
                      <span class="mega-bottom-icon">⚡</span>
                      <h4>Lazer Kesim Hizmeti</h4>
                      <p>CNC tabanlı yüksek hassasiyet</p>
                      <span class="mega-bottom-arrow">Detaylar →</span>
                    </div>
                  </a>
                  <a href="<?= h(url('hesaplama.php')) ?>" class="mega-bottom-card mega-bottom-dark">
                    <div class="mega-bottom-content">
                      <span class="mega-bottom-icon">⚖️</span>
                      <h4>Ağırlık Hesaplama</h4>
                      <p>14 ürün grubu, 100+ standart ölçü</p>
                      <span class="mega-bottom-arrow">Hesapla →</span>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Mega Panel Hover JS (preview update) -->
          <script>
          (function(){
            const rows = document.querySelectorAll('.mega-cat-row');
            const previewImg = document.getElementById('megaPreviewImg');
            const previewTitle = document.getElementById('megaPreviewTitle');
            const previewDesc = document.getElementById('megaPreviewDesc');
            const previewProducts = document.getElementById('megaPreviewProducts');
            const previewCta = document.getElementById('megaPreviewCta');
            if (!rows.length || !previewImg) return;

            rows.forEach(r => {
              r.addEventListener('mouseenter', () => {
                rows.forEach(x => x.classList.remove('active'));
                r.classList.add('active');
                const img = r.dataset.catImage;
                const name = r.dataset.catName;
                const desc = r.dataset.catDesc;
                const slug = r.dataset.catSlug;
                let products = [];
                try { products = JSON.parse(r.dataset.catProducts || '[]'); } catch(e){}

                if (img) previewImg.style.backgroundImage = "url('" + img + "')";
                else previewImg.style.backgroundImage = '';
                if (previewTitle) previewTitle.textContent = name;
                if (previewDesc) previewDesc.textContent = desc;
                if (previewCta) previewCta.href = '<?= h(url('kategori.php?slug=')) ?>' + slug;
                if (previewProducts) {
                  previewProducts.innerHTML = '';
                  products.forEach(p => {
                    const a = document.createElement('a');
                    a.href = '<?= h(url('urun.php?slug=')) ?>' + p.slug;
                    a.className = 'mega-product-pill';
                    a.textContent = p.name;
                    previewProducts.appendChild(a);
                  });
                }
              });
            });
          })();
          </script>
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
