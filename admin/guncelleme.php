<?php
define('TM_ADMIN', true);
$adminTitle = 'Güncelleme Merkezi';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

// Sadece superadmin ve admin
if (!in_array($adminUser['role'] ?? '', ['superadmin', 'admin'], true)) {
    adm_back_with('error', 'Bu sayfa için yetkiniz yok.', 'admin/index.php');
}

$action = $_GET['action'] ?? 'home';

// GitHub config — önce settings, sonra config.php constant'ları
$githubRepo  = settings('github_repo')  ?: (defined('GITHUB_REPO')  ? GITHUB_REPO  : '');
$githubToken = settings('github_token') ?: (defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '');

/* ========== HELPERS ========== */
function gh_api(string $url, ?string $token = null, bool $raw = false): array {
    $ch = curl_init($url);
    $headers = [
        'User-Agent: TekcanMetal-CMS/' . TM_VERSION,
        'Accept: ' . ($raw ? 'application/octet-stream' : 'application/vnd.github+json'),
        'X-GitHub-Api-Version: 2022-11-28',
    ];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'error' => $err];
}

function gh_download_to_file(string $url, string $token, string $dest): bool {
    $fp = fopen($dest, 'wb');
    if (!$fp) return false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE           => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 600,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: TekcanMetal-CMS/' . TM_VERSION,
            'Accept: application/octet-stream',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $ok   = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    return $ok && $code >= 200 && $code < 400 && filesize($dest) > 0;
}

function backup_dir(): string {
    $dir = __DIR__ . '/../yedek';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir;
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $p = $dir . '/' . $entry;
        if (is_dir($p)) rrmdir($p);
        else @unlink($p);
    }
    @rmdir($dir);
}

function rcopy(string $src, string $dst): void {
    if (!is_dir($dst)) @mkdir($dst, 0755, true);
    foreach (scandir($src) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $sp = $src . '/' . $entry;
        $dp = $dst . '/' . $entry;
        if (is_dir($sp)) rcopy($sp, $dp);
        else @copy($sp, $dp);
    }
}

function exclude_paths(): array {
    return ['config.php', 'uploads', 'yedek', '.htaccess', 'install', '.git', '.github'];
}

function backup_current(string $version): ?string {
    $bd = backup_dir();
    $stamp = date('Ymd_His');
    $file = $bd . "/{$version}_{$stamp}.zip";
    $root = realpath(__DIR__ . '/..');
    if (!$root) return null;

    $zip = new ZipArchive();
    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return null;

    $excluded = exclude_paths();
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($rii as $f) {
        $fpath = $f->getPathname();
        $rel = ltrim(substr($fpath, strlen($root)), '/\\');
        $top = explode('/', str_replace('\\', '/', $rel))[0] ?? '';
        if (in_array($top, $excluded, true)) continue;
        if ($f->isDir()) continue;
        $zip->addFile($fpath, $rel);
    }
    $zip->close();
    return file_exists($file) ? $file : null;
}

function extract_update_zip(string $zipPath): array {
    if (!file_exists($zipPath)) return ['ok' => false, 'msg' => 'Zip bulunamadı.'];
    $tmp = sys_get_temp_dir() . '/tm_update_' . uniqid();
    @mkdir($tmp, 0755, true);

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) return ['ok' => false, 'msg' => 'Zip açılamadı.'];
    if (!$zip->extractTo($tmp)) { $zip->close(); return ['ok' => false, 'msg' => 'Zip extract edilemedi.']; }
    $zip->close();

    // GitHub zipball iç klasör adı: <user>-<repo>-<sha>/. İlk klasörü bul.
    $entries = array_values(array_filter(scandir($tmp), fn($e) => $e !== '.' && $e !== '..'));
    if (count($entries) === 1 && is_dir($tmp . '/' . $entries[0])) {
        $source = $tmp . '/' . $entries[0];
    } else {
        $source = $tmp;
    }

    $root = realpath(__DIR__ . '/..');
    $excluded = exclude_paths();
    $copied = 0;

    foreach (scandir($source) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        if (in_array($entry, $excluded, true)) continue;
        $sp = $source . '/' . $entry;
        $dp = $root . '/' . $entry;
        if (is_dir($sp)) { rcopy($sp, $dp); $copied++; }
        else { @copy($sp, $dp); $copied++; }
    }

    rrmdir($tmp);
    return ['ok' => true, 'msg' => "$copied öğe güncellendi.", 'count' => $copied];
}

