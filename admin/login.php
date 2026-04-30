<?php
define('TM_ADMIN', true);
require_once __DIR__ . '/../includes/db.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('admin/index.php');
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $err = 'Oturum doğrulama hatası.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');
        $u = row("SELECT * FROM tm_users WHERE (username=? OR email=?) AND is_active=1", [$login, $login]);
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']    = (int)$u['id'];
            $_SESSION['admin_role']  = $u['role'];
            q("UPDATE tm_users SET last_login=NOW(), last_ip=? WHERE id=?", [get_ip(), $u['id']]);
            log_activity('login', 'user', $u['id'], 'Yönetici girişi');
            redirect('admin/index.php');
        } else {
            $err = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}

$logoPath = settings('logo', 'assets/img/logo.png');
$siteName = settings('site_short_name', 'Tekcan Metal');
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Giriş — <?= h($siteName) ?> Yönetim Paneli</title>
<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box}
  html,body{height:100%;margin:0}
  body{
    font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;
    background:#f5f7fa;
    color:#1a1a1a;
    -webkit-font-smoothing:antialiased;
    -moz-osx-font-smoothing:grayscale;
    line-height:1.5;
  }

  .login-shell{
    min-height:100vh;
    display:grid;
    grid-template-columns:1.1fr 1fr;
  }

  /* SOL: KURUMSAL MARKA PANELİ */
  .login-brand{
    background:linear-gradient(135deg, #050d24 0%, #0c1e44 50%, #143672 100%);
    color:#fff;
    padding:64px 56px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    position:relative;
    overflow:hidden;
  }
  .login-brand::before{
    content:'';
    position:absolute;
    top:-30%;right:-20%;
    width:80%;height:140%;
    background:radial-gradient(ellipse 50% 60% at 50% 50%, rgba(74,139,214,.18) 0%, transparent 65%);
    pointer-events:none;
  }
  .login-brand::after{
    content:'';
    position:absolute;
    left:0;bottom:0;
    width:100%;height:4px;
    background:#c8102e;
  }

  .brand-top{
    position:relative;
    z-index:2;
  }
  .brand-logo{
    display:block;
    margin-bottom:18px;
  }
  .brand-name-fallback{
    font-size:24px;
    font-weight:700;
    color:#fff;
    letter-spacing:-.5px;
  }
  .brand-logo img{
    height:60px;
    width:auto;
    max-width:280px;
    object-fit:contain;
    display:block;
  }
  .brand-tag{
    display:inline-block;
    padding:5px 12px;
    background:rgba(200,16,46,.15);
    border:1px solid rgba(200,16,46,.4);
    color:#fca5a5;
    font-size:10.5px;
    font-weight:700;
    letter-spacing:1.5px;
    text-transform:uppercase;
  }

  .brand-mid{
    position:relative;
    z-index:2;
    flex:1;
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:32px 0;
  }
  .brand-kicker{
    display:inline-block;
    font-size:11px;
    font-weight:700;
    letter-spacing:2.5px;
    text-transform:uppercase;
    color:#c8102e;
    margin-bottom:14px;
  }
  .brand-kicker::before{
    content:'';
    display:inline-block;
    width:32px;height:2px;
    background:#c8102e;
    vertical-align:middle;
    margin-right:12px;
  }
  .brand-mid h1{
    font-size:42px;
    font-weight:300;
    color:#fff;
    margin:0 0 18px;
    letter-spacing:-1px;
    line-height:1.1;
  }
  .brand-mid h1 strong{
    font-weight:600;
    color:#fff;
  }
  .brand-mid p{
    font-size:15.5px;
    color:rgba(255,255,255,.78);
    line-height:1.7;
    max-width:440px;
    margin:0;
  }

  .brand-stats{
    position:relative;
    z-index:2;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:0;
    padding-top:32px;
    border-top:1px solid rgba(255,255,255,.12);
  }
  .brand-stat{
    padding-right:18px;
    border-right:1px solid rgba(255,255,255,.12);
  }
  .brand-stat:last-child{border-right:0}
  .brand-stat strong{
    display:block;
    font-size:30px;
    font-weight:300;
    color:#fff;
    letter-spacing:-1px;
    line-height:1;
    margin-bottom:6px;
  }
  .brand-stat span{
    font-size:10.5px;
    font-weight:700;
    letter-spacing:1.5px;
    text-transform:uppercase;
    color:rgba(255,255,255,.6);
  }

  /* SAĞ: GİRİŞ FORMU */
  .login-form-wrap{
    background:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:64px 56px;
    position:relative;
  }
  .login-form{
    width:100%;
    max-width:380px;
  }
  .login-form-head{
    margin-bottom:36px;
  }
  .form-kicker{
    display:inline-block;
    font-size:11px;
    font-weight:700;
    letter-spacing:2px;
    text-transform:uppercase;
    color:#c8102e;
    margin-bottom:10px;
  }
  .login-form h2{
    font-size:28px;
    font-weight:600;
    color:#1e4a9e;
    margin:0 0 8px;
    letter-spacing:-.5px;
    line-height:1.2;
  }
  .login-form .subtitle{
    font-size:14px;
    color:#666;
    margin:0;
    line-height:1.6;
  }

  .login-error{
    background:#fff5f5;
    border:1px solid #fecaca;
    border-left:3px solid #c8102e;
    color:#991b1b;
    padding:12px 16px;
    font-size:13.5px;
    margin-bottom:18px;
    display:flex;
    align-items:center;
    gap:10px;
  }
  .login-error::before{
    content:'⚠';
    font-size:16px;
  }

  .form-row{margin-bottom:18px}
  .form-row label{
    display:block;
    font-size:11px;
    font-weight:700;
    letter-spacing:1.4px;
    text-transform:uppercase;
    color:#475569;
    margin-bottom:8px;
  }
  .form-row input{
    width:100%;
    padding:13px 16px;
    font-size:14.5px;
    font-family:inherit;
    color:#1a1a1a;
    background:#fff;
    border:1px solid #d0d7e0;
    border-radius:0;
    transition:.18s;
    outline:none;
  }
  .form-row input:focus{
    border-color:#1e4a9e;
    box-shadow:0 0 0 3px rgba(30,74,158,.12);
  }
  .form-row input::placeholder{color:#94a3b8}

  .form-row-checkbox{
    display:flex;
    align-items:center;
    gap:10px;
    margin:6px 0 24px;
    font-size:13px;
    color:#475569;
  }
  .form-row-checkbox input{width:16px;height:16px;cursor:pointer;accent-color:#1e4a9e}
  .form-row-checkbox label{margin:0;letter-spacing:0;text-transform:none;font-weight:500;cursor:pointer;color:#475569}

  .login-submit{
    width:100%;
    padding:14px;
    font-size:13px;
    font-weight:700;
    letter-spacing:1.5px;
    text-transform:uppercase;
    background:#1e4a9e;
    color:#fff;
    border:0;
    cursor:pointer;
    transition:.22s;
    font-family:inherit;
    position:relative;
    overflow:hidden;
  }
  .login-submit::after{
    content:'→';
    margin-left:10px;
    transition:transform .22s;
    display:inline-block;
  }
  .login-submit:hover{
    background:#143672;
    transform:translateY(-1px);
    box-shadow:0 12px 24px rgba(30,74,158,.25);
  }
  .login-submit:hover::after{transform:translateX(4px)}
  .login-submit:active{transform:translateY(0)}

  .login-form-foot{
    margin-top:36px;
    padding-top:24px;
    border-top:1px solid #e3e8ef;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
  }
  .login-form-foot small{
    font-size:11.5px;
    color:#94a3b8;
    letter-spacing:0;
  }
  .login-form-foot small strong{color:#475569;font-weight:600}
  .login-form-foot a{
    font-size:11px;
    font-weight:700;
    letter-spacing:1.4px;
    text-transform:uppercase;
    color:#c8102e;
    text-decoration:none;
    transition:.18s;
  }
  .login-form-foot a:hover{color:#1e4a9e}

  /* RESPONSIVE */
  @media (max-width: 960px){
    .login-shell{grid-template-columns:1fr}
    .login-brand{
      padding:40px 28px 50px;
      min-height:auto;
    }
    .login-brand::after{display:none}
    .brand-mid{padding:24px 0}
    .brand-mid h1{font-size:32px}
    .brand-mid p{font-size:14.5px}
    .brand-stats{padding-top:24px;margin-top:24px}
    .brand-stat strong{font-size:24px}
    .login-form-wrap{padding:40px 28px}
    .login-form{max-width:100%}
  }
  @media (max-width: 480px){
    .brand-stats{grid-template-columns:repeat(2,1fr);gap:18px 0;border-top:1px solid rgba(255,255,255,.12)}
    .brand-stat:nth-child(2n){border-right:0}
    .brand-stat:nth-child(3){grid-column:1/-1;border-right:0;text-align:left}
  }
</style>
</head>
<body>

<div class="login-shell">

  <!-- SOL: Kurumsal Marka Paneli -->
  <div class="login-brand">
    <div class="brand-top">
      <div class="brand-logo">
        <?php if ($logoPath && file_exists(__DIR__ . '/../' . $logoPath)): ?>
          <img src="<?= h(url($logoPath)) ?>" alt="<?= h($siteName) ?>">
        <?php else: ?>
          <div class="brand-name-fallback"><?= h($siteName) ?></div>
        <?php endif; ?>
      </div>
      <span class="brand-tag">Yönetim Paneli</span>
    </div>

    <div class="brand-mid">
      <span class="brand-kicker">Demir-çelik tedariğinde uçtan uca çözüm</span>
      <h1>2005'ten bu yana<br><strong>Konya'da güven</strong></h1>
      <p>Sac, boru, profil, hadde ve özel çelik ürünlerinde Türkiye'nin lider üreticilerinin temsilciliği ile sanayi ve inşaat sektörüne çözüm üretiyoruz.</p>
    </div>

    <div class="brand-stats">
      <div class="brand-stat">
        <strong>20+</strong>
        <span>Yıl Tecrübe</span>
      </div>
      <div class="brand-stat">
        <strong>9</strong>
        <span>Ana Ürün Grubu</span>
      </div>
      <div class="brand-stat">
        <strong>81</strong>
        <span>İl Sevkiyat</span>
      </div>
    </div>
  </div>

  <!-- SAĞ: Giriş Formu -->
  <div class="login-form-wrap">
    <form method="post" class="login-form" autocomplete="off">
      <?= csrf_field() ?>

      <div class="login-form-head">
        <span class="form-kicker">Yönetici Girişi</span>
        <h2>Hoş geldiniz</h2>
        <p class="subtitle">Devam etmek için yönetici hesabınıza giriş yapın.</p>
      </div>

      <?php if ($err): ?>
        <div class="login-error"><?= h($err) ?></div>
      <?php endif; ?>

      <div class="form-row">
        <label for="login">Kullanıcı Adı veya E-posta</label>
        <input type="text" id="login" name="login" required autofocus
               value="<?= h($_POST['login'] ?? '') ?>"
               placeholder="ornek@tekcanmetal.com">
      </div>

      <div class="form-row">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" required
               placeholder="••••••••">
      </div>

      <button type="submit" class="login-submit">
        Giriş Yap
      </button>

      <div class="login-form-foot">
        <small><strong>© <?= date('Y') ?> Tekcan Metal</strong> — Tüm hakları saklıdır.</small>
        <a href="https://codega.com.tr" target="_blank" rel="noopener">Codega</a>
      </div>
    </form>
  </div>

</div>

</body>
</html>
