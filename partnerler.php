<?php
require __DIR__ . '/includes/db.php';
$partners = all("SELECT * FROM tm_partners WHERE is_active=1 ORDER BY sort_order, name");
$pageTitle = 'Çözüm Ortaklarımız';
$metaDesc  = 'Türkiye\'nin önde gelen demir-çelik üreticileri ile stratejik tedarik ortaklıklarımız. Borçelik, Erdemir, Habaş ve daha fazlası.';
require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.pn-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --gold:#c9a86b;
  --red:#c8102e;
  --paper:#fafaf7;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* HERO — Global stage */
.pn-hero{
  background:
    linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%);
  color:#fff;
  padding:120px 0 100px;
  position:relative;
  overflow:hidden;
  border-bottom:4px solid var(--gold);
}
.pn-hero::before{
  /* World map dots pattern */
  content:'';position:absolute;
  inset:0;
  background-image:radial-gradient(circle, rgba(201,168,107,.15) 1px, transparent 1px);
  background-size:20px 20px;
  pointer-events:none;
  opacity:.4;
}
.pn-hero::after{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(ellipse at center, transparent 0%, rgba(5,13,36,.6) 70%, var(--navy) 100%);
  pointer-events:none;
}
.pn-hero .container{position:relative;z-index:2;text-align:center}
.pn-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:30px;
}
.pn-hero-eyebrow::before,
.pn-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.pn-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 86px);
  font-weight:500;
  line-height:1.05;
  letter-spacing:-1.5px;
  margin:0 0 24px;
  color:#fff;
}
.pn-hero h1 em{font-style:italic;color:var(--gold)}
.pn-hero-lead{
  font-family:var(--sans);
  font-size:17px;
  line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;
  margin:0 auto 40px;
}
.pn-hero-stats{
  display:flex;
  justify-content:center;
  gap:50px;
  flex-wrap:wrap;
  padding-top:40px;
  border-top:1px solid rgba(255,255,255,.12);
}
.pn-hero-stat{text-align:center}
.pn-hero-stat strong{
  display:block;
  font-family:var(--serif);
  font-size:40px;
  font-weight:500;
  color:var(--gold);
  line-height:1;
  letter-spacing:-1px;
}
.pn-hero-stat span{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:rgba(255,255,255,.6);
  margin-top:8px;
  display:block;
}

/* MANIFESTO */
.pn-manifesto{
  background:#fff;
  padding:90px 0;
  text-align:center;
  border-bottom:1px solid var(--line);
  position:relative;
}
.pn-manifesto::before{
  content:'“';
  position:absolute;
  top:30px;left:50%;
  transform:translateX(-50%);
  font-family:var(--serif);
  font-size:240px;
  color:var(--red);
  opacity:.06;
  line-height:1;
  pointer-events:none;
}
.pn-manifesto-inner{
  max-width:880px;margin:0 auto;
  position:relative;z-index:2;
}
.pn-manifesto-eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;
  color:var(--red);
  margin-bottom:30px;
  display:inline-flex;align-items:center;gap:14px;
}
.pn-manifesto-eyebrow::before,
.pn-manifesto-eyebrow::after{
  content:'';width:30px;height:1px;background:var(--red);
}
.pn-manifesto-quote{
  font-family:var(--serif);
  font-size:clamp(28px, 3.4vw, 42px);
  font-weight:400;
  font-style:italic;
  line-height:1.3;
  color:var(--navy);
  margin:0 0 30px;
  letter-spacing:-.3px;
}
.pn-manifesto-quote strong{
  color:var(--navy-2);
  font-style:normal;
  font-weight:600;
}

