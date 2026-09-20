<?php
define('TM_ADMIN', true);
$adminTitle = 'Sanal POS / Ödemeler';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';
require_once __DIR__ . '/../includes/qnbpay.php';

// Ödeme verisi ve POS kimlik bilgileri: yalnızca süper yönetici / yönetici
if (!in_array($adminUser['role'] ?? '', ['superadmin', 'admin'], true)) {
    adm_back_with('error', 'Bu sayfaya erişim yetkiniz yok.', 'admin/index.php');
}

qnb_ensure_schema();

$tab  = in_array($_GET['tab'] ?? '', ['payments', 'settings'], true) ? $_GET['tab'] : 'payments';
$self = 'admin/sanal-pos.php';

/* ============================================================
 * POST işlemleri
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $do = $_POST['do'] ?? '';

    /* ---- Ayarları kaydet ---- */
    if ($do === 'save_settings') {
        $errors = [];
        $enabled = isset($_POST['enabled']) ? '1' : '0';
        $mode    = ($_POST['mode'] ?? 'test') === 'live' ? 'live' : 'test';
        $appId   = trim((string)($_POST['app_id'] ?? ''));
        $mKey    = trim((string)($_POST['merchant_key'] ?? ''));
        $secretIn = trim((string)($_POST['app_secret'] ?? ''));
        $baseTest = trim((string)($_POST['base_url_test'] ?? ''));
        $baseLive = trim((string)($_POST['base_url_live'] ?? ''));
        $min     = qnb_parse_amount((string)($_POST['min_amount'] ?? '1'));
        $max     = qnb_parse_amount((string)($_POST['max_amount'] ?? '250000'));
        $notify  = trim((string)($_POST['notify_email'] ?? ''));

        $urlRe = '#^https://[a-z0-9.\-]+(:\d+)?(/[A-Za-z0-9._~\-/]*)?$#i';
        if ($baseTest !== '' && !preg_match($urlRe, $baseTest)) $errors[] = 'Test API adresi https:// ile başlamalı ve geçerli olmalı.';
        if ($baseLive !== '' && !preg_match($urlRe, $baseLive)) $errors[] = 'Canlı API adresi https:// ile başlamalı ve geçerli olmalı.';
        if ($min === null || $min <= 0)                          $errors[] = 'Minimum tutar geçersiz.';
        if ($max === null || ($min !== null && $max < $min))     $errors[] = 'Maksimum tutar, minimumdan küçük olamaz.';
        if ($notify !== '' && !filter_var($notify, FILTER_VALIDATE_EMAIL)) $errors[] = 'Bildirim e-postası geçersiz.';
        if (strlen($appId) > 255 || strlen($mKey) > 255 || strlen($secretIn) > 255) $errors[] = 'Kimlik bilgisi alanları çok uzun.';

        $secret = $secretIn !== '' ? $secretIn : (string)settings('qnbpay_app_secret', '');
        if ($enabled === '1' && ($appId === '' || $mKey === '' || $secret === '')) {
            $errors[] = 'Yayına almak için App ID, App Secret ve Merchant Key zorunludur.';
        }
        if ($enabled === '1' && $mode === 'live' && strpos((string)SITE_URL, 'https://') !== 0) {
            $errors[] = 'Canlı modda site adresi (SITE_URL) https:// olmalıdır.';
        }
        if ($errors) adm_back_with('error', implode(' ', $errors), $self . '?tab=settings');

        settings_set('qnbpay_enabled', $enabled, 'payment');
        settings_set('qnbpay_mode', $mode, 'payment');
        settings_set('qnbpay_app_id', $appId, 'payment');
        settings_set('qnbpay_merchant_key', $mKey, 'payment');
        if ($secretIn !== '') settings_set('qnbpay_app_secret', $secretIn, 'payment');
        settings_set('qnbpay_base_url_test', $baseTest, 'payment');
        settings_set('qnbpay_base_url_live', $baseLive, 'payment');
        settings_set('qnbpay_min_amount', qnb_amount($min), 'payment');
        settings_set('qnbpay_max_amount', qnb_amount($max), 'payment');
        settings_set('qnbpay_notify_email', $notify, 'payment');

        log_activity('update', 'sanal_pos', null, 'Sanal POS ayarları güncellendi (mod: ' . $mode . ', durum: ' . ($enabled === '1' ? 'açık' : 'kapalı') . ')');
        adm_back_with('success', 'Sanal POS ayarları kaydedildi.', $self . '?tab=settings');
    }

    /* ---- Bağlantı testi (kayıtlı ayarlarla token alır) ---- */
    if ($do === 'test') {
        $c = qnb_cfg();
        if ($c['app_id'] === '' || $c['app_secret'] === '') {
            adm_back_with('error', 'Önce App ID ve App Secret kaydedin.', $self . '?tab=settings');
        }
        $r = qnb_token();
        $host = parse_url($r['url'], PHP_URL_HOST) ?: '';
        adm_back_with($r['ok'] ? 'success' : 'error',
            'Bağlantı testi (' . $c['mode'] . ' / ' . $host . ', HTTP ' . $r['http'] . '): ' . $r['message'],
            $self . '?tab=settings');
    }

    /* ---- İnceleme/bekleyen kaydı elle sonuçlandır ---- */
    if ($do === 'resolve') {
        $id = (int)($_POST['id'] ?? 0);
        $to = $_POST['to'] ?? '';
        $p  = row("SELECT * FROM tm_payments WHERE id=?", [$id]);
        if (!$p || !in_array($to, ['paid', 'failed'], true) || !in_array($p['status'], ['review', 'pending'], true)) {
            adm_back_with('error', 'Bu kayıt elle güncellenemez.', $self);
        }
        $note = 'Manuel (' . ($adminUser['username'] ?? 'admin') . ', ' . date('d.m.Y H:i') . '): '
              . ($to === 'paid' ? 'ödeme QNBpay panelinden doğrulanarak onaylandı' : 'başarısız olarak işaretlendi');
        q("UPDATE tm_payments SET status=?, gateway_message=?" . ($to === 'paid' ? ', paid_at=NOW()' : '') . " WHERE id=?",
          [$to, mb_substr($note, 0, 500, 'UTF-8'), $id]);
        log_activity('update', 'payment', $id, 'Ödeme ' . $p['invoice_id'] . ' elle ' . ($to === 'paid' ? 'onaylandı' : 'reddedildi'));
        if ($to === 'paid') qnb_notify($id);
        adm_back_with('success', 'Kayıt güncellendi.', $self . '?view=' . $id);
    }
}

