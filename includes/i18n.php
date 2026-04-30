<?php
/**
 * i18n (internationalization) — Tekcan Metal CMS
 * v1.0.56 ile eklendi.
 *
 * Desteklenen diller: TR (default), EN, AR, RU
 * URL yapısı: /, /en/, /ar/, /ru/
 *
 * Kullanım:
 *   t('header.menu.products', 'Ürün Gruplarımız')   // UI çevirisi
 *   tr_field($row, 'title')                          // İçerik çevirisi (title_en, title_ar, ...)
 *   current_lang()                                   // 'tr' / 'en' / 'ar' / 'ru'
 *   url_lang('iletisim.php')                         // Aktif dile göre URL prefix
 *   lang_switch_url('en', 'iletisim.php')            // Dil değiştirme linki
 */

// ---- Sabitler ----
const I18N_LANGUAGES = [
    'tr' => ['name' => 'Türkçe',  'native' => 'Türkçe',   'flag' => '🇹🇷', 'dir' => 'ltr', 'locale' => 'tr_TR', 'is_default' => true],
    'en' => ['name' => 'English', 'native' => 'English',  'flag' => '🇬🇧', 'dir' => 'ltr', 'locale' => 'en_US', 'is_default' => false],
    'ar' => ['name' => 'Arabic',  'native' => 'العربية',   'flag' => '🇸🇦', 'dir' => 'rtl', 'locale' => 'ar_SA', 'is_default' => false],
    'ru' => ['name' => 'Russian', 'native' => 'Русский',  'flag' => '🇷🇺', 'dir' => 'ltr', 'locale' => 'ru_RU', 'is_default' => false],
];

const I18N_DEFAULT_LANG = 'tr';
const I18N_LANG_CODES   = ['tr', 'en', 'ar', 'ru'];

// ---- Mevcut dil tespiti ----
/**
 * İstek için aktif dili tespit eder.
 * Öncelik:
 *   1. Apache RewriteRule ile set edilen LANG ($_SERVER['LANG'] / $_ENV['LANG'])
 *   2. URL'in kendisinde /en/, /ar/, /ru/ prefix var mı? (htaccess yoksa fallback)
 *   3. Cookie tm_lang
 *   4. Default (tr)
 */
function current_lang(): string {
    static $cached = null;
    if ($cached !== null) return $cached;

    // 1. Apache RewriteRule LANG değişkeni
    $lang = $_SERVER['LANG'] ?? $_SERVER['REDIRECT_LANG'] ?? null;
    if ($lang && in_array($lang, I18N_LANG_CODES, true)) {
        return $cached = $lang;
    }

    // 2. URL pattern fallback — /en/..., /ar/..., /ru/... 
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // SITE_URL'in path kısmını çıkar (örn /tekcanmetal -> '')
    $sitePath = parse_url(SITE_URL ?? 'https://tekcanmetal.com', PHP_URL_PATH) ?? '';
    $sitePath = rtrim($sitePath, '/');
    if ($sitePath && str_starts_with($uri, $sitePath)) {
        $uri = substr($uri, strlen($sitePath));
    }
    if (preg_match('#^/(en|ar|ru)(/|$)#', $uri, $m)) {
        return $cached = $m[1];
    }

    // 3. Cookie
    $ck = $_COOKIE['tm_lang'] ?? null;
    if ($ck && in_array($ck, I18N_LANG_CODES, true)) {
        return $cached = $ck;
    }

    // 4. Default
    return $cached = I18N_DEFAULT_LANG;
}

/**
 * Mevcut dilin RTL olup olmadığı (Arapça için)
 */
function is_rtl(): bool {
    $lang = current_lang();
    return (I18N_LANGUAGES[$lang]['dir'] ?? 'ltr') === 'rtl';
}

/**
 * Mevcut dilin HTML lang attribute değeri (örn: tr_TR)
 */
function lang_locale(): string {
    return I18N_LANGUAGES[current_lang()]['locale'] ?? 'tr_TR';
}

// ---- UI Çeviri (translation key-value) ----
/**
 * UI metin çevirisi.
 * tm_translations tablosundan key+lang ile fetch eder, yoksa default'ı döner.
 *
 * @param string $key      Translation key, örn: 'header.menu.products'
 * @param string|null $default  Çeviri bulunamazsa fallback (genelde TR metin)
 */
