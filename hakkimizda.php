<?php
require __DIR__ . '/includes/db.php';
$page = row("SELECT * FROM tm_pages WHERE slug='hakkimizda' AND is_active=1");
$team = all("SELECT * FROM tm_team WHERE is_active=1 ORDER BY sort_order");
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

<?php if ($team): ?>
<section class="section">
  <div class="container">
    <div class="section-head">
      <h2 class="section-title">Ekibimiz</h2>
      <p class="section-sub">Tekcan Metal ailesi olarak güveninize layık olmak için çalışıyoruz.</p>
    </div>
    <div class="team-grid">
      <?php foreach ($team as $m): ?>
      <article class="team-card">
        <div class="avatar">
          <?php if (!empty($m['photo'])): ?>
            <img src="<?= h(img_url($m['photo'])) ?>" alt="<?= h($m['full_name']) ?>">
          <?php else: ?>
            <div class="avatar-initial"><?= h(mb_substr($m['full_name'], 0, 1, 'UTF-8')) ?></div>
          <?php endif; ?>
        </div>
        <h3><?= h($m['full_name']) ?></h3>
        <div class="role"><?= h($m['title']) ?></div>
        <?php if (!empty($m['bio'])): ?><p><?= h($m['bio']) ?></p><?php endif; ?>
        <div class="team-contact">
          <?php if (!empty($m['phone'])): ?><a href="<?= h(phone_link($m['phone'])) ?>"><?= h(format_phone($m['phone'])) ?></a><?php endif; ?>
          <?php if (!empty($m['email'])): ?><a href="mailto:<?= h($m['email']) ?>"><?= h($m['email']) ?></a><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
