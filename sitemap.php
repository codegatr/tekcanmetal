<?php
/**
 * Tekcan Metal — Dinamik Multi-Language Sitemap
 * /sitemap.xml -> .htaccess RewriteRule ile sitemap.php'ye yönlenir
 *
 * v1.0.66: hreflang etiketli multi-dil destekli sitemap.
 * Her URL için TR (default), EN, AR, RU alternates eklenir.
 * Google sayfanın hangi dilde hangi versiyonu olduğunu doğru indeksler.
 */
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$base = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
if (empty($base) || $base === 'https://') {
    $base = 'https://tekcanmetal.com';
}

$LANGS = ['tr', 'en', 'ar', 'ru'];
$DEFAULT_LANG = 'tr';

/**
 * Tek URL'i tüm dil alternatifleriyle yazdırır
 */
function sm_url_multi(string $base, string $path, ?string $lastmod = null,
                      string $freq = 'weekly', string $priority = '0.7',
                      array $langs = ['tr','en','ar','ru']): void {
    $defaultUrl = $base . '/' . ltrim($path, '/');

    foreach ($langs as $lang) {
        $loc = ($lang === 'tr')
            ? $defaultUrl
            : $base . '/' . $lang . '/' . ltrim($path, '/');

        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
        if ($lastmod) {
            $ts = strtotime($lastmod);
            if ($ts) echo "    <lastmod>" . date('Y-m-d', $ts) . "</lastmod>\n";
        }
        echo "    <changefreq>$freq</changefreq>\n";
        echo "    <priority>$priority</priority>\n";

        // hreflang alternates - Google bu sayfanın hangi diller için olduğunu anlar
        foreach ($langs as $altLang) {
            $altUrl = ($altLang === 'tr')
                ? $defaultUrl
                : $base . '/' . $altLang . '/' . ltrim($path, '/');
            echo '    <xhtml:link rel="alternate" hreflang="' . $altLang . '" href="' . htmlspecialchars($altUrl, ENT_XML1) . '"/>' . "\n";
        }
        // x-default → TR
        echo '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($defaultUrl, ENT_XML1) . '"/>' . "\n";

        echo "  </url>\n";
    }
}

/** Sadece tek dilli URL (örn: KVKK gibi yasal sayfalar TR-only ise) */
function sm_url_single(string $base, string $path, ?string $lastmod = null,
                       string $freq = 'weekly', string $priority = '0.7'): void {
    $loc = $base . '/' . ltrim($path, '/');
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
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

// 1) Ana sayfalar (multi-language)
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
    'iban.php'          => ['monthly', '0.5'],
];
foreach ($staticPages as $path => [$freq, $prio]) {
    sm_url_multi($base, $path, $today, $freq, $prio, $LANGS);
}

// 2) Kategoriler (multi-language)
try {
    $cats = all("SELECT slug, updated_at FROM tm_categories WHERE is_active=1 AND parent_id IS NULL ORDER BY sort_order");
    foreach ($cats as $c) {
        $lm = !empty($c['updated_at']) ? $c['updated_at'] : $today;
        sm_url_multi($base, 'kategori.php?slug=' . urlencode($c['slug']), $lm, 'weekly', '0.8', $LANGS);
    }
} catch (Throwable $e) {}

// 3) Ürünler (multi-language)
try {
    $products = all("SELECT slug, COALESCE(updated_at, created_at) AS lm FROM tm_products WHERE is_active=1");
    foreach ($products as $p) {
        sm_url_multi($base, 'urun.php?slug=' . urlencode($p['slug']),
                     $p['lm'] ?: $today, 'monthly', '0.8', $LANGS);
    }
} catch (Throwable $e) {}

// 4) Hizmetler (multi-language)
try {
    $services = all("SELECT slug FROM tm_services WHERE is_active=1");
    foreach ($services as $s) {
        sm_url_multi($base, 'hizmet.php?slug=' . urlencode($s['slug']), $today, 'monthly', '0.8', $LANGS);
    }
} catch (Throwable $e) {}

// 5) Blog yazıları (multi-language) — yüksek priority
try {
    $posts = all("SELECT slug, COALESCE(updated_at, published_at) AS lm FROM tm_blog_posts WHERE is_active=1 AND published_at IS NOT NULL ORDER BY published_at DESC");
    foreach ($posts as $b) {
        sm_url_multi($base, 'blog-detay.php?slug=' . urlencode($b['slug']),
                     $b['lm'] ?? $today, 'weekly', '0.7', $LANGS);
    }
} catch (Throwable $e) {}

// 6) İl SEO sayfaları (TR-only — Türkiye spesifik)
try {
    $iller = all("SELECT slug FROM tm_seo_iller WHERE is_active=1 ORDER BY sort_order");
    foreach ($iller as $il) {
        sm_url_single($base, 'il.php?slug=' . urlencode($il['slug']), $today, 'weekly', '0.7');
    }
} catch (Throwable $e) {}

// 7) İl × Ürün matrisi (TR-only)
try {
    $iller = all("SELECT slug FROM tm_seo_iller WHERE is_active=1 ORDER BY sort_order");
    $featured = all("SELECT slug FROM tm_products WHERE is_active=1 ORDER BY is_featured DESC, sort_order LIMIT 8");
    foreach ($iller as $il) {
        foreach ($featured as $p) {
            sm_url_single($base, 'il-urun.php?il=' . urlencode($il['slug']) . '&urun=' . urlencode($p['slug']),
                          $today, 'monthly', '0.5');
        }
    }
} catch (Throwable $e) {}

// 8) İhracat ülkeleri (multi-language — uluslararası içerik)
try {
    $ulkeler = all("SELECT slug FROM tm_seo_ulkeler WHERE is_active=1 ORDER BY sort_order");
    foreach ($ulkeler as $u) {
        sm_url_multi($base, 'ihracat.php?slug=' . urlencode($u['slug']), $today, 'weekly', '0.6', $LANGS);
    }
} catch (Throwable $e) {}

// 9) Yasal sayfalar (multi-language)
try {
    $pages = all("SELECT slug FROM tm_pages WHERE is_active=1");
    foreach ($pages as $sp) {
        sm_url_multi($base, 'sayfa.php?slug=' . urlencode($sp['slug']), $today, 'monthly', '0.4', $LANGS);
    }
} catch (Throwable $e) {}

echo '</urlset>' . "\n";
