<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$s = row("SELECT * FROM tm_services WHERE slug=? AND is_active=1", [$slug]);

if (!$s) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Features JSON parse
$features = [];
if (!empty($s['features'])) {
    $tmp = json_decode($s['features'], true);
    if (is_array($tmp)) $features = $tmp;
}

// Specs JSON parse (yeni — teknik özellik tablosu)
$specs = [];
if (!empty($s['specs'])) {
    $tmp = json_decode($s['specs'], true);
    if (is_array($tmp)) $specs = $tmp;
}

// Diğer hizmetler
$otherServices = all("SELECT slug,title,image,short_desc FROM tm_services WHERE is_active=1 AND id<>? ORDER BY sort_order LIMIT 3", [$s['id']]);

// Hizmet tipi tespiti — eyebrow ve ikon için
$svcType = 'general';
$svcEyebrow = 'Endüstriyel Yetkinlik';
$svcIcon = '⚙';
$lc = mb_strtolower($slug, 'UTF-8');
if (str_contains($lc, 'lazer')) {
    $svcType = 'laser';
    $svcEyebrow = 'Hassas Kesim Teknolojisi';
    $svcIcon = '⚡';
} elseif (str_contains($lc, 'oksijen') || str_contains($lc, 'plazma')) {
    $svcType = 'oxygen';
    $svcEyebrow = 'Kalın Levha Kesimi';
    $svcIcon = '🔥';
} elseif (str_contains($lc, 'dekoratif')) {
    $svcType = 'decorative';
    $svcEyebrow = 'Mimari Sac Üretimi';
    $svcIcon = '✦';
} elseif (str_contains($lc, 'bukum') || str_contains($lc, 'abkant')) {
    $svcType = 'bending';
    $svcEyebrow = 'Sac Şekillendirme';
    $svcIcon = '∠';
}

$pageTitle = $s['title'];
$metaDesc  = $s['meta_desc'] ?? $s['short_desc'] ?? '';
if (!$metaDesc && !empty($s['description'])) {
    $metaDesc = mb_substr(strip_tags($s['description']), 0, 160, 'UTF-8');
}

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

.hz-page{
  --navy:#050d24;--navy-2:#0c1e44;--navy-3:#143672;
  --gold:#c9a86b;--gold-light:#e0c48a;--gold-dark:#a88a4a;
  --red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--paper-2:#f3f1ec;--line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  --mono:'JetBrains Mono', monospace;
  background:var(--paper);
}

/* HERO */
.hz-hero{
  position:relative;overflow:hidden;
  color:#fff;
  padding:140px 0 120px;
  background:linear-gradient(135deg, rgba(5,13,36,.92) 0%, rgba(20,54,114,.82) 100%);
  border-bottom:4px solid var(--gold);
}
.hz-hero::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
<?php if (!empty($s['image'])): ?>
.hz-hero{
  background:
    linear-gradient(135deg, rgba(5,13,36,.85) 0%, rgba(20,54,114,.78) 100%),
    url('<?= h(img_url($s['image'])) ?>') center/cover no-repeat;
}
<?php endif; ?>
.hz-hero::after{
  content:'<?= $svcIcon ?>';
  position:absolute;
  bottom:-100px;right:-30px;
  font-size:480px;line-height:1;
  color:rgba(201,168,107,.05);
  pointer-events:none;
  font-family:var(--serif);
}
.hz-hero .container{position:relative;z-index:2;text-align:center}

.hz-hero-icon{
  display:inline-flex;align-items:center;justify-content:center;
  width:90px;height:90px;
  margin:0 auto 26px;
  background:rgba(201,168,107,.1);
  border:2px solid var(--gold);
  font-size:42px;line-height:1;
  font-family:var(--serif);font-weight:500;
  color:var(--gold);
}
.hz-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:24px;
}
.hz-hero-eyebrow::before,
.hz-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.hz-hero h1{
  font-family:var(--serif);
  font-size:clamp(46px, 7vw, 84px);font-weight:500;
  line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 22px;color:#fff;
}
.hz-hero h1 em{font-style:italic;color:var(--gold)}
.hz-hero-lead{
  font-family:var(--sans);
  font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:720px;margin:0 auto;
}

