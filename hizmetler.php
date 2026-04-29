<?php
require __DIR__ . '/includes/db.php';
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Hizmetlerimiz';
$metaDesc  = 'Lazer kesim, oksijen kesim ve dekoratif sac hizmetleri.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Hizmetler</span></nav>
    <h1>Hizmetlerimiz</h1>
    <p class="lead">Sadece tedarik değil; ürünü ihtiyacınız olan biçimde işleyerek sunuyoruz.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$services): ?>
      <p class="empty-state">Henüz hizmet eklenmedi.</p>
    <?php else: ?>
    <div class="svc-grid">
      <?php foreach ($services as $s):
          $features = [];
          if (!empty($s['features'])) {
              $tmp = json_decode($s['features'], true);
              if (is_array($tmp)) $features = $tmp;
          }
      ?>
      <article class="svc-card svc-card-lg">
        <?php if (!empty($s['icon'])): ?>
          <div class="svc-icon"><?= $s['icon'] ?></div>
        <?php endif; ?>
        <h3><a href="<?= h(url('hizmet.php?slug=' . urlencode($s['slug']))) ?>"><?= h($s['title']) ?></a></h3>
        <?php if (!empty($s['short_description'])): ?>
          <p><?= h($s['short_description']) ?></p>
        <?php endif; ?>
        <?php if ($features): ?>
        <ul class="svc-features">
          <?php foreach (array_slice($features, 0, 4) as $f): ?>
            <li><?= h($f) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="<?= h(url('hizmet.php?slug=' . urlencode($s['slug']))) ?>" class="svc-link">Detayları İncele →</a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
