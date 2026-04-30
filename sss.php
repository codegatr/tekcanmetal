<?php
require __DIR__ . '/includes/db.php';

$faqs = all("SELECT * FROM tm_faq WHERE is_active=1 ORDER BY category, sort_order");

// Kategoriye göre grupla
$grouped = [];
foreach ($faqs as $f) {
    $cat = $f['category'] ?? 'genel';
    $grouped[$cat][] = $f;
}

// Kategori meta bilgisi
$catMeta = [
    'tedarik'    => ['label' => 'Tedarik & Sipariş',      'icon' => '📦'],
    'sevkiyat'   => ['label' => 'Sevkiyat & Teslimat',    'icon' => '🚛'],
    'metal'      => ['label' => 'Metal & Çelik Bilgisi',  'icon' => '⚙'],
    'hesaplama'  => ['label' => 'Hesaplama & Teknik',     'icon' => '📐'],
    'odeme'      => ['label' => 'Ödeme & Fatura',         'icon' => '💳'],
    'islem'      => ['label' => 'Atölye Hizmetleri',      'icon' => '🔧'],
    'genel'      => ['label' => 'Genel Sorular',          'icon' => '❓'],
];

// Sıralama
$catOrder = ['tedarik', 'sevkiyat', 'metal', 'hesaplama', 'odeme', 'islem', 'genel'];
$sorted = [];
foreach ($catOrder as $k) {
    if (isset($grouped[$k])) $sorted[$k] = $grouped[$k];
}
foreach ($grouped as $k => $v) {
    if (!isset($sorted[$k])) $sorted[$k] = $v;
}

$totalCount = count($faqs);

$pageTitle = t('faq.title', 'Sıkça Sorulan Sorular');
$metaDesc  = t('faq.meta_desc', 'Tekcan Metal — demir, çelik, sac, boru, profil ve hesaplama konularında müşterilerimizin en çok sorduğu sorular ve detaylı yanıtları.');
require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.sss-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --gold:#c9a86b;
  --red:#c8102e;
  --red-dark:#a00d24;
  --paper:#fafaf7;
  --paper-2:#f3f1ec;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* HERO */
.sss-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;
  padding:120px 0 100px;
  position:relative;overflow:hidden;
  border-bottom:4px solid var(--gold);
}
.sss-hero::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.sss-hero::after{
  content:'?';
  position:absolute;
  bottom:-130px;right:-30px;
  font-family:var(--serif);
  font-size:480px;font-weight:500;font-style:italic;
  color:rgba(201,168,107,.06);
  line-height:1;
  pointer-events:none;
}
.sss-hero .container{position:relative;z-index:2;text-align:center}
.sss-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:30px;
}
.sss-hero-eyebrow::before,
.sss-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.sss-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 86px);
  font-weight:500;line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 24px;color:#fff;
}
.sss-hero h1 em{font-style:italic;color:var(--gold)}
.sss-hero-lead{
  font-family:var(--sans);
  font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;margin:0 auto 40px;
}
.sss-hero-stats{
  display:flex;justify-content:center;gap:50px;flex-wrap:wrap;
  padding-top:30px;border-top:1px solid rgba(255,255,255,.12);
}
.sss-hero-stat{text-align:center}
.sss-hero-stat strong{
  display:block;
  font-family:var(--serif);font-size:38px;font-weight:500;
  color:var(--gold);line-height:1;letter-spacing:-1px;
}
.sss-hero-stat span{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-top:8px;display:block;
}

