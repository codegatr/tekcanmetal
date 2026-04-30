<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$p = row("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
          FROM tm_products p LEFT JOIN tm_categories c ON c.id = p.category_id
          WHERE p.slug=? AND p.is_active=1", [$slug]);
if (!$p) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$images = all("SELECT * FROM tm_product_images WHERE product_id=? ORDER BY sort_order", [$p['id']]);
$related = all("SELECT * FROM tm_products WHERE category_id=? AND id<>? AND is_active=1 ORDER BY RAND() LIMIT 4", [$p['category_id'], $p['id']]);

$specs = [];
if (!empty($p['specifications'])) {
    $tmp = json_decode($p['specifications'], true);
    if (is_array($tmp)) $specs = $tmp;
}

$pageTitle = $p['name'];
$metaDesc  = $p['meta_desc'] ?: ($p['short_desc'] ?: excerpt($p['description'], 160));
require __DIR__ . '/includes/header.php';
?>
<section class="page-header page-header-sm">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url('urunler.php')) ?>">Ürünler</a> <span>›</span>
      <a href="<?= h(url('kategori.php?slug=' . urlencode($p['cat_slug']))) ?>"><?= h($p['cat_name']) ?></a> <span>›</span>
      <span><?= h($p['name']) ?></span>
    </nav>
  </div>
</section>

<section class="section product-detail">
  <div class="container">
    <div class="pd-grid">
      <div class="pd-gallery">
        <div class="pd-main-img">
          <?php $mainImg = $p['image'] ?: ($images[0]['image_path'] ?? null); ?>
          <?php if ($mainImg): ?>
            <img id="pdMain" src="<?= h(img_url($mainImg)) ?>" alt="<?= h($p['name']) ?>">
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
            <button type="button" class="pd-thumb" data-img="<?= h(img_url($im['image_path'])) ?>"><img src="<?= h(img_url($im['image_path'])) ?>" alt=""></button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="pd-info">
        <div class="pd-cat-tag"><a href="<?= h(url('kategori.php?slug=' . urlencode($p['cat_slug']))) ?>"><?= h($p['cat_name']) ?></a></div>
        <h1><?= h($p['name']) ?></h1>
        <?php if (!empty($p['short_desc'])): ?>
          <p class="pd-short"><?= h($p['short_desc']) ?></p>
        <?php endif; ?>

        <?php if ($specs): ?>
        <table class="pd-specs">
          <tbody>
            <?php foreach ($specs as $k => $v): ?>
            <tr><th><?= h($k) ?></th><td><?= h($v) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>

        <?php if ($p['stock_status']): ?>
        <div class="pd-stock pd-stock-<?= h($p['stock_status']) ?>">
          <?php
          $stockLabel = ['in_stock' => '✓ Stoklarımızda Mevcut', 'low_stock' => '⚠ Stok Sınırlı', 'out_of_stock' => '✗ Stokta Yok', 'on_order' => 'ⓘ Sipariş Üzerine'];
          echo h($stockLabel[$p['stock_status']] ?? '');
          ?>
        </div>
        <?php endif; ?>

        <div class="pd-actions">
          <a href="<?= h(phone_link(settings('contact_phone', '03323422452'))) ?>" class="btn btn-primary btn-lg">📞 Fiyat / Stok için Ara</a>
          <a href="<?= h(whatsapp_link(settings('contact_whatsapp', '05548350226'), 'Merhaba, ' . $p['name'] . ' ürünü hakkında bilgi almak istiyorum.')) ?>" class="btn btn-ghost btn-lg" target="_blank" rel="noopener">WhatsApp ile Sor</a>
        </div>
      </div>
    </div>

    <?php if (!empty($p['description'])): ?>
    <div class="pd-description">
      <h2>Ürün Açıklaması</h2>
      <div class="content-prose"><?= $p['description'] ?></div>
    </div>
    <?php endif; ?>

    <?php if ($related): ?>
    <div class="pd-related">
      <h2 class="section-title">Benzer Ürünler</h2>
      <div class="prod-grid">
        <?php foreach ($related as $r): ?>
        <a class="prod-card" href="<?= h(url('urun.php?slug=' . urlencode($r['slug']))) ?>">
          <div class="prod-img">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= h(img_url($r['image'])) ?>" alt="<?= h($r['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="prod-placeholder"><svg viewBox="0 0 64 64" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="8" y="20" width="48" height="32" rx="2"/><path d="M8 32h48"/></svg></div>
            <?php endif; ?>
          </div>
          <div class="prod-body">
            <h3 class="prod-title"><?= h($r['name']) ?></h3>
            <div class="prod-cta">Detay →</div>
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