/* HERO STATS */
.hz-hero-stats{
  display:flex;justify-content:center;gap:40px;flex-wrap:wrap;
  margin-top:42px;padding-top:30px;
  border-top:1px solid rgba(255,255,255,.12);
}
.hz-stat{text-align:center}
.hz-stat strong{
  display:block;font-family:var(--serif);
  font-size:34px;font-weight:500;
  color:var(--gold);line-height:1;letter-spacing:-.5px;
}
.hz-stat span{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:1.8px;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-top:7px;display:block;
}

/* BREADCRUMB */
.hz-breadcrumb-strip{
  background:#fff;border-bottom:1px solid var(--line);padding:18px 0;
}
.hz-breadcrumb{
  font-family:var(--sans);font-size:12.5px;
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.hz-breadcrumb a{color:#5a5a5a;text-decoration:none;font-weight:500}
.hz-breadcrumb a:hover{color:var(--red)}
.hz-breadcrumb .sep{color:var(--gold)}
.hz-breadcrumb .current{
  color:var(--navy);font-weight:600;font-style:italic;
  font-family:var(--serif);font-size:14.5px;
}

/* MAIN GRID */
.hz-main{padding:90px 0}
.hz-grid{
  display:grid;grid-template-columns:1.5fr 1fr;gap:60px;align-items:start;
}
@media (max-width:1000px){.hz-grid{grid-template-columns:1fr;gap:40px}}

/* CONTENT */
.hz-content{
  background:#fff;border:1px solid var(--line);
  border-top:4px solid var(--gold);
  padding:50px 60px;position:relative;
}
@media (max-width:768px){.hz-content{padding:30px 24px}}
.hz-content::before{
  content:'';position:absolute;
  top:-4px;left:50px;width:80px;height:4px;
  background:var(--red);
}

.hz-content h2{
  font-family:var(--serif);font-size:28px;font-weight:600;
  color:var(--navy);letter-spacing:-.3px;line-height:1.2;
  margin:32px 0 14px;padding-left:22px;
  border-left:3px solid var(--gold);
}
.hz-content h2:first-child{margin-top:0}
.hz-content h3{
  font-family:var(--serif);font-size:22px;font-weight:600;
  color:var(--navy);letter-spacing:-.2px;line-height:1.25;
  margin:26px 0 12px;padding-left:18px;
  border-left:3px solid var(--red);
}
.hz-content h4{
  font-family:var(--sans);font-size:13.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;color:var(--navy);
  margin:22px 0 10px;padding-bottom:6px;
  border-bottom:1px solid var(--line);
}

.hz-content p{
  font-family:var(--sans);font-size:15px;line-height:1.8;
  color:#3a3a3a;margin:0 0 16px;
}
.hz-content > p:first-of-type{
  font-size:17px;color:var(--navy);font-weight:500;line-height:1.7;
}
.hz-content > p:first-of-type::first-letter{
  font-family:var(--serif);font-size:64px;font-weight:600;font-style:italic;
  color:var(--red);float:left;line-height:.95;
  margin:6px 12px 0 0;
}

.hz-content strong{color:var(--navy);font-weight:700}
.hz-content em{
  font-family:var(--serif);font-style:italic;color:var(--red);
  font-weight:500;
}
.hz-content a{
  color:var(--red);
  text-decoration:underline;text-decoration-color:var(--gold);
  text-underline-offset:3px;font-weight:600;
}
.hz-content a:hover{color:var(--red-dark)}

.hz-content ul,
.hz-content ol{margin:0 0 20px;padding:0;list-style:none}
.hz-content ul li,
.hz-content ol li{
  position:relative;padding:6px 0 6px 28px;
  font-family:var(--sans);font-size:14.5px;line-height:1.7;color:#3a3a3a;
}
.hz-content ul li::before{
  content:'';position:absolute;left:0;top:14px;
  width:8px;height:8px;background:var(--gold);transform:rotate(45deg);
}
.hz-content ol{counter-reset:hz-list}
.hz-content ol li{counter-increment:hz-list}
.hz-content ol li::before{
  content:counter(hz-list, decimal-leading-zero);
  position:absolute;left:0;top:6px;
  font-family:var(--serif);font-size:18px;font-style:italic;
  color:var(--red);font-weight:600;
}

.hz-content blockquote{
  margin:24px 0;padding:20px 26px;
  background:var(--paper);
  border-left:3px solid var(--gold);
  font-family:var(--serif);font-style:italic;font-size:17px;
  line-height:1.6;color:var(--navy-2);
}
.hz-content blockquote p{
  font-family:var(--serif);font-style:italic;font-size:17px;
  color:var(--navy-2);margin:0;
}

.hz-content table{
  width:100%;border-collapse:collapse;
  margin:24px 0;font-family:var(--sans);font-size:13.5px;
}
.hz-content table th,
.hz-content table td{
  padding:12px 14px;border:1px solid var(--line);
  text-align:left;vertical-align:top;
}
.hz-content table th{
  background:var(--navy);color:#fff;
  font-size:11px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;
}
.hz-content table tr:nth-child(even) td{background:var(--paper)}
.hz-content table td:first-child{
  font-family:var(--mono);font-weight:600;color:var(--navy);
}

/* SIDEBAR */
.hz-sidebar{position:sticky;top:100px}
@media (max-width:1000px){.hz-sidebar{position:static}}

.hz-side-card{
  background:#fff;border:1px solid var(--line);
  margin-bottom:18px;
}
.hz-side-card-head{
  padding:20px 24px;
  border-bottom:1px solid var(--line);
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
}
.hz-side-card-head .eyebrow{
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);margin-bottom:4px;
}
.hz-side-card-head h3{
  font-family:var(--serif);font-size:21px;font-weight:600;
  color:#fff;margin:0;line-height:1.2;
}
.hz-side-card-head h3 em{font-style:italic;color:var(--gold)}
.hz-side-card-body{padding:22px 24px}

/* Features list */
.hz-features{list-style:none;margin:0;padding:0}
.hz-features li{
  position:relative;padding:11px 0 11px 30px;
  font-family:var(--sans);font-size:13.5px;line-height:1.6;color:var(--navy);
  border-bottom:1px solid var(--line);
}
.hz-features li:last-child{border-bottom:0;padding-bottom:0}
.hz-features li:first-child{padding-top:0}
.hz-features li::before{
  content:'✓';position:absolute;left:0;top:11px;
  width:20px;height:20px;
  background:var(--gold);color:var(--navy);
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:700;
}
.hz-features li:first-child::before{top:0}

/* Specs table (sidebar) */
.hz-specs-table{
  width:100%;border-collapse:collapse;font-size:12.5px;
}
.hz-specs-table th,
.hz-specs-table td{
  padding:9px 0;text-align:left;
  border-bottom:1px solid var(--line);vertical-align:top;
}
.hz-specs-table tr:last-child th,
.hz-specs-table tr:last-child td{border-bottom:0}
.hz-specs-table th{
  font-family:var(--sans);font-size:10.5px;font-weight:600;
  color:#5a5a5a;text-transform:uppercase;letter-spacing:.5px;
  width:50%;padding-right:10px;
}
.hz-specs-table td{
  font-family:var(--mono);font-size:12px;font-weight:600;color:var(--navy);
}

/* CTA Card */
.hz-cta-card{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;padding:28px;
  border-top:4px solid var(--red);
}
.hz-cta-card .eyebrow{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);
  margin-bottom:8px;
}
.hz-cta-card h3{
  font-family:var(--serif);font-size:24px;font-weight:600;
  color:#fff;margin:0 0 14px;line-height:1.2;
}
.hz-cta-card h3 em{font-style:italic;color:var(--gold)}
.hz-cta-card p{
  font-family:var(--sans);font-size:13px;line-height:1.6;
  color:rgba(255,255,255,.75);margin:0 0 20px;
}
.hz-cta-btn{
  display:block;text-align:center;
  padding:14px;margin-bottom:8px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  text-decoration:none;transition:.2s;
  border:1.5px solid transparent;
}
.hz-cta-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.hz-cta-btn-primary:hover{background:var(--red-dark);border-color:var(--red-dark)}
.hz-cta-btn-ghost{background:transparent;color:var(--gold);border-color:var(--gold)}
.hz-cta-btn-ghost:hover{background:var(--gold);color:var(--navy);border-color:var(--gold)}

