<?php
/**
 * WP İçerik Yenileme (v1.0.30)
 *
 * Bu sayfa tekcanme_talsite.sql'den tam parse edilmiş wp-content-full.json.gz'yi
 * okur ve mevcut blog yazılarının/sayfaların içeriklerini ezer.
 *
 * Önceki import'tan farkı: içerik (post_content) önceden BOŞ kalıyordu.
 * Bu refresh script'i 663 karakter Camgöz Demir gibi tam içerikleri DB'ye yazar.
 *
 * Sadece superadmin erişebilir. Admin panelinden tetiklenir.
 */

declare(strict_types=1);

define('TM_ADMIN', true);
$adminTitle = 'WP İçerik Yenileme (Full)';

require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

if (($adminUser['role'] ?? '') !== 'superadmin') {
    adm_back_with('error', 'Bu sayfa için süperadmin yetkisi gerekli.', 'admin/index.php');
}

$gzPath = __DIR__ . '/../install/wp-content-full.json.gz';
$exists = file_exists($gzPath);

$data = null;
$loadError = null;
if ($exists) {
    try {
        $raw = file_get_contents($gzPath);
        if ($raw === false) throw new Exception("Dosya okunamadı");
        $json = gzdecode($raw);
        if ($json === false) throw new Exception("gzdecode başarısız");
        $data = json_decode($json, true);
        if (!is_array($data)) throw new Exception("JSON parse başarısız");
    } catch (\Throwable $e) {
        $loadError = $e->getMessage();
    }
}

// İŞLEM
$action = $_POST['action'] ?? '';
$result = null;