/* PARTNER GRID — Premium showcase */
.pn-partners-section{
  background:var(--paper);
  padding:90px 0;
}
.pn-partners-head{
  text-align:center;max-width:680px;margin:0 auto 60px;
}
.pn-partners-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--red);
  margin-bottom:18px;display:inline-block;
}
.pn-partners-head h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4vw, 56px);
  font-weight:500;letter-spacing:-1px;
  margin:0 0 18px;
  color:var(--navy);line-height:1.1;
}
.pn-partners-head h2 em{font-style:italic;color:var(--red)}
.pn-partners-head p{
  font-family:var(--sans);
  font-size:15px;
  line-height:1.65;
  color:#5a5a5a;margin:0;
}
.pn-grid{
  display:grid;
  grid-template-columns:repeat(3, 1fr);
  gap:0;
  background:#fff;
  border:1px solid var(--line);
}
@media (max-width:900px){.pn-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.pn-grid{grid-template-columns:1fr}}
.pn-card{
  padding:40px 30px;
  border-right:1px solid var(--line);
  border-bottom:1px solid var(--line);
  background:#fff;
  text-align:center;
  text-decoration:none;
  color:inherit;
  position:relative;
  transition:.25s;
  display:flex;
  flex-direction:column;
  align-items:center;
  min-height:280px;
}
.pn-card:hover{
  background:var(--paper);
  transform:translateY(-3px);
  box-shadow:0 12px 28px rgba(5,13,36,.08);
  z-index:2;
}
.pn-card-num{
  position:absolute;
  top:14px;left:18px;
  font-family:var(--serif);
  font-size:13px;
  font-style:italic;
  color:var(--gold);
  font-weight:600;
}
.pn-card-logo{
  width:140px;height:80px;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:20px;
  filter:grayscale(100%) opacity(.75);
  transition:.3s;
}
.pn-card:hover .pn-card-logo{
  filter:grayscale(0%) opacity(1);
}
.pn-card-logo img{
  max-width:100%;max-height:100%;
  object-fit:contain;
}
.pn-card-initial{
  width:80px;height:80px;
  background:var(--navy);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);
  font-size:36px;font-weight:600;
  letter-spacing:-1px;
}
.pn-card h3{
  font-family:var(--serif);
  font-size:24px;font-weight:600;
  margin:0 0 10px;
  color:var(--navy);
  letter-spacing:-.3px;
}
.pn-card-tagline{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:14px;
}
.pn-card p{
  font-family:var(--sans);
  font-size:13.5px;
  line-height:1.65;
  color:#5a5a5a;
  margin:0 0 18px;
  flex:1;
}
.pn-card-link{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  color:var(--red);
  display:inline-flex;align-items:center;gap:6px;
  margin-top:auto;
  padding-top:14px;
  border-top:1px solid var(--line);
  width:100%;
  justify-content:center;
}
.pn-card:hover .pn-card-link{color:var(--navy)}

/* TRUST POINTS */
.pn-trust{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  padding:90px 0;
  color:#fff;
  position:relative;overflow:hidden;
}
.pn-trust::before{
  content:'';position:absolute;
  inset:0;
  background-image:
    radial-gradient(ellipse at 20% 0%, rgba(20,54,114,.4) 0%, transparent 50%),
    radial-gradient(ellipse at 80% 100%, rgba(200,16,46,.15) 0%, transparent 50%);
  pointer-events:none;
}
.pn-trust-head{text-align:center;max-width:680px;margin:0 auto 60px;position:relative;z-index:2}
.pn-trust-head .eyebrow{color:var(--gold)}
.pn-trust-head h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4vw, 52px);
  font-weight:500;letter-spacing:-.5px;color:#fff;margin:0 0 16px;line-height:1.1;
}
.pn-trust-head h2 em{font-style:italic;color:var(--gold)}
.pn-trust-head p{
  font-family:var(--sans);
  font-size:15px;color:rgba(255,255,255,.7);line-height:1.65;margin:0;
}
.pn-trust-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
  position:relative;z-index:2;
  border-top:1px solid rgba(255,255,255,.1);
  border-bottom:1px solid rgba(255,255,255,.1);
}
@media (max-width:900px){.pn-trust-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.pn-trust-grid{grid-template-columns:1fr}}
.pn-trust-item{
  padding:40px 30px;
  border-right:1px solid rgba(255,255,255,.1);
  text-align:center;
  position:relative;
  transition:.25s;
}
.pn-trust-item:last-child{border-right:0}
@media (max-width:900px){
  .pn-trust-item:nth-child(2){border-right:0}
  .pn-trust-item:nth-child(3){border-right:1px solid rgba(255,255,255,.1)}
}
.pn-trust-item:hover{background:rgba(255,255,255,.02)}
.pn-trust-icon{
  width:60px;height:60px;
  margin:0 auto 18px;
  border:1px solid var(--gold);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
}
.pn-trust-icon svg{width:26px;height:26px}
.pn-trust-item h3{
  font-family:var(--serif);
  font-size:22px;font-weight:600;
  margin:0 0 10px;color:#fff;
  letter-spacing:-.2px;
}
.pn-trust-item p{
  font-family:var(--sans);
  font-size:13px;
  line-height:1.65;
  color:rgba(255,255,255,.65);margin:0;
}

