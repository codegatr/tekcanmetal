<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = settings('site_short_name') . ' - ' . settings('site_slogan');
$metaDesc  = settings('site_description');

$sliders = all("SELECT * FROM tm_sliders WHERE is_active=1 ORDER BY sort_order");
$cats    = all("SELECT * FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");
$services= all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$partners= all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order LIMIT 8");
$blog    = all("SELECT * FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL ORDER BY published_at DESC LIMIT 3");

require __DIR__ . '/includes/header.php';

$slider = $sliders[0] ?? null;
?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-inner">
      <span class="kicker">Ticaret ile Bitmeyen Dostluk</span>
      <h1>Demir adına <em>Herşey...</em></h1>
      <p>Sac, boru, profil ve hadde ürünlerinde geniş stok, Konya merkezli hızlı sevkiyat ağıyla 1.000+ kurumsal müşteriye 7/24 hizmet.</p>
      <div class="hero-buttons">
        <a href="<?= h(url('urunler.php')) ?>" class="btn-hero primary">Ürünlerimizi Keşfet →</a>
        <a href="<?= h(url('iletisim.php')) ?>" class="btn-hero outline">Teklif Al</a>
      </div>
    </div>
  </div>
</section>

<!-- KATEGORİ KARTLARI -->
<section>
  <div class="container">
    <div class="section-head">
      <span class="kicker">Ürün Gruplarımız</span>
      <h2>Geniş Yelpazeli Stok</h2>
      <p>İnşaattan sanayiye, OEM üreticilerden ferforje ustalarına kadar her ihtiyacınızı karşılayan ürün portföyü.</p>
    </div>

    <div class="grid grid-3">
      <?php foreach ($cats as $c): ?>
      <a class="cat-card" href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>">
        <div class="cat-icon"><?= h(substr($c['name'], 0, 1)) ?></div>
        <h3><?= h($c['name']) ?></h3>
        <p><?= h($c['short_desc']) ?></p>
        <span class="cat-card-link">Detay <span>→</span></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HİZMETLER -->
<section style="background:var(--bg-alt)">
  <div class="container">
    <div class="section-head">
      <span class="kicker">Hizmetlerimiz</span>
      <h2>Kesim & Üretim Çözümleri</h2>
      <p>Sadece ürün satışı değil — projenizi gerçek ürüne dönüştüren özel kesim ve üretim hizmetleri.</p>
    </div>

    <div class="grid grid-3">
      <?php foreach ($services as $s): ?>
      <div class="svc-card">
        <div class="svc-icon"><?= h(strtoupper(substr($s['title'], 0, 1))) ?></div>
        <h3><?= h($s['title']) ?></h3>
        <p><?= h($s['short_desc']) ?></p>
        <a href="<?= h(url('hizmet.php?slug=' . $s['slug'])) ?>" class="card-link">Detayları Gör</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ABOUT / WHY US -->
<section>
  <div class="container">
    <div class="grid grid-2" style="align-items:center;gap:60px">
      <div>
        <span class="kicker">Birlikte Daha Güçlüyüz</span>
        <h2><?= h(settings('homepage_about_title', 'Birlikte Daha Güçlüyüz')) ?></h2>
        <p class="lead"><?= h(settings('homepage_about_text')) ?></p>
        <div class="form-actions">
          <a href="<?= h(url('hakkimizda.php')) ?>" class="btn">Hakkımızda</a>
          <a href="<?= h(url('ekibimiz.php')) ?>" class="btn btn-outline">Ekibimizle Tanışın</a>
        </div>
      </div>
      <div style="position:relative">
        <div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));border-radius:var(--r-lg);padding:50px 40px;color:#fff;box-shadow:var(--shadow-xl);position:relative;overflow:hidden">
          <div style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;background:var(--accent);opacity:.12;border-radius:50%"></div>
          <div style="position:relative;z-index:1">
            <div style="font-family:var(--serif);font-size:60px;color:var(--accent);line-height:.9;margin-bottom:14px">"</div>
            <p style="font-family:var(--serif);font-size:22px;font-style:italic;line-height:1.5;color:#fff;margin-bottom:24px">
              Müşterilerimize bir aile gibi yaklaşıyor, kalıcı dostluklar kuruyoruz.
            </p>
            <div style="display:flex;align-items:center;gap:14px;border-top:1px solid rgba(255,255,255,.1);padding-top:18px">
              <div style="width:46px;height:46px;background:var(--accent);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-weight:700;font-size:18px">M</div>
              <div>
                <div style="font-weight:600">Murat Can</div>
                <div style="font-size:12px;color:#cbd5e1">Tekcan Metal Kurucusu</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-bar">
  <div class="container">
    <div class="stats-grid">
      <div class="stat">
        <div class="stat-num"><?= h(settings('stat_year', '15+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_year_label', 'Yıllık Tecrübe')) ?></div>
      </div>
      <div class="stat">
        <div class="stat-num"><?= h(settings('stat_products', '500+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_products_label', 'Ürün Çeşidi')) ?></div>
      </div>
      <div class="stat">
        <div class="stat-num"><?= h(settings('stat_customers', '1.000+')) ?></div>
        <div class="stat-label"><?= h(settings('stat_customers_label', 'Mutlu Müşteri')) ?></div>
      </div>
      <div class="stat">
        <div class="stat-num"><?= h(settings('stat_delivery', '7/24')) ?></div>
        <div class="stat-label"><?= h(settings('stat_delivery_label', 'Sevkiyat Hizmeti')) ?></div>
      </div>
    </div>
  </div>
</section>

<!-- BLOG -->
<?php if ($blog): ?>
<section>
  <div class="container">
    <div class="section-head">
      <span class="kicker">Bilgi & Haberler</span>
      <h2>Sektör ve Teknik İçerikler</h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($blog as $b): ?>
      <article class="card">
        <div class="card-img" <?= $b['cover_image'] ? 'style="background-image:url(\''.h(img_url($b['cover_image'])).'\');background-size:cover;background-position:center"' : '' ?>></div>
        <div class="card-body">
          <div class="card-cat"><?= h(tr_date($b['published_at'])) ?></div>
          <h3><?= h($b['title']) ?></h3>
          <p><?= h(excerpt($b['excerpt'] ?: $b['content'], 130)) ?></p>
          <a href="<?= h(url('blog-detay.php?slug=' . $b['slug'])) ?>" class="card-link">Devamını Oku</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- PARTNERS -->
<?php if ($partners): ?>
<section style="background:var(--bg-alt);padding:55px 0">
  <div class="container">
    <div class="section-head" style="margin-bottom:30px">
      <span class="kicker">Çözüm Ortaklarımız</span>
      <h2 style="font-size:28px">Türkiye'nin Lider Üreticileriyle Çalışıyoruz</h2>
    </div>
    <div class="partners-grid">
      <?php foreach ($partners as $p): ?>
      <div class="partner">
        <div class="partner-name"><?= h($p['name']) ?></div>
        <div class="partner-desc"><?= h($p['description']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-strip">
  <div class="container">
    <div class="cta-strip-inner">
      <div>
        <h2>Aradığınız Ürünü Bulamadınız mı?</h2>
        <p>Stoklarımızda olmayan özel ürünleri 24-72 saat içinde tedarik ediyoruz. Ekibimiz size yardımcı olsun.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="<?= h(phone_link(settings('site_phone'))) ?>" class="btn">📞 Hemen Ara</a>
        <a href="<?= h(whatsapp_link(settings('site_whatsapp'))) ?>" target="_blank" rel="noopener" class="btn btn-gold">💬 WhatsApp Yaz</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
