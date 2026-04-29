<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$s = row("SELECT * FROM tm_services WHERE slug=? AND is_active=1", [$slug]);
if (!$s) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$features = [];
if (!empty($s['features'])) {
    $tmp = json_decode($s['features'], true);
    if (is_array($tmp)) $features = $tmp;
}
// Diğer hizmetler (alt alta öneri için)
$otherServices = all("SELECT slug,title,image,short_desc FROM tm_services WHERE is_active=1 AND id<>? ORDER BY sort_order LIMIT 3", [$s['id']]);

$pageTitle = $s['title'];
$metaDesc  = $s['short_desc'] ?? excerpt(strip_tags($s['description'] ?? ''), 160);
require __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a>
      <span>›</span>
      <a href="<?= h(url('hizmetler.php')) ?>">Endüstriyel Yetkinlikler</a>
      <span>›</span>
      <span><?= h($s['title']) ?></span>
    </nav>
    <h1><?= h($s['title']) ?></h1>
    <?php if (!empty($s['short_desc'])): ?>
      <p class="lead"><?= h($s['short_desc']) ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- HERO IMAGE — Hizmet görseli tam genişlik -->
<?php if (!empty($s['image'])): ?>
<section class="svc-hero-section">
  <div class="container">
    <div class="svc-hero-img-wrap">
      <img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>">
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ANA İÇERİK -->
<section class="section">
  <div class="container">
    <div class="svc-detail-grid">

      <!-- SOL: İçerik -->
      <div class="svc-detail-main">
        <?php if (!empty($s['short_desc'])): ?>
          <span class="kicker">Hakkında</span>
          <h2>Profesyonel <?= h($s['title']) ?> Hizmeti</h2>
        <?php endif; ?>
        <div class="content-prose">
          <?= $s['description'] ?: '<p>Bu hizmet için detaylı içerik yakında eklenecektir.</p>' ?>
        </div>
      </div>

      <!-- SAĞ: Özellikler + CTA sticky -->
      <aside class="svc-detail-aside">
        <?php if ($features): ?>
        <div class="svc-aside-box">
          <span class="kicker">Hizmet Özellikleri</span>
          <h3>Avantajlarımız</h3>
          <ul class="svc-features">
            <?php foreach ($features as $f): ?>
              <li><?= h($f) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <div class="svc-aside-box svc-cta-box">
          <span class="kicker kicker-light">Teklif İste</span>
          <h3>Bu Hizmet İçin Teklif Almak İster misiniz?</h3>
          <p>Çizim ya da ölçü dosyanızı bize iletin, satış ekibimiz aynı gün geri dönüş yapsın.</p>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $s['title'] . ' hizmeti için teklif almak istiyorum.')) ?>" class="svc-cta-btn svc-cta-primary" target="_blank" rel="noopener">
            💬 WhatsApp ile İletişim
          </a>
          <a href="<?= h(phone_link(settings('site_phone', '03323422452'))) ?>" class="svc-cta-btn svc-cta-secondary">
            📞 <?= h(format_phone(settings('site_phone', '03323422452'))) ?>
          </a>
          <a href="<?= h(url('iletisim.php')) ?>" class="svc-cta-btn svc-cta-ghost">
            📝 İletişim Formu
          </a>
        </div>
      </aside>
    </div>
  </div>
</section>

<!-- DİĞER HİZMETLER -->
<?php if ($otherServices): ?>
<section class="section bg-alt">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Diğer Yetkinliklerimiz</span>
      <h2>Diğer endüstriyel hizmetlerimiz</h2>
    </div>
    <div class="cap-grid">
      <?php foreach ($otherServices as $o): ?>
        <a href="<?= h(url('hizmet.php?slug=' . $o['slug'])) ?>" class="cap-card">
          <?php if (!empty($o['image'])): ?>
            <div class="cap-thumb"><img src="<?= h(img_url($o['image'])) ?>" alt="<?= h($o['title']) ?>" loading="lazy"></div>
          <?php endif; ?>
          <div class="cap-body">
            <h3><?= h($o['title']) ?></h3>
            <p><?= h($o['short_desc']) ?></p>
            <span class="link-arrow">Detay <span>→</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-banner-inner">
      <div>
        <span class="kicker kicker-light">Bize Ulaşın</span>
        <h2>Diğer hizmet ve ürünlerimiz için</h2>
        <p>Tek tedarikçi ile sac, boru, profil, hadde ve atölye hizmetleri — uçtan uca demir-çelik çözümü.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= h(url('urunler.php')) ?>" class="btn-hero primary">Ürün Gruplarımız</a>
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-hero outline">İletişim</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
