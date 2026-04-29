<?php
/**
 * Sistem Teşhisi & Acil Onarım
 *
 * Tüm tm_* tablolarındaki kayıt sayılarını gösterir ve
 * boş tabloları install/seed.php verisiyle YENİDEN doldurur (idempotent).
 *
 * INSERT pattern'leri install/install.php ile birebir aynıdır.
 */
declare(strict_types=1);

define('TM_ADMIN', true);
$adminTitle = 'Sistem Teşhisi & Acil Onarım';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

// Sadece superadmin
if (($adminUser['role'] ?? '') !== 'superadmin') {
    adm_back_with('error', 'Bu sayfa için süperadmin yetkisi gerekli.', 'admin/index.php');
}

$action = $_GET['action'] ?? 'home';

/* ========== Tablo sayım ========== */
function get_table_counts(): array {
    $tables = [
        'tm_settings'         => 'Ayarlar',
        'tm_users'            => 'Kullanıcılar',
        'tm_pages'            => 'Sayfalar',
        'tm_sliders'          => 'Slider',
        'tm_categories'       => 'Kategoriler',
        'tm_products'         => 'Ürünler',
        'tm_product_images'   => 'Ürün Görselleri',
        'tm_services'         => 'Hizmetler',
        'tm_team'             => 'Ekip',
        'tm_partners'         => 'Çözüm Ortakları',
        'tm_banks'            => 'Banka/IBAN',
        'tm_faq'              => 'SSS',
        'tm_blog_categories'  => 'Blog Kategorileri',
        'tm_blog_posts'       => 'Blog Yazıları',
        'tm_gallery_albums'   => 'Galeri Albümleri',
        'tm_gallery_images'   => 'Galeri Görselleri',
        'tm_contact_messages' => 'İletişim Mesajları',
        'tm_mail_orders'      => 'Mail Order',
        'tm_loyalty_members'  => 'Sadakat Üyeleri',
        'tm_system_versions'  => 'Sürüm Geçmişi',
        'tm_activity_logs'    => 'Aktivite Logları',
    ];
    $result = [];
    foreach ($tables as $t => $label) {
        try {
            $result[$t] = ['label' => $label, 'count' => (int)val("SELECT COUNT(*) FROM `$t`"), 'error' => null];
        } catch (\Throwable $e) {
            $result[$t] = ['label' => $label, 'count' => 0, 'error' => $e->getMessage()];
        }
    }
    return $result;
}

/* ========== Reseed boş tabloları ==========
 *  Pattern'ler install/install.php ile birebir aynı. */
