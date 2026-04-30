<?php
require __DIR__ . '/includes/db.php';

$pageTitle = 'İletişim';
$metaDesc  = 'Tekcan Metal iletişim — adres, telefon, çalışma saatleri ve mesaj formu.';

$errors = []; $sent = false;
$old = ['full_name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Oturum doğrulaması başarısız oldu, sayfayı yenileyip tekrar deneyin.';
    } else {
        foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');

        if ($old['full_name'] === '' || mb_strlen($old['full_name'], 'UTF-8') < 3) $errors[] = 'Lütfen adınızı doğru giriniz.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))                    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        if ($old['message'] === '' || mb_strlen($old['message'], 'UTF-8') < 10)   $errors[] = 'Mesajınız en az 10 karakter olmalı.';
        if (empty($_POST['kvkk']))                                                $errors[] = 'KVKK aydınlatma metnini onaylamanız gerekiyor.';
        // basit honeypot
        if (!empty($_POST['website']))                                            $errors[] = 'İstek reddedildi.';

        if (!$errors) {
            try {
                q("INSERT INTO tm_contact_messages (full_name,email,phone,subject,message,ip_address,user_agent)
                   VALUES (?,?,?,?,?,?,?)",
                  [$old['full_name'], $old['email'], $old['phone'], $old['subject'], $old['message'], get_ip(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)]);

                // mail bildirimi (basit PHP mail; SMTP isteyen kullanıcı admin'den ayarlar)
                $to     = settings('contact_email', 'info@tekcanmetal.com');
                $sub    = '[İletişim Formu] ' . ($old['subject'] ?: 'Yeni mesaj');
                $body   = "Yeni iletişim formu mesajı:\n\n"
                        . "Ad Soyad: {$old['full_name']}\n"
                        . "E-posta : {$old['email']}\n"
                        . "Telefon : {$old['phone']}\n"
                        . "Konu    : {$old['subject']}\n\n"
                        . "Mesaj:\n{$old['message']}\n\n"
                        . "----\nIP: " . get_ip() . "\n"
                        . "Zaman: " . date('Y-m-d H:i:s') . "\n";
                $headers = [
                    'From: ' . settings('mail_from_name', 'Tekcan Metal') . ' <' . settings('mail_from_email', 'noreply@tekcanmetal.com') . '>',
                    'Reply-To: ' . $old['email'],
                    'Content-Type: text/plain; charset=UTF-8',
                ];
                @mail($to, '=?UTF-8?B?' . base64_encode($sub) . '?=', $body, implode("\r\n", $headers));

                $sent = true;
                flash('success', 'Mesajınız iletildi, en kısa sürede dönüş yapacağız.');
                redirect('iletisim.php?gonderildi=1');
            } catch (Throwable $e) {
                $errors[] = 'Mesaj kaydedilemedi, lütfen daha sonra tekrar deneyin.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.cb-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --navy-3:#143672;
  --gold:#c9a86b;
  --gold-light:#e0c48a;
  --red:#c8102e;
  --paper:#fafaf7;
  --line:#e3e0d8;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
}

/* ═══ HERO ═══ */
.cb-hero{
  background:
    linear-gradient(135deg, rgba(5,13,36,.94) 0%, rgba(20,54,114,.85) 100%),
    url('<?= h(img_url('uploads/pages/tekcan-metal-bina.jpg')) ?>');
  background-size:cover;
  background-position:center;
  color:#fff;
  padding:130px 0 100px;
  border-bottom:4px solid var(--gold);
  position:relative;
  overflow:hidden;
}
.cb-hero::before{
  content:'';position:absolute;
  inset:0;
  background-image:repeating-linear-gradient(
    -45deg, transparent 0, transparent 4px,
    rgba(255,255,255,.02) 4px, rgba(255,255,255,.02) 5px
  );
  pointer-events:none;
}
.cb-hero::after{
  content:'TM';
  position:absolute;
  bottom:-100px;right:-40px;
  font-family:var(--serif);
  font-size:380px;
  font-weight:500;
  color:rgba(201,168,107,.06);
  letter-spacing:-20px;
  line-height:1;
  pointer-events:none;
  user-select:none;
}
.cb-hero .container{position:relative;z-index:2;text-align:center}
.cb-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:4px;text-transform:uppercase;
  color:var(--gold);
  margin-bottom:30px;
}
.cb-hero-eyebrow::before,
.cb-hero-eyebrow::after{
  content:'';width:50px;height:1px;background:var(--gold);
}
.cb-hero h1{
  font-family:var(--serif);
  font-size:clamp(48px, 7vw, 86px);
  font-weight:500;
  line-height:1.05;
  letter-spacing:-1.5px;
  margin:0 0 24px;
  color:#fff;
}
.cb-hero h1 em{font-style:italic;color:var(--gold)}
.cb-hero-lead{
  font-family:var(--sans);
  font-size:17px;
  line-height:1.65;
  color:rgba(255,255,255,.78);
  max-width:680px;
  margin:0 auto;
}

/* Quick contact strip */
.cb-quick{
  background:#fff;
  border-bottom:1px solid var(--line);
  padding:0;
}
.cb-quick-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
}
@media (max-width:900px){.cb-quick-grid{grid-template-columns:repeat(2,1fr)}}
@media (max-width:500px){.cb-quick-grid{grid-template-columns:1fr}}
.cb-quick-item{
  padding:30px 26px;
  border-right:1px solid var(--line);
  text-align:center;
  transition:.2s;
  text-decoration:none;
  color:inherit;
  display:block;
}
.cb-quick-item:last-child{border-right:0}
@media (max-width:900px){
  .cb-quick-item:nth-child(2){border-right:0}
  .cb-quick-item:nth-child(3){border-right:1px solid var(--line)}
}
.cb-quick-item:hover{
  background:var(--paper);
  transform:translateY(-2px);
}
.cb-quick-icon{
  width:54px;height:54px;
  margin:0 auto 14px;
  display:flex;align-items:center;justify-content:center;
  border:1.5px solid var(--gold);
  color:var(--gold);
  transition:.2s;
}
.cb-quick-item:hover .cb-quick-icon{
  background:var(--gold);color:var(--navy);
}
.cb-quick-icon svg{width:22px;height:22px}
.cb-quick-label{
  font-family:var(--sans);
  font-size:10px;
  font-weight:700;
  letter-spacing:2px;
  text-transform:uppercase;
  color:var(--gold);
  margin-bottom:6px;
}
.cb-quick-value{
  font-family:var(--serif);
  font-size:18px;
  font-weight:600;
  color:var(--navy);
  letter-spacing:-.2px;
}

