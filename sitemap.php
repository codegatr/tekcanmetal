<?php
/**
 * Tekcan Metal — Dinamik Sitemap
 * /sitemap.xml -> .htaccess RewriteRule ile sitemap.php'ye yönlenir
 */
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // sitemap'i Google index'lemesin

$base = rtrim(settings('site_url', url('')), '/');

function sm_url(string $loc, ?string $lastmod = null, string $freq = 'weekly', string $priority = '0.7'): void {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    if ($lastmod) {
        $ts = strtotime($lastmod);
        if ($ts) echo "    <lastmod>" . date('Y-m-d', $ts) . "</lastmod>\n";
    }
    echo "    <changefreq>$freq</changefreq>\n";
    echo "    <priority>$priority</priority>\n";
    echo "  </url>\n";
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Sabit sayfalar
$staticPages = [
    ''                  => ['daily',   '1.0'],
    'hakkimizda.php'    => ['monthly', '0.8'],
    'ekibimiz.php'      => ['monthly', '0.6'],
    'partnerler.php'    => ['monthly', '0.6'],
    'urunler.php'       => ['weekly',  '0.9'],
    'hizmetler.php'     => ['monthly', '0.8'],
    'hesaplama.php'     => ['monthly', '0.7'],
    'galeri.php'        => ['weekly',  '0.6'],
    'blog.php'          => ['daily',   '0.8'],
    'sss.php'           => ['monthly', '0.5'],
    'iban.php'          => ['monthly', '0.5'],
    'iletisim.php'      => ['monthly', '0.7'],
    'mail-order.php'    => ['monthly', '0.5'],
    'sadakat.php'       => ['monthly', '0.5'],
];
foreach ($staticPages as $p => [$freq, $pri]) {
    sm_url($base . '/' . $p, null, $freq, $pri);
}

// Veritabanından dinamik içerikler — her tablo ayrı try ile
try {
    foreach (all("SELECT slug FROM tm_categories WHERE is_active=1") as $r) {
        sm_url($base . '/kategori.php?slug=' . urlencode($r['slug']), null, 'weekly', '0.7');
    }
} catch (Throwable $e) {}

try {
    foreach (all("SELECT slug, created_at FROM tm_products WHERE is_active=1") as $r) {
        sm_url($base . '/urun.php?slug=' . urlencode($r['slug']), $r['created_at'] ?? null, 'monthly', '0.6');
    }
} catch (Throwable $e) {}

try {
    foreach (all("SELECT slug FROM tm_services WHERE is_active=1") as $r) {
        sm_url($base . '/hizmet.php?slug=' . urlencode($r['slug']), null, 'monthly', '0.7');
    }
} catch (Throwable $e) {}

try {
    foreach (all("SELECT slug, created_at FROM tm_gallery_albums WHERE is_active=1") as $r) {
        sm_url($base . '/galeri-detay.php?slug=' . urlencode($r['slug']), $r['created_at'] ?? null, 'monthly', '0.5');
    }
} catch (Throwable $e) {}

try {
    foreach (all("SELECT slug, published_at FROM tm_blog_posts WHERE is_active=1 AND (published_at IS NULL OR published_at <= NOW())") as $r) {
        sm_url($base . '/blog-detay.php?slug=' . urlencode($r['slug']), $r['published_at'] ?? null, 'weekly', '0.7');
    }
} catch (Throwable $e) {}

try {
    foreach (all("SELECT slug, updated_at FROM tm_pages WHERE is_active=1") as $r) {
        sm_url($base . '/sayfa.php?slug=' . urlencode($r['slug']), $r['updated_at'] ?? null, 'yearly', '0.4');
    }
} catch (Throwable $e) {}

echo '</urlset>';
