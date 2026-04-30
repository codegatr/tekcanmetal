<?php
/**
 * IndexNow API Entegrasyonu — Tekcan Metal
 *
 * Bing, Yandex, Seznam, Naver gibi arama motorlarına URL değişikliklerini
 * anında bildirir. Google IndexNow desteklemese de Bing/Yandex'te etki büyük.
 *
 * Doc: https://www.indexnow.org/documentation
 */

require_once __DIR__ . '/db.php';

if (!defined('TM_INDEXNOW_KEY')) {
    // Tekcan Metal'e özel API key (8-128 karakter, hex)
    define('TM_INDEXNOW_KEY', '7c3f8e2a9b1d4e6f5a8c2d3e4f5a6b7c8d9e0f1a');
}

/**
 * Tek bir URL için IndexNow ping atar (asenkron)
 *
 * @param string $url Tam URL — https://tekcanmetal.com/blog/genisletilmis-sac-rehberi
 * @return array ['ok'=>bool, 'http_code'=>int, 'message'=>string]
 */
function indexnow_ping(string $url): array {
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return ['ok' => false, 'http_code' => 0, 'message' => 'Invalid URL'];

    $endpoint = 'https://api.indexnow.org/indexnow';
    $payload = [
        'host' => $host,
        'key' => TM_INDEXNOW_KEY,
        'urlList' => [$url],
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'ok' => in_array($httpCode, [200, 202], true),
        'http_code' => $httpCode,
        'message' => $error ?: ($response ?: 'OK'),
    ];
}

/**
 * Birden fazla URL'i tek istekte gönderir (toplu indeks tetikleme)
 *
 * @param array $urls Tam URL listesi (max 10000)
 * @return array
 */
function indexnow_ping_bulk(array $urls): array {
    if (empty($urls)) return ['ok' => false, 'message' => 'No URLs'];

    $host = parse_url($urls[0], PHP_URL_HOST);
    if (!$host) return ['ok' => false, 'message' => 'Invalid first URL'];

    // Filter — sadece aynı host
    $urls = array_filter($urls, fn($u) => parse_url($u, PHP_URL_HOST) === $host);
    $urls = array_values(array_slice($urls, 0, 10000));

    $endpoint = 'https://api.indexnow.org/indexnow';
    $payload = [
        'host' => $host,
        'key' => TM_INDEXNOW_KEY,
        'urlList' => $urls,
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'ok' => in_array($httpCode, [200, 202], true),
        'http_code' => $httpCode,
        'urls_count' => count($urls),
        'message' => $response ?: 'Done',
    ];
}

/**
 * Site içindeki TÜM aktif sayfaları toplu olarak IndexNow'a gönderir.
 * Cron veya admin "yeniden indeksle" butonu ile tetiklenir.
 */
function indexnow_ping_full_site(): array {
    $base = rtrim(settings('site_url', 'https://tekcanmetal.com'), '/');
    $urls = [];

    // Statik sayfalar
    $statics = ['', 'urunler.php', 'hesaplama.php', 'hizmetler.php', 'partnerler.php',
                'blog.php', 'sss.php', 'hakkimizda.php', 'iletisim.php', 'galeri.php',
                'sadakat.php', 'mail-order.php', 'iban.php'];
    foreach ($statics as $s) $urls[] = $base . '/' . $s;

    // Dinamik
    try {
        foreach (all("SELECT slug FROM tm_categories WHERE is_active=1 AND parent_id IS NULL") as $r)
            $urls[] = $base . '/kategori/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_products WHERE is_active=1") as $r)
            $urls[] = $base . '/urun/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_services WHERE is_active=1") as $r)
            $urls[] = $base . '/hizmet/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_blog_posts WHERE is_active=1") as $r)
            $urls[] = $base . '/blog/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_seo_iller WHERE is_active=1") as $r)
            $urls[] = $base . '/il/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_seo_ulkeler WHERE is_active=1") as $r)
            $urls[] = $base . '/ihracat/' . $r['slug'];
        foreach (all("SELECT slug FROM tm_pages WHERE is_active=1") as $r)
            $urls[] = $base . '/sayfa/' . $r['slug'];
    } catch (Throwable $e) {}

    $urls = array_unique($urls);
    return indexnow_ping_bulk($urls);
}
