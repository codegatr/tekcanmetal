<?php
/**
 * Tekcan Metal — DB & Çekirdek Yardımcıları
 */

if (!defined('TM_INSTALLED')) {
    if (!file_exists(__DIR__ . '/../config.php')) {
        header('Location: ' . (defined('TM_ADMIN') ? '../install/install.php' : 'install/install.php'));
        exit;
    }
    require __DIR__ . '/../config.php';
}

// ---- PDO bağlantısı (lazy singleton) ----
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            if (defined('TM_DEBUG') && TM_DEBUG) {
                die('DB error: ' . $e->getMessage());
            }
            die('Veritabanına bağlanılamadı.');
        }
    }
    return $pdo;
}

// ---- Hızlı sorgu yardımcıları ----
function q(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function row(string $sql, array $params = []): array|false {
    return q($sql, $params)->fetch();
}

function all(string $sql, array $params = []): array {
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = []) {
    return q($sql, $params)->fetchColumn();
}

// ---- Ayarlar (cache'li) ----
function settings(?string $key = null, $default = null) {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (all("SELECT setting_key, setting_value FROM tm_settings") as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (Throwable $e) {}
    }
    if ($key === null) return $cache;
    return $cache[$key] ?? $default;
}

function settings_set(string $key, $value, string $group = 'general'): void {
    q("INSERT INTO tm_settings (setting_key, setting_value, setting_group) VALUES (?,?,?)
       ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)",
       [$key, (string)$value, $group]);
}

// ---- Güvenlik ----
function h($v): string {
    if ($v === null) return '';
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool {
    $t = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t);
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

// ---- URL & yönlendirme ----
function url(string $path = ''): string {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    if (!preg_match('#^https?://#i', $path)) {
        $path = url($path);
    }
    header('Location: ' . $path);
    exit;
}

function back(?string $fallback = null): void {
    $ref = $_SERVER['HTTP_REFERER'] ?? null;
    redirect($ref ?: ($fallback ?: ''));
}

// ---- Mesaj bayrakları ----
function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): array {
    $arr = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $arr;
}

// ---- Slug ----
function slugify(string $text): string {
    $tr = ['ç','Ç','ğ','Ğ','ı','İ','ö','Ö','ş','Ş','ü','Ü'];
    $en = ['c','c','g','g','i','i','o','o','s','s','u','u'];
    $text = str_replace($tr, $en, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]+/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ---- Tarih ----
function tr_date(?string $datetime, bool $with_time = false): string {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    $months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
    $out = date('j', $ts) . ' ' . $months[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
    if ($with_time) $out .= ' ' . date('H:i', $ts);
    return $out;
}

// ---- IP ----
function get_ip(): string {
    foreach (['HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

// ---- Aktivite log ----
function log_activity(string $action, ?string $target_type = null, ?int $target_id = null, ?string $description = null): void {
    try {
        $uid = $_SESSION['admin_id'] ?? null;
        q("INSERT INTO tm_activity_logs (user_id,action,target_type,target_id,description,ip_address) VALUES (?,?,?,?,?,?)",
          [$uid, $action, $target_type, $target_id, $description, get_ip()]);
    } catch (Throwable $e) {}
}

// ---- Resim yükleme ----
function upload_image(array $file, string $subdir = 'uploads', int $max_kb = 5120): array|false {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $max_kb * 1024) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif','svg'], true)) return false;

    $dir = __DIR__ . '/../' . trim($subdir, '/');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    return [
        'path' => trim($subdir, '/') . '/' . $name,
        'url'  => url(trim($subdir, '/') . '/' . $name),
        'name' => $name,
        'size' => $file['size'],
    ];
}

// ---- Telefon formatla ----
function format_phone(string $phone): string {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 11 && $digits[0] === '0') {
        return sprintf('0 %s %s %s %s',
            substr($digits, 1, 3), substr($digits, 4, 3),
            substr($digits, 7, 2), substr($digits, 9, 2)
        );
    }
    return $phone;
}

function phone_link(string $phone): string {
    return 'tel:+90' . preg_replace('/\D/', '', ltrim($phone, '0'));
}

function whatsapp_link(string $phone, string $msg = ''): string {
    $num = preg_replace('/\D/', '', $phone);
    if (strpos($num, '0') === 0) $num = '90' . substr($num, 1);
    if (strpos($num, '90') !== 0) $num = '90' . $num;
    $url = 'https://wa.me/' . $num;
    if ($msg) $url .= '?text=' . rawurlencode($msg);
    return $url;
}

// ---- Excerpt ----
function excerpt(?string $text, int $len = 150): string {
    if (!$text) return '';
    $text = strip_tags($text);
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text, 'UTF-8') <= $len) return $text;
    return mb_substr($text, 0, $len, 'UTF-8') . '…';
}

// ---- View (resim varsa upload yolundan, yoksa default) ----
function img_url(?string $path, string $default = 'assets/img/placeholder.svg'): string {
    if (!$path) return url($default);
    if (preg_match('#^https?://#i', $path)) return $path;
    return url($path);
}

// ---- Sürüm karşılaştırma ----
function version_gt(string $a, string $b): bool {
    return version_compare($a, $b, '>');
}

// ---- Session başlat ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- i18n bootstrap (v1.0.56) ----
require_once __DIR__ . '/i18n.php';

// Kullanıcı ?lang=en gibi parametre ile dil değiştirirse cookie'ye yaz
if (isset($_GET['set_lang']) && in_array($_GET['set_lang'], I18N_LANG_CODES, true)) {
    set_lang_cookie($_GET['set_lang']);
    // Aynı sayfaya redirect (lang param'sız)
    $url = lang_switch_url($_GET['set_lang']);
    header('Location: ' . $url);
    exit;
}
