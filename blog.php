<?php
require __DIR__ . '/includes/db.php';
$cat_slug = $_GET['kategori'] ?? '';
$page_no  = max(1, (int)($_GET['s'] ?? 1));
$per_page = 9;
$offset = ($page_no - 1) * $per_page;

$cats = all("SELECT * FROM tm_blog_categories ORDER BY name");

$where = ["p.is_published=1"];
$params = [];
if ($cat_slug) {
    $where[] = "c.slug=?";
    $params[] = $cat_slug;
}
$whereSql = implode(' AND ', $where);

$total = (int)val("SELECT COUNT(*) FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id=p.category_id WHERE $whereSql", $params);
$pages = max(1, (int)ceil($total / $per_page));

$posts = all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
              FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id = p.category_id
              WHERE $whereSql
              ORDER BY p.published_at DESC, p.id DESC
              LIMIT $per_page OFFSET $offset", $params);

$pageTitle = $cat_slug ? 'Blog — ' . h(val("SELECT name FROM tm_blog_categories WHERE slug=?", [$cat_slug])) : 'Blog';
$metaDesc  = 'Demir-çelik sektöründen haberler, ürün rehberleri ve teknik yazılar.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url('blog.php')) ?>">Blog</a>
      <?php if ($cat_slug): ?> <span>›</span> <span><?= h(val("SELECT name FROM tm_blog_categories WHERE slug=?", [$cat_slug])) ?></span><?php endif; ?>
    </nav>
    <h1>Blog</h1>
    <p class="lead">Demir-çelik sektörü, ürün rehberleri ve teknik yazılarımız.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($cats): ?>
    <div class="blog-cats">
      <a href="<?= h(url('blog.php')) ?>" class="blog-cat <?= !$cat_slug ? 'active' : '' ?>">Tümü</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= h(url('blog.php?kategori=' . urlencode($c['slug']))) ?>" class="blog-cat <?= $cat_slug === $c['slug'] ? 'active' : '' ?>"><?= h($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$posts): ?>
      <p class="empty-state">Henüz yayınlanan blog yazısı yok.</p>
    <?php else: ?>
    <div class="blog-grid">
      <?php foreach ($posts as $p): ?>
      <article class="blog-card">
        <a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>" class="blog-img-link">
          <?php if (!empty($p['featured_image'])): ?>
            <img src="<?= h(img_url($p['featured_image'])) ?>" alt="<?= h($p['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="blog-placeholder">📝</div>
          <?php endif; ?>
        </a>
        <div class="blog-body">
          <?php if (!empty($p['cat_name'])): ?>
            <a href="<?= h(url('blog.php?kategori=' . urlencode($p['cat_slug']))) ?>" class="blog-cat-tag"><?= h($p['cat_name']) ?></a>
          <?php endif; ?>
          <h3><a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>"><?= h($p['title']) ?></a></h3>
          <p><?= h($p['excerpt'] ?: excerpt($p['content'], 140)) ?></p>
          <div class="blog-meta">
            <span><?= h(tr_date($p['published_at'])) ?></span>
            <a href="<?= h(url('blog-detay.php?slug=' . urlencode($p['slug']))) ?>">Devamını Oku →</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="pager">
      <?php for ($i=1; $i<=$pages; $i++):
          $qs = ['s' => $i] + ($cat_slug ? ['kategori' => $cat_slug] : []);
      ?>
        <a href="?<?= http_build_query($qs) ?>" class="pager-link <?= $i === $page_no ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
