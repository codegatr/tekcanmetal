<?php
require __DIR__ . '/includes/db.php';
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Endüstriyel Yetkinliklerimiz';
$metaDesc  = 'Tekcan Metal endüstriyel yetkinlikleri: lazer kesim, oksijen kesim, dekoratif sac üretimi. CNC tabanlı tam donanımlı atölye.';
require __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="<?= h(url('')) ?>">Anasayfa</a>
      <span>›</span>
      <span>Endüstriyel Yetkinlikler</span>
    </nav>
    <h1>Endüstriyel Yetkinliklerimiz</h1>
    <p class="lead">Sadece tedarik değil; çelik ürünleri ihtiyacınız olan ölçü ve biçimde işleyerek sunuyoruz.</p>
  </div>
</section>

<!-- GİRİŞ — Limak intro tarzı 2 sütun -->
<section class="section">
  <div class="container">
    <div class="svc-intro-grid">
      <div class="svc-intro-left">
        <span class="kicker">Atölye Yetkinliği</span>
        <h2>Tedarikçiniz olmaktan<br>çözüm ortağınız olmaya</h2>
      </div>
      <div class="svc-intro-right">
        <p class="svc-intro-lead">Tekcan Metal olarak modern CNC ekipmanlarımız, deneyimli operatör kadromuz ve aynı gün üretim kapasitemizle, küçük adetli özel projelerden seri üretimlere kadar her ölçekte yan sanayi hizmeti sunuyoruz.</p>
        <p>Sac levhalarınızı tedarik etmenin yanında, ihtiyacınız olan biçim ve ölçüde keserek, bükerek veya işleyerek size teslim ediyoruz. Bu sayede tek bir tedarikçi ile hem ham malzeme hem de işlenmiş ürün ihtiyacınızı karşılıyorsunuz.</p>
        <div class="svc-intro-stats">
          <div class="svc-stat">
            <strong>%100</strong>
            <span>CNC Hassasiyet</span>
          </div>
          <div class="svc-stat">
            <strong>24 sa</strong>
            <span>Aynı Gün Üretim</span>
          </div>
          <div class="svc-stat">
            <strong><?= count($services) ?></strong>
            <span>Atölye Hizmeti</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HİZMETLER — Alternating büyük blok layout -->
<?php if (!$services): ?>
<section class="section bg-alt">
  <div class="container">
    <p class="empty-state">Henüz hizmet eklenmedi.</p>
  </div>
</section>
<?php else: ?>

<?php foreach ($services as $idx => $s):
    $features = [];
    if (!empty($s['features'])) {
        $tmp = json_decode($s['features'], true);
        if (is_array($tmp)) $features = $tmp;
    }
    $reverse = $idx % 2 === 1;
    $bgClass = $idx % 2 === 0 ? 'bg-alt' : '';
?>
<section class="svc-block <?= $bgClass ?>">
  <div class="container">
    <div class="svc-block-grid <?= $reverse ? 'reverse' : '' ?>">

      <!-- GÖRSEL -->
      <div class="svc-block-visual">
        <?php if (!empty($s['image'])): ?>
          <div class="svc-block-img-wrap">
            <img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>" loading="lazy">
          </div>
        <?php else: ?>
          <div class="svc-block-img-wrap svc-block-placeholder">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
          </div>
        <?php endif; ?>
        <span class="svc-block-num"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></span>
      </div>

      <!-- İÇERİK -->
      <div class="svc-block-content">
        <span class="kicker">Hizmet <?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></span>
        <h2><?= h($s['title']) ?></h2>
        <?php if (!empty($s['short_desc'])): ?>
          <p class="svc-block-lead"><?= h($s['short_desc']) ?></p>
        <?php endif; ?>

        <?php if ($features): ?>
        <div class="svc-block-features">
          <h4>Bu Hizmetin Avantajları</h4>
          <ul class="svc-features">
            <?php foreach (array_slice($features, 0, 6) as $f): ?>
              <li><?= h($f) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <div class="svc-block-actions">
          <a href="<?= h(url('hizmet.php?slug=' . urlencode($s['slug']))) ?>" class="svc-block-link">
            Detaylı Bilgi <span>→</span>
          </a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $s['title'] . ' hizmeti için teklif almak istiyorum.')) ?>"
             target="_blank" rel="noopener" class="svc-block-cta">
            Teklif İste
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endforeach; ?>
<?php endif; ?>

<!-- SÜREÇ — Limak tarzı işleyiş bölümü -->
<section class="section">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Çalışma Sürecimiz</span>
      <h2>Çiziminizden teslimata kadar 4 adım</h2>
      <p>Talebinizi aldığımızdan ürünün size teslim edildiği ana kadar şeffaf ve hızlı bir süreç işletiyoruz.</p>
    </div>

    <div class="svc-process">
      <div class="svc-process-step">
        <div class="svc-process-num">01</div>
        <h3>Talep ve Çizim</h3>
        <p>DXF, DWG veya PDF formatındaki çiziminizi ya da el çiziminizi bize iletin. Ölçü, malzeme ve adet bilgisi yeterli.</p>
      </div>
      <div class="svc-process-step">
        <div class="svc-process-num">02</div>
        <h3>Teklif ve Onay</h3>
        <p>Çiziminizi inceleyerek aynı gün içinde detaylı teklifimizi gönderiyoruz. Onayınızla üretim sürecini başlatıyoruz.</p>
      </div>
      <div class="svc-process-step">
        <div class="svc-process-num">03</div>
        <h3>Üretim ve Kontrol</h3>
        <p>CNC tabanlı modern ekipmanlarımızla üretim yapılır, her parça toleranslara göre kontrol edilir.</p>
      </div>
      <div class="svc-process-step">
        <div class="svc-process-num">04</div>
        <h3>Sevkiyat</h3>
        <p>Konya merkezli geniş sevkiyat ağımızla Türkiye genelinde hızlı teslimat sağlıyoruz.</p>
      </div>
    </div>
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
