<?php
require_once __DIR__ . '/includes/db.php';

$pageTitle = settings('site_short_name') . ' — ' . settings('site_slogan');
$metaDesc  = settings('site_description');

$sliders  = all("SELECT * FROM tm_sliders WHERE is_active=1 ORDER BY sort_order");
$cats     = all("SELECT * FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order LIMIT 8");
$news     = all("SELECT * FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL ORDER BY published_at DESC LIMIT 4");

$logoFile = settings('logo', 'assets/img/logo.png');

// v1.0.72: LCP optimizasyonu — ilk slider görselini preload et (Largest Contentful Paint kısalır)
$preloadImages = [];
if (!empty($sliders[0]['image'])) {
    $preloadImages[] = img_url($sliders[0]['image']);
}

require __DIR__ . '/includes/header.php';
?>

<!-- HERO — Limak'ın 50 logosu sahnesi gibi: koyu lacivert + ortada parlayan logo -->
<section class="hero-cinema" id="heroCinema">
  <?php if ($sliders): ?>
    <?php foreach ($sliders as $idx => $sl):
        // v1.0.72: WebP varsa modern tarayıcılarda WebP servisi
        $sliderImg = $sl['image'];
        $sliderImgUrl = img_url($sliderImg);
        $webpUrl = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $sliderImg);
        $hasWebp = ($webpUrl !== $sliderImg) && file_exists(__DIR__ . '/' . $webpUrl);
        $webpFullUrl = $hasWebp ? img_url($webpUrl) : null;

        // image-set: WebP modern + JPG/PNG fallback
        if ($hasWebp) {
            $bgImage = "linear-gradient(135deg, rgba(5,13,36,.78) 0%, rgba(12,30,68,.62) 50%, rgba(20,54,114,.78) 100%), "
                     . "image-set(url('" . h($webpFullUrl) . "') type('image/webp'), url('" . h($sliderImgUrl) . "') type('image/jpeg'))";
        } else {
            $bgImage = "linear-gradient(135deg, rgba(5,13,36,.78) 0%, rgba(12,30,68,.62) 50%, rgba(20,54,114,.78) 100%), "
                     . "url('" . h($sliderImgUrl) . "')";
        }
    ?>
      <div class="cinema-slide<?= $idx === 0 ? ' active' : '' ?>"
           style="background-image: <?= $bgImage ?>;">
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="cinema-slide active"></div>
  <?php endif; ?>

  <!-- Slide içerik metinleri -->
  <?php if ($sliders): ?>
  <div class="cinema-content">
    <?php foreach ($sliders as $idx => $sl): ?>
    <div class="cinema-slide-text<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>">
      <?php if (tr_has($sl, 'subtitle')): ?>
        <span class="cinema-eyebrow"><?= h(tr_field($sl, 'subtitle')) ?></span>
      <?php endif; ?>
      <h2 class="cinema-title"><?= h(tr_field($sl, 'title')) ?></h2>
      <?php if (tr_has($sl, 'description')): ?>
        <p class="cinema-desc"><?= h(tr_field($sl, 'description')) ?></p>
      <?php endif; ?>
      <?php if (!empty($sl['link_url']) && tr_has($sl, 'link_text')): ?>
        <a href="<?= h(url_lang($sl['link_url'])) ?>" class="cinema-cta"><?= h(tr_field($sl, 'link_text')) ?> <span>→</span></a>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Sol/Sağ Ok butonları (Limak'taki minimalist kutusuz oklar) -->
  <?php if (count($sliders) > 1): ?>
  <button class="cinema-arrow prev" id="cinemaPrev" aria-label="Önceki">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><polyline points="15 18 9 12 15 6"/></svg>
  </button>
  <button class="cinema-arrow next" id="cinemaNext" aria-label="Sonraki">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><polyline points="9 18 15 12 9 6"/></svg>
  </button>
  <?php endif; ?>

  <!-- Alt: Scroll Down dairesel ok (Limak'taki) -->
  <a href="#urun-gruplarimiz" class="cinema-scroll" aria-label="Aşağı kaydır">
    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
      <circle cx="20" cy="20" r="19" stroke="currentColor" stroke-width="1" opacity=".55"/>
      <polyline points="14 17 20 23 26 17" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </svg>
  </a>

  <!-- Alt nokta nav (Limak'taki ince çubuklar) -->
  <?php if (count($sliders) > 1): ?>
  <div class="cinema-dots">
    <?php foreach ($sliders as $idx => $sl): ?>
      <button class="cinema-dot<?= $idx === 0 ? ' active' : '' ?>" data-index="<?= $idx ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<script>
(function(){
  const root = document.getElementById('heroCinema');
  if (!root) return;
  const slides = root.querySelectorAll('.cinema-slide');
  const texts  = root.querySelectorAll('.cinema-slide-text');
  const dots   = root.querySelectorAll('.cinema-dot');
  const total  = slides.length;
  if (total < 2) return;
  let idx = 0, timer = null;
  function go(n){
    idx = (n + total) % total;
    slides.forEach((s,i) => s.classList.toggle('active', i === idx));
    texts.forEach((s,i) => s.classList.toggle('active', i === idx));
    dots.forEach((d,i) => d.classList.toggle('active', i === idx));
  }
  function next(){ go(idx + 1); }
  function prev(){ go(idx - 1); }
  function start(){ stop(); timer = setInterval(next, 7000); }
  function stop(){ if (timer) { clearInterval(timer); timer = null; } }
  document.getElementById('cinemaPrev')?.addEventListener('click', () => { prev(); start(); });
  document.getElementById('cinemaNext')?.addEventListener('click', () => { next(); start(); });
  dots.forEach(d => d.addEventListener('click', e => { go(+e.currentTarget.dataset.index); start(); }));
  let xStart = null;
  root.addEventListener('touchstart', e => xStart = e.touches[0].clientX);
  root.addEventListener('touchend', e => {
    if (xStart === null) return;
    const dx = e.changedTouches[0].clientX - xStart;
    if (Math.abs(dx) > 50) { dx > 0 ? prev() : next(); start(); }
    xStart = null;
  });
  start();
})();
</script>

<!-- ═══════════════════════════════════════════════
     v1.0.37 — KRALİYET AİLESİ ANASAYFA (HERO sonrası)
     hp-* prefix ile sayfa-özel inline style
     ═══════════════════════════════════════════════ -->

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.hp-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --navy-3:#143672;
  --gold:#c9a86b;
  --gold-light:#e0c48a;
  --gold-dark:#a88a4a;
  --gold-deep:#8b6f35;  /* WCAG AA: 4.75:1 — eyebrow/link kullanımı için */
  --red:#c8102e;
  --red-dark:#a00d24;
  --paper:#fafaf7;
  --paper-2:#f3f1ec;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
}

/* ═══ ORTAK SECTION HEAD ═══ */
.hp-section{
  padding:90px 0;
  background:#fff;
}
.hp-section.alt{background:var(--paper)}
.hp-section.dark{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  position:relative;overflow:hidden;
}
.hp-section.dark::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(ellipse at 50% 50%, rgba(201,168,107,.08) 0%, transparent 60%);
  pointer-events:none;
}

.hp-head{
  text-align:center;
  max-width:760px;
  margin:0 auto 60px;
  position:relative;z-index:2;
}
.hp-head.left{text-align:left;margin-left:0}
.hp-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;
  color:var(--red);
  margin-bottom:18px;
}
.hp-eyebrow::before,
.hp-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--red);
}
.hp-head.left .hp-eyebrow::before{display:none}
.hp-section.dark .hp-eyebrow{color:var(--gold)}
.hp-section.dark .hp-eyebrow::before,
.hp-section.dark .hp-eyebrow::after{background:var(--gold)}