/* ========== POST ACTIONS ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {

    /* Güncelleme kontrol */
    if ($action === 'check') {
        if (!$githubRepo) adm_back_with('error', 'GitHub repo ayarı boş. Önce Ayarlar → GitHub sekmesinden ayarlayın.', 'admin/guncelleme.php');

        $r = gh_api("https://api.github.com/repos/{$githubRepo}/releases/latest", $githubToken ?: null);
        if ($r['code'] !== 200) {
            adm_back_with('error', "GitHub API hatası ({$r['code']}): " . ($r['error'] ?: 'release bulunamadı.'), 'admin/guncelleme.php');
        }
        $_SESSION['gh_latest'] = $r['body'];
        adm_back_with('success', 'Sürüm bilgisi alındı.', 'admin/guncelleme.php');
    }

    /* GitHub'dan güncelle */
    if ($action === 'update_github') {
        if (!$githubRepo)  adm_back_with('error', 'GitHub repo ayarı boş.', 'admin/guncelleme.php');
        if (!$githubToken) adm_back_with('error', 'GitHub token boş. Ayarlardan ekleyin.', 'admin/guncelleme.php');

        $r = gh_api("https://api.github.com/repos/{$githubRepo}/releases/latest", $githubToken);
        if ($r['code'] !== 200) adm_back_with('error', "GitHub API hatası ({$r['code']}).", 'admin/guncelleme.php');

        $rel = json_decode($r['body'], true);
        $newVersion = ltrim($rel['tag_name'] ?? '', 'v');
        if (!$newVersion) adm_back_with('error', 'Geçerli sürüm bulunamadı.', 'admin/guncelleme.php');

        // Asset varsa onu, yoksa zipball'u al
        $downloadUrl = null;
        if (!empty($rel['assets']) && is_array($rel['assets'])) {
            foreach ($rel['assets'] as $asset) {
                if (str_ends_with(($asset['name'] ?? ''), '.zip')) {
                    $downloadUrl = $asset['url']; // API URL
                    break;
                }
            }
        }
        if (!$downloadUrl) $downloadUrl = $rel['zipball_url'] ?? null;
        if (!$downloadUrl) adm_back_with('error', 'İndirilebilir zip bulunamadı.', 'admin/guncelleme.php');

        // 1. Yedek
        $backupFile = backup_current(TM_VERSION);
        if (!$backupFile) adm_back_with('error', 'Yedekleme başarısız. Güncelleme iptal edildi.', 'admin/guncelleme.php');

        // 2. İndir
        $tmpZip = sys_get_temp_dir() . '/tm_update_' . uniqid() . '.zip';
        if (!gh_download_to_file($downloadUrl, $githubToken, $tmpZip)) {
            adm_back_with('error', 'Güncelleme zip indirilemedi.', 'admin/guncelleme.php');
        }

        // 3. Extract
        $res = extract_update_zip($tmpZip);
        @unlink($tmpZip);
        if (!$res['ok']) adm_back_with('error', 'Extract hatası: ' . $res['msg'], 'admin/guncelleme.php');

        // 4. Migration SQL varsa çalıştır
        $migrationFile = __DIR__ . '/../install/migration.sql';
        if (file_exists($migrationFile)) {
            try {
                $sql = file_get_contents($migrationFile);
                if ($sql) db()->exec($sql);
            } catch (Throwable $e) { /* ignore */ }
        }

        // 4.5. Yeni seed-images dosyalarını uploads'a senkronize et
        $seedImagesDir = __DIR__ . '/../install/seed-images';
        $uploadsDir    = __DIR__ . '/../uploads';
        if (is_dir($seedImagesDir)) {
            $rii = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($seedImagesDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($rii as $file) {
                $rel = ltrim(substr($file->getPathname(), strlen($seedImagesDir)), '/\\');
                $dst = $uploadsDir . '/' . $rel;
                if ($file->isDir()) {
                    if (!is_dir($dst)) @mkdir($dst, 0755, true);
                } else {
                    @mkdir(dirname($dst), 0755, true);
                    if (!file_exists($dst)) @copy($file->getPathname(), $dst);
                }
            }
        }

        // 5. Versiyon kaydı
        q("INSERT IGNORE INTO tm_system_versions (version, source, release_date, notes, applied_by) VALUES (?,?,?,?,?)", [
            $newVersion,
            'github',
            isset($rel['published_at']) ? date('Y-m-d H:i:s', strtotime($rel['published_at'])) : date('Y-m-d H:i:s'),
            mb_substr($rel['body'] ?? '', 0, 5000),
            $adminUser['username'] ?? 'admin',
        ]);

        // 6. config.php içindeki TM_VERSION'u güncelle (mümkünse)
        $cfgPath = __DIR__ . '/../config.php';
        if (file_exists($cfgPath) && is_writable($cfgPath)) {
            $cfg = file_get_contents($cfgPath);
            $cfg = preg_replace("/define\(['\"]TM_VERSION['\"]\s*,\s*['\"][^'\"]+['\"]\);/", "define('TM_VERSION', '" . $newVersion . "');", $cfg);
            file_put_contents($cfgPath, $cfg);
        }

        log_activity('update', 'system', 0, "GitHub'dan güncellendi: " . TM_VERSION . ' → ' . $newVersion);
        unset($_SESSION['gh_latest']);
        adm_back_with('success', "Sürüm $newVersion uygulandı. Sayfayı yenileyin.", 'admin/guncelleme.php');
    }

    /* Manuel zip yükleme ile güncelle */
    if ($action === 'manual_upload') {
        if (empty($_FILES['zip']['name'])) adm_back_with('error', 'Zip seçilmedi.', 'admin/guncelleme.php');
        if (($_FILES['zip']['error'] ?? 0) !== UPLOAD_ERR_OK) adm_back_with('error', 'Yükleme hatası.', 'admin/guncelleme.php');
        if (!str_ends_with(strtolower($_FILES['zip']['name']), '.zip')) adm_back_with('error', 'Sadece .zip dosyası kabul edilir.', 'admin/guncelleme.php');

        $tmpZip = sys_get_temp_dir() . '/tm_manual_' . uniqid() . '.zip';
        if (!move_uploaded_file($_FILES['zip']['tmp_name'], $tmpZip)) {
            adm_back_with('error', 'Geçici dosya oluşturulamadı.', 'admin/guncelleme.php');
        }

        $backupFile = backup_current(TM_VERSION);
        if (!$backupFile) {
            @unlink($tmpZip);
            adm_back_with('error', 'Yedekleme başarısız.', 'admin/guncelleme.php');
        }

        $res = extract_update_zip($tmpZip);
        @unlink($tmpZip);
        if (!$res['ok']) adm_back_with('error', $res['msg'], 'admin/guncelleme.php');

        // Migration
        $migrationFile = __DIR__ . '/../install/migration.sql';
        if (file_exists($migrationFile)) {
            try { $sql = file_get_contents($migrationFile); if ($sql) db()->exec($sql); } catch (Throwable $e) {}
        }

        // Seed-images senkronizasyonu (yeni dosyalar uploads'a gelsin)
        $seedImagesDir = __DIR__ . '/../install/seed-images';
        $uploadsDir    = __DIR__ . '/../uploads';
        if (is_dir($seedImagesDir)) {
            $rii = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($seedImagesDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($rii as $file) {
                $rel = ltrim(substr($file->getPathname(), strlen($seedImagesDir)), '/\\');
                $dst = $uploadsDir . '/' . $rel;
                if ($file->isDir()) {
                    if (!is_dir($dst)) @mkdir($dst, 0755, true);
                } else {
                    @mkdir(dirname($dst), 0755, true);
                    if (!file_exists($dst)) @copy($file->getPathname(), $dst);
                }
            }
        }

        q("INSERT IGNORE INTO tm_system_versions (version, source, notes, applied_by) VALUES (?,?,?,?)", [
            'manual-' . date('Ymd-His'), 'manual', 'Manuel zip ile güncelleme.', $adminUser['username'] ?? 'admin',
        ]);

        log_activity('update', 'system', 0, 'Manuel zip ile güncellendi');
        adm_back_with('success', 'Manuel güncelleme uygulandı: ' . $res['msg'], 'admin/guncelleme.php');
    }

    /* Yedek silme */
    if ($action === 'delete_backup') {
        $name = basename($_POST['file'] ?? '');
        $bd = backup_dir();
        $path = $bd . '/' . $name;
        if (str_ends_with($name, '.zip') && file_exists($path)) {
            @unlink($path);
            log_activity('delete', 'backup', 0, 'Yedek silindi: ' . $name);
            adm_back_with('success', 'Yedek silindi.', 'admin/guncelleme.php');
        }
        adm_back_with('error', 'Yedek bulunamadı.', 'admin/guncelleme.php');
    }
}

/* ========== RENDER ========== */
$latest = !empty($_SESSION['gh_latest']) ? json_decode($_SESSION['gh_latest'], true) : null;
$latestVersion = $latest ? ltrim($latest['tag_name'] ?? '', 'v') : null;
$hasUpdate = $latestVersion && version_gt($latestVersion, TM_VERSION);

$versions = all("SELECT * FROM tm_system_versions ORDER BY id DESC LIMIT 20");

// Backup dosyaları
$backups = [];
$bd = backup_dir();
if (is_dir($bd)) {
    foreach (scandir($bd) as $e) {
        if (str_ends_with($e, '.zip')) {
            $backups[] = ['name' => $e, 'size' => filesize($bd . '/' . $e), 'mtime' => filemtime($bd . '/' . $e)];
        }
    }
    usort($backups, fn($a, $b) => $b['mtime'] - $a['mtime']);
}
?>

<div class="adm-grid-2">

    <div class="adm-panel">
        <div class="adm-panel-head"><h2>📦 Sürüm Bilgisi</h2></div>
        <div class="adm-panel-body">
            <div class="adm-version-box">
                <div>
                    <small>Mevcut Sürüm</small>
                    <div class="big"><?= h(TM_VERSION) ?></div>
                </div>
                <?php if ($latestVersion): ?>
                    <div>
                        <small>GitHub Son Sürüm</small>
                        <div class="big"><?= h($latestVersion) ?>
                            <?php if ($hasUpdate): ?>
                                <span class="badge badge-warn">YENİ</span>
                            <?php else: ?>
                                <span class="badge badge-on">Güncel</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <hr style="margin:14px 0;border:0;border-top:1px solid #2a3552">

            <p style="font-size:13px;color:#aaa;margin:0 0 8px">
                <strong>Repo:</strong> <code><?= h($githubRepo ?: 'AYARLANMADI') ?></code><br>
                <strong>Token:</strong> <?= $githubToken ? '<span class="badge badge-on">Aktif</span>' : '<span class="badge badge-off">Yok</span>' ?>
            </p>

            <form method="post" action="?action=check" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="adm-btn adm-btn-primary">🔍 Güncelleme Kontrol Et</button>
            </form>
            <a href="<?= h(admin_url('settings.php#github')) ?>" class="adm-btn adm-btn-ghost">⚙ GitHub Ayarları</a>
        </div>
    </div>

    <?php if ($latest): ?>
        <div class="adm-panel">
            <div class="adm-panel-head"><h2>📋 Son Yayın Notları</h2></div>
            <div class="adm-panel-body">
                <h3 style="margin:0 0 6px"><?= h($latest['name'] ?? $latestVersion) ?></h3>
                <p style="font-size:12px;color:#aaa">
                    <?= h($latestVersion) ?>
                    <?php if (!empty($latest['published_at'])): ?>
                        · <?= h(tr_date($latest['published_at'])) ?>
                    <?php endif; ?>
                </p>
                <div class="adm-changelog"><?= nl2br(h(mb_substr($latest['body'] ?? '(boş)', 0, 4000))) ?></div>

                <?php if ($hasUpdate): ?>
                    <hr style="margin:14px 0;border:0;border-top:1px solid #2a3552">
                    <form method="post" action="?action=update_github" onsubmit="return confirm('Sürüm <?= h($latestVersion) ?> uygulanacak. Önce yedek alınacak. Devam edilsin mi?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="adm-btn adm-btn-primary">⬇ Sürüm <?= h($latestVersion) ?> Uygula</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="adm-panel">
    <div class="adm-panel-head"><h2>📥 Manuel Zip Yükle</h2></div>
    <div class="adm-panel-body">
        <p style="font-size:13px;color:#aaa">GitHub bağlantısı çalışmıyorsa, sürüm zip dosyasını buradan yükleyebilirsiniz. Yedek otomatik alınır.</p>
        <form method="post" action="?action=manual_upload" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
            <?= csrf_field() ?>
            <div style="flex:1;min-width:240px">
                <label>Sürüm Zip</label>
                <input type="file" name="zip" accept=".zip" required>
            </div>
            <button type="submit" class="adm-btn adm-btn-primary" onclick="return confirm('Zip uygulanacak. Yedek otomatik alınır. Devam?')">Yükle ve Uygula</button>
        </form>
    </div>
</div>

<div class="adm-panel">
    <div class="adm-panel-head"><h2>🕒 Sürüm Geçmişi (<?= count($versions) ?>)</h2></div>
    <div class="adm-panel-body" style="padding:0">
        <?php if (!$versions): ?>
            <div class="adm-empty"><div class="ico">🕒</div>Henüz sürüm geçmişi yok.</div>
        <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Sürüm</th><th>Kaynak</th><th>Yayın</th><th>Uygulanış</th><th>Uygulayan</th></tr></thead>
                <tbody>
                    <?php foreach ($versions as $v): ?>
                        <tr>
                            <td><strong><?= h($v['version']) ?></strong></td>
                            <td><span class="badge"><?= h($v['source']) ?></span></td>
                            <td><small><?= h($v['release_date'] ? tr_date($v['release_date']) : '—') ?></small></td>
                            <td><small><?= h(tr_date($v['applied_at'])) ?></small></td>
                            <td><small><?= h($v['applied_by'] ?: '—') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="adm-panel">
    <div class="adm-panel-head"><h2>💾 Yedekler (<?= count($backups) ?>)</h2></div>
    <div class="adm-panel-body" style="padding:0">
        <?php if (!$backups): ?>
            <div class="adm-empty"><div class="ico">💾</div>Henüz yedek yok.</div>
        <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Dosya</th><th>Boyut</th><th>Tarih</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($backups as $b): ?>
                        <tr>
                            <td><code><?= h($b['name']) ?></code></td>
                            <td><?= number_format($b['size'] / 1024 / 1024, 2, ',', '.') ?> MB</td>
                            <td><small><?= h(date('Y-m-d H:i', $b['mtime'])) ?></small></td>
                            <td class="actions">
                                <a href="<?= h(url('yedek/' . $b['name'])) ?>" class="adm-btn adm-btn-sm adm-btn-ghost" download>İndir</a>
                                <form method="post" action="?action=delete_backup" style="display:inline" onsubmit="return confirm('Yedek silinecek. Emin misiniz?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="file" value="<?= h($b['name']) ?>">
                                    <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
