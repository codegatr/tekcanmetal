<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$p = row("SELECT p.*, c.name AS cat_name, c.name_en AS cat_name_en, c.name_ar AS cat_name_ar, c.name_ru AS cat_name_ru, c.slug AS cat_slug
          FROM tm_products p LEFT JOIN tm_categories c ON c.id = p.category_id
          WHERE p.slug=? AND p.is_active=1", [$slug]);
if (!$p) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$images = all("SELECT * FROM tm_product_images WHERE product_id=? ORDER BY sort_order", [$p['id']]);
$related = all("SELECT * FROM tm_products WHERE category_id=? AND id<>? AND is_active=1 ORDER BY RAND() LIMIT 4", [$p['category_id'], $p['id']]);

// Specs JSON parse — i18n-aware
$specs = [];
$specsRaw = tr_field($p, 'specs') ?: ($p['specs'] ?? '');
if (!empty($specsRaw)) {
    $tmp = json_decode($specsRaw, true);
    if (is_array($tmp)) $specs = $tmp;
}

// Helper: kategori adını dile göre al
$catName = '';
$lang = current_lang();
if ($lang !== 'tr') {
    $catName = $p['cat_name_' . $lang] ?? '';
}
if (!$catName) $catName = $p['cat_name'] ?? '';

$pageTitle = tr_field($p, 'title') ?: $p['name'];
$metaDesc  = tr_field($p, 'meta_desc') ?: tr_field($p, 'short_desc') ?: ($p['short_desc'] ?? '') ?: excerpt(tr_field($p, 'description') ?: ($p['description'] ?? ''), 160);
require __DIR__ . '/includes/header.php';
?>
<section class="page-header page-header-sm">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url_lang('')) ?>"><?= h(t('bc.home', 'Anasayfa')) ?></a> <span>›</span>
      <a href="<?= h(url_lang('urunler.php')) ?>"><?= h(t('bc.products', 'Ürünler')) ?></a> <span>›</span>
      <a href="<?= h(url_lang('kategori.php?slug=' . urlencode($p['cat_slug']))) ?>"><?= h($catName) ?></a> <span>›</span>
      <span><?= h(tr_field($p, 'title') ?: $p['name']) ?></span>
    </nav>
  </div>
</section>

<section class="section product-detail">
  <div class="container">
    <div class="pd-grid">
      <div class="pd-gallery">
        <div class="pd-main-img">
          <?php $mainImg = $p['image'] ?: ($images[0]['image'] ?? null); ?>
          <?php if ($mainImg): ?>
            <img id="pdMain" src="<?= h(img_url($mainImg)) ?>" alt="<?= h(tr_field($p, 'title') ?: $p['name']) ?>">
          <?php else: ?>
            <div class="pd-placeholder">
              <svg viewBox="0 0 200 200" width="120" height="120" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="20" y="60" width="160" height="100" rx="3"/><path d="M20 100h160M70 60v100M130 60v100"/>
              </svg>
            </div>
          <?php endif; ?>
        </div>
        <?php if (count($images) > 1 || ($p['image'] && $images)): ?>
        <div class="pd-thumbs">
          <?php if ($p['image']): ?>
            <button type="button" class="pd-thumb" data-img="<?= h(img_url($p['image'])) ?>"><img src="<?= h(img_url($p['image'])) ?>" alt=""></button>
          <?php endif; ?>
          <?php foreach ($images as $im): ?>
            <button type="button" class="pd-thumb" data-img="<?= h(img_url($im['image'])) ?>"><img src="<?= h(img_url($im['image'])) ?>" alt=""></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="pd-info">
        <div class="pd-cat-tag"><a href="<?= h(url_lang('kategori.php?slug=' . urlencode($p['cat_slug']))) ?>"><?= h($catName) ?></a></div>
        <h1><?= h(tr_field($p, 'title') ?: $p['name']) ?></h1>
        <?php $shortDesc = tr_field($p, 'short_desc') ?: ($p['short_desc'] ?? ''); ?>
        <?php if (!empty($shortDesc)): ?>
          <p class="pd-short"><?= h($shortDesc) ?></p>
        <?php endif; ?>

        <?php if ($specs): ?>
        <div class="pd-specs-wrap">
          <div class="pd-specs-head">
            <span class="pd-specs-eyebrow"><?= h(t('label.specifications', 'Teknik Özellikler')) ?></span>
            <h3><?= h(t('product.specifications_h3', 'Ürün Spesifikasyonları')) ?></h3>
          </div>
          <table class="pd-specs">
            <tbody>
              <?php foreach ($specs as $k => $v): ?>
              <tr><th><?= h($k) ?></th><td><?= h($v) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>

        <div class="pd-actions">
          <a href="<?= h(phone_link(settings('site_phone', '03323422452'))) ?>" class="btn btn-primary btn-lg">📞 <?= h(t('product.call_for_price', 'Fiyat / Stok için Ara')) ?></a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . (tr_field($p, 'title') ?: $p['name']) . ' ' . t('product.whatsapp_intent', 'ürünü hakkında bilgi almak istiyorum.'))) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener"><?= h(t('product.whatsapp_ask', 'WhatsApp ile Sor')) ?></a>
        </div>
      </div>
    </div>

    <?php $description = tr_field($p, 'description') ?: ($p['description'] ?? ''); ?>
    <?php if (!empty($description)): ?>
    <div class="pd-description">
      <h2><?= h(t('product.description_h2', 'Ürün Açıklaması')) ?></h2>
      <div class="content-prose"><?= $description ?></div>
    </div>
    <?php endif; ?>

    <?php if ($related): ?>
    <div class="pd-related">
      <h2 class="section-title"><?= h(t('label.related_products', 'Benzer Ürünler')) ?></h2>
      <div class="prod-grid">
        <?php foreach ($related as $r): ?>
        <a class="prod-card" href="<?= h(url_lang('urun.php?slug=' . urlencode($r['slug']))) ?>">
          <div class="prod-img">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= h(img_url($r['image'])) ?>" alt="<?= h(tr_field($r, 'title') ?: $r['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="prod-placeholder"><svg viewBox="0 0 64 64" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="8" y="20" width="48" height="32" rx="2"/><path d="M8 32h48"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="prod-body">
            <h3 class="prod-title"><?= h(tr_field($r, 'title') ?: $r['name']) ?></h3>
            <div class="prod-cta"><?= h(t('btn.detail', 'Detay')) ?> →</div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
document.querySelectorAll('.pd-thumb').forEach(btn => {
  btn.addEventListener('click', () => {
    const img = btn.getAttribute('data-img');
    const main = document.getElementById('pdMain');
    if (main && img) main.src = img;
    document.querySelectorAll('.pd-thumb').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
