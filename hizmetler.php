<?php
require __DIR__ . '/includes/db.php';
$services = all("SELECT * FROM tm_services WHERE is_active=1 ORDER BY sort_order");
$pageTitle = 'Endüstriyel Yetkinliklerimiz';
$metaDesc  = 'Tekcan Metal hizmetleri: lazer kesim, oksijen kesim, dekoratif sac üretimi. CNC tabanlı tam donanımlı atölye yetkinliği.';
require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.hz-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --navy-3:#143672;
  --gold:#c9a86b;
  --gold-light:#e0c48a;
  --red:#c8102e;
  --paper:#fafaf7;
  --paper-2:#f3f1ec;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* HERO — Royal palace */
.hz-hero{
  background:
    linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%),
    url('<?= h(img_url('uploads/sliders/slider-2-laser.jpg')) ?>');
  background-size:cover;
  background-position:center;
  color:#fff;
  padding:140px 0 110px;
  border-bottom:4px solid var(--gold);
  position:relative;
  overflow:hidden;
}
.hz-hero::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(
    -45deg, transparent 0, transparent 4px,
    rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px);
  pointer-events:none;
}
.hz-hero::after{
  content:'';position:absolute;
  top:30px;right:30px;
  width:80px;height:80px;
  border:2px solid var(--gold);
  pointer-events:none;
}
.hz-hero .container{position:relative;z-index:2;text-align:center}
.hz-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:30px;
}
.hz-hero-eyebrow::before,
.hz-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.hz-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 90px);
  font-weight:500;line-height:1.05;letter-spacing:-1.5px;
  margin:0 0 24px;color:#fff;
}
.hz-hero h1 em{font-style:italic;color:var(--gold)}
.hz-hero-lead{
  font-family:var(--sans);font-size:17px;line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;margin:0 auto;
}

/* MANIFESTO STRIP */
.hz-strip{
  background:#fff;
  padding:60px 0;
  border-bottom:1px solid var(--line);
}
.hz-strip-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
  border-top:2px solid var(--navy);
  border-bottom:2px solid var(--navy);
}
@media (max-width:900px){.hz-strip-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.hz-strip-grid{grid-template-columns:1fr}}
.hz-strip-item{
  padding:32px 24px;
  text-align:center;
  border-right:1px solid var(--line);
}
.hz-strip-item:last-child{border-right:0}
@media (max-width:900px){
  .hz-strip-item:nth-child(2){border-right:0}
  .hz-strip-item:nth-child(3){border-right:1px solid var(--line);border-top:1px solid var(--line)}
  .hz-strip-item:nth-child(4){border-top:1px solid var(--line)}
}
.hz-strip-num{
  font-family:var(--serif);
  font-size:48px;font-weight:500;
  color:var(--navy);line-height:1;
  letter-spacing:-1.5px;margin-bottom:8px;
}
.hz-strip-num sup{
  font-size:24px;color:var(--red);font-weight:600;vertical-align:top;
}
.hz-strip-label{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  color:#5a5a5a;
}

/* SERVICES — alternating royal hero blocks */
.hz-services{
  background:var(--paper);
  padding:80px 0 100px;
}
.hz-services-head{
  text-align:center;max-width:680px;margin:0 auto 70px;
}
.hz-services-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--red);margin-bottom:18px;display:inline-block;
}
.hz-services-head h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4vw, 56px);
  font-weight:500;letter-spacing:-1px;
  margin:0 0 18px;color:var(--navy);line-height:1.1;
}
.hz-services-head h2 em{font-style:italic;color:var(--red)}

.hz-service{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:0;
  background:#fff;
  border:1px solid var(--line);
  margin-bottom:60px;
  border-top:4px solid var(--gold);
}
@media (max-width:900px){.hz-service{grid-template-columns:1fr}}
.hz-service.reverse > .hz-service-img{order:2}
@media (max-width:900px){.hz-service.reverse > .hz-service-img{order:0}}