.hp-head h2{
  font-family:var(--serif);
  font-size:clamp(34px, 4vw, 54px);
  font-weight:500;
  line-height:1.1;
  letter-spacing:-1px;
  margin:0 0 18px;
  color:var(--navy);
}
.hp-head h2 em{
  font-style:italic;
  color:var(--red);
}
.hp-section.dark .hp-head h2{color:#fff}
.hp-section.dark .hp-head h2 em{color:var(--gold)}
.hp-head p{
  font-family:var(--sans);
  font-size:15px;
  line-height:1.7;
  color:#5a5a5a;
  margin:0;
}
.hp-section.dark .hp-head p{color:rgba(255,255,255,.7)}


/* ═══ ÜRÜN GRUPLARI — ROYAL CARD GRID ═══ */
.hp-products{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  border:1px solid var(--line);
  background:#fff;
}
@media (max-width:900px){.hp-products{grid-template-columns:repeat(2,1fr)}}
@media (max-width:520px){.hp-products{grid-template-columns:1fr}}

.hp-product{
  position:relative;
  text-decoration:none;
  color:inherit;
  background:#fff;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  display:flex;
  flex-direction:column;
  overflow:hidden;
  transition:.3s;
  min-height:380px;
}
.hp-product:hover{
  background:var(--paper);
  z-index:2;
  box-shadow:0 18px 42px rgba(5,13,36,.12);
  transform:translateY(-4px);
}

.hp-product-img{
  position:relative;
  width:100%;
  height:230px;
  overflow:hidden;
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
}
.hp-product-img img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.6s ease;
}
.hp-product:hover .hp-product-img img{
  transform:scale(1.06);
}
.hp-product-img-placeholder{
  position:absolute;
  inset:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);
  font-size:90px;
  font-weight:500;
  color:rgba(201,168,107,.18);
}
.hp-product-num{
  position:absolute;
  top:14px;left:18px;
  font-family:var(--serif);
  font-size:13px;
  font-style:italic;
  font-weight:600;
  color:var(--gold);
  background:rgba(5,13,36,.65);
  padding:5px 12px;
  border:1px solid var(--gold);
  backdrop-filter:blur(6px);
  z-index:2;
}

