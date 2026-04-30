<?php
define('TM_ADMIN', true);
$adminTitle = 'Pano';
require __DIR__ . '/_layout.php';

$stats = [
    'products'  => (int)val("SELECT COUNT(*) FROM tm_products WHERE is_active=1"),
    'cats'      => (int)val("SELECT COUNT(*) FROM tm_categories WHERE is_active=1"),
    'msg_unr'   => (int)val("SELECT COUNT(*) FROM tm_contact_messages WHERE is_read=0"),
    'msg_total' => (int)val("SELECT COUNT(*) FROM tm_contact_messages"),
    'mail_ord'  => (int)val("SELECT COUNT(*) FROM tm_mail_orders WHERE status='pending'"),
    'blogs'     => (int)val("SELECT COUNT(*) FROM tm_blog_posts WHERE is_active=1"),
    'partners'  => (int)val("SELECT COUNT(*) FROM tm_partners WHERE is_active=1"),
    'gallery'   => (int)val("SELECT COUNT(*) FROM tm_gallery_images"),
    'sliders'   => (int)val("SELECT COUNT(*) FROM tm_sliders WHERE is_active=1"),
    'services'  => (int)val("SELECT COUNT(*) FROM tm_services WHERE is_active=1"),
];

// Site sağlığı kontrolü
$healthIssues = 0;
$healthMessages = [];
try {
    $catNullCount = (int)val("SELECT COUNT(*) FROM tm_categories WHERE image IS NULL OR image=''");
    if ($catNullCount > 0) { $healthIssues++; $healthMessages[] = "$catNullCount kategori görselsiz"; }

    $prodNullCount = (int)val("SELECT COUNT(*) FROM tm_products WHERE image IS NULL OR image=''");
    if ($prodNullCount > 0) { $healthIssues++; $healthMessages[] = "$prodNullCount ürün görselsiz"; }

    $uploadsRoot = realpath(__DIR__ . '/..') . '/uploads';
    if (!is_dir($uploadsRoot . '/categories')) { $healthIssues++; $healthMessages[] = "uploads/categories klasörü yok"; }
    if (!is_dir($uploadsRoot . '/products')) { $healthIssues++; $healthMessages[] = "uploads/products klasörü yok"; }
} catch (\Throwable $e) {}

$lastMsg     = all("SELECT id, full_name, email, subject, created_at, is_read FROM tm_contact_messages ORDER BY created_at DESC LIMIT 6");
$lastOrders  = all("SELECT id, full_name, amount, created_at, status FROM tm_mail_orders ORDER BY created_at DESC LIMIT 4");
$lastLogs    = all("SELECT a.*, u.username FROM tm_activity_logs a LEFT JOIN tm_users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 6");

$installedVersion = TM_VERSION;
?>

<style>
/* ═══════════════════════════════════════════════
   PANO — KURUMSAL B2B TASARIM (v1.0.28)
   ═══════════════════════════════════════════════ */
.pano-shell{display:grid;gap:18px}

