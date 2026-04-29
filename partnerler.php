<?php
require __DIR__ . '/includes/db.php';
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order, name");
$pageTitle = 'Çözüm Ortaklarımız';
$metaDesc  = 'Türkiye\'nin önde gelen demir-çelik üreticileri ile tedarik anlaşmalarımız.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a>
      <span>›</span>
      <span>Çözüm Ortaklarımız</span>
    </nav>
    <h1>Çözüm Ortaklarımız</h1>
    <p class="lead">Türkiye'nin lider demir-çelik üreticilerinin yetkili temsilciliği güvencesiyle, sanayi ve inşaat sektörüne kaliteli tedarik sunuyoruz.</p>
  </div>
</section>

<!-- Tanıtım metni -->
<section class="section">
  <div class="container">
    <div class="intro-grid">
      <div class="intro-text">
        <span class="kicker">Stratejik İş Birliği</span>
        <h2>Sektörün lider üreticileriyle<br>uzun vadeli iş birliği</h2>
      </div>
      <div class="intro-body">
        <p>Tekcan Metal olarak, Türkiye'nin entegre çelik üretim tesislerinin yetkili temsilcileri olarak faaliyet göstermekteyiz. Bu doğrudan üretici-temsilci ilişkisi sayesinde stoklarımız her zaman taze, fiyatlarımız her zaman rekabetçi, ürünlerimiz her zaman belgeli ve standartlara uygundur.</p>
        <a href="<?= h(url('iletisim.php')) ?>" class="link-arrow">Bize Ulaşın <span>→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- Çözüm ortakları kart grid -->
<section class="section bg-alt">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Üretici Partnerlerimiz</span>
      <h2>Birlikte çalıştığımız markalar</h2>
      <p>Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün önde gelen tedarikçileri ile uzun yıllara dayanan ticari ilişkilerimiz, müşterilerimize sunduğumuz kalite güvencesinin temelidir.</p>
    </div>

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
            <span class="partner-initial"><?= h(mb_strtoupper(mb_substr($p['name'], 0, 2, 'UTF-8'), 'UTF-8')) ?></span>
          <?php endif; ?>
        </div>
        <h3><?= h($p['name']) ?></h3>
        <?php if (!empty($p['description'])): ?>
          <p><?= h($p['description']) ?></p>
        <?php endif; ?>
        <?php if ($href): ?>
          <span class="partner-link">Web Sitesi <span>↗</span></span>
        <?php endif; ?>
      <?= $href ? '</a>' : '</div>' ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-banner-inner">
      <div>
        <span class="kicker kicker-light">Bize Ulaşın</span>
        <h2>Hangi marka, hangi ürün ihtiyacınız?</h2>
        <p>Anlaşmalı üreticilerimizden istediğiniz ürünü tedarik etmek için bizimle iletişime geçin. Aynı gün teklif, hızlı sevkiyat.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-hero primary">Teklif İste</a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp'), 'Merhaba, ürün/teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="btn-hero outline">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