.hp-product-body{
  padding:26px 26px 24px;
  display:flex;
  flex-direction:column;
  flex:1;
  position:relative;
}
.hp-product-body::before{
  content:'';
  position:absolute;
  top:0;left:26px;
  width:40px;height:3px;
  background:var(--red);
  transition:width .3s ease;
}
.hp-product:hover .hp-product-body::before{width:80px}
.hp-product h3{
  font-family:var(--serif);
  font-size:26px;
  font-weight:600;
  letter-spacing:-.3px;
  line-height:1.2;
  margin:0 0 10px;
  color:var(--navy);
}
.hp-product p{
  font-family:var(--sans);
  font-size:13.5px;
  line-height:1.65;
  color:#5a5a5a;
  margin:0 0 18px;
  flex:1;
}
.hp-product-link{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:1.8px;
  text-transform:uppercase;
  color:var(--red);
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding-top:14px;
  border-top:1px solid var(--line);
  transition:.2s;
}
.hp-product-link span{
  transition:transform .2s;
}
.hp-product:hover .hp-product-link{color:var(--navy)}
.hp-product:hover .hp-product-link span{transform:translateX(6px)}


/* ═══ HİZMETLER — ALTERNATING IMAGE+CONTENT ═══ */
.hp-services{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  background:#fff;
  border:1px solid var(--line);
  border-top:4px solid var(--gold);
}
@media (max-width:900px){.hp-services{grid-template-columns:1fr}}

.hp-service{
  position:relative;
  text-decoration:none;
  color:inherit;
  border-right:1px solid var(--line);
  background:#fff;
  display:flex;
  flex-direction:column;
  transition:.25s;
  overflow:hidden;
}
.hp-service:last-child{border-right:0}
@media (max-width:900px){
  .hp-service{border-right:0;border-bottom:1px solid var(--line)}
  .hp-service:last-child{border-bottom:0}
}
.hp-service:hover{
  background:var(--paper);
}
.hp-service-img{
  width:100%;
  height:240px;
  position:relative;
  overflow:hidden;
  background:var(--navy-2);
}
.hp-service-img img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.6s;
  filter:brightness(.92);
}
.hp-service:hover .hp-service-img img{
  transform:scale(1.05);
  filter:brightness(1);
}
.hp-service-img::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(180deg, transparent 0%, rgba(5,13,36,.55) 100%);
  z-index:2;
  pointer-events:none;
}
.hp-service-num{
  position:absolute;
  top:18px;left:18px;
  font-family:var(--serif);
  font-style:italic;
  font-weight:600;
  font-size:13px;
  color:var(--gold);
  background:rgba(5,13,36,.6);
  border:1px solid var(--gold);
  padding:5px 12px;
  z-index:3;
  backdrop-filter:blur(6px);
}
.hp-service-body{
  padding:30px 28px 28px;
  display:flex;flex-direction:column;flex:1;
}
.hp-service h3{
  font-family:var(--serif);
  font-size:26px;
  font-weight:600;
  letter-spacing:-.3px;
  line-height:1.2;
  color:var(--navy);
  margin:0 0 12px;
}
.hp-service p{
  font-family:var(--sans);
  font-size:13.5px;
  line-height:1.65;
  color:#5a5a5a;
  margin:0 0 20px;
  flex:1;
}
.hp-service-link{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:1.8px;
  text-transform:uppercase;
  color:var(--gold-deep);  /* v1.0.70: WCAG AA kontrast 4.75:1 (eski var(--gold) 2.26:1) */
  display:inline-flex;
  align-items:center;gap:6px;
  padding-top:14px;
  border-top:1px solid var(--line);
}
.hp-service:hover .hp-service-link{color:var(--red)}
.hp-service-link span{transition:transform .2s}
.hp-service:hover .hp-service-link span{transform:translateX(6px)}


