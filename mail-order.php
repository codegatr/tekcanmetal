<?php
require __DIR__ . '/includes/db.php';

$pageTitle = 'Mail Order';
$metaDesc  = 'Mail order ile ödeme talimatı oluşturma — kart sahibi adı, son 4 hane ve tutar bilgisi.';

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

                // bildirim
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

                flash('success', 'Mail order talimatınız alındı. En kısa sürede sizi arayacağız.');
                redirect('mail-order.php?ok=1');
            } catch (Throwable $e) {
                $errors[] = 'Talimat kaydedilemedi.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Mail Order</span></nav>
    <h1>Mail Order Talimatı</h1>
    <p class="lead">Krediniz/banka kartınızla ödeme talimatı oluşturmak için aşağıdaki formu doldurun. Sizi arayıp gerekli bilgileri telefon üzerinden teyit edeceğiz.</p>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">
    <div class="alert alert-warn">
      <strong>⚠ Güvenlik Uyarısı:</strong> Kart numaranızın <strong>tamamını</strong> bu form üzerinden <strong>girmeyiniz</strong>. Sadece <strong>son 4 hanesini</strong> giriniz. Tam kart bilgileri telefon görüşmesi sırasında, mail order sözleşmesi imzalandıktan sonra alınır.
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul style="margin:0;padding-left:20px"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="card-form" novalidate>
      <?= csrf_field() ?>
      <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

      <fieldset>
        <legend>İletişim Bilgileri</legend>
        <div class="row-2">
          <label>Ad Soyad *<input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required></label>
          <label>Telefon *<input type="tel" name="phone" value="<?= h($old['phone']) ?>" required></label>
        </div>
        <label>E-posta *<input type="email" name="email" value="<?= h($old['email']) ?>" required></label>
      </fieldset>

      <fieldset>
        <legend>Ödeme Bilgileri</legend>
        <label>Kart Üzerindeki Ad Soyad *<input type="text" name="card_holder" value="<?= h($old['card_holder']) ?>" required></label>
        <div class="row-2">
          <label>Kart Numarası — Son 4 Hane *<input type="text" name="card_last4" value="<?= h($old['card_last4']) ?>" maxlength="4" pattern="\d{4}" inputmode="numeric" required placeholder="1234"></label>
          <label>Tutar (TL) *<input type="number" step="0.01" min="0.01" name="amount" value="<?= h($old['amount']) ?>" required></label>
        </div>
        <label>Açıklama / Sipariş Notu<textarea name="description" rows="4"><?= h($old['description']) ?></textarea></label>
      </fieldset>

      <label class="checkbox">
        <input type="checkbox" name="kvkk" value="1" required>
        <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve mail order işlemi için kişisel verilerimin işlenmesine onay veriyorum.</span>
      </label>

      <button type="submit" class="btn btn-primary btn-lg btn-block">Talimat Oluştur</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
