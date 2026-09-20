<?php
/**
 * Tekcan Metal — QNBpay Sanal POS yardımcıları (v1.0.122)
 *
 * QNBpay, "CCPayment" protokolünü kullanır (Sipay ailesi):
 *   1) paySmart3D  : Kart formu TARAYICIDAN doğrudan QNBpay'e POST edilir.
 *                    Kart numarası / CVV sitemizin sunucusundan GEÇMEZ, saklanmaz.
 *   2) hash_key    : AES-256-CBC, "iv:salt:sifreli" paketi (app_secret ile türetilen anahtar).
 *   3) Dönüş       : QNBpay, return_url'e POST eder (sipay_status, invoice_id, hash_key ...).
 *                    Ödeme, ancak hash_key doğrulanır + tutar/fatura eşleşirse "paid" olur.
 *
 * Bu dosya HTTP/HTML üretmez; sayfalar (odeme.php, odeme-sonuc.php, admin/sanal-pos.php)
 * bu fonksiyonları çağırır. db.php (q/row/all/val/settings/url) önceden yüklenmiş olmalıdır.
 */

defined('QNB_BASE_TEST') or define('QNB_BASE_TEST', 'https://test.qnbpay.com.tr/ccpayment');
defined('QNB_BASE_LIVE') or define('QNB_BASE_LIVE', 'https://portal.qnbpay.com.tr/ccpayment');

/* ============================================================
 * AYARLAR
 * ============================================================ */

function qnb_cfg(): array {
    $mode = settings('qnbpay_mode', 'test') === 'live' ? 'live' : 'test';

    $baseTest = trim((string)settings('qnbpay_base_url_test', ''));
    $baseLive = trim((string)settings('qnbpay_base_url_live', ''));
    $baseTest = rtrim($baseTest !== '' ? $baseTest : QNB_BASE_TEST, '/');
    $baseLive = rtrim($baseLive !== '' ? $baseLive : QNB_BASE_LIVE, '/');

    $min = (float)settings('qnbpay_min_amount', '1');
    if ($min <= 0) $min = 1.0;
    $max = (float)settings('qnbpay_max_amount', '250000');
    if ($max < $min) $max = 250000.0;

    $notify = trim((string)settings('qnbpay_notify_email', ''));
    if ($notify === '') $notify = trim((string)settings('contact_email', 'info@tekcanmetal.com'));

    return [
        'enabled'      => (string)settings('qnbpay_enabled', '0') === '1',
        'mode'         => $mode,
        'app_id'       => trim((string)settings('qnbpay_app_id', '')),
        'app_secret'   => trim((string)settings('qnbpay_app_secret', '')),
        'merchant_key' => trim((string)settings('qnbpay_merchant_key', '')),
        'base'         => $mode === 'live' ? $baseLive : $baseTest,
        'base_test'    => $baseTest,
        'base_live'    => $baseLive,
        'min'          => $min,
        'max'          => $max,
        'notify'       => $notify,
    ];
}

/** Sanal POS yayında mı? (Açık + kimlik bilgileri dolu) */
function qnb_enabled(): bool {
    $c = qnb_cfg();
    return $c['enabled'] && $c['app_id'] !== '' && $c['app_secret'] !== '' && $c['merchant_key'] !== '';
}

/* ============================================================
 * ŞEMA (idempotent — migration.sql ile aynı DDL)
 * ============================================================ */

function qnb_schema_sql(): string {
    return "CREATE TABLE IF NOT EXISTS tm_payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_id VARCHAR(40) NOT NULL,
    public_ref CHAR(32) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    company VARCHAR(150) NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    description VARCHAR(500) NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'TRY',
    installments TINYINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('pending','paid','failed','review') NOT NULL DEFAULT 'pending',
    pos_mode ENUM('test','live') NOT NULL DEFAULT 'test',
    order_no VARCHAR(64) NULL,
    gateway_code VARCHAR(30) NULL,
    gateway_message VARCHAR(500) NULL,
    md_status VARCHAR(10) NULL,
    card_mask VARCHAR(32) NULL,
    hash_valid TINYINT(1) NOT NULL DEFAULT 0,
    raw_response TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_invoice (invoice_id),
    UNIQUE KEY uniq_ref (public_ref),
    INDEX idx_status_created (status, created_at),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
}

/** Tablo yoksa oluşturur (güncelleme sırasında migration çalışmadıysa kendini onarır). */
function qnb_ensure_schema(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        db()->exec(qnb_schema_sql());
    } catch (Throwable $e) {
        // Sessiz: çağıran sayfa tabloya erişemezse kendi hata mesajını gösterir
    }
}

