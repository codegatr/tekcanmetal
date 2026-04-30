<?php
require __DIR__ . '/includes/db.php';

$ilSlug   = $_GET['il']   ?? '';
$urunSlug = $_GET['urun'] ?? '';

$il = row("SELECT * FROM tm_seo_iller WHERE slug=? AND is_active=1", [$ilSlug]);
$urun = row("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
             FROM tm_products p
             LEFT JOIN tm_categories c ON c.id = p.category_id
             WHERE p.slug=? AND p.is_active=1", [$urunSlug]);

if (!$il || !$urun) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Specs JSON parse
$specs = [];
if (!empty($urun['specs'])) {
    $tmp = json_decode($urun['specs'], true);
    if (is_array($tmp)) $specs = $tmp;
}

// Aynı kategoride başka ürünler (cross-sell için)
$ilgili = all("SELECT * FROM tm_products WHERE category_id=? AND id<>? AND is_active=1 ORDER BY RAND() LIMIT 4",
              [$urun['category_id'], $urun['id']]);

// Custom intro varsa
$customIntro = val("SELECT custom_intro FROM tm_seo_il_urun WHERE il_slug=? AND urun_slug=?", [$ilSlug, $urunSlug]);

$pageTitle = $urun['name'] . ' ' . $il['name'] . ' Tedariği';
$metaDesc  = $il['name'] . ' için ' . $urun['name'] . ' tedariği. ' . $il['cargo_info'] . ' Üretici sertifikalı, rekabetçi fiyat.';

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

.iu-page{
  --navy:#050d24;--navy-2:#0c1e44;
  --gold:#c9a86b;--red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  --mono:'JetBrains Mono', monospace;
  background:var(--paper);
}

.iu-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;padding:120px 0 90px;
  position:relative;overflow:hidden;border-bottom:4px solid var(--gold);
}
.iu-hero::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
}
.iu-hero .container{position:relative;z-index:2}
.iu-hero-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center;
}
@media (max-width:900px){.iu-hero-grid{grid-template-columns:1fr;gap:30px}}
.iu-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;color:var(--gold);
  margin-bottom:24px;
}
.iu-hero-eyebrow::before{
  content:'';width:30px;height:1px;background:var(--gold);
}
.iu-hero h1{
  font-family:var(--serif);
  font-size:clamp(38px, 5.5vw, 64px);font-weight:500;
  line-height:1.05;letter-spacing:-1px;
  margin:0 0 20px;color:#fff;
}
.iu-hero h1 em{font-style:italic;color:var(--gold)}
.iu-hero-lead{
  font-family:var(--sans);font-size:16px;line-height:1.65;
  color:rgba(255,255,255,.78);margin:0 0 24px;
}
.iu-hero-cargo{
  background:rgba(201,168,107,.12);
  border-left:3px solid var(--gold);
  padding:14px 18px;
  font-family:var(--sans);font-size:13.5px;
  color:rgba(255,255,255,.9);line-height:1.55;
}
.iu-hero-cargo strong{color:var(--gold)}
.iu-hero-img{
  background:#fff;
  border:4px solid var(--gold);
  aspect-ratio:1;
  overflow:hidden;
  position:relative;
}
.iu-hero-img img{
  width:100%;height:100%;object-fit:cover;
}
.iu-hero-img-placeholder{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);font-size:160px;
  color:rgba(5,13,36,.1);
}

