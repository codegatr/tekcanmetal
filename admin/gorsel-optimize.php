<?php
/**
 * Görsel Optimizasyon Aracı — Tekcan Metal
 *
 * v1.0.72/v1.0.73: PageSpeed Insights raporundaki "modern resim biçimi (WebP)"
 * önerisi için kategori, slider, blog, hizmet görsellerini WebP'ye çevirir.
 *
 * Beklenen tasarruf: 600-900 KB per page → LCP'de 2-4 sn iyileşme
 */

// v1.0.74: 500 debug için — sayfa yüklenirken oluşan tüm hataları yakala
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('TM_ADMIN', true);
$adminTitle = 'Görsel Optimize (WebP)';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$messages = [];
$errors = [];

// POST: Optimize işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'optimize') {
        if (!extension_loaded('gd')) {
            $errors[] = 'GD PHP eklentisi gerekli. (Hosting yöneticinize başvurun.)';
        } else {
            $folders = [
                __DIR__ . '/../uploads/categories',
                __DIR__ . '/../uploads/sliders',
                __DIR__ . '/../uploads/services',
                __DIR__ . '/../uploads/blog',
                __DIR__ . '/../uploads/products',
                __DIR__ . '/../uploads/banks',
                __DIR__ . '/../uploads/partners',
                __DIR__ . '/../uploads/gallery',
            ];

            $stats = ['processed' => 0, 'skipped' => 0, 'savings' => 0, 'errors' => 0];
            $maxWidthMap = [
                'categories' => 800,
                'sliders'    => 1920,
                'services'   => 800,
                'blog'       => 1200,
                'products'   => 1000,
                'banks'      => 400,
                'partners'   => 400,
                'gallery'    => 1200,
            ];

            foreach ($folders as $folder) {
                if (!is_dir($folder)) continue;
                $folderName = basename($folder);
                $maxWidth = $maxWidthMap[$folderName] ?? 1000;

                $files = glob($folder . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);
                foreach ($files as $file) {
                    $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);

                    // Zaten varsa ve daha yeniyse skip
                    if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($file)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $origSize = filesize($file);

                    try {
                        $info = @getimagesize($file);
                        if (!$info) {
                            $stats['errors']++;
                            continue;
                        }

                        [$origW, $origH] = $info;
                        $type = $info[2];

                        $source = null;
                        if ($type === IMAGETYPE_JPEG) {
                            $source = @imagecreatefromjpeg($file);
                        } elseif ($type === IMAGETYPE_PNG) {
                            $source = @imagecreatefrompng($file);
                        }

                        if (!$source) {
                            $stats['errors']++;
                            continue;
                        }

                        // Resize if too large
                        if ($origW > $maxWidth) {
                            $ratio = $maxWidth / $origW;
                            $newW = $maxWidth;
                            $newH = (int)($origH * $ratio);

                            $resized = imagecreatetruecolor($newW, $newH);
                            // PNG transparency
                            if ($type === IMAGETYPE_PNG) {
                                imagealphablending($resized, false);
                                imagesavealpha($resized, true);
                                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                                imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);
                            }
                            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                            imagedestroy($source);
                            $source = $resized;
                        }

                        // WebP olarak kaydet
                        $quality = ($folderName === 'sliders' || $folderName === 'blog') ? 85 : 80;
                        if (@imagewebp($source, $webpPath, $quality)) {
                            $newSize = filesize($webpPath);
                            $stats['savings'] += ($origSize - $newSize);
                            $stats['processed']++;
                        } else {
                            $stats['errors']++;
                        }
                        imagedestroy($source);
                    } catch (Throwable $e) {
                        $stats['errors']++;
                    }
                }
            }

            $savingsKB = round($stats['savings'] / 1024, 1);
            $messages[] = "✓ {$stats['processed']} görsel WebP'ye çevrildi, ~{$savingsKB} KB tasarruf";
            if ($stats['skipped'] > 0) $messages[] = "ℹ {$stats['skipped']} görsel zaten optimize (atlandı)";
            if ($stats['errors'] > 0) $errors[] = "⚠ {$stats['errors']} görsel işlenemedi (format/izin sorunu)";
        }
    }
}