/* ═══ DEĞERLERİMİZ — Royal three columns with serif numbers ═══ */
.hp-values{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  background:#fff;
  border:1px solid var(--line);
}
@media (max-width:900px){.hp-values{grid-template-columns:1fr}}

.hp-value{
  padding:50px 40px;
  border-right:1px solid var(--line);
  position:relative;
  transition:.25s;
}
.hp-value:last-child{border-right:0}
@media (max-width:900px){
  .hp-value{border-right:0;border-bottom:1px solid var(--line)}
  .hp-value:last-child{border-bottom:0}
}
.hp-value:hover{background:var(--paper)}
.hp-value::before{
  content:'';
  position:absolute;
  top:0;left:0;
  width:100%;height:4px;
  background:var(--gold);
  transform:scaleX(0);
  transform-origin:left;
  transition:transform .35s ease;
}
.hp-value:hover::before{transform:scaleX(1)}
.hp-value-num{
  font-family:var(--serif);
  font-size:64px;
  font-weight:500;
  font-style:italic;
  color:var(--red);
  line-height:1;
  letter-spacing:-2px;
  margin-bottom:24px;
  display:flex;
  align-items:center;
  gap:14px;
}
.hp-value-num::after{
  content:'';
  flex:1;
  height:1px;
  background:var(--line);
}
.hp-value h3{
  font-family:var(--serif);
  font-size:28px;
  font-weight:600;
  letter-spacing:-.3px;
  line-height:1.2;
  margin:0 0 14px;
  color:var(--navy);
}
.hp-value p{
  font-family:var(--sans);
  font-size:14px;
  line-height:1.7;
  color:#3a3a3a;
  margin:0 0 22px;
}
.hp-value-link{
  font-family:var(--sans);
  font-size:10.5px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--navy);
  display:inline-flex;
  align-items:center;gap:6px;
  text-decoration:none;
  padding-bottom:4px;
  border-bottom:1.5px solid var(--gold);
  transition:.2s;
}
.hp-value-link:hover{color:var(--red);border-color:var(--red)}
.hp-value-link span{transition:transform .2s}
.hp-value-link:hover span{transform:translateX(4px)}


/* ═══ İSTATİSTİKLER — DARK ROYAL BAR ═══ */
.hp-stats{
  display:grid;
  grid-template-columns:repeat(5, 1fr);
  gap:0;
  position:relative;z-index:2;
  border-top:1px solid rgba(255,255,255,.1);
  border-bottom:1px solid rgba(255,255,255,.1);
}
@media (max-width:900px){.hp-stats{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.hp-stats{grid-template-columns:1fr}}
.hp-stat{
  padding:50px 30px;
  text-align:center;
  border-right:1px solid rgba(255,255,255,.1);
  transition:.25s;
}
.hp-stat:last-child{border-right:0}
@media (max-width:900px){
  .hp-stat{border-right:0;border-bottom:1px solid rgba(255,255,255,.06)}
  .hp-stat:last-child{border-bottom:0}
}
.hp-stat:hover{background:rgba(255,255,255,.02)}
.hp-stat-num{
  font-family:var(--serif);
  font-size:clamp(48px, 5vw, 72px);
  font-weight:500;
  color:var(--gold);
  line-height:1;
  letter-spacing:-2px;
  margin-bottom:14px;
}
.hp-stat-label{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:2.2px;
  text-transform:uppercase;
  color:rgba(255,255,255,.65);
}


/* ═══ HABERLER — EDITORIAL CARDS ═══ */
.hp-news-head{
  display:flex;justify-content:space-between;align-items:flex-end;
  flex-wrap:wrap;gap:20px;
  margin-bottom:50px;
  padding-bottom:24px;
  border-bottom:2px solid var(--navy);
}
.hp-news-head h2{
  font-family:var(--serif);
  font-size:clamp(32px, 3.6vw, 46px);
  font-weight:500;letter-spacing:-.7px;
  margin:0;color:var(--navy);line-height:1.1;
}
.hp-news-head h2 em{font-style:italic;color:var(--red)}
.hp-news-allbtn{
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;
  color:var(--navy);
  padding:10px 22px;
  border:1.5px solid var(--navy);
  transition:.2s;
}
.hp-news-allbtn:hover{
  background:var(--navy);color:#fff;
}

.hp-news-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:24px;
}
@media (max-width:900px){.hp-news-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:520px){.hp-news-grid{grid-template-columns:1fr}}

