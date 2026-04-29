<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = settings('site_short_name') . ' — ' . settings('site_slogan');
$metaDesc  = settings('site_description');

$sliders  = all("SELECT * FROM tm_sliders WHERE is_active=1 ORDER BY sort_order");
$cats     = all("SELECT * FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order LIMIT 8");
$news     = all("SELECT * FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL ORDER BY published_at DESC LIMIT 4");

require __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER -->
<?php if ($sliders): ?>
<section class="hero-slider" id="heroSlider">
  <?php foreach ($sliders as $idx => $sl): ?>
    <div class="hero-slide<?= $idx === 0 ? ' active' : '' ?>"
         <?= !empty($sl['image']) ? 'style="background-image:url(\''.h(url($sl['image'])).'\')"' : '' ?>>
      <div class="container">
        <div class="hero-slide-content">
          <?php if (!empty($sl['subtitle'])): ?>
            <span class="hero-eyebrow"><?= h($sl['subtitle']) ?></span>
          <?php endif; ?>
          <h1><?= h($sl['title']) ?></h1>
          <?php if (!empty($sl['description'])): ?><p><?= h($sl['description']) ?></p><?php endif; ?>
          <?php if (!empty($sl['link_url'])): ?>
            <a href="<?= h(url($sl['link_url'])) ?>" class="hero-cta"><?= h($sl['link_text'] ?: 'Detay') ?> <span>›</span></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (count($sliders) > 1): ?>
    <div class="hero-dots">
      <?php foreach ($sliders as $idx => $sl): ?>
        <button class="hero-dot<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
(function(){
  const root = document.getElementById('heroSlider');
  if (!root) return;
  const slides = root.querySelectorAll('.hero-slide');
  const dots   = root.querySelectorAll('.hero-dot');
  const total  = slides.length;
  if (total < 2) return;
  let idx = 0, timer = null;
  function go(n){
    idx = (n + total) % total;
    slides.forEach((s, i) => s.classList.toggle('active', i === idx));
    dots.forEach((d, i) => d.classList.toggle('active', i === idx));
  }
  function next(){ go(idx + 1); }
  function start(){ stop(); timer = setInterval(next, 6500); }
  function stop(){ if (timer) { clearInterval(timer); timer = null; } }
  dots.forEach(d => d.addEventListener('click', e => { go(+e.currentTarget.dataset.index); start(); }));
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  let xStart = null;
  root.addEventListener('touchstart', e => xStart = e.touches[0].clientX);
  root.addEventListener('touchend', e => {
    if (xStart === null) return;
    const dx = e.changedTouches[0].clientX - xStart;
    if (Math.abs(dx) > 50) { dx > 0 ? go(idx-1) : next(); start(); }
    xStart = null;
  });
  start();
})();
</script>
<?php endif; ?>

<!-- KURUMSAL TANITIM -->
<section class="intro-block">
  <div class="container">
    <div class="intro-grid">
      <div class="intro-text">
        <span class="kicker">Tekcan Metal</span>
        <h2>2005’ten bu yana demir-çelik sektöründe<br>kurumsal güveni temsil ediyoruz.</h2>
      </div>
      <div class="intro-body">
        <p>Konya Karatay merkezimizden, sac, boru, profil, hadde, panel ve inşaat demiri başta olmak üzere geniş bir ürün yelpazesinde tedarik ve üretim hizmeti sunuyoruz. Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş gibi sektörün lider üreticilerinin temsilciliği bizim güvencemizdir.</p>
        <a href="<?= h(url('hakkimizda.php')) ?>" class="link-arrow">Biz Kimiz <span>→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- FAALİYET ALANLARIMIZ -->
<section class="sector-section bg-alt">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Çözüm Yelpazemiz</span>
      <h2>Faaliyet Alanlarımız</h2>
      <p>Sanayi, inşaat ve özel proje gereksinimlerine yönelik <?= count($cats) ?> ana kategoride tedarik ve üretim hizmeti sunuyoruz.</p>
    </div>

    <div class="sector-grid">
      <?php foreach ($cats as $c): ?>
      <a class="sector-card" href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>">
        <?php if (!empty($c['image'])): ?>
          <div class="sector-thumb">
            <img src="<?= h(img_url($c['image'])) ?>" alt="<?= h($c['name']) ?>" loading="lazy">
          </div>
        <?php endif; ?>
        <div class="sector-body">
          <h3><?= h($c['name']) ?></h3>
          <p><?= h($c['short_desc']) ?></p>
          <span class="sector-link">İncele <span>→</span></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DEĞERLERİMİZ -->
<section class="values-section">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Kurumsal Değerlerimiz</span>
      <h2>İlke, kalite ve güvenle çalışıyoruz.</h2>
      <p>Üç temel ilkemizi her projemizde, her teslimatımızda yaşatmaya devam ediyoruz.</p>
    </div>

    <div class="values-grid">
      <div class="value-block">
        <div class="value-num">01</div>
        <h3>Kalite ve Standart</h3>
        <p>Türkiye’nin lider çelik üreticilerinin temsilciliği güvencesiyle, her ürünümüz uluslararası kalite standartlarındadır. EN, DIN ve TS standartlarına uygun belgeli tedarik.</p>
        <a href="<?= h(url('partnerler.php')) ?>" class="link-arrow">Çözüm Ortaklarımız <span>→</span></a>
      </div>
      <div class="value-block">
        <div class="value-num">02</div>
        <h3>Operasyonel Mükemmellik</h3>
        <p>Geniş stoğumuz, lazer ve oksijen kesim atölyemiz, aynı gün üretim seçeneğimiz ve 7/24 sevkiyat ağımızla zaman, teslimatımızın bir parçasıdır.</p>
        <a href="<?= h(url('hizmetler.php')) ?>" class="link-arrow">Yetkinliklerimiz <span>→</span></a>
      </div>
      <div class="value-block">
        <div class="value-num">03</div>
        <h3>Müşteri Odaklılık</h3>
        <p>"Ticaret ile Bitmeyen Dostluk" felsefemizle, her müşteriyi bir iş ortağı olarak görüyor; uzun vadeli ve güvene dayalı ilişkiler kuruyoruz.</p>
        <a href="<?= h(url('sadakat.php')) ?>" class="link-arrow">Sadakat Programı <span>→</span></a>
      </div>
    </div>
  </div>
