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
$showWorkspace = $payOn && !$payPaused && !$payClosed && $cust && !$cust['must_change_password'];
$showSplit  = $payOn && !$payPaused && !$payClosed && !$cust;

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
// Koyu (lacivert) zeminler için şeffaf/beyaz logo varsa onu kullan; yoksa normal logoya, o da yoksa metin işaretine düş.
$logoWhitePath = preg_replace('/\.(png|svg|jpe?g)$/i', '-white.$1', $logoPath) ?: $logoPath;
$logoOnDark = file_exists(__DIR__ . '/' . $logoWhitePath) ? $logoWhitePath : (file_exists(__DIR__ . '/' . $logoPath) ? $logoPath : null);
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
  --paper:#f6f7f9;--ink:#1a1a1a;--line:#e2e4e9;--muted:#6b7280;--serif:'Cormorant Garamond',Georgia,serif;--sans:'Inter',system-ui,sans-serif;--shadow:0 1px 2px rgba(15,13,8,.05),0 1px 6px rgba(15,13,8,.04)}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;overflow-x:hidden;font-family:var(--sans);color:var(--ink);background:#eef0f3;min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
a{color:inherit}
.ck-top{background:var(--navy);border-bottom:3px solid var(--red)}
.ck-top-inner{max-width:1080px;margin:0 auto;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.ck-brand{display:flex;align-items:center;gap:10px}
.ck-brand-logo{height:38px;width:auto;max-width:220px;display:block;object-fit:contain}
@media (max-width:480px){.ck-brand-logo{height:30px;max-width:160px}}
.ck-brand-mark{width:34px;height:34px;border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:18px;font-weight:600;color:var(--gold);flex-shrink:0}
.ck-brand-text{font-family:var(--serif);font-size:16.5px;font-weight:600;color:#fff;letter-spacing:.3px}
.ck-brand-text em{font-style:italic;color:var(--gold)}
.ck-secure-chip{display:flex;align-items:center;gap:7px;font-size:11.5px;font-weight:600;color:rgba(255,255,255,.85);letter-spacing:.3px;white-space:nowrap}
.ck-secure-chip .dot{width:7px;height:7px;border-radius:50%;background:#3ecf8e;box-shadow:0 0 0 3px rgba(62,207,142,.2)}
@media (max-width:480px){.ck-secure-chip span.txt{display:none}}

.ck-main{flex:1;padding:22px 16px 30px;display:flex;justify-content:center}
.ck-shell{width:100%;max-width:460px}
.ck-shell-wide{width:100%;max-width:900px}
.ck-shell-app{width:100%;max-width:none}
.ck-workspace{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:16px;align-items:start}
.ck-workspace-main{min-width:0}
.ck-workspace-side{position:sticky;top:16px;display:flex;flex-direction:column;gap:12px;min-width:0}
@media (max-width:860px){.ck-workspace{grid-template-columns:minmax(0,1fr)}.ck-workspace-side{position:static}}

/* v1.0.146 — Tam sayfa (edge-to-edge) sol navbarlı app kabuğu; kart/kenar boşluğu YOK — Peker Demir referansıyla birebir yerleşim */
html:has(body.ck-body-app),body.ck-body-app{height:100%}
body.ck-body-app{overflow:hidden}
.ck-top-app{display:none}
.ck-main-app{padding:0;display:block;overflow:hidden}
.ck-main-app .ck-shell-app{height:100vh}
.ck-app{display:flex;background:#fff;height:100vh;overflow:hidden}
.ck-nav{width:224px;flex-shrink:0;background:var(--navy);color:#fff;display:flex;flex-direction:column;padding:0 12px 16px;height:100vh;overflow-y:auto}
.ck-nav-brand{display:flex;align-items:center;gap:9px;padding:18px 8px 16px}
.ck-nav-brand img{height:30px;width:auto;max-width:170px;display:block;object-fit:contain}
.ck-nav-brand .mark{width:30px;height:30px;border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:16px;font-weight:600;color:var(--gold);flex-shrink:0}
.ck-nav-brand .txt{font-family:var(--serif);font-size:15px;font-weight:600;color:#fff;letter-spacing:.3px}
.ck-nav-brand .txt em{font-style:italic;color:var(--gold)}
.ck-nav-user{display:flex;align-items:center;gap:10px;padding:2px 8px 16px;margin-bottom:12px;border-bottom:1px solid rgba(255,255,255,.1)}
.ck-nav-avatar{width:34px;height:34px;border-radius:50%;background:rgba(201,168,107,.16);border:1px solid rgba(201,168,107,.5);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--sans);font-weight:700;font-size:13px;flex-shrink:0}
.ck-nav-user-info{min-width:0}
.ck-nav-user-name{font-family:var(--sans);font-size:12.5px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ck-nav-user-uname{font-family:var(--sans);font-size:10.5px;color:rgba(255,255,255,.5)}
.ck-nav-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:3px;flex:1}
.ck-nav-link{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:7px;color:rgba(255,255,255,.72);font-family:var(--sans);font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;transition:.15s;background:transparent;border:0;width:100%;text-align:left}
.ck-nav-link svg{width:17px;height:17px;flex-shrink:0}
.ck-nav-link:hover{background:rgba(255,255,255,.06);color:#fff}
.ck-nav-link.active{background:rgba(201,168,107,.14);color:var(--gold)}
.ck-nav-foot{border-top:1px solid rgba(255,255,255,.1);padding-top:10px;margin-top:6px}
.ck-nav-logout{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:7px;color:rgba(255,255,255,.55);font-family:var(--sans);font-size:12.5px;font-weight:600;background:none;border:0;width:100%;text-align:left;cursor:pointer;transition:.15s}
.ck-nav-logout:hover{background:rgba(200,16,46,.15);color:#ff9d9d}
.ck-nav-logout svg{width:16px;height:16px;flex-shrink:0}

.ck-app-main{flex:1;min-width:0;height:100vh;overflow-y:auto;background:var(--paper);padding:0}
.ck-app-topbar{position:sticky;top:0;z-index:5;background:#fff;border-bottom:1px solid var(--line);padding:16px 28px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.ck-app-topbar h1{font-family:var(--sans);font-size:18px;font-weight:800;color:#1a1a1a;margin:0;letter-spacing:-.2px}
.ck-app-topbar-actions{display:flex;align-items:center;gap:8px}
.ck-app-topbar-btn{display:inline-flex;align-items:center;gap:7px;background:var(--navy);color:#fff;border:0;border-radius:7px;padding:9px 16px;font-family:var(--sans);font-size:12.5px;font-weight:700;cursor:pointer;text-decoration:none;transition:.15s}
.ck-app-topbar-btn:hover{background:var(--navy-2)}
.ck-app-topbar-btn.ghost{background:#fff;color:var(--navy);border:1px solid var(--line)}
.ck-app-topbar-btn.ghost:hover{border-color:var(--red);color:var(--red)}
.ck-app-body{padding:22px 28px 40px}
.ck-tab{display:none}
.ck-tab.active{display:block}
.ck-panel-head{margin-bottom:16px}
.ck-panel-head h1{display:none}
.ck-panel-head p{font-family:var(--sans);font-size:12.5px;color:var(--muted);margin:0}

.lbl-short{display:none}
@media (max-width:900px){
  html:has(body.ck-body-app),body.ck-body-app{height:auto;overflow:visible}
  .ck-top-app{display:block}
  .ck-main-app .ck-shell-app{height:auto}
  .ck-app{height:auto}
  .ck-nav{height:auto}
  .ck-app-main{height:auto}
  .ck-app-body{padding:16px 14px 30px}
  .ck-app-topbar{padding:12px 14px}
  .ck-app{flex-direction:column;min-height:auto}
  .ck-nav{width:100%;flex-direction:row;align-items:center;padding:8px 8px;gap:4px}
  .ck-nav-brand{display:none}
  .ck-nav-user{display:none}
  .ck-nav-list{flex-direction:row;flex:1;overflow-x:auto;gap:2px;min-width:0}
  .ck-nav-link{white-space:nowrap;padding:8px 9px;font-size:11px;gap:6px}
  .ck-nav-link svg{width:15px;height:15px}
  .ck-nav-foot{border-top:0;border-left:1px solid rgba(255,255,255,.12);padding:0 0 0 6px;margin:0;flex-shrink:0}
  .ck-nav-logout{padding:8px 9px;font-size:11px;gap:6px}
  .ck-nav-logout svg{width:15px;height:15px}
  .lbl-full{display:none}
  .lbl-short{display:inline}
}
.ck-security-box{background:#fff;border:1px solid var(--line);border-radius:8px;padding:16px;box-shadow:var(--shadow)}
.ck-security-box h4{margin:0 0 12px;font-family:var(--sans);font-size:13px;font-weight:700;color:#1a1a1a}
.ck-security-item{display:flex;gap:8px;align-items:flex-start;font-family:var(--sans);font-size:12px;color:#4b5563;margin-bottom:10px;line-height:1.4}
.ck-security-item:last-child{margin-bottom:0}
.ck-security-item svg{width:15px;height:15px;flex-shrink:0;color:#2f9e5c;margin-top:1px}
.ck-amount-badge{display:none}

/* Giriş ekranı — split-screen (yalnızca kayıtlı müşteri girişi beklenirken; ödeme formu ve
   hesap ekranı işlevsel formlar olduğu için tek-kart düzeninde kalır). v1.0.144: kurumsal
   yeniden tasarım — sağ panelde çelik/profil temalı geometrik doku + rozet + güven listesi,
   sol panelde yeni sans-serif başlık dili ve mini güven şeridi.
   v1.0.148: masaüstünde ekran boyutu ne olursa olsun TEK SAYFAYA (kaydırmasız) sığacak
   şekilde tamamen akışkan (clamp/vh tabanlı) ölçülendirme; ürün galerisi 8 kalemle 4x2 düzene çıktı. */
html:has(body.ck-body-split),body.ck-body-split{height:100%}
body.ck-body-split{overflow:hidden}
body.ck-body-split .ck-top-inner{padding:clamp(8px,1.6vh,16px) 22px}
body.ck-body-split .ck-footer{padding:clamp(6px,1.4vh,14px) 18px;font-size:11px}
.ck-main-split{padding:0;align-items:stretch;min-height:0;overflow:hidden}
.ck-shell-split{display:grid;grid-template-columns:1fr 1fr;max-width:1080px;width:100%;height:100%;
  border-radius:10px;overflow:hidden;box-shadow:0 1px 2px rgba(15,13,8,.06),0 12px 40px rgba(5,13,36,.10)}

/* v1.0.149 — musteriportal.tekcanmetal.com referansına göre kurumsal, "premium" giriş ekranı:
   sağ panel editoryal başlık + ürün galerisi + segmentli güven şeridi, sol panelde üç renkli
   şeritli yüzen kart, güvenli bağlantı rozeti, ikonlu alanlar ve kırmızı degrade CTA. */
.ck-split-left{background:linear-gradient(180deg,#f6f4ef 0%,#f1efe8 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:clamp(14px,3.4vh,40px) clamp(18px,3.6vw,40px);height:100%;overflow-y:auto;min-height:0}
.ck-split-left .pay-gate{background:#fff;border-radius:14px;position:relative;overflow:hidden;text-align:center;
  box-shadow:0 1px 2px rgba(15,13,8,.06),0 22px 50px rgba(5,13,36,.12);border:1px solid var(--line);
  padding:clamp(6px,1.4vh,10px) clamp(18px,3vw,30px) clamp(16px,3vh,30px);max-width:378px;width:100%}
.ck-split-left .pay-gate::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;
  background:linear-gradient(90deg,var(--red) 0 33.3%,var(--gold) 33.3% 66.6%,var(--navy-2) 66.6% 100%)}
.ck-split-conn{display:flex;align-items:center;justify-content:center;gap:6px;margin:clamp(10px,2.2vh,16px) 0 clamp(10px,2vh,16px);
  font-family:var(--sans);font-size:10px;font-weight:700;letter-spacing:.6px;color:#4a7a5e;text-transform:uppercase}
.ck-split-conn .dot{width:6px;height:6px;border-radius:50%;background:#3ecf8e;box-shadow:0 0 0 3px rgba(62,207,142,.18);flex-shrink:0}
.ck-split-conn .sep{color:#c8c3b5;font-weight:400}
.ck-split-conn .bit{color:#9a9689}
.pay-gate-kicker{font-family:var(--sans);font-size:10.5px;font-weight:800;letter-spacing:1.4px;color:var(--red);text-transform:uppercase;margin:0 0 4px}
.pay-gate h2{font-family:var(--serif);font-size:clamp(19px,3.4vh,27px);font-weight:700;color:var(--navy);margin:0 0 clamp(4px,1vh,8px);letter-spacing:-.2px;line-height:1.18}
.pay-gate p{font-family:var(--sans);font-size:clamp(11.5px,1.5vh,12.5px);line-height:1.5;color:var(--muted);margin:0 0 clamp(10px,2vh,16px)}
.ck-split-left .pay-login-form{margin-top:0;flex-direction:column;gap:clamp(8px,1.6vh,12px)}
.ck-split-left .pay-login-form .fld{position:relative}
.ck-split-left .pay-login-form .fld > svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#a8a49a;pointer-events:none}
.ck-split-left .pay-login-form input{width:100%;padding:12px 14px 12px 38px!important;font-size:14px;border-radius:8px}
.ck-split-left .pay-login-form .pw-field input{padding-right:42px!important}
.ck-split-left .pay-login-form input:-webkit-autofill{-webkit-text-fill-color:var(--ink);box-shadow:0 0 0 40px #fff inset}
.ck-split-left .pay-login-form button[type="submit"]{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
  background:linear-gradient(135deg,var(--red) 0%,var(--red-dark) 100%);border-radius:8px;padding:12.5px 18px;font-size:12.5px;
  box-shadow:0 10px 22px rgba(200,16,46,.26);letter-spacing:1px}
.ck-split-left .pay-login-form button[type="submit"]:hover{background:linear-gradient(135deg,var(--red-dark) 0%,#7c0a1a 100%)}
.ck-split-left .pay-login-form button[type="submit"] svg{width:15px;height:15px;flex-shrink:0}
.ck-split-trustrow{display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;margin-top:clamp(8px,1.6vh,12px);
  font-family:var(--sans);font-size:10.5px;color:#5a8a6c}
.ck-split-trustrow span{display:inline-flex;align-items:center;gap:4px}
.ck-split-trustrow svg{width:12px;height:12px;color:#3ecf8e;flex-shrink:0}
.ck-split-trustrow .sep{color:#d5d2c8}
.ck-split-left .pay-gate .pay-login-hint{font-size:11px;color:var(--muted);margin:clamp(8px,1.6vh,12px) 0 0}
.pay-gate .pay-login-alt{margin:clamp(10px,2vh,14px) 0 0;padding-top:clamp(10px,2vh,14px);border-top:1px solid var(--line);font-family:var(--sans);font-size:11.5px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pay-gate .pay-login-alt a{color:var(--navy);text-decoration:underline}

.ck-split-right{background:
    repeating-linear-gradient(115deg,rgba(255,255,255,.035) 0 2px,transparent 2px 46px),
    linear-gradient(160deg,#0a1730 0%,var(--navy) 45%,var(--navy-2) 100%);
  position:relative;overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:clamp(14px,3.6vh,52px) clamp(20px,4vw,48px);color:#fff;height:100%;min-height:0;
  --gth:clamp(40px,8vh,82px)}
.ck-split-right::before{content:'';position:absolute;width:480px;height:480px;border:1px solid rgba(201,168,107,.16);
  border-radius:50%;right:-190px;bottom:-190px;pointer-events:none}
.ck-split-right::after{content:'';position:absolute;width:260px;height:260px;border:1px solid rgba(255,255,255,.06);
  border-radius:50%;left:-110px;top:-110px;pointer-events:none}
.ck-split-kicker{position:relative;z-index:1;display:flex;align-items:center;gap:7px;font-family:var(--sans);
  font-size:10px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:rgba(255,255,255,.6);
  margin-bottom:clamp(8px,1.8vh,16px);flex-shrink:0}
.ck-split-kicker .dot{width:6px;height:6px;border-radius:50%;background:var(--gold);flex-shrink:0}
.ck-split-right h2{font-family:var(--serif);font-size:clamp(19px,3.2vh,28px);font-weight:700;text-align:center;max-width:360px;line-height:1.22;
  position:relative;z-index:1;margin:0 0 clamp(4px,1vh,10px);letter-spacing:-.2px;flex-shrink:0}
.ck-split-right h2 em{font-style:italic;color:var(--gold)}
.ck-split-right p{font-family:var(--sans);font-size:clamp(10.5px,1.4vh,12.5px);color:rgba(255,255,255,.62);text-align:center;max-width:300px;
  position:relative;z-index:1;margin:0 0 clamp(10px,2.2vh,22px);line-height:1.5;flex-shrink:0}
.ck-split-trust{position:relative;z-index:1;width:100%;max-width:340px;display:flex;align-items:stretch;justify-content:center;
  border:1px solid rgba(255,255,255,.14);border-radius:10px;overflow:hidden;flex-shrink:0;background:rgba(255,255,255,.03)}
.ck-split-trust .t{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
  padding:clamp(8px,1.6vh,12px) 6px;text-align:center;border-left:1px solid rgba(255,255,255,.1)}
.ck-split-trust .t:first-child{border-left:0}
.ck-split-trust .t svg{width:15px;height:15px;color:var(--gold)}
.ck-split-trust .t span{font-family:var(--sans);font-size:clamp(8.5px,1.05vh,9.5px);font-weight:700;letter-spacing:.2px;
  color:rgba(255,255,255,.78);line-height:1.25}

/* v1.0.147/148/149 — sağ panelde ürün galerisi (Boru / Profil / Sac / Panel / Hadde / Genişletilmiş Sac / Delikli Sac / Trapez Sac) */
.ck-split-gallery{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,var(--gth));justify-content:center;
  gap:clamp(6px,1.2vh,10px);margin:0 0 clamp(10px,2.2vh,22px);flex-shrink:1}
.ck-split-gallery .g{width:var(--gth);display:flex;flex-direction:column;align-items:center;gap:clamp(3px,.7vh,6px)}
.ck-split-gallery .g-thumb{width:100%;aspect-ratio:1;border-radius:9px;overflow:hidden;position:relative;
  background:linear-gradient(160deg,#0e2148 0%,#152b57 100%);border:1px solid rgba(201,168,107,.28);
  box-shadow:0 8px 18px rgba(0,0,0,.22)}
.ck-split-gallery .g-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.ck-split-gallery .g[data-pad] .g-thumb img{object-fit:contain;padding:8%}
.ck-split-gallery .g-thumb::after{content:'';position:absolute;inset:0;box-shadow:inset 0 0 0 1px rgba(255,255,255,.06);border-radius:9px;pointer-events:none}
.ck-split-gallery .g-lbl{font-family:var(--sans);font-size:clamp(7.5px,1vh,9.5px);font-weight:700;letter-spacing:.4px;color:rgba(255,255,255,.72);
  text-transform:uppercase;text-align:center;line-height:1.2}
@media (max-width:900px){
  html:has(body.ck-body-split),body.ck-body-split{height:auto;overflow:visible}
  body.ck-body-split .ck-top-inner{padding:16px 22px}
  body.ck-body-split .ck-footer{padding:22px 18px 28px;font-size:11.5px}
  .ck-main-split{padding:0;overflow:visible}
  .ck-shell-split{grid-template-columns:1fr;border-radius:0;box-shadow:none;height:auto;min-height:auto}
  .ck-split-right{display:none}
  .ck-split-left{padding:36px 20px;height:auto;overflow:visible;background:#fff}
  .ck-split-left .pay-gate{box-shadow:none;border:0;border-radius:0;padding:0;max-width:360px}
  .ck-split-left .pay-gate::before{display:none}
}

/* Canlı kart önizlemesi — ödeme formunda kullanıcı yazdıkça güncellenir */
.ck-cardpreview{width:100%;max-width:300px;margin:0 auto 14px;aspect-ratio:1.586;border-radius:10px;position:relative;overflow:hidden;
  background:linear-gradient(135deg,#0c1e44 0%,#143672 45%,#1e4a9e 100%);box-shadow:0 6px 18px rgba(5,13,36,.22);color:#fff;
  padding:14px 16px;display:flex;flex-direction:column;justify-content:space-between}
.ck-cardpreview::before{content:'';position:absolute;top:-40%;right:-20%;width:75%;height:180%;background:radial-gradient(ellipse,rgba(201,168,107,.16) 0%,transparent 65%);pointer-events:none}
.ck-cp-top{display:flex;justify-content:space-between;align-items:flex-start;position:relative;z-index:1}
.ck-cp-chip{width:28px;height:20px;border-radius:4px;background:linear-gradient(135deg,#e8d9ab,var(--gold));position:relative}
.ck-cp-chip::after{content:'';position:absolute;inset:5px;border:1px solid rgba(10,20,40,.35);border-radius:2px}
.ck-cp-network{font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,.55);text-transform:uppercase}
.ck-cp-number{font-family:'JetBrains Mono',ui-monospace,monospace;font-size:clamp(13px,3.6vw,15px);letter-spacing:1.6px;font-weight:500;position:relative;z-index:1;margin:4px 0}
.ck-cp-bottom{display:flex;justify-content:space-between;align-items:flex-end;position:relative;z-index:1;gap:14px}
.ck-cp-holder,.ck-cp-exp{display:flex;flex-direction:column;gap:3px;min-width:0}
.ck-cp-exp{align-items:flex-end}
.ck-cp-lbl{font-size:7.5px;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.5);font-weight:600}
.ck-cp-val{font-family:var(--sans);font-size:11.5px;font-weight:600;letter-spacing:.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}

.ck-card{background:#fff;border-radius:8px;border:1px solid var(--line);box-shadow:var(--shadow);overflow:hidden}
.pay-off{background:#fff;border-radius:8px;border:1px solid var(--line);box-shadow:var(--shadow);padding:26px 24px;text-align:center;font-family:var(--sans)}
.pay-off h2{font-family:var(--serif);color:var(--navy);margin:0 0 8px;font-size:19px}
.pay-off p{font-size:13.5px;line-height:1.6;color:var(--muted)}
.pay-off a{color:var(--navy);text-decoration:underline;margin:0 8px;font-size:13px}
.ck-admin-note{background:#fff;border-radius:8px;border:1px solid var(--line);border-left:3px solid var(--red);padding:12px 16px;margin-bottom:12px;font-family:var(--sans);font-size:12.5px;text-align:left}
.ck-admin-note a{color:var(--navy)}

.pay-gate{background:#fff;border-radius:8px;border:1px solid var(--line);box-shadow:var(--shadow);padding:28px 26px;text-align:center}
@media (max-width:480px){.pay-gate{padding:22px 18px}}
.pay-gate h2{font-family:var(--sans);font-size:19px;font-weight:700;color:var(--navy);margin:0 0 6px;letter-spacing:-.2px}
.pay-gate p{font-family:var(--sans);font-size:12.5px;line-height:1.55;color:var(--muted);margin:0 0 14px}
.pay-gate .pay-login-form{margin-top:0;flex-direction:column}
.pay-gate .pay-login-form input,.pay-gate .pay-login-form button:not(.pw-toggle){width:100%}
.pay-gate .pay-login-hint{margin-top:14px}
.pay-gate .pay-login-alt{margin:12px 0 0;padding-top:12px;border-top:1px solid var(--line);font-family:var(--sans);font-size:11.5px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pay-gate .pay-login-alt a{color:var(--navy);text-decoration:underline}

.pay-login-form{display:flex;gap:10px;flex-wrap:wrap}
.pay-login-form input{flex:1;min-width:160px;padding:10px 12px;font-family:var(--sans);font-size:15px;border:1px solid #d5d8de;border-radius:6px;background:var(--paper)}
.pay-login-form .pw-field{flex:1;min-width:160px}
.pw-field{position:relative}
.pw-field input{width:100%;padding-right:42px!important}
.pw-toggle{position:absolute;right:4px;top:50%;transform:translateY(-50%);background:none;border:0;cursor:pointer;color:#9a9689;padding:8px;display:flex;align-items:center;justify-content:center;border-radius:6px}
.pw-toggle:hover{color:var(--navy)}
.pw-toggle .eye-off{display:none}
.pw-toggle.is-visible .eye-on{display:none}
.pw-toggle.is-visible .eye-off{display:block}
.pay-login-form input:focus{outline:0;border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,107,.15)}
.pay-login-form button:not(.pw-toggle){background:var(--navy);color:#fff;border:0;border-radius:6px;padding:10px 18px;font-family:var(--sans);font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;cursor:pointer;transition:.18s}
.pay-login-form button:not(.pw-toggle):hover{background:var(--navy-2)}
.pay-login-hint{font-family:var(--sans);font-size:11px;color:var(--muted);margin:8px 0 0}
.pay-account-error{font-family:var(--sans);font-size:13px;color:var(--red-dark);background:#fff5f5;border-radius:8px;border:1px solid #fecaca;padding:10px 14px;margin:0 0 14px;text-align:left}

.pay-account{background:#fff;border-radius:8px;border:1px solid var(--line);box-shadow:var(--shadow);margin-bottom:12px}
.pay-account-head{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;font-family:var(--sans);font-size:13px;color:var(--navy);flex-wrap:wrap;gap:10px}
.pay-account-user{opacity:.5;font-size:11.5px;margin-left:4px}
.pay-account-logout button{background:none;border:1px solid #d5d8de;border-radius:6px;padding:6px 12px;font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;cursor:pointer;color:#8a8a8a;transition:.18s}
.pay-account-logout button:hover{border-color:var(--red);color:var(--red)}
.pay-account-body{padding:0 16px 12px;border-top:1px solid var(--line)}
.pay-account-note{font-family:var(--sans);font-size:12.5px;color:#8a5a00;background:#fff7e6;border-radius:8px;border:1px solid #f0d9a8;padding:10px 14px;margin:16px 0}
.pay-account-form .pay-row{margin-top:14px}
.pay-account-hist-head{font-family:var(--sans);font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--navy);padding-top:12px;margin-bottom:6px}
.pay-account-hist-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.pay-account-hist{width:100%;border-collapse:collapse;font-family:var(--sans);font-size:11.5px;min-width:340px}
.pay-account-hist td{padding:6px 4px;border-bottom:1px solid var(--line);color:#3a3a3a}
.pay-account-hist td a{color:var(--navy);text-decoration:underline}

.pay-stats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;padding:12px 16px 2px}
.pay-stats-grid-4{padding:0;grid-template-columns:repeat(4,1fr)}
@media (max-width:900px){.pay-stats-grid-4{grid-template-columns:repeat(2,1fr)}}
.pay-stat{position:relative;min-width:0;background:#fff;border-radius:8px;padding:12px 12px 11px 14px;overflow:hidden;border:1px solid var(--line);border-left:4px solid transparent}
.pay-stat.green{border-left-color:#2f9e5c}
.pay-stat.gold{border-left-color:#d69a1f}
.pay-stat.blue{border-left-color:#2f6fa8}
.pay-stat.red{border-left-color:var(--red)}
.pay-stat-ic{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;margin-bottom:8px}
.pay-stat-ic svg{width:14px;height:14px}
.pay-stat-ic.green{background:rgba(47,158,92,.12);color:#2f9e5c}
.pay-stat-ic.gold{background:rgba(214,154,31,.14);color:#b9820f}
.pay-stat-ic.blue{background:rgba(47,111,168,.12);color:#2f6fa8}
.pay-stat-ic.red{background:rgba(200,16,46,.1);color:var(--red)}
.pay-stat-label{font-family:var(--sans);font-size:11px;font-weight:600;letter-spacing:0;color:#6b7280;margin-bottom:4px;line-height:1.35}
.pay-stat-value{font-family:var(--sans);font-size:19px;font-weight:700;color:#1a1a1a}
@media (max-width:360px){.pay-stats-grid{grid-template-columns:1fr;gap:8px}.pay-stat-value{font-size:14px}}

.pay-empty{text-align:center;padding:20px 14px 18px;color:#767b85}
.pay-empty-ic{font-size:22px;margin-bottom:6px;opacity:.5}
.pay-empty p{font-family:var(--sans);font-size:12px;margin:0 0 10px;color:#5c6270}
.pay-empty-btn{display:inline-block;background:var(--navy);color:#fff;font-family:var(--sans);font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;padding:8px 18px;border-radius:6px;text-decoration:none;transition:.18s}
.pay-empty-btn:hover{background:var(--navy-2)}

.ck-card-head{padding:16px 20px 2px;text-align:left}
.ck-card-head h1{font-family:var(--sans);font-size:17px;font-weight:700;color:#1a1a1a;margin:0 0 3px}
.ck-card-head p{font-family:var(--sans);font-size:11.5px;color:var(--muted);margin:0}
.pay-fieldset{padding:14px 20px;border:0;border-bottom:1px solid var(--line);margin:0}
@media (max-width:480px){.pay-fieldset{padding:12px 16px}}
.pay-fs-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.pay-fs-num{width:21px;height:21px;border-radius:50%;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--sans);font-size:11px;font-weight:700;flex-shrink:0}
.pay-fs-head h3{font-family:var(--sans);font-size:13.5px;font-weight:700;margin:0;color:var(--navy);letter-spacing:.2px}
.pay-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
.pay-row.r3{grid-template-columns:1.4fr 1fr 1fr}
@media (max-width:480px){.pay-row{grid-template-columns:1fr}
  .pay-row.r3{display:grid;grid-template-columns:1fr 1fr;grid-template-areas:"no no" "exp cvv"}
  .pay-row.r3 .pay-field:nth-child(1){grid-area:no}.pay-row.r3 .pay-field:nth-child(2){grid-area:exp}.pay-row.r3 .pay-field:nth-child(3){grid-area:cvv}}
.pay-field{display:flex;flex-direction:column;margin-bottom:10px}
.pay-row .pay-field{margin-bottom:0}
.pay-field label{font-family:var(--sans);font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--navy);margin-bottom:4px}
.pay-field input,.pay-field textarea{width:100%;padding:9px 11px;font-family:var(--sans);font-size:15px;border:1px solid #d5d8de;border-radius:6px;background:var(--paper);box-sizing:border-box;transition:.15s}
.pay-field input:focus,.pay-field textarea:focus{outline:0;border-color:var(--gold);background:#fff;box-shadow:0 0 0 3px rgba(201,168,107,.15)}
.pay-field textarea{resize:vertical;min-height:56px}
.pay-hint{font-family:var(--sans);font-size:10.5px;color:#999;margin-top:3px}
.pay-secure{display:flex;gap:8px;align-items:flex-start;background:#f2f7f4;border-radius:6px;border-left:3px solid #047857;padding:9px 12px;margin-bottom:12px;font-family:var(--sans);font-size:11.5px;line-height:1.45;color:#0b4a34}
.pay-submit{background:var(--paper);padding:14px 20px}
@media (max-width:480px){.pay-submit{padding:12px 16px}}
.pay-check{display:flex;gap:8px;align-items:flex-start;font-family:var(--sans);font-size:11.5px;line-height:1.45;color:#3a3a3a;margin-bottom:12px}
.pay-check input{margin-top:3px;flex-shrink:0;width:16px;height:16px}
.pay-check a{color:var(--navy);text-decoration:underline}
.pay-btn{width:100%;padding:12px;background:var(--navy);color:#fff;font-family:var(--sans);font-size:12px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;border:0;border-radius:6px;cursor:pointer;transition:.18s}
.pay-btn:hover:not(:disabled){background:var(--navy-2)}
.pay-btn:disabled{opacity:.6;cursor:wait}
.pay-btn-auto{width:auto;padding:9px 18px;border-radius:6px}
@media (max-width:480px){.pay-btn-auto{width:100%}}
.pay-alert{background:#fff5f5;border-radius:6px;border-left:3px solid var(--red);padding:10px 14px;margin:0 0 12px;font-family:var(--sans);font-size:12.5px;color:var(--red-dark);display:none}
.pay-alert ul{margin:0;padding-left:18px}

.ck-trust{display:flex;justify-content:center;gap:16px;flex-wrap:wrap;margin-top:14px;padding:0 8px}
.ck-trust-item{display:flex;align-items:center;gap:6px;font-family:var(--sans);font-size:11px;color:var(--muted);font-weight:500}
.ck-trust-item .ico{font-size:13px}
.ck-cardnet{display:flex;justify-content:center;gap:10px;margin-top:14px}
.ck-cardnet span{display:inline-flex;align-items:center;justify-content:center;width:38px;height:24px;border-radius:4px;background:#fff;border:1px solid var(--line);font-size:8.5px;font-weight:800;letter-spacing:.3px;color:#7a7a7a}

.ck-footer{text-align:center;padding:16px 18px 20px;font-family:var(--sans);font-size:11px;color:#9a9689}
.ck-footer a{color:#9a9689;text-decoration:underline}
.ck-footer a:hover{color:var(--navy)}
.ck-footer .sep{margin:0 7px;opacity:.6}
</style>
</head>
<body<?= $showWorkspace ? ' class="ck-body-app"' : ($showSplit ? ' class="ck-body-split"' : '') ?>>

<header class="ck-top<?= $showWorkspace ? ' ck-top-app' : '' ?>">
  <div class="ck-top-inner">
    <div class="ck-brand">
      <?php if ($logoOnDark): ?>
        <img src="<?= h(url($logoOnDark)) ?>" alt="<?= h($siteShort) ?>" class="ck-brand-logo">
      <?php else: ?>
        <span class="ck-brand-mark">T</span>
        <span class="ck-brand-text">TEKCAN <em>METAL</em></span>
      <?php endif; ?>
    </div>
    <div class="ck-secure-chip"><span class="dot"></span><span class="txt"><?= h(t('pay.secure_badge', 'Güvenli Bağlantı')) ?></span></div>
  </div>
</header>

<main class="ck-main<?= $showSplit ? ' ck-main-split' : '' ?><?= $showWorkspace ? ' ck-main-app' : '' ?>">
  <div class="<?= $showSplit ? 'ck-shell-split' : ($showWorkspace ? 'ck-shell-app' : 'ck-shell') ?>">
  <?php if ($showSplit): ?><div class="ck-split-left"><?php endif; ?>

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
      <div class="ck-split-conn">
        <span class="dot"></span><?= h(t('pay.secure_badge', 'Güvenli Bağlantı')) ?><span class="sep">·</span><span class="bit">TLS 256-BIT</span>
      </div>
      <div class="pay-gate-kicker"><?= h(t('pay.gate_kicker', 'Müşteri Girişi')) ?></div>
      <h2><?= h(t('pay.gate_title', 'Ödeme Yapmak İçin Giriş Yapın')) ?></h2>
      <p><?= h(t('pay.gate_lead', 'Online ödeme sayfamız yalnızca kayıtlı müşterilerimize açıktır. Kullanıcı adı ve şifreniz tarafımızca size iletilmiştir.')) ?></p>
      <?php if ($custErr): ?><div class="pay-account-error"><?= h($custErr) ?></div><?php endif; ?>
      <form method="post" class="pay-login-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="customer_login">
        <div class="fld">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input type="text" name="username" placeholder="<?= h(t('pay.cust_username', 'Kullanıcı Adı')) ?>" required autocomplete="username" autofocus>
        </div>
        <div class="fld pw-field">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="password" id="gatePassword" name="password" placeholder="<?= h(t('pay.cust_password', 'Şifre')) ?>" required autocomplete="current-password">
          <button type="button" class="pw-toggle" data-pw-toggle="#gatePassword" aria-label="<?= h(t('pay.pw_show', 'Şifreyi göster')) ?>"><svg class="eye-on" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg></button>
        </div>
        <button type="submit"><?= h(t('pay.cust_login_btn', 'Giriş Yap')) ?>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>
      <div class="ck-split-trustrow">
        <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?= h(t('pay.trust_enc', 'Verileriniz şifrelenir')) ?></span>
        <span class="sep">·</span>
        <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?= h(t('pay.trust_kvkk', 'KVKK uyumlu erişim')) ?></span>
      </div>
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
            <div class="pay-field"><label><?= h(t('pay.cust_current_pw', 'Mevcut Şifre')) ?></label>
              <div class="pw-field"><input type="password" id="curPw" name="current_password" required autocomplete="current-password"><button type="button" class="pw-toggle" data-pw-toggle="#curPw" aria-label="<?= h(t('pay.pw_show', 'Şifreyi göster')) ?>"><svg class="eye-on" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg></button></div>
            </div>
            <div class="pay-field"><label><?= h(t('pay.cust_new_pw', 'Yeni Şifre (en az 8 karakter)')) ?></label>
              <div class="pw-field"><input type="password" id="newPw" name="new_password" minlength="8" required autocomplete="new-password"><button type="button" class="pw-toggle" data-pw-toggle="#newPw" aria-label="<?= h(t('pay.pw_show', 'Şifreyi göster')) ?>"><svg class="eye-on" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg></button></div>
            </div>
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

      $custStats = ['paid_count' => 0, 'paid_sum' => 0.0, 'pending_count' => 0, 'pending_sum' => 0.0, 'failed_count' => 0, 'today_sum' => 0.0];
      try {
        $custStats['paid_count']    = (int)val("SELECT COUNT(*) FROM tm_payments WHERE customer_id=? AND status='paid'", [$cust['id']]);
        $custStats['paid_sum']      = (float)val("SELECT COALESCE(SUM(amount),0) FROM tm_payments WHERE customer_id=? AND status='paid'", [$cust['id']]);
        $custStats['pending_count'] = (int)val("SELECT COUNT(*) FROM tm_payments WHERE customer_id=? AND status='pending'", [$cust['id']]);
        $custStats['pending_sum']   = (float)val("SELECT COALESCE(SUM(amount),0) FROM tm_payments WHERE customer_id=? AND status='pending'", [$cust['id']]);
        $custStats['failed_count']  = (int)val("SELECT COUNT(*) FROM tm_payments WHERE customer_id=? AND status IN ('failed','review')", [$cust['id']]);
        $custStats['today_sum']     = (float)val("SELECT COALESCE(SUM(amount),0) FROM tm_payments WHERE customer_id=? AND status='paid' AND DATE(created_at)=CURDATE()", [$cust['id']]);
      } catch (Throwable $e) { /* istatistikler gösterilemezse ödeme akışı yine de çalışsın */ }
    ?>
    <?php $custInitial = mb_strtoupper(mb_substr(trim((string)$cust['full_name']), 0, 1, 'UTF-8'), 'UTF-8'); ?>
    <div class="ck-app" id="ckApp">
      <nav class="ck-nav">
        <div class="ck-nav-brand">
          <?php if ($logoOnDark): ?>
            <img src="<?= h(url($logoOnDark)) ?>" alt="<?= h($siteShort) ?>">
          <?php else: ?>
            <span class="mark">T</span><span class="txt">TEKCAN <em>METAL</em></span>
          <?php endif; ?>
        </div>
        <div class="ck-nav-user">
          <div class="ck-nav-avatar"><?= h($custInitial ?: '?') ?></div>
          <div class="ck-nav-user-info">
            <div class="ck-nav-user-name"><?= h($cust['full_name']) ?></div>
            <div class="ck-nav-user-uname">@<?= h($cust['username']) ?></div>
          </div>
        </div>
        <ul class="ck-nav-list">
          <li><button type="button" class="ck-nav-link active" data-tab="ozet">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            <span class="lbl"><?= h(t('pay.nav_ozet', 'Özet')) ?></span></button></li>
          <li><button type="button" class="ck-nav-link" data-tab="odeme">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            <span class="lbl"><?= h(t('pay.nav_odeme', 'Ödeme Yap')) ?></span></button></li>
          <li><a class="ck-nav-link" href="<?= h(url('odeme-gecmisim.php')) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="lbl lbl-full"><?= h(t('pay.nav_gecmis', 'Geçmiş Ödemelerim')) ?></span><span class="lbl lbl-short"><?= h(t('pay.nav_gecmis_short', 'Geçmiş')) ?></span></a></li>
        </ul>
        <div class="ck-nav-foot">
          <form method="post">
            <?= csrf_field() ?><input type="hidden" name="action" value="customer_logout">
            <button type="submit" class="ck-nav-logout">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              <span class="lbl"><?= h(t('pay.cust_logout', 'Çıkış Yap')) ?></span>
            </button>
          </form>
        </div>
      </nav>

      <div class="ck-app-main">

        <div class="ck-app-topbar">
          <h1 id="ckTopbarTitle"><?= h(t('pay.nav_ozet', 'Özet')) ?></h1>
          <div class="ck-app-topbar-actions">
            <a href="#odeme" class="ck-app-topbar-btn" data-tab-link="odeme">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              <?= h(t('pay.cust_empty_cta', 'Yeni Tahsilat')) ?>
            </a>
            <form method="post" style="margin:0">
              <?= csrf_field() ?><input type="hidden" name="action" value="customer_logout">
              <button type="submit" class="ck-app-topbar-btn ghost"><?= h(t('pay.cust_logout', 'Çıkış Yap')) ?></button>
            </form>
          </div>
        </div>

        <div class="ck-app-body">

        <div class="ck-tab active" id="tab-ozet" data-tab-panel="ozet">
          <div class="ck-panel-head">
            <div><h1><?= h(t('pay.nav_ozet', 'Özet')) ?></h1><p>👤 <?= h(t('pay.cust_hello', 'Merhaba')) ?>, <?= h($cust['full_name']) ?></p></div>
          </div>

          <div class="pay-stats-grid pay-stats-grid-4">
            <div class="pay-stat green">
              <div class="pay-stat-ic green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></div>
              <div class="pay-stat-label"><?= h(t('pay.stat_paid', 'Toplam Tahsilat')) ?> (<?= (int)$custStats['paid_count'] ?> <?= h(t('pay.stat_op', 'işlem')) ?>)</div>
              <div class="pay-stat-value"><?= h(qnb_money($custStats['paid_sum'])) ?></div>
            </div>
            <div class="pay-stat gold">
              <div class="pay-stat-ic gold"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
              <div class="pay-stat-label"><?= h(t('pay.stat_pending', 'Bekleyen Ödeme')) ?> (<?= (int)$custStats['pending_count'] ?> <?= h(t('pay.stat_op', 'işlem')) ?>)</div>
              <div class="pay-stat-value"><?= h(qnb_money($custStats['pending_sum'])) ?></div>
            </div>
            <div class="pay-stat blue">
              <div class="pay-stat-ic blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
              <div class="pay-stat-label"><?= h(t('pay.stat_today', 'Bugünkü Tahsilat')) ?></div>
              <div class="pay-stat-value"><?= h(qnb_money($custStats['today_sum'])) ?></div>
            </div>
            <div class="pay-stat red">
              <div class="pay-stat-ic red"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
              <div class="pay-stat-label"><?= h(t('pay.stat_failed', 'Başarısız / İncelemede')) ?></div>
              <div class="pay-stat-value"><?= (int)$custStats['failed_count'] ?> <span style="font-size:12px;font-weight:600;opacity:.6"><?= h(t('pay.stat_op', 'işlem')) ?></span></div>
            </div>
          </div>

          <div class="ck-card" style="margin-top:16px">
            <div class="pay-account-body" style="padding-top:14px">
              <div class="pay-account-hist-head" style="display:flex;justify-content:space-between;align-items:center;padding-top:0">
                <span><?= h(t('pay.cust_history', 'Son Ödemeler')) ?></span>
                <a href="<?= h(url('odeme-gecmisim.php')) ?>" style="color:var(--navy);text-transform:none;letter-spacing:0;font-weight:600;font-size:12px"><?= h(t('pay.cust_history_all', 'Tümünü gör')) ?> →</a>
              </div>
              <?php if ($custHistory): ?>
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
              <?php else: ?>
              <div class="pay-empty">
                <div class="pay-empty-ic">🧾</div>
                <p><?= h(t('pay.cust_empty', 'Henüz işlem yok. İlk tahsilatınızı aşağıdan oluşturabilirsiniz.')) ?></p>
                <a href="#odeme" class="pay-empty-btn" data-tab-link="odeme"><?= h(t('pay.cust_empty_cta', 'Yeni Tahsilat')) ?></a>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="ck-tab" id="tab-odeme" data-tab-panel="odeme">
          <div class="ck-panel-head">
            <div><h1><?= h(t('pay.card_h1', 'Güvenli Ödeme')) ?></h1><p><?= h(t('pay.card_lead', '3D Secure doğrulamalı, kart bilgisi saklanmayan ödeme.')) ?></p></div>
          </div>

          <div class="pay-alert" id="payErr" role="alert"></div>

          <div class="ck-workspace">
          <div class="ck-workspace-main">
          <div class="ck-card">
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
    </div><!-- .ck-card -->
    </div><!-- .ck-workspace-main -->

    <div class="ck-workspace-side">
      <div class="ck-cardpreview" id="ckCardPreview">
        <div class="ck-cp-top">
          <div class="ck-cp-chip"></div>
          <div class="ck-cp-network" id="ckCardNet">VISA</div>
        </div>
        <div class="ck-cp-number" id="ckCardNumber">•••• •••• •••• ••••</div>
        <div class="ck-cp-bottom">
          <div class="ck-cp-holder"><span class="ck-cp-lbl"><?= h(t('pay.cp_holder', 'Kart Sahibi')) ?></span><span class="ck-cp-val" id="ckCardHolder">AD SOYAD</span></div>
          <div class="ck-cp-exp"><span class="ck-cp-lbl"><?= h(t('pay.cp_exp', 'S.K.T.')) ?></span><span class="ck-cp-val" id="ckCardExp">AA/YY</span></div>
        </div>
      </div>

      <div class="ck-security-box">
        <h4><?= h(t('pay.security_h', 'Güvenlik')) ?></h4>
        <div class="ck-security-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"></path></svg><span><?= h(t('pay.security_1', '3D Secure ile bankanız tarafından doğrulanır.')) ?></span></div>
        <div class="ck-security-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg><span><?= h(t('pay.security_2', 'Kart bilgileriniz sunucularımızda saklanmaz.')) ?></span></div>
        <div class="ck-security-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M9 15l2 2 4-4"></path></svg><span><?= h(t('pay.security_3', 'Tüm işlemler kayıt altına alınır.')) ?></span></div>
      </div>
    </div><!-- .ck-workspace-side -->
    </div><!-- .ck-workspace -->

        </div><!-- #tab-odeme -->
        </div><!-- .ck-app-body -->
      </div><!-- .ck-app-main -->
    </div><!-- .ck-app -->

    <?php if (!$showWorkspace): ?>
    <div class="ck-trust">
      <div class="ck-trust-item"><span class="ico">🔒</span> <?= h(t('pay.trust1_t', '3D Secure')) ?></div>
      <div class="ck-trust-item"><span class="ico">🛡</span> <?= h(t('pay.trust2_t', 'Kart Bilgisi Saklanmaz')) ?></div>
      <div class="ck-trust-item"><span class="ico">✓</span> <?= h(t('pay.trust3_t', 'QNBpay Altyapısı')) ?></div>
    </div>
    <?php endif; ?>

    <script>
    (function () {
      /* Sol navbar sekme geçişi (Özet / Ödeme Yap) — sayfa yenilenmez, tam URL hash ile hatırlanır */
      var navLinks = Array.prototype.slice.call(document.querySelectorAll('.ck-nav-link[data-tab]'));
      var panels = Array.prototype.slice.call(document.querySelectorAll('.ck-tab[data-tab-panel]'));
      var topbarTitle = document.getElementById('ckTopbarTitle');
      var topbarTitles = { ozet: <?= json_encode(t('pay.nav_ozet', 'Özet'), JSON_UNESCAPED_UNICODE) ?>, odeme: <?= json_encode(t('pay.card_h1', 'Güvenli Ödeme'), JSON_UNESCAPED_UNICODE) ?> };
      var topbarNewBtn = document.querySelector('.ck-app-topbar-btn[data-tab-link="odeme"]');
      function activateTab(tab) {
        navLinks.forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-tab') === tab); });
        panels.forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-tab-panel') === tab); });
        if (topbarTitle && topbarTitles[tab]) topbarTitle.textContent = topbarTitles[tab];
        if (topbarNewBtn) topbarNewBtn.style.display = (tab === 'odeme') ? 'none' : '';
        if (window.history && history.replaceState) history.replaceState(null, '', '#' + tab);
      }
      navLinks.forEach(function (a) { a.addEventListener('click', function () { activateTab(a.getAttribute('data-tab')); }); });
      Array.prototype.slice.call(document.querySelectorAll('[data-tab-link]')).forEach(function (el) {
        el.addEventListener('click', function (ev) { ev.preventDefault(); activateTab(el.getAttribute('data-tab-link')); });
      });
      var initialTab = (location.hash || '').replace('#', '');
      if (initialTab === 'odeme') activateTab('odeme');
    })();
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

  <?php if ($showSplit): ?>
    </div><!-- .ck-split-left -->
    <div class="ck-split-right">
      <div class="ck-split-kicker"><span class="dot"></span><?= h(t('pay.split_kicker', 'Tekcan Metal Ödeme Alanı')) ?></div>
      <h2><?= h(t('pay.split_title1', 'Tekcan Metal ile')) ?> <em><?= h(t('pay.split_title2', 'güvenli ödeme')) ?></em></h2>
      <p><?= h(t('pay.split_lead', 'Bankanızın 3D Secure altyapısıyla kart bilgileriniz korunur. Tüm işlemler kayıt altına alınır.')) ?></p>
      <div class="ck-split-gallery">
        <?php foreach ([
          ['boru.jpg',               'pay.gal_boru',      'Boru'],
          ['profil.jpg',             'pay.gal_profil',    'Profil'],
          ['sac.jpg',                'pay.gal_sac',       'Sac'],
          ['genisletilmis-sac.jpg',  'pay.gal_gensac',    'Genişletilmiş Sac'],
          ['delikli-sac.png',        'pay.gal_deliklisac','Delikli Sac'],
          ['trapez-sac.png',         'pay.gal_trapezsac', 'Trapez Sac'],
          ['panel.png',              'pay.gal_panel',     'Panel'],
          ['hadde.jpg',              'pay.gal_hadde',     'Hadde'],
        ] as [$galFile, $galKey, $galDefault]): ?>
          <div class="g"<?= in_array($galFile, ['panel.png', 'delikli-sac.png'], true) ? ' data-pad' : '' ?>>
            <div class="g-thumb"><img src="<?= h(url('assets/img/login/' . $galFile)) ?>" alt="<?= h(t($galKey, $galDefault)) ?>" loading="lazy"></div>
            <span class="g-lbl"><?= h(t($galKey, $galDefault)) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="ck-split-trust">
        <div class="t">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 6v6c0 5.25 3.6 9.7 8 11 4.4-1.3 8-5.75 8-11V6z"/><path d="m9 12 2 2 4-4"/></svg>
          <span><?= h(t('pay.split_t1', '3D Secure')) ?></span>
        </div>
        <div class="t">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span><?= h(t('pay.split_t2', 'Kart Saklanmaz')) ?></span>
        </div>
        <div class="t">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
          <span><?= h(t('pay.split_t3', 'Kayıt Altında')) ?></span>
        </div>
      </div>
    </div>
  <?php endif; ?>
  </div>
</main>

<script>
/* Şifre göster/gizle — sayfada hangi ekran render edilmiş olursa olsun (giriş, şifre
   değiştirme) çalışır; bu yüzden tüm koşullu bloklardan bağımsız, tek yerde tanımlanır. */
(function () {
  document.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.querySelector(btn.getAttribute('data-pw-toggle'));
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.classList.toggle('is-visible', show);
      btn.setAttribute('aria-label', show ? <?= json_encode(t('pay.pw_hide', 'Şifreyi gizle'), JSON_UNESCAPED_UNICODE) ?> : <?= json_encode(t('pay.pw_show', 'Şifreyi göster'), JSON_UNESCAPED_UNICODE) ?>);
    });
  });
})();
</script>

<?php if (!$showWorkspace): ?>
<footer class="ck-footer">
  <p>© <?= h(date('Y')) ?> <?= h($siteShort) ?>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>">KVKK</a>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=mesafeli-satis-sozlesmesi')) ?>">Mesafeli Satış Sözleşmesi</a>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=iptal-iade-politikasi')) ?>">İptal ve İade Politikası</a>
    <span class="sep">·</span><a href="<?= h(url('iletisim.php')) ?>">İletişim</a>
  </p>
</footer>
<?php endif; ?>

</body>
</html>
