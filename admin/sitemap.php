<?php
define('TM_ADMIN', true);
$adminTitle = 'Site Haritası Yönetimi';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

/* ========== POST: Sitemap'i Test Et / Yenile ========== */
$testResult = null;
$pingResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $action = $_POST['_action'] ?? '';

    // SITEMAP'i FETCH ET ve istatistik çıkar
    if ($action === 'test') {
        $sitemapUrl = url('sitemap.xml');
        $start = microtime(true);
        $ctx = stream_context_create(['http' => ['timeout' => 15, 'user_agent' => 'Tekcan Admin Sitemap Test']]);
        $xml = @file_get_contents($sitemapUrl, false, $ctx);
        $duration = round((microtime(true) - $start) * 1000);

        if ($xml === false) {
            $testResult = ['ok' => false, 'msg' => 'Sitemap okunamadı: ' . $sitemapUrl, 'time' => $duration];
        } else {
            $size = strlen($xml);
            $urlCount = substr_count($xml, '<loc>');
            $imageCount = substr_count($xml, '<image:image>');
            $hreflangCount = substr_count($xml, 'hreflang=');

            // XML parse kontrolü
            libxml_use_internal_errors(true);
            $parsed = simplexml_load_string($xml);
            $isValid = $parsed !== false;
            $error = $isValid ? null : (libxml_get_last_error() ? libxml_get_last_error()->message : 'XML parse hatası');

            $testResult = [
                'ok' => $isValid,
                'msg' => $isValid ? 'Sitemap geçerli ve erişilebilir.' : 'XML hata: ' . $error,
                'time' => $duration,
                'size' => $size,
                'urls' => $urlCount,
                'images' => $imageCount,
                'hreflang' => $hreflangCount,
                'url' => $sitemapUrl,
            ];
        }
    }

    // SITEMAP'i SEARCH CONSOLE ÖNBELLEĞİNİ TEMİZLE (bilgi)
    if ($action === 'refresh_info') {
        $pingResult = [
            'ok' => true,
            'msg' => 'Sitemap dinamik olarak çalışır — her istekte güncel veriyi döner. Sadece Search Console\'a tekrar göndermeniz yeterli.',
        ];
    }
}

/* ========== STATS — Veritabanından URL kategori sayıları ========== */
function _sm_count(string $sql): int {
    $r = row($sql);
    return $r ? (int) $r['cnt'] : 0;
}

$stats = [
    'static_pages'    => 8,  // ana, hakkimizda, urunler, hizmetler, blog, iletisim, fiyat-listeleri, sss
    'categories'      => _sm_count("SELECT COUNT(*) AS cnt FROM tm_categories WHERE is_active=1"),
    'products'        => _sm_count("SELECT COUNT(*) AS cnt FROM tm_products WHERE is_active=1"),
    'services'        => _sm_count("SELECT COUNT(*) AS cnt FROM tm_services WHERE is_active=1"),
    'blog_posts'      => _sm_count("SELECT COUNT(*) AS cnt FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL"),
    'seo_iller'       => _sm_count("SELECT COUNT(*) AS cnt FROM tm_seo_iller WHERE is_active=1"),
    'seo_ulkeler'     => _sm_count("SELECT COUNT(*) AS cnt FROM tm_seo_ulkeler WHERE is_active=1"),
    'gallery'         => _sm_count("SELECT COUNT(*) AS cnt FROM tm_gallery_albums WHERE is_active=1"),
    'pages'           => _sm_count("SELECT COUNT(*) AS cnt FROM tm_pages WHERE is_active=1"),
    'featured_products' => _sm_count("SELECT COUNT(*) AS cnt FROM tm_products WHERE is_active=1 AND is_featured=1"),
];

// Toplam URL = (her bölüm × 4 dil) + il-urun kombinasyonları
$LANGS_COUNT = 4;
$totalUrls = 0;
foreach ($stats as $k => $v) $totalUrls += $v * $LANGS_COUNT;

// İl × öne çıkan ürün kombinasyonları (il-urun.php URL'leri)
$ilUrunKombo = $stats['seo_iller'] * $stats['featured_products'] * $LANGS_COUNT;
$totalUrls += $ilUrunKombo;

$lastSettings = settings('sitemap_last_check', '');
?>

