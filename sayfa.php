<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$page = row("SELECT * FROM tm_pages WHERE slug=? AND is_active=1", [$slug]);
if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$pageTitle = $page['title'];
$metaDesc  = $page['meta_desc'] ?: excerpt($page['content'], 160);
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span><?= h($page['title']) ?></span></nav>
    <h1><?= h($page['title']) ?></h1>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">
    <div class="content-prose">
      <?= $page['content'] ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
