<?php
require __DIR__ . '/includes/db.php';
$faq = all("SELECT * FROM tm_faq WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Sıkça Sorulan Sorular';
$metaDesc  = 'Tekcan Metal müşterilerimizin en çok sorduğu sorular ve yanıtları.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>SSS</span></nav>
    <h1>Sıkça Sorulan Sorular</h1>
    <p class="lead">Aklınıza takılan soruların yanıtlarını burada bulabilirsiniz. Cevaplayamadığımız bir konu varsa <a href="<?= h(url('iletisim.php')) ?>">bize ulaşın</a>.</p>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">
    <?php if (!$faq): ?>
      <p class="empty-state">Henüz soru eklenmedi.</p>
    <?php else: ?>
    <div class="faq-list">
      <?php foreach ($faq as $i => $f): ?>
      <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= h($f['question']) ?></summary>
        <div class="faq-answer"><?= nl2br(h($f['answer'])) ?></div>
      </details>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
