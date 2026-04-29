<?php
define('TM_ADMIN', true);
$adminTitle = 'WP İçerik Aktarımı';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';
require __DIR__ . '/../install/import-wp.php';

// Sadece superadmin
if (($adminUser['role'] ?? '') !== 'superadmin') {
    adm_back_with('error', 'Bu sayfa için süperadmin yetkisi gerekli.', 'admin/index.php');
}

$action = $_GET['action'] ?? 'home';

$gzPath  = __DIR__ . '/../install/wp-content.json.gz';
$zipPath = __DIR__ . '/../install/wp-uploads.zip';

$gzExists  = file_exists($gzPath);
$zipExists = file_exists($zipPath);

$gzSize  = $gzExists  ? filesize($gzPath)  : 0;
$zipSize = $zipExists ? filesize($zipPath) : 0;

$preview = null;
if ($gzExists) {
    try {
        $preview = WPImporter::loadDataFromGz($gzPath);
    } catch (\Throwable $e) {
        $preview = null;
        $previewError = $e->getMessage();
    }
}

/* ========== POST İşlemleri ========== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if ($action === 'dry_run') {
        try {
            $data = WPImporter::loadDataFromGz($gzPath);
            $importer = new WPImporter(db(), $data, __DIR__ . '/../uploads', ['dry_run' => true]);
            $result = $importer->run();
            $_SESSION['wp_import_result'] = $result;
            $_SESSION['wp_import_was_dry'] = true;
            adm_back_with('success', 'Önizleme tamamlandı. Sonuçlar aşağıda.', 'admin/wp-import.php');
        } catch (\Throwable $e) {
            adm_back_with('error', 'Önizleme hatası: ' . $e->getMessage(), 'admin/wp-import.php');
        }
    }

    if ($action === 'import') {
        // Mutlaka onay kutusu işaretli olmalı
        if (empty($_POST['confirm_import'])) {
            adm_back_with('error', 'Onay kutusunu işaretlemediniz.', 'admin/wp-import.php');
        }

        try {
            $data = WPImporter::loadDataFromGz($gzPath);

            $opts = [
                'dry_run'         => false,
                'wipe_seed'       => !empty($_POST['wipe_seed']),
                'import_pages'    => !empty($_POST['import_pages']),
                'import_posts'    => !empty($_POST['import_posts']),
                'import_banks'    => !empty($_POST['import_banks']),
                'import_partners' => !empty($_POST['import_partners']),
                'import_gallery'  => !empty($_POST['import_gallery']),
            ];

            // Bellek/zaman limit
            @set_time_limit(900);
            @ini_set('memory_limit', '512M');

            $importer = new WPImporter(db(), $data, __DIR__ . '/../uploads', $opts);
            $result = $importer->run();

            // Uploads zip'i de aç (TEK SEFER)
            if (!empty($_POST['extract_uploads']) && $zipExists) {
                $importer->extractUploadsZip($zipPath);
                // run() ikinci kez çağrılmamalı — extract işlemi importer'ın stats/log'unu güncelledi
                // Reflection ile güncel verileri al
                $rc = new \ReflectionClass($importer);
                $sp = $rc->getProperty('stats'); $sp->setAccessible(true);
                $lp = $rc->getProperty('log');   $lp->setAccessible(true);
                $result = [
                    'stats' => $sp->getValue($importer),
                    'log'   => $lp->getValue($importer),
                ];
            }

            $_SESSION['wp_import_result'] = $result;
            $_SESSION['wp_import_was_dry'] = false;

            log_activity('import', 'wp_content', 0, sprintf(
                'WP aktarım: pages=%d, posts=%d, banks=%d, partners=%d, gallery=%d, media=%d',
                $result['stats']['pages']['ok'],
                $result['stats']['posts']['ok'],
                $result['stats']['banks']['ok'],
                $result['stats']['partners']['ok'],
                $result['stats']['gallery']['ok'],
                $result['stats']['media']['copied']
            ));

            adm_back_with('success', 'Aktarım tamamlandı! Sonuçlar aşağıda.', 'admin/wp-import.php');
        } catch (\Throwable $e) {
            adm_back_with('error', 'Aktarım hatası: ' . $e->getMessage(), 'admin/wp-import.php');
        }
    }

    if ($action === 'extract_only') {
        if (!$zipExists) {
            adm_back_with('error', 'wp-uploads.zip bulunamadı.', 'admin/wp-import.php');
        }
        try {
            @set_time_limit(900);
            $data = $preview ?: ['attachments' => []];
            $importer = new WPImporter(db(), $data, __DIR__ . '/../uploads', ['dry_run' => false]);
            $importer->extractUploadsZip($zipPath);
            // Direct erişim için stats'i getMethod'la
            $reflStats = (new \ReflectionClass($importer))->getProperty('stats');
            $reflStats->setAccessible(true);
            $reflLog = (new \ReflectionClass($importer))->getProperty('log');
            $reflLog->setAccessible(true);
            $_SESSION['wp_import_result'] = [
                'stats' => $reflStats->getValue($importer),
                'log'   => $reflLog->getValue($importer),
            ];
            log_activity('extract', 'wp_uploads', 0, 'WP uploads zip açıldı');
            adm_back_with('success', 'Görseller kopyalandı.', 'admin/wp-import.php');
        } catch (\Throwable $e) {
            adm_back_with('error', 'Hata: ' . $e->getMessage(), 'admin/wp-import.php');
        }
    }
}

/* ========== Render ========== */
$result    = $_SESSION['wp_import_result'] ?? null;
$wasDry    = $_SESSION['wp_import_was_dry'] ?? false;
unset($_SESSION['wp_import_result'], $_SESSION['wp_import_was_dry']);
?>

