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

$siteShort = settings('site_short_name', 'Tekcan Metal');
$logoPath  = settings('logo', 'assets/img/logo.png');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= h($pageTitle) ?> — <?= h($siteShort) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<meta name="robots" content="<?= h($metaRobots) ?>">
<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ============================================================
 * ÖZEL, BAĞIMSIZ ÖDEME EKRANI — kurumsal site kabuğundan (menü, hero,
 * footer) tamamen ayrı. Yalnızca ödeme işlemine odaklanan, banka/ödeme
 * kuruluşu ekranlarının sadeliğini hedefleyen bir tasarım.
 * ============================================================ */
:root{--navy:#050d24;--navy-2:#0c1e44;--gold:#c9a86b;--gold-dark:#a88a4a;--red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--ink:#1a1a1a;--line:#e7e4dc;--muted:#767268;--serif:'Cormorant Garamond',Georgia,serif;--sans:'Inter',system-ui,sans-serif}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;overflow-x:hidden;font-family:var(--sans);color:var(--ink);background:linear-gradient(180deg,#f3f1ec 0%,#eceae3 100%);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
a{color:inherit}
.ck-top{background:var(--navy);border-bottom:3px solid var(--red)}
.ck-top-inner{max-width:1080px;margin:0 auto;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.ck-brand{display:flex;align-items:center;gap:10px}
.ck-brand-mark{width:34px;height:34px;border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:18px;font-weight:600;color:var(--gold);flex-shrink:0}
.ck-brand-text{font-family:var(--serif);font-size:16.5px;font-weight:600;color:#fff;letter-spacing:.3px}
.ck-brand-text em{font-style:italic;color:var(--gold)}
.ck-secure-chip{display:flex;align-items:center;gap:7px;font-size:11.5px;font-weight:600;color:rgba(255,255,255,.85);letter-spacing:.3px;white-space:nowrap}
.ck-secure-chip .dot{width:7px;height:7px;border-radius:50%;background:#3ecf8e;box-shadow:0 0 0 3px rgba(62,207,142,.2)}
@media (max-width:480px){.ck-secure-chip span.txt{display:none}}

.ck-main{flex:1;padding:36px 18px 46px;display:flex;justify-content:center}
.ck-shell{width:100%;max-width:460px}
.ck-amount-badge{display:none}

/* Canlı kart önizlemesi — ödeme formunda kullanıcı yazdıkça güncellenir */
.ck-cardpreview{width:100%;max-width:400px;margin:0 auto 22px;aspect-ratio:1.586;border-radius:16px;position:relative;overflow:hidden;
  background:linear-gradient(135deg,#0c1e44 0%,#143672 45%,#1e4a9e 100%);box-shadow:0 16px 40px rgba(5,13,36,.28);color:#fff;
  padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between}
.ck-cardpreview::before{content:'';position:absolute;top:-40%;right:-20%;width:75%;height:180%;background:radial-gradient(ellipse,rgba(201,168,107,.16) 0%,transparent 65%);pointer-events:none}
.ck-cp-top{display:flex;justify-content:space-between;align-items:flex-start;position:relative;z-index:1}
.ck-cp-chip{width:38px;height:28px;border-radius:5px;background:linear-gradient(135deg,#e8d9ab,var(--gold));position:relative}
.ck-cp-chip::after{content:'';position:absolute;inset:5px;border:1px solid rgba(10,20,40,.35);border-radius:2px}
.ck-cp-network{font-family:var(--serif);font-size:13px;font-weight:600;letter-spacing:1.5px;color:rgba(255,255,255,.55);text-transform:uppercase}
.ck-cp-number{font-family:'JetBrains Mono',ui-monospace,monospace;font-size:clamp(16px,4.6vw,20px);letter-spacing:2.5px;font-weight:500;position:relative;z-index:1;margin:6px 0}
.ck-cp-bottom{display:flex;justify-content:space-between;align-items:flex-end;position:relative;z-index:1;gap:14px}
.ck-cp-holder,.ck-cp-exp{display:flex;flex-direction:column;gap:3px;min-width:0}
.ck-cp-exp{align-items:flex-end}
.ck-cp-lbl{font-size:8.5px;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.5);font-weight:600}
.ck-cp-val{font-family:var(--sans);font-size:13.5px;font-weight:600;letter-spacing:.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}

.ck-card{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04),0 18px 44px rgba(15,13,8,.08);overflow:hidden}
.pay-off{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04),0 18px 44px rgba(15,13,8,.08);padding:40px 34px;text-align:center;font-family:var(--sans)}
.pay-off h2{font-family:var(--serif);color:var(--navy);margin:0 0 12px;font-size:24px}
.pay-off p{font-size:13.5px;line-height:1.6;color:var(--muted)}
.pay-off a{color:var(--navy);text-decoration:underline;margin:0 8px;font-size:13px}
.ck-admin-note{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04);border-left:4px solid var(--red);padding:16px 20px;margin-bottom:16px;font-family:var(--sans);font-size:13px;text-align:left}
.ck-admin-note a{color:var(--navy)}

.pay-gate{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04),0 18px 44px rgba(15,13,8,.08);padding:40px 34px;text-align:center}
@media (max-width:480px){.pay-gate{padding:30px 22px}}
.pay-gate-icon{width:52px;height:52px;line-height:52px;margin:0 auto 16px;background:var(--navy);color:var(--gold);border-radius:50%;font-size:20px}
.pay-gate h2{font-family:var(--serif);font-size:23px;font-weight:600;color:var(--navy);margin:0 0 8px}
.pay-gate p{font-family:var(--sans);font-size:13px;line-height:1.6;color:var(--muted);margin:0 0 20px}
.pay-gate .pay-login-form{margin-top:0;flex-direction:column}
.pay-gate .pay-login-form input,.pay-gate .pay-login-form button{width:100%}
.pay-gate .pay-login-hint{margin-top:14px}
.pay-gate .pay-login-alt{margin:18px 0 0;padding-top:16px;border-top:1px solid var(--line);font-family:var(--sans);font-size:12px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pay-gate .pay-login-alt a{color:var(--navy);text-decoration:underline}

.pay-login-form{display:flex;gap:10px;flex-wrap:wrap}
.pay-login-form input{flex:1;min-width:160px;padding:13px 14px;font-family:var(--sans);font-size:16px;border:1px solid #d8d5cc;border-radius:8px;background:var(--paper)}
.pay-login-form input:focus{outline:0;border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,107,.15)}
.pay-login-form button{background:var(--navy);color:#fff;border:0;border-radius:8px;padding:13px 22px;font-family:var(--sans);font-size:12.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;cursor:pointer;transition:.18s}
.pay-login-form button:hover{background:var(--navy-2)}
.pay-login-hint{font-family:var(--sans);font-size:11.5px;color:var(--muted);margin:10px 0 0}
.pay-account-error{font-family:var(--sans);font-size:13px;color:var(--red-dark);background:#fff5f5;border-radius:8px;border:1px solid #fecaca;padding:10px 14px;margin:0 0 14px;text-align:left}

.pay-account{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04);margin-bottom:16px}
.pay-account-head{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;font-family:var(--sans);font-size:13.5px;color:var(--navy);flex-wrap:wrap;gap:10px}
.pay-account-user{opacity:.5;font-size:11.5px;margin-left:4px}
.pay-account-logout button{background:none;border:1px solid #d8d5cc;border-radius:7px;padding:7px 14px;font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;cursor:pointer;color:#8a8578;transition:.18s}
.pay-account-logout button:hover{border-color:var(--red);color:var(--red)}
.pay-account-body{padding:0 20px 18px;border-top:1px solid var(--line)}
.pay-account-note{font-family:var(--sans);font-size:12.5px;color:#8a5a00;background:#fff7e6;border-radius:8px;border:1px solid #f0d9a8;padding:10px 14px;margin:16px 0}
.pay-account-form .pay-row{margin-top:14px}
.pay-account-hist-head{font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:1.1px;text-transform:uppercase;color:var(--navy);padding-top:16px;margin-bottom:8px}
.pay-account-hist-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.pay-account-hist{width:100%;border-collapse:collapse;font-family:var(--sans);font-size:12px;min-width:340px}
.pay-account-hist td{padding:7px 4px;border-bottom:1px solid var(--line);color:#3a3a3a}
.pay-account-hist td a{color:var(--navy);text-decoration:underline}

.ck-card-head{padding:22px 26px 4px;text-align:center}
.ck-card-head h1{font-family:var(--serif);font-size:24px;font-weight:600;color:var(--navy);margin:0 0 4px}
.ck-card-head p{font-family:var(--sans);font-size:12.5px;color:var(--muted);margin:0}
.pay-fieldset{padding:22px 26px;border:0;border-bottom:1px solid var(--line);margin:0}
@media (max-width:480px){.pay-fieldset{padding:18px 18px}}
.pay-fs-head{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.pay-fs-num{width:26px;height:26px;border-radius:50%;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:14px;font-weight:600;flex-shrink:0}
.pay-fs-head h3{font-family:var(--serif);font-size:18px;font-weight:600;margin:0;color:var(--navy)}
.pay-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.pay-row.r3{grid-template-columns:1.4fr 1fr 1fr}
@media (max-width:480px){.pay-row{grid-template-columns:1fr}
  .pay-row.r3{display:grid;grid-template-columns:1fr 1fr;grid-template-areas:"no no" "exp cvv"}
  .pay-row.r3 .pay-field:nth-child(1){grid-area:no}.pay-row.r3 .pay-field:nth-child(2){grid-area:exp}.pay-row.r3 .pay-field:nth-child(3){grid-area:cvv}}
.pay-field{display:flex;flex-direction:column;margin-bottom:14px}
.pay-row .pay-field{margin-bottom:0}
.pay-field label{font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--navy);margin-bottom:6px}
.pay-field input,.pay-field textarea{width:100%;padding:12px 13px;font-family:var(--sans);font-size:16px;border:1px solid #d8d5cc;border-radius:8px;background:var(--paper);box-sizing:border-box;transition:.15s}
.pay-field input:focus,.pay-field textarea:focus{outline:0;border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,107,.15)}
.pay-field textarea{resize:vertical;min-height:72px}
.pay-hint{font-family:var(--sans);font-size:11px;color:#999;margin-top:4px}
.pay-secure{display:flex;gap:10px;align-items:flex-start;background:#f2f7f4;border-radius:8px;border-left:3px solid #047857;padding:11px 14px;margin-bottom:18px;font-family:var(--sans);font-size:12px;line-height:1.5;color:#0b4a34}
.pay-submit{background:var(--paper);padding:22px 26px}
@media (max-width:480px){.pay-submit{padding:18px}}
.pay-check{display:flex;gap:9px;align-items:flex-start;font-family:var(--sans);font-size:12px;line-height:1.5;color:#3a3a3a;margin-bottom:18px}
.pay-check input{margin-top:3px;flex-shrink:0;width:16px;height:16px}
.pay-check a{color:var(--navy);text-decoration:underline}
.pay-btn{width:100%;padding:16px;background:var(--navy);color:#fff;font-family:var(--sans);font-size:12.5px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;border:0;border-radius:9px;cursor:pointer;transition:.18s}
.pay-btn:hover:not(:disabled){background:var(--navy-2)}
.pay-btn:disabled{opacity:.6;cursor:wait}
.pay-btn-auto{width:auto;padding:12px 24px;border-radius:8px}
@media (max-width:480px){.pay-btn-auto{width:100%}}
.pay-alert{background:#fff5f5;border-radius:8px;border-left:4px solid var(--red);padding:13px 16px;margin:0 0 16px;font-family:var(--sans);font-size:13px;color:var(--red-dark);display:none}
.pay-alert ul{margin:0;padding-left:18px}

.ck-trust{display:flex;justify-content:center;gap:18px;flex-wrap:wrap;margin-top:20px;padding:0 8px}
.ck-trust-item{display:flex;align-items:center;gap:6px;font-family:var(--sans);font-size:11px;color:var(--muted);font-weight:500}
.ck-trust-item .ico{font-size:13px}
.ck-cardnet{display:flex;justify-content:center;gap:10px;margin-top:14px}
.ck-cardnet span{display:inline-flex;align-items:center;justify-content:center;width:38px;height:24px;border-radius:4px;background:#fff;border:1px solid var(--line);font-size:8.5px;font-weight:800;letter-spacing:.3px;color:#7a7a7a}

.ck-footer{text-align:center;padding:22px 18px 28px;font-family:var(--sans);font-size:11.5px;color:#9a9689}
.ck-footer a{color:#9a9689;text-decoration:underline}
.ck-footer a:hover{color:var(--navy)}
.ck-footer .sep{margin:0 7px;opacity:.6}
</style>
</head>
<body>

<header class="ck-top">
  <div class="ck-top-inner">
    <div class="ck-brand">
      <span class="ck-brand-mark">T</span>
      <span class="ck-brand-text">TEKCAN <em>METAL</em></span>
    </div>
    <div class="ck-secure-chip"><span class="dot"></span><span class="txt"><?= h(t('pay.secure_badge', 'Güvenli Bağlantı')) ?></span></div>
  </div>
</header>

<main class="ck-main">
  <div class="ck-shell">

  <?php if (!$payOn || $payPaused || $payClosed): ?>
    <?php if (!$payOn && !empty($_SESSION['admin_id']) && in_array($_SESSION['admin_role'] ?? '', ['superadmin', 'admin'], true)): ?>
    <div class="ck-admin-note">
      <strong>Yönetici önizlemesi:</strong> Online ödeme henüz <em>yayında değil</em>; bu sayfayı yalnızca siz görüyorsunuz.
      <a href="<?= h(url('admin/sanal-pos.php?tab=settings')) ?>">Sanal POS → Ayarlar</a>
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
          <button type="submit" class="pay-btn pay-btn-auto"><?= h(t('pay.cust_change_pw_btn', 'Şifreyi Değiştir')) ?></button>
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
        <div class="pay-account-hist-wrap"><table class="pay-account-hist">
          <?php foreach ($custHistory as $ch): ?>
          <tr>
            <td><?= h(tr_date($ch['created_at'])) ?></td>
            <td><?= h(qnb_money((float)$ch['amount'])) ?></td>
            <td><?= qnb_status_badge((string)$ch['status']) ?></td>
            <td><?php if ($ch['status'] === 'paid'): ?><a href="<?= h(url('odeme-dekont.php?ref=' . $ch['public_ref'])) ?>" target="_blank"><?= h(t('pay.cust_receipt', 'Dekont')) ?></a><?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        </table></div>
      </div>
      <?php endif; ?>
    </div>

    <div class="ck-cardpreview" id="ckCardPreview">
      <div class="ck-cp-top">
        <div class="ck-cp-chip"></div>
        <div class="ck-cp-network" id="ckCardNet">&nbsp;</div>
      </div>
      <div class="ck-cp-number" id="ckCardNumber">•••• •••• •••• ••••</div>
      <div class="ck-cp-bottom">
        <div class="ck-cp-holder"><span class="ck-cp-lbl"><?= h(t('pay.cp_holder', 'Kart Sahibi')) ?></span><span class="ck-cp-val" id="ckCardHolder"><?= h(t('pay.cp_holder_ph', 'AD SOYAD')) ?></span></div>
        <div class="ck-cp-exp"><span class="ck-cp-lbl"><?= h(t('pay.cp_exp', 'Son Kul.')) ?></span><span class="ck-cp-val" id="ckCardExp">AA/YY</span></div>
      </div>
    </div>

    <div class="pay-alert" id="payErr" role="alert"></div>

    <div class="ck-card">
      <div class="ck-card-head">
        <h1><?= h(t('pay.card_h1', 'Güvenli Ödeme')) ?></h1>
        <p><?= h(t('pay.card_lead', '3D Secure doğrulamalı, kart bilgisi saklanmayan ödeme.')) ?></p>
      </div>
    <form id="payForm" novalidate autocomplete="on" data-init="<?= h(url('odeme.php')) ?>"
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
    </div>

    <div class="ck-trust">
      <div class="ck-trust-item"><span class="ico">🔒</span> <?= h(t('pay.trust1_t', '3D Secure')) ?></div>
      <div class="ck-trust-item"><span class="ico">🛡</span> <?= h(t('pay.trust2_t', 'Kart Bilgisi Saklanmaz')) ?></div>
      <div class="ck-trust-item"><span class="ico">✓</span> <?= h(t('pay.trust3_t', 'QNBpay Altyapısı')) ?></div>
    </div>

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

      /* ---- Canlı kart önizlemesi (yalnızca görsel; sunucuya hiçbir şey göndermez) ---- */
      var cpNum = document.getElementById('ckCardNumber'), cpHolder = document.getElementById('ckCardHolder'),
          cpExp = document.getElementById('ckCardExp'), cpNet = document.getElementById('ckCardNet'),
          cpHolderDef = cpHolder.textContent;
      function cardNetwork(n) {
        if (/^4/.test(n)) return 'VISA';
        if (/^(5[1-5]|2[2-7])/.test(n)) return 'MASTERCARD';
        if (/^9792/.test(n)) return 'TROY';
        if (/^3[47]/.test(n)) return 'AMEX';
        return '';
      }
      function updateCardPreview() {
        var n = digits(no.value);
        var grouped = (n + '•••••••••••••••••'.slice(0, Math.max(0, 16 - n.length))).slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
        cpNum.textContent = n.length ? grouped : '•••• •••• •••• ••••';
        cpHolder.textContent = holder.value.trim() ? holder.value.trim().toUpperCase() : cpHolderDef;
        var ed = digits(exp.value);
        cpExp.textContent = ed.length ? (ed.slice(0, 2) || 'AA') + '/' + (ed.slice(2, 4) || 'YY') : 'AA/YY';
        cpNet.textContent = cardNetwork(n);
      }

      no.addEventListener('input', function () { var v = digits(no.value).slice(0, 19); no.value = v.replace(/(.{4})/g, '$1 ').trim(); updateCardPreview(); });
      exp.addEventListener('input', function () { var v = digits(exp.value).slice(0, 4); exp.value = v.length >= 3 ? v.slice(0, 2) + ' / ' + v.slice(2) : v; updateCardPreview(); });
      cvv.addEventListener('input', function () { cvv.value = digits(cvv.value).slice(0, 4); });
      holder.addEventListener('input', updateCardPreview);

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
</main>

<footer class="ck-footer">
  <p>© <?= h(date('Y')) ?> <?= h($siteShort) ?>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>">KVKK</a>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=mesafeli-satis-sozlesmesi')) ?>">Mesafeli Satış Sözleşmesi</a>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=iptal-iade-politikasi')) ?>">İptal ve İade Politikası</a>
    <span class="sep">·</span><a href="<?= h(url('iletisim.php')) ?>">İletişim</a>
  </p>
</footer>

</body>
</html>
