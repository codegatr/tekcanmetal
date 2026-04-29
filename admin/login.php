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
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Giriş — <?= h(settings('site_short_name', 'Tekcan Metal')) ?> Admin</title>
<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(url('admin/admin.css')) ?>?v=<?= h(TM_VERSION) ?>">
</head>
<body class="adm-login">
  <form method="post" class="adm-login-card adm-form">
    <?= csrf_field() ?>
    <div class="adm-login-brand">
      <div class="mark">T</div>
      <h1>Yönetim Paneli</h1>
      <p>Tekcan Metal — Sürüm <?= h(TM_VERSION) ?></p>
    </div>

    <?php if ($err): ?>
      <div class="adm-flash adm-flash-error" style="margin:0 0 16px"><?= h($err) ?></div>
    <?php endif; ?>

    <div class="row">
      <label>Kullanıcı Adı veya E-posta</label>
      <input type="text" name="login" required autofocus>
    </div>
    <div class="row">
      <label>Şifre</label>
      <input type="password" name="password" required>
    </div>

    <button type="submit" class="adm-btn adm-btn-primary adm-btn-block" style="padding:11px;font-size:14px">Giriş Yap</button>

    <p style="text-align:center;margin:18px 0 0;font-size:12px;color:var(--text-muted)">
      © <?= date('Y') ?> Tekcan Metal — <a href="https://codega.com.tr" target="_blank">Codega</a>
    </p>
  </form>
</body>
</html>
