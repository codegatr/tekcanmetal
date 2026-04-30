<?php
/**
 * Marka adı yanlış yazım yakalama sayfası
 *
 * Search Console: 'tekman metal fiyat listesi' sorgusu için 333 gösterim/ay,
 * %2.4 CTR (8 tık). İnsanlar 'Tekcan' yerine 'Tekman' yazıyor.
 *
 * Bu sayfa kullanıcıyı doğru markaya yönlendirir + SEO için optimize edilir.
 *
 * URL: /tekman-metal.php
 *      veya .htaccess RedirectMatch ile /tekman-metal-fiyat-listesi/ → buraya
 */
require __DIR__ . '/includes/db.php';

$pageTitle = 'Tekman Metal mi? Tekcan Metal mi? Doğru Marka Adı';
$metaDesc  = 'Aradığınız "Tekman Metal" aslında Tekcan Metal\'dir. Konya merkezli demir-çelik tedarikçisi, sac, boru, profil, hadde fiyat listesi ve teklif.';

require __DIR__ . '/includes/header.php';
?>

<!-- Marka adı düzeltme schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Tekcan Metal",
  "alternateName": ["Tekman Metal", "Tekcan", "Tekcan Metal Sanayi", "Tekcan Demir Çelik"],
  "description": "Konya merkezli demir-çelik tedarikçisi. Doğru marka adı: Tekcan Metal Sanayi ve Ticaret Ltd. Şti.",
  "url": "https://tekcanmetal.com",
  "telephone": "+90 332 342 24 52",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS",
    "addressLocality": "Karatay",
    "addressRegion": "Konya",
    "addressCountry": "TR"
  }
}
</script>

<style>
.brand-correction {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-left: 4px solid #f59e0b;
  padding: 32px;
  border-radius: 12px;
  margin: 40px 0;
}
.brand-correction h2 {
  color: #92400e;
  margin-top: 0;
}
.brand-correction .arrow {
  font-size: 32px;
  color: #f59e0b;
  margin: 0 12px;
}
.brand-name-old {
  text-decoration: line-through;
  color: #991b1b;
  font-weight: 600;
}
.brand-name-correct {
  color: #166534;
  font-weight: 700;
  font-size: 1.2em;
}
.tekcan-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin: 32px 0;
}
.tekcan-info-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 24px;
}
.tekcan-info-card h3 { margin-top: 0; color: #050d24; }
</style>

<section class="page-header">
  <div class="container">
    <nav class="breadcrumb">
      <a href="/"><?= h(t('bc.home', 'Anasayfa')) ?></a> <span>›</span>
      <span>Tekman Metal — Doğru Marka Adı</span>
    </nav>
    <h1>Tekman Metal mi, Tekcan Metal mi?</h1>
    <p class="lead">Doğru marka adımız: <strong>Tekcan Metal</strong>. Konya'nın köklü demir-çelik tedarikçisi.</p>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">

    <div class="brand-correction">
      <h2>📌 Önemli Bilgi</h2>
      <p style="font-size: 1.15em; line-height: 1.7;">
        Aradığınız <span class="brand-name-old">Tekman Metal</span>
        <span class="arrow">→</span>
        <span class="brand-name-correct">Tekcan Metal</span>'dir.
      </p>
      <p>
        İsim benzerliği nedeniyle internet aramalarında zaman zaman <em>"Tekman Metal"</em>, <em>"Tekman Demir Çelik"</em> veya <em>"Tekman Metal fiyat listesi"</em> şeklinde aranıyoruz. Doğru marka adımız <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong>'dir.
      </p>
    </div>

    <h2>Tekcan Metal Hakkında</h2>
    <p>
      <strong>Tekcan Metal Sanayi ve Ticaret Ltd. Şti.</strong>, 2005 yılında Konya'da kurulmuş demir-çelik tedarik şirketidir. Karatay/Konya merkezimizden Türkiye geneline (81 il) sevkiyat yaparken, aynı zamanda Irak, Suriye, Azerbaycan ve Türkmenistan'a ihracat hizmeti sunuyoruz.
    </p>

    <div class="tekcan-info">

      <div class="tekcan-info-card">
        <h3>📞 İletişim</h3>
        <p><strong>Telefon:</strong> 0 332 342 24 52<br>
        <strong>WhatsApp:</strong> 0 532 065 24 00<br>
        <strong>E-posta:</strong> info@tekcanmetal.com<br>
        <strong>Adres:</strong> Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS — Karatay/KONYA</p>
      </div>

      <div class="tekcan-info-card">
        <h3>🏭 Ürün Yelpazemiz</h3>
        <ul>
          <li>Sac levha (DKP, HRP, galvanizli, paslanmaz, alüminyum)</li>
          <li>Boru (sanayi, galvanizli, doğalgaz, kazan)</li>
          <li>Profil (kutu, dikdörtgen, kare, köşebent)</li>
          <li>İnşaat demiri ve çelik hasır</li>
          <li>Trapez sac, baklava sac, genişletilmiş sac</li>
        </ul>
      </div>

      <div class="tekcan-info-card">
        <h3>🤝 Üretici Sertifikalı Tedarik</h3>
        <p>Türkiye'nin lider çelik üreticileriyle çözüm ortaklığımız:</p>
        <ul>
          <li>Erdemir</li>
          <li>Borçelik</li>
          <li>Habaş</li>
          <li>Tosyalı Çelik</li>
          <li>Kardemir</li>
          <li>İçdaş</li>
        </ul>
      </div>

      <div class="tekcan-info-card">
        <h3>⚡ Avantajlarımız</h3>
        <ul>
          <li>20+ yıl sektör tecrübesi</li>
          <li>Aynı gün sevkiyat (Konya merkez)</li>
          <li>Lazer + oksijen kesim atölyesi</li>
          <li>Anlık fiyat hesaplama (online)</li>
          <li>e-Fatura, üretici sertifikası</li>
        </ul>
      </div>

    </div>

    <h2>Tekman Metal Olarak Aramış Olabilirsiniz Çünkü...</h2>
    <p>
      Bazı müşterilerimiz markamızı <strong>"Tekman Metal"</strong> şeklinde hatırlıyor. Bunun nedenleri:
    </p>
    <ul>
      <li>Telefonla işitildiğinde 'Tekcan' ve 'Tekman' kulağa benzer geliyor</li>
      <li>Sektörde 'Te-' ile başlayan başka markalar mevcut</li>
      <li>Konya bölgesinde 'kan' eki yerine 'man' eki yaygın kullanılıyor</li>
    </ul>

    <p>
      <strong>Doğru bilgiye buradan ulaştığınız için memnunuz!</strong> Aradığınız fiyat listesi, ürün katalogu ve teklif için aşağıdaki bağlantıları kullanabilirsiniz:
    </p>

    <div class="cta-strip" style="margin-top:48px">
      <div class="cta-text">
        <h3>Aradığınız Tekcan Metal'di — Hoş geldiniz!</h3>
        <p>Demir-çelik tedariği, fiyat listesi, ağırlık hesaplama ve sevkiyat için iletişime geçebilirsiniz.</p>
      </div>
      <div class="cta-actions">
        <a href="/urunler.php" class="btn btn-primary">Ürünlerimize Bak</a>
        <a href="/hesaplama.php" class="btn btn-ghost">Ağırlık Hesaplama</a>
        <a href="/iletisim.php" class="btn btn-ghost">İletişim & Teklif</a>
      </div>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
