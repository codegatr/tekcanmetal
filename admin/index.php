<?php
define('TM_ADMIN', true);
$adminTitle = 'Pano';
require __DIR__ . '/_layout.php';

$stats = [
    'products'  => (int)val("SELECT COUNT(*) FROM tm_products WHERE is_active=1"),
    'cats'      => (int)val("SELECT COUNT(*) FROM tm_categories WHERE is_active=1"),
    'msg_unr'   => (int)val("SELECT COUNT(*) FROM tm_contact_messages WHERE is_read=0"),
    'mail_ord'  => (int)val("SELECT COUNT(*) FROM tm_mail_orders WHERE status='pending'"),
    'blogs'     => (int)val("SELECT COUNT(*) FROM tm_blog_posts WHERE is_active=1"),
    'team'      => (int)val("SELECT COUNT(*) FROM tm_team WHERE is_active=1"),
    'partners'  => (int)val("SELECT COUNT(*) FROM tm_partners WHERE is_active=1"),
    'gallery'   => (int)val("SELECT COUNT(*) FROM tm_gallery_images"),
];

// Site sağlığı kontrolü — anasayfada uyarı gösterelim
$healthIssues = 0;
$healthMessages = [];
try {
    $catNullCount = (int)val("SELECT COUNT(*) FROM tm_categories WHERE image IS NULL OR image=''");
    if ($catNullCount > 0) { $healthIssues++; $healthMessages[] = "$catNullCount kategori görselsiz"; }

    $prodNullCount = (int)val("SELECT COUNT(*) FROM tm_products WHERE image IS NULL OR image=''");
    if ($prodNullCount > 0) { $healthIssues++; $healthMessages[] = "$prodNullCount ürün görselsiz"; }

    if ($stats['team'] === 0) { $healthIssues++; $healthMessages[] = "Ekip listesi boş"; }

    $uploadsRoot = realpath(__DIR__ . '/..') . '/uploads';
    if (!is_dir($uploadsRoot . '/categories')) { $healthIssues++; $healthMessages[] = "uploads/categories klasörü yok"; }
    if (!is_dir($uploadsRoot . '/products')) { $healthIssues++; $healthMessages[] = "uploads/products klasörü yok"; }
} catch (\Throwable $e) {}

$lastMsg     = all("SELECT id, full_name, email, subject, created_at, is_read FROM tm_contact_messages ORDER BY created_at DESC LIMIT 8");
$lastOrders  = all("SELECT id, full_name, amount, created_at, status FROM tm_mail_orders ORDER BY created_at DESC LIMIT 5");
$lastLogs    = all("SELECT a.*, u.username FROM tm_activity_logs a LEFT JOIN tm_users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 8");
?>

<?php if ($healthIssues > 0 && ($adminUser['role'] ?? '') === 'superadmin'): ?>
<div style="background:linear-gradient(135deg, #c8102e 0%, #a00d24 100%);color:#fff;padding:18px 24px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;border-left:4px solid #fff;position:relative;overflow:hidden">
  <div style="position:relative;z-index:2">
    <h3 style="margin:0 0 4px;font-size:16px;font-weight:600;color:#fff">🔧 <?= $healthIssues ?> sorun tespit edildi</h3>
    <p style="margin:0;font-size:13.5px;color:rgba(255,255,255,.9);line-height:1.5">
      <?= htmlspecialchars(implode(' · ', $healthMessages)) ?>
    </p>
  </div>
  <a href="<?= h(url('admin/site-saglik.php')) ?>" style="background:#fff;color:#c8102e;padding:11px 22px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;text-decoration:none;flex-shrink:0;position:relative;z-index:2">
    🔧 Tek Tıkla Tamir Et →
  </a>
</div>
<?php endif; ?>