$cfg = qnb_cfg();
$statusOptions = ['pending', 'paid', 'failed', 'review'];
?>

<div class="adm-tabs">
  <a href="?tab=payments" class="<?= $tab === 'payments' ? 'active' : '' ?>">💳 Ödemeler</a>
  <a href="?tab=settings" class="<?= $tab === 'settings' ? 'active' : '' ?>">⚙ Ayarlar</a>
</div>

<?php if ($tab === 'settings'): ?>

  <?php
    $ready  = qnb_enabled();
    $secSet = $cfg['app_secret'] !== '';
  ?>
  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>QNBpay Durumu</h2>
      <span class="badge <?= $ready ? 'badge-on' : 'badge-off' ?>"><?= $ready ? 'Yayında (' . h($cfg['mode']) . ')' : 'Kapalı / eksik ayar' ?></span>
    </div>
    <div class="adm-panel-body">
      <p class="help" style="margin:0 0 10px">API adresi (<?= h($cfg['mode']) ?>): <code><?= h($cfg['base']) ?></code></p>
      <p class="help" style="margin:0 0 10px">QNBpay panelinde <strong>return / cancel URL</strong> olarak yetki vermeniz gerekirse: <code><?= h(url('odeme-sonuc.php')) ?></code></p>
      <?php if (strpos((string)SITE_URL, 'https://') !== 0): ?>
        <p style="margin:0 0 10px;color:#b45309"><strong>Uyarı:</strong> SITE_URL https:// ile başlamıyor. Ödeme kuruluşları https dönüş adresi ister; canlı mod kaydedilemez.</p>
      <?php endif; ?>
      <form method="post" style="display:inline">
        <?= csrf_field() ?><input type="hidden" name="do" value="test">
        <button type="submit" class="adm-btn adm-btn-ghost">🔌 Bağlantıyı Test Et (token al)</button>
      </form>
      <span class="help" style="margin-left:8px">Kayıtlı App ID / App Secret ile QNBpay'den token isteği yapar; ödeme oluşturmaz.</span>
    </div>
  </div>

  <form method="post" class="adm-form">
    <?= csrf_field() ?><input type="hidden" name="do" value="save_settings">
    <div class="adm-panel">
      <div class="adm-panel-head"><h2>Ayarlar</h2></div>
      <div class="adm-panel-body">
        <div class="row"><label class="checkbox"><input type="checkbox" name="enabled" <?= $cfg['enabled'] ? 'checked' : '' ?>> Online ödemeyi yayına al (menüde “Online Ödeme” görünür)</label></div>
        <div class="row">
          <label>Mod</label>
          <select name="mode" style="max-width:260px">
            <option value="test" <?= $cfg['mode'] === 'test' ? 'selected' : '' ?>>Test (gerçek para çekilmez)</option>
            <option value="live" <?= $cfg['mode'] === 'live' ? 'selected' : '' ?>>Canlı</option>
          </select>
        </div>
        <div class="row-2">
          <div class="row"><label>App ID</label><input type="text" name="app_id" value="<?= h($cfg['app_id']) ?>" autocomplete="off"></div>
          <div class="row"><label>Merchant Key</label><input type="text" name="merchant_key" value="<?= h($cfg['merchant_key']) ?>" autocomplete="off"></div>
        </div>
        <div class="row">
          <label>App Secret</label>
          <input type="password" name="app_secret" value="" autocomplete="new-password" placeholder="<?= $secSet ? '•••••••• (kayıtlı — değiştirmek için yeni değer girin)' : 'QNBpay panelindeki App Secret' ?>">
          <p class="help">Güvenlik için kayıtlı secret ekranda gösterilmez. Boş bırakırsanız mevcut değer korunur.</p>
        </div>
        <div class="row-2">
          <div class="row"><label>Min. tutar (₺)</label><input type="text" name="min_amount" value="<?= h(qnb_amount($cfg['min'])) ?>"></div>
          <div class="row"><label>Maks. tutar (₺)</label><input type="text" name="max_amount" value="<?= h(qnb_amount($cfg['max'])) ?>"></div>
        </div>
        <div class="row"><label>Bildirim e-postası</label><input type="text" name="notify_email" value="<?= h((string)settings('qnbpay_notify_email', '')) ?>" placeholder="Boşsa iletişim e-postası kullanılır: <?= h($cfg['notify']) ?>"></div>
        <details style="margin-top:10px">
          <summary style="cursor:pointer;font-weight:600">Gelişmiş: API adresi geçersiz kılma</summary>
          <p class="help">QNBpay size farklı bir adres verdiyse yazın. Boş bırakılırsa varsayılan kullanılır.</p>
          <div class="row"><label>Test API adresi</label><input type="text" name="base_url_test" value="<?= h((string)settings('qnbpay_base_url_test', '')) ?>" placeholder="<?= h(QNB_BASE_TEST) ?>"></div>
          <div class="row"><label>Canlı API adresi</label><input type="text" name="base_url_live" value="<?= h((string)settings('qnbpay_base_url_live', '')) ?>" placeholder="<?= h(QNB_BASE_LIVE) ?>"></div>
        </details>
      </div>
    </div>
    <div class="form-actions"><button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button></div>
  </form>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Canlıya Alma Kontrol Listesi</h2></div>
    <div class="adm-panel-body">
      <ol style="margin:0;padding-left:20px;line-height:1.8">
        <li>QNBpay üye iş yeri panelinden <strong>App ID, App Secret, Merchant Key</strong> değerlerini alın (test ve canlı ayrı olabilir).</li>
        <li>Mod <strong>Test</strong> iken kaydedin → <strong>Bağlantıyı Test Et</strong> başarılı olmalı.</li>
        <li>Yayına alıp <code>/odeme.php</code> üzerinden test kartıyla bir işlem yapın; sonuç “Ödendi” görünmeli.</li>
        <li>Başarısız ve iptal senaryolarını da deneyin (Ödemeler sekmesinde “Başarısız” görünmeli).</li>
        <li>Canlı bilgileri girip modu <strong>Canlı</strong> yapın; küçük tutarlı bir gerçek işlem deneyin.</li>
      </ol>
    </div>
  </div>

