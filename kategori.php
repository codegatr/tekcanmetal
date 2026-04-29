<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$cat = row("SELECT * FROM tm_categories WHERE slug=? AND is_active=1", [$slug]);
if (!$cat) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$products = all("SELECT * FROM tm_products WHERE category_id=? AND is_active=1 ORDER BY is_featured DESC, sort_order, name", [$cat['id']]);
$pageTitle = $cat['name'];
$metaDesc  = $cat['meta_description'] ?: ('Tekcan Metal ' . $cat['name'] . ' kategorisi ürünleri.');
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url('urunler.php')) ?>">Ürünler</a> <span>›</span>
      <span><?= h($cat['name']) ?></span>
    </nav>
    <h1><?= h($cat['name']) ?></h1>
    <?php if ($cat['description']): ?>
      <p class="lead"><?= h($cat['description']) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$products): ?>
      <p class="empty-state">Bu kategoride henüz ürün bulunmuyor.</p>
    <?php else: ?>
    <div class="prod-grid">
      <?php foreach ($products as $p): ?>
      <a class="prod-card" href="<?= h(url('urun.php?slug=' . urlencode($p['slug']))) ?>">
        <div class="prod-img">
          <?php if (!empty($p['main_image'])): ?>
            <img src="<?= h(img_url($p['main_image'])) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
          <?php else: ?>
            <div class="prod-placeholder">
              <svg viewBox="0 0 64 64" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="8" y="20" width="48" height="32" rx="2"/><path d="M8 32h48M24 20v32M40 20v32"/>
              </svg>
            </div>
          <?php endif; ?>
        </div>
        <div class="prod-body">
          <h3 class="prod-title"><?= h($p['name']) ?></h3>
          <?php if (!empty($p['short_description'])): ?>
            <p class="prod-desc"><?= h($p['short_description']) ?></p>
          <?php endif; ?>
          <div class="prod-cta">Detayları Gör →</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="cta-strip" style="margin-top:48px">
      <div class="cta-text">
        <h3>Aradığınız ölçü/cins bu listede yok mu?</h3>
        <p>Stok dışı veya özel ölçü ürünler için bizi arayın, hızlıca tedarik edelim.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= h(phone_link(settings('contact_phone', '03323422452'))) ?>" class="btn btn-primary">Hemen Ara</a>
        <a href="<?= h(whatsapp_link(settings('contact_whatsapp', '05548350226'), 'Merhaba, ' . $cat['name'] . ' kategorisinden ürün almak istiyorum.')) ?>" class="btn btn-ghost" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
