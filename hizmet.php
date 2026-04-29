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
$pageTitle = $s['title'];
$metaDesc  = $s['meta_description'] ?: ($s['short_description'] ?: excerpt($s['description'], 160));
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url('hizmetler.php')) ?>">Hizmetler</a> <span>›</span>
      <span><?= h($s['title']) ?></span>
    </nav>
    <h1><?= h($s['title']) ?></h1>
    <?php if (!empty($s['short_description'])): ?>
      <p class="lead"><?= h($s['short_description']) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="svc-detail-grid">
      <div class="svc-detail-main">
        <?php if (!empty($s['image'])): ?>
          <img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>" class="svc-hero-img">
        <?php endif; ?>
        <div class="content-prose">
          <?= $s['description'] ?: '<p>İçerik hazırlanıyor.</p>' ?>
        </div>
      </div>
      <aside class="svc-detail-aside">
        <?php if ($features): ?>
        <div class="svc-aside-box">
          <h3>Özellikler</h3>
          <ul class="svc-features">
            <?php foreach ($features as $f): ?><li><?= h($f) ?></li><?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <div class="svc-aside-box svc-cta-box">
          <h3>Teklif Almak İçin</h3>
          <p>Çizim ya da ölçü dosyanızı bize iletin, hızlıca fiyatlandıralım.</p>
          <a href="<?= h(phone_link(settings('contact_phone', '03323422452'))) ?>" class="btn btn-primary btn-block">📞 Hemen Ara</a>
          <a href="<?= h(whatsapp_link(settings('contact_whatsapp', '05548350226'), 'Merhaba, ' . $s['title'] . ' hizmeti için teklif almak istiyorum.')) ?>" class="btn btn-ghost btn-block" target="_blank" rel="noopener">WhatsApp</a>
          <a href="<?= h(url('iletisim.php')) ?>" class="btn btn-ghost btn-block">İletişim Formu</a>
        </div>
      </aside>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
