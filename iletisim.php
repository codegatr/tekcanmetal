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
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>İletişim</span></nav>
    <h1>İletişim</h1>
    <p class="lead">Sorularınız, talepleriniz ve teklifleriniz için bize ulaşın.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-info">
        <div class="ci-block">
          <h3>📍 Adresimiz</h3>
          <p><?= nl2br(h(settings('contact_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS — Karatay/Konya'))) ?></p>
        </div>
        <div class="ci-block">
          <h3>📞 Telefon</h3>
          <p><a href="<?= h(phone_link(settings('contact_phone', '03323422452'))) ?>"><?= h(format_phone(settings('contact_phone', '03323422452'))) ?></a></p>
          <p><a href="<?= h(phone_link(settings('contact_whatsapp', '05548350226'))) ?>"><?= h(format_phone(settings('contact_whatsapp', '05548350226'))) ?> (WhatsApp)</a></p>
        </div>
        <div class="ci-block">
          <h3>✉ E-posta</h3>
          <p><a href="mailto:<?= h(settings('contact_email', 'info@tekcanmetal.com')) ?>"><?= h(settings('contact_email', 'info@tekcanmetal.com')) ?></a></p>
        </div>
        <div class="ci-block">
          <h3>🕐 Çalışma Saatleri</h3>
          <p><?= nl2br(h(settings('contact_hours', "Pzt–Cuma: 08:30 — 18:30\nCumartesi: 08:30 — 17:00\nPazar: Kapalı"))) ?></p>
        </div>
      </div>

      <div class="contact-form-wrap">
        <h3>Bize Yazın</h3>
        <?php if ($errors): ?>
          <div class="alert alert-error">
            <ul style="margin:0;padding-left:20px;"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <form method="post" class="contact-form" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">

          <div class="row-2">
            <label>Ad Soyad *<input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required></label>
            <label>E-posta *<input type="email" name="email" value="<?= h($old['email']) ?>" required></label>
          </div>
          <div class="row-2">
            <label>Telefon<input type="tel" name="phone" value="<?= h($old['phone']) ?>"></label>
            <label>Konu<input type="text" name="subject" value="<?= h($old['subject']) ?>"></label>
          </div>
          <label>Mesajınız *<textarea name="message" rows="6" required><?= h($old['message']) ?></textarea></label>

          <label class="checkbox">
            <input type="checkbox" name="kvkk" value="1" required>
            <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum, verilerimin işlenmesine onay veriyorum.</span>
          </label>

          <button type="submit" class="btn btn-primary btn-lg btn-block">Mesajı Gönder</button>
        </form>
      </div>
    </div>

    <?php $mapEmbed = settings('contact_map_embed'); if ($mapEmbed): ?>
    <div class="contact-map">
      <?= $mapEmbed ?>
    </div>
    <?php else: ?>
    <div class="contact-map">
      <iframe src="https://www.google.com/maps?q=<?= urlencode(settings('contact_address', 'Fevziçakmak Mh. Gülistan Cad. Atiker 3, 2.Blok No:33 AS Karatay Konya')) ?>&output=embed"
              width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