<?php else: /* ---------------- ÖDEMELER ---------------- */

  $view = (int)($_GET['view'] ?? 0);

  /* ---- Detay ---- */
  if ($view) {
    $p = row("SELECT * FROM tm_payments WHERE id=?", [$view]);
    if (!$p) adm_back_with('error', 'Kayıt bulunamadı.', $self);
    $raw = json_decode((string)$p['raw_response'], true);
?>
  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>Ödeme <?= h($p['invoice_id']) ?> <?= qnb_status_badge((string)$p['status']) ?><?= $p['pos_mode'] === 'test' ? ' <span class="badge badge-warn">TEST</span>' : '' ?></h2>
      <a href="<?= h(admin_url('sanal-pos.php')) ?>" class="adm-btn adm-btn-ghost">← Listeye dön</a>
    </div>
    <div class="adm-panel-body" style="padding:0">
      <table class="adm-table">
        <tbody>
          <tr><td style="width:220px">Tutar</td><td><strong><?= h(qnb_money((float)$p['amount'])) ?></strong> (<?= h($p['currency']) ?>, <?= (int)$p['installments'] ?> taksit)</td></tr>
          <tr><td>Ad Soyad</td><td><?= h($p['full_name']) ?></td></tr>
          <tr><td>Firma</td><td><?= h($p['company'] ?: '—') ?></td></tr>
          <tr><td>E-posta</td><td><a href="mailto:<?= h($p['email']) ?>"><?= h($p['email']) ?></a></td></tr>
          <tr><td>Telefon</td><td><?= h($p['phone']) ?></td></tr>
          <tr><td>Açıklama</td><td><?= h($p['description'] ?: '—') ?></td></tr>
          <tr><td>Banka işlem no</td><td><?= h($p['order_no'] ?: '—') ?></td></tr>
          <tr><td>Kart (maskeli)</td><td><?= h($p['card_mask'] ?: '—') ?></td></tr>
          <tr><td>Ağ geçidi mesajı</td><td><?= h($p['gateway_message'] ?: '—') ?> <?= $p['gateway_code'] ? '<code>' . h($p['gateway_code']) . '</code>' : '' ?></td></tr>
          <tr><td>3D / hash doğrulama</td><td>md_status: <?= h($p['md_status'] ?: '—') ?> · hash: <?= $p['hash_valid'] ? '✓ geçerli' : '✗ doğrulanamadı' ?></td></tr>
          <tr><td>IP</td><td><?= h($p['ip_address'] ?: '—') ?></td></tr>
          <tr><td>Oluşturma</td><td><?= h($p['created_at']) ?></td></tr>
          <tr><td>Ödeme zamanı</td><td><?= h($p['paid_at'] ?: '—') ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($raw): ?>
  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Ağ geçidi yanıtı (kart verisi içermez)</h2></div>
    <div class="adm-panel-body"><pre style="margin:0;white-space:pre-wrap;font-size:12px"><?= h(json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre></div>
  </div>
  <?php endif; ?>

  <?php if (in_array($p['status'], ['review', 'pending'], true)): ?>
  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Elle Sonuçlandır</h2></div>
    <div class="adm-panel-body">
      <p class="help">İşlemi <strong>QNBpay üye iş yeri panelinden</strong> kontrol ettikten sonra kullanın. “Ödendi” seçilirse müşteriye onay e-postası gider.</p>
      <form method="post" style="display:inline" onsubmit="return confirm('Bu işlemi ÖDENDİ olarak onaylamak istediğinize emin misiniz? QNBpay panelinde tahsilatı doğruladınız mı?')">
        <?= csrf_field() ?><input type="hidden" name="do" value="resolve"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="to" value="paid">
        <button type="submit" class="adm-btn adm-btn-primary">✓ Ödendi olarak onayla</button>
      </form>
      <form method="post" style="display:inline" onsubmit="return confirm('Bu işlemi BAŞARISIZ olarak işaretlemek istediğinize emin misiniz?')">
        <?= csrf_field() ?><input type="hidden" name="do" value="resolve"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="to" value="failed">
        <button type="submit" class="adm-btn adm-btn-danger">✕ Başarısız işaretle</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

<?php
  } else {
    /* ---- Liste ---- */
    $fStatus = in_array($_GET['status'] ?? '', $statusOptions, true) ? $_GET['status'] : '';
    $fQ      = trim((string)($_GET['q'] ?? ''));
    $page    = max(1, (int)($_GET['p'] ?? 1));
    $per     = 25;
    $offset  = ($page - 1) * $per;

    $where = []; $params = [];
    if ($fStatus !== '') { $where[] = 'status=?'; $params[] = $fStatus; }
    if ($fQ !== '') {
        $like = '%' . addcslashes($fQ, '%_\\') . '%';
        $where[] = '(invoice_id LIKE ? OR full_name LIKE ? OR email LIKE ? OR order_no LIKE ? OR company LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int)val("SELECT COUNT(*) FROM tm_payments $w", $params);
    $rows  = all("SELECT * FROM tm_payments $w ORDER BY id DESC LIMIT " . (int)$per . " OFFSET " . (int)$offset, $params);
    $pages = max(1, (int)ceil($total / $per));

    $sToday = (float)val("SELECT COALESCE(SUM(amount),0) FROM tm_payments WHERE status='paid' AND pos_mode='live' AND DATE(paid_at)=CURDATE()");
    $sMonth = (float)val("SELECT COALESCE(SUM(amount),0) FROM tm_payments WHERE status='paid' AND pos_mode='live' AND YEAR(paid_at)=YEAR(CURDATE()) AND MONTH(paid_at)=MONTH(CURDATE())");
    $sRev   = (int)val("SELECT COUNT(*) FROM tm_payments WHERE status='review'");
    $sPaid  = (int)val("SELECT COUNT(*) FROM tm_payments WHERE status='paid' AND pos_mode='live'");

    $qs = function (array $extra = []) use ($fStatus, $fQ) {
        return '?' . http_build_query(array_filter(array_merge(['tab' => 'payments', 'status' => $fStatus, 'q' => $fQ], $extra), fn($v) => $v !== '' && $v !== null));
    };
?>
  <div class="adm-stat-grid">
    <div class="adm-stat"><div class="adm-stat-label">Bugün (canlı, ödendi)</div><div class="adm-stat-value"><?= h(qnb_money($sToday)) ?></div></div>
    <div class="adm-stat"><div class="adm-stat-label">Bu ay (canlı, ödendi)</div><div class="adm-stat-value"><?= h(qnb_money($sMonth)) ?></div></div>
    <div class="adm-stat <?= $sRev ? 'danger' : '' ?>"><div class="adm-stat-label">İnceleme bekleyen</div><div class="adm-stat-value"><?= $sRev ?></div></div>
    <div class="adm-stat"><div class="adm-stat-label">Toplam başarılı (canlı)</div><div class="adm-stat-value"><?= $sPaid ?></div></div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>Ödemeler (<?= $total ?>)</h2>
      <form method="get" style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="hidden" name="tab" value="payments">
        <select name="status" onchange="this.form.submit()">
          <option value="">Tüm durumlar</option>
          <?php foreach ($statusOptions as $s): ?><option value="<?= h($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= h(qnb_status_label($s)) ?></option><?php endforeach; ?>
        </select>
        <input type="text" name="q" value="<?= h($fQ) ?>" placeholder="Referans, ad, e-posta, işlem no…">
        <button type="submit" class="adm-btn adm-btn-ghost">Ara</button>
      </form>
    </div>
    <div class="adm-panel-body" style="padding:0">
      <?php if (!$rows): ?>
        <div class="adm-empty"><div class="ico">💳</div>Henüz ödeme kaydı yok.</div>
      <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Tarih</th><th>Referans</th><th>Müşteri</th><th>Tutar</th><th>Durum</th><th>Banka işlem no</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td style="white-space:nowrap"><?= h(tr_date($r['created_at'], true)) ?></td>
            <td><code style="font-size:11px"><?= h($r['invoice_id']) ?></code><?= $r['pos_mode'] === 'test' ? ' <span class="badge badge-warn">TEST</span>' : '' ?></td>
            <td><strong><?= h($r['full_name']) ?></strong><?= $r['company'] ? '<br><span style="opacity:.6;font-size:12px">' . h($r['company']) . '</span>' : '' ?></td>
            <td style="white-space:nowrap"><strong><?= h(qnb_money((float)$r['amount'])) ?></strong></td>
            <td><?= qnb_status_badge((string)$r['status']) ?></td>
            <td><?= h($r['order_no'] ?: '—') ?></td>
            <td class="actions"><a href="?view=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Detay</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php if ($pages > 1): ?>
        <div style="padding:14px;display:flex;gap:6px;flex-wrap:wrap;align-items:center">
          <?php if ($page > 1): ?><a class="adm-btn adm-btn-sm adm-btn-ghost" href="<?= h($qs(['p' => $page - 1])) ?>">‹ Önceki</a><?php endif; ?>
          <span class="help">Sayfa <?= $page ?> / <?= $pages ?></span>
          <?php if ($page < $pages): ?><a class="adm-btn adm-btn-sm adm-btn-ghost" href="<?= h($qs(['p' => $page + 1])) ?>">Sonraki ›</a><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
<?php } ?>

<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
