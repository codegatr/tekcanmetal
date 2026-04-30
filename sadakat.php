<?php
require __DIR__ . '/includes/db.php';

$pageTitle = 'Sadakat Programı';
$metaDesc  = 'Tekcan Metal Sadakat Programı — düzenli müşterilerimize özel indirim, öncelikli sevkiyat ve özel kampanyalar.';

$errors = [];
$old = ['full_name'=>'','company_name'=>'','phone'=>'','email'=>'','tax_id'=>'','city'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Oturum doğrulaması başarısız oldu.';
    } else {
        foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');

        if ($old['full_name'] === '')                          $errors[] = 'Ad Soyad zorunlu.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta giriniz.';
        if ($old['phone'] === '')                              $errors[] = 'Telefon zorunlu.';
        if (empty($_POST['kvkk']))                             $errors[] = 'KVKK onayı zorunludur.';
        if (!empty($_POST['website']))                         $errors[] = 'İstek reddedildi.';

        if (!$errors) {
            try {
                q("INSERT INTO tm_loyalty_members (full_name,company_name,phone,email,tax_id,city)
                   VALUES (?,?,?,?,?,?)
                   ON DUPLICATE KEY UPDATE
                     full_name=VALUES(full_name), company_name=VALUES(company_name),
                     phone=VALUES(phone), tax_id=VALUES(tax_id), city=VALUES(city)",
                  [$old['full_name'], $old['company_name'], $old['phone'], $old['email'], $old['tax_id'], $old['city']]);
                flash('success', 'Sadakat programına kayıt olduğunuz için teşekkürler. Üyelik avantajlarınız için en kısa sürede sizinle iletişime geçeceğiz.');
                redirect('sadakat.php?ok=1');
            } catch (Throwable $e) {
                $errors[] = 'Kayıt oluşturulamadı.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.sad-page{
  --gold:#c9a86b;
  --gold-light:#e0c48a;
  --gold-dark:#a88a4a;
  --navy:#050d24;
  --navy-2:#0c1e44;
  --paper:#fafaf7;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
  color:#1a1a1a;
}

/* HERO — Royal */
.sad-hero{
  background:
    linear-gradient(135deg, rgba(5,13,36,.96) 0%, rgba(12,30,68,.92) 100%),
    url('<?= h(img_url('uploads/pages/sadakat.jpg')) ?>');
  background-size:cover;
  background-position:center;
  color:#fff;
  padding:130px 0 100px;
  border-bottom:4px solid var(--gold);
  position:relative;
  overflow:hidden;
}
.sad-hero::after{
  content:'';position:absolute;
  top:30px;right:30px;
  width:80px;height:80px;
  border:2px solid var(--gold);
}
.sad-hero::before{
  content:'';position:absolute;
  bottom:30px;left:30px;
  width:80px;height:80px;
  border:2px solid var(--gold);
}
.sad-hero .container{position:relative;z-index:2;text-align:center}
.sad-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);margin-bottom:30px;
}
.sad-hero-eyebrow::before,
.sad-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.sad-hero h1{
  font-family:var(--serif);
  font-size:clamp(46px, 6vw, 78px);
  font-weight:500;
  letter-spacing:-1px;
  line-height:1.05;
  margin:0 0 24px;
  color:#fff;
}
.sad-hero h1 em{font-style:italic;color:var(--gold)}
.sad-hero-lead{
  font-family:var(--sans);
  font-size:17px;
  line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:640px;margin:0 auto;
}

