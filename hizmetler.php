<?php
require __DIR__ . '/includes/db.php';
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Endüstriyel Yetkinliklerimiz';
$metaDesc  = 'Tekcan Metal endüstriyel yetkinlikleri: lazer kesim, oksijen kesim, dekoratif sac üretimi.';
require __DIR__ . '/includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a>
      <span>›</span>
      <span>Endüstriyel Yetkinlikler</span>
    </nav>
    <h1>Endüstriyel Yetkinliklerimiz</h1>
    <p class="lead">Sadece tedarik değil; çelik ürünleri ihtiyacınız olan ölçü ve biçimde işleyerek sunuyoruz. Lazer kesim, oksijen kesim ve dekoratif sac üretimiyle projelerinize uçtan uca çözüm.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$services): ?>
      <p class="empty-state">Henüz hizmet eklenmedi.</p>
    <?php else: ?>

    <div class="section-head section-head-left">
      <span class="kicker">Atölye Yetkinlikleri</span>
      <h2>Tedarik ve üretimde uçtan uca çözüm</h2>
      <p>Modern CNC ekipmanlarımız, deneyimli operatör kadromuz ve aynı gün üretim kapasitemizle, küçük adetli özel projelerden seri üretimlere kadar her ölçekte hizmet veriyoruz.</p>
    </div>

    <div class="cap-grid">
      <?php foreach ($services as $s):
          $features = [];
          if (!empty($s['features'])) {
              $tmp = json_decode($s['features'], true);
              if (is_array($tmp)) $features = $tmp;
          }
      ?>
        <a href="<?= h(url('hizmet.php?slug=' . urlencode($s['slug']))) ?>" class="cap-card">
          <?php if (!empty($s['image'])): ?>
            <div class="cap-thumb">
              <img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="cap-body">
            <h3><?= h($s['title']) ?></h3>
            <?php if (!empty($s['short_desc'])): ?>
              <p><?= h($s['short_desc']) ?></p>
            <?php endif; ?>
            <?php if ($features): ?>
            <ul class="svc-features svc-features-compact">
              <?php foreach (array_slice($features, 0, 3) as $f): ?>
                <li><?= h($f) ?></li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <span class="link-arrow">Detayları İncele <span>→</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>
</section>

<!-- CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-banner-inner">
      <div>
        <span class="kicker kicker-light">Özel Projeniz İçin</span>
        <h2>Çiziminizi gönderin, fiyat alalım</h2>
        <p>DXF, DWG veya PDF dosyanızı bize iletin; satış ekibimiz aynı gün geri dönüş yapsın. Ölçü, kalınlık, malzeme ve adet bilgilerinize göre en uygun çözümü sunalım.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-hero primary">Çizim Gönder</a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, atölye hizmetleri için teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="btn-hero outline">WhatsApp</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