/* OTHER SERVICES */
.hz-other-section{
  background:#fff;padding:90px 0;border-top:1px solid var(--line);
}
.hz-other-head{text-align:center;max-width:680px;margin:0 auto 50px}
.hz-other-head .eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--red);
  margin-bottom:14px;
}
.hz-other-head .eyebrow::before,
.hz-other-head .eyebrow::after{
  content:'';width:30px;height:1px;background:var(--red);
}
.hz-other-head h2{
  font-family:var(--serif);font-size:clamp(32px,4vw,46px);font-weight:500;
  letter-spacing:-.5px;line-height:1.1;color:var(--navy);margin:0;
}
.hz-other-head h2 em{font-style:italic;color:var(--red)}

.hz-other-grid{
  display:grid;grid-template-columns:repeat(3, 1fr);gap:0;
  background:#fff;border:1px solid var(--line);
}
@media (max-width:900px){.hz-other-grid{grid-template-columns:1fr}}

.hz-other-card{
  position:relative;text-decoration:none;color:inherit;
  border-right:1px solid var(--line);background:#fff;
  display:flex;flex-direction:column;
  transition:.25s;overflow:hidden;
}
.hz-other-card:last-child{border-right:0}
@media (max-width:900px){
  .hz-other-card{border-right:0;border-bottom:1px solid var(--line)}
  .hz-other-card:last-child{border-bottom:0}
}
.hz-other-card:hover{
  background:var(--paper);
}
.hz-other-card-img{
  width:100%;height:240px;
  position:relative;overflow:hidden;background:var(--navy-2);
}
.hz-other-card-img img{
  width:100%;height:100%;object-fit:cover;
  transition:.6s;filter:brightness(.92);
}
.hz-other-card:hover .hz-other-card-img img{
  transform:scale(1.05);filter:brightness(1);
}
.hz-other-card-img::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(180deg, transparent 0%, rgba(5,13,36,.55) 100%);
  z-index:2;pointer-events:none;
}
.hz-other-card-num{
  position:absolute;top:18px;left:18px;
  font-family:var(--serif);font-style:italic;font-weight:600;font-size:13px;
  color:var(--gold);background:rgba(5,13,36,.6);
  border:1px solid var(--gold);padding:5px 12px;
  z-index:3;backdrop-filter:blur(6px);
}
.hz-other-card-body{padding:30px 28px}
.hz-other-card h3{
  font-family:var(--serif);font-size:24px;font-weight:600;
  letter-spacing:-.3px;line-height:1.2;
  color:var(--navy);margin:0 0 12px;
}
.hz-other-card p{
  font-family:var(--sans);font-size:13.5px;line-height:1.65;
  color:#5a5a5a;margin:0 0 18px;
}
.hz-other-link{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:1.8px;text-transform:uppercase;color:var(--gold);
  display:inline-flex;align-items:center;gap:6px;
  padding-top:14px;border-top:1px solid var(--line);
}
.hz-other-card:hover .hz-other-link{color:var(--red)}
.hz-other-link span{transition:transform .2s}
.hz-other-card:hover .hz-other-link span{transform:translateX(6px)}

