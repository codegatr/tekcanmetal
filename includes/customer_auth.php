<?php
/**
 * Tekcan Metal — Sanal POS müşteri hesapları (v1.0.130)
 *
 * Müşteri hesapları YÖNETİCİ tarafından oluşturulur (admin/customers.php); kendiliğinden
 * kayıt (self-servis) YOKTUR. Müşteri girişi tamamen isteğe bağlıdır: giriş yapmadan da
 * odeme.php üzerinden misafir olarak ödeme yapılabilir. Giriş yapan müşteri, ödeme formunun
 * ad/e-posta/telefon alanlarının önceden dolu gelmesinden ve "Geçmiş Ödemelerim" listesinden
 * faydalanır.
 *
 * Oturum, admin oturumundan tamamen ayrıdır: $_SESSION['customer_id'] / ['admin_id'] hiç
 * kesişmez; aynı tarayıcıda biri diğerini etkilemez.
 */

/* ============================================================
 * ŞEMA (idempotent)
 * ============================================================ */

function cust_schema_sql(): string {
    return "CREATE TABLE IF NOT EXISTS tm_customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(60) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    company VARCHAR(150) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    last_ip VARCHAR(45) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_username (username),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}

function cust_ensure_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        db()->exec(cust_schema_sql());
        $has = (int)val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tm_payments' AND COLUMN_NAME = 'customer_id'");
        if (!$has) {
            db()->exec("ALTER TABLE tm_payments ADD COLUMN customer_id INT UNSIGNED NULL AFTER email, ADD INDEX idx_customer (customer_id)");
        }
    } catch (Throwable $e) {
        // Sessiz — çağıran sayfa tabloya erişemezse kendi hata mesajını gösterir
    }
}

/* ============================================================
 * YARDIMCILAR
 * ============================================================ */

/** Karışmayan karakterlerden (I, O, 0, 1 yok) okunur, WhatsApp/telefonla iletilebilir şifre. */
function cust_generate_password(int $len = 10): string {
    $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
    $out = '';
    for ($i = 0; $i < $len; $i++) $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    return $out;
}

/** "ahmet.yilmaz" gibi bir öneriden benzersiz kullanıcı adı üretir (ahmet.yilmaz, ahmet.yilmaz2, ...). */
function cust_suggest_username(string $base): string {
    $base = mb_strtolower(trim($base), 'UTF-8');
    $map = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u', 'İ' => 'i'];
    $base = strtr($base, $map);
    $base = (string)preg_replace('/[^a-z0-9]+/', '.', $base);
    $base = trim($base, '.');
    if ($base === '') $base = 'musteri';
    $base = mb_substr($base, 0, 40, 'UTF-8');
    $try = $base;
    $i = 1;
    while (val("SELECT COUNT(*) FROM tm_customers WHERE username=?", [$try]) > 0) {
        $i++;
        $try = $base . $i;
    }
    return $try;
}

function cust_valid_username(string $u): bool {
    return (bool)preg_match('/^[a-z0-9._-]{3,60}$/', $u);
}

/* ============================================================
 * OTURUM
 * ============================================================ */

function customer(): ?array {
    static $cached = false;
    if ($cached !== false) return $cached ?: null;
    if (empty($_SESSION['customer_id'])) return $cached = null;
    $c = row("SELECT * FROM tm_customers WHERE id=? AND is_active=1", [(int)$_SESSION['customer_id']]);
    if (!$c) {
        unset($_SESSION['customer_id']);
        return $cached = null;
    }
    return $cached = $c;
}

function customer_logout(): void {
    unset($_SESSION['customer_id']);
    session_regenerate_id(true);
}

/**
 * @return array ['ok'=>bool, 'msg'=>string, 'must_change'=>bool]
 */
function customer_login(string $username, string $password): array {
    $username = mb_strtolower(trim($username), 'UTF-8');
    $fail = ['ok' => false, 'msg' => 'Kullanıcı adı veya şifre hatalı.', 'must_change' => false];
    if ($username === '' || $password === '') return $fail;

    $c = row("SELECT * FROM tm_customers WHERE username=?", [$username]);
    if (!$c) return $fail;

    if (!empty($c['locked_until']) && strtotime($c['locked_until']) > time()) {
        $mins = (int)ceil((strtotime($c['locked_until']) - time()) / 60);
        return ['ok' => false, 'msg' => "Çok sayıda hatalı deneme nedeniyle hesabınız kilitlendi. Lütfen {$mins} dakika sonra tekrar deneyin.", 'must_change' => false];
    }
    if (!$c['is_active']) return $fail;

    if (!password_verify($password, $c['password'])) {
        $attempts = (int)$c['failed_attempts'] + 1;
        if ($attempts >= 5) {
            q("UPDATE tm_customers SET failed_attempts=0, locked_until=(NOW() + INTERVAL 15 MINUTE) WHERE id=?", [$c['id']]);
            return ['ok' => false, 'msg' => 'Çok sayıda hatalı deneme nedeniyle hesabınız 15 dakika süreyle kilitlendi.', 'must_change' => false];
        }
        q("UPDATE tm_customers SET failed_attempts=? WHERE id=?", [$attempts, $c['id']]);
        return $fail;
    }

    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int)$c['id'];
    q("UPDATE tm_customers SET failed_attempts=0, locked_until=NULL, last_login_at=NOW(), last_ip=? WHERE id=?", [get_ip(), $c['id']]);
    if (function_exists('log_activity')) log_activity('login', 'customer', (int)$c['id'], 'Müşteri girişi: ' . $c['username']);

    return ['ok' => true, 'msg' => '', 'must_change' => (bool)$c['must_change_password']];
}

/**
 * @return array ['ok'=>bool, 'msg'=>string]
 */
function customer_change_password(int $customerId, string $current, string $new): array {
    $c = row("SELECT * FROM tm_customers WHERE id=?", [$customerId]);
    if (!$c) return ['ok' => false, 'msg' => 'Hesap bulunamadı.'];
    if (!password_verify($current, $c['password'])) return ['ok' => false, 'msg' => 'Mevcut şifreniz hatalı.'];
    if (mb_strlen($new, 'UTF-8') < 8) return ['ok' => false, 'msg' => 'Yeni şifre en az 8 karakter olmalıdır.'];
    if ($new === $current) return ['ok' => false, 'msg' => 'Yeni şifre, mevcut şifreyle aynı olamaz.'];

    q("UPDATE tm_customers SET password=?, must_change_password=0 WHERE id=?",
      [password_hash($new, PASSWORD_BCRYPT), $customerId]);
    if (function_exists('log_activity')) log_activity('update', 'customer', $customerId, 'Müşteri şifresini değiştirdi');
    return ['ok' => true, 'msg' => 'Şifreniz güncellendi.'];
}
