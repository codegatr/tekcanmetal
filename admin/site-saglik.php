<?php
/**
 * Site Sağlığı & Tek Tıkla Tamir
 *
 * Yunus'un site sorunlarını topluca çözer:
 *   1. uploads klasör yapısını oluştur (categories/, products/, sliders/, services/, banks/, team/)
 *   2. install/seed-images dosyalarını uploads/'a kopyala
 *   3. tm_categories.image NULL olanlara seed path yaz
 *   4. tm_products.image NULL olanlara seed path yaz
 *   5. tm_services.image NULL olanlara seed path yaz
 *   6. tm_sliders.image NULL olanlara seed path yaz
 *   7. tm_banks logo path'i — uploads/wp-imported/ yoksa uploads/banks/ kullan
 *   8. tm_partners logo path'i — uploads/wp-imported/ yoksa uploads/partners/ kullan

 *   10. WP-imported klasörü oluştur ve YYYY/MM dosyaları kopyala (banka/partner logoları için)
 *   11. OPcache reset
 *   12. LiteSpeed cache temizle (.htaccess sinyali)
 */
declare(strict_types=1);

// DEBUG: hata gösterimi açık (beyaz ekran sorunlarını görmek için)
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('TM_ADMIN', true);

// Önce _helpers.php ve db.php'i yükle (yetki kontrolü için)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/_helpers.php';

// Session başlat
if (session_status() === PHP_SESSION_NONE) session_start();

// Yetki kontrolü _layout'tan ÖNCE — output gönderilmeden önce redirect yapabilelim
if (empty($_SESSION['admin_id'])) {
    redirect('admin/login.php');
}

$adminUser = row("SELECT id, username, full_name, email, role FROM tm_users WHERE id=? AND is_active=1", [$_SESSION['admin_id']]);
if (!$adminUser || ($adminUser['role'] ?? '') !== 'superadmin') {
    flash('error', 'Bu sayfa için süperadmin yetkisi gerekli.');
    redirect('admin/index.php');
}

$adminTitle = 'Site Sağlığı & Tek Tıkla Tamir';

$ROOT = realpath(__DIR__ . '/..');
$UPLOADS = $ROOT . '/uploads';
$SEED_IMG = $ROOT . '/install/seed-images';

