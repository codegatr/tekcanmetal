<?php
/**
 * Görsel Optimizasyon Aracı — Tekcan Metal
 *
 * v1.0.72: PageSpeed Insights raporunda "modern resim biçimi (WebP)" önerisi
 * için kategori, slider, blog kapakları ve hizmet görsellerini WebP'ye çevirir.
 *
 * Beklenen tasarruf: 600-900 KB per page → LCP'de 2-4 sn iyileşme
 */
require_once __DIR__ . '/_helpers.php';
adm_require_login();

$action = $_POST['action'] ?? '';
$messages = [];
$errors = [];

if ($action === 'optimize') {
    if (!extension_loaded('gd') && !extension_loaded('imagick')) {
        $errors[] = 'GD veya Imagick PHP eklentisi gerekli.';
    } else {
        // İşlenecek klasörler
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
            'categories' => 800,   // Görüntülenen 422-803px → 2x retina için 800
            'sliders'    => 1920,  // Hero slider full-width
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
                    // GD ile dönüştür
                    $info = @getimagesize($file);
                    if (!$info) {
                        $stats['errors']++;
                        continue;
                    }

                    [$origW, $origH] = $info;
                    $type = $info[2];

                    // Source image yükle
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

                    // Boyut hesapla — eğer çok büyükse küçült
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

require __DIR__ . '/_header.php';
?>

<style>
.opt-tool { max-width: 900px; margin: 0 auto; }
.opt-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 28px;
    margin-bottom: 24px;
}
.opt-card h2 { margin: 0 0 16px; color: #050d24; }
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin: 20px 0;
}
.stats-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
}
.stats-name { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.stats-value { font-size: 24px; font-weight: 700; color: #050d24; margin: 4px 0; }
.stats-detail { font-size: 13px; color: #6b7280; }
.btn-optimize {
    background: linear-gradient(135deg, #050d24 0%, #143672 100%);
    color: #fff;
    padding: 14px 28px;
    border: 0;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
}
.btn-optimize:hover { background: linear-gradient(135deg, #0c1e44 0%, #143672 100%); }
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 12px; }
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
.alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
.tips-list li { margin-bottom: 8px; line-height: 1.6; }
</style>

<div class="opt-tool">
    <h1>🚀 Görsel Optimizasyonu</h1>
    <p>PageSpeed Insights önerilerine göre görselleri WebP formatına çevirir, retina-uyumlu maksimum boyuta indirir.</p>

    <?php foreach ($messages as $m): ?>
        <div class="alert alert-success"><?= h($m) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <!-- Mevcut Durum -->
    <div class="opt-card">
        <h2>📊 Mevcut Durum</h2>
        <div class="stats-grid">
            <?php foreach ($folderStats as $name => $s): ?>
                <div class="stats-item">
                    <div class="stats-name"><?= h(ucfirst($name)) ?></div>
                    <div class="stats-value"><?= $s['webp'] ?> / <?= $s['jpg_png'] + $s['webp'] ?></div>
                    <div class="stats-detail">
                        <?php if ($s['jpg_png'] > 0): ?>
                            <?= $s['jpg_png'] ?> JPG/PNG çevrilmedi
                        <?php else: ?>
                            <span style="color: #10b981;">✓ Tümü WebP</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Optimize Butonu -->
    <div class="opt-card">
        <h2>⚡ Toplu Optimizasyon</h2>
        <p>Tüm JPG/PNG görselleri WebP'ye dönüştürür ve aşırı büyük olanları retina-uyumlu boyutlara indirir.</p>

        <div class="alert alert-info">
            <strong>Önemli:</strong> Bu işlem mevcut JPG/PNG dosyalarını <em>silmez</em>, sadece yanlarına .webp versiyonu oluşturur. Picture etiketi otomatik olarak modern tarayıcılarda WebP'yi tercih eder.
        </div>

        <form method="post" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='İşleniyor… (1-2 dakika sürebilir)'">
            <input type="hidden" name="action" value="optimize">
            <button type="submit" class="btn-optimize">⚡ Tüm Görselleri Optimize Et</button>
        </form>

        <div style="margin-top:24px;padding:16px;background:#f9fafb;border-radius:8px">
            <h4 style="margin:0 0 8px">İşlem detayı:</h4>
            <ul class="tips-list" style="margin:0;padding-left:20px;color:#4b5563;font-size:14px">
                <li><strong>Maks. genişlik:</strong> categories=800px, sliders=1920px, services=800px, blog=1200px</li>
                <li><strong>Kalite:</strong> Sliders/Blog için %85, diğerleri %80 (görsel olarak fark edilemez)</li>
                <li><strong>Format:</strong> WebP — JPG/PNG'den ortalama %30-50 daha küçük</li>
                <li><strong>PNG transparency:</strong> Korunur (logo, ikon vb.)</li>
            </ul>
        </div>
    </div>

    <!-- Cloudflare Önerileri -->
    <div class="opt-card">
        <h2>☁ Cloudflare Optimizasyon Önerileri</h2>
        <p>PageSpeed Insights raporundaki <strong>email-decode.min.js</strong> kaynağı Cloudflare otomatik koruma scriptidir. 341 ms ek yükleme yaratır. <strong>Kapatmak güvenlidir</strong>:</p>

        <ol class="tips-list">
            <li>Cloudflare Dashboard → <strong>tekcanmetal.com</strong> seçin</li>
            <li>Sol menü → <strong>Scrape Shield</strong></li>
            <li><strong>Email Address Obfuscation</strong> → <code>OFF</code></li>
        </ol>

        <p>Bunun yerine bizim PHP <code>email-encode</code> fonksiyonumuz zaten e-posta adreslerini koruyor.</p>

        <h3 style="margin-top:24px">🚀 Diğer Cloudflare Performans Ayarları</h3>
        <ul class="tips-list">
            <li><strong>Speed → Optimization → Auto Minify:</strong> JavaScript ✓ + CSS ✓ + HTML ✓</li>
            <li><strong>Speed → Optimization → Brotli:</strong> ON</li>
            <li><strong>Speed → Optimization → Early Hints:</strong> ON</li>
            <li><strong>Caching → Configuration → Browser Cache TTL:</strong> 4 saat → <strong>1 ay</strong></li>
            <li><strong>Speed → Optimization → Image Resizing</strong> (Pro plan) — otomatik WebP servisi</li>
        </ul>
    </div>

    <!-- PageSpeed Sonrası -->
    <div class="opt-card">
        <h2>📈 Beklenen İyileşme</h2>
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px;text-align:left;border-bottom:2px solid #e5e7eb">Metrik</th>
                    <th style="padding:10px;text-align:center;border-bottom:2px solid #e5e7eb">Önce (Mobil)</th>
                    <th style="padding:10px;text-align:center;border-bottom:2px solid #e5e7eb">Sonra (Hedef)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb">Performans</td>
                    <td style="padding:10px;text-align:center;color:#f59e0b;font-weight:700">65</td>
                    <td style="padding:10px;text-align:center;color:#10b981;font-weight:700">85+</td></tr>
                <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb">LCP (Largest Contentful Paint)</td>
                    <td style="padding:10px;text-align:center">8.4 sn</td>
                    <td style="padding:10px;text-align:center;color:#10b981">2.5 sn</td></tr>
                <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb">FCP (First Contentful Paint)</td>
                    <td style="padding:10px;text-align:center">3.5 sn</td>
                    <td style="padding:10px;text-align:center;color:#10b981">1.2 sn</td></tr>
                <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb">Speed Index</td>
                    <td style="padding:10px;text-align:center">5.0 sn</td>
                    <td style="padding:10px;text-align:center;color:#10b981">2.0 sn</td></tr>
                <tr><td style="padding:10px;border-bottom:1px solid #e5e7eb">Görsel boyutu (per page)</td>
                    <td style="padding:10px;text-align:center">~1.4 MB</td>
                    <td style="padding:10px;text-align:center;color:#10b981">~600 KB</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