/* ============================================================
 * hash_key — AES-256-CBC  ("iv:salt:sifreli", '/' → '__')
 * ============================================================ */

function qnb_hash_generate(string $data, string $appSecret): string {
    $iv       = substr(sha1(random_bytes(16)), 0, 16);   // 16 hex karakter
    $salt     = substr(sha1(random_bytes(16)), 0, 4);
    $password = sha1($appSecret);
    $key      = hash('sha256', $password . $salt);       // 64 hex karakter (OpenSSL ilk 32 baytı kullanır)
    $enc      = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    if ($enc === false) {
        throw new RuntimeException('hash_key üretilemedi');
    }
    return str_replace('/', '__', $iv . ':' . $salt . ':' . $enc);
}

/** Ödeme başlatma hash'i: total|installment|currency|merchant_key|invoice_id */
function qnb_hash_payment(string $total, int $installment, string $currency, string $merchantKey, string $invoiceId, string $appSecret): string {
    return qnb_hash_generate($total . '|' . $installment . '|' . $currency . '|' . $merchantKey . '|' . $invoiceId, $appSecret);
}

/** Dönüşteki hash_key'i çözer; çözülemezse null. Dönen dizi '|' ile ayrılmış parçalardır. */
function qnb_hash_decode(string $hashKey, string $appSecret): ?array {
    $hashKey = trim($hashKey);
    if ($hashKey === '' || strlen($hashKey) > 2048 || $appSecret === '') return null;

    $hashKey = str_replace('__', '/', $hashKey);
    $p = explode(':', $hashKey, 3);
    if (count($p) !== 3) return null;
    [$iv, $salt, $enc] = $p;
    if (strlen($iv) !== 16 || $salt === '' || $enc === '') return null;

    // Form-encoding sırasında '+' → ' ' olmuş olabilir
    $enc = str_replace(' ', '+', $enc);

    $key   = hash('sha256', sha1($appSecret) . $salt);
    $plain = @openssl_decrypt($enc, 'aes-256-cbc', $key, 0, $iv);
    if ($plain === false || strpos($plain, '|') === false) return null;

    return explode('|', $plain);
}

/**
 * Çözülen hash, BU işlemle eşleşiyor mu?
 * Fatura no ve tutar (2 hane) birlikte bulunmalı. Parça sırasına bağlı değiliz.
 */
function qnb_hash_matches(?array $parts, array $pay): bool {
    if (!$parts) return false;
    $inv = false;
    $tot = false;
    foreach ($parts as $p) {
        $p = trim((string)$p);
        if ($p !== '' && hash_equals((string)$pay['invoice_id'], $p)) $inv = true;
        if (is_numeric($p) && abs((float)$p - (float)$pay['amount']) < 0.005) $tot = true;
    }
    return $inv && $tot;
}

/* ============================================================
 * YARDIMCILAR
 * ============================================================ */

function qnb_amount(float $a): string {
    return number_format($a, 2, '.', '');
}

function qnb_money(float $a): string {
    return number_format($a, 2, ',', '.') . ' ₺';
}

/** "1.250,50" / "1250.50" / "1250,5" → 1250.5 (geçersizse null) */
function qnb_parse_amount(string $s): ?float {
    $s = trim(str_replace(["\xC2\xA0", ' ', '₺', 'TL', 'tl'], '', $s));
    if ($s === '' || !preg_match('/^[0-9.,]+$/', $s)) return null;

    $hasDot = strpos($s, '.') !== false;
    $hasCom = strpos($s, ',') !== false;
    if ($hasDot && $hasCom) {
        // Son görülen ayraç ondalık kabul edilir
        if (strrpos($s, ',') > strrpos($s, '.')) { $s = str_replace('.', '', $s); $s = str_replace(',', '.', $s); }
        else { $s = str_replace(',', '', $s); }
    } elseif ($hasCom) {
        $s = (substr_count($s, ',') === 1) ? str_replace(',', '.', $s) : str_replace(',', '', $s);
    } elseif ($hasDot) {
        // Türkçe yazım: "12.500" / "1.250.000" binlik ayracıdır; "1250.50" ondalıktır.
        if (preg_match('/^[1-9]\d{0,2}(\.\d{3})+$/', $s)) $s = str_replace('.', '', $s);
        elseif (substr_count($s, '.') > 1) return null;   // "1.2.3" gibi belirsiz girdi
    }
    if (!is_numeric($s)) return null;
    return round((float)$s, 2);
}

