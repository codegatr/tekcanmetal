<?php
/**
 * Tekcan Metal — Kurulum Sihirbazı
 * 3 adımda kurulum: Veritabanı → Admin → Bitir
 */

// Daha önce kurulduysa engelle
if (file_exists(__DIR__ . '/../config.php')) {
    $existing = @file_get_contents(__DIR__ . '/../config.php');
    if ($existing && strpos($existing, "define('TM_INSTALLED', true)") !== false) {
        die('<!doctype html><meta charset="utf-8"><body style="font:15px/1.5 system-ui;max-width:560px;margin:60px auto;padding:30px;background:#fff;border:1px solid #e5e7eb;border-radius:12px"><h1 style="font-size:20px;margin:0 0 12px">⚠️ Sistem zaten kurulmuş</h1><p style="color:#475569">Yeniden kurulum için <code>config.php</code> dosyasını silin ve <code>install/</code> klasörüne tekrar girin.</p><p><a href="../" style="color:#1a2b4a;font-weight:600">← Anasayfaya dön</a></p>');
    }
}

session_start();
$step    = (int)($_GET['step'] ?? 1);
$errors  = [];
$success = '';

// ---- Adım 1: Veritabanı bağlantısı + tablolar ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {
    $db_host = trim($_POST['db_host'] ?? 'localhost');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';

    if (!$db_name || !$db_user) {
        $errors[] = 'Veritabanı adı ve kullanıcı adı zorunludur.';
    } else {
        try {
            $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ATTR_ERRMODE,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Şemayı çalıştır
            $schema = require __DIR__ . '/schema.php';
            $pdo->exec($schema);

            // Bağlantı bilgilerini sakla
            $_SESSION['tm_install'] = compact('db_host', 'db_name', 'db_user', 'db_pass');

            header('Location: install.php?step=2');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// ---- Adım 2: Admin oluştur + seed verileri yükle ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    if (empty($_SESSION['tm_install'])) {
        header('Location: install.php?step=1');
        exit;
    }

    $email     = trim($_POST['admin_email'] ?? '');
    $username  = trim($_POST['admin_username'] ?? '');
    $full_name = trim($_POST['admin_name'] ?? '');
    $password  = $_POST['admin_password'] ?? '';
    $password2 = $_POST['admin_password2'] ?? '';
    $site_url  = rtrim(trim($_POST['site_url'] ?? ''), '/');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta giriniz.';
    if (strlen($username) < 3)                       $errors[] = 'Kullanıcı adı en az 3 karakter olmalı.';
    if (strlen($password) < 6)                       $errors[] = 'Parola en az 6 karakter olmalı.';
    if ($password !== $password2)                    $errors[] = 'Parolalar eşleşmiyor.';
    if (!$full_name)                                 $errors[] = 'Ad-soyad boş olamaz.';
    if (!$site_url)                                  $errors[] = 'Site adresi boş olamaz.';

    if (!$errors) {
        try {
            $cfg = $_SESSION['tm_install'];
            $pdo = new PDO("mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4",
                $cfg['db_user'], $cfg['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $seed = require __DIR__ . '/seed.php';

            // 1) Settings
            $stmt = $pdo->prepare("INSERT INTO tm_settings (setting_key, setting_value, setting_group) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            foreach ($seed['settings'] as $s) $stmt->execute($s);

            // site_url'i de yaz
            $stmt->execute(['site_url', $site_url, 'system']);

            // 2) Pages
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_pages (slug,title,subtitle,content,meta_desc,sort_order,is_active) VALUES (?,?,?,?,?,?,1)");
            foreach ($seed['pages'] as $p) {
                $stmt->execute([
                    $p['slug'], $p['title'], $p['subtitle'] ?? null,
                    $p['content'] ?? null, $p['meta_desc'] ?? null, $p['sort_order'] ?? 0
                ]);
            }

            // 3) Categories
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_categories (slug,name,short_desc,icon,sort_order,image,is_active) VALUES (?,?,?,?,?,?,1)");
            foreach ($seed['categories'] as $c) {
                $stmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5] ?? null]);
            }

            // 4) Products
            $catIds = [];
            foreach ($pdo->query("SELECT id, slug FROM tm_categories")->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $catIds[$r['slug']] = (int)$r['id'];
            }
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_products (category_id,slug,name,short_desc,description,image,is_active) VALUES (?,?,?,?,?,?,1)");
            foreach ($seed['products'] as $p) {
                $cid = $catIds[$p[0]] ?? null;
                if ($cid) $stmt->execute([$cid, $p[1], $p[2], $p[3], $p[4] ?? null, $p[5] ?? null]);
            }

            // 5) Services
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_services (slug,title,short_desc,description,icon,image,features,is_active,sort_order) VALUES (?,?,?,?,?,?,?,1,?)");
            $i = 1;
            foreach ($seed['services'] as $s) {
                $stmt->execute([
                    $s['slug'], $s['title'], $s['short_desc'], $s['description'],
                    $s['icon'], $s['image'] ?? null, $s['features'], $i++
                ]);
            }

            // 6) Team
            $stmt = $pdo->prepare("INSERT INTO tm_team (full_name,position,bio,photo,email,phone,sort_order,is_active) VALUES (?,?,?,?,?,?,?,1)");
            $i = 1;
            foreach ($seed['team'] as $t) {
                $stmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $i++]);
            }

            // 7) Sliders
            $stmt = $pdo->prepare("INSERT INTO tm_sliders (title,subtitle,description,image,link_text,link_url,sort_order,is_active) VALUES (?,?,?,?,?,?,?,1)");
            $i = 1;
            foreach ($seed['sliders'] as $s) {
                $stmt->execute([
                    $s['title'], $s['subtitle'], $s['description'], $s['image'],
                    $s['link_text'], $s['link_url'], $i++
                ]);
            }

            // 8) FAQ
            $stmt = $pdo->prepare("INSERT INTO tm_faq (category,question,answer,sort_order,is_active) VALUES (?,?,?,?,1)");
            $i = 1;
            foreach ($seed['faq'] as $f) $stmt->execute([$f[0], $f[1], $f[2], $i++]);

            // 9) Partners
            $stmt = $pdo->prepare("INSERT INTO tm_partners (name,website,description,logo,sort_order,is_active) VALUES (?,?,?,?,?,1)");
            $i = 1;
            foreach ($seed['partners'] as $p) $stmt->execute([$p[0], $p[1], $p[2], $p[3] ?? null, $i++]);

            // 10) Banks
            $stmt = $pdo->prepare("INSERT INTO tm_banks (bank_name,branch,iban,currency,logo,sort_order,is_active) VALUES (?,?,?,?,?,?,1)");
            $i = 1;
            foreach ($seed['banks'] as $b) $stmt->execute([$b[0], $b[1], $b[2], $b[3], $b[4] ?? null, $i++]);

            // 11) Blog categories
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_blog_categories (slug,name,description,sort_order,is_active) VALUES (?,?,?,?,1)");
            $i = 1;
            foreach ($seed['blog_categories'] as $bc) $stmt->execute([$bc[0], $bc[1], $bc[2], $i++]);

            // 12) Gallery albums
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_gallery_albums (slug,title,description,cover_image,sort_order,is_active) VALUES (?,?,?,?,?,1)");
            $i = 1;
            foreach ($seed['gallery_albums'] as $ga) $stmt->execute([$ga[0], $ga[1], $ga[2], $ga[3] ?? null, $i++]);

            // 12.5) Seed görsellerini install/seed-images/'dan uploads/'a kopyala
            $seedImagesDir = __DIR__ . '/seed-images';
            $uploadsDir    = __DIR__ . '/../uploads';
            if (is_dir($seedImagesDir)) {
                $rii = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($seedImagesDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($rii as $file) {
                    $rel = ltrim(substr($file->getPathname(), strlen($seedImagesDir)), '/\\');
                    $dst = $uploadsDir . '/' . $rel;
                    if ($file->isDir()) {
                        if (!is_dir($dst)) @mkdir($dst, 0755, true);
                    } else {
                        @mkdir(dirname($dst), 0755, true);
                        if (!file_exists($dst)) @copy($file->getPathname(), $dst);
                    }
                }
            }

            // 13) Admin user
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO tm_users (email,username,password,full_name,role,is_active) VALUES (?,?,?,?,'superadmin',1)");
            $stmt->execute([$email, $username, $hashed, $full_name]);

            // 14) System version
            $stmt = $pdo->prepare("INSERT IGNORE INTO tm_system_versions (version,source,release_date,notes,applied_by) VALUES (?,?,NOW(),?,?)");
            $stmt->execute([$seed['version'], 'install', 'İlk kurulum', $full_name]);

            // 15) config.php yaz
            $cfg_content = "<?php\n";
            $cfg_content .= "// Tekcan Metal — Yapılandırma\n";
            $cfg_content .= "// Otomatik oluşturuldu: " . date('Y-m-d H:i:s') . "\n\n";
            $cfg_content .= "define('TM_INSTALLED', true);\n";
            $cfg_content .= "define('TM_VERSION', '" . $seed['version'] . "');\n\n";
            $cfg_content .= "// Veritabanı\n";
            $cfg_content .= "define('DB_HOST', " . var_export($cfg['db_host'], true) . ");\n";
            $cfg_content .= "define('DB_NAME', " . var_export($cfg['db_name'], true) . ");\n";
            $cfg_content .= "define('DB_USER', " . var_export($cfg['db_user'], true) . ");\n";
            $cfg_content .= "define('DB_PASS', " . var_export($cfg['db_pass'], true) . ");\n\n";
            $cfg_content .= "// Site\n";
            $cfg_content .= "define('SITE_URL', " . var_export($site_url, true) . ");\n\n";
            $cfg_content .= "// GitHub güncelleme\n";
            $cfg_content .= "define('GITHUB_REPO', 'codegatr/tekcanmetal');\n";
            $cfg_content .= "define('GITHUB_TOKEN', '');\n\n";
            $cfg_content .= "// Hata gösterimi\n";
            $cfg_content .= "define('TM_DEBUG', false);\n";
            $cfg_content .= "if (TM_DEBUG) { ini_set('display_errors','1'); error_reporting(E_ALL); }\n";

            file_put_contents(__DIR__ . '/../config.php', $cfg_content);

            unset($_SESSION['tm_install']);
            $_SESSION['install_complete'] = true;
            $_SESSION['admin_email'] = $email;

            header('Location: install.php?step=3');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Kurulum hatası: ' . $e->getMessage();
        }
    }
}

