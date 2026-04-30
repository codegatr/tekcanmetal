<?php
require __DIR__ . '/includes/db.php';

$pageTitle = 'Mail Order Talimatı';
$metaDesc  = 'Tekcan Metal Mail Order — kart bilgilerinizi telefonla teyit edip güvenli ödeme talimatı oluşturun.';

$errors = [];
$old = ['full_name'=>'','phone'=>'','email'=>'','card_holder'=>'','card_last4'=>'','amount'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Oturum doğrulaması başarısız oldu.';
    } else {
        foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');

        if ($old['full_name'] === '')                                  $errors[] = 'Ad Soyad zorunlu.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))         $errors[] = 'Geçerli e-posta giriniz.';
        if ($old['phone'] === '')                                      $errors[] = 'Telefon zorunlu.';
        if ($old['card_holder'] === '')                                $errors[] = 'Kart üzerindeki ad zorunlu.';
        if (!preg_match('/^\d{4}$/', $old['card_last4']))              $errors[] = 'Kart son 4 hane (sadece rakam) giriniz.';
        if (!is_numeric($old['amount']) || (float)$old['amount'] <= 0) $errors[] = 'Geçerli bir tutar giriniz.';
        if (empty($_POST['kvkk']))                                     $errors[] = 'KVKK onayı zorunludur.';
        if (!empty($_POST['website']))                                 $errors[] = 'İstek reddedildi.';

        if (!$errors) {
            try {
                q("INSERT INTO tm_mail_orders (full_name,phone,email,card_holder,card_last4,amount,description,ip_address)
                   VALUES (?,?,?,?,?,?,?,?)",
                  [$old['full_name'], $old['phone'], $old['email'],
                   $old['card_holder'], $old['card_last4'],
                   (float)$old['amount'], $old['description'], get_ip()]);

                $to = settings('contact_email', 'info@tekcanmetal.com');
                $sub = '[Mail Order] Yeni talimat: ' . $old['full_name'];
                $body = "Yeni mail order talimatı:\n\n"
                      . "Ad Soyad     : {$old['full_name']}\n"
                      . "Telefon      : {$old['phone']}\n"
                      . "E-posta      : {$old['email']}\n"
                      . "Kart Sahibi  : {$old['card_holder']}\n"
                      . "Kart Son 4   : ****-****-****-{$old['card_last4']}\n"
                      . "Tutar        : " . number_format((float)$old['amount'], 2, ',', '.') . " TL\n\n"
                      . "Açıklama:\n{$old['description']}\n\n"
                      . "----\nIP: " . get_ip() . "\nZaman: " . date('Y-m-d H:i:s') . "\n";
                $headers = [
                    'From: ' . settings('mail_from_name', 'Tekcan Metal') . ' <' . settings('mail_from_email', 'noreply@tekcanmetal.com') . '>',
                    'Reply-To: ' . $old['email'],
                    'Content-Type: text/plain; charset=UTF-8',
                ];
                @mail($to, '=?UTF-8?B?' . base64_encode($sub) . '?=', $body, implode("\r\n", $headers));

                flash('success', 'Mail order talimatınız alındı. Müşteri temsilcimiz en kısa sürede sizi arayacak.');
                redirect('mail-order.php?ok=1');
            } catch (Throwable $e) {
                $errors[] = 'Talimat kaydedilemedi.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.mo-page{
  --navy:#050d24;
  --navy-2:#0c1e44;
  --gold:#c9a86b;
  --red:#c8102e;
  --paper:#fafaf7;
  --serif:'Cormorant Garamond', Georgia, serif;
  --sans:'Inter', system-ui, sans-serif;
  background:var(--paper);
  color:#1a1a1a;
}

/* HERO */
.mo-hero{
  background:linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
  color:#fff;
  padding:90px 0 70px;
  border-bottom:4px solid var(--red);
  position:relative;
  overflow:hidden;
}
.mo-hero::before{
  content:'';position:absolute;
  top:0;left:0;right:0;bottom:0;
  background-image:repeating-linear-gradient(
    -45deg,
    transparent 0,transparent 4px,
    rgba(255,255,255,.02) 4px,rgba(255,255,255,.02) 5px
  );
  pointer-events:none;
}
.mo-hero .container{position:relative;z-index:2;text-align:center}
.mo-hero-eyebrow{
  display:inline-flex;align-items:center;gap:14px;
  font-family:var(--sans);font-size:11px;font-weight:700;
  letter-spacing:3px;text-transform:uppercase;
  color:var(--gold);margin-bottom:24px;
}
.mo-hero-eyebrow::before,.mo-hero-eyebrow::after{
  content:'';width:40px;height:1px;background:var(--gold);
}
.mo-hero h1{
  font-family:var(--serif);
  font-size:clamp(38px, 5vw, 60px);
  font-weight:500;letter-spacing:-.8px;
  margin:0 0 16px;color:#fff;line-height:1.1;
}
.mo-hero h1 em{font-style:italic;color:var(--gold)}
.mo-hero p{
  font-family:var(--sans);
  font-size:15.5px;line-height:1.65;
  color:rgba(255,255,255,.75);
  max-width:620px;margin:0 auto;
}

/* TRUST BAR — secure indicators */
.mo-trust{
  background:#fff;
  padding:30px 0;
  border-bottom:1px solid #e3e0d8;
}
.mo-trust-grid{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:0;
}
@media (max-width:900px){.mo-trust-grid{grid-template-columns:repeat(2,1fr);gap:18px}}
@media (max-width:500px){.mo-trust-grid{grid-template-columns:1fr;gap:14px}}
.mo-trust-item{
  display:flex;align-items:center;gap:14px;
  padding:0 24px;
  border-right:1px solid #e3e0d8;
}
.mo-trust-item:last-child{border-right:0}
@media (max-width:900px){
  .mo-trust-item{border-right:0}
  .mo-trust-item:nth-child(odd){border-right:1px solid #e3e0d8}
}
@media (max-width:500px){
  .mo-trust-item:nth-child(odd){border-right:0}
}
.mo-trust-icon{
  width:40px;height:40px;flex-shrink:0;
  background:rgba(201,168,107,.12);
  color:var(--gold);
  display:flex;align-items:center;justify-content:center;
}
.mo-trust-icon svg{width:20px;height:20px}
.mo-trust-text{
  font-family:var(--sans);
  font-size:12px;line-height:1.45;
}
.mo-trust-text strong{
  display:block;
  color:var(--navy);
  font-size:13px;font-weight:700;margin-bottom:2px;
}
.mo-trust-text span{color:#5a5a5a}

/* WARNING — kart bilgisi uyarısı */
.mo-warn{
  background:linear-gradient(135deg, #fff7e6 0%, #fffaf0 100%);
  border-left:4px solid var(--red);
  padding:24px 32px;
  margin-bottom:30px;
  display:flex;
  gap:18px;
  align-items:flex-start;
}
.mo-warn-icon{
  width:44px;height:44px;flex-shrink:0;
  background:var(--red);color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-size:22px;font-weight:700;
}
.mo-warn-content h3{
  font-family:var(--serif);
  font-size:20px;font-weight:600;
  margin:0 0 8px;color:var(--navy);
}
.mo-warn-content p{
  font-family:var(--sans);
  font-size:13.5px;line-height:1.65;
  color:#3a3a3a;margin:0;
}
.mo-warn-content strong{color:var(--red)}

/* FORM */
.mo-form-section{
  padding:60px 0 100px;
  background:var(--paper);
}
.mo-form-wrap{
  max-width:780px;margin:0 auto;
}
.mo-form{
  background:#fff;
  border:1px solid #e3e0d8;
  border-top:4px solid var(--gold);
  padding:0;
}
.mo-fieldset{
  padding:36px 44px;
  border-bottom:1px solid #e3e0d8;
}
.mo-fieldset:last-of-type{border-bottom:0}
@media (max-width:600px){.mo-fieldset{padding:28px 22px}}
.mo-fieldset-head{
  display:flex;align-items:center;gap:14px;margin-bottom:24px;
}
.mo-fieldset-num{
  width:32px;height:32px;
  background:var(--navy);color:var(--gold);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--serif);font-size:18px;font-weight:600;
}
.mo-fieldset-head h3{
  font-family:var(--serif);
  font-size:24px;font-weight:600;
  margin:0;color:var(--navy);
  letter-spacing:-.2px;
}
.mo-form-row{
  display:grid;grid-template-columns:1fr 1fr;gap:18px;
  margin-bottom:18px;
}
@media (max-width:600px){.mo-form-row{grid-template-columns:1fr}}
.mo-form-row.row-3{grid-template-columns:2fr 1fr 1fr}
@media (max-width:600px){.mo-form-row.row-3{grid-template-columns:1fr}}
.mo-form-field{display:flex;flex-direction:column}
.mo-form-field label{
  font-family:var(--sans);
  font-size:11px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:var(--navy);
  margin-bottom:6px;
}
.mo-form-field input,
.mo-form-field textarea{
  width:100%;
  padding:12px 14px;
  font-family:var(--sans);font-size:14px;
  border:1px solid #d4d2cc;
  background:var(--paper);
  transition:.15s;
  border-radius:0;
}
.mo-form-field input:focus,
.mo-form-field textarea:focus{
  outline:0;border-color:var(--gold);
  background:#fff;
  box-shadow:0 0 0 3px rgba(201,168,107,.15);
}
.mo-form-field textarea{resize:vertical;min-height:90px}
.mo-form-field-hint{
  font-family:var(--sans);
  font-size:11.5px;color:#888;margin-top:4px;
}
.mo-submit-section{
  background:var(--paper);
  padding:30px 44px;
}
@media (max-width:600px){.mo-submit-section{padding:24px 22px}}
.mo-checkbox{
  display:flex;gap:10px;align-items:flex-start;
  font-family:var(--sans);font-size:13px;line-height:1.55;
  color:#3a3a3a;
  margin-bottom:24px;
}
.mo-checkbox input{margin-top:3px;flex-shrink:0}
.mo-checkbox a{color:var(--navy);text-decoration:underline}
.mo-submit-btn{
  width:100%;
  padding:18px;
  background:var(--navy);
  color:#fff;
  font-family:var(--sans);
  font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
  border:0;cursor:pointer;
  transition:.18s;
}
.mo-submit-btn:hover{
  background:var(--gold);color:var(--navy);
}

.mo-alert{
  background:#fff;border-left:4px solid var(--red);
  padding:14px 18px;margin-bottom:20px;
  font-family:var(--sans);font-size:13.5px;color:#a00d24;
}
.mo-alert ul{margin:0;padding-left:20px}
.mo-success{
  background:#e8f4ec;border-left:4px solid #047857;
  padding:18px;margin-bottom:24px;
  font-family:var(--sans);font-size:14px;color:#055a3c;
  text-align:center;
}
</style>

<div class="mo-page">

  <!-- HERO -->
  <section class="mo-hero">
    <div class="container">
      <div class="mo-hero-eyebrow">Tekcan Metal · Güvenli Ödeme</div>
      <h1>Mail Order <em>Talimatı</em></h1>
      <p>Kart bilgilerinizi telefonla teyit ederek güvenli ödeme talimatı oluşturun. Müşteri temsilcimiz size en kısa sürede ulaşacaktır.</p>
    </div>
  </section>

  <!-- TRUST BAR -->
  <section class="mo-trust">
    <div class="container">
      <div class="mo-trust-grid">
        <div class="mo-trust-item">
          <div class="mo-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="1"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <div class="mo-trust-text">
            <strong>SSL Şifreli</strong>
            <span>Tüm form verileri şifrelenir</span>
          </div>
        </div>
        <div class="mo-trust-item">
          <div class="mo-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 11l3 3L22 4"/>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
          </div>
          <div class="mo-trust-text">
            <strong>Kart Numarası Gizli</strong>
            <span>Sadece son 4 hane alınır</span>
          </div>
        </div>
        <div class="mo-trust-item">
          <div class="mo-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
          </div>
          <div class="mo-trust-text">
            <strong>Telefon Teyidi</strong>
            <span>Tam bilgileri telefonla onaylanır</span>
          </div>
        </div>
        <div class="mo-trust-item">
          <div class="mo-trust-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2L4 6v6c0 5.5 3.5 10.5 8 12 4.5-1.5 8-6.5 8-12V6l-8-4z"/>
            </svg>
          </div>
          <div class="mo-trust-text">
            <strong>KVKK Uyumlu</strong>
            <span>Verileriniz yasal koruma altında</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FORM -->
  <section class="mo-form-section">
    <div class="container">
      <div class="mo-form-wrap">

        <?php if (isset($_GET['ok'])): ?>
          <div class="mo-success">✓ Mail order talimatınız alındı. Müşteri temsilcimiz en kısa sürede sizi arayacaktır.</div>
        <?php endif; ?>

        <div class="mo-warn">
          <div class="mo-warn-icon">⚠</div>
          <div class="mo-warn-content">
            <h3>Güvenlik Uyarısı</h3>
            <p>Kart numaranızın <strong>tamamını</strong> bu form üzerinden <strong>girmeyin</strong>. Sadece <strong>son 4 hanesini</strong> giriniz. Tam kart bilgileri telefon görüşmesi sırasında, mail order sözleşmesi imzalandıktan sonra alınır.</p>
          </div>
        </div>

        <?php if ($errors): ?>
          <div class="mo-alert"><ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" class="mo-form" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

          <fieldset class="mo-fieldset">
            <div class="mo-fieldset-head">
              <div class="mo-fieldset-num">1</div>
              <h3>İletişim Bilgileri</h3>
            </div>
            <div class="mo-form-row">
              <div class="mo-form-field">
                <label>Ad Soyad *</label>
                <input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required>
              </div>
              <div class="mo-form-field">
                <label>Telefon *</label>
                <input type="tel" name="phone" value="<?= h($old['phone']) ?>" required>
              </div>
            </div>
            <div class="mo-form-field">
              <label>E-posta *</label>
              <input type="email" name="email" value="<?= h($old['email']) ?>" required>
            </div>
          </fieldset>

          <fieldset class="mo-fieldset">
            <div class="mo-fieldset-head">
              <div class="mo-fieldset-num">2</div>
              <h3>Ödeme Bilgileri</h3>
            </div>
            <div class="mo-form-field" style="margin-bottom:18px">
              <label>Kart Üzerindeki Ad Soyad *</label>
              <input type="text" name="card_holder" value="<?= h($old['card_holder']) ?>" required>
              <div class="mo-form-field-hint">Kartınızın üzerinde basılı olan tam adı yazınız</div>
            </div>
            <div class="mo-form-row row-3">
              <div class="mo-form-field">
                <label>Kart No — Son 4 Hane *</label>
                <input type="text" name="card_last4" value="<?= h($old['card_last4']) ?>" maxlength="4" pattern="\d{4}" inputmode="numeric" required placeholder="1234">
                <div class="mo-form-field-hint">Sadece son 4 rakam</div>
              </div>
              <div class="mo-form-field">
                <label>Tutar (₺) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="<?= h($old['amount']) ?>" required placeholder="0.00">
              </div>
              <div class="mo-form-field">
                <label>Para Birimi</label>
                <input type="text" value="TL" disabled style="background:#f0eee5">
              </div>
            </div>
          </fieldset>

          <fieldset class="mo-fieldset">
            <div class="mo-fieldset-head">
              <div class="mo-fieldset-num">3</div>
              <h3>Sipariş Açıklaması</h3>
            </div>
            <div class="mo-form-field">
              <label>Açıklama / Sipariş Notu</label>
              <textarea name="description" rows="4" placeholder="Sipariş içeriği, özel talepler, fatura adresi vb."><?= h($old['description']) ?></textarea>
            </div>
          </fieldset>

          <div class="mo-submit-section">
            <label class="mo-checkbox">
              <input type="checkbox" name="kvkk" value="1" required>
              <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve mail order işlemi için kişisel verilerimin işlenmesine onay veriyorum.</span>
            </label>
            <button type="submit" class="mo-submit-btn">Talimat Oluştur →</button>
          </div>
        </form>

      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