/* ═══ MAIN CONTACT GRID ═══ */
.cb-main{
  padding:90px 0;
  background:var(--paper);
}
.cb-grid{
  display:grid;
  grid-template-columns:1fr 1.2fr;
  gap:50px;
  align-items:start;
}
@media (max-width:900px){.cb-grid{grid-template-columns:1fr;gap:30px}}

/* Sol: Info card */
.cb-info{
  background:#fff;
  border:1px solid var(--line);
  border-top:4px solid var(--gold);
  padding:40px 36px;
  position:sticky;
  top:100px;
}
@media (max-width:900px){.cb-info{position:static;padding:30px 26px}}
.cb-info-eyebrow{
  font-family:var(--sans);
  font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;
  color:var(--red);
  margin-bottom:14px;
  display:inline-block;
}
.cb-info h2{
  font-family:var(--serif);
  font-size:32px;font-weight:500;letter-spacing:-.5px;
  margin:0 0 28px;
  color:var(--navy);
  line-height:1.1;
}
.cb-info h2 em{font-style:italic;color:var(--red)}

.cb-info-block{
  padding:18px 0;
  border-bottom:1px solid var(--line);
}
.cb-info-block:last-of-type{border-bottom:0}
.cb-info-block h3{
  font-family:var(--sans);
  font-size:10.5px;
  font-weight:700;
  letter-spacing:2.5px;
  text-transform:uppercase;
  color:var(--gold);
  margin:0 0 8px;
}
.cb-info-block p{
  font-family:var(--sans);
  font-size:14.5px;
  line-height:1.65;
  color:#3a3a3a;
  margin:0 0 4px;
}
.cb-info-block p strong{
  color:var(--navy);
  font-weight:600;
}
.cb-info-block a{
  color:var(--navy);
  text-decoration:none;
  font-weight:600;
  transition:.15s;
  font-family:var(--sans);
}
.cb-info-block a:hover{
  color:var(--red);
}
.cb-info-actions{
  margin-top:24px;
  padding-top:24px;
  border-top:2px solid var(--gold);
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
}
.cb-info-action{
  display:flex;align-items:center;gap:8px;
  padding:14px;
  text-decoration:none;
  font-family:var(--sans);
  font-size:11.5px;font-weight:700;
  letter-spacing:1.2px;text-transform:uppercase;
  text-align:center;
  justify-content:center;
  transition:.18s;
  border:1.5px solid transparent;
}
.cb-info-action.primary{
  background:var(--navy);color:#fff;border-color:var(--navy);
}
.cb-info-action.primary:hover{
  background:var(--gold);color:var(--navy);border-color:var(--gold);
  transform:translateY(-2px);
}
.cb-info-action.whatsapp{
  background:#25D366;color:#fff;border-color:#25D366;
}
.cb-info-action.whatsapp:hover{
  background:#128c7e;border-color:#128c7e;
  transform:translateY(-2px);
}

