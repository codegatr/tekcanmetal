<?php
require __DIR__ . '/includes/db.php';
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order, name");
$pageTitle = 'Çözüm Ortaklarımız';
$metaDesc  = 'Türkiye\'nin önde gelen demir-çelik üreticileri ile tedarik anlaşmalarımız.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Çözüm Ortakları</span></nav>
    <h1>Çözüm Ortaklarımız</h1>
    <p class="lead">Türkiye'nin önde gelen demir-çelik üreticileri ile birlikte çalışıyor, kaliteli ürünleri sizlerle buluşturuyoruz.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$partners): ?>
      <p class="empty-state">Henüz çözüm ortağı eklenmedi.</p>
    <?php else: ?>
    <div class="partners-grid">
      <?php foreach ($partners as $p): ?>
      <?php $href = !empty($p['website']) ? $p['website'] : null; ?>
      <?= $href ? '<a class="partner-card" href="' . h($href) . '" target="_blank" rel="noopener">' : '<div class="partner-card">' ?>
        <div class="partner-logo">
          <?php if (!empty($p['logo'])): ?>
            <img src="<?= h(img_url($p['logo'])) ?>" alt="<?= h($p['name']) ?>">
          <?php else: ?>
            <span class="partner-initial"><?= h(mb_substr($p['name'], 0, 2, 'UTF-8')) ?></span>
          <?php endif; ?>
        </div>
        <h3><?= h($p['name']) ?></h3>
        <?php if (!empty($p['description'])): ?>
          <p><?= h($p['description']) ?></p>
        <?php endif; ?>
      <?= $href ? '</a>' : '</div>' ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