/* BREADCRUMB */
.iu-breadcrumb-strip{
  background:#fff;border-bottom:1px solid var(--line);
  padding:18px 0;
}
.iu-breadcrumb{
  font-family:var(--sans);font-size:12.5px;
  display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.iu-breadcrumb a{color:#5a5a5a;text-decoration:none;font-weight:500}
.iu-breadcrumb a:hover{color:var(--red)}
.iu-breadcrumb .sep{color:var(--gold)}
.iu-breadcrumb .current{color:var(--navy);font-weight:600;font-style:italic;font-family:var(--serif);font-size:14.5px}

/* MAIN */
.iu-main{padding:80px 0}
.iu-grid{
  display:grid;grid-template-columns:1.4fr 1fr;gap:50px;align-items:start;
}
@media (max-width:900px){.iu-grid{grid-template-columns:1fr}}

.iu-content h2{
  font-family:var(--serif);font-size:30px;font-weight:600;
  color:var(--navy);margin:32px 0 14px;letter-spacing:-.3px;
  border-left:3px solid var(--gold);padding-left:14px;
}
.iu-content h2:first-child{margin-top:0}
.iu-content p{
  font-family:var(--sans);font-size:15.5px;line-height:1.75;
  color:#3a3a3a;margin:0 0 16px;
}
.iu-content ul{
  margin:0 0 20px;padding:0;list-style:none;
}
.iu-content ul li{
  position:relative;padding-left:22px;margin-bottom:10px;
  font-family:var(--sans);font-size:14.5px;line-height:1.65;color:#3a3a3a;
}
.iu-content ul li::before{
  content:'';position:absolute;left:0;top:9px;
  width:8px;height:8px;background:var(--gold);transform:rotate(45deg);
}
.iu-content strong{color:var(--navy);font-weight:700}

/* SIDE: SPECS + CTA */
.iu-side{position:sticky;top:100px}
@media (max-width:900px){.iu-side{position:static}}
.iu-specs{
  background:#fff;border:1px solid var(--line);
  border-top:4px solid var(--gold);padding:28px 28px 24px;
  margin-bottom:20px;
}
.iu-specs-eyebrow{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;color:var(--red);
  margin-bottom:6px;
}
.iu-specs h3{
  font-family:var(--serif);font-size:24px;font-weight:600;
  color:var(--navy);margin:0 0 18px;letter-spacing:-.3px;
}
.iu-specs-table{
  width:100%;border-collapse:collapse;font-size:13px;
}
.iu-specs-table th,
.iu-specs-table td{
  padding:9px 0;text-align:left;border-bottom:1px solid var(--line);vertical-align:top;
}
.iu-specs-table tr:last-child th,
.iu-specs-table tr:last-child td{border-bottom:0}
.iu-specs-table th{
  font-family:var(--sans);font-size:11px;font-weight:600;
  color:#5a5a5a;text-transform:uppercase;letter-spacing:.5px;
  width:45%;padding-right:10px;
}
.iu-specs-table td{
  font-family:var(--mono);font-size:12.5px;font-weight:600;color:var(--navy);
}

.iu-side-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;padding:28px;
  border-top:4px solid var(--red);
}
.iu-side-cta-eyebrow{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);
  margin-bottom:8px;
}
.iu-side-cta h3{
  font-family:var(--serif);font-size:22px;font-weight:600;
  color:#fff;margin:0 0 12px;line-height:1.2;
}
.iu-side-cta h3 em{font-style:italic;color:var(--gold)}
.iu-side-cta p{
  font-family:var(--sans);font-size:13px;line-height:1.6;
  color:rgba(255,255,255,.75);margin:0 0 18px;
}
.iu-side-btn{
  display:block;text-align:center;
  padding:14px;margin-bottom:8px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  text-decoration:none;transition:.2s;border:1.5px solid transparent;
}
.iu-side-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.iu-side-btn-primary:hover{background:var(--red-dark);border-color:var(--red-dark)}
.iu-side-btn-ghost{background:transparent;color:var(--gold);border-color:var(--gold)}
.iu-side-btn-ghost:hover{background:var(--gold);color:var(--navy);border-color:var(--gold)}

/* RELATED */
.iu-related{background:#fff;padding:80px 0;border-top:1px solid var(--line)}
.iu-related-head{text-align:center;max-width:680px;margin:0 auto 40px}
.iu-related-head .eyebrow{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--red);
  margin-bottom:14px;display:inline-block;
}
.iu-related-head h2{
  font-family:var(--serif);font-size:34px;font-weight:500;
  letter-spacing:-.5px;line-height:1.15;color:var(--navy);margin:0;
}
.iu-related-head h2 em{font-style:italic;color:var(--red)}
.iu-related-grid{
  display:grid;grid-template-columns:repeat(4, 1fr);gap:20px;
}
@media (max-width:900px){.iu-related-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:480px){.iu-related-grid{grid-template-columns:1fr}}
.iu-related-card{
  text-decoration:none;color:inherit;
  background:#fff;border:1px solid var(--line);
  display:flex;flex-direction:column;transition:.25s;
}
.iu-related-card:hover{
  border-color:var(--gold);
  transform:translateY(-3px);
  box-shadow:0 12px 28px rgba(5,13,36,.1);
}
.iu-related-card-img{
  height:140px;background:var(--navy-2);overflow:hidden;
}
.iu-related-card-img img{
  width:100%;height:100%;object-fit:cover;
}
.iu-related-card-body{padding:14px 16px}
.iu-related-card h4{
  font-family:var(--serif);font-size:16px;font-weight:600;
  color:var(--navy);margin:0 0 6px;line-height:1.25;
}
.iu-related-card-link{
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;color:var(--red);
}
</style>