/* BENEFITS — 4 columns */
.sad-benefits{
  background:#fff;
  padding:90px 0;
  border-bottom:1px solid #e3e0d8;
}
.sad-benefits-head{
  text-align:center;max-width:680px;margin:0 auto 60px;
}
.sad-benefits-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gold-dark);margin-bottom:14px;display:inline-block;
}
.sad-benefits-head h2{
  font-family:var(--serif);
  font-size:clamp(32px, 4vw, 48px);
  font-weight:500;letter-spacing:-.5px;
  margin:0;color:var(--navy);
}
.sad-benefits-head h2 em{font-style:italic;color:var(--gold-dark)}
.sad-benefits-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
  border:1px solid #e3e0d8;
}
@media (max-width:900px){.sad-benefits-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.sad-benefits-grid{grid-template-columns:1fr}}
.sad-benefit{
  padding:38px 30px;
  text-align:center;
  border-right:1px solid #e3e0d8;
  position:relative;
  transition:.2s;
}
.sad-benefit:last-child{border-right:0}
@media (max-width:900px){
  .sad-benefit{border-bottom:1px solid #e3e0d8}
  .sad-benefit:nth-child(2){border-right:0}
  .sad-benefit:nth-child(3),.sad-benefit:nth-child(4){border-bottom:0}
}
.sad-benefit:hover{background:var(--paper)}
.sad-benefit-num{
  font-family:var(--serif);
  font-size:60px;
  font-weight:500;
  line-height:1;
  color:var(--gold);
  margin-bottom:18px;
}
.sad-benefit h3{
  font-family:var(--serif);
  font-size:22px;font-weight:600;
  margin:0 0 12px;
  color:var(--navy);
  letter-spacing:-.2px;
}
.sad-benefit p{
  font-family:var(--sans);
  font-size:13.5px;
  line-height:1.65;
  color:#5a5a5a;
  margin:0;
}

/* TIERS — Membership levels */
.sad-tiers{
  background:var(--paper);
  padding:90px 0;
  border-bottom:1px solid #e3e0d8;
}
.sad-tiers-head{
  text-align:center;max-width:680px;margin:0 auto 60px;
}
.sad-tiers-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gold-dark);margin-bottom:14px;display:inline-block;
}
.sad-tiers-head h2{
  font-family:var(--serif);
  font-size:clamp(32px, 4vw, 44px);
  font-weight:500;letter-spacing:-.5px;
  margin:0;color:var(--navy);
}
.sad-tiers-head h2 em{font-style:italic;color:var(--gold-dark)}
.sad-tiers-grid{
  display:grid;grid-template-columns:repeat(3, 1fr);
  gap:24px;
}
@media (max-width:900px){.sad-tiers-grid{grid-template-columns:1fr}}
.sad-tier{
  background:#fff;
  border:1px solid #e3e0d8;
  padding:38px 30px;
  text-align:center;
  position:relative;
  transition:.2s;
}
.sad-tier-mid{
  border:2px solid var(--gold);
  transform:translateY(-12px);
  box-shadow:0 16px 40px rgba(201,168,107,.18);
}
.sad-tier-mid::before{
  content:'POPÜLER';
  position:absolute;top:-12px;left:50%;transform:translateX(-50%);
  background:var(--gold);color:var(--navy);
  font-family:var(--sans);font-size:10px;font-weight:700;
  letter-spacing:2px;padding:5px 14px;
}
.sad-tier-icon{
  width:56px;height:56px;
  margin:0 auto 18px;
  border:2px solid var(--gold);
  display:flex;align-items:center;justify-content:center;
  color:var(--gold);
}
.sad-tier-icon svg{width:26px;height:26px}
.sad-tier h3{
  font-family:var(--serif);
  font-size:28px;font-weight:600;
  margin:0 0 6px;color:var(--navy);
}
.sad-tier-cond{
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:var(--gold-dark);
  margin-bottom:24px;
}
.sad-tier ul{
  list-style:none;padding:0;margin:0;
  text-align:left;
}
.sad-tier li{
  font-family:var(--sans);
  font-size:13.5px;line-height:1.6;
  color:#3a3a3a;
  padding:10px 0 10px 28px;
  position:relative;
  border-bottom:1px solid #f0eee5;
}
.sad-tier li:last-child{border-bottom:0}
.sad-tier li::before{
  content:'';
  position:absolute;left:8px;top:14px;
  width:8px;height:8px;
  background:var(--gold);
  transform:rotate(45deg);
}

