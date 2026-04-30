<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$ulke = row("SELECT * FROM tm_seo_ulkeler WHERE slug=? AND is_active=1", [$slug]);

if (!$ulke) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$cats = all("SELECT * FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order LIMIT 8");

$pageTitle = $ulke['name'] . ' Demir Çelik Sevkiyat';
$metaDesc  = $ulke['name'] . ' için demir-çelik ürün sevkiyat hattımız. ' . $ulke['cargo_info'] . ' Üretici sertifikalı, gümrük belgeleri dahil.';

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.uu-page{
  --navy:#050d24;--navy-2:#0c1e44;
  --gold:#c9a86b;--red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

.uu-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;padding:130px 0 100px;
  position:relative;overflow:hidden;border-bottom:4px solid var(--gold);
}
.uu-hero::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.uu-hero .container{position:relative;z-index:2;text-align:center}
.uu-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;color:var(--gold);
  margin-bottom:30px;
}
.uu-hero-eyebrow::before,
.uu-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.uu-hero h1{
  font-family:var(--serif);font-size:clamp(44px, 6.5vw, 76px);
  font-weight:500;line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 24px;color:#fff;
}
.uu-hero h1 em{font-style:italic;color:var(--gold)}
.uu-hero-lead{
  font-family:var(--sans);font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);max-width:760px;margin:0 auto 40px;
}
.uu-hero-stats{
  display:flex;justify-content:center;gap:40px;flex-wrap:wrap;
  padding-top:30px;border-top:1px solid rgba(255,255,255,.12);
}
.uu-stat{text-align:center}
.uu-stat strong{
  display:block;font-family:var(--serif);font-size:32px;
  font-weight:500;color:var(--gold);line-height:1;
}
.uu-stat span{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:1.8px;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-top:8px;display:block;
}

.uu-section{padding:90px 0;background:#fff}
.uu-section.alt{background:var(--paper)}
.uu-section-head{text-align:center;max-width:760px;margin:0 auto 50px}
.uu-section-head .eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--red);
  margin-bottom:18px;
}
.uu-section-head .eyebrow::before,
.uu-section-head .eyebrow::after{
  content:'';width:30px;height:1px;background:var(--red);
}
.uu-section-head h2{
  font-family:var(--serif);font-size:clamp(32px, 4vw, 48px);
  font-weight:500;letter-spacing:-.5px;line-height:1.1;
  color:var(--navy);margin:0 0 16px;
}
.uu-section-head h2 em{font-style:italic;color:var(--red)}
.uu-section-head p{
  font-family:var(--sans);font-size:15px;line-height:1.65;
  color:#5a5a5a;margin:0;
}

/* INTRO GRID */
.uu-intro-grid{
  display:grid;grid-template-columns:1fr 1.4fr;gap:50px;
  max-width:1100px;margin:0 auto;
}
@media (max-width:900px){.uu-intro-grid{grid-template-columns:1fr;gap:30px}}
.uu-intro-card{
  background:var(--paper);border:1px solid var(--line);
  border-top:4px solid var(--gold);padding:32px;
}
.uu-intro-card h3{
  font-family:var(--serif);font-size:22px;font-weight:600;
  color:var(--navy);margin:0 0 18px;letter-spacing:-.2px;
}
.uu-intro-card-row{
  padding:12px 0;border-bottom:1px solid var(--line);
  display:flex;justify-content:space-between;gap:14px;align-items:flex-start;
}
.uu-intro-card-row:last-child{border-bottom:0}
.uu-intro-card-row .lbl{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;color:#5a5a5a;
  flex-shrink:0;
}
.uu-intro-card-row .val{
  font-family:var(--sans);font-size:13.5px;font-weight:600;
  color:var(--navy);text-align:right;
}
.uu-intro-text p{
  font-family:var(--sans);font-size:15.5px;line-height:1.75;
  color:#3a3a3a;margin:0 0 16px;
}

