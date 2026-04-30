<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$page = row("SELECT * FROM tm_pages WHERE slug=? AND is_active=1", [$slug]);

if (!$page) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Sayfa tipi tespiti — eyebrow ve ikon için
$pageType = 'document';
$pageEyebrow = 'Kurumsal Bilgi';
$pageIcon = '📄';

$lc = mb_strtolower($slug, 'UTF-8');
if (str_contains($lc, 'kvkk') || str_contains($lc, 'kisisel') || str_contains($lc, 'aydinlat')) {
    $pageType = 'kvkk';
    $pageEyebrow = 'Yasal Bilgilendirme';
    $pageIcon = '🛡';
} elseif (str_contains($lc, 'cerez') || str_contains($lc, 'cookie')) {
    $pageType = 'cookies';
    $pageEyebrow = 'Çerez Politikası';
    $pageIcon = '🍪';
} elseif (str_contains($lc, 'gizlilik') || str_contains($lc, 'privacy')) {
    $pageType = 'privacy';
    $pageEyebrow = 'Gizlilik Politikası';
    $pageIcon = '🔐';
} elseif (str_contains($lc, 'kullanim') || str_contains($lc, 'sartlar') || str_contains($lc, 'terms')) {
    $pageType = 'terms';
    $pageEyebrow = 'Kullanım Şartları';
    $pageIcon = '📜';
} elseif (str_contains($lc, 'iade') || str_contains($lc, 'refund')) {
    $pageType = 'refund';
    $pageEyebrow = 'İade ve Değişim';
    $pageIcon = '↺';
}

// Son güncelleme tarihi
$lastUpdate = !empty($page['updated_at']) ? $page['updated_at'] : ($page['created_at'] ?? null);

// Diğer kurumsal sayfaları al (sidebar için)
$otherPages = all("SELECT slug, title FROM tm_pages WHERE is_active=1 AND id<>? ORDER BY sort_order, title LIMIT 8", [$page['id']]);

$pageTitle = tr_field($page, 'title') ?: $page['title'];
$metaDesc  = tr_field($page, 'meta_desc') ?: ($page['meta_desc'] ?? '');
if (!$metaDesc && function_exists('excerpt')) $metaDesc = excerpt(tr_field($page, 'content') ?: $page['content'], 160);
if (!$metaDesc) $metaDesc = mb_substr(strip_tags($page['content']), 0, 160, 'UTF-8');

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.lp-page{
  --navy:#050d24;--navy-2:#0c1e44;--navy-3:#143672;
  --gold:#c9a86b;--gold-light:#e0c48a;--gold-dark:#a88a4a;
  --red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--paper-2:#f3f1ec;--line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* HERO */
.lp-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;padding:130px 0 90px;
  position:relative;overflow:hidden;
  border-bottom:4px solid var(--gold);
}
.lp-hero::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.lp-hero::after{
  content:'§';
  position:absolute;
  bottom:-160px;right:-50px;
  font-family:var(--serif);font-size:520px;font-weight:500;font-style:italic;
  color:rgba(201,168,107,.05);line-height:1;
  pointer-events:none;
}
.lp-hero .container{position:relative;z-index:2;text-align:center}
.lp-hero-icon{
  display:inline-flex;align-items:center;justify-content:center;
  width:90px;height:90px;
  margin:0 auto 24px;
  background:rgba(201,168,107,.1);
  border:2px solid var(--gold);
  font-size:44px;line-height:1;
}
.lp-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;color:var(--gold);
  margin-bottom:24px;
}
.lp-hero-eyebrow::before,
.lp-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.lp-hero h1{
  font-family:var(--serif);
  font-size:clamp(42px, 6vw, 70px);font-weight:500;
  line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 20px;color:#fff;
}
.lp-hero h1 em{font-style:italic;color:var(--gold)}
.lp-hero-lead{
  font-family:var(--sans);
  font-size:16px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;margin:0 auto;
}
.lp-hero-meta{
  margin-top:36px;padding-top:24px;
  border-top:1px solid rgba(255,255,255,.12);
  display:flex;justify-content:center;gap:40px;flex-wrap:wrap;
}
.lp-hero-meta-item{
  text-align:center;
}
.lp-hero-meta-item .lbl{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:rgba(255,255,255,.55);
  margin-bottom:6px;
}
.lp-hero-meta-item .val{
  font-family:var(--serif);font-size:18px;font-style:italic;
  color:var(--gold);font-weight:500;
}