if ($action === 'refresh' && $data) {
    csrf_check();

    $pdo = db();
    $log = [];
    $stats = ['posts' => 0, 'pages' => 0, 'created' => 0, 'updated' => 0, 'errors' => 0];

    // Blog yazıları
    foreach ($data['posts'] as $bp) {
        try {
            $existing = row("SELECT id FROM tm_blog_posts WHERE slug=?", [$bp['slug']]);

            // İçerik tam mı?
            $content = trim($bp['content'] ?? '');
            $title = trim($bp['title'] ?? '');
            $excerpt = trim($bp['excerpt'] ?? '');
            $featured = $bp['featured_image'] ?? null;
            $pubDate = $bp['date'] ?? date('Y-m-d H:i:s');

            if ($existing) {
                // GÜNCELLE
                $stmt = $pdo->prepare("UPDATE tm_blog_posts
                    SET title=?, content=?, excerpt=?, cover_image=?, published_at=?
                    WHERE id=?");
                $stmt->execute([$title, $content, $excerpt, $featured, $pubDate, $existing['id']]);
                $stats['updated']++;
                $log[] = "♻ [POST] {$bp['slug']} — güncellendi (" . strlen($content) . " char)";
            } else {
                // YENİ EKLE
                $stmt = $pdo->prepare("INSERT INTO tm_blog_posts
                    (slug, title, content, excerpt, cover_image, published_at, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$bp['slug'], $title, $content, $excerpt, $featured, $pubDate]);
                $stats['created']++;
                $log[] = "✓ [POST] {$bp['slug']} — eklendi (" . strlen($content) . " char)";
            }
            $stats['posts']++;
        } catch (\Throwable $e) {
            $stats['errors']++;
            $log[] = "❌ [POST] {$bp['slug']} — " . $e->getMessage();
        }
    }

    // Sayfalar
    foreach ($data['pages'] as $pg) {
        try {
            $existing = row("SELECT id FROM tm_pages WHERE slug=?", [$pg['slug']]);

            $content = trim($pg['content'] ?? '');
            $title = trim($pg['title'] ?? '');

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE tm_pages
                    SET title=?, content=?
                    WHERE id=?");
                $stmt->execute([$title, $content, $existing['id']]);
                $stats['updated']++;
                $log[] = "♻ [PAGE] {$pg['slug']} — güncellendi (" . strlen($content) . " char)";
            } else {
                $stmt = $pdo->prepare("INSERT INTO tm_pages
                    (slug, title, content, is_active)
                    VALUES (?, ?, ?, 1)");
                $stmt->execute([$pg['slug'], $title, $content]);
                $stats['created']++;
                $log[] = "✓ [PAGE] {$pg['slug']} — eklendi (" . strlen($content) . " char)";
            }
            $stats['pages']++;
        } catch (\Throwable $e) {
            $stats['errors']++;
            $log[] = "❌ [PAGE] {$pg['slug']} — " . $e->getMessage();
        }
    }

    log_activity('wp_refresh', 'system', null,
        "Blog: {$stats['posts']}, Page: {$stats['pages']}, Created: {$stats['created']}, Updated: {$stats['updated']}, Errors: {$stats['errors']}");

    $result = [
        'stats' => $stats,
        'log' => $log,
    ];
}

// Mevcut DB durumu
$dbBlogCount = (int)val("SELECT COUNT(*) FROM tm_blog_posts");
$dbBlogEmptyCount = (int)val("SELECT COUNT(*) FROM tm_blog_posts WHERE content IS NULL OR content=''");
$dbPageCount = (int)val("SELECT COUNT(*) FROM tm_pages");
$dbPageEmptyCount = (int)val("SELECT COUNT(*) FROM tm_pages WHERE content IS NULL OR content=''");
?>

<style>
.wprefresh{display:grid;gap:18px}
.wpr-banner{
  background:linear-gradient(135deg, #050d24 0%, #143672 100%);
  color:#fff;padding:28px 32px;
}
.wpr-banner h1{margin:0 0 6px;font-size:22px;font-weight:600;letter-spacing:-.3px}
.wpr-banner p{margin:0;font-size:13.5px;color:rgba(255,255,255,.75);line-height:1.5}

.wpr-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media (max-width:900px){.wpr-grid{grid-template-columns:1fr}}

.wpr-card{
  background:#fff;border:1px solid #e3e8ef;padding:20px 22px;
}
.wpr-card-label{
  font-size:10.5px;font-weight:700;letter-spacing:1.5px;
  text-transform:uppercase;color:#5e6470;margin-bottom:14px;
}
.wpr-card-row{
  display:flex;justify-content:space-between;align-items:baseline;
  padding:10px 0;border-bottom:1px solid #f0f3f7;font-size:13px;
}
.wpr-card-row:last-child{border-bottom:0}
.wpr-card-row .lbl{color:#5e6470}
.wpr-card-row .val{font-weight:600;color:#0a1a3a;font-family:Georgia,serif;font-size:18px}
.wpr-card-row .val.warn{color:#c8102e}
.wpr-card-row .val.ok{color:#10803a}

.wpr-source{
  background:#fafbfd;padding:18px 22px;border:1px solid #e3e8ef;
}
.wpr-source code{
  font-family:ui-monospace,monospace;background:#fff;
  padding:2px 6px;border:1px solid #e3e8ef;font-size:12px;
}

.wpr-action{
  background:#0a1a3a;color:#fff;padding:32px;
  display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap;
}
.wpr-action h2{margin:0 0 6px;font-size:18px;font-weight:600;color:#fff}
.wpr-action p{margin:0;font-size:13px;color:rgba(255,255,255,.7);line-height:1.5;max-width:560px}
.wpr-action button{
  background:#c8102e;color:#fff;border:0;padding:14px 28px;
  font-size:12.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  cursor:pointer;flex-shrink:0;transition:.18s;
}
.wpr-action button:hover{background:#a00d24;transform:translateY(-1px)}

.wpr-result{
  background:#0d1418;color:#9be4bb;padding:20px 24px;
  font-family:ui-monospace,monospace;font-size:11.5px;line-height:1.7;
  max-height:500px;overflow:auto;
}
.wpr-result-stats{
  background:#fff;border:1px solid #e3e8ef;
  padding:18px 22px;display:flex;gap:32px;flex-wrap:wrap;
}
.wpr-result-stats div{font-size:13px}
.wpr-result-stats .num{font-size:22px;font-weight:700;color:#0a1a3a;font-family:Georgia,serif;display:block}
.wpr-result-stats .lbl{font-size:10.5px;text-transform:uppercase;letter-spacing:1px;color:#5e6470;font-weight:700;margin-top:4px;display:block}

.wpr-error{background:#fee;border:1px solid #fcc;color:#900;padding:14px 18px;font-size:13px}
</style>

<div class="wprefresh">

  <div class="wpr-banner">
    <h1>📥 WP İçerik Yenileme — TAM AKTARIM</h1>
    <p>Bu işlem orijinal <code>tekcanme_talsite.sql</code> (17 MB) yedeğinden tam parse edilmiş 47 blog yazısı ve 9 sayfanın içeriğini DB'deki kayıtlara <strong>EZER</strong>. Önceki import içerik alanını boş bırakmıştı (örn. <em>Camgöz Demir'e Ziyaret</em>); bu işlem onu giderir.</p>
  </div>

  <?php if ($result): ?>
  <div class="wpr-result-stats">
    <div>
      <span class="num"><?= $result['stats']['posts'] ?></span>
      <span class="lbl">Blog Yazısı İşlendi</span>
    </div>
    <div>
      <span class="num"><?= $result['stats']['pages'] ?></span>
      <span class="lbl">Sayfa İşlendi</span>
    </div>
    <div>
      <span class="num"><?= $result['stats']['created'] ?></span>
      <span class="lbl">Yeni Eklendi</span>
    </div>
    <div>
      <span class="num"><?= $result['stats']['updated'] ?></span>
      <span class="lbl">Güncellendi</span>
    </div>
    <div>
      <span class="num" style="color:<?= $result['stats']['errors'] > 0 ? '#c8102e' : '#10803a' ?>"><?= $result['stats']['errors'] ?></span>
      <span class="lbl">Hata</span>
    </div>
  </div>

  <div class="wpr-result">
    <?php foreach ($result['log'] as $line): ?><?= h($line) ?>
<?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($loadError): ?>
  <div class="wpr-error">❌ JSON dosyası okunamadı: <?= h($loadError) ?></div>
  <?php elseif (!$exists): ?>
  <div class="wpr-error">❌ <code>install/wp-content-full.json.gz</code> bulunamadı.<br>Bu dosya v1.0.30 güncellemesi ile gelir. Önce güncellemeyi uygula.</div>
  <?php else: ?>
  <div class="wpr-grid">
    <div class="wpr-card">
      <div class="wpr-card-label">📦 Kaynak — JSON dosyası</div>
      <div class="wpr-card-row">
        <span class="lbl">Blog Yazıları</span>
        <span class="val ok"><?= count($data['posts']) ?></span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">Sayfalar</span>
        <span class="val ok"><?= count($data['pages']) ?></span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">Dosya boyutu</span>
        <span class="val" style="font-size:13px;font-family:ui-monospace,monospace"><?= number_format(filesize($gzPath) / 1024, 1) ?> KB</span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">Kaynak</span>
        <span class="val" style="font-size:11px;font-family:ui-monospace,monospace">tekcanme_talsite.sql</span>
      </div>
    </div>

    <div class="wpr-card">
      <div class="wpr-card-label">🗄️ Hedef — Mevcut DB</div>
      <div class="wpr-card-row">
        <span class="lbl">Toplam Blog Yazısı</span>
        <span class="val"><?= $dbBlogCount ?></span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">— İçeriği BOŞ olan</span>
        <span class="val <?= $dbBlogEmptyCount > 0 ? 'warn' : 'ok' ?>"><?= $dbBlogEmptyCount ?></span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">Toplam Sayfa</span>
        <span class="val"><?= $dbPageCount ?></span>
      </div>
      <div class="wpr-card-row">
        <span class="lbl">— İçeriği BOŞ olan</span>
        <span class="val <?= $dbPageEmptyCount > 0 ? 'warn' : 'ok' ?>"><?= $dbPageEmptyCount ?></span>
      </div>
    </div>
  </div>

  <div class="wpr-action">
    <div>
      <h2>🔄 Tam Yenilemeyi Başlat</h2>
      <p>JSON kaynağındaki <strong><?= count($data['posts']) ?> blog yazısı</strong> ve <strong><?= count($data['pages']) ?> sayfa</strong> içeriği DB'ye işlenecek. Mevcut kayıtlar GÜNCELLENECEK, yenileri EKLENECEK. <strong>Geri alınamaz.</strong></p>
    </div>
    <form method="post" onsubmit="return confirm('TAM YENİLEME başlasın mı?\n\n• Mevcut blog/page içerikleri ezilir\n• Eksik yazılar eklenir\n• Featured image bağlantıları yenilenir\n\nDevam etmek istediğinden emin misin?');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="refresh">
      <button type="submit">🔄 TAM YENİLEMEYİ BAŞLAT →</button>
    </form>
  </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/_footer.php'; ?>
