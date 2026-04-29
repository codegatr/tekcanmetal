<?php
require __DIR__ . '/includes/db.php';
$team = all("SELECT * FROM tm_team WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Ekibimiz';
$metaDesc  = 'Tekcan Metal ekibi — kurucumuz, satış ve sevkiyat sorumluları.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Ekibimiz</span></nav>
    <h1>Ekibimiz</h1>
    <p class="lead">Sektörel deneyimi ile sizlere hizmet veren Tekcan Metal ailesi.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$team): ?>
      <p class="empty-state">Henüz ekip bilgisi eklenmedi.</p>
    <?php else: ?>
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
          <?php if (!empty($m['phone'])): ?>
            <a href="<?= h(phone_link($m['phone'])) ?>" class="tc-link">📞 <?= h(format_phone($m['phone'])) ?></a>
          <?php endif; ?>
          <?php if (!empty($m['email'])): ?>
            <a href="mailto:<?= h($m['email']) ?>" class="tc-link">✉ <?= h($m['email']) ?></a>
          <?php endif; ?>
          <?php if (!empty($m['phone'])): ?>
            <a href="<?= h(whatsapp_link($m['phone'], 'Merhaba, web sitenizden ulaşıyorum.')) ?>" class="tc-link tc-wa" target="_blank" rel="noopener">WhatsApp</a>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