.hp-news{
  text-decoration:none;color:inherit;
  background:#fff;
  border:1px solid var(--line);
  display:flex;
  flex-direction:column;
  transition:.25s;
  overflow:hidden;
}
.hp-news:hover{
  border-color:var(--gold);
  transform:translateY(-3px);
  box-shadow:0 14px 30px rgba(5,13,36,.1);
}
.hp-news-thumb{
  width:100%;
  height:180px;
  position:relative;
  overflow:hidden;
  background:var(--navy-2);
}
.hp-news-thumb img{
  width:100%;height:100%;
  object-fit:cover;
  transition:.5s;
}
.hp-news:hover .hp-news-thumb img{transform:scale(1.05)}
.hp-news-body{
  padding:22px 22px 24px;
  flex:1;
  display:flex;
  flex-direction:column;
}
.hp-news-date{
  font-family:var(--sans);
  font-size:10px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--gold-deep);  /* v1.0.70: WCAG AA kontrast 4.75:1 */
  margin-bottom:10px;
}
.hp-news h3{
  font-family:var(--serif);
  font-size:20px;
  font-weight:600;
  line-height:1.25;
  letter-spacing:-.2px;
  color:var(--navy);
  margin:0 0 14px;
  flex:1;
}
.hp-news-link{
  font-family:var(--sans);
  font-size:10.5px;
  font-weight:700;
  letter-spacing:1.8px;
  text-transform:uppercase;
  color:var(--red);
  display:inline-flex;align-items:center;gap:6px;
  padding-top:12px;
  border-top:1px solid var(--line);
}
.hp-news-link span{transition:transform .2s}
.hp-news:hover .hp-news-link span{transform:translateX(4px)}


/* ═══ ÇÖZÜM ORTAKLARI — Premium Logo Wall ═══ */
.hp-partners-wrap{
  background:#fff;
  border:1px solid var(--line);
}
.hp-partners-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
}
@media (max-width:900px){.hp-partners-grid{grid-template-columns:repeat(3,1fr)}}
@media (max-width:520px){.hp-partners-grid{grid-template-columns:repeat(2,1fr)}}

.hp-partner{
  padding:36px 28px;
  text-align:center;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  position:relative;
  transition:.25s;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  min-height:160px;
}
.hp-partner:nth-child(4n){border-right:0}
@media (max-width:900px){
  .hp-partner{border-right:1px solid var(--line)}
  .hp-partner:nth-child(4n){border-right:1px solid var(--line)}
  .hp-partner:nth-child(3n){border-right:0}
}
@media (max-width:520px){
  .hp-partner:nth-child(3n){border-right:1px solid var(--line)}
  .hp-partner:nth-child(2n){border-right:0}
}
.hp-partner:hover{
  background:var(--paper);
  z-index:2;
}
.hp-partner-logo{
  max-height:60px;
  max-width:140px;
  width:auto;
  height:auto;
  object-fit:contain;
  filter:grayscale(100%) opacity(.65);
  transition:.3s;
}
.hp-partner:hover .hp-partner-logo{
  filter:grayscale(0%) opacity(1);
  transform:scale(1.05);
}
.hp-partner-initial{
  width:64px;height:64px;
  background:var(--navy);
  color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);
  font-size:28px;font-weight:600;
}

.hp-partners-foot{
  padding:24px 30px;
  border-top:2px solid var(--gold);
  background:var(--paper);
  text-align:center;
}
.hp-partners-foot a{
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--navy);
  text-decoration:none;
  display:inline-flex;align-items:center;gap:8px;
  transition:.2s;
}
.hp-partners-foot a:hover{color:var(--red)}
.hp-partners-foot a span{transition:transform .2s}
.hp-partners-foot a:hover span{transform:translateX(4px)}