/* FORM */
.sad-form-section{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  padding:90px 0;
  color:#fff;
  position:relative;
  overflow:hidden;
}
.sad-form-section::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(circle at 50% 50%, rgba(201,168,107,.08) 0%, transparent 50%);
}
.sad-form-wrap{
  max-width:720px;margin:0 auto;
  position:relative;z-index:2;
}
.sad-form-head{
  text-align:center;margin-bottom:40px;
}
.sad-form-head .eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);margin-bottom:14px;display:inline-block;
}
.sad-form-head h2{
  font-family:var(--serif);
  font-size:clamp(32px, 4vw, 44px);
  font-weight:500;letter-spacing:-.5px;
  margin:0 0 12px;color:#fff;
}
.sad-form-head h2 em{font-style:italic;color:var(--gold)}
.sad-form-head p{
  font-family:var(--sans);
  font-size:14.5px;
  color:rgba(255,255,255,.7);
  line-height:1.6;
  margin:0;
}
.sad-form{
  background:#fff;
  padding:46px 50px;
  border-top:4px solid var(--gold);
  color:#1a1a1a;
}
@media (max-width:600px){.sad-form{padding:30px 24px}}
.sad-form-row{
  display:grid;grid-template-columns:1fr 1fr;gap:18px;
  margin-bottom:18px;
}
@media (max-width:600px){.sad-form-row{grid-template-columns:1fr}}
.sad-form label{
  display:block;
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:var(--navy);margin-bottom:6px;
}
.sad-form input[type=text],
.sad-form input[type=email],
.sad-form input[type=tel]{
  width:100%;
  padding:12px 14px;
  font-family:var(--sans);font-size:14px;
  border:1px solid #d4d2cc;
  background:#fafaf7;
  transition:.15s;
  border-radius:0;
}
.sad-form input:focus{
  outline:0;
  border-color:var(--gold);
  background:#fff;
  box-shadow:0 0 0 3px rgba(201,168,107,.15);
}
.sad-form-checkbox{
  display:flex;align-items:flex-start;gap:10px;
  margin:24px 0;
  font-family:var(--sans);font-size:13px;line-height:1.55;
  color:#3a3a3a;
}
.sad-form-checkbox input{margin-top:3px;flex-shrink:0}
.sad-form-checkbox a{color:var(--navy);text-decoration:underline}
.sad-form button{
  width:100%;
  padding:18px;
  background:var(--navy);
  color:#fff;
  font-family:var(--sans);
  font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  border:0;cursor:pointer;
  transition:.18s;
}
.sad-form button:hover{
  background:var(--gold);
  color:var(--navy);
}
.sad-alert{
  background:#fff;border-left:4px solid #c8102e;
  padding:14px 18px;margin-bottom:20px;
  font-family:var(--sans);font-size:13.5px;color:#a00d24;
}
.sad-alert ul{margin:0;padding-left:20px}
.sad-success{
  background:#e8f4ec;border-left:4px solid #047857;
  padding:18px;margin-bottom:24px;
  font-family:var(--sans);font-size:14px;color:#055a3c;
  text-align:center;
}
</style>