/* CATEGORY NAV */
.sss-catnav{
  background:#fff;
  border-bottom:1px solid var(--line);
  padding:0;
  position:sticky;
  top:80px;
  z-index:30;
  box-shadow:0 2px 12px rgba(5,13,36,.04);
}
.sss-catnav-grid{
  display:flex;
  gap:0;
  overflow-x:auto;
  scrollbar-width:none;
}
.sss-catnav-grid::-webkit-scrollbar{display:none}
.sss-catnav-item{
  flex:1;
  min-width:160px;
  text-decoration:none;color:inherit;
  padding:20px 16px;
  text-align:center;
  border-right:1px solid var(--line);
  transition:.2s;
  position:relative;
}
.sss-catnav-item:last-child{border-right:0}
.sss-catnav-item:hover{
  background:var(--paper);
}
.sss-catnav-item.active{
  background:var(--paper);
}
.sss-catnav-item.active::after{
  content:'';
  position:absolute;
  bottom:0;left:0;right:0;
  height:3px;
  background:var(--red);
}
.sss-catnav-icon{
  font-size:22px;
  margin-bottom:6px;
  display:block;
}
.sss-catnav-label{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:1.5px;
  text-transform:uppercase;
  color:var(--navy);
}
.sss-catnav-count{
  display:block;
  font-family:var(--serif);
  font-size:13px;
  color:var(--gold);
  font-style:italic;
  margin-top:2px;
}

/* MAIN CONTENT */
.sss-main{
  padding:80px 0;
}
.sss-content{
  max-width:920px;
  margin:0 auto;
}

.sss-cat-section{
  margin-bottom:60px;
  scroll-margin-top:160px;
}
.sss-cat-section:last-child{margin-bottom:0}
.sss-cat-head{
  display:flex;
  align-items:center;
  gap:20px;
  margin-bottom:30px;
  padding-bottom:18px;
  border-bottom:2px solid var(--navy);
}
.sss-cat-head-icon{
  width:60px;height:60px;
  background:var(--navy);
  color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-size:28px;
  flex-shrink:0;
}
.sss-cat-head-text{flex:1}
.sss-cat-head-eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;
  color:var(--red);
  margin-bottom:4px;
}
.sss-cat-head h2{
  font-family:var(--serif);
  font-size:34px;font-weight:600;
  letter-spacing:-.5px;line-height:1.1;
  margin:0;color:var(--navy);
}
.sss-cat-head-count{
  font-family:var(--serif);
  font-size:48px;
  font-style:italic;
  font-weight:500;
  color:var(--gold);
  letter-spacing:-1px;
  line-height:1;
}

/* FAQ ACCORDION */
.sss-faq{
  background:#fff;
  border:1px solid var(--line);
  margin-bottom:10px;
  transition:.2s;
}
.sss-faq[open]{
  border-color:var(--gold);
  box-shadow:0 6px 18px rgba(5,13,36,.06);
}
.sss-faq summary{
  list-style:none;
  cursor:pointer;
  padding:22px 26px;
  display:flex;
  align-items:center;
  gap:18px;
  user-select:none;
  transition:.15s;
}
.sss-faq summary::-webkit-details-marker{display:none}
.sss-faq summary:hover{background:var(--paper)}
.sss-faq[open] summary{
  background:var(--paper);
  border-bottom:1px solid var(--line);
}
.sss-faq-num{
  font-family:var(--serif);
  font-size:20px;
  font-style:italic;
  font-weight:500;
  color:var(--gold);
  flex-shrink:0;
  min-width:36px;
}
.sss-faq-q{
  flex:1;
  font-family:var(--serif);
  font-size:20px;
  font-weight:600;
  letter-spacing:-.2px;
  line-height:1.3;
  color:var(--navy);
}
.sss-faq[open] .sss-faq-q{color:var(--red-dark)}
.sss-faq-toggle{
  width:32px;height:32px;
  border:1.5px solid var(--line);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
  transition:.25s;
  position:relative;
}
.sss-faq-toggle::before,
.sss-faq-toggle::after{
  content:'';
  position:absolute;
  background:var(--navy);
  transition:.25s;
}
.sss-faq-toggle::before{
  width:14px;height:1.5px;
}
.sss-faq-toggle::after{
  width:1.5px;height:14px;
}
.sss-faq[open] .sss-faq-toggle{
  border-color:var(--red);
  background:var(--red);
}
.sss-faq[open] .sss-faq-toggle::before,
.sss-faq[open] .sss-faq-toggle::after{
  background:#fff;
}
.sss-faq[open] .sss-faq-toggle::after{
  transform:rotate(90deg);
  opacity:0;
}