/* ═══ CTA BANNER ═══ */
.hp-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 50%, var(--navy-3) 100%);
  color:#fff;
  padding:90px 0;
  position:relative;
  overflow:hidden;
  border-top:4px solid var(--gold);
}
.hp-cta::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(
    -45deg, transparent 0, transparent 4px,
    rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.hp-cta::after{
  content:'TM';
  position:absolute;
  bottom:-80px;right:-30px;
  font-family:var(--serif);
  font-size:300px;font-weight:500;
  color:rgba(201,168,107,.06);
  letter-spacing:-15px;line-height:1;
  pointer-events:none;
}
.hp-cta-inner{
  position:relative;z-index:2;
  display:grid;
  grid-template-columns:1.4fr 1fr;
  gap:60px;
  align-items:center;
}
@media (max-width:900px){.hp-cta-inner{grid-template-columns:1fr;text-align:center;gap:40px}}
.hp-cta h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4.2vw, 56px);
  font-weight:500;
  font-style:italic;
  letter-spacing:-1px;
  line-height:1.1;
  margin:0 0 16px;
  color:#fff;
}
.hp-cta h2 strong{
  font-style:normal;
  color:var(--gold);
  font-weight:600;
}
.hp-cta-lead{
  font-family:var(--sans);
  font-size:15px;
  line-height:1.7;
  color:rgba(255,255,255,.75);
  margin:0;
  max-width:560px;
}
@media (max-width:900px){.hp-cta-lead{margin:0 auto}}
.hp-cta-actions{
  display:flex;
  gap:14px;
  flex-wrap:wrap;
  justify-content:flex-end;
}
@media (max-width:900px){.hp-cta-actions{justify-content:center}}
.hp-cta-btn{
  display:inline-block;
  padding:18px 38px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;
  transition:.2s;
  border:1px solid transparent;
}
.hp-cta-btn-primary{
  background:var(--red);color:#fff;border-color:var(--red);
}
.hp-cta-btn-primary:hover{
  background:var(--red-dark);border-color:var(--red-dark);
  transform:translateY(-2px);
  box-shadow:0 10px 22px rgba(200,16,46,.4);
}
.hp-cta-btn-ghost{
  background:transparent;color:var(--gold);border-color:var(--gold);
}
.hp-cta-btn-ghost:hover{
  background:var(--gold);color:var(--navy);border-color:var(--gold);
  transform:translateY(-2px);
}
</style>

