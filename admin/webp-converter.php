<?php
/**
 * WebP Görsel Dönüştürücü — Tekcan Metal Admin
 *
 * /uploads/ klasöründeki tüm JPG/PNG görselleri WebP versiyonuna dönüştürür.
 * .htaccess kuralı sayesinde tarayıcı WebP destekliyorsa otomatik servis edilir
 * (URL aynı kalır — image.jpg → image.jpg.webp transparent olarak gönderilir).
 *
 * Tasarruf: PageSpeed Insights raporuna göre ~666 KiB (mobil), 931 KiB (masaüstü)
 *
 * v1.0.72'de eklendi.
 */
define('TM_ADMIN', true);
$adminTitle = 'WebP Görsel Dönüştürücü';
require __DIR__ . '/_layout.php';

// Kontrol — GD kütüphanesi WebP destekliyor mu?
$gdInfo = function_exists('gd_info') ? gd_info() : [];
$webpSupport = !empty($gdInfo['WebP Support']);

// İstatistikleri topla
function tm_collect_image_stats(string $dir, array &$stats): void {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            tm_collect_image_stats($path, $stats);
        } elseif (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $stats['total']++;
                $stats['total_size'] += filesize($path);
                if (file_exists($path . '.webp')) {
                    $stats['converted']++;
                    $stats['webp_size'] += filesize($path . '.webp');
                } else {
                    $stats['pending']++;
                }
            }
        }
    }
}

function tm_convert_to_webp(string $sourcePath, int $quality = 82): array {
    if (!file_exists($sourcePath)) {
        return ['ok' => false, 'message' => 'Dosya yok'];
    }

    $targetPath = $sourcePath . '.webp';
    if (file_exists($targetPath)) {
        return ['ok' => true, 'skipped' => true, 'message' => 'Zaten mevcut'];
    }

    $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

    try {
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $img = @imagecreatefromjpeg($sourcePath);
        } elseif ($ext === 'png') {
            $img = @imagecreatefrompng($sourcePath);
            if ($img !== false) {
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
            }
        } else {
            return ['ok' => false, 'message' => 'Desteklenmeyen format'];
        }

        if ($img === false) {
            return ['ok' => false, 'message' => 'Görsel açılamadı'];
        }

        $result = imagewebp($img, $targetPath, $quality);
        imagedestroy($img);

        if ($result) {
            $orig = filesize($sourcePath);
            $new = filesize($targetPath);
            $saved = $orig - $new;
            return [
                'ok' => true,
                'message' => sprintf('OK (orig %s → webp %s, %s tasarruf)',
                    tm_format_size($orig),
                    tm_format_size($new),
                    tm_format_size($saved)
                ),
                'saved' => $saved
            ];
        }
        return ['ok' => false, 'message' => 'WebP yazma hatası'];

    } catch (Throwable $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function tm_format_size(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / (1024 * 1024), 2) . ' MB';
}

function tm_walk_and_convert(string $dir, int $quality, array &$report, int $limit = 0): void {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if ($limit > 0 && $report['processed'] >= $limit) return;

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            tm_walk_and_convert($path, $quality, $report, $limit);
        } elseif (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                if (file_exists($path . '.webp')) continue;
                $r = tm_convert_to_webp($path, $quality);
                $report['processed']++;
                if ($r['ok']) {
                    if (!empty($r['skipped'])) {
                        $report['skipped']++;
                    } else {
                        $report['success']++;
                        $report['total_saved'] += ($r['saved'] ?? 0);
                    }
                } else {
                    $report['errors']++;
                    $report['error_log'][] = ['path' => $path, 'msg' => $r['message']];
                }
            }
        }
    }
}

// ============ POST Aksiyonu ============
$convertReport = null;
if (($_POST['action'] ?? '') === 'convert_all' && $webpSupport) {
    @set_time_limit(180);
    @ini_set('memory_limit', '256M');

    $quality = max(60, min(95, (int)($_POST['quality'] ?? 82)));
    $limit = max(0, min(500, (int)($_POST['limit'] ?? 50)));

    $convertReport = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => 0,
        'total_saved' => 0,
        'error_log' => [],
        'started' => microtime(true),
    ];

    $uploadsDir = realpath(__DIR__ . '/../uploads');
    if ($uploadsDir) {
        tm_walk_and_convert($uploadsDir, $quality, $convertReport, $limit);
    }

    // Logo da dahil
    $assetsDir = realpath(__DIR__ . '/../assets/img');
    if ($assetsDir && $convertReport['processed'] < $limit) {
        tm_walk_and_convert($assetsDir, $quality, $convertReport, $limit);
    }

    $convertReport['duration'] = round(microtime(true) - $convertReport['started'], 2);
}

// İstatistikleri topla (her sayfa yüklemede)
$stats = [
    'total' => 0,
    'converted' => 0,
    'pending' => 0,
    'total_size' => 0,
    'webp_size' => 0,
];

