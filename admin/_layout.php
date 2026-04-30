<?php
/**
 * Admin Layout — yeni admin.css yapısına uygun
 * .adm-wrap > .adm-sidebar + .adm-main > .adm-top + .adm-content
 */
if (!defined('TM_ADMIN')) define('TM_ADMIN', true);
require_once __DIR__ . '/../includes/db.php';

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

$roleLabels = ['superadmin' => 'Süper Yönetici', 'admin' => 'Yönetici', 'editor' => 'Editör'];
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

<div class="adm-wrap">

  <!-- ========== SIDEBAR ========== -->
  <aside class="adm-sidebar" id="admSidebar">

    <div class="adm-sidebar-head">
      <a href="<?= h(admin_url('index.php')) ?>" class="logo-block">
        <span class="logo-mark">T</span>
        <span class="logo-text">
          <span class="logo-name">Tekcan Metal</span>
          <span class="logo-sub">Yönetim</span>
        </span>
      </a>
    </div>

    <nav class="adm-nav">
      <div class="adm-nav-section">
        <div class="adm-nav-title">Genel</div>
        <a href="<?= h(admin_url('index.php')) ?>"<?= nav_active('index.php', $current) ?>>📊 Pano</a>
        <a href="<?= h(admin_url('messages.php')) ?>"<?= nav_active('messages.php', $current) ?>>📨 Mesajlar</a>
      </div>

      <div class="adm-nav-section">
        <div class="adm-nav-title">İçerik</div>
        <a href="<?= h(admin_url('sliders.php')) ?>"<?= nav_active('sliders.php', $current) ?>>🎬 Slider</a>
        <a href="<?= h(admin_url('pages.php')) ?>"<?= nav_active('pages.php', $current) ?>>📄 Sayfalar</a>
        <a href="<?= h(admin_url('blog.php')) ?>"<?= nav_active('blog.php', $current) ?>>✍ Blog Yazıları</a>
        <a href="<?= h(admin_url('blog-categories.php')) ?>"<?= nav_active('blog-categories.php', $current) ?>>📁 Blog Kategorileri</a>
        <a href="<?= h(admin_url('gallery.php')) ?>"<?= nav_active('gallery.php', $current) ?>>🖼 Galeri</a>
        <a href="<?= h(admin_url('faq.php')) ?>"<?= nav_active('faq.php', $current) ?>>❓ SSS</a>
      </div>

      <div class="adm-nav-section">
        <div class="adm-nav-title">Katalog</div>
        <a href="<?= h(admin_url('categories.php')) ?>"<?= nav_active('categories.php', $current) ?>>🗂 Kategoriler</a>
        <a href="<?= h(admin_url('products.php')) ?>"<?= nav_active('products.php', $current) ?>>📦 Ürünler</a>
        <a href="<?= h(admin_url('services.php')) ?>"<?= nav_active('services.php', $current) ?>>🛠 Hizmetler</a>
      </div>

      <div class="adm-nav-section">
        <div class="adm-nav-title">Kurumsal</div>
        <a href="<?= h(admin_url('team.php')) ?>"<?= nav_active('team.php', $current) ?>>👥 Ekip</a>
        <a href="<?= h(admin_url('partners.php')) ?>"<?= nav_active('partners.php', $current) ?>>🤝 Çözüm Ortakları</a>
        <a href="<?= h(admin_url('banks.php')) ?>"<?= nav_active('banks.php', $current) ?>>🏦 Bankalar / IBAN</a>
      </div>

      <div class="adm-nav-section">
        <div class="adm-nav-title">Sistem</div>
        <a href="<?= h(admin_url('settings.php')) ?>"<?= nav_active('settings.php', $current) ?>>⚙ Ayarlar</a>
        <a href="<?= h(admin_url('users.php')) ?>"<?= nav_active('users.php', $current) ?>>🔐 Kullanıcılar</a>
        <a href="<?= h(admin_url('guncelleme.php')) ?>"<?= nav_active('guncelleme.php', $current) ?>>🔄 Güncelleme</a>
        <?php if (($adminUser['role'] ?? '') === 'superadmin'): ?>
        <a href="<?= h(admin_url('site-saglik.php')) ?>"<?= nav_active('site-saglik.php', $current) ?> style="color:#c8102e;font-weight:600">🔧 Site Sağlığı</a>
        <a href="<?= h(admin_url('teshis.php')) ?>"<?= nav_active('teshis.php', $current) ?>>🔍 Sistem Teşhisi</a>
        <?php endif; ?>
        <?php if (file_exists(__DIR__ . '/../install/wp-content.json.gz') && ($adminUser['role'] ?? '') === 'superadmin'): ?>
        <a href="<?= h(admin_url('wp-import.php')) ?>"<?= nav_active('wp-import.php', $current) ?>>📥 WP Aktarımı</a>
        <?php endif; ?>
        <a href="<?= h(admin_url('activity.php')) ?>"<?= nav_active('activity.php', $current) ?>>📜 Aktivite Logları</a>
      </div>
    </nav>

    <div class="adm-sidebar-foot">
      <?php if ($adminUser): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <span class="adm-user-avatar"><?= h(mb_strtoupper(mb_substr($adminUser['full_name'] ?: $adminUser['username'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
          <div class="adm-user-info">
            <div class="adm-user-name"><?= h($adminUser['full_name'] ?: $adminUser['username']) ?></div>
            <div class="adm-user-role"><?= h($roleLabels[$adminUser['role']] ?? $adminUser['role']) ?></div>
          </div>
        </div>
      <?php endif; ?>
      <div class="adm-version">
        Sürüm <?= h(TM_VERSION) ?> ·
        <a href="<?= h(url('')) ?>" target="_blank">↗ Siteyi Görüntüle</a>
      </div>
    </div>
  </aside>

  <!-- ========== MAIN ========== -->
  <div class="adm-main">

    <header class="adm-top">
      <div class="adm-top-title">
        <h1><?= h($adminTitle) ?></h1>
      </div>
      <div class="adm-top-actions">
        <button type="button" class="adm-mobile-toggle" id="admMenuBtn" aria-label="Menü">☰</button>
        <?php if ($adminUser): ?>
          <a href="<?= h(admin_url('logout.php')) ?>" class="adm-btn adm-btn-ghost adm-btn-sm">Çıkış</a>
        <?php endif; ?>
      </div>
    </header>

    <?php foreach (flash_get() as $f): ?>
      <div class="adm-flash adm-flash-<?= h($f['type']) ?>"><?= h($f['msg']) ?></div>
    <?php endforeach; ?>

    <main class="adm-content">
