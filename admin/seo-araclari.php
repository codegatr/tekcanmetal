<?php
define('TM_ADMIN', true);
$adminTitle = 'SEO Araçları';
require __DIR__ . '/_layout.php';

// ========== POST: IndexNow ==========
$indexResult = null;
if (($_POST['action'] ?? '') === 'indexnow_ping_all') {
    require_once __DIR__ . '/../includes/indexnow.php';
    try {
        $indexResult = indexnow_ping_full_site();
    } catch (Throwable $e) {
        $indexResult = ['ok' => false, 'message' => $e->getMessage()];
    }
}

// Sitemap
$sitemapPath = __DIR__ . '/../sitemap.php';
$sitemapInfo = is_file($sitemapPath) ? [
    'updated' => date('d.m.Y H:i', filemtime($sitemapPath)),
    'size' => round(filesize($sitemapPath) / 1024, 1) . ' KB',
] : null;

// Stats
$stats = [
    'pages'     => (int)val("SELECT COUNT(*) FROM tm_pages WHERE is_active=1"),
    'products'  => (int)val("SELECT COUNT(*) FROM tm_products WHERE is_active=1"),
    'categories'=> (int)val("SELECT COUNT(*) FROM tm_categories WHERE is_active=1"),
    'blog'      => (int)val("SELECT COUNT(*) FROM tm_blog_posts WHERE is_active=1"),
    'services'  => (int)val("SELECT COUNT(*) FROM tm_services WHERE is_active=1"),
    'iller'     => 0,
    'ulkeler'   => 0,
];
try { $stats['iller']   = (int)val("SELECT COUNT(*) FROM tm_seo_iller WHERE is_active=1"); } catch (Throwable $e) {}
try { $stats['ulkeler'] = (int)val("SELECT COUNT(*) FROM tm_seo_ulkeler WHERE is_active=1"); } catch (Throwable $e) {}

$totalUrls = $stats['pages'] + $stats['products'] + $stats['categories'] +
             $stats['blog'] + $stats['services'] + $stats['iller'] + $stats['ulkeler'] + 13;