/* PRODUCTS */
.uu-products{
  display:grid;grid-template-columns:repeat(4, 1fr);gap:0;
  background:#fff;border:1px solid var(--line);
}
@media (max-width:900px){.uu-products{grid-template-columns:repeat(2,1fr)}}
@media (max-width:480px){.uu-products{grid-template-columns:1fr}}
.uu-product{
  text-decoration:none;color:inherit;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  background:#fff;display:flex;flex-direction:column;
  transition:.25s;
}
.uu-product:hover{
  background:var(--paper);transform:translateY(-3px);
  box-shadow:0 12px 28px rgba(5,13,36,.1);
}
.uu-product-img{height:160px;background:var(--navy-2);overflow:hidden}
.uu-product-img img{
  width:100%;height:100%;object-fit:cover;transition:.5s;
}
.uu-product:hover .uu-product-img img{transform:scale(1.06)}
.uu-product-body{padding:18px 22px}
.uu-product h3{
  font-family:var(--serif);font-size:18px;font-weight:600;
  color:var(--navy);margin:0 0 6px;line-height:1.25;
}
.uu-product-link{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;color:var(--red);
}

/* SEO TEXT */
.uu-seo{max-width:880px;margin:0 auto}
.uu-seo h2{
  font-family:var(--serif);font-size:30px;font-weight:600;
  color:var(--navy);margin:32px 0 14px;letter-spacing:-.3px;
  border-left:3px solid var(--gold);padding-left:14px;
}
.uu-seo h2:first-child{margin-top:0}
.uu-seo p{
  font-family:var(--sans);font-size:15.5px;line-height:1.75;
  color:#3a3a3a;margin:0 0 16px;
}
.uu-seo strong{color:var(--navy);font-weight:700}
.uu-seo ul{margin:0 0 20px;padding:0;list-style:none}
.uu-seo ul li{
  position:relative;padding-left:22px;margin-bottom:10px;
  font-family:var(--sans);font-size:14.5px;line-height:1.65;color:#3a3a3a;
}
.uu-seo ul li::before{
  content:'';position:absolute;left:0;top:9px;
  width:8px;height:8px;background:var(--gold);transform:rotate(45deg);
}

/* CTA */
.uu-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;padding:90px 0;text-align:center;
  position:relative;overflow:hidden;border-top:4px solid var(--gold);
}
.uu-cta-inner{max-width:680px;margin:0 auto;position:relative;z-index:2}
.uu-cta h2{
  font-family:var(--serif);font-size:clamp(32px, 4vw, 46px);
  font-weight:500;font-style:italic;letter-spacing:-.5px;
  line-height:1.15;margin:0 0 16px;color:#fff;
}
.uu-cta h2 strong{font-style:normal;color:var(--gold);font-weight:600}
.uu-cta p{
  font-family:var(--sans);font-size:15px;line-height:1.7;
  color:rgba(255,255,255,.75);margin:0 0 32px;
}
.uu-cta-actions{
  display:flex;justify-content:center;gap:14px;flex-wrap:wrap;
}
.uu-btn{
  display:inline-block;padding:18px 36px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;transition:.2s;border:1px solid transparent;
}
.uu-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.uu-btn-primary:hover{background:var(--red-dark);transform:translateY(-2px);box-shadow:0 12px 26px rgba(200,16,46,.4)}
.uu-btn-ghost{background:transparent;color:var(--gold);border-color:var(--gold)}
.uu-btn-ghost:hover{background:var(--gold);color:var(--navy);transform:translateY(-2px)}
</style>