/* ── Sağlık Banner ── */
.pano-health{
  background:linear-gradient(135deg, #c8102e 0%, #a00d24 100%);
  color:#fff;padding:20px 28px;
  display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;
  border-left:4px solid #fff;position:relative;overflow:hidden;
}
.pano-health::before{
  content:'';position:absolute;right:-40px;top:-40px;
  width:200px;height:200px;border:1px solid rgba(255,255,255,.1);border-radius:50%;
}
.pano-health h3{margin:0 0 4px;font-size:16px;font-weight:600;color:#fff;letter-spacing:-.2px}
.pano-health p{margin:0;font-size:13.5px;color:rgba(255,255,255,.92);line-height:1.55}
.pano-health-btn{
  background:#fff;color:#c8102e;padding:12px 22px;
  font-size:11.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  text-decoration:none;flex-shrink:0;position:relative;z-index:2;
  transition:.18s;
}
.pano-health-btn:hover{background:#0a1a3a;color:#fff;transform:translateY(-1px);box-shadow:0 8px 20px rgba(10,26,58,.3)}

/* ── Welcome ── */
.pano-welcome{
  background:linear-gradient(135deg, #050d24 0%, #0c1e44 50%, #143672 100%);
  color:#fff;padding:32px 36px;position:relative;overflow:hidden;
  border-bottom:3px solid #c8102e;
}
.pano-welcome::after{
  content:'TEKCAN METAL';position:absolute;right:-30px;bottom:-40px;
  font-size:140px;font-weight:900;letter-spacing:-6px;
  color:rgba(255,255,255,.04);line-height:1;pointer-events:none;
  font-family:Georgia,serif;
}
.pano-welcome h1{margin:0 0 8px;font-size:28px;font-weight:300;letter-spacing:-.5px;color:#fff;position:relative;z-index:2}
.pano-welcome h1 strong{font-weight:600}
.pano-welcome p{margin:0;font-size:14px;color:rgba(255,255,255,.7);position:relative;z-index:2}
.pano-welcome-meta{
  display:flex;gap:32px;margin-top:22px;flex-wrap:wrap;position:relative;z-index:2;
}
.pano-welcome-meta div{font-size:12px}
.pano-welcome-meta .lbl{
  display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:4px;
}
.pano-welcome-meta .val{color:#fff;font-weight:500;font-size:13px}

/* ── İstatistik Kartları ── */
.pano-stats{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:16px;
}
.pano-stat{
  background:#fff;border:1px solid #e3e8ef;
  padding:22px 24px;position:relative;
  transition:.18s;
  border-left:3px solid transparent;
}
.pano-stat:hover{border-color:#c8102e;border-left-color:#c8102e;box-shadow:0 6px 16px rgba(10,26,58,.06);transform:translateY(-1px)}
.pano-stat-label{
  font-size:10.5px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:#5e6470;margin-bottom:14px;
}
.pano-stat-value{
  font-size:36px;font-weight:300;color:#0a1a3a;
  line-height:1;letter-spacing:-1.5px;
  font-family:Georgia, serif;
}
.pano-stat-sub{
  font-size:11.5px;color:#5e6470;margin-top:8px;
  display:flex;align-items:center;gap:6px;
}
.pano-stat-sub a{color:#1e4a9e;text-decoration:none;font-weight:500}
.pano-stat-sub a:hover{color:#c8102e}
.pano-stat.highlight{
  background:linear-gradient(135deg, #fff7e6 0%, #fffaf0 100%);
  border-color:#fde68a;
}
.pano-stat.highlight .pano-stat-value{color:#c8102e}
.pano-stat.highlight::before{
  content:'!';position:absolute;top:14px;right:18px;
  width:22px;height:22px;background:#c8102e;color:#fff;
  font-size:13px;font-weight:700;
  display:flex;align-items:center;justify-content:center;border-radius:50%;
}

/* ── İki kolonlu içerik ── */
.pano-grid{
  display:grid;grid-template-columns:2fr 1fr;gap:18px;
}
@media (max-width:1100px){.pano-grid{grid-template-columns:1fr}}

.pano-panel{
  background:#fff;border:1px solid #e3e8ef;display:flex;flex-direction:column;
}
.pano-panel-head{
  padding:16px 22px;border-bottom:1px solid #e3e8ef;
  display:flex;justify-content:space-between;align-items:center;
}
.pano-panel-head h2{
  margin:0;font-size:13px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:#0a1a3a;
}
.pano-panel-head a{
  font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
  color:#1e4a9e;text-decoration:none;
}
.pano-panel-head a:hover{color:#c8102e}
.pano-panel-body{padding:8px 0}

/* ── Mesaj listesi ── */
.pano-msg{
  display:flex;align-items:center;gap:14px;
  padding:12px 22px;border-bottom:1px solid #f0f3f7;
  transition:.15s;text-decoration:none;color:inherit;
}
.pano-msg:last-child{border-bottom:0}
.pano-msg:hover{background:#fafbfd}
.pano-msg-avatar{
  width:36px;height:36px;
  background:#143672;color:#fff;
  display:flex;align-items:center;justify-content:center;
  font-weight:600;font-size:13px;flex-shrink:0;
  border-radius:50%;
}
.pano-msg.unread .pano-msg-avatar{background:#c8102e}
.pano-msg-body{flex:1;min-width:0}
.pano-msg-name{font-size:13px;font-weight:600;color:#0a1a3a;margin-bottom:2px}
.pano-msg-subject{font-size:12px;color:#5e6470;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pano-msg-time{font-size:11px;color:#999;flex-shrink:0}
.pano-msg.unread .pano-msg-name::after{
  content:'YENİ';margin-left:8px;
  font-size:9px;background:#c8102e;color:#fff;
  padding:2px 6px;letter-spacing:1px;font-weight:700;
}

/* ── Aktivite log ── */
.pano-act{padding:12px 22px;border-bottom:1px solid #f0f3f7;font-size:12.5px}
.pano-act:last-child{border-bottom:0}
.pano-act-head{display:flex;justify-content:space-between;font-size:11px;color:#999;margin-bottom:4px}
.pano-act-head strong{color:#1e4a9e;font-weight:600;font-family:ui-monospace,monospace}
.pano-act-body{color:#0a1a3a;line-height:1.5}
.pano-act-body em{color:#5e6470;font-style:normal;font-size:11.5px}

/* ── Sistem Bilgisi ── */
.pano-info{padding:8px 22px 16px}
.pano-info-row{
  display:flex;justify-content:space-between;align-items:center;
  padding:10px 0;border-bottom:1px solid #f0f3f7;
}
.pano-info-row:last-child{border-bottom:0}
.pano-info-key{font-size:11.5px;color:#5e6470;font-weight:600;letter-spacing:.3px}
.pano-info-val{font-size:12.5px;color:#0a1a3a;font-family:ui-monospace,monospace}
.pano-info-val a{color:#1e4a9e;text-decoration:none}
.pano-info-val .pill{background:#143672;color:#fff;padding:3px 8px;font-size:10.5px;font-weight:700;letter-spacing:.5px}

.pano-empty{padding:50px 20px;text-align:center;color:#999;font-size:12.5px}
.pano-empty-icon{font-size:34px;margin-bottom:10px;opacity:.4}

/* ── Quick Actions ── */
.pano-actions{
  background:#fafbfd;padding:18px 22px;border:1px solid #e3e8ef;
  display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:10px;
}
.pano-action{
  background:#fff;border:1px solid #e3e8ef;padding:14px 16px;
  text-decoration:none;color:#0a1a3a;font-size:12.5px;font-weight:500;
  display:flex;align-items:center;gap:10px;transition:.15s;
}
.pano-action:hover{border-color:#1e4a9e;color:#1e4a9e;background:#fff}
.pano-action-icon{
  width:30px;height:30px;background:#143672;color:#fff;
  display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;
}
.pano-action:hover .pano-action-icon{background:#c8102e}
</style>

<div class="pano-shell">

  <?php if ($healthIssues > 0 && ($adminUser['role'] ?? '') === 'superadmin'): ?>
  <div class="pano-health">
    <div style="position:relative;z-index:2">
      <h3>🔧 <?= $healthIssues ?> sorun tespit edildi</h3>
      <p><?= htmlspecialchars(implode(' · ', $healthMessages)) ?></p>
    </div>
    <a href="<?= h(url('admin/site-saglik.php')) ?>" class="pano-health-btn">🔧 Tek Tıkla Tamir Et →</a>
  </div>
  <?php endif; ?>

  <div class="pano-welcome">
    <h1>Merhaba, <strong><?= h(explode(' ', $adminUser['full_name'] ?: $adminUser['username'])[0]) ?></strong></h1>
    <p>Tekcan Metal Yönetim Paneli'ne hoş geldiniz. Aşağıda sitenin güncel durumu özetlenmiştir.</p>
    <div class="pano-welcome-meta">
      <div>
        <span class="lbl">Sürüm</span>
        <span class="val"><?= h($installedVersion) ?></span>
      </div>
      <div>
        <span class="lbl">PHP</span>
        <span class="val"><?= h(PHP_VERSION) ?></span>
      </div>
      <div>
        <span class="lbl">Tarih</span>
        <span class="val"><?= date('d.m.Y · H:i') ?></span>
      </div>
      <div>
        <span class="lbl">Yetki</span>
        <span class="val"><?= h($roleLabels[$adminUser['role']] ?? $adminUser['role']) ?></span>
      </div>
    </div>
  </div>

  <div class="pano-stats">

    <div class="pano-stat <?= $stats['msg_unr'] > 0 ? 'highlight' : '' ?>">
      <div class="pano-stat-label">Okunmamış Mesaj</div>
      <div class="pano-stat-value"><?= $stats['msg_unr'] ?></div>
      <div class="pano-stat-sub">
        <a href="<?= h(url('admin/messages.php')) ?>">Tümünü Gör →</a>
        <span style="margin-left:auto">/<?= $stats['msg_total'] ?> toplam</span>
      </div>
    </div>

    <div class="pano-stat <?= $stats['mail_ord'] > 0 ? 'highlight' : '' ?>">
      <div class="pano-stat-label">Bekleyen Sipariş</div>
      <div class="pano-stat-value"><?= $stats['mail_ord'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/mail-orders.php')) ?>">Mail Order →</a></div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Aktif Ürün</div>
      <div class="pano-stat-value"><?= $stats['products'] ?></div>
      <div class="pano-stat-sub">
        <a href="<?= h(url('admin/products.php')) ?>">Yönet →</a>
        <span style="margin-left:auto"><?= $stats['cats'] ?> kategori</span>
      </div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Hizmetler</div>
      <div class="pano-stat-value"><?= $stats['services'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/services.php')) ?>">Yönet →</a></div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Slider</div>
      <div class="pano-stat-value"><?= $stats['sliders'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/sliders.php')) ?>">Yönet →</a></div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Blog Yazıları</div>
      <div class="pano-stat-value"><?= $stats['blogs'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/blog.php')) ?>">Yönet →</a></div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Çözüm Ortakları</div>
      <div class="pano-stat-value"><?= $stats['partners'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/partners.php')) ?>">Yönet →</a></div>
    </div>

    <div class="pano-stat">
      <div class="pano-stat-label">Galeri Görseli</div>
      <div class="pano-stat-value"><?= $stats['gallery'] ?></div>
      <div class="pano-stat-sub"><a href="<?= h(url('admin/gallery.php')) ?>">Yönet →</a></div>
    </div>

  </div>

  <div class="pano-actions">
    <a href="<?= h(url('admin/products.php?action=add')) ?>" class="pano-action">
      <span class="pano-action-icon">+</span>
      <span>Yeni Ürün Ekle</span>
    </a>
    <a href="<?= h(url('admin/blog.php?action=add')) ?>" class="pano-action">
      <span class="pano-action-icon">📝</span>
      <span>Yeni Blog Yazısı</span>
    </a>
    <a href="<?= h(url('admin/sliders.php')) ?>" class="pano-action">
      <span class="pano-action-icon">🎬</span>
      <span>Slider Düzenle</span>
    </a>
    <a href="<?= h(url('admin/settings.php')) ?>" class="pano-action">
      <span class="pano-action-icon">⚙</span>
      <span>Site Ayarları</span>
    </a>
    <a href="<?= h(url('admin/guncelleme.php')) ?>" class="pano-action">
      <span class="pano-action-icon">🚀</span>
      <span>Güncelleme Merkezi</span>
    </a>
    <a href="<?= h(url('')) ?>" target="_blank" class="pano-action">
      <span class="pano-action-icon">↗</span>
      <span>Siteyi Görüntüle</span>
    </a>
  </div>

  <div class="pano-grid">

    <div class="pano-panel">
      <div class="pano-panel-head">
        <h2>📬 Son Mesajlar</h2>
        <a href="<?= h(url('admin/messages.php')) ?>">Tümünü Gör →</a>
      </div>
      <div class="pano-panel-body">
        <?php if (empty($lastMsg)): ?>
          <div class="pano-empty">
            <div class="pano-empty-icon">📭</div>
            <div>Henüz mesaj yok.</div>
          </div>
        <?php else: ?>
          <?php foreach ($lastMsg as $m): ?>
          <a href="<?= h(url('admin/messages.php?action=view&id=' . $m['id'])) ?>" class="pano-msg <?= !$m['is_read'] ? 'unread' : '' ?>">
            <div class="pano-msg-avatar"><?= h(mb_strtoupper(mb_substr($m['full_name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
            <div class="pano-msg-body">
              <div class="pano-msg-name"><?= h($m['full_name']) ?></div>
              <div class="pano-msg-subject"><?= h($m['subject'] ?: '(konu yok)') ?></div>
            </div>
            <div class="pano-msg-time"><?= h(date('d.m H:i', strtotime($m['created_at']))) ?></div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="pano-panel">
      <div class="pano-panel-head">
        <h2>⚙ Sistem Bilgisi</h2>
      </div>
      <div class="pano-info">
        <div class="pano-info-row">
          <span class="pano-info-key">Sürüm</span>
          <span class="pano-info-val"><span class="pill"><?= h($installedVersion) ?></span></span>
        </div>
        <div class="pano-info-row">
          <span class="pano-info-key">PHP</span>
          <span class="pano-info-val"><?= h(PHP_VERSION) ?></span>
        </div>
        <div class="pano-info-row">
          <span class="pano-info-key">Site</span>
          <span class="pano-info-val"><a href="<?= h(url('')) ?>" target="_blank"><?= h(parse_url(SITE_URL, PHP_URL_HOST) ?: SITE_URL) ?></a></span>
        </div>
        <div class="pano-info-row">
          <span class="pano-info-key">Repo</span>
          <span class="pano-info-val"><?= h(defined('GITHUB_REPO') ? GITHUB_REPO : '-') ?></span>
        </div>
        <div class="pano-info-row">
          <span class="pano-info-key">Sunucu</span>
          <span class="pano-info-val"><?= h($_SERVER['SERVER_SOFTWARE'] ?? '?') ?></span>
        </div>
      </div>
      <div style="padding:0 22px 16px">
        <a href="<?= h(url('admin/guncelleme.php')) ?>" style="display:block;padding:11px 16px;background:#1e4a9e;color:#fff;text-decoration:none;text-align:center;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">🚀 Güncelleme Kontrol Et</a>
      </div>
    </div>

  </div>

  <div class="pano-panel">
    <div class="pano-panel-head">
      <h2>📋 Son Aktiviteler</h2>
      <a href="<?= h(url('admin/activity.php')) ?>">Tümünü Gör →</a>
    </div>
    <div class="pano-panel-body">
      <?php if (empty($lastLogs)): ?>
        <div class="pano-empty">
          <div class="pano-empty-icon">📋</div>
          <div>Henüz aktivite yok.</div>
        </div>
      <?php else: ?>
        <?php foreach ($lastLogs as $l): ?>
        <div class="pano-act">
          <div class="pano-act-head">
            <strong><?= h($l['username'] ?? 'sistem') ?></strong>
            <span><?= h(date('d.m.Y H:i', strtotime($l['created_at']))) ?></span>
          </div>
          <div class="pano-act-body">
            <strong><?= h($l['action'] ?? '?') ?></strong>
            <?php if (!empty($l['target_type'])): ?> · <em><?= h($l['target_type']) ?></em><?php endif; ?>
            <?php if (!empty($l['description'])): ?> — <em><?= h($l['description']) ?></em><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require __DIR__ . '/_footer.php'; ?>