</section>

<!-- İSTATİSTİKLER -->
<section class="stats-strip">
  <div class="container">
    <div class="stats-grid">
      <div class="stat-block">
        <div class="stat-num"><?= h(settings('stat_year', '20+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_year_label', 'Yıllık Tecrübe')) ?></div>
      </div>
      <div class="stat-block">
        <div class="stat-num"><?= h(settings('stat_products', '1.000+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_products_label', 'Ürün Çeşidi')) ?></div>
      </div>
      <div class="stat-block">
        <div class="stat-num"><?= h(settings('stat_customers', '1.000+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_customers_label', 'Mutlu Müşteri')) ?></div>
      </div>
      <div class="stat-block">
        <div class="stat-num"><?= h(settings('stat_orders', '3.436')) ?></div>
        <div class="stat-label"><?= h(settings('stat_orders_label', 'Ürün Siparişi')) ?></div>
      </div>
      <div class="stat-block">
        <div class="stat-num"><?= h(settings('stat_delivery', '7/24')) ?></div>
        <div class="stat-label"><?= h(settings('stat_delivery_label', 'Sevkiyat Hizmeti')) ?></div>
      </div>
    </div>
  </div>
</section>

<!-- ENDÜSTRİYEL YETKİNLİKLER -->
<section class="capabilities-section bg-alt">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Endüstriyel Yetkinlikler</span>
      <h2>Tedarik ve üretimde uçtan uca çözüm.</h2>
      <p>Stoklu satışın yanı sıra atölye yetkinliklerimizle proje tabanlı üretim hizmetleri sunuyoruz.</p>
    </div>

    <div class="cap-grid">
      <?php foreach ($services as $s): ?>
        <a href="<?= h(url('hizmet.php?slug=' . $s['slug'])) ?>" class="cap-card">
          <?php if (!empty($s['image'])): ?>
            <div class="cap-thumb"><img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>" loading="lazy"></div>
          <?php endif; ?>
          <div class="cap-body">
            <h3><?= h($s['title']) ?></h3>
            <p><?= h($s['short_desc']) ?></p>
            <span class="link-arrow">Detay <span>→</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HABERLER -->
<?php if ($news): ?>
<section class="news-section">
  <div class="container">
    <div class="section-head section-head-row">
      <div>
        <span class="kicker">Tekcan’dan Haberler</span>
        <h2>Sektörel Gelişmeler ve Duyurular</h2>
      </div>
      <a href="<?= h(url('blog.php')) ?>" class="link-arrow">Tüm Haberler <span>→</span></a>
    </div>

    <div class="news-grid">
      <?php foreach ($news as $n): ?>
        <a href="<?= h(url('blog-detay.php?slug=' . $n['slug'])) ?>" class="news-card">
          <?php if (!empty($n['cover_image'])): ?>
            <div class="news-thumb"><img src="<?= h(img_url($n['cover_image'])) ?>" alt="<?= h($n['title']) ?>" loading="lazy"></div>
          <?php endif; ?>
          <div class="news-body">
            <span class="news-date"><?= h(tr_date($n['published_at'])) ?></span>
            <h3><?= h($n['title']) ?></h3>
            <p><?= h(excerpt($n['excerpt'] ?: $n['content'], 110)) ?></p>
            <span class="link-arrow">Devamı <span>→</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ÇÖZÜM ORTAKLARI -->
<?php if ($partners): ?>
<section class="partners-section bg-alt">
  <div class="container">
    <div class="section-head section-head-left">
      <span class="kicker">Çözüm Ortaklarımız</span>
      <h2>Türkiye'nin lider çelik üreticilerinin temsilcisiyiz.</h2>
    </div>

    <div class="partners-grid">
      <?php foreach ($partners as $p): ?>
      <div class="partner">
        <?php if (!empty($p['logo'])): ?>
          <div class="partner-logo"><img src="<?= h(img_url($p['logo'])) ?>" alt="<?= h($p['name']) ?>" loading="lazy"></div>
        <?php endif; ?>
        <div class="partner-name"><?= h($p['name']) ?></div>
        <div class="partner-desc"><?= h($p['description']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BANNER -->
<section class="cta-banner">
  <div class="container">
    <div class="cta-banner-inner">
      <div>
        <span class="kicker kicker-light">Bize Ulaşın</span>
        <h2>Projeniz için özel teklif almak ister misiniz?</h2>
        <p>Uzman satış ekibimiz, ihtiyacınıza özel ürün ve sevkiyat planlamasını en kısa sürede hazırlar.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-hero primary">Teklif İste</a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp'), 'Merhaba, ürün/teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="btn-hero outline">WhatsApp ile İletişim</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