/* Sağ: Form */
.cb-form-wrap{
  background:#fff;
  border:1px solid var(--line);
  padding:0;
}
.cb-form-head{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  padding:40px 40px 36px;
  position:relative;
  overflow:hidden;
  border-bottom:4px solid var(--gold);
}
@media (max-width:600px){.cb-form-head{padding:30px 26px}}
.cb-form-head::before{
  content:'';position:absolute;
  inset:0;
  background:radial-gradient(circle at 90% 0%, rgba(201,168,107,.18) 0%, transparent 60%);
  pointer-events:none;
}
.cb-form-head .cb-info-eyebrow{
  color:var(--gold);
}
.cb-form-head h2{
  font-family:var(--serif);
  font-size:36px;
  font-weight:500;
  letter-spacing:-.5px;
  color:#fff;
  margin:0 0 10px;
  line-height:1.1;
  position:relative;z-index:2;
}
.cb-form-head h2 em{font-style:italic;color:var(--gold)}
.cb-form-head p{
  font-family:var(--sans);
  font-size:14px;line-height:1.6;
  color:rgba(255,255,255,.75);
  margin:0;
  max-width:520px;
  position:relative;z-index:2;
}

.cb-form{
  padding:36px 40px 40px;
}
@media (max-width:600px){.cb-form{padding:28px 26px}}
.cb-form-row{
  display:grid;grid-template-columns:1fr 1fr;gap:18px;
  margin-bottom:18px;
}
@media (max-width:600px){.cb-form-row{grid-template-columns:1fr}}
.cb-form-field{display:flex;flex-direction:column}
.cb-form-field-full{grid-column:1/-1}
.cb-form-field label{
  font-family:var(--sans);
  font-size:11px;
  font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:var(--navy);
  margin-bottom:6px;
}
.cb-form-field label .req{color:var(--red);margin-left:3px}
.cb-form-field input,
.cb-form-field textarea{
  width:100%;
  padding:13px 14px;
  font-family:var(--sans);font-size:14.5px;
  border:1.5px solid var(--line);
  background:var(--paper);
  color:var(--navy);
  transition:.15s;
  border-radius:0;
}
.cb-form-field textarea{
  resize:vertical;min-height:120px;
  font-family:var(--sans);
}
.cb-form-field input:focus,
.cb-form-field textarea:focus{
  outline:0;
  border-color:var(--gold);
  background:#fff;
  box-shadow:0 0 0 3px rgba(201,168,107,.18);
}
.cb-form-checkbox{
  display:flex;gap:10px;align-items:flex-start;
  font-family:var(--sans);font-size:13px;line-height:1.55;
  color:#3a3a3a;
  margin:24px 0;
}
.cb-form-checkbox input{margin-top:3px;flex-shrink:0}
.cb-form-checkbox a{color:var(--navy);text-decoration:underline;font-weight:600}
.cb-form-submit{
  width:100%;
  padding:18px;
  background:var(--navy);
  color:#fff;
  font-family:var(--sans);
  font-size:13px;
  font-weight:700;letter-spacing:2.5px;text-transform:uppercase;
  border:0;cursor:pointer;
  transition:.18s;
}
.cb-form-submit:hover{
  background:var(--gold);color:var(--navy);
}

.cb-alert{
  padding:14px 18px;margin-bottom:20px;
  font-family:var(--sans);font-size:13.5px;
  border-left:4px solid;
}
.cb-alert.error{
  background:#fff1f1;color:#a00d24;border-color:var(--red);
}
.cb-alert.success{
  background:#e8f4ec;color:#055a3c;border-color:#10803a;
}
.cb-alert ul{margin:0;padding-left:20px}

