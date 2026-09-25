<?php
require __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/qnbpay.php';
require_once __DIR__ . '/includes/customer_auth.php';
cust_ensure_schema();

/* ============================================================
 * Müşteri girişi / çıkışı / zorunlu şifre değiştirme (PRG deseni)
 * ============================================================ */
$custErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'customer_login') {
    if (!csrf_check()) {
        $custErr = 'Oturum doğrulama hatası. Sayfayı yenileyip tekrar deneyin.';
    } else {
        $r = customer_login((string)($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''));
        if ($r['ok']) redirect('odeme.php'); else $custErr = $r['msg'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'customer_logout' && csrf_check()) {
    customer_logout();
    redirect('odeme.php');
}
$cust = customer();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'customer_change_password' && $cust) {
    if (!csrf_check()) {
        $custErr = 'Oturum doğrulama hatası. Sayfayı yenileyip tekrar deneyin.';
    } else {
        $r = customer_change_password((int)$cust['id'], (string)($_POST['current_password'] ?? ''), (string)($_POST['new_password'] ?? ''));
        if ($r['ok']) redirect('odeme.php'); else $custErr = $r['msg'];
    }
}

/* ============================================================
 * AJAX: ödeme kaydı aç + QNBpay form alanlarını (hash_key dahil) üret
 * Kart bilgileri BU isteğe dahil değildir; tarayıcı doğrudan QNBpay'e gönderir.
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'init') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $fail = function (string $msg, int $code = 422): void {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    };
    $in = fn(string $k): string => trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)($_POST[$k] ?? '')));

    if (!csrf_check())                 $fail('Oturum doğrulaması başarısız oldu. Sayfayı yenileyip tekrar deneyin.', 419);
    if ($in('website') !== '')         $fail('İstek reddedildi.', 400);

    qnb_ensure_schema();
    if (!qnb_enabled())                $fail('Online ödeme şu anda kullanılamıyor.', 503);
    if (!qnb_is_open_now())            $fail(qnb_closed_msg(), 503);   // çalışma saatleri dışında yeni ödeme başlatılamaz
    if (!$cust)                        $fail('Ödeme yapmak için giriş yapmanız gerekiyor.', 401);   // zorunlu müşteri girişi (sunucu tarafı — client-side gizleme yeterli değil)
    if ($cust['must_change_password']) $fail('Ödeme yapmadan önce şifrenizi değiştirmeniz gerekiyor.', 403);
    $cfg = qnb_cfg();

    $full    = mb_substr($in('full_name'), 0, 150, 'UTF-8');
    $company = mb_substr($in('company'), 0, 150, 'UTF-8');
    $email   = mb_substr($in('email'), 0, 150, 'UTF-8');
    $phone   = mb_substr($in('phone'), 0, 30, 'UTF-8');
    $desc    = mb_substr($in('description'), 0, 300, 'UTF-8');
    $amount  = qnb_parse_amount($in('amount'));

    if (count(preg_split('/\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY)) < 2) $fail('Lütfen ad ve soyadınızı birlikte yazın.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))                     $fail('Geçerli bir e-posta adresi girin.');
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15)         $fail('Geçerli bir telefon numarası girin.');
    if ($amount === null || $amount <= 0)                               $fail('Geçerli bir tutar girin.');
    if ($amount < $cfg['min'] || $amount > $cfg['max'])                 $fail('Tutar ' . qnb_money($cfg['min']) . ' ile ' . qnb_money($cfg['max']) . ' arasında olmalıdır.');
    if (empty($_POST['kvkk']))                                          $fail('KVKK aydınlatma metnini onaylamanız gerekir.');

    // Kötüye kullanım koruması: IP/bağlantı/e-posta/başarısız deneme sınırları + otomatik duraklatma
    $abuse = qnb_abuse_check($email);
    if ($abuse) $fail($abuse['msg'], $abuse['code']);

    try {
        $invoiceId = qnb_new_invoice_id();
        $id = qnb_create_payment([
            'invoice_id'  => $invoiceId,
            'full_name'   => $full,
            'company'     => $company,
            'email'       => $email,
            'phone'       => $phone,
            'description' => $desc,
            'amount'      => $amount,
        ]);
        if ($cust) q("UPDATE tm_payments SET customer_id=? WHERE id=?", [$cust['id'], $id]);
        $pay  = row("SELECT * FROM tm_payments WHERE id=?", [$id]);
        $form = qnb_build_form($pay);
    } catch (Throwable $e) {
        $fail('Ödeme başlatılamadı. Lütfen tekrar deneyin.', 500);
    }

    echo json_encode(['ok' => true, 'action' => $form['action'], 'fields' => $form['fields']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* ============================================================
 * SAYFA
 * ============================================================ */
$pageTitle  = t('pay.title', 'Online Ödeme');
$metaDesc   = t('pay.meta_desc', 'Tekcan Metal güvenli online ödeme — kredi/banka kartınızla 3D Secure doğrulamalı ödeme yapın.');
$metaRobots = 'noindex, nofollow';
$payOn      = qnb_enabled();
$payPaused  = $payOn && qnb_is_paused();
$payClosed  = $payOn && !$payPaused && !qnb_is_open_now();
$payCfg     = qnb_cfg();

$js = [
    'btn'        => t('pay.btn', 'Güvenli Ödeme Yap'),
    'processing' => t('pay.processing', 'Bankaya yönlendiriliyor…'),
    'generic'    => t('pay.err_generic', 'Bir hata oluştu. Lütfen tekrar deneyin.'),
    'name'       => t('pay.err_name', 'Lütfen ad ve soyadınızı birlikte yazın.'),
    'email'      => t('pay.err_email', 'Geçerli bir e-posta adresi girin.'),
    'phone'      => t('pay.err_phone', 'Geçerli bir telefon numarası girin.'),
    'amount'     => t('pay.err_amount', 'Geçerli bir tutar girin.'),
    'range'      => t('pay.err_range', 'Tutar izin verilen aralığın dışında.'),
    'holder'     => t('pay.err_holder', 'Kart üzerindeki adı girin.'),
    'card'       => t('pay.err_card', 'Kart numarası geçersiz.'),
    'expiry'     => t('pay.err_expiry', 'Son kullanma tarihi geçersiz veya süresi dolmuş.'),
    'cvv'        => t('pay.err_cvv', 'CVV kodunu girin.'),
    'kvkk'       => t('pay.err_kvkk', 'KVKK aydınlatma metnini onaylamanız gerekir.'),
];

require __DIR__ . '/includes/header.php';
?>

<style>.pay-page{--navy:#050d24;--navy-2:#0c1e44;--gold:#c9a86b;--red:#c8102e;--paper:#fafaf7;--serif:'Cormorant Garamond',Georgia,serif;--sans:'Inter',system-ui,sans-serif;background:var(--paper);color:#1a1a1a}
.pay-hero{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-2) 100%);color:#fff;padding:80px 0 60px;border-bottom:4px solid var(--red);text-align:center}
.pay-eyebrow{display:inline-flex;align-items:center;gap:14px;font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:22px}
.pay-eyebrow::before,.pay-eyebrow::after{content:'';width:40px;height:1px;background:var(--gold)}
.pay-hero h1{font-family:var(--serif);font-size:clamp(36px,5vw,56px);font-weight:500;margin:0 0 14px;color:#fff;line-height:1.1}
.pay-hero h1 em{font-style:italic;color:var(--gold)}
.pay-hero p{font-family:var(--sans);font-size:15.5px;line-height:1.65;color:rgba(255,255,255,.75);max-width:640px;margin:0 auto}
.pay-trust{background:#fff;padding:26px 0;border-bottom:1px solid #e3e0d8}
.pay-trust-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0}
@media (max-width:760px){.pay-trust-grid{grid-template-columns:1fr;gap:14px}}
.pay-trust-item{padding:0 24px;border-right:1px solid #e3e0d8;font-family:var(--sans);font-size:12px;line-height:1.45}
.pay-trust-item:last-child{border-right:0}
@media (max-width:760px){.pay-trust-item{border-right:0}}
.pay-trust-item strong{display:block;color:var(--navy);font-size:13px;font-weight:700;margin-bottom:2px}
.pay-trust-item span{color:#5a5a5a}
.pay-section{padding:56px 0 90px;background:var(--paper)}
.pay-wrap{max-width:780px;margin:0 auto}
.pay-form{background:#fff;border:1px solid #e3e0d8;border-top:4px solid var(--gold)}
.pay-fieldset{padding:34px 42px;border:0;border-bottom:1px solid #e3e0d8;margin:0}
@media (max-width:600px){.pay-fieldset{padding:26px 20px}}
.pay-fs-head{display:flex;align-items:center;gap:14px;margin-bottom:22px}
.pay-fs-num{width:32px;height:32px;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:18px;font-weight:600}
.pay-fs-head h3{font-family:var(--serif);font-size:23px;font-weight:600;margin:0;color:var(--navy)}
.pay-row{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
.pay-row.r3{grid-template-columns:1.4fr 1fr 1fr}
@media (max-width:600px){.pay-row,.pay-row.r3{grid-template-columns:1fr}}
.pay-field{display:flex;flex-direction:column;margin-bottom:18px}
.pay-row .pay-field{margin-bottom:0}
.pay-field label{font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--navy);margin-bottom:6px}
.pay-field input,.pay-field textarea{width:100%;padding:12px 14px;font-family:var(--sans);font-size:14px;border:1px solid #d4d2cc;background:var(--paper);border-radius:0;box-sizing:border-box}
.pay-field input:focus,.pay-field textarea:focus{outline:0;border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,107,.15)}
.pay-field textarea{resize:vertical;min-height:80px}
.pay-hint{font-family:var(--sans);font-size:11.5px;color:#888;margin-top:4px}
.pay-secure{display:flex;gap:12px;align-items:flex-start;background:#f3f7f4;border-left:4px solid #047857;padding:14px 18px;margin-bottom:22px;font-family:var(--sans);font-size:13px;line-height:1.55;color:#0b4a34}
.pay-submit{background:var(--paper);padding:28px 42px}
@media (max-width:600px){.pay-submit{padding:22px 20px}}
.pay-check{display:flex;gap:10px;align-items:flex-start;font-family:var(--sans);font-size:13px;line-height:1.55;color:#3a3a3a;margin-bottom:22px}
.pay-check input{margin-top:3px;flex-shrink:0}
.pay-check a{color:var(--navy);text-decoration:underline}
.pay-btn{width:100%;padding:18px;background:var(--navy);color:#fff;font-family:var(--sans);font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;border:0;cursor:pointer;transition:.18s}
.pay-btn:hover:not(:disabled){background:var(--gold);color:var(--navy)}
.pay-btn:disabled{opacity:.6;cursor:wait}
.pay-alert{background:#fff;border-left:4px solid var(--red);padding:14px 18px;margin:0 0 20px;font-family:var(--sans);font-size:13.5px;color:#a00d24;display:none}
.pay-alert ul{margin:0;padding-left:20px}
.pay-off{background:#fff;border:1px solid #e3e0d8;border-top:4px solid var(--gold);padding:44px;text-align:center;font-family:var(--sans)}
.pay-off h2{font-family:var(--serif);color:var(--navy);margin:0 0 12px}
.pay-off a{color:var(--navy);text-decoration:underline;margin:0 8px}
.pay-account{background:#fff;border:1px solid #e3e0d8;border-left:4px solid #047857;margin-bottom:20px}
.pay-account-head{display:flex;justify-content:space-between;align-items:center;padding:16px 22px;font-family:var(--sans);font-size:14px;color:var(--navy);flex-wrap:wrap;gap:10px}
.pay-account-user{opacity:.55;font-size:12px;margin-left:4px}
.pay-account-logout button{background:none;border:1px solid #d4d2cc;padding:7px 14px;font-family:var(--sans);font-size:11.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;cursor:pointer;color:#777}
.pay-account-logout button:hover{border-color:var(--red);color:var(--red)}
.pay-account-body{padding:0 22px 20px;border-top:1px solid #f0eee8}
.pay-account-note{font-family:var(--sans);font-size:13px;color:#8a5a00;background:#fff7e6;border:1px solid #f0d9a8;padding:10px 14px;margin:16px 0}
.pay-account-error{font-family:var(--sans);font-size:13px;color:#a00d24;background:#fff5f5;border:1px solid #fecaca;padding:10px 14px;margin:0 0 14px}
.pay-account-form .pay-row{margin-top:14px}
.pay-account-hist-head{font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--navy);padding-top:16px;margin-bottom:8px}
.pay-account-hist{width:100%;border-collapse:collapse;font-family:var(--sans);font-size:12.5px}
.pay-account-hist td{padding:7px 4px;border-bottom:1px solid #f0eee8;color:#3a3a3a}
.pay-account-hist td a{color:var(--navy);text-decoration:underline}
.pay-login{background:#fff;border:1px solid #e3e0d8;margin-bottom:20px}
.pay-login summary{padding:14px 22px;font-family:var(--sans);font-size:13.5px;font-weight:600;color:var(--navy);cursor:pointer;list-style:none}
.pay-login summary::-webkit-details-marker{display:none}
.pay-login-body{padding:0 22px 20px;border-top:1px solid #f0eee8}
.pay-login-form{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.pay-login-form input{flex:1;min-width:160px;padding:11px 13px;font-family:var(--sans);font-size:13.5px;border:1px solid #d4d2cc;background:var(--paper)}
.pay-login-form input:focus{outline:0;border-color:var(--gold);background:#fff}
.pay-login-form button{background:var(--navy);color:#fff;border:0;padding:11px 22px;font-family:var(--sans);font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;cursor:pointer}
.pay-login-form button:hover{background:var(--gold);color:var(--navy)}
.pay-login-hint{font-family:var(--sans);font-size:11.5px;color:#888;margin:10px 0 0}
.pay-gate{background:#fff;border:1px solid #e3e0d8;border-top:4px solid var(--navy);padding:48px 42px;text-align:center;max-width:480px;margin:0 auto}
@media (max-width:600px){.pay-gate{padding:32px 22px}}
.pay-gate-icon{width:56px;height:56px;line-height:56px;margin:0 auto 18px;background:var(--navy);color:var(--gold);border-radius:50%;font-size:22px}
.pay-gate h2{font-family:var(--serif);font-size:26px;font-weight:600;color:var(--navy);margin:0 0 10px}
.pay-gate p{font-family:var(--sans);font-size:13.5px;line-height:1.6;color:#666;margin:0 0 22px}
.pay-gate .pay-login-form{margin-top:0;flex-direction:column}
.pay-gate .pay-login-form input,.pay-gate .pay-login-form button{width:100%}
.pay-gate .pay-login-hint{margin-top:14px}
.pay-gate .pay-login-alt{margin:20px 0 0;padding-top:18px;border-top:1px solid #f0eee8;font-family:var(--sans);font-size:12.5px;display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.pay-gate .pay-login-alt a{color:var(--navy);text-decoration:underline}
</style>

<div class="pay-page">

  <section class="pay-hero">
    <div class="container">
      <div class="pay-eyebrow">Tekcan Metal · <?= h(t('pay.eyebrow', 'Güvenli Ödeme')) ?></div>
      <h1><?= t('pay.h1', 'Online <em>Ödeme</em>') ?></h1>
      <p><?= h(t('pay.lead', 'Kredi veya banka kartınızla, bankanızın 3D Secure doğrulamasıyla güvenle ödeme yapın.')) ?></p>
    </div>
  </section>

  <section class="pay-trust">
    <div class="container">
      <div class="pay-trust-grid">
        <div class="pay-trust-item"><strong><?= h(t('pay.trust1_t', '3D Secure Doğrulama')) ?></strong><span><?= h(t('pay.trust1_s', 'Ödemeniz bankanızın onay ekranında tamamlanır')) ?></span></div>
        <div class="pay-trust-item"><strong><?= h(t('pay.trust2_t', 'Kart Bilgisi Saklanmaz')) ?></strong><span><?= h(t('pay.trust2_s', 'Kart verileri sitemize uğramaz, doğrudan ödeme kuruluşuna iletilir')) ?></span></div>
        <div class="pay-trust-item"><strong><?= h(t('pay.trust3_t', 'QNBpay Altyapısı')) ?></strong><span><?= h(t('pay.trust3_s', 'Lisanslı ödeme kuruluşu üzerinden işlem')) ?></span></div>
      </div>
    </div>
  </section>

  <section class="pay-section">
    <div class="container">
      <div class="pay-wrap">

      <?php if (!$payOn || $payPaused || $payClosed): ?>
        <?php if (!$payOn && !empty($_SESSION['admin_id']) && in_array($_SESSION['admin_role'] ?? '', ['superadmin', 'admin'], true)): ?>
        <div class="pay-off" style="margin-bottom:18px;border-top-color:#c8102e;text-align:left">
          <strong>Yönetici önizlemesi:</strong> Online ödeme henüz <em>yayında değil</em>; bu menü öğesini ve sayfayı yalnızca siz görüyorsunuz.
          <a href="<?= h(url('admin/sanal-pos.php?tab=settings')) ?>" style="margin:0 0 0 6px">Sanal POS → Ayarlar</a>
        </div>
        <?php endif; ?>
        <div class="pay-off">
          <?php if ($payClosed): ?>
            <h2><?= h(t('pay.closed_title', 'Online ödeme sistemi şu anda kapalı')) ?></h2>
            <p><?= h(qnb_closed_msg()) ?></p>
            <p><?= h(t('pay.off_text', 'Ödemenizi aşağıdaki yöntemlerle yapabilirsiniz.')) ?></p>
          <?php elseif ($payPaused): ?>
            <h2><?= h(t('pay.paused_title', 'Online ödeme geçici olarak durduruldu')) ?></h2>
            <p><?= h(t('pay.paused_text', 'Güvenlik nedeniyle online ödeme kısa süreliğine durduruldu. Lütfen daha sonra tekrar deneyin veya aşağıdaki yöntemlerle ödeme yapın.')) ?></p>
          <?php else: ?>
            <h2><?= h(t('pay.off_title', 'Online ödeme şu anda kullanılamıyor')) ?></h2>
            <p><?= h(t('pay.off_text', 'Ödemenizi aşağıdaki yöntemlerle yapabilirsiniz.')) ?></p>
          <?php endif; ?>
          <p>
            <a href="<?= h(url_lang('iban.php')) ?>"><?= h(t('header.menu.iban', 'IBAN Bilgilerimiz')) ?></a>
            <a href="<?= h(url_lang('mail-order.php')) ?>"><?= h(t('header.menu.mail_order', 'Mail Order Formu')) ?></a>
            <a href="<?= h(url_lang('iletisim.php')) ?>"><?= h(t('header.menu.contact', 'İletişim')) ?></a>
          </p>
        </div>
      <?php else: ?>

        <?php if (!$cust): ?>

        <div class="pay-gate">
          <div class="pay-gate-icon">🔒</div>
          <h2><?= h(t('pay.gate_title', 'Ödeme Yapmak İçin Giriş Yapın')) ?></h2>
          <p><?= h(t('pay.gate_lead', 'Online ödeme sayfamız yalnızca kayıtlı müşterilerimize açıktır. Kullanıcı adı ve şifreniz tarafımızca size iletilmiştir.')) ?></p>
          <?php if ($custErr): ?><div class="pay-account-error"><?= h($custErr) ?></div><?php endif; ?>
          <form method="post" class="pay-login-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="customer_login">
            <input type="text" name="username" placeholder="<?= h(t('pay.cust_username', 'Kullanıcı Adı')) ?>" required autocomplete="username" autofocus>
            <input type="password" name="password" placeholder="<?= h(t('pay.cust_password', 'Şifre')) ?>" required autocomplete="current-password">
            <button type="submit"><?= h(t('pay.cust_login_btn', 'Giriş Yap')) ?></button>
          </form>
          <p class="pay-login-hint"><?= h(t('pay.cust_no_account', 'Kullanıcı adı ve şifrenizi almadıysanız veya kaybettiyseniz bizimle iletişime geçin.')) ?></p>
          <p class="pay-login-alt">
            <a href="<?= h(url_lang('iban.php')) ?>"><?= h(t('header.menu.iban', 'IBAN Bilgilerimiz')) ?></a>
            <a href="<?= h(url_lang('mail-order.php')) ?>"><?= h(t('header.menu.mail_order', 'Mail Order Formu')) ?></a>
            <a href="<?= h(url_lang('iletisim.php')) ?>"><?= h(t('header.menu.contact', 'İletişim')) ?></a>
          </p>
        </div>

        <?php elseif ($cust['must_change_password']): ?>

        <div class="pay-account">
          <div class="pay-account-head">
            <div>👤 <?= h(t('pay.cust_hello', 'Merhaba')) ?>, <strong><?= h($cust['full_name']) ?></strong>
              <span class="pay-account-user">@<?= h($cust['username']) ?></span></div>
            <form method="post" class="pay-account-logout">
              <?= csrf_field() ?><input type="hidden" name="action" value="customer_logout">
              <button type="submit"><?= h(t('pay.cust_logout', 'Çıkış Yap')) ?></button>
            </form>
          </div>
          <div class="pay-account-body">
            <p class="pay-account-note">⚠ <?= h(t('pay.cust_must_change', 'Güvenliğiniz için, size iletilen ilk şifreyi değiştirmeniz gerekiyor. Yeni şifrenizi belirledikten sonra ödeme işleminize devam edebilirsiniz.')) ?></p>
            <?php if ($custErr): ?><div class="pay-account-error"><?= h($custErr) ?></div><?php endif; ?>
            <form method="post" class="pay-account-form">
              <?= csrf_field() ?><input type="hidden" name="action" value="customer_change_password">
              <div class="pay-row">
                <div class="pay-field"><label><?= h(t('pay.cust_current_pw', 'Mevcut Şifre')) ?></label><input type="password" name="current_password" required autocomplete="current-password"></div>
                <div class="pay-field"><label><?= h(t('pay.cust_new_pw', 'Yeni Şifre (en az 8 karakter)')) ?></label><input type="password" name="new_password" minlength="8" required autocomplete="new-password"></div>
              </div>
              <button type="submit" class="pay-btn" style="width:auto;padding:12px 26px"><?= h(t('pay.cust_change_pw_btn', 'Şifreyi Değiştir')) ?></button>
            </form>
          </div>
        </div>

        <?php else: ?>

        <?php
          $custHistory = [];
          try { $custHistory = all("SELECT invoice_id, public_ref, amount, status, created_at FROM tm_payments WHERE customer_id=? ORDER BY id DESC LIMIT 5", [$cust['id']]); }
          catch (Throwable $e) { /* geçmiş listesi gösterilemezse ödeme akışı yine de çalışsın */ }
        ?>
        <div class="pay-account">
          <div class="pay-account-head">
            <div>👤 <?= h(t('pay.cust_hello', 'Merhaba')) ?>, <strong><?= h($cust['full_name']) ?></strong>
              <span class="pay-account-user">@<?= h($cust['username']) ?></span></div>
            <form method="post" class="pay-account-logout">
              <?= csrf_field() ?><input type="hidden" name="action" value="customer_logout">
              <button type="submit"><?= h(t('pay.cust_logout', 'Çıkış Yap')) ?></button>
            </form>
          </div>
          <?php if ($custHistory): ?>
          <div class="pay-account-body">
            <div class="pay-account-hist-head"><?= h(t('pay.cust_history', 'Geçmiş Ödemelerim')) ?></div>
            <table class="pay-account-hist">
              <?php foreach ($custHistory as $ch): ?>
              <tr>
                <td><?= h(tr_date($ch['created_at'])) ?></td>
                <td><?= h(qnb_money((float)$ch['amount'])) ?></td>
                <td><?= qnb_status_badge((string)$ch['status']) ?></td>
                <td><?php if ($ch['status'] === 'paid'): ?><a href="<?= h(url('odeme-dekont.php?ref=' . $ch['public_ref'])) ?>" target="_blank"><?= h(t('pay.cust_receipt', 'Dekont')) ?></a><?php endif; ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
          <?php endif; ?>
        </div>

        <div class="pay-alert" id="payErr" role="alert"></div>

        <form id="payForm" class="pay-form" novalidate autocomplete="on" data-init="<?= h(url('odeme.php')) ?>"
              data-min="<?= h((string)$payCfg['min']) ?>" data-max="<?= h((string)$payCfg['max']) ?>">
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">

          <fieldset class="pay-fieldset">
            <div class="pay-fs-head"><div class="pay-fs-num">1</div><h3><?= h(t('pay.s1', 'Ödeme Yapan')) ?></h3></div>
            <div class="pay-row">
              <div class="pay-field"><label for="pf_name"><?= h(t('pay.f_name', 'Ad Soyad')) ?> *</label>
                <input type="text" id="pf_name" name="full_name" maxlength="150" autocomplete="name" required value="<?= h($cust['full_name'] ?? '') ?>"></div>
              <div class="pay-field"><label for="pf_phone"><?= h(t('pay.f_phone', 'Telefon')) ?> *</label>
                <input type="tel" id="pf_phone" name="phone" maxlength="30" autocomplete="tel" required value="<?= h($cust['phone'] ?? '') ?>"></div>
            </div>
            <div class="pay-row">
              <div class="pay-field"><label for="pf_email"><?= h(t('pay.f_email', 'E-posta')) ?> *</label>
                <input type="email" id="pf_email" name="email" maxlength="150" autocomplete="email" required value="<?= h($cust['email'] ?? '') ?>"></div>
              <div class="pay-field"><label for="pf_company"><?= h(t('pay.f_company', 'Firma (opsiyonel)')) ?></label>
                <input type="text" id="pf_company" name="company" maxlength="150" autocomplete="organization" value="<?= h($cust['company'] ?? '') ?>"></div>
            </div>
          </fieldset>

          <fieldset class="pay-fieldset">
            <div class="pay-fs-head"><div class="pay-fs-num">2</div><h3><?= h(t('pay.s2', 'Tutar ve Açıklama')) ?></h3></div>
            <div class="pay-field" style="max-width:280px"><label for="pf_amount"><?= h(t('pay.f_amount', 'Tutar (₺)')) ?> *</label>
              <input type="text" id="pf_amount" name="amount" inputmode="decimal" placeholder="0,00" autocomplete="off" required>
              <div class="pay-hint" id="pf_amount_show" data-label="<?= h(t('pay.amount_show', 'Ödenecek tutar')) ?>"><?= h(t('pay.amount_hint', 'Örn: 12.500,00')) ?></div></div>
            <div class="pay-field"><label for="pf_desc"><?= h(t('pay.f_desc', 'Açıklama')) ?></label>
              <textarea id="pf_desc" name="description" rows="3" maxlength="300" placeholder="<?= h(t('pay.desc_ph', 'Fatura / cari / sipariş numaranız')) ?>"></textarea></div>
          </fieldset>

          <fieldset class="pay-fieldset">
            <div class="pay-fs-head"><div class="pay-fs-num">3</div><h3><?= h(t('pay.s3', 'Kart Bilgileri')) ?></h3></div>
            <div class="pay-secure">🔒 <span><?= h(t('pay.secure', 'Kart bilgileriniz sunucularımıza gönderilmez ve saklanmaz; doğrudan ödeme kuruluşuna iletilir ve bankanızın 3D Secure ekranında doğrulanır.')) ?></span></div>
            <!-- Kart alanlarında bilerek name="" yok: form hiçbir koşulda bu değerleri sunucumuza POST etmez -->
            <div class="pay-field"><label for="pc_holder"><?= h(t('pay.c_holder', 'Kart Üzerindeki Ad Soyad')) ?> *</label>
              <input type="text" id="pc_holder" autocomplete="cc-name" maxlength="80" required></div>
            <div class="pay-row r3">
              <div class="pay-field"><label for="pc_no"><?= h(t('pay.c_no', 'Kart Numarası')) ?> *</label>
                <input type="text" id="pc_no" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000" required></div>
              <div class="pay-field"><label for="pc_exp"><?= h(t('pay.c_exp', 'Son Kullanma')) ?> *</label>
                <input type="text" id="pc_exp" inputmode="numeric" autocomplete="cc-exp" maxlength="7" placeholder="AA / YY" required></div>
              <div class="pay-field"><label for="pc_cvv">CVV *</label>
                <input type="text" id="pc_cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="•••" required></div>
            </div>
          </fieldset>

          <div class="pay-submit">
            <label class="pay-check">
              <input type="checkbox" name="kvkk" value="1" required>
              <span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>" target="_blank" rel="noopener"><?= h(t('pay.kvkk_link', 'KVKK Aydınlatma Metni')) ?></a>, <a href="<?= h(url('sayfa.php?slug=mesafeli-satis-sozlesmesi')) ?>" target="_blank" rel="noopener"><?= h(t('pay.mesafeli_link', 'Mesafeli Satış Sözleşmesi')) ?></a> <?= h(t('pay.ve', 've')) ?> <a href="<?= h(url('sayfa.php?slug=iptal-iade-politikasi')) ?>" target="_blank" rel="noopener"><?= h(t('pay.iade_link', 'İptal ve İade Politikası')) ?></a>'<?= h(t('pay.kvkk_rest2', "nı okudum, anladım ve kabul ediyorum.")) ?></span>
            </label>
            <button type="submit" class="pay-btn" id="payBtn"><?= h($js['btn']) ?> →</button>
          </div>
        </form>

        <script>
        (function () {
          var I = <?= json_encode($js, JSON_UNESCAPED_UNICODE) ?>;
          var form = document.getElementById('payForm'), btn = document.getElementById('payBtn'), box = document.getElementById('payErr');
          var holder = document.getElementById('pc_holder'), no = document.getElementById('pc_no'),
              exp = document.getElementById('pc_exp'), cvv = document.getElementById('pc_cvv'),
              amt = document.getElementById('pf_amount');
          var MIN = parseFloat(form.getAttribute('data-min')) || 1, MAX = parseFloat(form.getAttribute('data-max')) || 250000;

          function digits(s) { return (s || '').replace(/\D/g, ''); }
          function luhn(n) { var s = 0, alt = false; for (var i = n.length - 1; i >= 0; i--) { var d = +n.charAt(i); if (alt) { d *= 2; if (d > 9) d -= 9; } s += d; alt = !alt; } return n.length > 0 && s % 10 === 0; }
          function parseAmount(s) {
            s = (s || '').replace(/[\s₺]|TL/gi, ''); if (!/^[0-9.,]+$/.test(s)) return null;
            var d = s.indexOf('.') > -1, c = s.indexOf(',') > -1;
            if (d && c) { s = s.lastIndexOf(',') > s.lastIndexOf('.') ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, ''); }
            else if (c) { s = (s.split(',').length === 2) ? s.replace(',', '.') : s.replace(/,/g, ''); }
            else if (d) { if (/^[1-9]\d{0,2}(\.\d{3})+$/.test(s)) { s = s.replace(/\./g, ''); } else if (s.split('.').length > 2) { return null; } }
            var v = parseFloat(s); return isNaN(v) ? null : Math.round(v * 100) / 100;
          }

          var amtShow = document.getElementById('pf_amount_show'), amtShowDef = amtShow.textContent;
          amt.addEventListener('input', function () {
            var v = parseAmount(amt.value);
            amtShow.textContent = (v === null || v <= 0) ? amtShowDef : amtShow.getAttribute('data-label') + ': ' + v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
          });
          no.addEventListener('input', function () { var v = digits(no.value).slice(0, 19); no.value = v.replace(/(.{4})/g, '$1 ').trim(); });
          exp.addEventListener('input', function () { var v = digits(exp.value).slice(0, 4); exp.value = v.length >= 3 ? v.slice(0, 2) + ' / ' + v.slice(2) : v; });
          cvv.addEventListener('input', function () { cvv.value = digits(cvv.value).slice(0, 4); });

          function show(list) {
            if (!list.length) { box.style.display = 'none'; box.innerHTML = ''; return; }
            var ul = document.createElement('ul');
            list.forEach(function (m) { var li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
            box.innerHTML = ''; box.appendChild(ul); box.style.display = 'block';
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }

          function validate() {
            var e = [];
            if (form.elements.full_name.value.trim().split(/\s+/).length < 2) e.push(I.name);
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.elements.email.value.trim())) e.push(I.email);
            if (digits(form.elements.phone.value).length < 10) e.push(I.phone);
            var a = parseAmount(amt.value);
            if (a === null || a <= 0) e.push(I.amount); else if (a < MIN || a > MAX) e.push(I.range);
            if (!holder.value.trim()) e.push(I.holder);
            var n = digits(no.value); if (n.length < 13 || !luhn(n)) e.push(I.card);
            var ed = digits(exp.value), mm = parseInt(ed.slice(0, 2), 10), yy = parseInt(ed.slice(2, 4), 10), now = new Date();
            var okExp = ed.length === 4 && mm >= 1 && mm <= 12 && (2000 + yy > now.getFullYear() || (2000 + yy === now.getFullYear() && mm >= now.getMonth() + 1));
            if (!okExp) e.push(I.expiry);
            if (digits(cvv.value).length < 3) e.push(I.cvv);
            if (!form.elements.kvkk.checked) e.push(I.kvkk);
            return e;
          }

          function toGateway(j) {
            var f = document.createElement('form'); f.method = 'POST'; f.action = j.action; f.acceptCharset = 'UTF-8';
            function add(k, v) { var i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v; f.appendChild(i); }
            Object.keys(j.fields).forEach(function (k) { add(k, j.fields[k]); });
            var ed = digits(exp.value);
            add('cc_holder_name', holder.value.trim());
            add('cc_no', digits(no.value));
            add('expiry_month', ed.slice(0, 2));
            add('expiry_year', '20' + ed.slice(2, 4));
            add('cvv', digits(cvv.value));
            document.body.appendChild(f); f.submit();
          }

          form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var errs = validate(); show(errs); if (errs.length) return;
            btn.disabled = true; btn.textContent = I.processing;

            var fd = new FormData(form); fd.append('action', 'init');   // yalnızca name'li alanlar (kart alanları YOK)
            fetch(form.getAttribute('data-init'), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
              .then(function (r) { return r.json().catch(function () { return { ok: false, error: I.generic }; }); })
              .then(function (j) { if (!j.ok) throw new Error(j.error || I.generic); toGateway(j); })
              .catch(function (err) { show([err.message || I.generic]); btn.disabled = false; btn.textContent = I.btn + ' →'; });
          });

          // Bankadan "geri" ile dönülürse butonu yeniden etkinleştir
          window.addEventListener('pageshow', function (e) { if (e.persisted) { btn.disabled = false; btn.textContent = I.btn + ' →'; } });
        })();
        </script>

        <?php endif; ?>

      <?php endif; ?>

      </div>
    </div>
  </section>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