/** "Ahmet Can Yılmaz" → ["Ahmet Can", "Yılmaz"] */
function qnb_split_name(string $full): array {
    $full = trim((string)preg_replace('/\s+/u', ' ', $full));
    $pos  = mb_strrpos($full, ' ', 0, 'UTF-8');
    if ($pos === false) return [$full, $full];
    return [mb_substr($full, 0, $pos, 'UTF-8'), mb_substr($full, $pos + 1, null, 'UTF-8')];
}

function qnb_new_invoice_id(): string {
    return 'TM' . date('ymd') . strtoupper(bin2hex(random_bytes(4)));   // ASCII, 16 karakter
}

/** Kart numarası benzeri bir değer gelirse ilk 6 + son 4 dışını maskeler. */
function qnb_mask_pan(string $s): string {
    $digits = preg_replace('/\D/', '', $s);
    if (strlen($digits) >= 13) {
        return substr($digits, 0, 6) . str_repeat('*', strlen($digits) - 10) . substr($digits, -4);
    }
    return substr((string)preg_replace('/[^0-9*Xx\- ]/', '', $s), 0, 32);
}

function qnb_status_label(string $st): string {
    return [
        'pending' => 'Bekliyor',
        'paid'    => 'Ödendi',
        'failed'  => 'Başarısız',
        'review'  => 'İnceleme Gerekli',
    ][$st] ?? $st;
}

function qnb_status_badge(string $st): string {
    $cls = ['paid' => 'badge-on', 'failed' => 'badge-danger', 'review' => 'badge-warn', 'pending' => 'badge-off'][$st] ?? 'badge-off';
    return '<span class="badge ' . $cls . '">' . h(qnb_status_label($st)) . '</span>';
}

/* ============================================================
 * ÖDEME KAYDI + QNBpay FORMU
 * ============================================================ */

/** Beklemede bir kayıt açar; id döner. */
function qnb_create_payment(array $d): int {
    $c = qnb_cfg();
    q("INSERT INTO tm_payments
         (invoice_id, public_ref, full_name, company, email, phone, description, amount, currency,
          installments, status, pos_mode, ip_address, user_agent)
       VALUES (?,?,?,?,?,?,?,?, 'TRY', 1, 'pending', ?, ?, ?)",
      [
        $d['invoice_id'], bin2hex(random_bytes(16)),
        $d['full_name'], $d['company'] !== '' ? $d['company'] : null,
        $d['email'], $d['phone'],
        $d['description'] !== '' ? $d['description'] : null,
        qnb_amount((float)$d['amount']),
        $c['mode'], get_ip(), mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
      ]);
    return (int)db()->lastInsertId();
}

/**
 * Tarayıcının QNBpay'e POST edeceği alanlar (KART ALANLARI HARİÇ — onları tarayıcı ekler).
 * Alan adları QNBpay paySmart3D belgesine göre burada TEK YERDE tutulur.
 */
function qnb_build_form(array $pay): array {
    $c = qnb_cfg();
    $total = qnb_amount((float)$pay['amount']);
    [$name, $surname] = qnb_split_name((string)$pay['full_name']);
    $desc = trim((string)($pay['description'] ?? ''));
    $invDesc = mb_substr($desc !== '' ? $desc : 'Tekcan Metal online ödeme', 0, 250, 'UTF-8');

    $items = json_encode([[
        'name'        => 'Online Ödeme',
        'price'       => $total,
        'quantity'    => 1,
        'description' => mb_substr($invDesc, 0, 100, 'UTF-8'),
    ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return [
        'action' => $c['base'] . '/api/paySmart3D',
        'fields' => [
            'merchant_key'        => $c['merchant_key'],
            'invoice_id'          => (string)$pay['invoice_id'],
            'invoice_description' => $invDesc,
            'total'               => $total,
            'currency_code'       => 'TRY',
            'installments_number' => '1',
            'name'                => $name,
            'surname'             => $surname,
            'items'               => $items,
            'bill_email'          => (string)$pay['email'],
            'bill_phone'          => (string)$pay['phone'],
            'return_url'          => url('odeme-sonuc.php'),
            'cancel_url'          => url('odeme-sonuc.php'),
            'hash_key'            => qnb_hash_payment($total, 1, 'TRY', $c['merchant_key'], (string)$pay['invoice_id'], $c['app_secret']),
        ],
    ];
}

/* ============================================================
 * SUNUCU → QNBpay: token (bağlantı testi)
 * ============================================================ */

function qnb_token(): array {
    $c = qnb_cfg();
    $url = $c['base'] . '/api/token';
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'http' => 0, 'message' => 'PHP cURL eklentisi yüklü değil.', 'url' => $url];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['app_id' => $c['app_id'], 'app_secret' => $c['app_secret']]),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['ok' => false, 'http' => $http, 'message' => 'Bağlantı hatası: ' . $err, 'url' => $url];
    }
    $j = json_decode((string)$body, true);
    $token = is_array($j) ? ($j['data']['token'] ?? '') : '';
    if ($token !== '') {
        return ['ok' => true, 'http' => $http, 'message' => 'Kimlik doğrulandı (token alındı).', 'url' => $url];
    }
    $msg = is_array($j) ? (string)($j['status_description'] ?? $j['message'] ?? '') : '';
    if ($msg === '') $msg = mb_substr(trim(strip_tags((string)$body)), 0, 200, 'UTF-8');
    return ['ok' => false, 'http' => $http, 'message' => 'Token alınamadı: ' . ($msg ?: 'boş yanıt'), 'url' => $url];
}