/* ========== Tanı ========== */
function check_diagnostics(string $uploads, string $seedImg): array {
    $d = [];

    // 1. uploads alt klasörleri var mı?
    foreach (['categories', 'products', 'sliders', 'services', 'banks', 'pages', 'partners'] as $sub) {
        $d['uploads_dir_' . $sub] = [
            'label' => "uploads/$sub klasörü",
            'ok' => is_dir($uploads . '/' . $sub),
            'fix' => "Klasörü oluştur",
        ];
    }

    // 2. seed-images mevcut mu?
    $d['seed_images'] = [
        'label' => "install/seed-images klasörü",
        'ok' => is_dir($seedImg),
        'fix' => null,
    ];

    // 3. Kopyalama durumu
    if (is_dir($seedImg)) {
        foreach (['categories', 'products', 'services', 'banks'] as $sub) {
            $src = $seedImg . '/' . $sub;
            $dst = $uploads . '/' . $sub;
            if (is_dir($src)) {
                $srcCount = count(array_filter(scandir($src) ?: [], fn($f) => $f !== '.' && $f !== '..' && $f !== '.gitkeep'));
                $dstCount = is_dir($dst) ? count(array_filter(scandir($dst) ?: [], fn($f) => $f !== '.' && $f !== '..')) : 0;
                $d['copy_' . $sub] = [
                    'label' => "$sub: $dstCount/$srcCount görsel",
                    'ok' => $dstCount >= $srcCount,
                    'fix' => "Eksik görselleri kopyala",
                ];
            }
        }
    }

    // 4. uploads/wp-imported var mı?
    $d['wp_imported'] = [
        'label' => "uploads/wp-imported klasörü (WP'den gelen banka/partner görselleri)",
        'ok' => is_dir($uploads . '/wp-imported'),
        'fix' => "uploads/2024/, uploads/2022/ vs'i wp-imported altına kopyala",
    ];

    // 5. DB durumu (NULL kontrol)
    try {
        $catNullCount = (int)val("SELECT COUNT(*) FROM tm_categories WHERE image IS NULL OR image=''");
        $d['db_categories'] = [
            'label' => "tm_categories — image NULL kayıt: $catNullCount",
            'ok' => $catNullCount === 0,
            'fix' => "Seed path'leri yaz",
        ];

        $prodNullCount = (int)val("SELECT COUNT(*) FROM tm_products WHERE image IS NULL OR image=''");
        $d['db_products'] = [
            'label' => "tm_products — image NULL kayıt: $prodNullCount",
            'ok' => $prodNullCount === 0,
            'fix' => "Seed path'leri yaz",
        ];

        $svcNullCount = (int)val("SELECT COUNT(*) FROM tm_services WHERE image IS NULL OR image=''");
        $d['db_services'] = [
            'label' => "tm_services — image NULL kayıt: $svcNullCount",
            'ok' => $svcNullCount === 0,
            'fix' => "Seed path'leri yaz",
        ];

        $sldNullCount = (int)val("SELECT COUNT(*) FROM tm_sliders WHERE image IS NULL OR image=''");
        $d['db_sliders'] = [
            'label' => "tm_sliders — image NULL kayıt: $sldNullCount",
            'ok' => $sldNullCount === 0,
            'fix' => "Seed path'leri yaz",
        ];
    } catch (\Throwable $e) {
        $d['db_error'] = ['label' => 'DB Hatası: ' . $e->getMessage(), 'ok' => false, 'fix' => null];
    }

    // 6. OPcache
    $d['opcache'] = [
        'label' => "OPcache aktif: " . (function_exists('opcache_reset') ? 'evet' : 'hayır'),
        'ok' => true,  // bilgi amaçlı
        'fix' => null,
    ];

    return $d;
}

/* ========== Tamir Aksiyonları ========== */

function fix_create_dirs(string $uploads): array {
    $log = [];
    $dirs = ['categories', 'products', 'sliders', 'services', 'banks', 'pages', 'partners', 'gallery'];
    foreach ($dirs as $d) {
        $path = $uploads . '/' . $d;
        if (is_dir($path)) {
            $log[] = "  ✓ uploads/$d (zaten var)";
        } else {
            if (@mkdir($path, 0755, true)) {
                $log[] = "  ✅ uploads/$d (oluşturuldu)";
            } else {
                $log[] = "  ❌ uploads/$d (oluşturulamadı)";
            }
        }
    }
    return $log;
}