/* MAP */
.cb-map{
  margin-top:0;
  background:#fff;
  border-top:1px solid var(--line);
}
.cb-map-head{
  text-align:center;
  padding:50px 0 30px;
  background:#fff;
}
.cb-map-head h2{
  font-family:var(--serif);
  font-size:32px;
  font-weight:500;
  letter-spacing:-.3px;
  margin:0 0 8px;
  color:var(--navy);
}
.cb-map-head h2 em{font-style:italic;color:var(--red)}
.cb-map-head p{
  font-family:var(--sans);
  font-size:14px;color:#5a5a5a;margin:0;
}
.cb-map iframe{
  width:100%;
  height:480px;
  display:block;
  filter:grayscale(.2);
}
</style>

<div class="cb-page">

  <!-- HERO -->
  <section class="cb-hero">
    <div class="container">
      <div class="cb-hero-eyebrow">İletişim Merkezi</div>
      <h1>Bizimle <em>İletişim</em> Kurun</h1>
      <p class="cb-hero-lead">
        Sorularınız, ürün talepleriniz, özel proje teklifleriniz ve iş birlikleri için satış ve operasyon ekibimiz hizmetinizdedir.
      </p>
    </div>
  </section>

  <!-- QUICK ACCESS STRIP -->
  <section class="cb-quick">
    <div class="container">
      <div class="cb-quick-grid">
        <a class="cb-quick-item" href="<?= h(phone_link(settings('site_phone', '0 332 342 24 52'))) ?>">
          <div class="cb-quick-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
          </div>
          <div class="cb-quick-label">Telefon</div>
          <div class="cb-quick-value"><?= h(settings('site_phone', '0 332 342 24 52')) ?></div>
        </a>
        <a class="cb-quick-item" href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba, web sitenizden size ulaşıyorum.')) ?>" target="_blank" rel="noopener">
          <div class="cb-quick-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.5 14.4c-.3-.2-1.7-.8-2-.9-.3-.1-.5-.2-.7.2s-.8.9-1 1.1c-.2.2-.4.2-.7 0a8.4 8.4 0 0 1-2.5-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.6c.1-.2.2-.3.3-.5a.5.5 0 0 0 0-.5c-.1-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.8.4 3.4 3.4 0 0 0-1.1 2.6c0 1.5 1.1 3 1.3 3.2.1.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.8.6.7.2 1.4.2 2 .1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3M12 22a10 10 0 0 1-5.1-1.4l-3.7 1 1-3.5A10 10 0 1 1 12 22"/>
            </svg>
          </div>
          <div class="cb-quick-label">WhatsApp</div>
          <div class="cb-quick-value"><?= h(settings('site_mobile', '0 554 835 0 226')) ?></div>
        </a>
        <a class="cb-quick-item" href="mailto:<?= h(settings('site_email', 'info@tekcanmetal.com')) ?>">
          <div class="cb-quick-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
          <div class="cb-quick-label">E-Posta</div>
          <div class="cb-quick-value"><?= h(settings('site_email', 'info@tekcanmetal.com')) ?></div>
        </a>
        <a class="cb-quick-item" href="https://www.google.com/maps?q=<?= urlencode(settings('contact_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS Karatay Konya')) ?>" target="_blank" rel="noopener">
          <div class="cb-quick-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <div class="cb-quick-label">Adres</div>
          <div class="cb-quick-value">Karatay · Konya</div>
        </a>
      </div>
    </div>
  </section>

  <!-- MAIN -->
  <section class="cb-main">
    <div class="container">
      <div class="cb-grid">

        <!-- INFO CARD -->
        <div class="cb-info">
          <div class="cb-info-eyebrow">Tekcan Metal</div>
          <h2>Konya'nın <em>Köklü</em> Demir Çelik Tedarikçisi</h2>

          <div class="cb-info-block">
            <h3>Merkez Adres</h3>
            <p><?= nl2br(h(settings('site_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS'))) ?></p>
            <p><strong><?= h(settings('site_district', 'Karatay')) ?> / <?= h(settings('site_city', 'Konya')) ?></strong></p>
          </div>

          <div class="cb-info-block">
            <h3>Telefon Numaraları</h3>
            <p>Sabit: <a href="<?= h(phone_link(settings('site_phone', '0 332 342 24 52'))) ?>"><?= h(settings('site_phone', '0 332 342 24 52')) ?></a></p>
            <p>Mobil: <a href="<?= h(phone_link(settings('site_mobile', '0 554 835 0 226'))) ?>"><?= h(settings('site_mobile', '0 554 835 0 226')) ?></a></p>
          </div>

          <div class="cb-info-block">
            <h3>E-Posta Adresleri</h3>
            <p>Genel: <a href="mailto:<?= h(settings('site_email', 'info@tekcanmetal.com')) ?>"><?= h(settings('site_email', 'info@tekcanmetal.com')) ?></a></p>
            <p>Satış: <a href="mailto:satis@tekcanmetal.com">satis@tekcanmetal.com</a></p>
          </div>

          <div class="cb-info-block">
            <h3>Çalışma Saatleri</h3>
            <p><strong>Pazartesi – Cuma:</strong> 08:00 – 18:00</p>
            <p><strong>Cumartesi:</strong> 08:00 – 13:00</p>
            <p><strong>Pazar:</strong> Kapalı</p>
          </div>

          <div class="cb-info-actions">
            <a href="<?= h(phone_link(settings('site_phone', '0 332 342 24 52'))) ?>" class="cb-info-action primary">📞 Hemen Ara</a>
            <a href="<?= h(whatsapp_link(settings('site_whatsapp', '905320652400'), 'Merhaba')) ?>" target="_blank" rel="noopener" class="cb-info-action whatsapp">💬 WhatsApp</a>
          </div>
        </div>

        <!-- FORM -->
        <div class="cb-form-wrap">
          <div class="cb-form-head">
            <div class="cb-info-eyebrow">Mesaj Gönderin</div>
            <h2>Doğrudan <em>Bize Yazın</em></h2>
            <p>Aşağıdaki formu doldurun, satış ekibimiz en kısa sürede size dönüş yapsın. Standart yanıt süremiz: <strong style="color:var(--gold)">2 saat</strong>.</p>
          </div>

          <form method="post" class="cb-form" novalidate>
            <?= csrf_field() ?>
            <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">

            <?php if (isset($_GET['gonderildi'])): ?>
              <div class="cb-alert success">
                ✓ <strong>Mesajınız iletildi.</strong> Satış ekibimiz en kısa sürede sizinle iletişime geçecek. Teşekkür ederiz.
              </div>
            <?php endif; ?>

            <?php if ($errors): ?>
              <div class="cb-alert error">
                <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
              </div>
            <?php endif; ?>

            <div class="cb-form-row">
              <div class="cb-form-field">
                <label>Ad Soyad <span class="req">*</span></label>
                <input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required>
              </div>
              <div class="cb-form-field">
                <label>E-posta <span class="req">*</span></label>
                <input type="email" name="email" value="<?= h($old['email']) ?>" required>
              </div>
            </div>

            <div class="cb-form-row">
              <div class="cb-form-field">
                <label>Telefon</label>
                <input type="tel" name="phone" value="<?= h($old['phone']) ?>" placeholder="0 5xx xxx xx xx">
              </div>
              <div class="cb-form-field">
                <label>Konu</label>
                <input type="text" name="subject" value="<?= h($old['subject']) ?>" placeholder="Teklif talebi, ürün sorgusu vb.">
              </div>
            </div>

            <div class="cb-form-field cb-form-field-full">
              <label>Mesajınız <span class="req">*</span></label>
              <textarea name="message" rows="6" required placeholder="Talebinizin detaylarını yazınız..."><?= h($old['message']) ?></textarea>
            </div>

            <label class="cb-form-checkbox">
              <input type="checkbox" name="kvkk" value="1" required>
              <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum, kişisel verilerimin işlenmesine onay veriyorum.</span>
            </label>

            <button type="submit" class="cb-form-submit">Mesajı Gönder →</button>
          </form>
        </div>

      </div>
    </div>
  </section>

  <!-- MAP -->
  <section class="cb-map">
    <div class="cb-map-head">
      <div class="container">
        <h2>Bizi <em>Ziyaret Edin</em></h2>
        <p>Karatay'da, Atiker 3 Sanayi Sitesi'nin tam ortasında — kahve hazır</p>
      </div>
    </div>
    <?php $mapEmbed = settings('contact_map_embed'); if ($mapEmbed): ?>
      <?= $mapEmbed ?>
    <?php else: ?>
      <iframe src="https://www.google.com/maps?q=<?= urlencode(settings('contact_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS Karatay Konya')) ?>&output=embed"
              allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <?php endif; ?>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
