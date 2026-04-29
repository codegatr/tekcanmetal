<?php
require __DIR__ . '/includes/db.php';
$albums = all("SELECT a.*, (SELECT image_path FROM tm_gallery_images WHERE album_id = a.id ORDER BY sort_order LIMIT 1) AS cover,
                          (SELECT COUNT(*) FROM tm_gallery_images WHERE album_id = a.id) AS img_count
               FROM tm_gallery_albums a WHERE is_active=1 ORDER BY sort_order, id DESC");
$pageTitle = 'Galeri';
$metaDesc  = 'Tekcan Metal galeri — atölye, ürünlerimiz, sevkiyat ve makinelerimizden görseller.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Galeri</span></nav>
    <h1>Galeri</h1>
    <p class="lead">Atölyemizden, ürünlerimizden ve hizmetlerimizden kareler.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$albums): ?>
      <p class="empty-state">Henüz galeri eklenmedi.</p>
    <?php else: ?>
    <div class="album-grid">
      <?php foreach ($albums as $a): ?>
      <a class="album-card" href="<?= h(url('galeri-detay.php?slug=' . urlencode($a['slug']))) ?>">
        <div class="album-cover">
          <?php $cover = $a['cover'] ?? $a['cover_image'] ?? null; ?>
          <?php if (!empty($cover)): ?>
            <img src="<?= h(img_url($cover)) ?>" alt="<?= h($a['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="album-placeholder">📷</div>
          <?php endif; ?>
          <span class="album-count"><?= (int)$a['img_count'] ?> görsel</span>
        </div>
        <div class="album-body">
          <h3><?= h($a['title']) ?></h3>
          <?php if (!empty($a['description'])): ?>
            <p><?= h(excerpt($a['description'], 100)) ?></p>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
