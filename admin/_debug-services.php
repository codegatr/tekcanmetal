<?php
/**
 * v1.0.50 — Geçici tanı sayfası
 * tm_services tablosunda 3 hizmetin durumunu gösterir.
 * Yunus tarafından açılması için: /admin/_debug-services.php?key=DEBUG-2026
 *
 * KULLANIM SONRASI BU DOSYAYI SİL!
 */
session_start();
require_once __DIR__ . '/../includes/db.php';

if (($_GET['key'] ?? '') !== 'DEBUG-2026') {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html><head><title>Service Debug</title>
<style>
body{font-family:monospace;padding:20px;background:#0d1117;color:#e6edf3}
table{border-collapse:collapse;width:100%;margin:20px 0;font-size:12px}
th,td{padding:8px;border:1px solid #30363d;text-align:left;vertical-align:top;max-width:400px;word-wrap:break-word}
th{background:#161b22;color:#7ee787}
.ok{color:#7ee787} .err{color:#f85149} .dim{color:#7d8590}
h2{color:#79c0ff;border-bottom:1px solid #30363d;padding-bottom:8px;margin-top:30px}
pre{background:#161b22;padding:12px;overflow-x:auto;font-size:11px;color:#a5d6ff}
</style></head><body>

<h1>🔍 tm_services Debug</h1>
<p class="dim">Migration sonrası 3 hizmet durumu</p>

<h2>1. Tüm hizmet listesi</h2>
<?php
$rows = all("SELECT id, slug, title, LENGTH(short_desc) AS sd_len, LENGTH(description) AS desc_len, LENGTH(features) AS feat_len, LENGTH(specs) AS spec_len, is_active, sort_order FROM tm_services ORDER BY sort_order");
?>
<table>
<tr><th>ID</th><th>Slug</th><th>Title</th><th>short_desc len</th><th>description len</th><th>features len</th><th>specs len</th><th>active</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td class="ok"><?= h($r['slug']) ?></td>
<td><?= h($r['title']) ?></td>
<td><?= $r['sd_len'] ?: '<span class="err">NULL</span>' ?></td>
<td><?= $r['desc_len'] ?: '<span class="err">NULL</span>' ?></td>
<td><?= $r['feat_len'] ?: '<span class="err">NULL</span>' ?></td>
<td><?= $r['spec_len'] ?: '<span class="err">NULL</span>' ?></td>
<td><?= $r['is_active'] ? 'evet' : '<span class="err">HAYIR</span>' ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>2. Slug Bazlı Detay (ilk 200 karakter)</h2>
<?php
foreach (['lazer-kesim', 'oksijen-kesim', 'dekoratif-saclar'] as $slug) {
    $s = row("SELECT * FROM tm_services WHERE slug=?", [$slug]);
    echo '<h3>📌 slug = ' . htmlspecialchars($slug) . '</h3>';
    if (!$s) {
        echo '<p class="err">❌ BU SLUG İLE KAYIT YOK</p>';
        continue;
    }
    echo '<table>';
    foreach (['id','slug','title','short_desc','meta_title','meta_desc','is_active'] as $col) {
        $val = $s[$col] ?? '';
        if (strlen($val) > 200) $val = substr($val, 0, 200) . '... [+' . (strlen($s[$col]) - 200) . ' kr]';
        echo '<tr><th style="width:120px">' . $col . '</th><td>' . htmlspecialchars((string)$val) . '</td></tr>';
    }
    // description ilk 250 chr
    $desc = $s['description'] ?? '';
    echo '<tr><th>description</th><td><pre>' . htmlspecialchars(mb_substr($desc, 0, 250, 'UTF-8')) . '</pre></td></tr>';
    // features
    $feat = $s['features'] ?? '';
    echo '<tr><th>features</th><td><pre>' . htmlspecialchars(mb_substr($feat, 0, 200, 'UTF-8')) . '</pre></td></tr>';
    // specs
    $spec = $s['specs'] ?? '';
    echo '<tr><th>specs</th><td><pre>' . htmlspecialchars(mb_substr($spec, 0, 200, 'UTF-8')) . '</pre></td></tr>';
    echo '</table>';
}
?>

<h2>3. Aynılık Kontrolü</h2>
<?php
$d_lazer    = (string) val("SELECT description FROM tm_services WHERE slug='lazer-kesim'");
$d_oksijen  = (string) val("SELECT description FROM tm_services WHERE slug='oksijen-kesim'");
$d_dekoratif = (string) val("SELECT description FROM tm_services WHERE slug='dekoratif-saclar'");
?>
<table>
<tr><th>karşılaştırma</th><th>md5</th><th>uzunluk</th></tr>
<tr><td>lazer-kesim.description</td><td><?= md5($d_lazer) ?></td><td><?= strlen($d_lazer) ?> byte</td></tr>
<tr><td>oksijen-kesim.description</td><td><?= md5($d_oksijen) ?></td><td><?= strlen($d_oksijen) ?> byte</td></tr>
<tr><td>dekoratif-saclar.description</td><td><?= md5($d_dekoratif) ?></td><td><?= strlen($d_dekoratif) ?> byte</td></tr>
</table>
<p>
<?php if (md5($d_lazer) === md5($d_oksijen) && md5($d_oksijen) === md5($d_dekoratif)): ?>
  <span class="err">🚨 SORUN: 3 description birbirinin AYNI! Migration UPDATE'leri çakışmış olmalı.</span>
<?php elseif (md5($d_lazer) === md5($d_oksijen)): ?>
  <span class="err">🚨 Lazer = Oksijen aynı içerik</span>
<?php else: ?>
  <span class="ok">✓ 3 description farklı içeriğe sahip</span>
<?php endif; ?>
</p>

<h2>4. Cloudflare Cache Test</h2>
<p class="dim">Eğer DB doğru ama tarayıcıda aynı görünüyorsa, Cloudflare cache temizlenmeli:</p>
<ol>
<li><a href="https://dash.cloudflare.com" target="_blank" style="color:#79c0ff">Cloudflare Dashboard</a> aç</li>
<li>tekcanmetal.com domain'i seç</li>
<li>Sol menü → <strong>Caching → Configuration</strong></li>
<li><strong>Purge Everything</strong> tıkla</li>
<li>Tekrar test et: tarayıcıda <kbd>Ctrl+Shift+R</kbd></li>
</ol>

<h2>5. Browser Cache Test</h2>
<p>Browser cache da olabilir. Tarayıcıda gizli/incognito sekmede aç:</p>
<ul>
<li><a href="<?= h(url('hizmet.php?slug=lazer-kesim&v=' . time())) ?>" target="_blank">Lazer Kesim (cache-bust)</a></li>
<li><a href="<?= h(url('hizmet.php?slug=oksijen-kesim&v=' . time())) ?>" target="_blank">Oksijen Kesim (cache-bust)</a></li>
<li><a href="<?= h(url('hizmet.php?slug=dekoratif-saclar&v=' . time())) ?>" target="_blank">Dekoratif Sac (cache-bust)</a></li>
</ul>

<p class="dim" style="margin-top:40px;border-top:1px solid #30363d;padding-top:20px">
⚠ <strong>Bu dosyayı kullanım sonrası siliniz:</strong> /admin/_debug-services.php
</p>

</body></html>