<div class="sad-page">

  <!-- HERO -->
  <section class="sad-hero">
    <div class="container">
      <div class="sad-hero-eyebrow">Tekcan Metal · Üyelik</div>
      <h1>Sadakat <em>Programı</em></h1>
      <p class="sad-hero-lead">
        Düzenli müşterilerimize özel kademeli indirimler, öncelikli sevkiyat hakkı, özel kampanyalar ve davet usulü etkinlikler. Tekcan Metal ailesinin bir parçası olun.
      </p>
    </div>
  </section>

  <!-- BENEFITS -->
  <section class="sad-benefits">
    <div class="container">
      <div class="sad-benefits-head">
        <div class="eyebrow">Üyelik Ayrıcalıkları</div>
        <h2>Üyelerimize <em>Özel</em></h2>
      </div>
      <div class="sad-benefits-grid">
        <div class="sad-benefit">
          <div class="sad-benefit-num">01</div>
          <h3>Kademeli İndirim</h3>
          <p>Yıllık ciroya göre kademeli fiyat avantajı. Üyeliğin gümüş, altın, platin seviyelerine göre artar.</p>
        </div>
        <div class="sad-benefit">
          <div class="sad-benefit-num">02</div>
          <h3>Öncelikli Sevkiyat</h3>
          <p>Sıraya girmeden, üyelerimize ayrılan kapasiteden gün içinde teslimat önceliği.</p>
        </div>
        <div class="sad-benefit">
          <div class="sad-benefit-num">03</div>
          <h3>Özel Kampanyalar</h3>
          <p>Mevsimsel kampanyalara üyelerimiz öncelikli erişim. Sadece üyelere açık özel partiler.</p>
        </div>
        <div class="sad-benefit">
          <div class="sad-benefit-num">04</div>
          <h3>Hediye Programı</h3>
          <p>Doğum günü, şirket kuruluş yıldönümü ve yıl sonu hediyeleri ile sürpriz teşekkürler.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TIERS -->
  <section class="sad-tiers">
    <div class="container">
      <div class="sad-tiers-head">
        <div class="eyebrow">Üyelik Seviyeleri</div>
        <h2>Üç Kademeli <em>Asaletli Avantaj</em></h2>
      </div>
      <div class="sad-tiers-grid">

        <div class="sad-tier">
          <div class="sad-tier-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <circle cx="12" cy="8" r="6"/>
              <path d="M16 13.5L18 22l-6-3-6 3 2-8.5"/>
            </svg>
          </div>
          <h3>Gümüş</h3>
          <div class="sad-tier-cond">İlk Adım</div>
          <ul>
            <li>%2 standart indirim</li>
            <li>Aylık fiyat listesi e-postası</li>
            <li>Yıl sonu özel teşekkür hediyesi</li>
            <li>Doğum günü tebrikleri</li>
          </ul>
        </div>

        <div class="sad-tier sad-tier-mid">
          <div class="sad-tier-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M2 17l10 5 10-5"/>
              <path d="M2 12l10 5 10-5"/>
              <path d="M12 2L2 7l10 5 10-5z"/>
            </svg>
          </div>
          <h3>Altın</h3>
          <div class="sad-tier-cond">Yılda 250.000 ₺ üzeri</div>
          <ul>
            <li>%5'e varan kademeli indirim</li>
            <li>Öncelikli sevkiyat hakkı</li>
            <li>Özel kampanya erken erişim</li>
            <li>Yıl sonu özel hediye seti</li>
            <li>Atanmış müşteri temsilcisi</li>
          </ul>
        </div>

        <div class="sad-tier">
          <div class="sad-tier-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
              <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <h3>Platin</h3>
          <div class="sad-tier-cond">Davet usulü</div>
          <ul>
            <li>Görüşme bazlı özel fiyatlandırma</li>
            <li>VIP sevkiyat & ekspres teslim</li>
            <li>Yöneticilerle doğrudan iletişim</li>
            <li>Özel etkinliklere davet</li>
            <li>Yılbaşı ve özel günler hediyeleri</li>
            <li>Stok rezervasyon önceliği</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- FORM -->
  <section class="sad-form-section">
    <div class="container">
      <div class="sad-form-wrap">

        <div class="sad-form-head">
          <div class="eyebrow">Üyelik Başvurusu</div>
          <h2>Aileye <em>Katılın</em></h2>
          <p>Aşağıdaki kısa formu doldurun, biz size en kısa sürede dönüş yapalım.</p>
        </div>

        <?php if (isset($_GET['ok'])): ?>
          <div class="sad-success">✓ Sadakat programına kayıt olduğunuz için teşekkürler. En kısa sürede sizinle iletişime geçeceğiz.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="sad-alert"><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" class="sad-form" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

          <div class="sad-form-row">
            <div>
              <label>Ad Soyad *</label>
              <input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required>
            </div>
            <div>
              <label>Firma Adı</label>
              <input type="text" name="company_name" value="<?= h($old['company_name']) ?>">
            </div>
          </div>

          <div class="sad-form-row">
            <div>
              <label>Telefon *</label>
              <input type="tel" name="phone" value="<?= h($old['phone']) ?>" required>
            </div>
            <div>
              <label>E-posta *</label>
              <input type="email" name="email" value="<?= h($old['email']) ?>" required>
            </div>
          </div>

          <div class="sad-form-row">
            <div>
              <label>Vergi / T.C. No</label>
              <input type="text" name="tax_id" value="<?= h($old['tax_id']) ?>">
            </div>
            <div>
              <label>Şehir</label>
              <input type="text" name="city" value="<?= h($old['city']) ?>">
            </div>
          </div>

          <label class="sad-form-checkbox">
            <input type="checkbox" name="kvkk" value="1" required>
            <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve sadakat programı kapsamında verilerimin işlenmesine onay veriyorum.</span>
          </label>

          <button type="submit">Üyelik Başvurusu Gönder →</button>
        </form>

      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