<div class="iu-page">

  <!-- BREADCRUMB -->
  <section class="iu-breadcrumb-strip">
    <div class="container">
      <nav class="iu-breadcrumb">
        <a href="<?= h(url('')) ?>">Anasayfa</a>
        <span class="sep">›</span>
        <a href="<?= h(url('il.php?slug=' . $il['slug'])) ?>"><?= h($il['name']) ?></a>
        <span class="sep">›</span>
        <a href="<?= h(url('urun.php?slug=' . $urun['slug'])) ?>"><?= h($urun['name']) ?></a>
        <span class="sep">›</span>
        <span class="current"><?= h($il['name']) ?> Tedariği</span>
      </nav>
    </div>
  </section>

  <!-- HERO -->
  <section class="iu-hero">
    <div class="container">
      <div class="iu-hero-grid">
        <div>
          <div class="iu-hero-eyebrow"><?= h($il['name']) ?> · <?= h($urun['cat_name']) ?></div>
          <h1><?= h($urun['name']) ?> <em><?= h($il['name']) ?></em><br>Tedariği</h1>
          <p class="iu-hero-lead"><?= h($urun['short_desc'] ?: 'Üretici sertifikalı, taze stok ' . $urun['name'] . ' ürünleri ' . $il['name'] . ' ve çevresine hızlı sevkiyat ile.') ?></p>
          <div class="iu-hero-cargo">
            <strong>📦 Sevkiyat:</strong> <?= h($il['cargo_info']) ?>
          </div>
        </div>
        <div class="iu-hero-img">
          <?php if (!empty($urun['image'])): ?>
            <img src="<?= h(img_url($urun['image'])) ?>" alt="<?= h($urun['name']) ?> <?= h($il['name']) ?>">
          <?php else: ?>
            <div class="iu-hero-img-placeholder"><?= h(mb_strtoupper(mb_substr($urun['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- MAIN -->
  <section class="iu-main">
    <div class="container">
      <div class="iu-grid">

        <!-- CONTENT -->
        <div class="iu-content">
          <h2><?= h($il['name']) ?>'da <?= h($urun['name']) ?> Tedariği</h2>
          <?php if ($customIntro): ?>
            <p><?= nl2br(h($customIntro)) ?></p>
          <?php else: ?>
            <p>Tekcan Metal olarak <?= h($il['name']) ?> ve çevresinde <?= h($urun['name']) ?> ihtiyacı olan müşterilere düzenli tedarik sağlıyoruz. <?= h($il['industry_focus']) ?> sektörlerine yönelik geniş stoğumuz, üretici sertifikalı ürünlerimiz ve hızlı sevkiyat hattımızla <?= h($il['name']) ?>'daki sanayicileri, müteahhitleri ve makine imalatçılarını destekliyoruz.</p>
            <p>Konya merkez stoğumuzdan <?= h($il['name']) ?>'a <?= h($il['cargo_info']) ?> Talep ettiğiniz ölçü ve kalite stoğumuzda yoksa, üretici partnerlerimizden 24-72 saat içinde tedarik edebiliyoruz.</p>
          <?php endif; ?>

          <?php if (!empty($urun['description'])): ?>
            <h2><?= h($urun['name']) ?> Hakkında</h2>
            <div><?= $urun['description'] ?></div>
          <?php endif; ?>

          <h2><?= h($il['name']) ?>'da <?= h($urun['name']) ?> İçin Neden Tekcan Metal?</h2>
          <ul>
            <li><strong>Üretici Sertifikalı:</strong> Erdemir, Borçelik, Habaş ve diğer entegre tesislerden doğrudan tedarik. Her parti menşei belgesi ve test raporu ile birlikte.</li>
            <li><strong>Hızlı Sevkiyat:</strong> <?= h($il['cargo_info']) ?> 30 ton üzeri sevkiyatlarda direkt yük seferi mümkün.</li>
            <li><strong>Geniş Stok:</strong> Standart ölçüler her zaman stoğumuzda; özel ölçü için 24-72 saat tedarik.</li>
            <li><strong>Atölye Hizmetleri:</strong> Lazer kesim (±0,1 mm), oksijen kesim ve dekoratif sac üretimi ile hassas ölçü desteği.</li>
            <li><strong>Rekabetçi Fiyat:</strong> Üreticiden direkt tedarik avantajı, toplu sipariş indirimi, vadeli ödeme imkânı.</li>
            <li><strong>Profesyonel Destek:</strong> 7/24 satış desteği, WhatsApp ile aynı saat içinde fiyat dönüşü.</li>
          </ul>

          <h2><?= h($il['name']) ?> + <?= h($urun['name']) ?> Sipariş Süreci</h2>
          <p><strong>1. Teklif:</strong> Aradığınız <?= h($urun['name']) ?> için ölçü, kalite ve adet bilgisini bize iletin. Aynı saat içinde teklif gönderiyoruz.</p>
          <p><strong>2. Onay:</strong> Teklifi onayladığınızda banka havalesi veya kart ödemeyle siparişi başlatıyoruz.</p>
          <p><strong>3. Sevkiyat:</strong> Konya merkez stoğumuzdan <?= h($il['name']) ?>'a <?= h($il['cargo_info']) ?></p>
          <p><strong>4. Teslim:</strong> Üretici sertifikası ve fatura ile birlikte teslim. İhtiyaç varsa lojistik koordinasyon dahil.</p>
        </div>

        <!-- SIDE -->
        <aside class="iu-side">
          <?php if ($specs): ?>
          <div class="iu-specs">
            <div class="iu-specs-eyebrow">Teknik Özellikler</div>
            <h3><?= h($urun['name']) ?> Spesifikasyonları</h3>
            <table class="iu-specs-table">
              <tbody>
                <?php foreach ($specs as $k => $v): ?>
                <tr><th><?= h($k) ?></th><td><?= h($v) ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>

          <div class="iu-side-cta">
            <div class="iu-side-cta-eyebrow"><?= h($il['name']) ?> Tedarik</div>
            <h3>Aynı Gün <em>Teklif</em></h3>
            <p><?= h($il['name']) ?>'a <?= h($urun['name']) ?> tedariği için bize ulaşın. Saatler içinde detaylı teklif elinizde.</p>
            <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $il['name'] . ' için ' . $urun['name'] . ' fiyat almak istiyorum.')) ?>" target="_blank" rel="noopener" class="iu-side-btn iu-side-btn-primary">💬 WhatsApp Teklif</a>
            <a href="<?= h(url('iletisim.php')) ?>" class="iu-side-btn iu-side-btn-ghost">📝 İletişim Formu</a>
            <a href="<?= h(phone_link(settings('site_phone', '03323422452'))) ?>" class="iu-side-btn iu-side-btn-ghost" style="margin-bottom:0">📞 <?= h(settings('site_phone', '0 332 342 24 52')) ?></a>
          </div>
        </aside>

      </div>
    </div>
  </section>

  <!-- RELATED PRODUCTS -->
  <?php if ($ilgili): ?>
  <section class="iu-related">
    <div class="container">
      <div class="iu-related-head">
        <div class="eyebrow">İlgili Ürünler</div>
        <h2><?= h($il['name']) ?>'<em>da Diğer</em> <?= h($urun['cat_name']) ?> Ürünleri</h2>
      </div>
      <div class="iu-related-grid">
        <?php foreach ($ilgili as $r): ?>
        <a class="iu-related-card" href="<?= h(url('il-urun.php?il=' . $il['slug'] . '&urun=' . $r['slug'])) ?>">
          <div class="iu-related-card-img">
            <?php if (!empty($r['image'])): ?>
              <img src="<?= h(img_url($r['image'])) ?>" alt="<?= h($r['name']) ?>" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="iu-related-card-body">
            <h4><?= h($r['name']) ?></h4>
            <span class="iu-related-card-link"><?= h($il['name']) ?>'da Tedarik →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
