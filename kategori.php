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
$pageTitle = tr_field($cat, 'name') ?: $cat['name'];
$metaDesc  = tr_field($cat, 'meta_desc') ?: ('Tekcan Metal ' . tr_field($cat, 'name') . ' ' . t('products.category_meta_suffix', 'kategorisi ürünleri.'));
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url_lang('')) ?>"><?= h(t('bc.home', 'Anasayfa')) ?></a> <span>›</span>
      <a href="<?= h(url_lang('urunler.php')) ?>"><?= h(t('bc.products', 'Ürünler')) ?></a> <span>›</span>
      <span><?= h(tr_field($cat, 'name')) ?></span>
    </nav>
    <h1><?= h(tr_field($cat, 'name')) ?></h1>
    <?php if (tr_has($cat, 'description')): ?>
      <p class="lead"><?= h(tr_field($cat, 'description')) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$products): ?>
      <p class="empty-state"><?= h(t('products.empty_category', 'Bu kategoride henüz ürün bulunmuyor.')) ?></p>
    <?php else: ?>
    <div class="prod-grid">
      <?php foreach ($products as $p): ?>
      <a class="prod-card" href="<?= h(url_lang('urun.php?slug=' . urlencode($p['slug']))) ?>">
        <div class="prod-img">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= h(img_url($p['image'])) ?>" alt="<?= h(tr_field($p, 'title') ?: $p['name']) ?>" loading="lazy">
          <?php else: ?>
            <div class="prod-placeholder">
              <svg viewBox="0 0 64 64" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="8" y="20" width="48" height="32" rx="2"/><path d="M8 32h48M24 20v32M40 20v32"/>
              </svg>
            </div>
          <?php endif; ?>
        </div>
        <div class="prod-body">
          <h3 class="prod-title"><?= h(tr_field($p, 'title') ?: $p['name']) ?></h3>
          <?php if (tr_has($p, 'short_desc') || !empty($p['short_desc'])): ?>
            <p class="prod-desc"><?= h(tr_field($p, 'short_desc') ?: $p['short_desc']) ?></p>
          <?php endif; ?>
          <div class="prod-cta"><?= h(t('btn.view_detail', 'Detayları Gör')) ?> →</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="cta-strip" style="margin-top:48px">
      <div class="cta-text">
        <h3><?= h(t('products.cta_custom_title', 'Aradığınız ölçü/cins bu listede yok mu?')) ?></h3>
        <p><?= h(t('products.cta_custom_lead', 'Stok dışı veya özel ölçü ürünler için bizi arayın, hızlıca tedarik edelim.')) ?></p>
      </div>
      <div class="cta-actions">
        <a href="<?= h(phone_link(settings('site_phone', '03323422452'))) ?>" class="btn btn-primary"><?= h(t('btn.call_now', 'Hemen Ara')) ?></a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . tr_field($cat, 'name') . ' ' . t('products.whatsapp_intent', 'kategorisinden ürün almak istiyorum.'))) ?>" class="btn btn-ghost" target="_blank" rel="noopener"><?= h(t('btn.whatsapp', 'WhatsApp')) ?></a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