.hz-service-img{
  position:relative;
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  min-height:380px;
  overflow:hidden;
}
.hz-service-img img{
  width:100%;height:100%;
  object-fit:cover;
  position:absolute;inset:0;
  filter:brightness(.85);
  transition:.6s;
}
.hz-service:hover .hz-service-img img{
  transform:scale(1.04);
  filter:brightness(.95);
}
.hz-service-img-placeholder{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);
  font-size:120px;
  font-weight:500;
  color:rgba(201,168,107,.15);
  letter-spacing:-4px;
}
.hz-service-img-num{
  position:absolute;
  top:24px;left:24px;
  font-family:var(--serif);
  font-size:14px;
  font-weight:600;
  font-style:italic;
  color:var(--gold);
  background:rgba(5,13,36,.6);
  padding:6px 14px;
  border:1px solid var(--gold);
  backdrop-filter:blur(6px);
  z-index:2;
}

.hz-service-body{
  padding:50px 50px;
  display:flex;
  flex-direction:column;
  justify-content:center;
}
@media (max-width:600px){.hz-service-body{padding:36px 28px}}
.hz-service-eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);margin-bottom:14px;
}
.hz-service-body h3{
  font-family:var(--serif);
  font-size:clamp(28px, 3vw, 40px);
  font-weight:500;letter-spacing:-.5px;
  line-height:1.1;
  margin:0 0 18px;
  color:var(--navy);
}
.hz-service-body h3 em{font-style:italic;color:var(--red)}
.hz-service-short{
  font-family:var(--sans);
  font-size:15px;
  line-height:1.65;
  color:#3a3a3a;margin:0 0 22px;
}
.hz-service-features{
  list-style:none;padding:0;margin:0 0 28px;
  display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;
}
@media (max-width:500px){.hz-service-features{grid-template-columns:1fr}}
.hz-service-features li{
  font-family:var(--sans);
  font-size:13.5px;
  color:#3a3a3a;
  padding-left:22px;
  position:relative;
}
.hz-service-features li::before{
  content:'';
  position:absolute;left:0;top:7px;
  width:8px;height:8px;
  background:var(--gold);
  transform:rotate(45deg);
}
.hz-service-cta{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;
  text-decoration:none;
  color:var(--navy);
  border:1.5px solid var(--navy);
  background:transparent;
  transition:.18s;
  width:fit-content;
}
.hz-service-cta:hover{
  background:var(--navy);color:#fff;
  transform:translateY(-2px);
}
.hz-service-cta span{transition:transform .2s}
.hz-service-cta:hover span{transform:translateX(4px)}

/* CAPABILITIES BAND */
.hz-cap{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  padding:90px 0;
  position:relative;overflow:hidden;
}
.hz-cap::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(circle at 50% 50%, rgba(201,168,107,.08) 0%, transparent 60%);
}
.hz-cap-head{text-align:center;max-width:680px;margin:0 auto 60px;position:relative;z-index:2}
.hz-cap-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);margin-bottom:18px;display:inline-block;
}
.hz-cap-head h2{
  font-family:var(--serif);
  font-size:clamp(34px, 4vw, 50px);
  font-weight:500;letter-spacing:-.5px;
  margin:0;color:#fff;line-height:1.1;
}
.hz-cap-head h2 em{font-style:italic;color:var(--gold)}
.hz-cap-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  position:relative;z-index:2;
  border-top:1px solid rgba(255,255,255,.1);
  border-bottom:1px solid rgba(255,255,255,.1);
}
@media (max-width:900px){.hz-cap-grid{grid-template-columns:1fr}}
.hz-cap-item{
  padding:50px 40px;
  border-right:1px solid rgba(255,255,255,.1);
  text-align:center;
  transition:.25s;
}
.hz-cap-item:last-child{border-right:0}
@media (max-width:900px){
  .hz-cap-item{border-right:0;border-bottom:1px solid rgba(255,255,255,.1)}
  .hz-cap-item:last-child{border-bottom:0}
}
.hz-cap-item:hover{background:rgba(255,255,255,.02)}
.hz-cap-icon{
  width:64px;height:64px;
  margin:0 auto 24px;
  border:1px solid var(--gold);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
}
.hz-cap-icon svg{width:30px;height:30px}
.hz-cap-item h3{
  font-family:var(--serif);
  font-size:24px;font-weight:600;
  margin:0 0 12px;color:#fff;letter-spacing:-.2px;
}
.hz-cap-item p{
  font-family:var(--sans);
  font-size:13.5px;line-height:1.65;
  color:rgba(255,255,255,.7);margin:0;
}