<style>
.sm-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:24px; }
.sm-stat { background:#fff; border:1px solid #e5e7eb; border-left:4px solid #c8102e; padding:18px 20px; }
.sm-stat .lbl { font-size:11px; font-weight:600; letter-spacing:1.2px; text-transform:uppercase; color:#6b7280; margin-bottom:6px; }
.sm-stat .val { font-family:'Cormorant Garamond', Georgia, serif; font-size:32px; font-weight:600; color:#0c1e44; line-height:1; }
.sm-stat .sub { font-size:12px; color:#9ca3af; margin-top:4px; }
.sm-stat.featured { border-left-color:#c9a86b; background:linear-gradient(135deg, #fff 0%, #fafaf7 100%); }
.sm-stat.featured .val { color:#c8102e; font-size:36px; }

.sm-section { background:#fff; border:1px solid #e5e7eb; padding:24px; margin-bottom:20px; }
.sm-section h3 { font-family:'Cormorant Garamond', Georgia, serif; font-size:22px; color:#0c1e44; margin:0 0 16px; padding-bottom:12px; border-bottom:1px solid #e5e7eb; }
.sm-section h3 .icon { color:#c9a86b; margin-right:8px; }

.sm-row { display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:14px; }
.sm-row label { font-size:13px; color:#6b7280; min-width:140px; }
.sm-row .val { font-family:'JetBrains Mono', monospace; font-size:13px; color:#0c1e44; word-break:break-all; }
.sm-row .val a { color:#143672; text-decoration:none; border-bottom:1px solid #c9a86b; }

.sm-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:16px; }
.sm-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; font-size:13px; font-weight:600; border-radius:6px; cursor:pointer; text-decoration:none; transition:all 0.15s; border:0; font-family:inherit; }
.sm-btn-primary { background:#0c1e44; color:#fff; }
.sm-btn-primary:hover { background:#143672; }
.sm-btn-gold { background:#c9a86b; color:#fff; }
.sm-btn-gold:hover { background:#a88a4a; }
.sm-btn-ghost { background:#fff; color:#0c1e44; border:1px solid #d1d5db; }
.sm-btn-ghost:hover { background:#f3f4f6; }

.sm-result { background:#ecfdf5; border-left:3px solid #047857; padding:14px 18px; margin-bottom:16px; font-size:14px; color:#064e3b; }
.sm-result.error { background:#fef2f2; border-color:#dc2626; color:#7f1d1d; }
.sm-result-stats { display:flex; gap:24px; margin-top:10px; flex-wrap:wrap; }
.sm-result-stats span { font-family:'JetBrains Mono', monospace; font-size:12px; }
.sm-result-stats strong { color:#0c1e44; }

.sm-guide { background:#fafaf7; border:1px dashed #c9a86b; padding:18px 20px; margin-top:14px; }
.sm-guide ol { margin:0; padding-left:20px; }
.sm-guide ol li { font-size:14px; color:#374151; line-height:1.7; padding:4px 0; }
.sm-guide ol li strong { color:#0c1e44; }
.sm-guide code { background:#fff; border:1px solid #e3e0d8; padding:2px 8px; font-family:'JetBrains Mono', monospace; font-size:12px; border-radius:3px; color:#c8102e; }
.sm-guide a { color:#143672; font-weight:600; }

.sm-link-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#0c1e44; color:#fff; text-decoration:none; font-size:12px; font-weight:600; border-radius:4px; margin-left:8px; }
.sm-link-btn:hover { background:#143672; color:#fff; }
</style>

<?php if ($testResult): ?>
<div class="sm-result <?= $testResult['ok'] ? '' : 'error' ?>">
    <strong><?= $testResult['ok'] ? '✓' : '✗' ?> <?= h($testResult['msg']) ?></strong>
    <?php if ($testResult['ok']): ?>
        <div class="sm-result-stats">
            <span>📦 Boyut: <strong><?= number_format($testResult['size'] / 1024, 1) ?> KB</strong></span>
            <span>🔗 URL sayısı: <strong><?= number_format($testResult['urls']) ?></strong></span>
            <span>🌐 hreflang: <strong><?= number_format($testResult['hreflang']) ?></strong></span>
            <span>⏱ Yanıt: <strong><?= $testResult['time'] ?>ms</strong></span>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($pingResult): ?>
<div class="sm-result"><strong>ℹ <?= h($pingResult['msg']) ?></strong></div>
<?php endif; ?>

<!-- TOPLAM ÖZET -->
<div class="sm-grid">
    <div class="sm-stat featured">
        <div class="lbl">Toplam URL Tahmini</div>
        <div class="val"><?= number_format($totalUrls) ?></div>
        <div class="sub">4 dilde (TR + EN + AR + RU)</div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Statik Sayfa</div>
        <div class="val"><?= $stats['static_pages'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['static_pages'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Kategoriler</div>
        <div class="val"><?= $stats['categories'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['categories'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Ürünler</div>
        <div class="val"><?= $stats['products'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['products'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Hizmetler</div>
        <div class="val"><?= $stats['services'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['services'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Blog Yazıları</div>
        <div class="val"><?= $stats['blog_posts'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['blog_posts'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">SEO İl Sayfaları</div>
        <div class="val"><?= $stats['seo_iller'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['seo_iller'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">SEO Ülke Sayfaları</div>
        <div class="val"><?= $stats['seo_ulkeler'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['seo_ulkeler'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">İl × Ürün</div>
        <div class="val"><?= number_format($ilUrunKombo) ?></div>
        <div class="sub"><?= $stats['seo_iller'] ?> il × <?= $stats['featured_products'] ?> ürün × 4 dil</div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Galeri Albümleri</div>
        <div class="val"><?= $stats['gallery'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['gallery'] * 4 ?></div>
    </div>
    <div class="sm-stat">
        <div class="lbl">Özel Sayfalar</div>
        <div class="val"><?= $stats['pages'] ?></div>
        <div class="sub">× 4 dil = <?= $stats['pages'] * 4 ?></div>
    </div>
</div>

<!-- SITEMAP URL ve TEST -->
<div class="sm-section">
    <h3><span class="icon">🗺</span>Sitemap Bilgileri</h3>

    <div class="sm-row">
        <label>Sitemap URL:</label>
        <span class="val"><a href="<?= h(url('sitemap.xml')) ?>" target="_blank"><?= h(url('sitemap.xml')) ?></a>
            <a href="<?= h(url('sitemap.xml')) ?>" target="_blank" class="sm-link-btn">↗ Aç</a></span>
    </div>
    <div class="sm-row">
        <label>robots.txt:</label>
        <span class="val"><a href="<?= h(url('robots.txt')) ?>" target="_blank"><?= h(url('robots.txt')) ?></a>
            <a href="<?= h(url('robots.txt')) ?>" target="_blank" class="sm-link-btn">↗ Aç</a></span>
    </div>
    <div class="sm-row">
        <label>Mod:</label>
        <span class="val">📡 Dinamik (her istekte DB'den freş veri)</span>
    </div>
    <div class="sm-row">
        <label>Multi-dil:</label>
        <span class="val">✓ TR + EN + AR + RU (hreflang etiketli)</span>
    </div>

    <form method="post" class="sm-actions">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="_action" value="test">
        <button type="submit" class="sm-btn sm-btn-primary">🔄 Sitemap'i Test Et + İstatistikleri Çıkar</button>
        <a href="<?= h(url('sitemap.xml')) ?>" target="_blank" class="sm-btn sm-btn-ghost">👁 Sitemap'i Görüntüle</a>
    </form>
</div>

<!-- GOOGLE SEARCH CONSOLE'A NASIL GÖNDERILIR -->
<div class="sm-section">
    <h3><span class="icon">📤</span>Google Search Console'a Sitemap Gönderme</h3>

    <p style="font-size:14px;color:#374151;line-height:1.7;margin:0 0 14px;">
        Sitemap'i Google'a tanıtmak için Search Console'da bir kez göndermeniz yeterli. Sonraki güncellemeleri Google otomatik tarar (sitemap dinamik olduğu için).
    </p>

    <div class="sm-guide">
        <ol>
            <li><a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> aç</li>
            <li>Sol menüde <strong>Site Haritaları</strong> (Sitemaps) tıkla</li>
            <li>"Yeni site haritası ekle" alanına sadece <code>sitemap.xml</code> yaz (tam URL değil)</li>
            <li><strong>Gönder</strong> butonuna bas</li>
            <li>Birkaç saniye sonra "Başarılı" durumu görünmeli</li>
            <li>Bing Webmaster Tools için aynı adımı <a href="https://www.bing.com/webmasters" target="_blank">bing.com/webmasters</a>'da tekrarla</li>
        </ol>
    </div>

    <p style="font-size:13px;color:#6b7280;margin-top:14px;line-height:1.6;">
        <strong>📌 Önemli:</strong> Google, ayrı bir "ping" API'sini 2023'te kaldırdı. Şimdi sadece Search Console'dan göndermek veya robots.txt'te belirtmek yeterli (her ikisi de yapıldı).
    </p>
</div>

<!-- AÇIKLAMALAR -->
<div class="sm-section">
    <h3><span class="icon">💡</span>Sitemap Nasıl Çalışıyor?</h3>

    <p style="font-size:14px;color:#374151;line-height:1.75;margin:0 0 12px;">
        Tekcan Metal'in sitemap'i <strong>tamamen dinamik</strong>. Yani:
    </p>
    <ul style="font-size:14px;color:#374151;line-height:1.75;margin:0 0 14px;padding-left:20px;">
        <li>Yeni ürün eklediğinde → sitemap'te <strong>otomatik</strong> görünür</li>
        <li>Blog yazısı yayınladığında → sitemap'te <strong>otomatik</strong> görünür</li>
        <li>Kategori, hizmet, fiyat listesi vb değiştiğinde → <strong>anında</strong> yansır</li>
        <li>Manuel "yeniden oluştur" butonuna basmaya <strong>gerek yok</strong></li>
    </ul>

    <p style="font-size:14px;color:#374151;line-height:1.75;margin:0 0 12px;">
        <strong>Yapman gereken tek şey:</strong>
    </p>
    <ol style="font-size:14px;color:#374151;line-height:1.75;margin:0;padding-left:20px;">
        <li>İlk seferde Search Console'da <code>sitemap.xml</code> gönder</li>
        <li>İçerik değişikliklerinden sonra (büyük güncellemeler için) Search Console → <strong>İste</strong> butonuna basıp tekrar gönder (opsiyonel, hızlandırır)</li>
    </ol>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
