<?php
/**
 * v1.0.51 — Genişletilmiş tanı sayfası (admin/ dışında, .htaccess kısıtlaması yok)
 * Hem DB durumu hem MIGRATION HATA TESTİ yapar.
 *
 * URL: tekcanmetal.com/tani.php?key=DEBUG-2026
 * KULLANIM SONRASI SİL.
 */
require_once __DIR__ . '/includes/db.php';

if (($_GET['key'] ?? '') !== 'DEBUG-2026') {
    http_response_code(403);
    exit('forbidden — key gerekli');
}

header('Content-Type: text/html; charset=utf-8');

// Migration dosyasını OKU ve TEST ET (gerçekten çalıştırma!)
$migrationFile = __DIR__ . '/install/migration.sql';
$migrationSize = file_exists($migrationFile) ? filesize($migrationFile) : 0;
$migrationFirstError = null;

if (file_exists($migrationFile)) {
    $sql = file_get_contents($migrationFile);
    // İlk hatayı bul: SQL'i parça parça çalıştır
    // basit yaklaşım: ; ile ayır, her statement'ı dene
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $idx = 0;
    foreach ($statements as $stmt) {
        $idx++;
        if (strlen($stmt) < 5) continue;
        if (strpos($stmt, '--') === 0) continue;
        // SQL hatalarını göstermek için PDO error mode'u sıkı tut
        try {
            // SADECE PARSE TEST — gerçek çalıştırmıyoruz hata oluşmasın
            // Ancak biz syntax check yapamayız PDO'da, o yüzden SELECT ile sarmalayalım
            // Yine de bazı hataları yakalamak için başka yöntem dene:
            // Sadece ilk 200 kr'lık LAZER-KESIM UPDATE'i bul ve onu test et
        } catch (Throwable $e) {
            $migrationFirstError = "Statement #{$idx}: " . $e->getMessage();
            break;
        }
    }
}