// Mevcut durum: kaç görsel var, kaçı WebP
$folderStats = [];
$rootFolders = [
    'categories' => __DIR__ . '/../uploads/categories',
    'sliders'    => __DIR__ . '/../uploads/sliders',
    'services'   => __DIR__ . '/../uploads/services',
    'blog'       => __DIR__ . '/../uploads/blog',
    'products'   => __DIR__ . '/../uploads/products',
    'banks'      => __DIR__ . '/../uploads/banks',
    'partners'   => __DIR__ . '/../uploads/partners',
    'gallery'    => __DIR__ . '/../uploads/gallery',
];
foreach ($rootFolders as $name => $path) {
    if (!is_dir($path)) continue;
    $jpgPng = count(glob($path . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE));
    $webp = count(glob($path . '/*.{webp,WEBP}', GLOB_BRACE));
    $folderStats[$name] = ['jpg_png' => $jpgPng, 'webp' => $webp];
}
?>

<style>
.go-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 24px;
    margin-bottom: 20px;
}
.go-card h2 { margin: 0 0 14px; color: #050d24; font-size: 18px; }
.go-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
    margin: 16px 0;
}
.go-stat-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px;
}
.go-stat-name { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.go-stat-value { font-size: 22px; font-weight: 700; color: #050d24; margin: 4px 0; }
.go-stat-detail { font-size: 12px; color: #6b7280; }
.go-stat-success { color: #10b981 !important; }
.go-btn-optimize {
    background: linear-gradient(135deg, #050d24 0%, #143672 100%);
    color: #fff;
    padding: 12px 24px;
    border: 0;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}
.go-btn-optimize:hover { opacity: 0.92; }
.go-btn-optimize:disabled { opacity: 0.6; cursor: wait; }
.go-msg { padding: 10px 14px; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
.go-msg-success { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
.go-msg-error { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }
.go-msg-info { background: #dbeafe; color: #1e40af; border-left: 3px solid #3b82f6; }
.go-tips li { margin-bottom: 6px; line-height: 1.55; font-size: 14px; }
.go-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.go-table th { background: #f9fafb; padding: 10px; text-align: left; border-bottom: 2px solid #e5e7eb; font-weight: 600; }
.go-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
</style>

<?php foreach ($messages as $m): ?>
    <div class="go-msg go-msg-success"><?= h($m) ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $e): ?>
    <div class="go-msg go-msg-error"><?= h($e) ?></div>
<?php endforeach; ?>

<p style="color:#6b7280;font-size:14px;margin-bottom:24px">PageSpeed Insights önerilerine göre görselleri WebP formatına çevirir, retina-uyumlu maksimum boyuta indirir. Mobil performansı 65 → 85+ hedef.</p>

<!-- Mevcut Durum -->
<div class="go-card">
    <h2>📊 Mevcut Durum</h2>
    <div class="go-stats">
        <?php foreach ($folderStats as $name => $s): ?>
            <div class="go-stat-item">
                <div class="go-stat-name"><?= h(ucfirst($name)) ?></div>
                <div class="go-stat-value"><?= $s['webp'] ?> / <?= $s['jpg_png'] + $s['webp'] ?></div>
                <div class="go-stat-detail">
                    <?php if ($s['jpg_png'] > 0): ?>
                        <?= $s['jpg_png'] ?> JPG/PNG çevrilmedi
                    <?php elseif ($s['webp'] > 0): ?>
                        <span class="go-stat-success">✓ Tümü WebP</span>
                    <?php else: ?>
                        Boş klasör
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Optimize Butonu -->
<div class="go-card">
    <h2>⚡ Toplu Optimizasyon</h2>
    <p style="color:#4b5563;font-size:14px">Tüm JPG/PNG görselleri WebP'ye dönüştürür ve aşırı büyük olanları retina-uyumlu boyutlara indirir.</p>

    <div class="go-msg go-msg-info">
        <strong>Önemli:</strong> Bu işlem mevcut JPG/PNG dosyalarını silmez, sadece yanlarına .webp versiyonu oluşturur. Picture etiketi modern tarayıcılarda WebP'yi tercih eder, eski tarayıcılar JPG/PNG fallback kullanır.
    </div>

    <form method="post" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='İşleniyor… (1-2 dakika)';">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="optimize">
        <button type="submit" class="go-btn-optimize">⚡ Tüm Görselleri Optimize Et</button>
    </form>

    <div style="margin-top:20px;padding:14px;background:#f9fafb;border-radius:8px">
        <h4 style="margin:0 0 8px;font-size:14px">İşlem detayı:</h4>
        <ul class="go-tips" style="margin:0;padding-left:18px;color:#4b5563">
            <li><strong>Maks. genişlik:</strong> categories=800px, sliders=1920px, services=800px, blog=1200px</li>
            <li><strong>Kalite:</strong> Sliders/Blog için %85, diğerleri %80</li>
            <li><strong>Format:</strong> WebP — JPG/PNG'den ortalama %30-50 daha küçük</li>
            <li><strong>PNG transparency:</strong> Korunur (logo, ikon vb.)</li>
        </ul>
    </div>
</div>

<!-- Cloudflare Önerileri -->
<div class="go-card">
    <h2>☁ Cloudflare Optimizasyon Önerileri</h2>
    <p style="color:#4b5563;font-size:14px">PageSpeed raporundaki <code>email-decode.min.js</code> kaynağı Cloudflare otomatik koruma scriptidir. 341 ms ek yükleme yaratır. Kapatmak güvenlidir:</p>

    <ol class="go-tips" style="padding-left:20px">
        <li>Cloudflare Dashboard → <strong>tekcanmetal.com</strong> seçin</li>
        <li>Sol menü → <strong>Scrape Shield</strong></li>
        <li><strong>Email Address Obfuscation</strong> → <code>OFF</code></li>
    </ol>

    <h3 style="margin-top:20px;font-size:15px">🚀 Diğer Performans Ayarları</h3>
    <ul class="go-tips" style="padding-left:20px">
        <li><strong>Speed → Optimization → Auto Minify:</strong> JS ✓ + CSS ✓ + HTML ✓</li>
        <li><strong>Speed → Optimization → Brotli:</strong> ON</li>
        <li><strong>Speed → Optimization → Early Hints:</strong> ON</li>
        <li><strong>Caching → Configuration → Browser Cache TTL:</strong> 4 saat → 1 ay</li>
    </ul>
</div>

<!-- Beklenen İyileşme -->
<div class="go-card">
    <h2>📈 Beklenen İyileşme</h2>
    <table class="go-table">
        <thead>
            <tr>
                <th>Metrik</th>
                <th style="text-align:center">Önce (Mobil)</th>
                <th style="text-align:center">Sonra (Hedef)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Performans skoru</td>
                <td style="text-align:center;color:#f59e0b;font-weight:700">65</td>
                <td style="text-align:center;color:#10b981;font-weight:700">85+</td></tr>
            <tr><td>LCP (Largest Contentful Paint)</td>
                <td style="text-align:center">8.4 sn</td>
                <td style="text-align:center;color:#10b981">2.5 sn</td></tr>
            <tr><td>FCP (First Contentful Paint)</td>
                <td style="text-align:center">3.5 sn</td>
                <td style="text-align:center;color:#10b981">1.2 sn</td></tr>
            <tr><td>Speed Index</td>
                <td style="text-align:center">5.0 sn</td>
                <td style="text-align:center;color:#10b981">2.0 sn</td></tr>
            <tr><td>Görsel boyutu (per page)</td>
                <td style="text-align:center">~1.4 MB</td>
                <td style="text-align:center;color:#10b981">~600 KB</td></tr>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