/* CTA */
.hz-cta{
  background:#fff;
  padding:90px 0;
  text-align:center;
  border-top:4px solid var(--gold);
}
.hz-cta-inner{max-width:760px;margin:0 auto}
.hz-cta h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4vw, 52px);
  font-weight:500;font-style:italic;letter-spacing:-.5px;
  margin:0 0 16px;color:var(--navy);line-height:1.15;
}
.hz-cta h2 strong{font-style:normal;color:var(--red)}
.hz-cta p{
  font-family:var(--sans);
  font-size:15px;line-height:1.65;
  color:#5a5a5a;margin:0 0 36px;
}
.hz-cta-actions{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.hz-btn{
  display:inline-block;padding:18px 38px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;transition:.2s;border:1px solid transparent;
}
.hz-btn-primary{background:var(--navy);color:#fff;border-color:var(--navy)}
.hz-btn-primary:hover{background:var(--gold);color:var(--navy);border-color:var(--gold);transform:translateY(-2px)}
.hz-btn-ghost{background:transparent;color:var(--navy);border-color:var(--navy)}
.hz-btn-ghost:hover{background:var(--navy);color:#fff;transform:translateY(-2px)}
</style>

<div class="hz-page">

  <!-- HERO -->
  <section class="hz-hero">
    <div class="container">
      <div class="hz-hero-eyebrow">Endüstriyel Yetkinlikler</div>
      <h1>Tedarikçinizden <em>Çözüm</em><br>Ortağınıza</h1>
      <p class="hz-hero-lead">
        Tekcan Metal'in tam donanımlı atölye yetkinlikleri ile sadece çelik tedarik etmiyor; ihtiyacınız olan biçim, ölçü ve hassasiyetle işlenmiş ürünleri kapınıza teslim ediyoruz.
      </p>
    </div>
  </section>

  <!-- STATS STRIP -->
  <section class="hz-strip">
    <div class="container">
      <div class="hz-strip-grid">
        <div class="hz-strip-item">
          <div class="hz-strip-num"><?= count($services) ?><sup>+</sup></div>
          <div class="hz-strip-label">Atölye Hizmeti</div>
        </div>
        <div class="hz-strip-item">
          <div class="hz-strip-num">24<sup>sa</sup></div>
          <div class="hz-strip-label">Aynı Gün Üretim</div>
        </div>
        <div class="hz-strip-item">
          <div class="hz-strip-num">%100</div>
          <div class="hz-strip-label">CNC Hassasiyet</div>
        </div>
        <div class="hz-strip-item">
          <div class="hz-strip-num">±0.1<sup>mm</sup></div>
          <div class="hz-strip-label">Tolerans Hassasiyeti</div>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section class="hz-services">
    <div class="container">
      <div class="hz-services-head">
        <div class="eyebrow">Hizmetlerimiz</div>
        <h2>Çelik İşleme <em>Sanatı</em></h2>
      </div>

      <?php if (!$services): ?>
        <p style="text-align:center;font-family:var(--serif);font-style:italic;color:#5a5a5a">Hizmet listesi yenileniyor.</p>
      <?php else: ?>
      <?php foreach ($services as $i => $s): ?>
      <article class="hz-service <?= $i % 2 === 1 ? 'reverse' : '' ?>">
        <div class="hz-service-img">
          <span class="hz-service-img-num">— <?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?> —</span>
          <?php if (!empty($s['image'])): ?>
            <img src="<?= h(img_url($s['image'])) ?>" alt="<?= h($s['title']) ?>" loading="lazy">
          <?php else: ?>
            <div class="hz-service-img-placeholder"><?= h(mb_strtoupper(mb_substr($s['title'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
          <?php endif; ?>
        </div>
        <div class="hz-service-body">
          <div class="hz-service-eyebrow"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?> · Atölye Hizmeti</div>
          <h3><?= h($s['title']) ?></h3>
          <?php if (!empty($s['short_desc'])): ?>
            <p class="hz-service-short"><?= h($s['short_desc']) ?></p>
          <?php endif; ?>
          <?php
          // Servis özelliklerini description'dan veya statik özelliklerden çıkar
          $features = [];
          $slug = $s['slug'] ?? '';
          if ($slug === 'lazer-kesim') {
              $features = ['CNC kontrollü lazer kesim','0,5 mm – 25 mm sac kalınlık','±0,1 mm tolerans','Hassas kontur kesimi','Aynı gün teslimat','DXF/DWG/STEP destek'];
          } elseif ($slug === 'oksijen-kesim') {
              $features = ['Kalın levha kesimi','5 mm – 200 mm aralık','Endüstriyel proje desteği','Plaka açma','Sıcak kesim hassasiyeti','Toplu üretim kapasitesi'];
          } elseif ($slug === 'dekoratif-sac' || str_contains($slug, 'dekorat')) {
              $features = ['CNC desen kesimi','Cephe ve mimari uygulamalar','Tasarım danışmanlığı','Galvanizli ve paslanmaz seçenekler','Sıcak daldırma kaplama','Korkuluk ve panel üretimi'];
          } else {
              $features = ['Profesyonel atölye ekipmanı','Deneyimli operatör kadro','Hassas tolerans kontrolü','Aynı gün üretim','Konya merkezli stok','Türkiye geneli sevkiyat'];
          }
          ?>
          <ul class="hz-service-features">
            <?php foreach ($features as $f): ?>
              <li><?= h($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="<?= h(url('hizmet.php?slug=' . urlencode($s['slug']))) ?>" class="hz-service-cta">Detaylı İncele <span>→</span></a>
        </div>
      </article>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- CAPABILITIES -->
  <section class="hz-cap">
    <div class="container">
      <div class="hz-cap-head">
        <div class="eyebrow">Atölye Kapasitemiz</div>
        <h2>Üç İlke Üzerinde <em>Yükseliriz</em></h2>
      </div>
      <div class="hz-cap-grid">

        <div class="hz-cap-item">
          <div class="hz-cap-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <circle cx="12" cy="12" r="6"/>
              <circle cx="12" cy="12" r="2" fill="currentColor"/>
            </svg>
          </div>
          <h3>Hassasiyet</h3>
          <p>CNC kontrollü makinelerimiz mikron toleransla çalışır. Hassasiyet bizim için bir lüks değil, standart.</p>
        </div>

        <div class="hz-cap-item">
          <div class="hz-cap-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <h3>Hız</h3>
          <p>Aynı gün sipariş, aynı gün üretim. 24 saat içinde nakil hazır. Sıkışık projelerde rakipsiziz.</p>
        </div>

        <div class="hz-cap-item">
          <div class="hz-cap-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <h3>Garanti</h3>
          <p>Yanlış üretilen parça bizim sorunumuzdur. Müşterimiz parça ile uğraşmaz, biz üretir, biz teslim ederiz.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="hz-cta">
    <div class="container">
      <div class="hz-cta-inner">
        <h2>Projeniz için <strong>özel teklif</strong> alın</h2>
        <p>DXF/DWG dosyanızı bize iletin, aynı gün hassas teklif sunalım. Lazer kesimden plaka açmaya kadar tüm atölye yetkinliklerimiz hizmetinizde.</p>
        <div class="hz-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="hz-btn hz-btn-primary">Teklif İste</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, atölye hizmetleri için fiyat teklifi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="hz-btn hz-btn-ghost">WhatsApp Teklif</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