/* ============================================================
 * DÖNÜŞ İŞLEME (return_url / cancel_url)
 * ============================================================ */

/** Dönüş parametrelerinden yalnızca bilinen, kart içermeyen alanları alır. */
function qnb_pick_return(array $in): array {
    $keys = ['sipay_status', 'order_no', 'invoice_id', 'status_code', 'status_description',
             'transaction_type', 'payment_status', 'payment_method', 'sipay_payment_method',
             'error_code', 'error', 'md_status', 'original_bank_error_code',
             'original_bank_error_description', 'installments_number'];
    $out = [];
    foreach ($keys as $k) {
        if (isset($in[$k]) && is_scalar($in[$k])) {
            $out[$k] = mb_substr((string)$in[$k], 0, 300, 'UTF-8');
        }
    }
    return $out;
}

/**
 * Dönüşü işler ve kaydı günceller.
 *
 * Güvenlik kuralları:
 *  - "paid" yalnızca sipay_status=1 + geçerli hash_key + fatura/tutar eşleşmesi ile olur.
 *  - sipay_status=1 ama hash doğrulanamazsa "review" (para çekilmiş olabilir → elle kontrol).
 *  - Doğrulanmamış (sahte) bir "başarısız" dönüş gerçek bir başarıyı KİLİTLEYEMEZ:
 *    failed/review → paid geçişine izin verilir; paid asla geri alınmaz.
 *
 * @return array ['status'=>string, 'changed'=>bool, 'hash_ok'=>bool]
 */