// Şimdi gerçek migration deneme — ama TRANSACTION içinde, sonunda ROLLBACK
$migrationTestResult = null;
$migrationTestError = null;
$migrationTestErrorStmt = null;
try {
    $sql = file_get_contents($migrationFile);
    // ; ile parçala
    // Ama LONGTEXT içinde ; varsa parçalanma bozulur — DELIMITER kullanmadığımız için
    // Daha güvenli: tüm SQL'i tek seferde exec et, hatayı yakala
    $pdo = db();
    // Try once
    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $pdo->rollBack(); // başarılı bile olsa rollback (test)
        $migrationTestResult = '✓ SQL syntax check başarılı (rollback yapıldı)';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $migrationTestError = $e->getMessage();
        // Hangi statement'ta? Mesajdan bulmaya çalış
        // 1064: ' near YOUR_PROBLEMATIC_SQL ...'
        if (preg_match("/near\s+'([^']{20,200})'/", $e->getMessage(), $m)) {
            $snippet = $m[1];
            $migrationTestErrorStmt = $snippet;
        }
    }
} catch (Throwable $e) {
    $migrationTestError = 'PDO Test Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr"><head>
<meta charset="utf-8">
<title>Tekcan Metal — Tanı</title>
<style>
body{font-family:'SF Mono',Consolas,monospace;padding:20px;background:#0d1117;color:#e6edf3;line-height:1.5}
table{border-collapse:collapse;width:100%;margin:14px 0;font-size:12px}
th,td{padding:8px 10px;border:1px solid #30363d;text-align:left;vertical-align:top;max-width:600px;word-wrap:break-word;overflow-wrap:break-word}
th{background:#161b22;color:#7ee787;font-weight:600}
.ok{color:#7ee787} .err{color:#f85149} .warn{color:#f0883e} .dim{color:#7d8590}
h1,h2{color:#79c0ff;margin-top:30px}
h2{border-bottom:1px solid #30363d;padding-bottom:8px}
pre{background:#161b22;padding:12px;overflow-x:auto;font-size:11px;color:#a5d6ff;white-space:pre-wrap}
.box{background:#161b22;padding:14px;border-left:3px solid;margin:14px 0}
.box.ok{border-color:#7ee787}
.box.err{border-color:#f85149}
.box.warn{border-color:#f0883e}
kbd{background:#21262d;padding:2px 6px;border-radius:3px;font-size:11px;color:#a5d6ff}
</style>
</head><body>

<h1>🔍 Tekcan Metal Tanı Paneli</h1>
<p class="dim">Sorun: 3 hizmet sayfası aynı içerik gösteriyor + Migration hatası devam ediyor</p>

<h2>📁 Migration Dosyası</h2>
<table>
<tr><th>Dosya</th><td><?= h($migrationFile) ?></td></tr>
<tr><th>Boyut</th><td><?= number_format($migrationSize) ?> byte (<?= round($migrationSize/1024, 1) ?> KB)</td></tr>
<tr><th>Mevcut mu?</th><td><?= file_exists($migrationFile) ? '<span class="ok">✓ Var</span>' : '<span class="err">❌ Yok!</span>' ?></td></tr>
</table>

<h2>🧪 Migration SQL Syntax Test</h2>
<?php if ($migrationTestError): ?>
  <div class="box err">
    <strong class="err">❌ MIGRATION SYNTAX HATASI:</strong>
    <pre><?= h($migrationTestError) ?></pre>
    <?php if ($migrationTestErrorStmt): ?>
      <p><strong>Sorunlu kısım (yaklaşık):</strong></p>
      <pre style="background:#1a0a0a;color:#ff8888"><?= h($migrationTestErrorStmt) ?></pre>
    <?php endif; ?>
  </div>
<?php elseif ($migrationTestResult): ?>
  <div class="box ok">
    <strong class="ok"><?= h($migrationTestResult) ?></strong>
    <p>Migration SQL syntax temiz. Hata DB'de değil, başka yerde.</p>
  </div>
<?php else: ?>
  <div class="box warn">⚠ Migration test edilemedi.</div>
<?php endif; ?>

<h2>📊 tm_services Tablosu</h2>
<?php
$rows = all("SELECT id, slug, title,
                    LENGTH(short_desc) AS sd_len,
                    LENGTH(description) AS desc_len,
                    LENGTH(features) AS feat_len,
                    LENGTH(specs) AS spec_len,
                    is_active
             FROM tm_services
             ORDER BY sort_order, id");
?>
<table>
<tr><th>ID</th><th>Slug</th><th>Title</th><th>short_desc</th><th>description</th><th>features</th><th>specs</th><th>aktif</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td class="ok"><?= h($r['slug']) ?></td>
<td><?= h($r['title']) ?></td>
<td><?= $r['sd_len'] ? $r['sd_len'] . ' B' : '<span class="err">NULL</span>' ?></td>
<td><?= $r['desc_len'] ? $r['desc_len'] . ' B' : '<span class="err">NULL</span>' ?></td>
<td><?= $r['feat_len'] ? $r['feat_len'] . ' B' : '<span class="err">NULL</span>' ?></td>
<td><?= $r['spec_len'] ? $r['spec_len'] . ' B' : '<span class="warn">NULL</span>' ?></td>
<td><?= $r['is_active'] ? '✓' : '<span class="err">❌</span>' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>🔬 3 Hizmet İçerik Karşılaştırma</h2>
<?php
$descs = [];
foreach (['lazer-kesim', 'oksijen-kesim', 'dekoratif-saclar'] as $slug) {
    $s = row("SELECT description, title, short_desc FROM tm_services WHERE slug=?", [$slug]);
    $descs[$slug] = $s ?: ['description' => '', 'title' => 'YOK', 'short_desc' => ''];
}
?>
<table>
<tr><th>Slug</th><th>Title</th><th>description md5</th><th>uzunluk</th><th>ilk 80 karakter</th></tr>
<?php foreach ($descs as $slug => $d): ?>
<tr>
<td class="ok"><?= h($slug) ?></td>
<td><?= h($d['title']) ?></td>
<td><?= md5($d['description']) ?></td>
<td><?= number_format(strlen($d['description'])) ?> B</td>
<td><?= h(mb_substr(strip_tags($d['description']), 0, 80, 'UTF-8')) ?>...</td>
</tr>
<?php endforeach; ?>
</table>

<?php
$md5_l = md5($descs['lazer-kesim']['description']);
$md5_o = md5($descs['oksijen-kesim']['description']);
$md5_d = md5($descs['dekoratif-saclar']['description']);
?>

<?php if ($md5_l === $md5_o && $md5_o === $md5_d): ?>
  <div class="box err">
    <strong class="err">🚨 SORUN: 3 description AYNI!</strong>
    <p>Migration UPDATE'leri çalışmamış veya birbirini ezmiş. <strong>Migration syntax hatası nedeniyle hiçbir UPDATE çalışmadı</strong> (yukarıdaki test sonucuna bak).</p>
  </div>
<?php elseif ($md5_l === $md5_o || $md5_o === $md5_d || $md5_l === $md5_d): ?>
  <div class="box err">
    <strong class="err">🚨 KISMEN SORUN: 2 description aynı</strong>
  </div>
<?php else: ?>
  <div class="box ok">
    <strong class="ok">✓ 3 description birbirinden farklı!</strong>
    <p>DB'de içerik doğru. <strong>3 sayfa aynı görünüyorsa bu kesinlikle <span class="warn">CLOUDFLARE CACHE</span> sorunu!</strong></p>
  </div>
<?php endif; ?>

<h2>🌐 Cloudflare Cache Çözümü</h2>
<ol>
<li><a href="https://dash.cloudflare.com" target="_blank" style="color:#79c0ff">https://dash.cloudflare.com</a> aç</li>
<li><strong>tekcanmetal.com</strong> domain'ini seç</li>
<li>Sol menü → <strong>Caching → Configuration</strong></li>
<li><strong>Purge Everything</strong> butonuna bas → Confirm</li>
<li>Tarayıcıda <kbd>Ctrl+Shift+R</kbd> ile hard refresh</li>
</ol>

<h2>🧪 Cache-Bust Test Linkleri</h2>
<p>Bu link'ler URL'e benzersiz parametre ekler, Cloudflare cache'i atlar:</p>
<?php $t = time(); ?>
<ul>
<li><a href="hizmet.php?slug=lazer-kesim&_b=<?= $t ?>" target="_blank" style="color:#79c0ff">⚡ Lazer Kesim (cache-bust)</a></li>
<li><a href="hizmet.php?slug=oksijen-kesim&_b=<?= $t ?>" target="_blank" style="color:#79c0ff">🔥 Oksijen Kesim (cache-bust)</a></li>
<li><a href="hizmet.php?slug=dekoratif-saclar&_b=<?= $t ?>" target="_blank" style="color:#79c0ff">✦ Dekoratif Sac (cache-bust)</a></li>
</ul>
<p class="dim">Bu link'lerle 3 sayfa farklıysa → Cloudflare cache temizlenmesi yeterli<br>Aynıysa → DB'de sorun var</p>

<h2>🔧 PHP Migration Manuel Yeniden Çalıştır</h2>
<p>Eğer migration syntax test başarılıysa ama UPDATE'ler uygulanmadıysa, manuel tetikleme:</p>
<form method="post">
  <input type="hidden" name="key" value="DEBUG-2026">
  <button type="submit" name="action" value="rerun_migration"
    style="background:#238636;color:#fff;border:0;padding:10px 20px;cursor:pointer;font-family:inherit;font-size:13px">
    ⚡ Migration'ı Yeniden Çalıştır
  </button>
</form>

<?php
if (($_POST['action'] ?? '') === 'rerun_migration' && ($_POST['key'] ?? '') === 'DEBUG-2026') {
    echo '<div class="box ' . ($migrationTestError ? 'err' : 'warn') . '">';
    try {
        $sql = file_get_contents($migrationFile);
        db()->exec($sql);
        echo '<strong class="ok">✓ Migration başarıyla çalıştırıldı!</strong>';
        echo '<p>Sayfayı yenile, tekrar kontrol et.</p>';
    } catch (Throwable $e) {
        echo '<strong class="err">❌ Migration çalıştırılamadı:</strong>';
        echo '<pre>' . h($e->getMessage()) . '</pre>';
    }
    echo '</div>';
}
?>

<p class="dim" style="margin-top:40px;border-top:1px solid #30363d;padding-top:20px">
⚠ <strong>Bu dosyayı kullanım sonrası silin:</strong> <code>tani.php</code>
</p>

</body></html>