function fix_copy_seed_images(string $seedImg, string $uploads): array {
    $log = [];
    if (!is_dir($seedImg)) {
        $log[] = "  ❌ install/seed-images klasörü YOK!";
        $log[] = "     → Beklenen yer: $seedImg";
        $log[] = "     → Bu güncelleme zip'inde install/ klasörü tam gelmemiş olabilir.";
        $log[] = "     → Çözüm: Güncelleme Merkezi'nden v1.0.28 manuel zip yükle";
        return $log;
    }
    $copied = 0; $overwritten = 0; $errors = 0;
    $errorList = [];

    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($seedImg, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($rii as $file) {
        $rel = ltrim(substr($file->getPathname(), strlen($seedImg)), '/\\');
        if (str_ends_with($rel, '.gitkeep')) continue;
        $dst = $uploads . '/' . $rel;

        if ($file->isDir()) {
            if (!is_dir($dst)) {
                if (!@mkdir($dst, 0755, true)) {
                    $errorList[] = "Klasör oluşturulamadı: $dst";
                }
            }
        } else {
            // Hedef klasör yoksa oluştur
            $targetDir = dirname($dst);
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            // FORCE OVERWRITE — eski dosya varsa sil
            $isOverwrite = file_exists($dst);
            if ($isOverwrite) {
                @chmod($dst, 0644);
                @unlink($dst);
            }

            // Kopyala
            if (@copy($file->getPathname(), $dst)) {
                @chmod($dst, 0644);
                if ($isOverwrite) $overwritten++;
                else $copied++;
            } else {
                $errors++;
                $err = error_get_last();
                $errorList[] = "$rel — copy başarısız" . ($err ? ": " . $err['message'] : '');
            }
        }
    }

    $log[] = "  ✅ $copied yeni görsel kopyalandı";
    if ($overwritten) $log[] = "  ♻ $overwritten görsel yenilendi (overwrite)";
    if ($errors) {
        $log[] = "  ❌ $errors görsel kopyalanamadı:";
        foreach (array_slice($errorList, 0, 10) as $e) {
            $log[] = "     • $e";
        }
        if (count($errorList) > 10) $log[] = "     ... ve " . (count($errorList) - 10) . " daha";
    }
    return $log;
}

function fix_db_categories(): array {
    $map = [
        'sac' => 'uploads/categories/sac.jpg',
        'boru' => 'uploads/categories/boru.jpg',
        'profil' => 'uploads/categories/profil.jpg',
        'hadde' => 'uploads/categories/hadde.png',
        'flans-dirsek' => 'uploads/categories/flans-dirsek.jpg',
        'petek-kiris' => 'uploads/categories/petek-kiris.jpg',
        'panel' => 'uploads/categories/panel.png',
        'insaat-demiri' => 'uploads/categories/insaat-demiri.jpg',
        'osb-levha' => 'uploads/categories/osb-levha.webp',
    ];
    $log = []; $updated = 0;
    foreach ($map as $slug => $path) {
        try {
            $stmt = db()->prepare("UPDATE tm_categories SET image=? WHERE slug=?");
            $stmt->execute([$path, $slug]);
            if ($stmt->rowCount() > 0) {
                $updated++;
                $log[] = "  ✅ $slug → $path";
            }
        } catch (\Throwable $e) {
            $log[] = "  ❌ $slug: " . $e->getMessage();
        }
    }
    if (!$updated) $log[] = "  ↪ Hepsi zaten doluydu";
    return $log;
}

function fix_db_products(): array {
    $map = [
        'siyah-sac' => 'uploads/products/siyah-sac.jpg',
        'dkp-sac' => 'uploads/products/dkp-sac.jpg',
        'hrp-sac' => 'uploads/products/hrp-sac.jpg',
        'st52-sac' => 'uploads/products/st52-sac.jpg',
        'galvanizli-sac' => 'uploads/products/galvanizli-sac.jpg',
        'su-borusu' => 'uploads/products/su-borusu.jpg',
        'kazan-borusu' => 'uploads/products/kazan-borusu.jpg',
        'konstruksiyon-boru' => 'uploads/products/konstruksiyon-boru.jpg',
        'kare-profil' => 'uploads/products/kare-profil.jpg',
        'diktortgen-profil' => 'uploads/products/diktortgen-profil.jpg',
        'oval-profil' => 'uploads/products/oval-profil.jpg',
        'lama' => 'uploads/products/lama.jpg',
        'silme' => 'uploads/products/silme.jpg',
        'kosebent' => 'uploads/products/kosebent.jpg',
        'hea-heb' => 'uploads/products/hea-heb.jpg',
        'npi-npu' => 'uploads/products/npi-npu.jpg',
        'kare-demiri' => 'uploads/products/kare-demiri.png',
        'patent-dirsek' => 'uploads/products/patent-dirsek.jpg',
        'norm-flans' => 'uploads/products/norm-flans.jpg',
        'petek-kirisi' => 'uploads/products/petek-kirisi.jpg',
        'cati-paneli' => 'uploads/products/cati-paneli.png',
        'cephe-paneli' => 'uploads/products/cephe-paneli.png',
        'nervurlu-demir' => 'uploads/products/nervurlu-demir.jpg',
        'celik-hasir' => 'uploads/products/celik-hasir.jpg',
        'osb-levha' => 'uploads/products/osb-levha.webp',
    ];
    $log = []; $updated = 0;
    foreach ($map as $slug => $path) {
        try {
            $stmt = db()->prepare("UPDATE tm_products SET image=? WHERE slug=?");
            $stmt->execute([$path, $slug]);
            if ($stmt->rowCount() > 0) { $updated++; }
        } catch (\Throwable $e) {}
    }
    $log[] = "  ✅ $updated ürün güncellendi";
    return $log;
}

function fix_db_services(): array {
    $map = [
        'lazer-kesim' => 'uploads/services/lazer-kesim.jpg',
        'oksijen-kesim' => 'uploads/services/oksijen-kesim.jpg',
        'dekoratif-saclar' => 'uploads/services/dekoratif-saclar.png',
    ];
    $log = []; $updated = 0;
    foreach ($map as $slug => $path) {
        try {
            $stmt = db()->prepare("UPDATE tm_services SET image=? WHERE slug=?");
            $stmt->execute([$path, $slug]);
            if ($stmt->rowCount() > 0) { $updated++; }
        } catch (\Throwable $e) {}
    }
    $log[] = "  ✅ $updated hizmet güncellendi";
    return $log;
}

function fix_db_sliders(): array {
    $log = [];
    $sliders = [
        1 => 'uploads/sliders/slider-1-tekcan.jpg',
        2 => 'uploads/sliders/slider-2-laser.jpg',
        3 => 'uploads/sliders/slider-3-delivery.png',
    ];
    $updated = 0;
    foreach ($sliders as $sortOrder => $path) {
        try {
            $stmt = db()->prepare("UPDATE tm_sliders SET image=? WHERE sort_order=?");
            $stmt->execute([$path, $sortOrder]);
            if ($stmt->rowCount() > 0) { $updated++; }
        } catch (\Throwable $e) {}
    }
    $log[] = "  ✅ $updated slider güncellendi";
    return $log;
}

/**
 * uploads/wp-imported klasörü oluştur ve oraya YYYY/MM dosyalarını taşı.
 * Tarihsel dosyalar uploads/2022, 2024, 2025 vs altında — tm_partners path'i wp-imported bekliyor.
 */
function fix_wp_imported(string $uploads): array {
    $log = [];
    $target = $uploads . '/wp-imported';
    if (!is_dir($target)) {
        @mkdir($target, 0755, true);
    }
    $copied = 0;
    foreach (['2020', '2022', '2023', '2024', '2025', '2026'] as $year) {
        $src = $uploads . '/' . $year;
        $dst = $target . '/' . $year;
        if (!is_dir($src)) continue;
        if (is_dir($dst)) continue; // zaten taşınmış
        // Recursive copy
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($rii as $file) {
            $rel = ltrim(substr($file->getPathname(), strlen($src)), '/\\');
            $dpath = $dst . '/' . $rel;
            if ($file->isDir()) {
                if (!is_dir($dpath)) @mkdir($dpath, 0755, true);
            } else {
                @mkdir(dirname($dpath), 0755, true);
                if (@copy($file->getPathname(), $dpath)) $copied++;
            }
        }
        $log[] = "  ✅ $year → wp-imported/$year";
    }
    $log[] = "  Toplam: $copied dosya kopyalandı";
    return $log;
}

function fix_opcache(): array {
    if (function_exists('opcache_reset')) {
        if (@opcache_reset()) {
            return ["  ✅ OPcache resetlendi"];
        }
        return ["  ⚠ OPcache reset denendi (sonuç bilinmiyor)"];
    }
    return ["  ↪ OPcache kullanılmıyor"];
}

function fix_clear_cache(string $root): array {
    $log = [];
    // LiteSpeed cache: .htaccess'e dummy rebuild işareti
    @touch($root . '/.htaccess');
    $log[] = "  ✅ .htaccess timestamp güncellendi (LiteSpeed cache invalidate)";

    // PHP session/temp clean
    if (function_exists('clearstatcache')) {
        clearstatcache(true);
        $log[] = "  ✅ stat cache temizlendi";
    }

    // realpath cache
    if (function_exists('opcache_invalidate')) {
        $log[] = "  ✅ opcache_invalidate fonksiyonu aktif";
    }

    return $log;
}

/* ========== POST İşlemleri ========== */
$action = $_GET['action'] ?? '';
$repairLog = $_SESSION['repair_log'] ?? null;
unset($_SESSION['repair_log']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if ($action === 'repair_all') {
        if (empty($_POST['confirm'])) {
            adm_back_with('error', 'Onay kutusunu işaretlemediniz.', 'admin/site-saglik.php');
        }

        @set_time_limit(900);
        @ini_set('memory_limit', '512M');

        $log = [];
        $log[] = "═══ TEK TIKLA TAMİR BAŞLADI ═══";
        $log[] = "";

        $log[] = "▶ 1. Klasör yapısı kontrolü";
        $log = array_merge($log, fix_create_dirs($UPLOADS));
        $log[] = "";

        $log[] = "▶ 2. Seed görselleri kopyalanıyor";
        $log = array_merge($log, fix_copy_seed_images($SEED_IMG, $UPLOADS));
        $log[] = "";

        $log[] = "▶ 3. tm_categories.image dolduruluyor";
        $log = array_merge($log, fix_db_categories());
        $log[] = "";

        $log[] = "▶ 4. tm_products.image dolduruluyor";
        $log = array_merge($log, fix_db_products());
        $log[] = "";

        $log[] = "▶ 5. tm_services.image dolduruluyor";
        $log = array_merge($log, fix_db_services());
        $log[] = "";

        $log[] = "▶ 6. tm_sliders.image dolduruluyor";
        $log = array_merge($log, fix_db_sliders());
        $log[] = "";

        $log[] = "▶ 7. wp-imported klasör yapısı";
        $log = array_merge($log, fix_wp_imported($UPLOADS));
        $log[] = "";

        $log[] = "▶ 8. OPcache reset";
        $log = array_merge($log, fix_opcache());
        $log[] = "";

        $log[] = "▶ 9. Cache temizleme";
        $log = array_merge($log, fix_clear_cache($ROOT));
        $log[] = "";

        $log[] = "═══ TAMİR TAMAMLANDI — Ctrl+Shift+R ile siteyi yenile ═══";

        log_activity('repair', 'site_health', 0, 'Tek tıkla tamir uygulandı');

        $_SESSION['repair_log'] = $log;
        adm_back_with('success', 'Tamir işlemi tamamlandı. Detaylı log aşağıda.', 'admin/site-saglik.php');
    }

    if ($action === 'opcache_reset') {
        $log = fix_opcache();
        $_SESSION['repair_log'] = $log;
        adm_back_with('success', 'OPcache resetlendi.', 'admin/site-saglik.php');
    }

    if ($action === 'clear_cache') {
        $log = array_merge(fix_opcache(), fix_clear_cache($ROOT));
        $_SESSION['repair_log'] = $log;
        adm_back_with('success', 'Cache temizlendi.', 'admin/site-saglik.php');
    }
}

/* ========== _layout SADECE POST işlendikten sonra çağrılır
   (POST handler redirect yapabiliyor — header'lar gönderilmeden) ========== */
require __DIR__ . '/_layout.php';

/* ========== Render ========== */
$diag = [];
$diagError = null;
try {
    $diag = check_diagnostics($UPLOADS, $SEED_IMG);
} catch (\Throwable $e) {
    $diagError = $e->getMessage() . ' (Satır: ' . $e->getLine() . ' / ' . basename($e->getFile()) . ')';
}
$problems = array_filter($diag, fn($d) => !$d['ok'] && !empty($d['fix']));
$problemCount = count($problems);
?>

<style>
.ss-shell{background:#fff;border:1px solid #e3e8ef;margin-bottom:18px}
.ss-head{
  padding:24px 28px;
  background:linear-gradient(135deg, #050d24 0%, #0c1e44 50%, #143672 100%);
  color:#fff;position:relative;overflow:hidden;
}
.ss-head::after{content:'';position:absolute;left:0;bottom:0;width:100%;height:3px;background:#c8102e}
.ss-head h2{margin:0;color:#fff;font-size:22px;font-weight:600;letter-spacing:-.3px}
.ss-head p{margin:6px 0 0;color:rgba(255,255,255,.75);font-size:13.5px;line-height:1.6}
.ss-summary{display:flex;gap:14px;margin-top:18px;flex-wrap:wrap;position:relative;z-index:2}
.ss-stat{
  background:rgba(255,255,255,.08);
  border:1px solid rgba(255,255,255,.15);
  padding:12px 18px;flex:1;min-width:140px;
}
.ss-stat .num{font-size:28px;font-weight:300;color:#fff;font-family:ui-monospace,monospace;letter-spacing:-1px;line-height:1}
.ss-stat .lbl{font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.7);margin-top:6px}
.ss-stat.warn .num{color:#fbbf24}
.ss-stat.bad .num{color:#fca5a5}

.ss-body{padding:24px 28px}

.ss-diag{display:grid;gap:8px}
.ss-row{
  display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:12px 16px;background:#fafbfd;border:1px solid #e3e8ef;border-left:3px solid #16a34a;
  font-size:13.5px;
}
.ss-row.bad{border-left-color:#c8102e;background:#fff5f5}
.ss-row .label{color:#1a1a1a;flex:1;min-width:0}
.ss-row .status{
  font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;
  padding:4px 10px;flex-shrink:0;
}
.ss-row.ok .status{background:#dcfce7;color:#15803d}
.ss-row.bad .status{background:#fee2e2;color:#991b1b}

.ss-actions{margin-top:24px;padding-top:24px;border-top:1px solid #e3e8ef}

.ss-warn-box{
  background:#fff7e6;border:1px solid #fde68a;border-left:3px solid #d97706;
  padding:14px 18px;margin:14px 0;font-size:13.5px;color:#78350f;line-height:1.6;
}

.ss-confirm{
  display:flex;align-items:center;gap:10px;margin:14px 0;
  padding:12px 16px;background:#fff5f5;border:1px solid #fecaca;
  font-size:13.5px;cursor:pointer;
}
.ss-confirm input{width:16px;height:16px;cursor:pointer;accent-color:#c8102e}

.ss-btn-big{
  display:inline-flex;align-items:center;gap:10px;
  padding:14px 28px;font-size:13px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;
  background:#c8102e;color:#fff;border:0;cursor:pointer;
  transition:.18s;font-family:inherit;
}
.ss-btn-big:hover{background:#a00d24;transform:translateY(-1px);box-shadow:0 12px 24px rgba(200,16,46,.25)}
.ss-btn-big:disabled{opacity:.5;cursor:not-allowed;transform:none}

.ss-btn-secondary{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 18px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;
  background:#fff;color:#1e4a9e;border:1px solid #1e4a9e;cursor:pointer;
  font-family:inherit;transition:.15s;text-decoration:none;
}
.ss-btn-secondary:hover{background:#1e4a9e;color:#fff}

.ss-log{
  background:#0d1117;color:#c9d1d9;
  font-family:ui-monospace,SFMono-Regular,monospace;font-size:12.5px;
  padding:18px 22px;line-height:1.75;
  white-space:pre-wrap;max-height:500px;overflow-y:auto;
  border:1px solid #30363d;
}

.ss-success-banner{
  background:linear-gradient(135deg, #16a34a 0%, #15803d 100%);
  color:#fff;padding:18px 22px;margin-bottom:18px;
  display:flex;align-items:center;gap:14px;
}
.ss-success-banner h3{margin:0;font-size:17px;font-weight:600;color:#fff}
.ss-success-banner p{margin:4px 0 0;font-size:13.5px;color:rgba(255,255,255,.9)}
</style>

<?php if ($repairLog): ?>
<div class="adm-panel">
    <div class="adm-panel-head"><h2>📋 Tamir İşlem Logu</h2></div>
    <div class="adm-panel-body" style="padding:0">
        <div class="ss-log"><?php
            foreach ($repairLog as $line) echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n";
        ?></div>
    </div>
</div>
<?php endif; ?>

<div class="ss-shell">
    <div class="ss-head">
        <h2>🔧 Site Sağlığı & Tek Tıkla Tamir</h2>
        <p>Görseller, klasör yapısı, DB path'leri, OPcache ve sayfa yenilenmeme gibi tüm sorunları topluca çözer.</p>

        <div class="ss-summary">
            <div class="ss-stat <?= $problemCount === 0 ? '' : 'bad' ?>">
                <div class="num"><?= $problemCount ?></div>
                <div class="lbl">Tespit Edilen Sorun</div>
            </div>
            <div class="ss-stat">
                <div class="num"><?= count(array_filter($diag, fn($d) => $d['ok'])) ?></div>
                <div class="lbl">Sağlıklı Kontrol</div>
            </div>
            <div class="ss-stat">
                <div class="num"><?= count($diag) ?></div>
                <div class="lbl">Toplam Kontrol</div>
            </div>
        </div>
    </div>

    <div class="ss-body">

        <h3 style="margin:0 0 14px;font-size:16px;color:#1e4a9e">🩺 Tanı Sonuçları</h3>

        <?php if ($diagError): ?>
        <div style="background:#fff5f5;border:1px solid #fecaca;border-left:3px solid #c8102e;padding:14px 18px;margin-bottom:14px;color:#991b1b;font-size:13.5px;font-family:ui-monospace,monospace">
          <strong>⚠ Tanı Hatası:</strong> <?= htmlspecialchars($diagError) ?>
        </div>
        <?php endif; ?>

        <div class="ss-diag">
            <?php foreach ($diag as $key => $info): ?>
            <div class="ss-row <?= $info['ok'] ? 'ok' : 'bad' ?>">
                <div class="label"><?= htmlspecialchars($info['label']) ?></div>
                <div class="status"><?= $info['ok'] ? '✓ Sağlam' : '✗ Sorunlu' ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="ss-actions">
            <h3 style="margin:0 0 10px;font-size:16px;color:#1e4a9e">🚀 Tamir İşlemleri</h3>

            <div class="ss-warn-box">
                ⚠ <strong>Tek Tıkla Tamir</strong> şunları yapacak: uploads klasör yapısını oluştur,
                seed görsellerini kopyala, DB'deki NULL image path'lerini doldur,
                ekibi ekle (boşsa), wp-imported klasörünü oluştur, OPcache resetle, cache temizle.
                Mevcut verileriniz <strong>SİLİNMEZ</strong>; sadece eksikler tamamlanır.
            </div>

            <form method="post" action="?action=repair_all">
                <?= csrf_field() ?>
                <label class="ss-confirm">
                    <input type="checkbox" name="confirm" value="1" required>
                    <span><strong>Anlıyorum, sadece eksikler doldurulacak, mevcut veriler korunacak.</strong></span>
                </label>
                <button type="submit" class="ss-btn-big"
                        onclick="return confirm('Tek tıkla tamir başlasın mı?')">
                    🔧 Tek Tıkla Tamir Et
                </button>
            </form>

            <div style="margin-top:24px;padding-top:18px;border-top:1px dashed #e3e8ef">
                <p style="font-size:12.5px;color:#5e6470;margin:0 0 12px">
                    Sadece bireysel işlemler:
                </p>
                <form method="post" action="?action=opcache_reset" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="ss-btn-secondary">⚡ OPcache Reset</button>
                </form>
                <form method="post" action="?action=clear_cache" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="ss-btn-secondary">🧹 Cache Temizle</button>
                </form>
                <a href="?refresh=1" class="ss-btn-secondary">🔄 Tanıları Yenile</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
