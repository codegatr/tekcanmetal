<?php
/**
 * Tekcan Metal — Dinamik Sitemap
 * /sitemap.xml -> .htaccess RewriteRule ile sitemap.php'ye yönlenir
 *
 * v1.0.39: il, il-urun, ihracat, kategori, ürün, blog, hizmet, statik sayfalar dahil
 */
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$base = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
if (empty($base) || $base === 'https://') {
    $base = 'https://tekcanmetal.com';
}

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

$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// 1) Ana sayfalar
$staticPages = [
    ''                  => ['daily',   '1.0'],
    'urunler.php'       => ['daily',   '0.9'],
    'hesaplama.php'     => ['weekly',  '0.9'],
    'hizmetler.php'     => ['weekly',  '0.8'],
    'partnerler.php'    => ['weekly',  '0.7'],
    'blog.php'          => ['daily',   '0.7'],
    'sss.php'           => ['weekly',  '0.7'],
    'hakkimizda.php'    => ['monthly', '0.7'],
    'iletisim.php'      => ['monthly', '0.7'],
    'galeri.php'        => ['monthly', '0.6'],
    'sadakat.php'       => ['monthly', '0.5'],
    'mail-order.php'    => ['monthly', '0.5'],
];
foreach ($staticPages as $path => [$freq, $prio]) {
    sm_url($base . '/' . $path, $today, $freq, $prio);
}

// 2) Kategoriler
try {
    $cats = all("SELECT slug FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");
    foreach ($cats as $c) {
        sm_url($base . '/kategori.php?slug=' . urlencode($c['slug']), $today, 'weekly', '0.8');
    }
} catch (Throwable $e) {}

// 3) Ürünler (24 ürün)
try {
    $products = all("SELECT slug, created_at FROM tm_products WHERE is_active=1");
    foreach ($products as $p) {
        $lm = !empty($p['created_at']) ? $p['created_at'] : $today;
        sm_url($base . '/urun.php?slug=' . urlencode($p['slug']), $lm, 'monthly', '0.8');
    }
} catch (Throwable $e) {}

// 4) Hizmetler (lazer kesim, oksijen kesim, dekoratif sac)
try {
    $services = all("SELECT slug FROM tm_services WHERE is_active=1");
    foreach ($services as $s) {
        sm_url($base . '/hizmet.php?slug=' . urlencode($s['slug']), $today, 'monthly', '0.7');
    }
} catch (Throwable $e) {}

// 5) Blog yazıları
try {
    $posts = all("SELECT slug, published_at FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL");
    foreach ($posts as $b) {
        sm_url($base . '/blog-detay.php?slug=' . urlencode($b['slug']),
               $b['published_at'] ?? $today, 'monthly', '0.6');
    }
} catch (Throwable $e) {}

// 6) İl SEO sayfaları (25 il)
try {
    $iller = all("SELECT slug FROM tm_seo_iller WHERE is_active=1 ORDER BY sort_order");
    foreach ($iller as $il) {
        sm_url($base . '/il.php?slug=' . urlencode($il['slug']), $today, 'weekly', '0.7');
    }
} catch (Throwable $e) {}

// 7) İl × Ürün matrisi (25 il × 8 öne çıkan ürün = ~200 sayfa)
try {
    $iller = all("SELECT slug FROM tm_seo_iller WHERE is_active=1 ORDER BY sort_order");
    $featured = all("SELECT slug FROM tm_products WHERE is_active=1 ORDER BY is_featured DESC, sort_order LIMIT 8");
    foreach ($iller as $il) {
        foreach ($featured as $p) {
            sm_url($base . '/il-urun.php?il=' . urlencode($il['slug']) . '&urun=' . urlencode($p['slug']),
                   $today, 'monthly', '0.5');
        }
    }
} catch (Throwable $e) {}

// 8) İhracat ülkeleri (4)
try {
    $ulkeler = all("SELECT slug FROM tm_seo_ulkeler WHERE is_active=1 ORDER BY sort_order");
    foreach ($ulkeler as $u) {
        sm_url($base . '/ihracat.php?slug=' . urlencode($u['slug']), $today, 'weekly', '0.6');
    }
} catch (Throwable $e) {}

// 9) Statik sayfalar (KVKK, çerez vs)
try {
    $pages = all("SELECT slug FROM tm_pages WHERE is_active=1");
    foreach ($pages as $sp) {
        sm_url($base . '/sayfa.php?slug=' . urlencode($sp['slug']), $today, 'monthly', '0.4');
    }
} catch (Throwable $e) {}

echo '</urlset>' . "\n";