$uploadsDir = realpath(__DIR__ . '/../uploads');
if ($uploadsDir) tm_collect_image_stats($uploadsDir, $stats);

$assetsDir = realpath(__DIR__ . '/../assets/img');
if ($assetsDir) tm_collect_image_stats($assetsDir, $stats);

$convertedPct = $stats['total'] > 0 ? round(($stats['converted'] / $stats['total']) * 100) : 0;
?>

<style>
.webp-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin:20px 0; }
.webp-card {
  background:#fff;
  border:1px solid #e5e7eb;
  border-radius:8px;
  padding:18px;
  text-align:center;
}
.webp-stat { font-size:30px; font-weight:700; color:#0c1e44; line-height:1; margin:6px 0 4px; }
.webp-stat-label { color:#64748b; font-size:12px; }
.webp-stat.green { color:#16a34a; }
.webp-stat.amber { color:#d97706; }

.webp-progress {
  background:#f3f4f6;
  border-radius:6px;
  height:24px;
  overflow:hidden;
  margin:8px 0;
}
.webp-progress-bar {
  background:linear-gradient(90deg, #16a34a, #22c55e);
  height:100%;
  transition:width .4s;
  display:flex;
  align-items:center;
  justify-content:flex-end;
  padding-right:10px;
  color:#fff;
  font-weight:600;
  font-size:12px;
}

.webp-action {
  background:#fafaf7;
  border:1px solid #e3e0d8;
  border-radius:8px;
  padding:20px;
  margin:16px 0;
}
.webp-action h2 { margin:0 0 12px; font-size:18px; color:#0c1e44; }

.webp-button {
  background:#16a34a;
  color:#fff;
  border:none;
  padding:14px 28px;
  border-radius:6px;
  font-weight:600;
  cursor:pointer;
  font-size:14px;
  transition:background .2s;
}
.webp-button:hover { background:#15803d; }
.webp-button:disabled { background:#9ca3af; cursor:not-allowed; }

.webp-form-row {
  display:flex;
  gap:14px;
  align-items:end;
  flex-wrap:wrap;
  margin:16px 0;
}
.webp-form-row label { display:flex; flex-direction:column; font-size:12px; color:#64748b; gap:6px; }
.webp-form-row input[type="number"] {
  border:1px solid #d1d5db;
  border-radius:6px;
  padding:8px 12px;
  width:120px;
}

.webp-result {
  background:#ecfdf5;
  border:1px solid #6ee7b7;
  border-radius:8px;
  padding:20px;
  margin:16px 0;
}
.webp-result.error { background:#fef2f2; border-color:#fca5a5; }
.webp-result h3 { margin:0 0 10px; color:#065f46; font-size:16px; }
.webp-result.error h3 { color:#991b1b; }
.webp-result table { width:100%; margin-top:10px; font-size:13px; }
.webp-result table td { padding:4px 10px; }

.webp-warning {
  background:#fef3c7;
  border:1px solid #fcd34d;
  border-radius:8px;
  padding:16px;
  margin:16px 0;
  color:#92400e;
}
</style>

<h1>🖼️ WebP Görsel Dönüştürücü</h1>
<p style="color:#64748b">PageSpeed Insights mobil performansı için kritik. JPG/PNG görseller WebP versiyona çevrilir, dosya boyutu %30-70 azalır. Aynı URL ile servis edilir (transparent serve via .htaccess).</p>

<?php if (!$webpSupport): ?>
<div class="webp-warning">
  <strong>⚠ GD WebP desteği bulunamadı</strong><br>
  PHP'nin GD kütüphanesi WebP desteği olmadan derlenmiş. Hosting sağlayıcınıza başvurun veya
  <code>--with-webp</code> seçeneğiyle derlenmiş bir PHP versiyonu kullanın.
  <br><br>
  Mevcut durumu test: <code>php -r "var_dump(gd_info());"</code>
</div>
<?php endif; ?>

<?php if ($convertReport): ?>
<div class="webp-result <?= $convertReport['errors'] > 0 ? 'error' : '' ?>">
  <h3>
    <?= $convertReport['success'] > 0 ? '✅' : ($convertReport['errors'] > 0 ? '⚠️' : 'ℹ️') ?>
    Dönüşüm Tamamlandı (<?= h($convertReport['duration']) ?> sn)
  </h3>
  <table>
    <tr>
      <td><strong>İşlenen toplam:</strong></td>
      <td><?= $convertReport['processed'] ?></td>
    </tr>
    <tr>
      <td><strong>Başarılı:</strong></td>
      <td style="color:#065f46;font-weight:600"><?= $convertReport['success'] ?></td>
    </tr>
    <tr>
      <td><strong>Atlanan (zaten var):</strong></td>
      <td><?= $convertReport['skipped'] ?></td>
    </tr>
    <tr>
      <td><strong>Hata:</strong></td>
      <td style="color:#991b1b;font-weight:600"><?= $convertReport['errors'] ?></td>
    </tr>
    <tr>
      <td><strong>Toplam Tasarruf:</strong></td>
      <td style="color:#065f46;font-weight:600"><?= h(tm_format_size($convertReport['total_saved'])) ?></td>
    </tr>
  </table>

  <?php if (!empty($convertReport['error_log'])): ?>
  <details style="margin-top:12px">
    <summary style="cursor:pointer;font-weight:600">Hata detayları (<?= count($convertReport['error_log']) ?>)</summary>
    <pre style="background:#fff;padding:10px;border-radius:6px;font-size:11px;overflow:auto;max-height:200px"><?php
      foreach ($convertReport['error_log'] as $err) {
          echo h(basename($err['path'])) . ': ' . h($err['msg']) . "\n";
      }
    ?></pre>
  </details>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="webp-grid">
  <div class="webp-card">
    <div class="webp-stat-label">Toplam JPG/PNG</div>
    <div class="webp-stat"><?= $stats['total'] ?></div>
  </div>
  <div class="webp-card">
    <div class="webp-stat-label">WebP Üretildi</div>
    <div class="webp-stat green"><?= $stats['converted'] ?></div>
  </div>
  <div class="webp-card">
    <div class="webp-stat-label">Bekliyor</div>
    <div class="webp-stat amber"><?= $stats['pending'] ?></div>
  </div>
  <div class="webp-card">
    <div class="webp-stat-label">Orjinal Boyut</div>
    <div class="webp-stat"><?= h(tm_format_size($stats['total_size'])) ?></div>
  </div>
  <div class="webp-card">
    <div class="webp-stat-label">WebP Boyut</div>
    <div class="webp-stat green"><?= h(tm_format_size($stats['webp_size'])) ?></div>
  </div>
  <div class="webp-card">
    <div class="webp-stat-label">Tasarruf</div>
    <div class="webp-stat green">
      <?php if ($stats['total_size'] > 0 && $stats['webp_size'] > 0): ?>
        <?= round((1 - $stats['webp_size'] / max(1, $stats['total_size'])) * 100) ?>%
      <?php else: ?>—<?php endif; ?>
    </div>
  </div>
</div>

<div class="webp-progress">
  <div class="webp-progress-bar" style="width:<?= $convertedPct ?>%">
    <?= $convertedPct ?>%
  </div>
</div>

<div class="webp-action">
  <h2>🚀 Toplu Dönüşüm</h2>
  <p>Tüm <code>/uploads/</code> ve <code>/assets/img/</code> klasöründeki JPG/PNG görselleri WebP versiyonu üretir.</p>

  <form method="POST">
    <input type="hidden" name="action" value="convert_all">

    <div class="webp-form-row">
      <label>
        Kalite (60-95)
        <input type="number" name="quality" value="82" min="60" max="95">
      </label>
      <label>
        Limit (max görsel/seans)
        <input type="number" name="limit" value="50" min="1" max="500">
      </label>
      <button type="submit" class="webp-button" <?= $webpSupport ? '' : 'disabled' ?>>
        ⚡ Dönüşümü Başlat
      </button>
    </div>
  </form>

  <p style="font-size:12px;color:#64748b;margin-top:16px">
    💡 <strong>İpucu:</strong> Büyük sitelerde 50'şer 50'şer çalıştırın (timeout riski). Toplam <?= $stats['pending'] ?> görsel beklerken,
    <?= ceil($stats['pending'] / 50) ?> kez tıklamak gerekebilir. Kalite 82 mobilde idealdir.
  </p>
</div>

<div class="webp-action" style="background:#dbeafe;border-color:#93c5fd">
  <h2>📋 .htaccess Otomatik Servis</h2>
  <p>v1.0.72 ile eklenen <code>.htaccess</code> kuralı sayesinde, tarayıcı WebP destekliyorsa <code>foo.jpg</code> isteği otomatik <code>foo.jpg.webp</code> dosyasına yönlendirilir. <strong>HTML değiştirmeniz gerekmez.</strong></p>

  <details style="margin-top:12px">
    <summary style="cursor:pointer;font-weight:600">Test Edin (canlıda)</summary>
    <ol style="margin:10px 0 0 20px">
      <li>Chrome/Edge ile <a href="<?= h(rtrim(settings('site_url', 'https://tekcanmetal.com'), '/')) ?>" target="_blank">anasayfayı</a> açın</li>
      <li>F12 → Network sekmesi</li>
      <li>Bir görsel istek satırına tıklayın</li>
      <li><strong>Response Headers</strong>'ta <code>content-type: image/webp</code> görmelisiniz</li>
    </ol>
  </details>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
