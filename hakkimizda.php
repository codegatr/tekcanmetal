<?php
require __DIR__ . '/includes/db.php';
$page = row("SELECT * FROM tm_pages WHERE slug='hakkimizda' AND is_active=1");
$pageTitle = $page['title'] ?? 'Hakkımızda';
$metaDesc  = $page['meta_desc'] ?? settings('site_description');
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Hakkımızda</span></nav>
    <h1><?= h($pageTitle) ?></h1>
    <p class="lead">2010'dan bu yana Konya'da demir-çelik tedariği — <em>Ticaret ile bitmeyen dostluk</em>.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="content-prose">
      <?= $page['content'] ?? '<p>İçerik hazırlanıyor.</p>' ?>
    </div>
  </div>
</section>

<section class="section bg-alt">
  <div class="container">
    <div class="stats-bar">
      <div class="stat"><div class="num"><?= h(settings('homepage_stat1_value', '15+')) ?></div><div class="lbl"><?= h(settings('homepage_stat1_label', 'Yıllık Tecrübe')) ?></div></div>
      <div class="stat"><div class="num"><?= h(settings('homepage_stat2_value', '500+')) ?></div><div class="lbl"><?= h(settings('homepage_stat2_label', 'Mutlu Müşteri')) ?></div></div>
      <div class="stat"><div class="num"><?= h(settings('homepage_stat3_value', '24+')) ?></div><div class="lbl"><?= h(settings('homepage_stat3_label', 'Ürün Çeşidi')) ?></div></div>
      <div class="stat"><div class="num"><?= h(settings('homepage_stat4_value', '6')) ?></div><div class="lbl"><?= h(settings('homepage_stat4_label', 'Çözüm Ortağı')) ?></div></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
