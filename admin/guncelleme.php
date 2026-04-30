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
  /* ═══ Güncelleme Merkezi — Tekcan Metal palet (lacivert + kırmızı) ═══ */
  :root{
    --gm-primary:#1e4a9e;
    --gm-primary-dark:#143672;
    --gm-primary-soft:#ebf1fb;
    --gm-accent:#c8102e;
    --gm-accent-dark:#a00d24;
    --gm-accent-soft:#fff0f2;
    --gm-bg:#fff;
    --gm-bg-alt:#f5f7fa;
    --gm-bg-section:#fafbfd;
    --gm-border:#e3e8ef;
    --gm-border-strong:#d0d7e0;
    --gm-text:#1a1a1a;
    --gm-text-muted:#5e6470;
    --gm-text-dim:#94a3b8;
    --gm-success:#16a34a;
    --gm-success-soft:#dcfce7;
    --gm-warn:#d97706;
    --gm-warn-soft:#fef3c7;
  }

  .gm-shell{background:var(--gm-bg);border:1px solid var(--gm-border);margin:0 0 16px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;color:var(--gm-text)}

  /* ÜST BAŞLIK — koyu lacivert holding banner */
  .gm-head{
    padding:28px 30px;
    background:linear-gradient(135deg, #050d24 0%, #0c1e44 50%, var(--gm-primary-dark) 100%);
    color:#fff;
    position:relative;
    overflow:hidden;
  }
  .gm-head::before{
    content:'';
    position:absolute;
    top:-30%;right:-15%;
    width:60%;height:160%;
    background:radial-gradient(ellipse 50% 50% at 50% 50%, rgba(74,139,214,.18) 0%, transparent 65%);
    pointer-events:none;
  }
  .gm-head::after{
    content:'';
    position:absolute;
    left:0;bottom:0;
    width:100%;height:3px;
    background:var(--gm-accent);
  }
  .gm-head-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;z-index:2}
  .gm-title{margin:0;font-size:22px;font-weight:600;color:#fff;letter-spacing:-.4px;line-height:1.2}
  .gm-title small{display:block;font-size:12.5px;font-weight:400;color:rgba(255,255,255,.7);letter-spacing:0;margin-top:6px}
  .gm-kicker{
    display:inline-block;
    font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;
    color:#fff;background:var(--gm-accent);padding:5px 12px;margin-bottom:10px;
  }

  /* Sürüm kartı */
  .gm-version-row{display:flex;align-items:flex-end;gap:24px;margin-top:22px;flex-wrap:wrap;position:relative;z-index:2}
  .gm-vbox{display:flex;flex-direction:column;gap:6px;min-width:140px}
  .gm-vbox label{font-size:10.5px;letter-spacing:1.6px;text-transform:uppercase;color:rgba(255,255,255,.65);font-weight:700}
  .gm-vbox .val{font-size:28px;font-weight:600;color:#fff;font-family:ui-monospace,SFMono-Regular,monospace;letter-spacing:-.5px;line-height:1}
  .gm-vbox .val.muted{font-size:14px;font-weight:400;color:rgba(255,255,255,.85);font-family:inherit;letter-spacing:0}
  .gm-arrow{font-size:24px;color:rgba(255,255,255,.4);align-self:center;font-weight:300;padding:0 4px}

  /* Badge'ler */
  .gm-badge{
    display:inline-flex;align-items:center;gap:6px;
    padding:7px 14px;font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
    border:1px solid;
  }
  .gm-badge-ok{background:rgba(34,197,94,.15);color:#86efac;border-color:rgba(34,197,94,.4)}
  .gm-badge-new{background:var(--gm-accent);color:#fff;border-color:var(--gm-accent);animation:gmPulse 1.8s infinite}
  .gm-badge-off{background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.2)}
  @keyframes gmPulse{0%,100%{box-shadow:0 0 0 0 rgba(200,16,46,.5)}50%{box-shadow:0 0 0 10px rgba(200,16,46,0)}}

  /* SEKMELER — Light tema */
  .gm-tabs{
    display:flex;gap:0;
    border-bottom:1px solid var(--gm-border);
    background:var(--gm-bg);
    padding:0 30px;
    overflow-x:auto;
  }
  .gm-tab{
    display:inline-flex;align-items:center;gap:8px;
    padding:16px 22px;
    font-size:13px;font-weight:600;letter-spacing:.3px;
    color:var(--gm-text-muted);
    text-decoration:none;
    border-bottom:3px solid transparent;
    white-space:nowrap;
    transition:.18s;
  }
  .gm-tab:hover{color:var(--gm-primary);background:var(--gm-bg-section)}
  .gm-tab.active{color:var(--gm-primary);border-bottom-color:var(--gm-accent);font-weight:700}
  .gm-tab-count{
    display:inline-block;padding:2px 9px;font-size:11px;font-weight:600;
    background:var(--gm-bg-alt);color:var(--gm-text-muted);
    border-radius:20px;margin-left:4px;
  }
  .gm-tab.active .gm-tab-count{background:var(--gm-accent-soft);color:var(--gm-accent)}

  .gm-body{padding:24px 30px}

  /* Kartlar */
  .gm-card{background:#fff;border:1px solid var(--gm-border);margin-bottom:16px;overflow:hidden}
  .gm-card-head{
    padding:16px 22px;
    border-bottom:1px solid var(--gm-border);
    background:var(--gm-bg-section);
    display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
  }
  .gm-card-head h3{margin:0;font-size:15px;font-weight:700;color:var(--gm-primary);letter-spacing:-.1px}
  .gm-card-head small{color:var(--gm-text-muted);font-size:12px}
  .gm-card-body{padding:20px 22px}
  .gm-card-body p{color:var(--gm-text);font-size:13.5px;line-height:1.7;margin:0 0 12px}
  .gm-card-body p:last-child{margin-bottom:0}

  /* Butonlar */
  .gm-btn{
    display:inline-flex;align-items:center;gap:8px;
    padding:10px 18px;
    font-size:12.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
    cursor:pointer;text-decoration:none;
    border:1px solid transparent;
    font-family:inherit;
    transition:.18s;
    line-height:1.4;
  }
  .gm-btn-primary{background:var(--gm-primary);color:#fff}
  .gm-btn-primary:hover{background:var(--gm-primary-dark);transform:translateY(-1px);box-shadow:0 8px 16px rgba(30,74,158,.2)}
  .gm-btn-accent{background:var(--gm-accent);color:#fff}
  .gm-btn-accent:hover{background:var(--gm-accent-dark);transform:translateY(-1px);box-shadow:0 8px 16px rgba(200,16,46,.2)}
  .gm-btn-warn{background:var(--gm-warn);color:#fff}
  .gm-btn-warn:hover{background:#b45309;transform:translateY(-1px)}
  .gm-btn-default{background:#fff;color:var(--gm-text);border-color:var(--gm-border)}
  .gm-btn-default:hover{background:var(--gm-bg-alt);border-color:var(--gm-border-strong)}
  .gm-btn-danger{background:#fff;color:var(--gm-accent);border-color:var(--gm-accent)}
  .gm-btn-danger:hover{background:var(--gm-accent);color:#fff}
  .gm-btn-block{display:flex;width:100%;justify-content:center}

  /* Liste */
  .gm-list{margin:0;padding:0;list-style:none}
  .gm-list-item{
    padding:16px 22px;
    border-bottom:1px solid var(--gm-border);
    display:flex;align-items:center;justify-content:space-between;gap:14px;
    transition:.15s;
  }
  .gm-list-item:last-child{border-bottom:0}
  .gm-list-item:hover{background:var(--gm-bg-section)}
  .gm-list-meta{display:flex;flex-direction:column;gap:5px;min-width:0;flex:1}
  .gm-list-name{font-size:14px;font-weight:700;color:var(--gm-primary)}
  .gm-list-name code{background:var(--gm-bg-alt);font-size:13px;color:var(--gm-primary);padding:2px 8px;border-radius:0;font-family:ui-monospace,monospace}
  .gm-list-info{font-size:12px;color:var(--gm-text-muted);display:flex;gap:16px;flex-wrap:wrap}
  .gm-list-info span{display:inline-flex;gap:5px;align-items:center}
  .gm-list-actions{display:flex;gap:8px;flex-shrink:0;align-items:center}

  /* Empty state */
  .gm-empty{text-align:center;padding:48px 24px;color:var(--gm-text-muted)}
  .gm-empty .icon{font-size:42px;margin-bottom:10px;opacity:.4}
  .gm-empty p{margin:0;font-size:13.5px}

  /* Form */
  .gm-form-row{display:flex;flex-direction:column;gap:8px;margin-bottom:18px}
  .gm-form-row label{font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;color:var(--gm-text-muted)}
  .gm-form-row input[type="text"],.gm-form-row input[type="password"],.gm-form-row input[type="file"]{
    padding:11px 14px;font-size:13.5px;font-family:ui-monospace,monospace;
    background:#fff;border:1px solid var(--gm-border);color:var(--gm-text);
    outline:none;transition:.15s;
  }
  .gm-form-row input:focus{border-color:var(--gm-primary);box-shadow:0 0 0 3px rgba(30,74,158,.12)}
  .gm-form-row input[readonly]{background:var(--gm-bg-alt);cursor:not-allowed;color:var(--gm-text-muted)}
  .gm-form-row .help{font-size:11.5px;color:var(--gm-text-muted);margin-top:2px}

  /* Release notes */
  .gm-release-notes{
    background:var(--gm-bg-alt);border:1px solid var(--gm-border);
    padding:18px 22px;
    font-family:ui-monospace,SFMono-Regular,monospace;font-size:12.5px;
    color:var(--gm-text);line-height:1.75;
    max-height:320px;overflow-y:auto;
    white-space:pre-wrap;word-break:break-word;
  }

  /* Update banner — site paleti */
  .gm-update-banner{
    background:linear-gradient(135deg, var(--gm-accent) 0%, var(--gm-accent-dark) 100%);
    color:#fff;
    padding:22px 28px;
    display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;
    margin-bottom:20px;
    position:relative;overflow:hidden;
  }
  .gm-update-banner::before{
    content:'';
    position:absolute;
    top:-30%;right:-10%;
    width:50%;height:160%;
    background:radial-gradient(ellipse at center, rgba(255,255,255,.12) 0%, transparent 70%);
    pointer-events:none;
  }
  .gm-update-banner > *{position:relative;z-index:2}
  .gm-update-banner h3{margin:0 0 6px;font-size:18px;font-weight:600;color:#fff;letter-spacing:-.2px}
  .gm-update-banner p{margin:0;font-size:13.5px;color:rgba(255,255,255,.9);line-height:1.55}
  .gm-update-banner .gm-btn-primary{background:#fff;color:var(--gm-accent);border-color:rgba(0,0,0,.05)}
  .gm-update-banner .gm-btn-primary:hover{background:rgba(255,255,255,.92);color:var(--gm-accent-dark);box-shadow:0 12px 24px rgba(0,0,0,.15)}

  /* Stats grid */
  .gm-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:20px}
  .gm-stat{
    background:#fff;
    border:1px solid var(--gm-border);
    border-left:3px solid var(--gm-accent);
    padding:16px 20px;
  }
  .gm-stat .num{
    font-size:24px;font-weight:300;
    color:var(--gm-primary);
    font-family:ui-monospace,SFMono-Regular,monospace;
    letter-spacing:-.5px;line-height:1.2;
  }
  .gm-stat .lbl{
    font-size:10.5px;letter-spacing:1.4px;text-transform:uppercase;
    color:var(--gm-text-muted);font-weight:700;margin-top:6px;
  }

  /* Mobile */
  @media (max-width:768px){
    .gm-head{padding:20px 18px}
    .gm-tabs{padding:0 18px}
    .gm-body{padding:18px}
    .gm-version-row{gap:14px}
    .gm-vbox .val{font-size:22px}
    .gm-arrow{display:none}
    .gm-update-banner{padding:18px 20px}
    .gm-list-item{flex-direction:column;align-items:flex-start;gap:12px}
    .gm-list-actions{align-self:stretch;justify-content:flex-start}
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
          <div class="val" style="color:<?= $hasUpdate ? '#f59e0b' : 'var(--gm-success)' ?>">v<?= h($latestVersion) ?></div>
        </div>
        <?php if ($latest && !empty($latest['published_at'])): ?>
        <div class="gm-vbox" style="margin-left:14px">
          <label>Yayın Tarihi</label>
          <div class="val" style="font-size:14px;font-weight:400;color:var(--gm-text-muted)"><?= h(date('d.m.Y H:i', strtotime($latest['published_at']))) ?></div>
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
          <p style="color:var(--gm-accent);margin-top:12px;font-size:13px">
            ⚠ GitHub ayarları eksik. <a href="?tab=settings" style="color:var(--gm-primary)">Ayarlar</a> sekmesinden tamamla.
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
                <div class="gm-list-name">v<?= h($v['version']) ?> <span style="color:var(--gm-text-muted);font-weight:400;font-size:12px">— <?= h($v['source'] ?? '') ?></span></div>
                <div class="gm-list-info">
                  <span>📅 <?= h(date('d.m.Y H:i', strtotime($v['created_at'] ?? 'now'))) ?></span>
                  <?php if (!empty($v['applied_by'])): ?>
                    <span>👤 <?= h($v['applied_by']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="gm-list-actions">
                <span style="color:var(--gm-text-muted);font-size:12px;font-family:ui-monospace,monospace">#<?= (int)$v['id'] ?></span>
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
          <p>Sitedeki ürün/kategori/hizmet/slider görselleri eksikse, paketle gelen <code style="color:var(--gm-primary)">install/seed-images/</code> klasöründeki dosyaları <code style="color:var(--gm-primary)">uploads/</code>'a senkronize eder.</p>
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
            <input type="text" value="<?= h($githubRepo ?: '—') ?>" readonly style="background:var(--gm-bg-alt);cursor:not-allowed">
            <div class="help">Settings → System → GitHub Repo'dan değiştirilebilir</div>
          </div>
          <div class="gm-form-row">
            <label>🌿 Branch</label>
            <input type="text" value="<?= h($githubBranch) ?>" readonly style="background:var(--gm-bg-alt);cursor:not-allowed">
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