function qnb_apply_return(array $pay, array $in): array {
    $cur = (string)$pay['status'];
    if ($cur === 'paid') {
        return ['status' => 'paid', 'changed' => false, 'hash_ok' => (bool)$pay['hash_valid']];
    }

    $c       = qnb_cfg();
    $parts   = qnb_hash_decode((string)($in['hash_key'] ?? ''), $c['app_secret']);
    $hashOk  = qnb_hash_matches($parts, $pay);
    $sipayOk = ((string)($in['sipay_status'] ?? '')) === '1';

    if ($sipayOk && $hashOk)      $new = 'paid';
    elseif ($sipayOk && !$hashOk) $new = 'review';
    else                          $new = 'failed';

    $allowedFrom = ['paid' => ['pending', 'failed', 'review'], 'review' => ['pending'], 'failed' => ['pending', 'failed']][$new];
    if (!in_array($cur, $allowedFrom, true)) {
        return ['status' => $cur, 'changed' => false, 'hash_ok' => $hashOk];
    }

    $picked = qnb_pick_return($in);
    $msg = '';
    foreach (['status_description', 'error', 'original_bank_error_description'] as $k) {
        if (!empty($picked[$k])) { $msg = $picked[$k]; break; }
    }
    if ($new === 'paid')   $msg = $msg !== '' ? $msg : 'Onaylandı';
    if ($new === 'review') $msg = 'Banka onayı geldi ancak hash/tutar doğrulanamadı — QNBpay panelinden kontrol edin.';
    if ($new === 'failed') $msg = $msg !== '' ? $msg : 'İşlem tamamlanamadı.';

    $picked['hash_ok'] = $hashOk ? 1 : 0;
    $raw = json_encode($picked, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ph  = implode(',', array_fill(0, count($allowedFrom), '?'));
    $sql = "UPDATE tm_payments
               SET status=?, order_no=?, gateway_code=?, gateway_message=?, md_status=?,
                   card_mask=?, hash_valid=?, raw_response=?" . ($new === 'paid' ? ', paid_at=NOW()' : '') . "
             WHERE id=? AND status IN ($ph)";
    $params = array_merge([
        $new,
        mb_substr((string)($picked['order_no'] ?? ''), 0, 64, 'UTF-8') ?: null,
        mb_substr((string)($picked['status_code'] ?? ''), 0, 30, 'UTF-8') ?: null,
        mb_substr($msg, 0, 500, 'UTF-8'),
        mb_substr((string)($picked['md_status'] ?? ''), 0, 10, 'UTF-8') ?: null,
        isset($in['credit_card_no']) && is_scalar($in['credit_card_no']) ? (qnb_mask_pan((string)$in['credit_card_no']) ?: null) : null,
        $hashOk ? 1 : 0,
        $raw,
        (int)$pay['id'],
    ], $allowedFrom);

    $st = q($sql, $params);
    return ['status' => $new, 'changed' => $st->rowCount() > 0 && $cur !== $new, 'hash_ok' => $hashOk];
}

/* ============================================================
 * BİLDİRİM E-POSTALARI (projenin mevcut @mail() yaklaşımı)
 * ============================================================ */

function qnb_mail(string $to, string $subject, string $body, ?string $replyTo = null): void {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return;
    $fromName  = str_replace(["\r", "\n"], '', (string)settings('mail_from_name', 'Tekcan Metal'));
    $fromEmail = (string)settings('mail_from_email', 'noreply@tekcanmetal.com');
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) $fromEmail = 'noreply@tekcanmetal.com';
    $headers = [
        'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: ' . $replyTo;
    @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
}

/** paid → yönetime + müşteriye; review → yalnızca yönetime. */
function qnb_notify(int $paymentId): void {
    $p = row("SELECT * FROM tm_payments WHERE id=?", [$paymentId]);
    if (!$p || !in_array($p['status'], ['paid', 'review'], true)) return;

    $c = qnb_cfg();
    $tag  = $p['pos_mode'] === 'test' ? ' [TEST]' : '';
    $amt  = qnb_money((float)$p['amount']);
    $site = settings('site_short_name', 'Tekcan Metal');

    $lines = [
        'Referans     : ' . $p['invoice_id'],
        'Banka İşlem  : ' . ($p['order_no'] ?: '—'),
        'Tutar        : ' . $amt,
        'Ad Soyad     : ' . $p['full_name'],
        'Firma        : ' . ($p['company'] ?: '—'),
        'E-posta      : ' . $p['email'],
        'Telefon      : ' . $p['phone'],
        'Açıklama     : ' . ($p['description'] ?: '—'),
        'Kart         : ' . ($p['card_mask'] ?: '—'),
        'Durum        : ' . qnb_status_label((string)$p['status']),
        'Mesaj        : ' . ($p['gateway_message'] ?: '—'),
        'Zaman        : ' . date('Y-m-d H:i:s'),
    ];

    if ($p['status'] === 'review') {
        qnb_mail($c['notify'], "[Sanal POS] İNCELEME GEREKLİ{$tag} — {$amt}",
            "Banka onayı geldi ancak doğrulama tamamlanamadı. QNBpay panelinden işlemi kontrol edip\n"
          . "yönetim panelinden (Sanal POS → Ödemeler) durumu elle güncelleyin.\n\n" . implode("\n", $lines) . "\n");
        return;
    }

    qnb_mail($c['notify'], "[Sanal POS] Ödeme alındı{$tag} — {$amt}",
        "Yeni online ödeme alındı.\n\n" . implode("\n", $lines) . "\n", (string)$p['email']);

    qnb_mail((string)$p['email'], "{$site} — Ödemeniz alındı ({$amt})",
        "Sayın {$p['full_name']},\n\n"
      . "{$amt} tutarındaki ödemeniz başarıyla alınmıştır.\n\n"
      . "Referans No : {$p['invoice_id']}\n"
      . "Tarih       : " . date('d.m.Y H:i') . "\n\n"
      . "Bu e-posta bilgilendirme amaçlıdır. Sorularınız için bizimle iletişime geçebilirsiniz.\n\n"
      . "{$site}\n" . url('') . "\n");
}