/* CTA BANNER */
.hz-cta-banner{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 50%, var(--navy-3) 100%);
  color:#fff;padding:90px 0;
  position:relative;overflow:hidden;border-top:4px solid var(--gold);
}
.hz-cta-banner::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
}
.hz-cta-banner-inner{
  display:grid;grid-template-columns:1.4fr 1fr;gap:50px;align-items:center;
  position:relative;z-index:2;
}
@media (max-width:900px){.hz-cta-banner-inner{grid-template-columns:1fr;text-align:center}}
.hz-cta-banner-eyebrow{
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--gold);
  margin-bottom:14px;
}
.hz-cta-banner h2{
  font-family:var(--serif);font-size:clamp(32px,4vw,46px);
  font-weight:500;font-style:italic;letter-spacing:-.5px;
  line-height:1.15;margin:0 0 14px;color:#fff;
}
.hz-cta-banner h2 strong{font-style:normal;color:var(--gold);font-weight:600}
.hz-cta-banner-lead{
  font-family:var(--sans);font-size:14.5px;line-height:1.7;
  color:rgba(255,255,255,.75);margin:0;
}
.hz-cta-banner-actions{
  display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end;
}
@media (max-width:900px){.hz-cta-banner-actions{justify-content:center}}
.hz-cta-banner-btn{
  display:inline-block;padding:16px 32px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;transition:.2s;border:1px solid transparent;
}
.hz-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.hz-btn-primary:hover{background:var(--red-dark);transform:translateY(-2px);box-shadow:0 10px 22px rgba(200,16,46,.4)}
.hz-btn-ghost{background:transparent;color:var(--gold);border-color:var(--gold)}
.hz-btn-ghost:hover{background:var(--gold);color:var(--navy);transform:translateY(-2px)}
</style>

