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

    /* ========== Rollback — yedekten geri dön ========== */
    if ($action === 'rollback') {
        $name = basename($_POST['file'] ?? '');
        $bd = backup_dir();
        $path = $bd . '/' . $name;
        if (!str_ends_with($name, '.zip') || !file_exists($path)) {
            adm_back_with('error', 'Yedek bulunamadı.', 'admin/guncelleme.php');
        }

        // Önce mevcut hâlin yedeğini al (rollback öncesi snapshot)
        $preRollback = backup_current('pre-rollback-' . TM_VERSION);

        // Sonra yedeği aç
        $tmp = sys_get_temp_dir() . '/tm_rollback_' . uniqid();
        @mkdir($tmp, 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            adm_back_with('error', 'Yedek zip açılamadı.', 'admin/guncelleme.php');
        }
        if (!$zip->extractTo($tmp)) {
            $zip->close();
            adm_back_with('error', 'Yedek extract edilemedi.', 'admin/guncelleme.php');
        }
        $zip->close();

        // Dosyaları root'a kopyala (config.php ve uploads hariç)
        $root = realpath(__DIR__ . '/..');
        $excluded = exclude_paths();
        $copied = 0;
        foreach (scandir($tmp) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            if (in_array($entry, $excluded, true)) continue;
            $sp = $tmp . '/' . $entry;
            $dp = $root . '/' . $entry;
            if (is_dir($sp)) { rcopy($sp, $dp); $copied++; }
            else { @copy($sp, $dp); $copied++; }
        }
        rrmdir($tmp);

        log_activity('rollback', 'system', 0, "Yedekten geri dönüldü: $name ($copied öğe)");
        adm_back_with('success', "Yedekten geri dönüldü: $copied öğe restore edildi.", 'admin/guncelleme.php');
    }

    /* ========== Görselleri Yenile (Seed-Images Resync) ========== */
    if ($action === 'resync_images') {
        $seedDir   = __DIR__ . '/../install/seed-images';
        $uploadDir = __DIR__ . '/../uploads';
        $copied = 0; $skipped = 0; $errors = 0;
        $force = !empty($_POST['force']);

        if (!is_dir($seedDir)) {
            adm_back_with('error', 'Seed-images dizini bulunamadı: ' . $seedDir, 'admin/guncelleme.php');
        }
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

        try {
            $rii = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($seedDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($rii as $file) {
                $rel = ltrim(substr($file->getPathname(), strlen($seedDir)), '/\\');
                $dst = $uploadDir . '/' . $rel;
                if ($file->isDir()) {
                    if (!is_dir($dst)) @mkdir($dst, 0755, true);
                } else {
                    @mkdir(dirname($dst), 0755, true);
                    if ($force || !file_exists($dst)) {
                        if (@copy($file->getPathname(), $dst)) $copied++;
                        else $errors++;
                    } else {
                        $skipped++;
                    }
                }
            }
            log_activity('sync', 'seed-images', 0, "Senkronize edildi: $copied yeni, $skipped atlandı, $errors hata");
            $msg = "Görseller yenilendi: $copied yeni dosya kopyalandı";
            if ($skipped) $msg .= ", $skipped dosya zaten mevcut";
            if ($errors) $msg .= ", $errors hata oluştu";
            adm_back_with($errors ? 'warning' : 'success', $msg . '.', 'admin/guncelleme.php');
        } catch (Throwable $e) {
            adm_back_with('error', 'Senkronizasyon hatası: ' . $e->getMessage(), 'admin/guncelleme.php');
        }
    }
}


/* ════════════════════════════════════════════════════════════════
 * RENDER — Dark GitHub-style UI (5 sekme)
 * ════════════════════════════════════════════════════════════════ */
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

$activeTab = $_GET['tab'] ?? 'overview';
$valid = ['overview','releases','backups','tools','settings'];
if (!in_array($activeTab, $valid, true)) $activeTab = 'overview';