<div class="uu-page">

  <!-- HERO -->
  <section class="uu-hero">
    <div class="container">
      <div class="uu-hero-eyebrow">İhracat & Sınır Ötesi Sevkiyat</div>
      <h1><?= h($ulke['name']) ?>'<em>a Demir Çelik</em><br>Sevkiyatı</h1>
      <p class="uu-hero-lead">
        Türkiye'den <?= h($ulke['name']) ?>'a düzenli sevkiyat hattımız. Üretici sertifikalı çelik ürünler, profesyonel gümrük süreç desteği ile hedef ülkenize.
      </p>
      <div class="uu-hero-stats">
        <div class="uu-stat"><strong><?= h($ulke['capital']) ?></strong><span>Başkent</span></div>
        <div class="uu-stat"><strong><?= h($ulke['population']) ?></strong><span>Nüfus</span></div>
        <div class="uu-stat"><strong>Sertifikalı</strong><span>Üretici Belgesi</span></div>
        <div class="uu-stat"><strong>Gümrük</strong><span>Belge Desteği</span></div>
      </div>
    </div>
  </section>

  <!-- INTRO -->
  <section class="uu-section">
    <div class="container">
      <div class="uu-section-head">
        <div class="eyebrow"><?= h($ulke['name']) ?> Tedarik Hattı</div>
        <h2><?= h($ulke['name']) ?>'<em>a Sevkiyat</em> Bilgileri</h2>
      </div>

      <div class="uu-intro-grid">
        <div class="uu-intro-card">
          <h3>📍 Sevkiyat Bilgisi</h3>
          <div class="uu-intro-card-row">
            <span class="lbl">Sınır Geçişi</span>
            <span class="val"><?= h($ulke['border_distance']) ?></span>
          </div>
          <div class="uu-intro-card-row">
            <span class="lbl">Teslim Süresi</span>
            <span class="val"><?= h($ulke['cargo_info']) ?></span>
          </div>
          <div class="uu-intro-card-row">
            <span class="lbl">Ticaret Hacmi</span>
            <span class="val"><?= h($ulke['trade_volume']) ?></span>
          </div>
          <div class="uu-intro-card-row">
            <span class="lbl">Gümrük Belgesi</span>
            <span class="val">Dahil</span>
          </div>
          <div class="uu-intro-card-row">
            <span class="lbl">Ödeme</span>
            <span class="val">USD / EUR / TL</span>
          </div>
        </div>

        <div class="uu-intro-text">
          <p><?= h($ulke['intro_text']) ?></p>
          <p><strong>Sevkiyat süreci:</strong> Türkiye merkez stoğumuzdan profesyonel uluslararası lojistik firmaları ile anlaşmalı olarak <?= h($ulke['name']) ?>'a sevkiyat sağlanır. Gümrük belgesi (proforma fatura, menşei şahadetnamesi, ATR/EUR.1 belgeleri) hazırlığı dahil hizmettir.</p>
          <p><strong>Ödeme:</strong> Banka havalesi (USD, EUR, TL) veya akreditif (LC) ile çalışıyoruz. İlk siparişte tutar avansı, sonraki siparişlerde uzun vadeli iş ortakları için özel koşullar sunabiliyoruz.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PRODUCTS -->
  <section class="uu-section alt">
    <div class="container">
      <div class="uu-section-head">
        <div class="eyebrow"><?= h($ulke['name']) ?>'a Sevk Edilen Ürünler</div>
        <h2>İhracat Yapılan <em>Ürün Yelpazesi</em></h2>
        <p><?= h($ulke['name']) ?>'a düzenli olarak sevk ettiğimiz demir-çelik ürün grupları. Tüm ürünler üretici sertifikalı.</p>
      </div>

      <div class="uu-products">
        <?php foreach ($cats as $c): ?>
        <a class="uu-product" href="<?= h(url('kategori.php?slug=' . $c['slug'])) ?>">
          <div class="uu-product-img">
            <?php if (!empty($c['image'])): ?>
              <img src="<?= h(img_url($c['image'])) ?>" alt="<?= h($c['name']) ?> <?= h($ulke['name']) ?>" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="uu-product-body">
            <h3><?= h($c['name']) ?></h3>
            <span class="uu-product-link">İncele →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- SEO TEXT -->
  <section class="uu-section">
    <div class="container">
      <div class="uu-seo">
        <h2><?= h($ulke['name']) ?>'a Türkiye'den Demir Çelik Sevkiyatı</h2>
        <p>Tekcan Metal olarak <?= h($ulke['name']) ?>'a düzenli demir-çelik ürün sevkiyatı sağlıyoruz. <strong><?= h($ulke['border_distance']) ?></strong> ve <strong><?= h($ulke['trade_volume']) ?></strong> bilgisi ışığında, profesyonel uluslararası lojistik firmalarıyla anlaşmalı sevkiyat ağımızla hedef ülkeye güvenli ürün ulaştırıyoruz.</p>

        <h2>Sevkiyat Sürecimiz</h2>
        <ul>
          <li><strong>Teklif:</strong> İhtiyaç duyduğunuz ürün için ölçü, kalite, kalınlık ve adet bilgisini bize iletin. Aynı gün içinde proforma fatura ve sevkiyat planı sunuyoruz.</li>
          <li><strong>Gümrük Belgeleri:</strong> Menşei şahadetnamesi, ATR (AB ticareti için), EUR.1 (Türkiye-AB serbest ticaret), faturalandırma — hepsi tarafımızdan hazırlanır.</li>
          <li><strong>Üretici Sertifikası:</strong> Erdemir, Borçelik, Habaş gibi entegre üreticilerden gelen kalite belgeleri sevk evraklarına dahil edilir.</li>
          <li><strong>Lojistik:</strong> Türkiye merkez stoğumuzdan <?= h($ulke['name']) ?>'a profesyonel nakliyat firmaları ile sevkiyat. <?= h($ulke['cargo_info']) ?></li>
          <li><strong>Ödeme:</strong> Banka havalesi veya akreditif ile USD/EUR/TL bazlı ödeme.</li>
        </ul>

        <h2><?= h($ulke['name']) ?>'a Sevk Ettiğimiz Ürünler</h2>
        <p><strong>Sac Ürünleri:</strong> Siyah sac, DKP sac, HRP sac, ST-52 sac, galvanizli sac (1 mm – 100 mm kalınlık aralığı, çeşitli ölçüler).</p>
        <p><strong>Boru ve Profil:</strong> Su borusu, kazan borusu, konstrüksiyon borusu, kare profil, dikdörtgen profil, oval profil.</p>
        <p><strong>Hadde Mamulleri:</strong> Lama, köşebent, HEA/HEB profil, IPE, NPI/NPU profil, kare demiri.</p>
        <p><strong>İnşaat Demirleri:</strong> Nervürlü inşaat demiri (BÇIII-A / B500C), Q ve R tipi çelik hasır.</p>
        <p><strong>Özel Ürünler:</strong> Patent dirsek, norm flanş, petek kiriş, çatı paneli, cephe paneli.</p>

        <h2>Neden Tekcan Metal?</h2>
        <p>2005'ten bu yana Konya merkezli faaliyet gösteren Tekcan Metal, Türkiye'nin önde gelen entegre çelik üretim tesislerinden (Erdemir, Borçelik, Habaş, İçdaş, Tosyalı Çelik, Kardemir) doğrudan tedarik gücüyle <?= h($ulke['name']) ?> müşterilerine fiyat avantajı ve üretici sertifikalı ürün güvencesi sunmaktadır.</p>
        <p>Profesyonel ihracat süreç desteği, 20+ yıllık tedarikçi deneyimi, hassas ölçü atölye desteği ve hızlı sevkiyat hattımız ile <?= h($ulke['name']) ?>'daki müteahhitlerin, sanayicilerin ve devlet kurumlarının uzun vadeli iş ortağıyız.</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="uu-cta">
    <div class="container">
      <div class="uu-cta-inner">
        <h2><?= h($ulke['name']) ?>'a Sevkiyat İçin <strong>Bize Yazın</strong></h2>
        <p>İhtiyaç duyduğunuz ürün listesini, ölçü ve kalitelerle birlikte gönderin. Aynı gün proforma fatura ve sevkiyat süreç bilgisi sunalım.</p>
        <div class="uu-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="uu-btn uu-btn-primary">İhracat Teklifi İste</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $ulke['name'] . ' için demir-çelik sevkiyat teklifi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="uu-btn uu-btn-ghost">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
