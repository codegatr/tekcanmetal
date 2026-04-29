<?php
require __DIR__ . '/includes/db.php';
$cat_slug = $_GET['kategori'] ?? '';
$search   = trim($_GET['q'] ?? '');

$cats = all("SELECT slug,name FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");

$where = ["p.is_active=1"];
$params = [];
if ($cat_slug) {
    $where[] = "c.slug = ?"; $params[] = $cat_slug;
}
if ($search) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
$whereSql = implode(' AND ', $where);

$products = all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
                 FROM tm_products p
                 LEFT JOIN tm_categories c ON c.id = p.category_id
                 WHERE $whereSql
                 ORDER BY p.is_featured DESC, p.sort_order, p.name", $params);

$pageTitle = $cat_slug ? 'Ürünler — ' . h(val("SELECT name FROM tm_categories WHERE slug=?", [$cat_slug])) : 'Tüm Ürünler';
$metaDesc  = 'Tekcan Metal ürün katalogu — sac, boru, profil, hadde, flanş, demir, panel ve daha fazlası.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url('urunler.php')) ?>">Ürünler</a>
      <?php if ($cat_slug): ?> <span>›</span> <span><?= h(val("SELECT name FROM tm_categories WHERE slug=?", [$cat_slug])) ?></span><?php endif; ?>
    </nav>
    <h1><?= h($pageTitle) ?></h1>
    <p class="lead">Stoklarımızdaki ürünleri keşfedin. Toplu siparişlerinizde özel fiyat için iletişime geçin.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <form method="get" class="filter-bar">
      <input type="search" name="q" placeholder="Ürün ara…" value="<?= h($search) ?>">
      <select name="kategori" onchange="this.form.submit()">
        <option value="">— Tüm Kategoriler —</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= h($c['slug']) ?>" <?= $cat_slug === $c['slug'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filtrele</button>
      <?php if ($cat_slug || $search): ?>
        <a href="<?= h(url('urunler.php')) ?>" class="btn btn-ghost">Temizle</a>
      <?php endif; ?>
    </form>

    <?php if (!$products): ?>
      <p class="empty-state">Bu kriterlere uygun ürün bulunamadı.</p>
    <?php else: ?>
    <div class="prod-grid">
      <?php foreach ($products as $p): ?>
      <a class="prod-card" href="<?= h(url('urun.php?slug=' . urlencode($p['slug']))) ?>">
        <div class="prod-img">
          <?php if (!empty($p['image'])): ?>
            <img src="<?= h(img_url($p['image'])) ?>" alt="<?= h($p['name']) ?>" loading="lazy">
          <?php else: ?>
            <div class="prod-placeholder">
              <svg viewBox="0 0 64 64" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="8" y="20" width="48" height="32" rx="2"/>
                <path d="M8 32h48M24 20v32M40 20v32"/>
              </svg>
            </div>
          <?php endif; ?>
          <?php if (!empty($p['is_featured'])): ?><span class="prod-badge">Öne Çıkan</span><?php endif; ?>
        </div>
        <div class="prod-body">
          <div class="prod-cat"><?= h($p['cat_name']) ?></div>
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
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