.sss-faq-answer{
  padding:24px 26px 28px;
  font-family:var(--sans);
  font-size:14.5px;
  line-height:1.75;
  color:#3a3a3a;
  background:#fff;
}
.sss-faq-answer p{margin:0 0 1em}
.sss-faq-answer p:last-child{margin-bottom:0}
.sss-faq-answer strong{color:var(--navy);font-weight:700}
.sss-faq-answer a{
  color:var(--red);
  text-decoration:underline;
  text-decoration-color:var(--gold);
  text-underline-offset:3px;
  font-weight:600;
}
.sss-faq-answer a:hover{color:var(--red-dark)}
.sss-faq-answer ul{
  margin:0 0 1em;padding:0;list-style:none;
}
.sss-faq-answer ul li{
  padding-left:20px;position:relative;margin-bottom:8px;
}
.sss-faq-answer ul li::before{
  content:'';
  position:absolute;left:0;top:9px;
  width:6px;height:6px;
  background:var(--gold);
  transform:rotate(45deg);
}

/* CONTACT CTA */
.sss-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  padding:80px 0;
  position:relative;overflow:hidden;
  border-top:4px solid var(--gold);
}
.sss-cta::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(circle at 80% 50%, rgba(201,168,107,.1) 0%, transparent 60%);
  pointer-events:none;
}
.sss-cta-inner{
  text-align:center;max-width:680px;margin:0 auto;
  position:relative;z-index:2;
}
.sss-cta-eyebrow{
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:18px;
  display:inline-flex;align-items:center;gap:14px;
}
.sss-cta-eyebrow::before,
.sss-cta-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--gold);
}
.sss-cta h2{
  font-family:var(--serif);
  font-size:clamp(32px, 4vw, 46px);
  font-weight:500;font-style:italic;
  letter-spacing:-.5px;line-height:1.15;
  margin:0 0 16px;color:#fff;
}
.sss-cta h2 strong{font-style:normal;color:var(--gold);font-weight:600}
.sss-cta p{
  font-family:var(--sans);
  font-size:15px;line-height:1.7;
  color:rgba(255,255,255,.75);
  margin:0 0 32px;
}
.sss-cta-actions{
  display:flex;justify-content:center;gap:14px;flex-wrap:wrap;
}
.sss-btn{
  display:inline-block;
  padding:18px 36px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;
  transition:.2s;border:1px solid transparent;
}
.sss-btn-primary{
  background:var(--red);color:#fff;border-color:var(--red);
}
.sss-btn-primary:hover{
  background:var(--red-dark);border-color:var(--red-dark);
  transform:translateY(-2px);
  box-shadow:0 12px 26px rgba(200,16,46,.4);
}
.sss-btn-ghost{
  background:transparent;color:var(--gold);border-color:var(--gold);
}
.sss-btn-ghost:hover{
  background:var(--gold);color:var(--navy);
  transform:translateY(-2px);
}

@media (max-width:768px){
  .sss-catnav{position:relative;top:0}
  .sss-catnav-item{min-width:120px;padding:14px 10px}
  .sss-catnav-label{font-size:10px}
  .sss-cat-head-icon{width:50px;height:50px;font-size:22px}
  .sss-cat-head h2{font-size:24px}
  .sss-cat-head-count{font-size:36px}
  .sss-faq summary{padding:18px 20px;gap:12px}
  .sss-faq-q{font-size:16px}
  .sss-faq-answer{padding:20px;font-size:14px}
}
</style>