/* BREADCRUMB */
.lp-breadcrumb-strip{
  background:#fff;border-bottom:1px solid var(--line);padding:18px 0;
}
.lp-breadcrumb{
  font-family:var(--sans);font-size:12.5px;
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.lp-breadcrumb a{color:#5a5a5a;text-decoration:none;font-weight:500}
.lp-breadcrumb a:hover{color:var(--red)}
.lp-breadcrumb .sep{color:var(--gold)}
.lp-breadcrumb .current{
  color:var(--navy);font-weight:600;font-style:italic;
  font-family:var(--serif);font-size:14.5px;
}

/* MAIN */
.lp-main{padding:80px 0}
.lp-grid{
  display:grid;grid-template-columns:1fr 280px;gap:60px;align-items:start;
}
@media (max-width:900px){.lp-grid{grid-template-columns:1fr;gap:40px}}

/* CONTENT (Editorial layout) */
.lp-content{
  background:#fff;
  border:1px solid var(--line);
  border-top:4px solid var(--gold);
  padding:50px 60px;
  position:relative;
}
@media (max-width:768px){.lp-content{padding:30px 24px}}
.lp-content::before{
  content:'';position:absolute;
  top:-4px;left:50px;width:80px;height:4px;
  background:var(--red);
}

.lp-content h2,
.lp-content h3{
  font-family:var(--serif);
  font-weight:600;color:var(--navy);
  letter-spacing:-.3px;
  position:relative;
  padding-left:22px;
  border-left:3px solid var(--gold);
  scroll-margin-top:120px;
}
.lp-content h2{
  font-size:28px;margin:36px 0 16px;line-height:1.2;
}
.lp-content h2:first-child{margin-top:0}
.lp-content h3{
  font-size:22px;margin:28px 0 12px;line-height:1.25;
  border-left-color:var(--red);
}
.lp-content h4{
  font-family:var(--sans);font-size:14px;font-weight:700;
  letter-spacing:1px;text-transform:uppercase;color:var(--navy);
  margin:20px 0 10px;padding-bottom:6px;
  border-bottom:1px solid var(--line);
}

.lp-content p{
  font-family:var(--sans);font-size:15px;line-height:1.8;
  color:#3a3a3a;margin:0 0 16px;
}
.lp-content p:first-of-type{
  font-size:17px;color:var(--navy);font-weight:500;line-height:1.7;
}

.lp-content strong{color:var(--navy);font-weight:700}
.lp-content em{
  font-family:var(--serif);font-style:italic;color:var(--red);
  font-weight:500;
}

.lp-content a{
  color:var(--red);
  text-decoration:underline;
  text-decoration-color:var(--gold);
  text-underline-offset:3px;
  font-weight:600;
  transition:.15s;
}
.lp-content a:hover{
  color:var(--red-dark);
  text-decoration-color:var(--red-dark);
}

.lp-content ul,
.lp-content ol{
  margin:0 0 20px;
  padding:0;list-style:none;
  counter-reset:lp-list;
}
.lp-content ul li,
.lp-content ol li{
  position:relative;
  padding:6px 0 6px 28px;
  font-family:var(--sans);font-size:14.5px;line-height:1.7;color:#3a3a3a;
}
.lp-content ul li::before{
  content:'';
  position:absolute;left:0;top:14px;
  width:8px;height:8px;
  background:var(--gold);transform:rotate(45deg);
}
.lp-content ol{counter-reset:lp-list}
.lp-content ol li{counter-increment:lp-list}
.lp-content ol li::before{
  content:counter(lp-list, decimal-leading-zero);
  position:absolute;left:0;top:6px;
  font-family:var(--serif);font-size:18px;font-style:italic;
  color:var(--red);font-weight:600;
  background:none;width:auto;height:auto;transform:none;
}

.lp-content blockquote{
  margin:24px 0;
  padding:20px 24px;
  background:var(--paper);
  border-left:3px solid var(--gold);
  font-family:var(--serif);font-style:italic;font-size:17px;
  line-height:1.6;color:var(--navy-2);
}
.lp-content blockquote p{
  font-family:var(--serif);font-style:italic;font-size:17px;
  color:var(--navy-2);margin:0;
}

.lp-content table{
  width:100%;border-collapse:collapse;
  margin:24px 0;font-family:var(--sans);font-size:14px;
}
.lp-content table th,
.lp-content table td{
  padding:12px 14px;border:1px solid var(--line);
  text-align:left;vertical-align:top;
}
.lp-content table th{
  background:var(--navy);color:#fff;
  font-size:11px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;
}
.lp-content table tr:nth-child(even) td{background:var(--paper)}

.lp-content code{
  background:var(--paper);
  padding:2px 7px;
  border:1px solid var(--line);
  font-family:'JetBrains Mono', monospace;
  font-size:12.5px;color:var(--navy-2);
}

.lp-content hr{
  border:0;height:1px;
  background:var(--line);
  margin:32px 0;
  position:relative;
}
.lp-content hr::after{
  content:'❦';
  position:absolute;left:50%;top:50%;
  transform:translate(-50%, -50%);
  background:#fff;padding:0 14px;
  font-family:var(--serif);color:var(--gold);font-size:18px;
}

/* DROP CAP for first paragraph */
.lp-content > p:first-child::first-letter{
  font-family:var(--serif);
  font-size:64px;font-weight:600;font-style:italic;
  color:var(--red);
  float:left;line-height:.95;
  margin:6px 12px 0 0;padding:0;
}

/* SIGNATURE BLOCK */
.lp-signature{
  margin-top:40px;padding-top:32px;
  border-top:2px solid var(--gold);
}
.lp-signature-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:30px;
  font-family:var(--sans);font-size:13px;color:#3a3a3a;
}
@media (max-width:600px){.lp-signature-grid{grid-template-columns:1fr}}
.lp-signature-block .lbl{
  font-size:10px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;color:var(--red);
  margin-bottom:6px;
}
.lp-signature-block .val{
  font-family:var(--serif);font-size:18px;font-weight:600;
  color:var(--navy);font-style:italic;line-height:1.4;
}
.lp-signature-block .sub{
  font-size:12px;color:#666;line-height:1.5;
}

/* SIDEBAR */
.lp-sidebar{
  position:sticky;top:100px;
}
@media (max-width:900px){.lp-sidebar{position:static}}

.lp-sidebar-card{
  background:#fff;border:1px solid var(--line);
  margin-bottom:18px;
}
.lp-sidebar-card-head{
  padding:18px 22px;
  border-bottom:1px solid var(--line);
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
}
.lp-sidebar-card-head .eyebrow{
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:var(--gold);margin-bottom:4px;
}
.lp-sidebar-card-head h3{
  font-family:var(--serif);font-size:19px;font-weight:600;
  color:#fff;margin:0;line-height:1.2;
}
.lp-sidebar-card-body{padding:18px 22px}

.lp-other-list{list-style:none;margin:0;padding:0}
.lp-other-list li{
  border-bottom:1px solid var(--line);
}
.lp-other-list li:last-child{border-bottom:0}
.lp-other-list a{
  display:flex;align-items:center;gap:10px;
  padding:11px 0;
  font-family:var(--sans);font-size:13.5px;font-weight:500;
  color:var(--navy);text-decoration:none;
  transition:.15s;
}
.lp-other-list a:hover{
  color:var(--red);transform:translateX(3px);
}
.lp-other-list a::before{
  content:'›';color:var(--gold);font-size:16px;font-weight:700;
}

.lp-contact-card{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;padding:24px;
  border-top:4px solid var(--red);
}
.lp-contact-card .eyebrow{
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;color:var(--gold);
  margin-bottom:6px;
}
.lp-contact-card h3{
  font-family:var(--serif);font-size:22px;font-weight:600;
  color:#fff;margin:0 0 12px;line-height:1.2;
}
.lp-contact-card h3 em{font-style:italic;color:var(--gold)}
.lp-contact-card p{
  font-family:var(--sans);font-size:13px;line-height:1.6;
  color:rgba(255,255,255,.75);margin:0 0 16px;
}
.lp-contact-card a.btn{
  display:block;text-align:center;
  padding:13px;margin-bottom:7px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  text-decoration:none;transition:.18s;
  border:1.5px solid transparent;
}
.lp-contact-card a.btn-primary{
  background:var(--red);color:#fff;border-color:var(--red);
}
.lp-contact-card a.btn-primary:hover{background:var(--red-dark);border-color:var(--red-dark)}
.lp-contact-card a.btn-ghost{
  background:transparent;color:var(--gold);border-color:var(--gold);
}
.lp-contact-card a.btn-ghost:hover{background:var(--gold);color:var(--navy)}
</style>

<div class="lp-page">

  <!-- HERO -->
  <section class="lp-hero">
    <div class="container">
      <div class="lp-hero-icon"><?= $pageIcon ?></div>
      <div class="lp-hero-eyebrow"><?= h($pageEyebrow) ?></div>
      <h1><?= h(tr_field($page, 'title') ?: $page['title']) ?></h1>
      <?php if (!empty($page['subtitle'])): ?>
        <p class="lp-hero-lead"><?= h(tr_field($page, 'subtitle') ?: ($page['subtitle'] ?? '')) ?></p>
      <?php endif; ?>

      <?php if ($lastUpdate): ?>
      <div class="lp-hero-meta">
        <div class="lp-hero-meta-item">
          <div class="lbl">Yürürlük Tarihi</div>
          <div class="val"><?= h(date('d.m.Y', strtotime($lastUpdate))) ?></div>
        </div>
        <div class="lp-hero-meta-item">
          <div class="lbl">Belge Türü</div>
          <div class="val"><?= h($pageEyebrow) ?></div>
        </div>
        <div class="lp-hero-meta-item">
          <div class="lbl">Sürüm</div>
          <div class="val">v1.0</div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- BREADCRUMB -->
  <section class="lp-breadcrumb-strip">
    <div class="container">
      <nav class="lp-breadcrumb">
        <a href="<?= h(url('')) ?>">Anasayfa</a>
        <span class="sep">›</span>
        <span class="current"><?= h(tr_field($page, 'title') ?: $page['title']) ?></span>
      </nav>
    </div>
  </section>

  <!-- MAIN -->
  <section class="lp-main">
    <div class="container">
      <div class="lp-grid">

        <!-- CONTENT -->
        <article class="lp-content">
          <?= tr_field($page, 'content') ?: $page['content'] ?>

          <!-- SIGNATURE BLOCK -->
          <div class="lp-signature">
            <div class="lp-signature-grid">
              <div class="lp-signature-block">
                <div class="lbl">Yayınlayan</div>
                <div class="val">Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</div>
                <div class="sub">2005 yılından bu yana Konya merkezli demir-çelik tedarikçisi</div>
              </div>
              <div class="lp-signature-block">
                <div class="lbl">İletişim</div>
                <div class="val">info@tekcanmetal.com</div>
                <div class="sub">Soru ve talepleriniz için 7/24 yazabilirsiniz</div>
              </div>
            </div>
          </div>
        </article>

        <!-- SIDEBAR -->
        <aside class="lp-sidebar">
          <?php if ($otherPages): ?>
          <div class="lp-sidebar-card">
            <div class="lp-sidebar-card-head">
              <div class="eyebrow">Kurumsal</div>
              <h3>Diğer Belgeler</h3>
            </div>
            <div class="lp-sidebar-card-body">
              <ul class="lp-other-list">
                <?php foreach ($otherPages as $op): ?>
                <li><a href="<?= h(url('sayfa.php?slug=' . $op['slug'])) ?>"><?= h($op['title']) ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <?php endif; ?>

          <div class="lp-contact-card">
            <div class="eyebrow">Sorularınız mı var?</div>
            <h3>Bize <em>Yazın</em></h3>
            <p>Belge ile ilgili her türlü soru ve talebiniz için iletişim ekibimiz 7/24 hizmetinizde.</p>
            <a href="<?= h(url('iletisim.php')) ?>" class="btn btn-primary">İletişim</a>
            <a href="mailto:info@tekcanmetal.com" class="btn btn-ghost">E-Posta</a>
          </div>
        </aside>

      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