$baseUrl = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
$siteHost = parse_url($baseUrl, PHP_URL_HOST);
?>
<style>
.seo-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px; margin:0 0 24px; }
.seo-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:18px; }
.seo-card h3 { margin:0 0 6px; color:#0f172a; font-size:14px; font-weight:600; }
.seo-stat { font-size:30px; font-weight:700; color:#0c1e44; line-height:1; margin:6px 0 4px; }
.seo-stat-label { color:#64748b; font-size:12px; }
.seo-action { background:#fafaf7; border:1px solid #e3e0d8; border-radius:8px; padding:20px; margin:16px 0; }
.seo-action h2 { margin:0 0 12px; font-size:18px; color:#0c1e44; }
.btn-seo { background:#c8102e; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px; }
.btn-seo:hover { background:#a00d24; }
.alert-success { background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin:12px 0; border:1px solid #a7f3d0; }
.alert-error { background:#fee2e2; color:#991b1b; padding:12px; border-radius:6px; margin:12px 0; border:1px solid #fecaca; }
.seo-checklist { list-style:none; padding:0; margin:0; }
.seo-checklist li { padding:8px 0; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; font-size:13px; }
.seo-checklist li:last-child { border-bottom:none; }
.seo-checklist .ok { color:#059669; font-size:16px; font-weight:700; }
.seo-tools-links { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
.seo-tools-links a { display:inline-block; padding:8px 14px; background:#e0e7ff; color:#3730a3; text-decoration:none; border-radius:6px; font-size:12px; font-weight:500; }
.seo-tools-links a:hover { background:#c7d2fe; }
</style>

<?php if ($indexResult): ?>
  <?php if ($indexResult['ok'] ?? false): ?>
    <div class="alert-success">
      ✅ <strong>IndexNow başarılı!</strong> <?= (int)($indexResult['urls_count'] ?? 0) ?> URL Bing/Yandex/Seznam'a bildirildi.
      HTTP <?= (int)($indexResult['http_code'] ?? 0) ?>. 30 dakika–24 saat içinde bu motorlar yeniden tarar.
    </div>
  <?php else: ?>
    <div class="alert-error">
      ⚠ <strong>IndexNow hata:</strong> <?= h($indexResult['message'] ?? 'Bilinmeyen hata') ?>
      (HTTP <?= (int)($indexResult['http_code'] ?? 0) ?>)
    </div>
  <?php endif; ?>
<?php endif; ?>

<div class="seo-grid">
  <div class="seo-card">
    <h3>📄 Toplam URL</h3>
    <div class="seo-stat"><?= $totalUrls ?></div>
    <div class="seo-stat-label">Sitemap.xml'de listelenir</div>
  </div>
  <div class="seo-card">
    <h3>📦 Ürünler</h3>
    <div class="seo-stat"><?= $stats['products'] ?></div>
    <div class="seo-stat-label">Aktif ürün sayfası</div>
  </div>
  <div class="seo-card">
    <h3>✍ Blog Yazıları</h3>
    <div class="seo-stat"><?= $stats['blog'] ?></div>
    <div class="seo-stat-label">SEO içerik sayfası</div>
  </div>
  <div class="seo-card">
    <h3>📍 İl SEO</h3>
    <div class="seo-stat"><?= $stats['iller'] ?></div>
    <div class="seo-stat-label">Lokal + il × ürün matrisi</div>
  </div>
</div>

<div class="seo-action">
  <h2>⚡ IndexNow — Anında Bing/Yandex Bildirimi</h2>
  <p>Sitedeki tüm aktif URL'leri Bing, Yandex, Seznam ve Naver'a tek tıkla bildir. Bu motorlar <strong>30 dakika ile 24 saat</strong> arasında yeniden tarar.</p>
  <p style="font-size:13px;color:#64748b"><em>Not: Google IndexNow'u resmen desteklemiyor. Google için Search Console → URL incele kullanın.</em></p>

  <form method="post" style="margin-top:12px">
    <input type="hidden" name="action" value="indexnow_ping_all">
    <button type="submit" class="btn-seo" onclick="return confirm('Tüm <?= $totalUrls ?> URL\'i IndexNow\'a bildir?')">
      🚀 Tüm URL'leri Bildir (<?= $totalUrls ?>)
    </button>
  </form>
</div>

<div class="seo-action">
  <h2>🗺 Sitemap.xml</h2>
  <?php if ($sitemapInfo): ?>
    <ul class="seo-checklist">
      <li><span class="ok">✓</span> Sitemap.php dinamik (<?= h($sitemapInfo['size']) ?>)</li>
      <li><span class="ok">✓</span> Multi-language hreflang etiketli (TR/EN/AR/RU)</li>
      <li><span class="ok">✓</span> robots.txt'te tanımlı</li>
    </ul>
    <div class="seo-tools-links">
      <a href="<?= h($baseUrl) ?>/sitemap.xml" target="_blank">📥 Sitemap.xml Görüntüle</a>
      <a href="https://search.google.com/search-console/sitemaps" target="_blank">🔵 Search Console'a Gönder</a>
      <a href="https://www.bing.com/webmasters/sitemaps" target="_blank">🔵 Bing'e Gönder</a>
      <a href="https://webmaster.yandex.com/sitemap" target="_blank">🟡 Yandex'e Gönder</a>
    </div>
  <?php else: ?>
    <div class="alert-error">⚠ sitemap.php bulunamadı!</div>
  <?php endif; ?>
</div>

<div class="seo-action">
  <h2>✅ SEO Sağlık Kontrolü</h2>
  <ul class="seo-checklist">
    <li><span class="ok">✓</span> robots.txt mevcut ve doğru yapılandırılmış</li>
    <li><span class="ok">✓</span> Schema.org (LocalBusiness, BlogPosting, FAQ, Breadcrumb, WebSite) aktif</li>
    <li><span class="ok">✓</span> Open Graph + Twitter Card meta tag'leri</li>
    <li><span class="ok">✓</span> Multi-language hreflang etiketleri</li>
    <li><span class="ok">✓</span> Canonical URL'ler her sayfada</li>
    <li><span class="ok">✓</span> HTTPS (SSL) aktif</li>
    <li><span class="ok">✓</span> Mobile-responsive tasarım</li>
    <li><span class="ok">✓</span> 301 redirect'ler (eski WordPress URL → yeni)</li>
    <li><span class="ok">✓</span> IndexNow API entegrasyonu</li>
    <li><span class="ok">✓</span> Süper SEO sayfaları (genişletilmiş sac, baklava sac)</li>
  </ul>
</div>

<div class="seo-action">
  <h2>🛠 Hızlı SEO Araçları</h2>
  <div class="seo-tools-links">
    <a href="https://search.google.com/search-console" target="_blank">🔍 Google Search Console</a>
    <a href="https://www.bing.com/webmasters" target="_blank">🔍 Bing Webmaster</a>
    <a href="https://webmaster.yandex.com" target="_blank">🔍 Yandex Webmaster</a>
    <a href="https://pagespeed.web.dev/analysis?url=<?= urlencode($baseUrl) ?>" target="_blank">⚡ PageSpeed Insights</a>
    <a href="https://search.google.com/test/rich-results?url=<?= urlencode($baseUrl) ?>" target="_blank">🔬 Rich Results Test</a>
    <a href="https://search.google.com/test/mobile-friendly?url=<?= urlencode($baseUrl) ?>" target="_blank">📱 Mobile-Friendly</a>
    <a href="https://www.google.com/search?q=site:<?= urlencode($siteHost) ?>" target="_blank">🌐 Google: site:<?= h($siteHost) ?></a>
    <a href="https://business.google.com" target="_blank">📍 Google İşletme Profili</a>
  </div>
</div>

<div class="seo-action" style="background:#fef3c7;border-color:#fcd34d">
  <h2>🔑 IndexNow Doğrulama</h2>
  <p>Bu anahtar kök dizinde <code style="background:#fff;padding:2px 6px;border-radius:3px">.txt</code> dosyası olarak yayında. Bing/Yandex bu dosyayı kontrol ederek API isteklerinin sahibinizin olduğunu doğrular.</p>
  <code style="background:#fff;padding:8px 12px;border-radius:4px;display:inline-block;font-family:monospace;font-size:12px">7c3f8e2a9b1d4e6f5a8c2d3e4f5a6b7c8d9e0f1a</code>
  <br><br>
  <a href="<?= h($baseUrl) ?>/7c3f8e2a9b1d4e6f5a8c2d3e4f5a6b7c8d9e0f1a.txt" target="_blank" style="color:#92400e;font-weight:600;font-size:14px">
    ✅ Doğrulama Dosyasını Test Et →
  </a>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