<style>
  .wp-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin:12px 0}
  .wp-stat{background:#fff;border:1px solid #e3e8ef;padding:14px 16px;border-left:3px solid var(--accent, #c8102e)}
  .wp-stat strong{display:block;font-size:24px;font-weight:300;color:var(--primary, #1e4a9e);line-height:1}
  .wp-stat span{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#666;font-weight:600;display:block;margin-top:4px}
  .wp-files{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:18px 0}
  .wp-file{background:#fff;border:1px solid #e3e8ef;padding:16px 18px}
  .wp-file h4{margin:0 0 6px;color:#1e4a9e;font-size:14px}
  .wp-file p{margin:0;font-size:13px;color:#666}
  .wp-file.missing{border-left:3px solid #c8102e;background:#fff5f5}
  .wp-file.ok{border-left:3px solid #16a34a}
  .wp-log{background:#0a1a3a;color:#cbd5e1;font-family:ui-monospace,monospace;font-size:12px;padding:18px 22px;max-height:480px;overflow-y:auto;line-height:1.7;white-space:pre-wrap}
  .wp-log .hl{color:#fff;font-weight:600;display:block;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.1);margin-top:6px}
  .wp-options{display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;margin:14px 0}
  .wp-options label{display:flex;align-items:center;gap:8px;padding:8px 0;font-size:13.5px;cursor:pointer}
  .wp-options input{width:16px;height:16px;cursor:pointer}
  .wp-warn{background:#fff7e6;border-left:3px solid #d97706;padding:14px 18px;font-size:13.5px;color:#7c2d12;margin:14px 0}
</style>

<div class="adm-panel">
    <div class="adm-panel-head"><h2>📥 WP İçerik Aktarım Aracı</h2></div>
    <div class="adm-panel-body">
        <p style="margin:0 0 14px;color:#666;font-size:14px">
            Eski tekcanmetal.com WordPress sitesinden çıkarılan içerikler ve medya dosyalarını yeni CMS'e aktarır.
            Bu araç sadece bir kez kullanılır ve sonrasında kaldırılır.
        </p>

        <div class="wp-files">
            <div class="wp-file <?= $gzExists ? 'ok' : 'missing' ?>">
                <h4><?= $gzExists ? '✓' : '✗' ?> WP İçerik (JSON)</h4>
                <p>install/wp-content.json.gz<br>
                <?php if ($gzExists): ?>
                    <strong><?= number_format($gzSize/1024, 1) ?> KB</strong>
                    <?php if ($preview): ?>
                        — <?= count($preview['pages'] ?? []) ?> sayfa,
                        <?= count($preview['posts'] ?? []) ?> blog yazısı,
                        <?= count($preview['attachments'] ?? []) ?> medya
                    <?php endif; ?>
                <?php else: ?>
                    <em style="color:#c8102e">Bulunamadı</em>
                <?php endif; ?></p>
            </div>
            <div class="wp-file <?= $zipExists ? 'ok' : 'missing' ?>">
                <h4><?= $zipExists ? '✓' : '✗' ?> WP Uploads (ZIP)</h4>
                <p>install/wp-uploads.zip<br>
                <?php if ($zipExists): ?>
                    <strong><?= number_format($zipSize/1024/1024, 1) ?> MB</strong> — uploads/wp-imported/ altına açılacak
                <?php else: ?>
                    <em style="color:#c8102e">Bulunamadı</em>
                <?php endif; ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($result): ?>
<div class="adm-panel">
    <div class="adm-panel-head">
        <h2><?= $wasDry ? '🔍 Önizleme Sonuçları' : '✅ Aktarım Sonuçları' ?></h2>
    </div>
    <div class="adm-panel-body">
        <div class="wp-stats">
            <div class="wp-stat"><strong><?= $result['stats']['pages']['ok'] ?></strong><span>Sayfa</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['posts']['ok'] ?></strong><span>Blog Yazısı</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['banks']['ok'] ?></strong><span>Banka</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['partners']['ok'] ?></strong><span>Partner</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['gallery']['ok'] ?></strong><span>Galeri Görseli</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['media']['copied'] ?? 0 ?></strong><span>Medya Kopyalandı</span></div>
            <div class="wp-stat"><strong><?= $result['stats']['urls_rewritten'] ?></strong><span>URL Yeniden Yazıldı</span></div>
        </div>

        <details>
            <summary style="cursor:pointer;padding:10px 0;font-weight:600;color:#1e4a9e">📋 Detaylı log (<?= count($result['log']) ?> satır)</summary>
            <div class="wp-log"><?php
                foreach ($result['log'] as $line) {
                    if (str_starts_with($line, '===')) {
                        echo '<span class="hl">' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</span>';
                    } else {
                        echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n";
                    }
                }
            ?></div>
        </details>
    </div>
</div>
<?php endif; ?>

<?php if ($gzExists): ?>
<div class="adm-panel">
    <div class="adm-panel-head"><h2>⚙ Aktarım Seçenekleri</h2></div>
    <div class="adm-panel-body">

        <form method="post" action="?action=dry_run" style="margin-bottom:24px">
            <?= csrf_field() ?>
            <p style="font-size:13.5px;color:#666;margin:0 0 10px">
                <strong>Önizleme:</strong> Veritabanına hiçbir şey yazılmaz. Sadece neyin nereye gideceğini gösterir.
            </p>
            <button type="submit" class="adm-btn adm-btn-ghost">🔍 Önizle (Dry Run)</button>
        </form>

        <hr style="border:0;border-top:1px solid #e3e8ef;margin:18px 0">

        <form method="post" action="?action=import">
            <?= csrf_field() ?>

            <h3 style="font-size:14px;color:#1e4a9e;margin:0 0 10px">Aktarılacak İçerikler</h3>
            <div class="wp-options">
                <label><input type="checkbox" name="import_pages" value="1" checked> 📄 Sayfalar (Hakkımızda, KVKK, Fiyat Listeleri)</label>
                <label><input type="checkbox" name="import_posts" value="1" checked> 📝 47 Blog Yazısı</label>
                <label><input type="checkbox" name="import_banks" value="1" checked> 🏦 IBAN (Halkbank + Ziraat)</label>
                <label><input type="checkbox" name="import_partners" value="1" checked> 🤝 11 Çözüm Ortağı (Logolu)</label>
                <label><input type="checkbox" name="import_gallery" value="1" checked> 🖼 Foto Galeri</label>
                <label><input type="checkbox" name="extract_uploads" value="1" <?= $zipExists ? 'checked' : 'disabled' ?>>
                    📦 Medya dosyalarını uploads/'a aç <?= !$zipExists ? '<em>(zip yok)</em>' : '' ?></label>
            </div>

            <h3 style="font-size:14px;color:#1e4a9e;margin:18px 0 10px">Önemli Ayarlar</h3>
            <div class="wp-options">
                <label><input type="checkbox" name="wipe_seed" value="1" checked>
                    🗑 Mevcut seed verilerini temizle (önerilen)</label>
            </div>

            <div class="wp-warn">
                ⚠ <strong>Dikkat:</strong> Bu işlem geri alınamaz. Aktarımdan önce
                <a href="<?= h(url('admin/guncelleme.php')) ?>">Güncelleme Merkezi</a>'nden
                yedek almanız önerilir. "Mevcut seed verilerini temizle" seçili ise mevcut blog
                yazıları, partnerler, IBAN'lar silinir ve WP'den gelen veriler yazılır.
            </div>

            <label style="display:flex;align-items:center;gap:10px;margin:14px 0;padding:10px 14px;background:#fff5f5;border:1px solid #fecaca;font-size:13.5px;cursor:pointer">
                <input type="checkbox" name="confirm_import" value="1" required>
                <span><strong>Anlıyorum, içeriklerin yeniden yazılacağını kabul ediyorum.</strong></span>
            </label>

            <button type="submit" class="adm-btn adm-btn-primary"
                    onclick="return confirm('Aktarım başlasın mı? Bu işlem geri alınamaz.')">
                🚀 Aktarımı Başlat
            </button>
        </form>

        <?php if ($zipExists): ?>
        <hr style="border:0;border-top:1px solid #e3e8ef;margin:18px 0">
        <form method="post" action="?action=extract_only">
            <?= csrf_field() ?>
            <p style="font-size:13.5px;color:#666;margin:0 0 10px">
                <strong>Sadece Görselleri Aç:</strong> Veritabanına dokunmadan sadece wp-uploads.zip'i aç.
            </p>
            <button type="submit" class="adm-btn adm-btn-ghost">📦 Sadece Görselleri Aç</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($preview && empty($previewError)): ?>
<div class="adm-panel">
    <div class="adm-panel-head"><h2>👁 İçerik Önizlemesi</h2></div>
    <div class="adm-panel-body">
        <details>
            <summary style="cursor:pointer;padding:10px 0;font-weight:600;color:#1e4a9e">📄 Sayfalar (<?= count($preview['pages']) ?>)</summary>
            <ul style="font-size:13px;line-height:1.8;margin:10px 0;padding-left:22px">
                <?php foreach ($preview['pages'] as $p): ?>
                    <li><strong><?= htmlspecialchars($p['title']) ?></strong>
                        — <code><?= htmlspecialchars($p['slug']) ?></code>
                        — <?= number_format(strlen($p['content_clean'] ?? '')) ?> kar.</li>
                <?php endforeach; ?>
            </ul>
        </details>

        <details>
            <summary style="cursor:pointer;padding:10px 0;font-weight:600;color:#1e4a9e">📝 Blog Yazıları (<?= count($preview['posts']) ?>)</summary>
            <ul style="font-size:13px;line-height:1.7;margin:10px 0;padding-left:22px;column-count:2;column-gap:30px">
                <?php foreach ($preview['posts'] as $p): ?>
                    <li><?= htmlspecialchars($p['title']) ?></li>
                <?php endforeach; ?>
            </ul>
        </details>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
