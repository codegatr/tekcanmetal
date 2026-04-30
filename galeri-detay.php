<?php
require __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$album = row("SELECT * FROM tm_gallery_albums WHERE slug=? AND is_active=1", [$slug]);
if (!$album) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$images = all("SELECT * FROM tm_gallery_images WHERE album_id=? ORDER BY sort_order", [$album['id']]);
$pageTitle = tr_field($album, 'title') ?: $album['title'];
$metaDesc  = excerpt(tr_field($album, 'description') ?: $album['description'], 160);
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span>
      <a href="<?= h(url_lang('galeri.php')) ?>">Galeri</a> <span>›</span>
      <span><?= h(tr_field($album, 'title') ?: $album['title']) ?></span>
    </nav>
    <h1><?= h(tr_field($album, 'title') ?: $album['title']) ?></h1>
    <?php if (!empty($album['description'])): ?>
      <p class="lead"><?= h(tr_field($album, 'description') ?: $album['description']) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$images): ?>
      <p class="empty-state">Bu albümde henüz görsel yok.</p>
    <?php else: ?>
    <div class="gallery-grid">
      <?php foreach ($images as $i => $im): ?>
      <button type="button" class="gallery-item"
              data-index="<?= $i ?>"
              data-img="<?= h(img_url($im['image'])) ?>"
              data-caption="<?= h($im['caption'] ?? '') ?>">
        <img src="<?= h(img_url($im['image'])) ?>" alt="<?= h($im['caption'] ?? $album['title']) ?>" loading="lazy">
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<div id="lightbox" class="lightbox" hidden>
  <button class="lb-close" aria-label="Kapat">×</button>
  <button class="lb-prev" aria-label="Önceki">‹</button>
  <button class="lb-next" aria-label="Sonraki">›</button>
  <div class="lb-frame">
    <img id="lbImg" src="" alt="">
    <div id="lbCap" class="lb-cap"></div>
  </div>
</div>

<script>
(function(){
  const items = Array.from(document.querySelectorAll('.gallery-item'));
  const lb = document.getElementById('lightbox');
  const img = document.getElementById('lbImg');
  const cap = document.getElementById('lbCap');
  let cur = 0;

  function show(i){
    if (!items.length) return;
    cur = (i + items.length) % items.length;
    img.src = items[cur].dataset.img;
    cap.textContent = items[cur].dataset.caption || '';
    lb.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function close(){ lb.hidden = true; document.body.style.overflow = ''; }

  items.forEach(el => el.addEventListener('click', () => show(parseInt(el.dataset.index, 10))));
  lb.querySelector('.lb-close').addEventListener('click', close);
  lb.querySelector('.lb-prev').addEventListener('click', () => show(cur-1));
  lb.querySelector('.lb-next').addEventListener('click', () => show(cur+1));
  lb.addEventListener('click', e => { if (e.target === lb) close(); });
  document.addEventListener('keydown', e => {
    if (lb.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(cur-1);
    if (e.key === 'ArrowRight') show(cur+1);
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
