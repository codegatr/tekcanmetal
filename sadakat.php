<?php
require __DIR__ . '/includes/db.php';

$pageTitle = 'Sadakat Programı';
$metaDesc  = 'Tekcan Metal Sadakat Programı — düzenli müşterilerimize özel avantajlar.';

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
                flash('success', 'Sadakat programına kayıt olduğunuz için teşekkürler. Üyelik avantajlarınız için sizinle iletişime geçeceğiz.');
                redirect('sadakat.php?ok=1');
            } catch (Throwable $e) {
                $errors[] = 'Kayıt oluşturulamadı.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url('')) ?>">Anasayfa</a> <span>›</span> <span>Sadakat Programı</span></nav>
    <h1>Sadakat Programı</h1>
    <p class="lead">Düzenli müşterilerimize özel; <em>indirim</em>, <em>öncelikli sevkiyat</em> ve <em>yıl sonu hediyeleri</em>. Aşağıdaki formla üye olun.</p>
  </div>
</section>

<section class="section">
  <div class="container container-narrow">
    <div class="alert alert-info">
      🎁 Üyelerimiz için: alışveriş tutarına göre puan, yıllık ciroya bağlı kademeli indirim, doğum günü/şirket kuruluşu özel mesajları, öncelikli müşteri hizmetleri.
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error"><ul style="margin:0;padding-left:20px"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" class="card-form" novalidate>
      <?= csrf_field() ?>
      <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

      <div class="row-2">
        <label>Ad Soyad *<input type="text" name="full_name" value="<?= h($old['full_name']) ?>" required></label>
        <label>Firma Adı<input type="text" name="company_name" value="<?= h($old['company_name']) ?>"></label>
      </div>
      <div class="row-2">
        <label>Telefon *<input type="tel" name="phone" value="<?= h($old['phone']) ?>" required></label>
        <label>E-posta *<input type="email" name="email" value="<?= h($old['email']) ?>" required></label>
      </div>
      <div class="row-2">
        <label>Vergi/T.C. No<input type="text" name="tax_id" value="<?= h($old['tax_id']) ?>"></label>
        <label>Şehir<input type="text" name="city" value="<?= h($old['city']) ?>"></label>
      </div>

      <label class="checkbox">
        <input type="checkbox" name="kvkk" value="1" required>
        <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum ve sadakat programı kapsamında verilerimin işlenmesine onay veriyorum.</span>
      </label>

      <button type="submit" class="btn btn-primary btn-lg btn-block">Üyelik Başvurusu Gönder</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
