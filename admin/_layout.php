<?php
/**
 * Admin Layout
 * Header / sidebar / topbar
 * Sayfanın sonunda admin_footer.php require edilmeli.
 */
if (!defined('TM_ADMIN')) define('TM_ADMIN', true);
require_once __DIR__ . '/../includes/db.php';

// auth zorunlu — login.php hariç
$selfFile = basename($_SERVER['PHP_SELF']);
if ($selfFile !== 'login.php' && empty($_SESSION['admin_id'])) {
    redirect('admin/login.php');
}

$adminUser = null;
if (!empty($_SESSION['admin_id'])) {
    $adminUser = row("SELECT id, username, full_name, email, role FROM tm_users WHERE id=? AND is_active=1", [$_SESSION['admin_id']]);
    if (!$adminUser) {
        session_destroy();
        redirect('admin/login.php');
    }
}

$adminTitle = $adminTitle ?? 'Yönetim Paneli';
$current = $selfFile;

function admin_url(string $p = ''): string { return url('admin/' . ltrim($p, '/')); }
function nav_active(string $file, string $current): string { return $file === $current ? ' class="active"' : ''; }
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($adminTitle) ?> — <?= h(settings('site_short_name', 'Tekcan Metal')) ?> Admin</title>
<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(url('admin/admin.css')) ?>?v=<?= h(TM_VERSION) ?>">
</head>
<body>

<aside class="adm-sidebar" id="admSidebar">
  <div class="adm-brand">
    <a href="<?= h(admin_url('index.php')) ?>">
      <span class="adm-mark">T</span>
      <span class="adm-brand-text">
        <strong>Tekcan Metal</strong>
        <small>Yönetim Paneli</small>
      </span>
    </a>
  </div>

  <nav class="adm-nav">
    <div class="adm-nav-title">Genel</div>
    <a href="<?= h(admin_url('index.php')) ?>"<?= nav_active('index.php', $current) ?>>📊 Pano</a>
    <a href="<?= h(admin_url('messages.php')) ?>"<?= nav_active('messages.php', $current) ?>>📨 Mesajlar</a>

    <div class="adm-nav-title">İçerik</div>
    <a href="<?= h(admin_url('sliders.php')) ?>"<?= nav_active('sliders.php', $current) ?>>🎬 Slider</a>
    <a href="<?= h(admin_url('pages.php')) ?>"<?= nav_active('pages.php', $current) ?>>📄 Sayfalar</a>
    <a href="<?= h(admin_url('blog.php')) ?>"<?= nav_active('blog.php', $current) ?>>✍ Blog Yazıları</a>
    <a href="<?= h(admin_url('blog-categories.php')) ?>"<?= nav_active('blog-categories.php', $current) ?>>📁 Blog Kategorileri</a>
    <a href="<?= h(admin_url('gallery.php')) ?>"<?= nav_active('gallery.php', $current) ?>>🖼 Galeri</a>
    <a href="<?= h(admin_url('faq.php')) ?>"<?= nav_active('faq.php', $current) ?>>❓ SSS</a>

    <div class="adm-nav-title">Katalog</div>
    <a href="<?= h(admin_url('categories.php')) ?>"<?= nav_active('categories.php', $current) ?>>🗂 Kategoriler</a>
    <a href="<?= h(admin_url('products.php')) ?>"<?= nav_active('products.php', $current) ?>>📦 Ürünler</a>
    <a href="<?= h(admin_url('services.php')) ?>"<?= nav_active('services.php', $current) ?>>🛠 Hizmetler</a>

    <div class="adm-nav-title">Kurumsal</div>
    <a href="<?= h(admin_url('team.php')) ?>"<?= nav_active('team.php', $current) ?>>👥 Ekip</a>
    <a href="<?= h(admin_url('partners.php')) ?>"<?= nav_active('partners.php', $current) ?>>🤝 Çözüm Ortakları</a>
    <a href="<?= h(admin_url('banks.php')) ?>"<?= nav_active('banks.php', $current) ?>>🏦 Bankalar / IBAN</a>

    <div class="adm-nav-title">Sistem</div>
    <a href="<?= h(admin_url('settings.php')) ?>"<?= nav_active('settings.php', $current) ?>>⚙ Ayarlar</a>
    <a href="<?= h(admin_url('users.php')) ?>"<?= nav_active('users.php', $current) ?>>🔐 Kullanıcılar</a>
    <a href="<?= h(admin_url('guncelleme.php')) ?>"<?= nav_active('guncelleme.php', $current) ?>>🔄 Güncelleme</a>
    <a href="<?= h(admin_url('activity.php')) ?>"<?= nav_active('activity.php', $current) ?>>📜 Aktivite Logları</a>
  </nav>

  <div class="adm-sidebar-foot">
    <div class="adm-version">Sürüm <?= h(TM_VERSION) ?></div>
    <a href="<?= h(url('')) ?>" target="_blank" class="adm-mini">↗ Siteyi Görüntüle</a>
  </div>
</aside>

<div class="adm-main">
  <header class="adm-topbar">
    <button type="button" class="adm-menu-btn" id="admMenuBtn" aria-label="Menü">☰</button>
    <h1 class="adm-page-title"><?= h($adminTitle) ?></h1>
    <div class="adm-top-right">
      <?php if ($adminUser): ?>
        <span class="adm-user">
          <span class="adm-avatar"><?= h(mb_strtoupper(mb_substr($adminUser['full_name'] ?: $adminUser['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
          <span><?= h($adminUser['full_name'] ?: $adminUser['username']) ?></span>
        </span>
        <a href="<?= h(admin_url('logout.php')) ?>" class="adm-btn adm-btn-ghost">Çıkış</a>
      <?php endif; ?>
    </div>
  </header>

  <?php foreach (flash_get() as $f): ?>
    <div class="adm-flash adm-flash-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
  <?php endforeach; ?>

  <main class="adm-content">
