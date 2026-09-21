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
        $merchId = trim((string)($_POST['merchant_id'] ?? ''));
        $appId   = trim((string)($_POST['app_id'] ?? ''));
        $mKey    = trim((string)($_POST['merchant_key'] ?? ''));
        $secretIn = trim((string)($_POST['app_secret'] ?? ''));
        $baseTest = trim((string)($_POST['base_url_test'] ?? ''));
        $baseLive = trim((string)($_POST['base_url_live'] ?? ''));
        $min     = qnb_parse_amount((string)($_POST['min_amount'] ?? '1'));
        $max     = qnb_parse_amount((string)($_POST['max_amount'] ?? '250000'));
        $notify  = trim((string)($_POST['notify_email'] ?? ''));
        $hoursOn = isset($_POST['hours_enabled']) ? '1' : '0';
        $openT   = trim((string)($_POST['open_time'] ?? '07:00'));
        $closeT  = trim((string)($_POST['close_time'] ?? '23:00'));

        $urlRe = '#^https://[a-z0-9.\-]+(:\d+)?(/[A-Za-z0-9._~\-/]*)?$#i';
        if ($baseTest !== '' && !preg_match($urlRe, $baseTest)) $errors[] = 'Test Sunucu adresi https:// ile başlamalı ve geçerli olmalı.';
        if ($baseLive !== '' && !preg_match($urlRe, $baseLive)) $errors[] = 'Canlı Sunucu adresi https:// ile başlamalı ve geçerli olmalı.';
        if ($min === null || $min <= 0)                          $errors[] = 'Minimum tutar geçersiz.';
        if ($max === null || ($min !== null && $max < $min))     $errors[] = 'Maksimum tutar, minimumdan küçük olamaz.';
        if ($notify !== '' && !filter_var($notify, FILTER_VALIDATE_EMAIL)) $errors[] = 'Bildirim e-postası geçersiz.';
        if (qnb_hhmm_to_min($openT) === null || qnb_hhmm_to_min($closeT) === null) $errors[] = 'Açılış/kapanış saati SS:DD biçiminde olmalı (örn. 07:00).';
        elseif ($hoursOn === '1' && $openT === $closeT) $errors[] = 'Açılış ve kapanış saati aynı olamaz.';
        if ($merchId !== '' && !preg_match('/^[0-9A-Za-z_\-]{1,40}$/', $merchId)) $errors[] = 'Üye İşyeri ID yalnızca harf/rakam içermeli.';
        if (strlen($appId) > 255 || strlen($mKey) > 255 || strlen($secretIn) > 255) $errors[] = 'Kimlik bilgisi alanları çok uzun.';

        $secret = $secretIn !== '' ? $secretIn : (string)settings('qnbpay_app_secret', '');
        if ($enabled === '1' && ($appId === '' || $mKey === '' || $secret === '')) {
            $errors[] = 'Yayına almak için Uygulama Anahtarı, Uygulama Parolası ve Üye İşyeri Anahtarı zorunludur.';
        }
        if ($enabled === '1' && $mode === 'live' && strpos((string)SITE_URL, 'https://') !== 0) {
            $errors[] = 'Canlı modda site adresi (SITE_URL) https:// olmalıdır.';
        }
        if ($errors) adm_back_with('error', implode(' ', $errors), $self . '?tab=settings');

        settings_set('qnbpay_enabled', $enabled, 'payment');
        settings_set('qnbpay_mode', $mode, 'payment');
        settings_set('qnbpay_merchant_id', $merchId, 'payment');
        settings_set('qnbpay_app_id', $appId, 'payment');
        settings_set('qnbpay_merchant_key', $mKey, 'payment');
        if ($secretIn !== '') settings_set('qnbpay_app_secret', $secretIn, 'payment');
        settings_set('qnbpay_base_url_test', $baseTest, 'payment');
        settings_set('qnbpay_base_url_live', $baseLive, 'payment');
        settings_set('qnbpay_min_amount', qnb_amount($min), 'payment');
        settings_set('qnbpay_max_amount', qnb_amount($max), 'payment');
        settings_set('qnbpay_notify_email', $notify, 'payment');
        settings_set('qnbpay_hours_enabled', $hoursOn, 'payment');
        settings_set('qnbpay_open_time', $openT, 'payment');
        settings_set('qnbpay_close_time', $closeT, 'payment');

        log_activity('update', 'sanal_pos', null, 'Sanal POS ayarları güncellendi (mod: ' . $mode . ', durum: ' . ($enabled === '1' ? 'açık' : 'kapalı') . ')');
        adm_back_with('success', 'Sanal POS ayarları kaydedildi.', $self . '?tab=settings');
    }

    /* ---- Bağlantı testi (kayıtlı ayarlarla token alır; adres adaylarını dener) ---- */
    if ($do === 'test') {
        $c = qnb_cfg();
        if ($c['app_id'] === '' || $c['app_secret'] === '') {
            adm_back_with('error', 'Önce Uygulama Anahtarı ve Uygulama Parolası kaydedin.', $self . '?tab=settings');
        }
        $pr  = qnb_probe();
        $r   = $pr['result'];
        $host = parse_url($r['url'], PHP_URL_HOST) ?: '';
        $msg = 'Bağlantı testi (' . $c['mode'] . ' / ' . $host . ', HTTP ' . $r['http'] . '): ' . $r['message'];
        if ($pr['ok']) {
            if ($r['base'] !== $c['base']) {
                settings_set($c['mode'] === 'live' ? 'qnbpay_base_url_live' : 'qnbpay_base_url_test', $r['base'], 'payment');
                $msg .= ' Çalışan sunucu adresi bulundu ve kaydedildi: ' . $r['base'];
            }
        } else {
            $list = [];
            foreach ($pr['tries'] as $t) $list[] = ($t['base'] ?? '') . ' → HTTP ' . $t['http'];
            $msg .= ' | Denenen adresler: ' . implode('; ', $list);
            if (!empty($r['json'])) $msg .= ' | Sunucu yanıt verdi ama kimlik reddedildi: Uygulama Anahtarı ile Uygulama Parolası\'nı (ve modu) kontrol edin; ikisi yer değiştirmiş olabilir.';
        }
        adm_back_with($pr['ok'] ? 'success' : 'error', $msg, $self . '?tab=settings');
    }

    /* ---- Otomatik duraklatmayı kaldır ---- */
    if ($do === 'resume') {
        settings_set('qnbpay_paused_until', '0', 'payment');
        log_activity('update', 'sanal_pos', null, 'Ödeme formu duraklatması elle kaldırıldı');
        adm_back_with('success', 'Ödeme formu yeniden açıldı.', $self);
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

<?php if (qnb_is_paused()):
    $wMin = (int)qnb_limits()['brk_window'];
    $recentFails = 0; try { $recentFails = (int)val("SELECT COUNT(*) FROM tm_payments WHERE status='failed' AND updated_at > (NOW() - INTERVAL $wMin MINUTE)"); } catch (Throwable $e) {}
?>
  <div class="adm-panel" style="border-left:4px solid #c8102e">
    <div class="adm-panel-body">
      <strong>⚠ Ödeme formu otomatik duraklatıldı</strong> — Türkiye saatiyle <strong><?= h((new DateTime('@' . qnb_paused_until()))->setTimezone(qnb_tz())->format('H:i')) ?></strong>'e kadar.
      Son <?= $wMin ?> dakikada <?= $recentFails ?> başarısız işlem görüldü (kart deneme saldırısı olabilir).
      <form method="post" style="display:inline;margin-left:10px" onsubmit="return confirm('Duraklatmayı kaldırıp formu şimdi açmak istiyor musunuz?')">
        <?= csrf_field() ?><input type="hidden" name="do" value="resume">
        <button type="submit" class="adm-btn adm-btn-sm adm-btn-primary">Duraklatmayı kaldır</button>
      </form>
      <a href="<?= h(admin_url('sanal-pos.php?status=failed')) ?>" class="adm-btn adm-btn-sm adm-btn-ghost" style="margin-left:6px">Başarısızları incele</a>
    </div>
  </div>
<?php endif; ?>

<?php if ($tab === 'settings'): ?>

  <?php
    $ready  = qnb_enabled();
    $secSet = $cfg['app_secret'] !== '';
    $retUrl = url('odeme-sonuc.php');
  ?>
  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>QNBpay Durumu</h2>
      <span class="badge <?= $ready ? 'badge-on' : 'badge-off' ?>"><?= $ready ? 'Yayında (' . ($cfg['mode'] === 'live' ? 'canlı' : 'test') . ')' : 'Kapalı / eksik ayar' ?></span>
    </div>
    <div class="adm-panel-body">
      <p class="help" style="margin:0 0 10px">Kullanılan sunucu (<?= $cfg['mode'] === 'live' ? 'Canlı' : 'Test' ?>): <code><?= h($cfg['base']) ?></code></p>
      <?php $hc = qnb_hours_cfg(); $isOpen = qnb_is_open_now(); ?>
      <p class="help" style="margin:0 0 10px">Çalışma saatleri:
        <?php if ($hc['enabled']): ?><strong><?= h($hc['open']) ?>–<?= h($hc['close']) ?></strong> (Türkiye saati) · şu an Türkiye saati <strong><?= h(qnb_now_tr()) ?></strong> →
          <span class="badge <?= $isOpen ? 'badge-on' : 'badge-warn' ?>"><?= $isOpen ? 'Açık' : 'Kapalı (yeni ödeme alınmıyor)' ?></span>
        <?php else: ?><strong>Kısıt yok</strong> (7/24 açık)<?php endif; ?></p>
      <?php if (!$ready): ?>
        <p class="help" style="margin:0 0 10px">Menüde “Online Ödeme” yalnızca <strong>yayına alınınca</strong> ziyaretçilere görünür. Siz yönetici olarak giriş yapmışken menüde her zaman görürsünüz (önizleme).</p>
      <?php endif; ?>
      <?php if (strpos((string)SITE_URL, 'https://') !== 0): ?>
        <p style="margin:0 0 10px;color:#b45309"><strong>Uyarı:</strong> SITE_URL https:// ile başlamıyor. Ödeme kuruluşları https dönüş adresi ister; canlı mod kaydedilemez.</p>
      <?php endif; ?>
      <form method="post" style="display:inline">
        <?= csrf_field() ?><input type="hidden" name="do" value="test">
        <button type="submit" class="adm-btn adm-btn-ghost">🔌 Bağlantıyı Test Et</button>
      </form>
      <span class="help" style="margin-left:8px">Kayıtlı Uygulama Anahtarı/Parolası ile QNBpay'den token ister; ödeme oluşturmaz. Sunucu yolu farklıysa çalışan adresi bulup kaydeder.</span>
    </div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>QNBpay Panelinde Girilecek Adresler</h2></div>
    <div class="adm-panel-body">
      <p class="help" style="margin:0 0 8px">QNBpay → <strong>Üye İşyeri Ayarları → API &amp; Entegrasyon → Url Bilgileri</strong>:</p>
      <table class="adm-table"><tbody>
        <tr><td style="width:220px">Dönüş Url</td><td><code><?= h($retUrl) ?></code></td></tr>
        <tr><td>Başarılı Dönüş Url</td><td><code><?= h($retUrl) ?></code></td></tr>
        <tr><td>Başarısız Dönüş Url</td><td><code><?= h($retUrl) ?></code></td></tr>
      </tbody></table>
      <p class="help" style="margin:10px 0 0">Üçü de aynı adres olabilir: sonucu bu sayfa, bankadan gelen doğrulamaya (hash) bakarak kendisi belirler. Adres panelde yazdığınızla <strong>birebir</strong> aynı olmalı (https, www var/yok).</p>
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
          <select name="mode" style="max-width:280px">
            <option value="test" <?= $cfg['mode'] === 'test' ? 'selected' : '' ?>>Test (gerçek para çekilmez)</option>
            <option value="live" <?= $cfg['mode'] === 'live' ? 'selected' : '' ?>>Canlı</option>
          </select>
        </div>


        <p class="help" style="margin:14px 0 6px"><strong>Çalışma Saatleri</strong> (Türkiye saati) — kapalıyken yeni ödeme başlatılamaz; açıkken başlamış bir ödemenin banka dönüşü ve dekontu her zaman işlenir.</p>
        <div class="row"><label class="checkbox"><input type="checkbox" name="hours_enabled" <?= $hc['enabled'] ? 'checked' : '' ?>> Çalışma saatlerini uygula</label></div>
        <div class="row-2">
          <div class="row"><label>Açılış saati</label><input type="time" name="open_time" value="<?= h($hc['open']) ?>" style="max-width:160px"></div>
          <div class="row"><label>Kapanış saati</label><input type="time" name="close_time" value="<?= h($hc['close']) ?>" style="max-width:160px"></div>
        </div>
        <p class="help">Varsayılan 07:00–23:00. Gece yarısını aşan aralık da girilebilir (örn. 22:00–06:00 açık). Gece test yapacaksanız geçici olarak kutuyu kaldırın.</p>

        <p class="help" style="margin:14px 0 6px"><strong>Entegrasyon Verileri</strong> — QNBpay panelindeki “API &amp; Entegrasyon” sayfasından (gözle butonuyla gösterin):</p>
        <div class="row-2">
          <div class="row"><label>Üye İşyeri ID</label><input type="text" name="merchant_id" value="<?= h($cfg['merchant_id']) ?>" autocomplete="off" placeholder="örn. 41271">
            <p class="help">Bilgi amaçlı saklanır; şu an ödeme isteklerinde kullanılmıyor.</p></div>
          <div class="row"><label>Üye İşyeri Anahtarı</label><input type="text" name="merchant_key" value="<?= h($cfg['merchant_key']) ?>" autocomplete="off">
            <p class="help">Genellikle <code>$2y$10$…</code> ile başlayan uzun anahtar.</p></div>
        </div>
        <div class="row-2">
          <div class="row"><label>Uygulama Anahtarı</label><input type="text" name="app_id" value="<?= h($cfg['app_id']) ?>" autocomplete="off">
            <p class="help">Panelde “Uygulama Anahtarı” (App ID).</p></div>
          <div class="row"><label>Uygulama Parolası</label>
            <input type="password" name="app_secret" value="" autocomplete="new-password" placeholder="<?= $secSet ? '•••••••• (kayıtlı — değiştirmek için yeni değer girin)' : 'Panelde “Uygulama Parolası”' ?>">
            <p class="help">Güvenlik için kayıtlı parola gösterilmez; boş bırakırsanız korunur.</p></div>
        </div>

        <p class="help" style="margin:14px 0 6px"><strong>Sunucu adresleri</strong> — panelde “Canlı Sunucu” olarak görünen adres. Yalnızca alan adı yazarsanız <code>/ccpayment</code> otomatik eklenir; boş bırakırsanız varsayılan kullanılır.</p>
        <div class="row-2">
          <div class="row"><label>Canlı Sunucu</label><input type="text" name="base_url_live" value="<?= h((string)settings('qnbpay_base_url_live', '')) ?>" placeholder="https://panel.qnbpay.com.tr">
            <p class="help">Şu an kullanılan: <code><?= h($cfg['base_live']) ?></code></p></div>
          <div class="row"><label>Test Sunucu</label><input type="text" name="base_url_test" value="<?= h((string)settings('qnbpay_base_url_test', '')) ?>" placeholder="<?= h(QNB_BASE_TEST) ?>">
            <p class="help">Şu an kullanılan: <code><?= h($cfg['base_test']) ?></code></p></div>
        </div>

        <div class="row-2" style="margin-top:14px">
          <div class="row"><label>Asgari tutar (₺)</label><input type="text" name="min_amount" value="<?= h(qnb_amount($cfg['min'])) ?>">
            <p class="help">Kart deneme saldırıları küçük tutarla yapılır; işinize uygun bir asgari tutar belirleyin.</p></div>
          <div class="row"><label>Azami tutar (₺)</label><input type="text" name="max_amount" value="<?= h(qnb_amount($cfg['max'])) ?>"></div>
        </div>
        <div class="row"><label>Bildirim e-postası</label><input type="text" name="notify_email" value="<?= h((string)settings('qnbpay_notify_email', '')) ?>" placeholder="Boşsa iletişim e-postası kullanılır: <?= h($cfg['notify']) ?>">
          <p class="help">Ödeme alındığında ve “İnceleme Gerekli” durumunda buraya e-posta gider.</p></div>
      </div>
    </div>
    <div class="form-actions"><button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button></div>
  </form>

  <?php $L = qnb_limits(); ?>
  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Otomatik Koruma (kart deneme / bot)</h2></div>
    <div class="adm-panel-body">
      <ul style="margin:0;padding-left:20px;line-height:1.8">
        <li>3D Secure zorunlu; kart bilgisi sunucudan geçmez ve saklanmaz.</li>
        <li>10 dakikada en fazla: aynı bağlantı adresinden <?= (int)$L['per_remote'] ?>, bildirilen IP'den <?= (int)$L['per_claimed_ip'] ?>, aynı e-postadan <?= (int)$L['per_email'] ?>, toplam <?= (int)$L['global'] ?> deneme.</li>
        <li>Aynı IP/e-posta <?= (int)$L['fail_window'] ?> dakikada <?= (int)$L['fail_per_actor'] ?> başarısız işlemden sonra <?= (int)$L['fail_window'] ?> dk bekletilir.</li>
        <li><?= (int)$L['brk_window'] ?> dakikada <?= (int)$L['brk_failures'] ?> başarısız işlem olursa form <?= (int)$L['brk_pause'] ?> dk otomatik durdurulur ve size e-posta gelir.</li>
      </ul>
    </div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Canlıya Alma Kontrol Listesi</h2></div>
    <div class="adm-panel-body">
      <ol style="margin:0;padding-left:20px;line-height:1.8">
        <li>QNBpay panelinde <strong>Url Bilgileri</strong> alanlarına yukarıdaki adresi girip <strong>Kaydet</strong>'e basın.</li>
        <li>Buradaki <strong>Entegrasyon Verileri</strong> alanlarını doldurup <strong>Kaydet</strong>; ardından <strong>Bağlantıyı Test Et</strong> başarılı olmalı.</li>
        <li>“Yayına al”ı işaretleyin → menüde <strong>Online Ödeme</strong> görünür. <code>/odeme.php</code> ile küçük tutarlı bir işlem yapın.</li>
        <li>Sonuç <strong>Ödemeler</strong> sekmesinde “Ödendi” görünmeli; başarısız/iptal senaryosunu da deneyin.</li>
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