if ($step === 3 && empty($_SESSION['install_complete'])) {
    header('Location: install.php?step=1');
    exit;
}

?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tekcan Metal — Kurulum</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font:15px/1.6 'Inter',system-ui;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px 20px}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse at top,#1e3a5f 0%,#0f172a 60%);z-index:-1}
.wrap{max-width:680px;width:100%;background:#fff;color:#1e293b;border-radius:16px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.4)}
.head{background:linear-gradient(135deg,#1a2b4a 0%,#2d4a7a 100%);color:#fff;padding:32px 36px;border-bottom:3px solid #c9a961}
.brand{display:flex;align-items:center;gap:14px;margin-bottom:18px}
.brand-mark{width:44px;height:44px;background:#c9a961;color:#1a2b4a;display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond';font-weight:700;font-size:24px;border-radius:8px}
.brand-name{font-family:'Cormorant Garamond';font-weight:600;font-size:22px;letter-spacing:.5px}
.brand-sub{font-size:11px;opacity:.7;letter-spacing:2px;text-transform:uppercase;margin-top:2px}
h1{font-family:'Cormorant Garamond';font-weight:600;font-size:32px;line-height:1.2}
.head p{margin-top:6px;opacity:.8;font-size:14px}
.steps{display:flex;gap:8px;padding:18px 36px;background:#f8fafc;border-bottom:1px solid #e2e8f0}
.step{flex:1;display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:13px;color:#64748b}
.step.active{background:#1a2b4a;color:#fff}
.step.done{color:#047857}
.step .num{width:22px;height:22px;border-radius:50%;background:#e2e8f0;color:#475569;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px}
.step.active .num{background:#c9a961;color:#1a2b4a}
.step.done .num{background:#10b981;color:#fff}
.body{padding:36px}
.body h2{font-family:'Cormorant Garamond';font-weight:600;font-size:24px;margin-bottom:6px;color:#1a2b4a}
.body .lead{color:#64748b;margin-bottom:24px;font-size:14px}
label{display:block;margin-bottom:5px;font-weight:600;font-size:13px;color:#1e293b}
input[type=text],input[type=email],input[type=password],input[type=url]{width:100%;padding:11px 13px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-size:14px;color:#1e293b;background:#fff;transition:.15s}
input:focus{outline:0;border-color:#1a2b4a;box-shadow:0 0 0 3px rgba(26,43,74,.12)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{margin-bottom:16px}
.help{font-size:12px;color:#94a3b8;margin-top:5px}
.btn{display:inline-flex;align-items:center;gap:8px;background:#1a2b4a;color:#fff;border:0;padding:13px 28px;font:inherit;font-size:14px;font-weight:600;border-radius:8px;cursor:pointer;transition:.15s}
.btn:hover{background:#2d4a7a;transform:translateY(-1px)}
.btn-block{width:100%;justify-content:center}
.alert{padding:13px 16px;border-radius:8px;margin-bottom:18px;font-size:13px}
.alert-error{background:#fee2e2;color:#991b1b;border-left:3px solid #dc2626}
.alert-success{background:#d1fae5;color:#065f46;border-left:3px solid #10b981}
.box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px;margin-bottom:18px;font-size:13px;color:#475569}
.box strong{color:#1a2b4a}
.success-icon{width:64px;height:64px;background:#10b981;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 18px}
.text-center{text-align:center}
.tf{display:flex;gap:10px;margin-top:24px}
.tf a{flex:1;padding:13px;border-radius:8px;text-align:center;font-weight:600;font-size:14px;text-decoration:none;border:2px solid #1a2b4a}
.tf a.primary{background:#1a2b4a;color:#fff}
.tf a.secondary{color:#1a2b4a}
.tf a:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
@media (max-width:600px){.row{grid-template-columns:1fr}.body{padding:24px}.head{padding:24px}.steps{padding:14px 20px;gap:4px}.step{padding:6px 8px;font-size:12px}}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="brand">
      <div class="brand-mark">T</div>
      <div>
        <div class="brand-name">Tekcan Metal</div>
        <div class="brand-sub">CMS Kurulum</div>
      </div>
    </div>
    <h1>Kurulum Sihirbazı</h1>
    <p>3 adımda sisteminizi hazırlıyoruz</p>
  </div>

  <div class="steps">
    <div class="step <?= $step===1?'active':($step>1?'done':'') ?>"><span class="num">1</span> Veritabanı</div>
    <div class="step <?= $step===2?'active':($step>2?'done':'') ?>"><span class="num">2</span> Yönetici</div>
    <div class="step <?= $step===3?'active':'' ?>"><span class="num">3</span> Tamamlandı</div>
  </div>

  <div class="body">
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <?php if ($step === 1): ?>
      <h2>Veritabanı Bilgileri</h2>
      <p class="lead">DirectAdmin → MySQL Yönetimi’nden oluşturduğunuz veritabanı bilgilerini girin.</p>

      <form method="post">
        <div class="field">
          <label>MySQL Sunucusu</label>
          <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
          <div class="help">Genelde <code>localhost</code> olarak kalır.</div>
        </div>
        <div class="field">
          <label>Veritabanı Adı</label>
          <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" placeholder="hesapadi_tekcanmetal" required>
        </div>
        <div class="row">
          <div class="field">
            <label>Kullanıcı Adı</label>
            <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Parola</label>
            <input type="password" name="db_pass" value="">
          </div>
        </div>
        <button type="submit" class="btn btn-block">Bağlan & Tabloları Oluştur →</button>
      </form>

    <?php elseif ($step === 2): ?>
      <h2>Yönetici Hesabı</h2>
      <p class="lead">Admin paneline giriş için kullanacağınız hesabı oluşturun.</p>

      <form method="post">
        <div class="field">
          <label>Site Adresi</label>
          <input type="url" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? 'https://v2.tekcanmetal.com') ?>" required>
          <div class="help">Sondaki "/" karakteri olmadan.</div>
        </div>
        <div class="row">
          <div class="field">
            <label>Ad-Soyad</label>
            <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Kullanıcı Adı</label>
            <input type="text" name="admin_username" value="<?= htmlspecialchars($_POST['admin_username'] ?? '') ?>" required>
          </div>
        </div>
        <div class="field">
          <label>E-Posta</label>
          <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" required>
        </div>
        <div class="row">
          <div class="field">
            <label>Parola</label>
            <input type="password" name="admin_password" required minlength="6">
          </div>
          <div class="field">
            <label>Parola (Tekrar)</label>
            <input type="password" name="admin_password2" required minlength="6">
          </div>
        </div>
        <button type="submit" class="btn btn-block">Kurulumu Tamamla →</button>
      </form>

    <?php else: ?>
      <div class="text-center">
        <div class="success-icon">✓</div>
        <h2>Kurulum Başarılı!</h2>
        <p class="lead">Tekcan Metal CMS başarıyla kuruldu. Artık admin paneline giriş yapabilirsiniz.</p>
      </div>

      <div class="box">
        <strong>⚠️ ÖNEMLİ — Güvenlik:</strong> Kurulum tamamlandı.
        <strong>install/</strong> klasörünü FTP ya da DirectAdmin File Manager üzerinden mutlaka silin veya yeniden adlandırın.
      </div>

      <div class="box">
        <strong>📌 Sonraki Adımlar:</strong>
        <ul style="margin:10px 0 0 18px">
          <li>Admin paneline giriş yapıp ayarları kontrol edin</li>
          <li>Logo ve görselleri Site Ayarları’ndan yükleyin</li>
          <li>SMTP ayarlarını yapın (Mail bölümü)</li>
          <li>İlk ürün/blog yazısı/galeri içeriklerini ekleyin</li>
        </ul>
      </div>

      <div class="tf">
        <a href="../" class="secondary">Siteyi Görüntüle</a>
        <a href="../admin/" class="primary">Admin Paneline Git →</a>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