function t(string $key, ?string $default = null): string {
    static $cache = null;
    $lang = current_lang();

    // Cache: tüm çevirileri tek seferde belleğe al (performans)
    if ($cache === null) {
        $cache = [];
        try {
            foreach (all("SELECT `key`, lang, value FROM tm_translations WHERE lang IN ('tr','en','ar','ru')") as $r) {
                $cache[$r['lang']][$r['key']] = $r['value'];
            }
        } catch (Throwable $e) {
            // Tablo yoksa (henüz migration çalışmadıysa) sessiz fallback
        }
    }

    // Aktif dilde varsa onu, yoksa TR'yi, o da yoksa default'ı dön
    if (isset($cache[$lang][$key]) && $cache[$lang][$key] !== '') {
        return $cache[$lang][$key];
    }
    if (isset($cache['tr'][$key]) && $cache['tr'][$key] !== '') {
        return $cache['tr'][$key];
    }
    return $default ?? $key;
}

// ---- İçerik Çevirisi (DB row için) ----
/**
 * DB row'undan dile göre alan getir.
 * Örnek: tm_services tablosunda title, title_en, title_ar, title_ru kolonları varsa
 *   tr_field($row, 'title') → aktif dile göre title_en/title_ar/title_ru veya title döner
 *
 * @param array  $row    DB satırı
 * @param string $field  Temel alan adı (title, description, short_desc, ...)
 */
function tr_field(?array $row, string $field): string {
    if (!$row) return '';
    $lang = current_lang();

    if ($lang !== 'tr') {
        $key = $field . '_' . $lang;
        if (!empty($row[$key])) {
            return (string)$row[$key];
        }
    }
    // Fallback TR
    return (string)($row[$field] ?? '');
}

/**
 * tr_field'ın null/empty güvenli versiyonu — bool döndürür
 */
function tr_has(?array $row, string $field): bool {
    return !empty(tr_field($row, $field));
}

// ---- URL Helpers ----
/**
 * Aktif dile göre URL prefix ekler.
 * url_lang('iletisim.php') → '/iletisim.php' (TR) veya '/en/iletisim.php' (EN)
 */
function url_lang(string $path = ''): string {
    $lang = current_lang();
    $prefix = ($lang === I18N_DEFAULT_LANG) ? '' : '/' . $lang;
    $base = rtrim(SITE_URL, '/');
    $path = '/' . ltrim($path, '/');
    return $base . $prefix . $path;
}

/**
 * Belirli bir dile geçiş URL'i üretir (header dil seçici için)
 * Mevcut sayfanın aynısına gider, sadece dil değişir.
 *
 * @param string $targetLang  'tr', 'en', 'ar', 'ru'
 */
function lang_switch_url(string $targetLang): string {
    if (!in_array($targetLang, I18N_LANG_CODES, true)) {
        $targetLang = I18N_DEFAULT_LANG;
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Mevcut /en/, /ar/, /ru/ prefix'i varsa kaldır
    $uri = preg_replace('#^/(en|ar|ru)(/|$)#', '/', $uri);

    $base = rtrim(SITE_URL, '/');
    if ($targetLang === I18N_DEFAULT_LANG) {
        return $base . $uri;
    }
    return $base . '/' . $targetLang . ltrim($uri, '/');
}

/**
 * Dil cookie'sini set et (kullanıcı bayrağa tıklarsa hatırla)
 */
function set_lang_cookie(string $lang): void {
    if (!in_array($lang, I18N_LANG_CODES, true)) return;
    setcookie('tm_lang', $lang, [
        'expires'  => time() + 86400 * 365,
        'path'     => '/',
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']),
    ]);
}

// ---- hreflang tag üretimi (SEO) ----
/**
 * <head> içine yapıştırılacak hreflang link tag'leri (her sayfada).
 * Aynı sayfanın tüm dillerdeki versiyonlarını Google'a tanıtır.
 */
function hreflang_tags(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = preg_replace('#^/(en|ar|ru)(/|$)#', '/', $uri);
    $base = rtrim(SITE_URL, '/');

    $tags = [];
    foreach (I18N_LANG_CODES as $code) {
        $href = ($code === I18N_DEFAULT_LANG)
            ? $base . $uri
            : $base . '/' . $code . ltrim($uri, '/');
        $tags[] = '<link rel="alternate" hreflang="' . $code . '" href="' . htmlspecialchars($href, ENT_QUOTES) . '">';
    }
    // x-default
    $tags[] = '<link rel="alternate" hreflang="x-default" href="' . htmlspecialchars($base . $uri, ENT_QUOTES) . '">';
    return implode("\n  ", $tags);
}
