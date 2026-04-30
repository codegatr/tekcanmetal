<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$il = row("SELECT * FROM tm_seo_iller WHERE slug=? AND is_active=1", [$slug]);

if (!$il) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Ana ürün kategorileri
$cats = all("SELECT * FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order LIMIT 8");

// İlk 8 öne çıkan ürün
$products = all("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
                 FROM tm_products p
                 LEFT JOIN tm_categories c ON c.id = p.category_id
                 WHERE p.is_active=1
                 ORDER BY p.is_featured DESC, p.sort_order
                 LIMIT 12");

$pageTitle = $il['name'] . ' Demir Çelik Tedarik';
$metaDesc  = $il['name'] . ' için demir, çelik, sac, boru, profil tedariği. Tekcan Metal — ' . $il['cargo_info'];

// Schema.org için
$pageType = 'city';
$citySlug = $il['slug'];
$cityName = $il['name'];

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.il-page{
  --navy:#050d24;--navy-2:#0c1e44;--navy-3:#143672;
  --gold:#c9a86b;--red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

.il-hero{
  background:linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;padding:130px 0 100px;
  position:relative;overflow:hidden;
  border-bottom:4px solid var(--gold);
}
.il-hero::before{
  content:'';position:absolute;inset:0;
  background-image:repeating-linear-gradient(-45deg, transparent 0, transparent 4px, rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.il-hero::after{
  content:'';position:absolute;
  bottom:-100px;right:-40px;
  width:380px;height:380px;
  border:2px solid rgba(201,168,107,.08);
  border-radius:50%;
  pointer-events:none;
}
.il-hero .container{position:relative;z-index:2;text-align:center}
.il-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;color:var(--gold);
  margin-bottom:30px;
}
.il-hero-eyebrow::before,
.il-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.il-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 86px);font-weight:500;
  line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 24px;color:#fff;
}
.il-hero h1 em{font-style:italic;color:var(--gold)}
.il-hero-lead{
  font-family:var(--sans);
  font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:760px;margin:0 auto 40px;
}
.il-hero-stats{
  display:flex;justify-content:center;gap:50px;flex-wrap:wrap;
  padding-top:30px;border-top:1px solid rgba(255,255,255,.12);
}
.il-stat strong{
  display:block;font-family:var(--serif);
  font-size:38px;font-weight:500;color:var(--gold);
  line-height:1;letter-spacing:-1px;
}
.il-stat span{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-top:8px;display:block;
}

/* INTRO BAND */
.il-intro{
  background:#fff;padding:80px 0;border-bottom:1px solid var(--line);
}
.il-intro-grid{
  display:grid;grid-template-columns:1fr 1.5fr;gap:60px;align-items:start;
}
@media (max-width:900px){.il-intro-grid{grid-template-columns:1fr;gap:30px}}
.il-intro-left .eyebrow{
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;color:var(--red);
  margin-bottom:14px;display:inline-block;
}
.il-intro-left h2{
  font-family:var(--serif);font-size:clamp(30px, 3.6vw, 46px);
  font-weight:500;letter-spacing:-.5px;line-height:1.15;
  margin:0;color:var(--navy);
}
.il-intro-left h2 em{font-style:italic;color:var(--red)}
.il-intro-right p{
  font-family:var(--sans);font-size:15.5px;line-height:1.75;
  color:#3a3a3a;margin:0 0 16px;
}
.il-intro-right p:last-child{margin-bottom:0}
.il-intro-tags{
  display:flex;flex-wrap:wrap;gap:8px;
  margin-top:24px;padding-top:24px;
  border-top:1px solid var(--line);
}
.il-intro-tag{
  font-family:var(--sans);font-size:11.5px;font-weight:600;
  padding:8px 14px;background:var(--paper);
  border:1px solid var(--line);color:var(--navy);
}
.il-intro-tag strong{color:var(--red)}

/* PRODUCTS GRID */
.il-section{padding:90px 0;background:var(--paper)}
.il-section.alt{background:#fff}
.il-section-head{text-align:center;max-width:760px;margin:0 auto 50px}
.il-section-head .eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;color:var(--red);
  margin-bottom:18px;
}
.il-section-head .eyebrow::before,
.il-section-head .eyebrow::after{
  content:'';width:30px;height:1px;background:var(--red);
}
.il-section-head h2{
  font-family:var(--serif);font-size:clamp(34px, 4vw, 52px);
  font-weight:500;letter-spacing:-1px;line-height:1.1;
  margin:0 0 16px;color:var(--navy);
}
.il-section-head h2 em{font-style:italic;color:var(--red)}
.il-section-head p{
  font-family:var(--sans);font-size:15px;line-height:1.65;
  color:#5a5a5a;margin:0;
}

.il-products{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;background:#fff;border:1px solid var(--line);
}
@media (max-width:900px){.il-products{grid-template-columns:repeat(2,1fr)}}
@media (max-width:480px){.il-products{grid-template-columns:1fr}}
.il-product{
  text-decoration:none;color:inherit;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  display:flex;flex-direction:column;
  transition:.25s;background:#fff;
}
.il-product:hover{
  background:var(--paper);transform:translateY(-3px);
  box-shadow:0 12px 28px rgba(5,13,36,.1);
}
.il-product-img{
  height:170px;background:var(--navy-2);position:relative;overflow:hidden;
}
.il-product-img img{
  width:100%;height:100%;object-fit:cover;
  transition:.5s;
}
.il-product:hover .il-product-img img{transform:scale(1.06)}
.il-product-body{padding:18px 20px}
.il-product-cat{
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:1.8px;text-transform:uppercase;color:var(--gold);
  margin-bottom:6px;
}
.il-product h3{
  font-family:var(--serif);font-size:18px;font-weight:600;
  letter-spacing:-.2px;line-height:1.25;
  color:var(--navy);margin:0 0 8px;
}
.il-product-link{
  font-family:var(--sans);font-size:10.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;color:var(--red);
}

/* WHY US */
.il-why{
  display:grid;grid-template-columns:repeat(4, 1fr);gap:0;
  background:#fff;border:1px solid var(--line);
  border-top:4px solid var(--gold);
}
@media (max-width:900px){.il-why{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.il-why{grid-template-columns:1fr}}
.il-why-item{
  padding:36px 28px;text-align:center;
  border-right:1px solid var(--line);
  transition:.25s;
}
.il-why-item:last-child{border-right:0}
@media (max-width:900px){
  .il-why-item:nth-child(2){border-right:0}
  .il-why-item:nth-child(3){border-top:1px solid var(--line);border-right:1px solid var(--line)}
  .il-why-item:nth-child(4){border-top:1px solid var(--line)}
}
.il-why-item:hover{background:var(--paper)}
.il-why-icon{
  width:54px;height:54px;
  margin:0 auto 18px;
  border:1.5px solid var(--gold);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  transition:.2s;
}
.il-why-item:hover .il-why-icon{background:var(--gold);color:var(--navy)}
.il-why-icon svg{width:24px;height:24px}
.il-why-item h3{
  font-family:var(--serif);font-size:20px;font-weight:600;
  margin:0 0 10px;color:var(--navy);
}
.il-why-item p{
  font-family:var(--sans);font-size:13px;line-height:1.6;
  color:#5a5a5a;margin:0;
}

/* SEO TEXT - long form content */
.il-seo-text{
  max-width:880px;margin:0 auto;
}
.il-seo-text h2{
  font-family:var(--serif);font-size:30px;font-weight:600;
  color:var(--navy);margin:32px 0 14px;letter-spacing:-.3px;
  border-left:3px solid var(--gold);padding-left:14px;
}
.il-seo-text h2:first-child{margin-top:0}
.il-seo-text p{
  font-family:var(--sans);font-size:15px;line-height:1.75;
  color:#3a3a3a;margin:0 0 16px;
}
.il-seo-text ul{
  margin:0 0 20px;padding:0;list-style:none;
}
.il-seo-text ul li{
  position:relative;padding-left:22px;margin-bottom:8px;
  font-family:var(--sans);font-size:14.5px;line-height:1.65;color:#3a3a3a;
}
.il-seo-text ul li::before{
  content:'';position:absolute;left:0;top:9px;
  width:8px;height:8px;background:var(--gold);transform:rotate(45deg);
}

/* CTA */
.il-cta{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;padding:80px 0;
  position:relative;overflow:hidden;
  border-top:4px solid var(--gold);
}
.il-cta-inner{
  text-align:center;max-width:680px;margin:0 auto;
  position:relative;z-index:2;
}
.il-cta h2{
  font-family:var(--serif);font-size:clamp(32px, 4vw, 46px);
  font-weight:500;font-style:italic;letter-spacing:-.5px;
  line-height:1.15;margin:0 0 16px;color:#fff;
}
.il-cta h2 strong{font-style:normal;color:var(--gold);font-weight:600}
.il-cta p{
  font-family:var(--sans);font-size:15px;line-height:1.7;
  color:rgba(255,255,255,.75);margin:0 0 32px;
}
.il-cta-actions{
  display:flex;justify-content:center;gap:14px;flex-wrap:wrap;
}
.il-btn{
  display:inline-block;padding:18px 36px;
  font-family:var(--sans);font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;transition:.2s;border:1px solid transparent;
}
.il-btn-primary{background:var(--red);color:#fff;border-color:var(--red)}
.il-btn-primary:hover{background:var(--red-dark);transform:translateY(-2px);box-shadow:0 12px 26px rgba(200,16,46,.4)}
.il-btn-ghost{background:transparent;color:var(--gold);border-color:var(--gold)}
.il-btn-ghost:hover{background:var(--gold);color:var(--navy);transform:translateY(-2px)}
</style>

<div class="il-page">

  <!-- HERO -->
  <section class="il-hero">
    <div class="container">
      <div class="il-hero-eyebrow">İl Tedarik Sayfası</div>
      <h1><?= h($il['name']) ?> <em>Demir Çelik</em><br>Tedarikçiniz</h1>
      <p class="il-hero-lead">
        <?= h($il['name']) ?> ve çevresine sac, boru, profil, hadde, demir, panel ve özel ölçü çelik ürünleri tedariği. <?= h($il['industry_focus']) ?>.
      </p>
      <div class="il-hero-stats">
        <div class="il-stat"><strong><?= h($il['population'] ?: '—') ?></strong><span>Şehir Nüfusu</span></div>
        <div class="il-stat"><strong>20+ Yıl</strong><span>Tedarik Deneyimi</span></div>
        <div class="il-stat"><strong>1.000+</strong><span>Ürün Çeşidi</span></div>
        <div class="il-stat"><strong>%100</strong><span>Sertifikalı</span></div>
      </div>
    </div>
  </section>

  <!-- INTRO -->
  <section class="il-intro">
    <div class="container">
      <div class="il-intro-grid">
        <div class="il-intro-left">
          <div class="eyebrow">Bölgesel Hizmet</div>
          <h2><?= h($il['name']) ?>'<em>a Özel</em><br>Demir Çelik Çözümleri</h2>
        </div>
        <div class="il-intro-right">
          <p><?= h($il['intro_text']) ?></p>
          <p><strong><?= h($il['cargo_info']) ?></strong> Konya merkez stoğumuzdan <?= h($il['name']) ?> ve çevresine düzenli sevkiyat sağlıyoruz. Belirli tutar üzeri siparişlerde nakliye dahil teklif sunabiliyoruz.</p>
          <div class="il-intro-tags">
            <span class="il-intro-tag"><strong>📦</strong> Stok Hazır</span>
            <span class="il-intro-tag"><strong>📜</strong> Üretici Sertifikalı</span>
            <span class="il-intro-tag"><strong>🚚</strong> Hızlı Sevkiyat</span>
            <span class="il-intro-tag"><strong>💰</strong> Rekabetçi Fiyat</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- PRODUCTS -->
  <section class="il-section">
    <div class="container">
      <div class="il-section-head">
        <div class="eyebrow"><?= h($il['name']) ?> Ürün Yelpazesi</div>
        <h2>En Çok Talep Edilen <em>Demir Çelik</em> Ürünleri</h2>
        <p><?= h($il['name']) ?>'da en çok tedarik ettiğimiz ürün grupları. Stoğumuzda olmayan özel ölçü ve kaliteler için 24-72 saat tedarik mümkün.</p>
      </div>

      <div class="il-products">
        <?php foreach ($products as $p): ?>
        <a class="il-product" href="<?= h(url('il-urun.php?il=' . $il['slug'] . '&urun=' . $p['slug'])) ?>">
          <div class="il-product-img">
            <?php if (!empty($p['image'])): ?>
              <img src="<?= h(img_url($p['image'])) ?>" alt="<?= h($p['name']) ?> <?= h($il['name']) ?>" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="il-product-body">
            <div class="il-product-cat"><?= h($p['cat_name']) ?></div>
            <h3><?= h($p['name']) ?></h3>
            <span class="il-product-link"><?= h($il['name']) ?>'da Tedarik →</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- WHY US -->
  <section class="il-section alt">
    <div class="container">
      <div class="il-section-head">
        <div class="eyebrow">Neden Tekcan Metal?</div>
        <h2><?= h($il['name']) ?>'<em>da Neden Bizi</em> Tercih Etmelisiniz?</h2>
      </div>
      <div class="il-why">
        <div class="il-why-item">
          <div class="il-why-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <h3>Hızlı Teslimat</h3>
          <p><?= h($il['cargo_info']) ?></p>
        </div>
        <div class="il-why-item">
          <div class="il-why-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/><path d="M9 12l2 2 4-4"/></svg>
          </div>
          <h3>Üretici Sertifikalı</h3>
          <p>Erdemir, Borçelik, Habaş ve diğer Türkiye lider üreticilerinden sertifikalı tedarik.</p>
        </div>
        <div class="il-why-item">
          <div class="il-why-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <h3>Rekabetçi Fiyat</h3>
          <p>Üretici fiyatına yakın oranlar, toplu siparişte ek indirim, vadeli ödeme imkânı.</p>
        </div>
        <div class="il-why-item">
          <div class="il-why-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          </div>
          <h3>Anında Destek</h3>
          <p>Telefon ve WhatsApp ile satış ekibine 7 saat içinde direkt ulaşım.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SEO RICH TEXT -->
  <section class="il-section">
    <div class="container">
      <div class="il-seo-text">
        <h2><?= h($il['name']) ?>'da Demir Çelik Tedariği</h2>
        <p>Tekcan Metal olarak <?= h($il['name']) ?> ve çevresindeki sanayicilere, müteahhitlere, makine imalatçılarına ve bireysel müşterilere demir-çelik ürün tedariği sağlıyoruz. <?= h($il['name']) ?>'daki <?= h($il['industry_focus']) ?> sektörlerinin gereksinimlerine yönelik geniş stoğumuz ve hızlı sevkiyat ağımızla hizmetinizdeyiz.</p>

        <h2><?= h($il['name']) ?>'da Hangi Ürünleri Tedarik Ediyoruz?</h2>
        <ul>
          <li><strong>Sac Ürünleri:</strong> Siyah sac, DKP sac, HRP sac, ST-52 sac, galvanizli sac — 1 mm ile 100 mm arası tüm kalınlıklar</li>
          <li><strong>Boru Çeşitleri:</strong> Su borusu, kazan borusu, konstrüksiyon borusu — 1/2" ile 4" arası</li>
          <li><strong>Profiller:</strong> Kare profil, dikdörtgen profil, oval profil — 20×20 ile 200×200 mm aralığında</li>
          <li><strong>Hadde Ürünleri:</strong> Lama, köşebent, HEA/HEB profil, NPI/NPU profil, kare demiri</li>
          <li><strong>İnşaat Demiri:</strong> Nervürlü inşaat demiri (Ø8-Ø32), Q ve R tipi çelik hasır</li>
          <li><strong>Özel Ürünler:</strong> Patent dirsek, norm flanş, petek kiriş, çatı paneli, cephe paneli</li>
        </ul>

        <h2><?= h($il['name']) ?>'a Sevkiyat Sürecimiz</h2>
        <p>Konya merkez stoğumuzdan <?= h($il['name']) ?>'a düzenli sevkiyat hattımız mevcuttur. Sipariş onayından sonra <strong><?= h($il['cargo_info']) ?></strong> Belirli tonaj üzeri siparişlerde direkt yük seferi düzenliyoruz; daha küçük siparişler için anlaşmalı nakliyat firmalarımızla parça yük sevkiyat çözümü sunuyoruz.</p>

        <h2>Neden Tekcan Metal?</h2>
        <p>2005'ten bu yana Konya merkezli faaliyet gösteren Tekcan Metal, Türkiye'nin önde gelen entegre çelik üretim tesislerinden (Erdemir, Borçelik, Habaş, İçdaş, Tosyalı Çelik, Kardemir) doğrudan tedarik gücüyle <?= h($il['name']) ?> müşterilerine fiyat avantajı ve üretici sertifikalı ürün güvencesi sunmaktadır.</p>
        <p>Sertifikalı tedarik, hassas ölçüye göre kesim/işleme atölye desteği, 81 il sevkiyat ağı ve 7/24 satış desteği ile <?= h($il['name']) ?>'daki müşterilerimizin uzun vadeli iş ortağıyız.</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="il-cta">
    <div class="container">
      <div class="il-cta-inner">
        <h2><?= h($il['name']) ?>'a <strong>Ürün Tedariği</strong> İçin Bize Yazın</h2>
        <p>Aradığınız ürün için aynı gün teklif, hassas ölçü gerekiyorsa atölye desteği, taze stok ve hızlı sevkiyat — hepsi tek mesaj uzağınızda.</p>
        <div class="il-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="il-btn il-btn-primary">Teklif İste</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ' . $il['name'] . ' için demir-çelik ürün tedariği hakkında bilgi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="il-btn il-btn-ghost">💬 WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