<div class="sss-page">

  <!-- HERO -->
  <section class="sss-hero">
    <div class="container">
      <div class="sss-hero-eyebrow">Bilgi Merkezi</div>
      <h1>Sıkça Sorulan <em>Sorular</em></h1>
      <p class="sss-hero-lead">
        Demir-çelik dünyasının en çok merak edilen konuları, teknik bilgiler ve müşterilerimizin sıkça sorduğu sorulara detaylı yanıtlar.
      </p>
      <div class="sss-hero-stats">
        <div class="sss-hero-stat"><strong><?= $totalCount ?></strong><span>Soru &amp; Yanıt</span></div>
        <div class="sss-hero-stat"><strong><?= count($sorted) ?></strong><span>Konu Başlığı</span></div>
        <div class="sss-hero-stat"><strong>2 sa</strong><span>Yanıt Süresi</span></div>
      </div>
    </div>
  </section>

  <!-- CATEGORY NAV -->
  <?php if ($sorted): ?>
  <nav class="sss-catnav">
    <div class="sss-catnav-grid">
      <?php foreach ($sorted as $catKey => $items): ?>
        <?php $meta = $catMeta[$catKey] ?? ['label' => ucfirst($catKey), 'icon' => '❓']; ?>
        <a href="#cat-<?= h($catKey) ?>" class="sss-catnav-item">
          <span class="sss-catnav-icon"><?= $meta['icon'] ?></span>
          <span class="sss-catnav-label"><?= h($meta['label']) ?></span>
          <span class="sss-catnav-count"><?= count($items) ?> soru</span>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>
  <?php endif; ?>

  <!-- MAIN CONTENT -->
  <section class="sss-main">
    <div class="container">
      <div class="sss-content">

        <?php if (!$sorted): ?>
          <p style="text-align:center;font-family:var(--serif);font-style:italic;color:#5a5a5a;font-size:18px;padding:60px 0">
            Henüz soru eklenmedi. Aklınıza takılan bir konu varsa <a href="<?= h(url('iletisim.php')) ?>" style="color:var(--red)">bize ulaşın</a>.
          </p>
        <?php else: ?>

        <?php foreach ($sorted as $catKey => $items): ?>
        <?php $meta = $catMeta[$catKey] ?? ['label' => ucfirst($catKey), 'icon' => '❓']; ?>
        <section class="sss-cat-section" id="cat-<?= h($catKey) ?>">
          <header class="sss-cat-head">
            <div class="sss-cat-head-icon"><?= $meta['icon'] ?></div>
            <div class="sss-cat-head-text">
              <div class="sss-cat-head-eyebrow">Konu Başlığı</div>
              <h2><?= h($meta['label']) ?></h2>
            </div>
            <div class="sss-cat-head-count"><?= str_pad(count($items), 2, '0', STR_PAD_LEFT) ?></div>
          </header>

          <?php foreach ($items as $i => $f): ?>
          <details class="sss-faq">
            <summary>
              <span class="sss-faq-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?>.</span>
              <span class="sss-faq-q"><?= h($f['question']) ?></span>
              <span class="sss-faq-toggle" aria-hidden="true"></span>
            </summary>
            <div class="sss-faq-answer">
              <?= nl2br($f['answer']) ?>
            </div>
          </details>
          <?php endforeach; ?>
        </section>
        <?php endforeach; ?>

        <?php endif; ?>

      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="sss-cta">
    <div class="container">
      <div class="sss-cta-inner">
        <div class="sss-cta-eyebrow">Sorunuz mu var?</div>
        <h2>Aklınıza takılan <strong>bir konu mu</strong> var?</h2>
        <p>Burada cevabını bulamadığınız her sorunuz için satış ekibimiz hizmetinizdedir. WhatsApp veya iletişim formundan yazın, en geç 2 saat içinde dönüş yapalım.</p>
        <div class="sss-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="sss-btn sss-btn-primary">İletişim Formu</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, sizden bilgi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="sss-btn sss-btn-ghost">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
// Smooth scroll + sticky nav active state
(function(){
  const navItems = document.querySelectorAll('.sss-catnav-item');
  const sections = document.querySelectorAll('.sss-cat-section');

  navItems.forEach(item => {
    item.addEventListener('click', e => {
      e.preventDefault();
      const target = document.querySelector(item.getAttribute('href'));
      if (target) {
        navItems.forEach(n => n.classList.remove('active'));
        item.classList.add('active');
        const offset = window.innerWidth < 768 ? 20 : 160;
        window.scrollTo({
          top: target.getBoundingClientRect().top + window.pageYOffset - offset,
          behavior: 'smooth'
        });
      }
    });
  });

  // IntersectionObserver for active state
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const id = e.target.id;
        navItems.forEach(n => {
          n.classList.toggle('active', n.getAttribute('href') === '#' + id);
        });
      }
    });
  }, { rootMargin: '-30% 0px -60% 0px' });

  sections.forEach(s => observer.observe(s));
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