function reseed_empty_tables(): array
{
    $log = [];
    $stats = ['inserted' => 0, 'errors' => 0];

    $seedFile = __DIR__ . '/../install/seed.php';
    if (!file_exists($seedFile)) {
        return ['log' => ['HATA: install/seed.php bulunamadı'], 'stats' => $stats];
    }

    /** @var array $seed */
    $seed = include $seedFile;
    if (!is_array($seed)) {
        return ['log' => ['HATA: seed.php array değil'], 'stats' => $stats];
    }

    $pdo = db();
    $counts = get_table_counts();

    $isEmpty = fn(string $t): bool => ($counts[$t]['count'] ?? 0) === 0 && empty($counts[$t]['error']);

    /* 1) Settings — her zaman INSERT IGNORE */
    if (isset($seed['settings'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_settings (setting_key, setting_value, group_name) VALUES (?,?,?)");
        $cnt = 0;
        foreach ($seed['settings'] as $s) {
            try { $stmt->execute([$s[0], $s[1], $s[2] ?? 'general']); if ($stmt->rowCount() > 0) $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "Settings: $cnt yeni anahtar eklendi (mevcutlar korundu)";
        $stats['inserted'] += $cnt;
    }

    /* 2) Categories */
    if ($isEmpty('tm_categories') && isset($seed['categories'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_categories (slug,name,short_desc,icon,sort_order,image,is_active) VALUES (?,?,?,?,?,?,1)");
        $cnt = 0;
        foreach ($seed['categories'] as $c) {
            try { $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5] ?? null]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Kategoriler: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Kategoriler: korundu (zaten dolu)";
    }

    /* 3) Products */
    if ($isEmpty('tm_products') && isset($seed['products'])) {
        $catIds = [];
        foreach ($pdo->query("SELECT id, slug FROM tm_categories")->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $catIds[$r['slug']] = (int)$r['id'];
        }
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_products (category_id,slug,name,short_desc,description,image,is_active) VALUES (?,?,?,?,?,?,1)");
        $cnt = 0;
        foreach ($seed['products'] as $p) {
            $cid = $catIds[$p[0]] ?? null;
            if (!$cid) continue;
            try { $stmt->execute([$cid, $p[1], $p[2], $p[3], $p[4] ?? null, $p[5] ?? null]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Ürünler: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Ürünler: korundu (zaten dolu)";
    }

    /* 4) Services */
    if ($isEmpty('tm_services') && isset($seed['services'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_services (slug,title,short_desc,description,icon,image,features,is_active,sort_order) VALUES (?,?,?,?,?,?,?,1,?)");
        $i = 1; $cnt = 0;
        foreach ($seed['services'] as $s) {
            try {
                $stmt->execute([$s['slug'], $s['title'], $s['short_desc'], $s['description'],
                                $s['icon'], $s['image'] ?? null, $s['features'], $i++]);
                $cnt++;
            } catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Hizmetler: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Hizmetler: korundu (zaten dolu)";
    }

    /* 5) Team */
    if ($isEmpty('tm_team') && isset($seed['team'])) {
        $stmt = $pdo->prepare("INSERT INTO tm_team (full_name,position,bio,photo,email,phone,sort_order,is_active) VALUES (?,?,?,?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['team'] as $t) {
            try { $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Ekip: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Ekip: korundu";
    }

    /* 6) Sliders */
    if ($isEmpty('tm_sliders') && isset($seed['sliders'])) {
        $stmt = $pdo->prepare("INSERT INTO tm_sliders (title,subtitle,description,image,link_text,link_url,sort_order,is_active) VALUES (?,?,?,?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['sliders'] as $s) {
            try {
                $stmt->execute([$s['title'], $s['subtitle'], $s['description'], $s['image'],
                                $s['link_text'], $s['link_url'], $i++]);
                $cnt++;
            } catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Slider: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Slider: korundu";
    }

    /* 7) FAQ */
    if ($isEmpty('tm_faq') && isset($seed['faq'])) {
        $stmt = $pdo->prepare("INSERT INTO tm_faq (category,question,answer,sort_order,is_active) VALUES (?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['faq'] as $f) {
            try { $stmt->execute([$f[0], $f[1], $f[2], $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ SSS: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  SSS: korundu";
    }

    /* 8) Partners */
    if ($isEmpty('tm_partners') && isset($seed['partners'])) {
        $stmt = $pdo->prepare("INSERT INTO tm_partners (name,website,description,logo,sort_order,is_active) VALUES (?,?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['partners'] as $p) {
            try { $stmt->execute([$p[0], $p[1], $p[2], $p[3] ?? null, $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Partnerler: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Partnerler: korundu";
    }

    /* 9) Banks */
    if ($isEmpty('tm_banks') && isset($seed['banks'])) {
        $stmt = $pdo->prepare("INSERT INTO tm_banks (bank_name,branch,iban,currency,logo,sort_order,is_active) VALUES (?,?,?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['banks'] as $b) {
            try { $stmt->execute([$b[0], $b[1], $b[2], $b[3], $b[4] ?? null, $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Banka/IBAN: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Banka/IBAN: korundu";
    }

    /* 10) Blog categories */
    if ($isEmpty('tm_blog_categories') && isset($seed['blog_categories'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_blog_categories (slug,name,description,sort_order,is_active) VALUES (?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['blog_categories'] as $bc) {
            try { $stmt->execute([$bc[0], $bc[1], $bc[2], $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Blog Kategorileri: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Blog Kategorileri: korundu";
    }

    /* 11) Gallery albums */
    if ($isEmpty('tm_gallery_albums') && isset($seed['gallery_albums'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_gallery_albums (slug,title,description,cover_image,sort_order,is_active) VALUES (?,?,?,?,?,1)");
        $i = 1; $cnt = 0;
        foreach ($seed['gallery_albums'] as $ga) {
            try { $stmt->execute([$ga[0], $ga[1], $ga[2], $ga[3] ?? null, $i++]); $cnt++; }
            catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "✓ Galeri Albümleri: $cnt kayıt eklendi";
        $stats['inserted'] += $cnt;
    } else {
        $log[] = "  Galeri: korundu";
    }

    /* 12) Pages — her zaman INSERT IGNORE (UNIQUE slug var) */
    if (isset($seed['pages'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO tm_pages (slug,title,content,is_active,sort_order) VALUES (?,?,?,1,?)");
        $cnt = 0;
        foreach ($seed['pages'] as $p) {
            try {
                $stmt->execute([$p['slug'], $p['title'], $p['content'] ?? '', $p['sort_order'] ?? 0]);
                if ($stmt->rowCount() > 0) $cnt++;
            } catch (\Throwable $e) { $stats['errors']++; }
        }
        $log[] = "Sayfalar: $cnt yeni eklendi (mevcutlar korundu)";
        $stats['inserted'] += $cnt;
    }

    return ['log' => $log, 'stats' => $stats];
}

/* ========== POST işlemleri ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if ($action === 'reseed') {
        if (empty($_POST['confirm_reseed'])) {
            adm_back_with('error', 'Onay kutusu işaretlenmedi.', 'admin/teshis.php');
        }

        try {
            @set_time_limit(300);
            $result = reseed_empty_tables();

            log_activity('reseed', 'system', 0, sprintf(
                'Boş tablolar yeniden seed edildi: %d kayıt, %d hata',
                $result['stats']['inserted'], $result['stats']['errors']
            ));

            $_SESSION['reseed_log'] = $result['log'];
            adm_back_with('success', sprintf(
                'Reseed tamamlandı: %d yeni kayıt eklendi, %d hata.',
                $result['stats']['inserted'], $result['stats']['errors']
            ), 'admin/teshis.php');

        } catch (\Throwable $e) {
            adm_back_with('error', 'Hata: ' . $e->getMessage(), 'admin/teshis.php');
        }
    }
}

/* ========== Render ========== */
$counts = get_table_counts();
$reseedLog = $_SESSION['reseed_log'] ?? null;
unset($_SESSION['reseed_log']);

$emptyCount = count(array_filter($counts, fn($c) => $c['count'] === 0 && empty($c['error'])));
$errorCount = count(array_filter($counts, fn($c) => !empty($c['error'])));
?>

<style>
  .diag-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;margin:14px 0}
  .diag-card{background:#fff;border:1px solid #e3e8ef;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px}
  .diag-card.empty{border-left:3px solid #c8102e;background:#fff5f5}
  .diag-card.has-data{border-left:3px solid #16a34a}
  .diag-card.error{border-left:3px solid #d97706;background:#fff7e6}
  .diag-label{font-size:13px;color:#1a1a1a;font-weight:600}
  .diag-table{font-size:10.5px;color:#666;font-family:ui-monospace,monospace;display:block;margin-top:2px}
  .diag-count{font-size:24px;font-weight:300;color:#1e4a9e;letter-spacing:-1px;line-height:1}
  .diag-card.empty .diag-count{color:#c8102e}
  .diag-card.has-data .diag-count{color:#16a34a}
  .diag-error-msg{font-size:11px;color:#d97706;margin-top:4px;font-family:ui-monospace,monospace;word-break:break-word}
  .reseed-warn{background:#fff7e6;border-left:3px solid #d97706;padding:14px 18px;font-size:13.5px;color:#7c2d12;margin:14px 0;line-height:1.6}
  .reseed-log{background:#0a1a3a;color:#cbd5e1;font-family:ui-monospace,monospace;font-size:12.5px;padding:14px 18px;line-height:1.7;white-space:pre-wrap;max-height:360px;overflow-y:auto}
  .summary{display:flex;gap:14px;margin:16px 0;flex-wrap:wrap}
  .summary span{padding:8px 14px;border:1px solid #e3e8ef;font-size:12.5px;background:#fff}
  .summary .empty-badge{border-left:3px solid #c8102e;color:#c8102e;font-weight:700}
  .summary .ok-badge{border-left:3px solid #16a34a;color:#16a34a;font-weight:700}
  .summary .err-badge{border-left:3px solid #d97706;color:#d97706;font-weight:700}
</style>

<?php if ($reseedLog): ?>
<div class="adm-panel">
    <div class="adm-panel-head"><h2>📋 Reseed Logu</h2></div>
    <div class="adm-panel-body">
        <div class="reseed-log"><?php
            foreach ($reseedLog as $line) echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n";
        ?></div>
    </div>
</div>
<?php endif; ?>

<div class="adm-panel">
    <div class="adm-panel-head"><h2>🔍 Sistem Teşhisi</h2></div>
    <div class="adm-panel-body">
        <p style="margin:0 0 14px;color:#666;font-size:14px">
            Tüm CMS tablolarındaki kayıt sayıları.
        </p>

        <div class="summary">
            <span class="ok-badge"><?= count($counts) - $emptyCount - $errorCount ?> tablo dolu</span>
            <span class="empty-badge"><?= $emptyCount ?> tablo boş</span>
            <?php if ($errorCount): ?>
            <span class="err-badge"><?= $errorCount ?> tablo hatalı</span>
            <?php endif; ?>
        </div>

        <div class="diag-grid">
            <?php foreach ($counts as $table => $info):
                $cls = !empty($info['error']) ? 'error' : ($info['count'] === 0 ? 'empty' : 'has-data');
            ?>
            <div class="diag-card <?= $cls ?>">
                <div style="flex:1;min-width:0">
                    <div class="diag-label"><?= htmlspecialchars($info['label']) ?></div>
                    <code class="diag-table"><?= htmlspecialchars($table) ?></code>
                    <?php if (!empty($info['error'])): ?>
                        <div class="diag-error-msg"><?= htmlspecialchars(substr($info['error'], 0, 100)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="diag-count"><?= $info['count'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($emptyCount > 0): ?>
<div class="adm-panel">
    <div class="adm-panel-head"><h2>🔧 Acil Onarım — Boş Tabloları Yeniden Doldur</h2></div>
    <div class="adm-panel-body">
        <p style="font-size:13.5px;color:#666;line-height:1.7">
            Bu işlem <strong>sadece yukarıda kırmızıyla işaretli boş tabloları</strong>
            install/seed.php verisiyle doldurur. Dolu tablolardaki veriler korunur.
            Settings tablosu için INSERT IGNORE kullanır (mevcut anahtarlar dokunulmaz).
        </p>

        <div class="reseed-warn">
            ⚠ <strong>Önce yedek alın:</strong>
            <a href="<?= h(url('admin/guncelleme.php')) ?>">Güncelleme Merkezi</a>'nden bir yedek alın.
            İşlem geri alınamaz.
        </div>

        <form method="post" action="?action=reseed">
            <?= csrf_field() ?>
            <label style="display:flex;align-items:center;gap:10px;margin:14px 0;padding:10px 14px;background:#fff5f5;border:1px solid #fecaca;font-size:13.5px;cursor:pointer">
                <input type="checkbox" name="confirm_reseed" value="1" required>
                <span><strong>Anlıyorum, sadece <?= $emptyCount ?> boş tablo install/seed.php verisiyle doldurulacak.</strong></span>
            </label>
            <button type="submit" class="adm-btn adm-btn-primary"
                    onclick="return confirm('Boş tabloları seed verisiyle doldurmak için emin misiniz?')">
                🔄 Boş Tabloları Doldur (<?= $emptyCount ?> tablo)
            </button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="adm-panel">
    <div class="adm-panel-body" style="text-align:center;padding:32px">
        <div style="font-size:48px;margin-bottom:8px">✅</div>
        <h3 style="color:#16a34a;font-size:18px;margin:0 0 6px">Tüm tablolar dolu</h3>
        <p style="color:#666;margin:0;font-size:13.5px">Reseed gerekmiyor.</p>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