/* CTA */
.pn-cta{
  background:#fff;padding:90px 0;
  text-align:center;
  border-top:4px solid var(--gold);
}
.pn-cta-inner{max-width:760px;margin:0 auto}
.pn-cta h2{
  font-family:var(--serif);
  font-size:clamp(36px, 4vw, 52px);
  font-weight:500;
  font-style:italic;
  letter-spacing:-.5px;
  margin:0 0 16px;
  color:var(--navy);
  line-height:1.15;
}
.pn-cta h2 strong{font-style:normal;color:var(--red)}
.pn-cta p{
  font-family:var(--sans);
  font-size:15px;line-height:1.65;
  color:#5a5a5a;margin:0 0 36px;
}
.pn-cta-actions{
  display:flex;gap:14px;justify-content:center;flex-wrap:wrap;
}
.pn-btn{
  display:inline-block;
  padding:18px 38px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:2px;text-transform:uppercase;
  text-decoration:none;
  transition:.2s;
  border:1px solid transparent;
}
.pn-btn-primary{
  background:var(--navy);color:#fff;border-color:var(--navy);
}
.pn-btn-primary:hover{
  background:var(--gold);color:var(--navy);border-color:var(--gold);
  transform:translateY(-2px);
}
.pn-btn-ghost{
  background:transparent;color:var(--navy);border-color:var(--navy);
}
.pn-btn-ghost:hover{
  background:var(--navy);color:#fff;
  transform:translateY(-2px);
}
</style>