<div class="adm-stat-grid">
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['products'] ?></div><div class="adm-stat-label">Aktif Ürün</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['cats'] ?></div><div class="adm-stat-label">Kategori</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['msg_unr'] ?></div><div class="adm-stat-label">Okunmamış Mesaj</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['mail_ord'] ?></div><div class="adm-stat-label">Bekleyen Mail Order</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['blogs'] ?></div><div class="adm-stat-label">Blog Yazısı</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['team'] ?></div><div class="adm-stat-label">Ekip Üyesi</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['partners'] ?></div><div class="adm-stat-label">Çözüm Ortağı</div></div>
  <div class="adm-stat"><div class="adm-stat-value"><?= $stats['gallery'] ?></div><div class="adm-stat-label">Galeri Görseli</div></div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px">
  <div>
    <div class="adm-panel">
      <div class="adm-panel-head"><h2>Son İletişim Mesajları</h2><a href="<?= h(admin_url('messages.php')) ?>" class="adm-btn adm-btn-sm adm-btn-ghost">Tümünü Gör</a></div>
      <div class="adm-panel-body" style="padding:0">
        <?php if (!$lastMsg): ?>
          <div class="adm-empty"><div class="ico">📭</div>Henüz mesaj yok.</div>
        <?php else: ?>
        <table class="adm-table">
          <thead><tr><th>Ad</th><th>Konu</th><th>Tarih</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($lastMsg as $m): ?>
          <tr style="<?= !$m['is_read'] ? 'font-weight:600' : '' ?>">
            <td><?= h($m['full_name']) ?><br><small style="color:var(--text-muted)"><?= h($m['email']) ?></small></td>
            <td><?= h($m['subject'] ?: '—') ?></td>
            <td><?= h(tr_date($m['created_at'], true)) ?></td>
            <td class="actions"><a href="<?= h(admin_url('messages.php?id=' . (int)$m['id'])) ?>" class="adm-btn adm-btn-sm adm-btn-ghost">Görüntüle</a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <div class="adm-panel">
      <div class="adm-panel-head"><h2>Son Mail Order Talimatları</h2><a href="<?= h(admin_url('messages.php?tip=mail-order')) ?>" class="adm-btn adm-btn-sm adm-btn-ghost">Tümü</a></div>
      <div class="adm-panel-body" style="padding:0">
        <?php if (!$lastOrders): ?>
          <div class="adm-empty"><div class="ico">💳</div>Talimat yok.</div>
        <?php else: ?>
        <table class="adm-table">
          <thead><tr><th>Müşteri</th><th>Tutar</th><th>Tarih</th><th>Durum</th></tr></thead>
          <tbody>
          <?php foreach ($lastOrders as $o): ?>
          <tr>
            <td><?= h($o['full_name']) ?></td>
            <td><?= number_format((float)$o['amount'], 2, ',', '.') ?> TL</td>
            <td><?= h(tr_date($o['created_at'], true)) ?></td>
            <td><span class="badge badge-<?= $o['status']==='pending' ? 'off' : 'on' ?>"><?= h($o['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div>
    <div class="adm-panel">
      <div class="adm-panel-head"><h2>Sistem Bilgisi</h2></div>
      <div class="adm-panel-body">
        <p><strong>Sürüm:</strong> <?= h(TM_VERSION) ?></p>
        <p><strong>PHP:</strong> <?= h(PHP_VERSION) ?></p>
        <p><strong>Site:</strong> <a href="<?= h(SITE_URL) ?>" target="_blank"><?= h(SITE_URL) ?></a></p>
        <p><strong>Repo:</strong> <code><?= h(defined('GITHUB_REPO') ? GITHUB_REPO : 'codegatr/tekcanmetal') ?></code></p>
        <a href="<?= h(admin_url('guncelleme.php')) ?>" class="adm-btn adm-btn-primary adm-btn-block">🔄 Güncelleme Kontrol Et</a>
      </div>
    </div>

    <div class="adm-panel">
      <div class="adm-panel-head"><h2>Son Aktiviteler</h2></div>
      <div class="adm-panel-body">
        <?php if (!$lastLogs): ?>
          <div class="adm-empty"><div class="ico">📜</div>Log yok.</div>
        <?php else: ?>
        <ul class="adm-log" style="list-style:none;padding:0;margin:0">
          <?php foreach ($lastLogs as $l): ?>
          <li style="padding:6px 0;border-bottom:1px solid var(--line)">
            <time><?= h(tr_date($l['created_at'], true)) ?></time><br>
            <strong style="color:var(--text)"><?= h($l['username'] ?: 'sistem') ?></strong> — <?= h($l['action']) ?>
            <?php if ($l['description']): ?><div><?= h($l['description']) ?></div><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