<div class="hz-page">

  <!-- HERO -->
  <section class="hz-hero">
    <div class="container">
      <div class="hz-hero-icon"><?= $svcIcon ?></div>
      <div class="hz-hero-eyebrow"><?= h($svcEyebrow) ?></div>
      <h1><?= h($s['title']) ?></h1>
      <?php if (!empty($s['short_desc'])): ?>
        <p class="hz-hero-lead"><?= h($s['short_desc']) ?></p>
      <?php endif; ?>

      <div class="hz-hero-stats">
        <div class="hz-stat"><strong>20+ Yıl</strong><span>Deneyim</span></div>
        <div class="hz-stat"><strong>%100</strong><span>Hassasiyet</span></div>
        <div class="hz-stat"><strong>Aynı Gün</strong><span>Teklif</span></div>
        <div class="hz-stat"><strong>81 İl</strong><span>Sevkiyat</span></div>
      </div>
    </div>
  </section>

  <!-- BREADCRUMB -->
  <section class="hz-breadcrumb-strip">
    <div class="container">
      <nav class="hz-breadcrumb">
        <a href="<?= h(url('')) ?>">Anasayfa</a>
        <span class="sep">›</span>
        <a href="<?= h(url('hizmetler.php')) ?>">Endüstriyel Yetkinlikler</a>
        <span class="sep">›</span>
        <span class="current"><?= h($s['title']) ?></span>
      </nav>
    </div>
  </section>

  <!-- MAIN -->
  <section class="hz-main">
    <div class="container">
      <div class="hz-grid">

        <!-- CONTENT -->
        <article class="hz-content">
          <?= !empty($s['description']) ? $s['description'] : '<p>Bu hizmet için detaylı içerik yakında eklenecektir.</p>' ?>
        </article>

        <!-- SIDEBAR -->
        <aside class="hz-sidebar">

          <?php if ($features): ?>
          <div class="hz-side-card">
            <div class="hz-side-card-head">
              <div class="eyebrow">Hizmet <?= count($features) ?> Avantaj</div>
              <h3><em>Avantajlarımız</em></h3>
            </div>
            <div class="hz-side-card-body">
              <ul class="hz-features">
                <?php foreach ($features as $f): ?>
                  <li><?= h($f) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <?php endif; ?>

          <?php if ($specs): ?>
          <div class="hz-side-card">
            <div class="hz-side-card-head">
              <div class="eyebrow">Teknik Spesifikasyon</div>
              <h3>Üretim <em>Özellikleri</em></h3>
            </div>
            <div class="hz-side-card-body">
              <table class="hz-specs-table">
                <tbody>
                  <?php foreach ($specs as $k => $v): ?>
                  <tr><th><?= h($k) ?></th><td><?= h($v) ?></td></tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php endif; ?>

          <div class="hz-cta-card">
            <div class="eyebrow">Aynı Gün Teklif</div>
            <h3>Bu Hizmet İçin <em>Bize Yazın</em></h3>
            <p>DXF/DWG çiziminizi veya ölçü detaylarınızı gönderin, satış ekibimiz aynı gün geri dönüş yapsın.</p>
            <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $s['title'] . ' hizmeti için teklif almak istiyorum.')) ?>"
               target="_blank" rel="noopener"
               class="hz-cta-btn hz-cta-btn-primary">💬 WhatsApp Teklif</a>
            <a href="<?= h(url('iletisim.php')) ?>" class="hz-cta-btn hz-cta-btn-ghost">📝 İletişim Formu</a>
            <a href="<?= h(phone_link(settings('site_phone', '03323422452'))) ?>" class="hz-cta-btn hz-cta-btn-ghost"
               style="margin-bottom:0">📞 <?= h(format_phone(settings('site_phone', '03323422452'))) ?></a>
          </div>

        </aside>
      </div>
    </div>
  </section>

  <!-- DİĞER HİZMETLER -->
  <?php if ($otherServices): ?>
  <section class="hz-other-section">
    <div class="container">
      <div class="hz-other-head">
        <div class="eyebrow">Diğer Yetkinlikler</div>
        <h2>Endüstriyel <em>Atölye Hizmetlerimiz</em></h2>
      </div>
      <div class="hz-other-grid">
        <?php foreach ($otherServices as $i => $o): ?>
        <a class="hz-other-card" href="<?= h(url('hizmet.php?slug=' . $o['slug'])) ?>">
          <div class="hz-other-card-img">
            <span class="hz-other-card-num">— 0<?= $i+1 ?> —</span>
            <?php if (!empty($o['image'])): ?>
              <img src="<?= h(img_url($o['image'])) ?>" alt="<?= h($o['title']) ?>" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="hz-other-card-body">
            <h3><?= h($o['title']) ?></h3>
            <p><?= h($o['short_desc']) ?></p>
            <span class="hz-other-link">Detaylı İncele <span>→</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA BANNER -->
  <section class="hz-cta-banner">
    <div class="container">
      <div class="hz-cta-banner-inner">
        <div>
          <div class="hz-cta-banner-eyebrow">Tek Tedarikçi · Uçtan Uca Çözüm</div>
          <h2>Sac, boru, profil ve <strong>atölye desteği</strong><br>tek adresten</h2>
          <p class="hz-cta-banner-lead">Sertifikalı malzeme tedariği, hassas kesim, sevkiyat ve mühendislik desteği ile demir-çelik projelerinizde uçtan uca iş ortağıyız.</p>
        </div>
        <div class="hz-cta-banner-actions">
          <a href="<?= h(url('urunler.php')) ?>" class="hz-cta-banner-btn hz-btn-primary">Ürün Katalogu</a>
          <a href="<?= h(url('iletisim.php')) ?>" class="hz-cta-banner-btn hz-btn-ghost">İletişim</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