function fmt_bytes(int $b): string {
    if ($b < 1024) return $b . ' B';
    if ($b < 1024*1024) return number_format($b/1024, 1) . ' KB';
    return number_format($b/1024/1024, 1) . ' MB';
}
function rel_time(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60) return $diff . ' sn önce';
    if ($diff < 3600) return floor($diff/60) . ' dk önce';
    if ($diff < 86400) return floor($diff/3600) . ' saat önce';
    if ($diff < 604800) return floor($diff/86400) . ' gün önce';
    return date('d.m.Y', $ts);
}
?>

<style>
  /* ═══ DARK UI — Güncelleme Merkezi (GitHub Releases tarzı) ═══ */
  .gm-shell{background:#0d1117;color:#c9d1d9;border:1px solid #30363d;margin:-20px -20px 0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}
  .gm-head{padding:24px 28px;border-bottom:1px solid #30363d;background:linear-gradient(180deg, #161b22 0%, #0d1117 100%)}
  .gm-head-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
  .gm-title{margin:0;font-size:20px;font-weight:600;color:#f0f6fc;letter-spacing:-.3px}
  .gm-title small{display:block;font-size:12px;font-weight:400;color:#8b949e;letter-spacing:0;margin-top:4px}
  .gm-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;border-radius:20px}
  .gm-badge-ok{background:rgba(46,160,67,.15);color:#3fb950;border:1px solid rgba(46,160,67,.4)}
  .gm-badge-new{background:rgba(248,158,38,.15);color:#f8a337;border:1px solid rgba(248,158,38,.4);animation:gmPulse 1.8s infinite}
  .gm-badge-off{background:rgba(248,81,73,.15);color:#f85149;border:1px solid rgba(248,81,73,.4)}
  @keyframes gmPulse{0%,100%{box-shadow:0 0 0 0 rgba(248,158,38,.4)}50%{box-shadow:0 0 0 6px rgba(248,158,38,0)}}

  .gm-version-row{display:flex;align-items:baseline;gap:18px;margin-top:18px;flex-wrap:wrap}
  .gm-vbox{display:flex;flex-direction:column;gap:4px}
  .gm-vbox label{font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;color:#8b949e;font-weight:600}
  .gm-vbox .val{font-size:24px;font-weight:600;color:#f0f6fc;font-family:ui-monospace,SFMono-Regular,monospace}
  .gm-arrow{font-size:24px;color:#8b949e;align-self:center}

  /* Sekmeler */
  .gm-tabs{display:flex;gap:0;border-bottom:1px solid #30363d;background:#0d1117;padding:0 28px;overflow-x:auto}
  .gm-tab{display:inline-flex;align-items:center;gap:8px;padding:14px 18px;font-size:13px;font-weight:500;color:#8b949e;text-decoration:none;border-bottom:2px solid transparent;white-space:nowrap;transition:.18s}
  .gm-tab:hover{color:#c9d1d9;border-bottom-color:#484f58}
  .gm-tab.active{color:#f0f6fc;border-bottom-color:#f8a337;font-weight:600}
  .gm-tab-count{display:inline-block;padding:2px 8px;font-size:11px;background:#30363d;color:#c9d1d9;border-radius:20px;margin-left:4px}

  .gm-body{padding:24px 28px}

  /* Kartlar */
  .gm-card{background:#161b22;border:1px solid #30363d;border-radius:6px;margin-bottom:14px;overflow:hidden}
  .gm-card-head{padding:14px 18px;border-bottom:1px solid #30363d;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#0d1117}
  .gm-card-head h3{margin:0;font-size:14px;font-weight:600;color:#f0f6fc;letter-spacing:-.1px}
  .gm-card-head small{color:#8b949e;font-size:12px}
  .gm-card-body{padding:18px}
  .gm-card-body p{color:#c9d1d9;font-size:13.5px;line-height:1.7;margin:0 0 12px}
  .gm-card-body p:last-child{margin-bottom:0}

  /* Butonlar */
  .gm-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;font-size:13px;font-weight:500;border-radius:6px;cursor:pointer;text-decoration:none;border:1px solid transparent;font-family:inherit;transition:.15s;line-height:1.4}
  .gm-btn-primary{background:#238636;color:#fff;border-color:rgba(240,246,252,.1)}
  .gm-btn-primary:hover{background:#2ea043}
  .gm-btn-warn{background:#bb800a;color:#fff;border-color:rgba(240,246,252,.1)}
  .gm-btn-warn:hover{background:#cc8e0c}
  .gm-btn-danger{background:#da3633;color:#fff;border-color:rgba(240,246,252,.1)}
  .gm-btn-danger:hover{background:#f85149}
  .gm-btn-default{background:#21262d;color:#c9d1d9;border-color:#30363d}
  .gm-btn-default:hover{background:#30363d;border-color:#484f58}
  .gm-btn-block{display:flex;width:100%;justify-content:center}

  /* Liste/tablo */
  .gm-list{margin:0;padding:0;list-style:none}
  .gm-list-item{padding:14px 18px;border-bottom:1px solid #21262d;display:flex;align-items:center;justify-content:space-between;gap:12px;transition:.15s}
  .gm-list-item:last-child{border-bottom:0}
  .gm-list-item:hover{background:#0d1117}
  .gm-list-meta{display:flex;flex-direction:column;gap:4px;min-width:0;flex:1}
  .gm-list-name{font-size:13.5px;font-weight:600;color:#f0f6fc}
  .gm-list-name code{background:transparent;font-size:13px;color:#79c0ff}
  .gm-list-info{font-size:12px;color:#8b949e;display:flex;gap:14px;flex-wrap:wrap}
  .gm-list-info span{display:inline-flex;gap:4px}
  .gm-list-actions{display:flex;gap:6px;flex-shrink:0}

  /* Empty state */
  .gm-empty{text-align:center;padding:40px 20px;color:#8b949e}
  .gm-empty .icon{font-size:36px;margin-bottom:8px;opacity:.5}
  .gm-empty p{margin:0;font-size:13.5px}

  /* Form */
  .gm-form-row{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
  .gm-form-row label{font-size:11px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:#8b949e}
  .gm-form-row input[type="text"],.gm-form-row input[type="password"],.gm-form-row input[type="file"]{
    padding:9px 12px;font-size:13.5px;font-family:ui-monospace,monospace;
    background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;outline:none;transition:.15s;
  }
  .gm-form-row input:focus{border-color:#388bfd;box-shadow:0 0 0 3px rgba(56,139,253,.25)}
  .gm-form-row .help{font-size:11.5px;color:#8b949e;margin-top:4px}

  /* Release notes */
  .gm-release-notes{background:#0d1117;border:1px solid #30363d;border-radius:6px;padding:16px 20px;font-family:ui-monospace,monospace;font-size:12.5px;color:#c9d1d9;line-height:1.7;max-height:280px;overflow-y:auto;white-space:pre-wrap}

  /* Update banner */
  .gm-update-banner{background:linear-gradient(135deg, #1f6feb 0%, #388bfd 100%);color:#fff;padding:18px 22px;border-radius:8px;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:18px;border:1px solid rgba(255,255,255,.1)}
  .gm-update-banner h3{margin:0 0 4px;font-size:16px;font-weight:600;color:#fff}
  .gm-update-banner p{margin:0;font-size:13.5px;color:rgba(255,255,255,.85);line-height:1.5}
  .gm-update-banner .gm-btn-primary{background:#fff;color:#1f6feb;border-color:rgba(0,0,0,.1)}
  .gm-update-banner .gm-btn-primary:hover{background:rgba(255,255,255,.92)}

  /* Stats grid */
  .gm-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px}
  .gm-stat{background:#161b22;border:1px solid #30363d;border-radius:6px;padding:14px 16px}
  .gm-stat .num{font-size:22px;font-weight:600;color:#f0f6fc;font-family:ui-monospace,monospace;letter-spacing:-.5px;line-height:1.2}
  .gm-stat .lbl{font-size:11px;letter-spacing:1.2px;text-transform:uppercase;color:#8b949e;font-weight:600;margin-top:4px}

  /* Mobile */
  @media (max-width:768px){
    .gm-shell{margin:-12px -12px 0}
    .gm-head{padding:18px 16px}
    .gm-tabs{padding:0 16px}
    .gm-body{padding:18px 16px}
  }
</style>

<div class="gm-shell">

  <!-- ═══ ÜST BAŞLIK ═══ -->
  <div class="gm-head">
    <div class="gm-head-row">
      <div>
        <h1 class="gm-title">
          🚀 Güncelleme Merkezi
          <small>Tekcan Metal CMS — GitHub Releases üzerinden otomatik güncelleme</small>
        </h1>
      </div>
      <div>
        <?php if ($hasUpdate): ?>
          <span class="gm-badge gm-badge-new">⚠ Yeni Sürüm Mevcut</span>
        <?php elseif ($latestVersion): ?>
          <span class="gm-badge gm-badge-ok">✓ Güncel</span>
        <?php else: ?>
          <span class="gm-badge gm-badge-off">Bilinmiyor</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="gm-version-row">
      <div class="gm-vbox">
        <label>Mevcut Sürüm</label>
        <div class="val">v<?= h(TM_VERSION) ?></div>
      </div>
      <?php if ($latestVersion): ?>
        <div class="gm-arrow">→</div>
        <div class="gm-vbox">
          <label>GitHub Son Sürüm</label>
          <div class="val" style="color:<?= $hasUpdate ? '#f8a337' : '#3fb950' ?>">v<?= h($latestVersion) ?></div>
        </div>
        <?php if ($latest && !empty($latest['published_at'])): ?>
        <div class="gm-vbox" style="margin-left:14px">
          <label>Yayın Tarihi</label>
          <div class="val" style="font-size:14px;font-weight:400;color:#8b949e"><?= h(date('d.m.Y H:i', strtotime($latest['published_at']))) ?></div>
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══ SEKMELER ═══ -->
  <nav class="gm-tabs">
    <a href="?tab=overview" class="gm-tab <?= $activeTab === 'overview' ? 'active' : '' ?>">📊 Genel Durum</a>
    <a href="?tab=releases" class="gm-tab <?= $activeTab === 'releases' ? 'active' : '' ?>">🏷 Sürüm Geçmişi <span class="gm-tab-count"><?= count($versions) ?></span></a>
    <a href="?tab=backups" class="gm-tab <?= $activeTab === 'backups' ? 'active' : '' ?>">💾 Yedekler <span class="gm-tab-count"><?= count($backups) ?></span></a>
    <a href="?tab=tools" class="gm-tab <?= $activeTab === 'tools' ? 'active' : '' ?>">🔧 Araçlar</a>
    <a href="?tab=settings" class="gm-tab <?= $activeTab === 'settings' ? 'active' : '' ?>">⚙ Ayarlar</a>
  </nav>

  <div class="gm-body">

    <?php if ($activeTab === 'overview'): ?>
      <!-- ═══ GENEL DURUM ═══ -->

      <?php if ($hasUpdate): ?>
      <div class="gm-update-banner">
        <div>
          <h3>🚀 Yeni sürüm hazır: v<?= h($latestVersion) ?></h3>
          <p>Mevcut: v<?= h(TM_VERSION) ?> — Tek tıkla yedek alıp güncelleyebilirsin.</p>
        </div>
        <form method="post" action="?action=update_github" style="margin:0">
          <?= csrf_field() ?>
          <button type="submit" class="gm-btn gm-btn-primary"
                  onclick="return confirm('Güncelleme uygulansın mı? Önce otomatik yedek alınacak.')">
            ⬇ Güncelle (v<?= h($latestVersion) ?>)
          </button>
        </form>
      </div>
      <?php endif; ?>

      <div class="gm-stats">
        <div class="gm-stat">
          <div class="num">v<?= h(TM_VERSION) ?></div>
          <div class="lbl">Mevcut Sürüm</div>
        </div>
        <div class="gm-stat">
          <div class="num"><?= $latestVersion ? 'v' . h($latestVersion) : '—' ?></div>
          <div class="lbl">GitHub Son</div>
        </div>
        <div class="gm-stat">
          <div class="num"><?= count($versions) ?></div>
          <div class="lbl">Toplam Güncelleme</div>
        </div>
        <div class="gm-stat">
          <div class="num"><?= count($backups) ?></div>
          <div class="lbl">Mevcut Yedek</div>
        </div>
      </div>

      <div class="gm-card">
        <div class="gm-card-head">
          <h3>🔄 Sürüm Kontrolü</h3>
          <small>GitHub Releases API üzerinden son sürümü kontrol et</small>
        </div>
        <div class="gm-card-body">
          <p>GitHub'da yeni bir sürüm yayınlanıp yayınlanmadığını kontrol etmek için aşağıdaki butonu kullan. Kontrol sonucu üst bölümde görüntülenir.</p>
          <form method="post" action="?action=check" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="gm-btn gm-btn-default">🔍 Güncelleme Kontrol Et</button>
          </form>
          <?php if (!$githubRepo || !$githubToken): ?>
          <p style="color:#f85149;margin-top:12px;font-size:13px">
            ⚠ GitHub ayarları eksik. <a href="?tab=settings" style="color:#79c0ff">Ayarlar</a> sekmesinden tamamla.
          </p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($latest && !empty($latest['body'])): ?>
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>📋 v<?= h($latestVersion) ?> — Yayın Notları</h3>
          <small><?= !empty($latest['published_at']) ? h(date('d.m.Y H:i', strtotime($latest['published_at']))) : '' ?></small>
        </div>
        <div class="gm-card-body">
          <div class="gm-release-notes"><?= h(mb_substr($latest['body'] ?? '', 0, 4000)) ?></div>
        </div>
      </div>
      <?php endif; ?>

    <?php elseif ($activeTab === 'releases'): ?>
      <!-- ═══ SÜRÜM GEÇMİŞİ ═══ -->
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>🏷 Uygulanan Sürümler</h3>
          <small><?= count($versions) ?> kayıt</small>
        </div>
        <?php if (!$versions): ?>
          <div class="gm-empty">
            <div class="icon">📭</div>
            <p>Henüz hiç güncelleme uygulanmamış.</p>
          </div>
        <?php else: ?>
          <ul class="gm-list">
            <?php foreach ($versions as $v): ?>
            <li class="gm-list-item">
              <div class="gm-list-meta">
                <div class="gm-list-name">v<?= h($v['version']) ?> <span style="color:#8b949e;font-weight:400;font-size:12px">— <?= h($v['source'] ?? '') ?></span></div>
                <div class="gm-list-info">
                  <span>📅 <?= h(date('d.m.Y H:i', strtotime($v['created_at'] ?? 'now'))) ?></span>
                  <?php if (!empty($v['applied_by'])): ?>
                    <span>👤 <?= h($v['applied_by']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="gm-list-actions">
                <span style="color:#8b949e;font-size:12px;font-family:ui-monospace,monospace">#<?= (int)$v['id'] ?></span>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

    <?php elseif ($activeTab === 'backups'): ?>
      <!-- ═══ YEDEKLER ═══ -->
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>💾 Otomatik Yedekler</h3>
          <small>Her güncelleme öncesi otomatik alınır — geri dönmek için 'Geri Yükle'</small>
        </div>
        <?php if (!$backups): ?>
          <div class="gm-empty">
            <div class="icon">💿</div>
            <p>Henüz yedek yok. Bir güncelleme uygulandığında otomatik yedek alınır.</p>
          </div>
        <?php else: ?>
          <ul class="gm-list">
            <?php foreach ($backups as $b): ?>
            <li class="gm-list-item">
              <div class="gm-list-meta">
                <div class="gm-list-name"><code><?= h($b['name']) ?></code></div>
                <div class="gm-list-info">
                  <span>📦 <?= fmt_bytes($b['size']) ?></span>
                  <span>⏱ <?= rel_time($b['mtime']) ?></span>
                  <span>📅 <?= date('d.m.Y H:i', $b['mtime']) ?></span>
                </div>
              </div>
              <div class="gm-list-actions">
                <form method="post" action="?action=rollback" style="display:inline" onsubmit="return confirm('⚠ DİKKAT: Bu işlem mevcut dosyaları yedekteki haliyle değiştirecek. Devam edilsin mi?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="file" value="<?= h($b['name']) ?>">
                  <button type="submit" class="gm-btn gm-btn-warn">↶ Geri Yükle</button>
                </form>
                <form method="post" action="?action=delete_backup" style="display:inline" onsubmit="return confirm('Yedek silinsin mi?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="file" value="<?= h($b['name']) ?>">
                  <button type="submit" class="gm-btn gm-btn-default">🗑</button>
                </form>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

    <?php elseif ($activeTab === 'tools'): ?>
      <!-- ═══ ARAÇLAR ═══ -->

      <!-- Manuel ZIP Yükle -->
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>📥 Manuel ZIP Yükle</h3>
          <small>GitHub bağlantısı sorunluysa manuel sürüm zip yükle</small>
        </div>
        <div class="gm-card-body">
          <p>GitHub bağlantısı çalışmıyorsa veya elde release zip dosyan varsa buradan yükleyebilirsin. Yedek otomatik alınır.</p>
          <form method="post" action="?action=manual_upload" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            <?= csrf_field() ?>
            <div class="gm-form-row" style="flex:1;min-width:240px;margin:0">
              <label>Sürüm ZIP Dosyası</label>
              <input type="file" name="zip" accept=".zip" required>
            </div>
            <button type="submit" class="gm-btn gm-btn-primary"
                    onclick="return confirm('ZIP uygulansın mı? Yedek otomatik alınır.')">
              ⬆ Yükle ve Uygula
            </button>
          </form>
        </div>
      </div>

      <!-- Görselleri Yenile -->
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>🖼 Görselleri Yenile</h3>
          <small>install/seed-images klasöründen uploads klasörüne kopyala</small>
        </div>
        <div class="gm-card-body">
          <p>Sitedeki ürün/kategori/hizmet/slider görselleri eksikse, paketle gelen <code style="color:#79c0ff">install/seed-images/</code> klasöründeki dosyaları <code style="color:#79c0ff">uploads/</code>'a senkronize eder.</p>
          <form method="post" action="?action=resync_images" style="display:flex;gap:8px;flex-wrap:wrap">
            <?= csrf_field() ?>
            <button type="submit" class="gm-btn gm-btn-default">📋 Sadece Eksik Olanları Kopyala</button>
            <button type="submit" name="force" value="1" class="gm-btn gm-btn-warn"
                    onclick="return confirm('Mevcut tüm görseller paket içindekilerle değiştirilecek. Devam edilsin mi?')">
              ⚡ Tüm Görselleri Üzerine Yaz
            </button>
          </form>
        </div>
      </div>

    <?php elseif ($activeTab === 'settings'): ?>
      <!-- ═══ AYARLAR ═══ -->
      <div class="gm-card">
        <div class="gm-card-head">
          <h3>⚙ GitHub Bağlantı Ayarları</h3>
          <small>Otomatik güncelleme için gerekli bilgiler</small>
        </div>
        <div class="gm-card-body">
          <div class="gm-form-row">
            <label>📦 Repository</label>
            <input type="text" value="<?= h($githubRepo ?: '—') ?>" readonly style="background:#0d1117;cursor:not-allowed">
            <div class="help">Settings → System → GitHub Repo'dan değiştirilebilir</div>
          </div>
          <div class="gm-form-row">
            <label>🌿 Branch</label>
            <input type="text" value="<?= h($githubBranch) ?>" readonly style="background:#0d1117;cursor:not-allowed">
          </div>
          <div class="gm-form-row">
            <label>🔑 Token Durumu</label>
            <div>
              <?php if ($githubToken): ?>
                <span class="gm-badge gm-badge-ok">✓ Token Aktif (****<?= h(substr($githubToken, -6)) ?>)</span>
              <?php else: ?>
                <span class="gm-badge gm-badge-off">✗ Token Eksik</span>
              <?php endif; ?>
            </div>
          </div>
          <p style="margin-top:18px">
            <a href="<?= h(url('admin/settings.php')) ?>" class="gm-btn gm-btn-default">→ Ayarlar Sayfasına Git</a>
          </p>
        </div>
      </div>

      <div class="gm-card">
        <div class="gm-card-head">
          <h3>📍 Sürüm Bilgisi</h3>
        </div>
        <div class="gm-card-body">
          <div class="gm-form-row">
            <label>Mevcut Sürüm</label>
            <input type="text" value="v<?= h(TM_VERSION) ?>" readonly>
          </div>
          <div class="gm-form-row">
            <label>config.php Yolu</label>
            <input type="text" value="<?= h(realpath(__DIR__ . '/../config.php')) ?>" readonly>
            <div class="help">Sürüm güncellendiğinde TM_VERSION otomatik yazılır</div>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
