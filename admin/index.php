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
$lastMsg     = all("SELECT id, full_name, email, subject, created_at, is_read FROM tm_contact_messages ORDER BY created_at DESC LIMIT 8");
$lastOrders  = all("SELECT id, full_name, amount, created_at, status FROM tm_mail_orders ORDER BY created_at DESC LIMIT 5");
$lastLogs    = all("SELECT a.*, u.username FROM tm_activity_logs a LEFT JOIN tm_users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 8");
?>

<div class="adm-stats">
  <div class="adm-stat"><div class="num"><?= $stats['products'] ?></div><div class="lbl">Aktif Ürün</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['cats'] ?></div><div class="lbl">Kategori</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['msg_unr'] ?></div><div class="lbl">Okunmamış Mesaj</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['mail_ord'] ?></div><div class="lbl">Bekleyen Mail Order</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['blogs'] ?></div><div class="lbl">Blog Yazısı</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['team'] ?></div><div class="lbl">Ekip Üyesi</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['partners'] ?></div><div class="lbl">Çözüm Ortağı</div></div>
  <div class="adm-stat"><div class="num"><?= $stats['gallery'] ?></div><div class="lbl">Galeri Görseli</div></div>
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