<div class="hp-page">

  <!-- ═══ ÜRÜN GRUPLARI ═══ -->
  <section class="hp-section">
    <div class="container">
      <div class="hp-head">
        <div class="hp-eyebrow"><?= h(t('home.product_groups', 'Ürün Gruplarımız')) ?></div>
        <h2><?= count($cats) ?> <?= t('home.products_h2', 'Ana Grupta <em>Geniş Yelpaze</em>') ?></h2>
        <p><?= h(t('home.products_section_lead', 'Sanayi, inşaat ve özel proje gereksinimlerine yönelik tedarik ve üretim hizmeti sunuyoruz. Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün lider üreticilerinden doğrudan tedarik güvencesiyle.')) ?></p>
      </div>

      <div class="hp-products">
        <?php foreach ($cats as $i => $c): ?>
        <a class="hp-product" href="<?= h(url_lang('kategori.php?slug=' . $c['slug'])) ?>">
          <div class="hp-product-img">
            <span class="hp-product-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?> —</span>
            <?php if (!empty($c['image'])): ?>
              <?= picture_tag($c['image'], [
                  'alt' => tr_field($c, 'name'),
                  'loading' => 'lazy',
                  'width' => 422,
                  'height' => 282,
              ]) ?>
            <?php else: ?>
              <div class="hp-product-img-placeholder"><?= h(mb_strtoupper(mb_substr($c['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <?php endif; ?>
          </div>
          <div class="hp-product-body">
            <h3><?= h(tr_field($c, 'name')) ?></h3>
            <p><?= h(tr_field($c, 'short_desc')) ?></p>
            <span class="hp-product-link"><?= h(t('home.explore_products', 'Ürünleri İncele')) ?> <span>→</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>


  <!-- ═══ HİZMETLER ═══ -->
  <?php if ($services): ?>
  <section class="hp-section alt">
    <div class="container">
      <div class="hp-head">
        <div class="hp-eyebrow"><?= h(t('bc.industrial_capabilities', 'Endüstriyel Yetkinlikler')) ?></div>
        <h2><?= t('home.services_h2', 'Tedarikten <em>Üretime</em><br>Uçtan Uca Çözüm') ?></h2>
        <p><?= h(t('home.services_section_lead', 'Stoklu satışın yanı sıra atölye yetkinliklerimizle proje tabanlı üretim hizmetleri sunuyoruz. Lazer kesimden oksijen kesime, dekoratif sac üretiminden CNC işleme kadar.')) ?></p>
      </div>

      <div class="hp-services">
        <?php foreach (array_slice($services, 0, 3) as $i => $s): ?>
        <a class="hp-service" href="<?= h(url_lang('hizmet.php?slug=' . $s['slug'])) ?>">
          <div class="hp-service-img">
            <span class="hp-service-num">— 0<?= $i+1 ?> —</span>
            <?php if (!empty($s['image'])): ?>
              <?= picture_tag($s['image'], [
                  'alt' => tr_field($s, 'title'),
                  'loading' => 'lazy',
                  'width' => 422,
                  'height' => 282,
              ]) ?>
            <?php endif; ?>
          </div>
          <div class="hp-service-body">
            <h3><?= h(tr_field($s, 'title')) ?></h3>
            <p><?= h(tr_field($s, 'short_desc')) ?></p>
            <span class="hp-service-link"><?= h(t('home.explore_detail', 'Detaylı İncele')) ?> <span>→</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- ═══ KURUMSAL DEĞERLERİMİZ ═══ -->
  <section class="hp-section">
    <div class="container">
      <div class="hp-head">
        <div class="hp-eyebrow"><?= h(t('home.corporate_values_eyebrow', 'Kurumsal Değerlerimiz')) ?></div>
        <h2><?= t('home.values_h2', 'İlke, Kalite ve <em>Güvenle</em> Çalışıyoruz') ?></h2>
        <p><?= h(t('home.values_lead', "2005'ten bu yana üç temel ilke üzerinde yükseliyoruz: kaliteden ödün vermeden, operasyonel mükemmellikle ve müşteri odaklı bir yaklaşımla.")) ?></p>
      </div>

      <div class="hp-values">
        <div class="hp-value">
          <div class="hp-value-num">01</div>
          <h3><?= h(t('home.value_quality', 'Kalite ve Standart')) ?></h3>
          <p><?= h(t('home.value_quality_desc', "Türkiye'nin lider çelik üreticilerinden doğrudan tedarik ettiğimiz ürünler, uluslararası kalite standartlarındadır. Sertifikalı, izlenebilir, üretici onaylı.")) ?></p>
          <a href="<?= h(url_lang('partnerler.php')) ?>" class="hp-value-link"><?= h(t('header.menu.partners', 'Çözüm Ortaklarımız')) ?> <span>→</span></a>
        </div>
        <div class="hp-value">
          <div class="hp-value-num">02</div>
          <h3><?= h(t('home.value_operational', 'Operasyonel Mükemmellik')) ?></h3>
          <p><?= h(t('home.value_operational_desc', 'Geniş stoğumuz, lazer ve oksijen kesim atölyemiz, aynı gün üretim seçeneğimiz ve 7/24 sevkiyat ağımızla zaman, teslimatımızın ayrılmaz bir parçasıdır.')) ?></p>
          <a href="<?= h(url_lang('hizmetler.php')) ?>" class="hp-value-link"><?= h(t('home.our_capabilities', 'Yetkinliklerimiz')) ?> <span>→</span></a>
        </div>
        <div class="hp-value">
          <div class="hp-value-num">03</div>
          <h3><?= h(t('home.value_customer', 'Müşteri Odaklılık')) ?></h3>
          <p><?= t('home.value_customer_desc', '&quot;Ticaret ile Bitmeyen Dostluk&quot; felsefemizle her müşteriyi bir iş ortağı olarak görüyor; uzun vadeli ve güvene dayalı ilişkiler kuruyoruz.') ?></p>
          <a href="<?= h(url_lang('sadakat.php')) ?>" class="hp-value-link"><?= h(t('header.menu.loyalty', 'Sadakat Programı')) ?> <span>→</span></a>
        </div>
      </div>
    </div>
  </section>


  <!-- ═══ İSTATİSTİKLER (DARK BAND) ═══ -->
  <section class="hp-section dark" style="padding:0">
    <div class="container">
      <div class="hp-stats">
        <div class="hp-stat">
          <div class="hp-stat-num"><?= h(t_setting('stat_year', '20+')) ?></div>
          <div class="hp-stat-label"><?= h(t_setting('stat_year_label', 'Yıllık Tecrübe')) ?></div>
        </div>
        <div class="hp-stat">
          <div class="hp-stat-num"><?= h(t_setting('stat_products', '1.000+')) ?></div>
          <div class="hp-stat-label"><?= h(t_setting('stat_products_label', 'Ürün Çeşidi')) ?></div>
        </div>
        <div class="hp-stat">
          <div class="hp-stat-num"><?= h(t_setting('stat_customers', '1.000+')) ?></div>
          <div class="hp-stat-label"><?= h(t_setting('stat_customers_label', 'Mutlu Müşteri')) ?></div>
        </div>
        <div class="hp-stat">
          <div class="hp-stat-num"><?= h(t_setting('stat_orders', '3.436')) ?></div>
          <div class="hp-stat-label"><?= h(t_setting('stat_orders_label', 'Ürün Siparişi')) ?></div>
        </div>
        <div class="hp-stat">
          <div class="hp-stat-num"><?= h(t_setting('stat_delivery', '7/24')) ?></div>
          <div class="hp-stat-label"><?= h(t_setting('stat_delivery_label', 'Sevkiyat Hizmeti')) ?></div>
        </div>
      </div>
    </div>
  </section>


  <!-- ═══ HABERLER ═══ -->
  <?php if ($news): ?>
  <section class="hp-section">
    <div class="container">
      <div class="hp-news-head">
        <div>
          <div class="hp-eyebrow" style="margin-bottom:10px"><?= h(t('header.menu.news', 'Haberler & Basın')) ?></div>
          <h2><?= t('home.news_h2', 'Sektörel <em>Gelişmeler</em> ve Duyurular') ?></h2>
        </div>
        <a href="<?= h(url_lang('blog.php')) ?>" class="hp-news-allbtn"><?= h(t('home.view_all_news', 'Tümünü İncele')) ?> →</a>
      </div>

      <div class="hp-news-grid">
        <?php foreach ($news as $n): ?>
        <a class="hp-news" href="<?= h(url_lang('blog-detay.php?slug=' . $n['slug'])) ?>">
          <?php if (!empty($n['cover_image'])): ?>
          <div class="hp-news-thumb">
            <?= picture_tag($n['cover_image'], [
                'alt' => tr_field($n, 'title'),
                'loading' => 'lazy',
                'width' => 380,
                'height' => 240,
            ]) ?>
          </div>
          <?php endif; ?>
          <div class="hp-news-body">
            <span class="hp-news-date"><?= h(tr_date($n['published_at'])) ?></span>
            <h3><?= h(tr_field($n, 'title')) ?></h3>
            <span class="hp-news-link"><?= h(t('btn.read_more', 'Devamını Oku')) ?> <span>→</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- ═══ ÇÖZÜM ORTAKLARI ═══ -->
  <?php if ($partners): ?>
  <section class="hp-section alt">
    <div class="container">
      <div class="hp-head">
        <div class="hp-eyebrow"><?= h(t('home.partners', 'Çözüm Ortaklarımız')) ?></div>
        <h2><?= t('home.partners_h2', "Türkiye'nin <em>Çelik Devleri</em><br>Tedarik Ortaklarımız") ?></h2>
        <p><?= h(t('home.partners_lead', 'Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün önde gelen entegre çelik üretim tesislerinden doğrudan ürün tedarik ediyoruz.')) ?></p>
      </div>

      <div class="hp-partners-wrap">
        <div class="hp-partners-grid">
          <?php foreach ($partners as $p): ?>
          <div class="hp-partner" title="<?= h($p['name']) ?>">
            <?php if (!empty($p['logo'])): ?>
              <img class="hp-partner-logo" src="<?= h(img_url($p['logo'])) ?>" alt="<?= h(tr_field($p, 'name')) ?>" loading="lazy">
            <?php else: ?>
              <div class="hp-partner-initial"><?= h(mb_strtoupper(mb_substr($p['name'], 0, 2, 'UTF-8'), 'UTF-8')) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="hp-partners-foot">
          <a href="<?= h(url_lang('partnerler.php')) ?>"><?= h(t('home.view_all_partners', 'Tüm Çözüm Ortaklarını İncele')) ?> <span>→</span></a>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- ═══ CTA BANNER ═══ -->
  <section class="hp-cta">
    <div class="container">
      <div class="hp-cta-inner">
        <div>
          <div class="hp-eyebrow" style="margin-bottom:14px"><?= h(t('btn.contact_us', 'Bize Ulaşın')) ?></div>
          <h2><?= t('home.cta_title', 'Projeniz için <strong>özel teklif</strong> almak ister misiniz?') ?></h2>
          <p class="hp-cta-lead"><?= h(t('home.cta_lead', 'Uzman satış ekibimiz, ihtiyacınıza özel ürün ve sevkiyat planlamasını en kısa sürede hazırlayıp size sunar.')) ?></p>
        </div>
        <div class="hp-cta-actions">
          <a href="<?= h(url_lang('iletisim.php')) ?>" class="hp-cta-btn hp-cta-btn-primary"><?= h(t('btn.request_quote', 'Teklif İste')) ?></a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp'), 'Merhaba, ürün/teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="hp-cta-btn hp-cta-btn-ghost">WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