<div class="pn-page">

  <!-- HERO -->
  <section class="pn-hero">
    <div class="container">
      <div class="pn-hero-eyebrow">Stratejik İş Birlikleri</div>
      <h1>Türkiye'nin Çelik <em>Devleri</em><br>Yetkili Temsilciliği</h1>
      <p class="pn-hero-lead">
        Sektörün lider entegre çelik üretim tesislerinin yetkili temsilcileri olarak, tedarik gücümüzü doğrudan üreticilerden alıyor, müşterilerimize üretici garantisiyle sunuyoruz.
      </p>
      <div class="pn-hero-stats">
        <div class="pn-hero-stat"><strong><?= count($partners) ?></strong><span>Çözüm Ortağı</span></div>
        <div class="pn-hero-stat"><strong>21+</strong><span>Yıllık Tedarik</span></div>
        <div class="pn-hero-stat"><strong>81</strong><span>İl Sevkiyat Ağı</span></div>
        <div class="pn-hero-stat"><strong>%100</strong><span>Üretici Garantili</span></div>
      </div>
    </div>
  </section>

  <!-- MANIFESTO -->
  <section class="pn-manifesto">
    <div class="container">
      <div class="pn-manifesto-inner">
        <div class="pn-manifesto-eyebrow">Tedarik Felsefemiz</div>
        <p class="pn-manifesto-quote">
          Sahte ürün, fason kalite, belirsiz menşe ile çalışmayız. Sektörün <strong>en büyük üreticilerinin</strong> yetkili temsilcisi olarak müşterimize ulaştırdığımız her parti, doğrudan üretici fabrikadan, sertifikalı, taze stoğumuzdan çıkar.
        </p>
        <div style="display:inline-block;font-family:var(--sans);font-size:11.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:#3a3a3a;padding-top:20px;border-top:1px solid var(--line)">— Tekcan Metal Tedarik Politikası</div>
      </div>
    </div>
  </section>

  <!-- PARTNERS GRID -->
  <section class="pn-partners-section">
    <div class="container">
      <div class="pn-partners-head">
        <div class="eyebrow">Çözüm Ortaklarımız</div>
        <h2>Birlikte Çalıştığımız <em>Üreticiler</em></h2>
        <p>Borçelik, Erdemir, Habaş, Tosyalı Çelik, Kardemir ve İçdaş başta olmak üzere sektörün önde gelen entegre çelik üretim tesislerinin yetkili temsilciliği.</p>
      </div>

      <?php if (!$partners): ?>
        <p style="text-align:center;font-family:var(--serif);font-style:italic;color:#5a5a5a">Çözüm ortağı listesi yenileniyor.</p>
      <?php else: ?>
      <div class="pn-grid">
        <?php foreach ($partners as $i => $p): ?>
        <?php $href = !empty($p['website']) ? $p['website'] : null; ?>
        <?= $href ? '<a class="pn-card" href="' . h($href) . '" target="_blank" rel="noopener">' : '<div class="pn-card">' ?>
          <span class="pn-card-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?> —</span>
          <div class="pn-card-logo">
            <?php if (!empty($p['logo'])): ?>
              <img src="<?= h(img_url($p['logo'])) ?>" alt="<?= h($p['name']) ?>">
            <?php else: ?>
              <span class="pn-card-initial"><?= h(mb_strtoupper(mb_substr($p['name'], 0, 2, 'UTF-8'), 'UTF-8')) ?></span>
            <?php endif; ?>
          </div>
          <h3><?= h($p['name']) ?></h3>
          <div class="pn-card-tagline">Yetkili Temsilci</div>
          <?php if (!empty($p['description'])): ?>
            <p><?= h($p['description']) ?></p>
          <?php else: ?>
            <p>Türkiye'nin önde gelen demir-çelik üreticilerinden, üretici garantisiyle sertifikalı tedarik.</p>
          <?php endif; ?>
          <?php if ($href): ?>
            <span class="pn-card-link">Web Sitesini Ziyaret Et <span>↗</span></span>
          <?php else: ?>
            <span class="pn-card-link">Yetkili Tedarikçi <span>✓</span></span>
          <?php endif; ?>
        <?= $href ? '</a>' : '</div>' ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- TRUST POINTS -->
  <section class="pn-trust">
    <div class="container">
      <div class="pn-trust-head">
        <div class="eyebrow">Tedarik Garantisi</div>
        <h2>Üretici Ortaklığının <em>Avantajları</em></h2>
        <p>Sektör lideri üreticilerle direkt çalışmanın size sağladığı somut faydalar.</p>
      </div>
      <div class="pn-trust-grid">

        <div class="pn-trust-item">
          <div class="pn-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <h3>Üretici Sertifikalı</h3>
          <p>Her parti, üretici fabrikadan menşei belgesi, kalite belgesi ve test raporları ile birlikte çıkar.</p>
        </div>

        <div class="pn-trust-item">
          <div class="pn-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
          <h3>Direkt Üretici Fiyatı</h3>
          <p>Aracılı tedarik zinciri yok. Üreticiden alıyoruz, müşteriye ulaştırıyoruz — fiyat avantajı sizin.</p>
        </div>

        <div class="pn-trust-item">
          <div class="pn-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3>Geniş Stok</h3>
          <p>Üretici partner ağımız sayesinde stoğumuzda olmayan ürün için 24-48 saat içinde tedarik mümkün.</p>
        </div>

        <div class="pn-trust-item">
          <div class="pn-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <h3>Hızlı Sevkiyat</h3>
          <p>Konya merkezli stoğumuz + üretici depolarına yakınlık = 81 ile aynı hafta teslimat.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="pn-cta">
    <div class="container">
      <div class="pn-cta-inner">
        <h2>Hangi marka, <strong>hangi ürün</strong> ihtiyacınız var?</h2>
        <p>Anlaşmalı üreticilerimizden istediğiniz ürünü tedarik etmek için bizimle iletişime geçin. Aynı gün teklif, hızlı sevkiyat, üretici garantili teslim.</p>
        <div class="pn-cta-actions">
          <a href="<?= h(url('iletisim.php')) ?>" class="pn-btn pn-btn-primary">Teklif İste</a>
          <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, ürün/teklif almak istiyorum.')) ?>" target="_blank" rel="noopener" class="pn-btn pn-btn-ghost">WhatsApp ile İletişim</a>
        </div>
      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
