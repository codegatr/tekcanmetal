<?php
/**
 * Telefonla Sipariş (MOTO — Mail/Telephone Order) — v1.0.152
 *
 * Senaryo: Müşteri dışarıda/mağazada değil ama telefonda kart bilgisini yönetici ile
 * paylaşıp ödeme yapmak istiyor. Bu ekran, admin panelinden kart bilgisinin GİRİLMESİNE
 * izin verir — ama kart alanları (numara/SKT/CVV) yine odeme.php'deki müşteri akışıyla
 * BİREBİR AYNI şekilde çalışır: bu sayfaya hiçbir <input name="..."> ile POST edilmez,
 * tarayıcı JS ile doğrudan QNBpay'in 3D Secure sayfasına gönderilir. Yani:
 *
 *   - Kart numarası/CVV bizim sunucumuza ASLA dokunmaz, tm_payments tablosuna hiç yazılmaz.
 *   - 3D Secure onay kodu (OTP) yine bankanın kart sahibinin KENDİ telefonuna gönderdiği
 *     SMS'tir — yönetici oturumu bunu göremez/atlayamaz. Müşteri hattı kapatmadan
 *     kendisine gelen SMS kodunu (banka 3D sayfasında) onaylamalıdır.
 *   - Bu yüzden bu ekran "admin müşteri hesabına girip onun yerine öder" değil, "yönetici
 *     telefonda müşteriden aldığı kart bilgisini müşteri adına GİRER, onay yine müşteride
 *     kalır" mantığıyla çalışır (MOTO / Mail Order Telephone Order).
 *
 * Denetim: her kayıt channel='admin_moto' ve created_by_admin_id ile işaretlenir,
 * activity log'a yazılır ve Sanal POS listesinde ayrı rozetle görünür.
 */
define('TM_ADMIN', true);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/qnbpay.php';
require_once __DIR__ . '/../includes/customer_auth.php';

/* ============================================================
 * AJAX: ödeme kaydı aç + QNBpay form alanlarını üret (kart alanları HARİÇ)
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'init') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $fail = function (string $msg, int $code = 422): void {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    };

    if (empty($_SESSION['admin_id'])) $fail('Oturumunuz sona ermiş. Lütfen tekrar giriş yapın.', 401);
    $adminUser = row("SELECT id, username, full_name, role FROM tm_users WHERE id=? AND is_active=1", [(int)$_SESSION['admin_id']]);
    if (!$adminUser || !in_array($adminUser['role'], ['superadmin', 'admin'], true)) $fail('Bu işlem için yetkiniz yok.', 403);
    if (!csrf_check()) $fail('Oturum doğrulaması başarısız oldu. Sayfayı yenileyip tekrar deneyin.', 419);

    qnb_ensure_schema();
    cust_ensure_schema();
    if (!qnb_enabled()) $fail('Online ödeme sistemi şu anda kapalı.', 503);
    if (qnb_is_paused()) $fail('Ödeme sistemi geçici olarak duraklatıldı.', 503);
    $cfg = qnb_cfg();

    $in = fn(string $k): string => trim((string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)($_POST[$k] ?? '')));

    $full    = mb_substr($in('full_name'), 0, 150, 'UTF-8');
    $company = mb_substr($in('company'), 0, 150, 'UTF-8');
    $email   = mb_substr($in('email'), 0, 150, 'UTF-8');
    $phone   = mb_substr($in('phone'), 0, 30, 'UTF-8');
    $desc    = mb_substr($in('description'), 0, 300, 'UTF-8');
    $amount  = qnb_parse_amount($in('amount'));
    $customerId = (int)($_POST['customer_id'] ?? 0);

    if (count(preg_split('/\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY)) < 2) $fail('Lütfen ad ve soyadı birlikte yazın.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))                     $fail('Geçerli bir e-posta adresi girin.');
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15)         $fail('Geçerli bir telefon numarası girin.');
    if ($amount === null || $amount <= 0)                               $fail('Geçerli bir tutar girin.');
    if ($amount < $cfg['min'] || $amount > $cfg['max'])                 $fail('Tutar ' . qnb_money($cfg['min']) . ' ile ' . qnb_money($cfg['max']) . ' arasında olmalıdır.');
    if (empty($_POST['confirm_phone']))                                 $fail('Kart bilgisinin bizzat kart sahibinden telefonda alındığını onaylayın.');

    if ($customerId) {
        $exists = (int)val("SELECT COUNT(*) FROM tm_customers WHERE id=?", [$customerId]);
        if (!$exists) $customerId = 0;
    }

    try {
        $invoiceId = qnb_new_invoice_id();
        $noteDesc = $desc !== '' ? $desc : 'Telefon siparişi';
        $id = qnb_create_payment([
            'invoice_id'  => $invoiceId,
            'full_name'   => $full,
            'company'     => $company,
            'email'       => $email,
            'phone'       => $phone,
            'description' => $noteDesc,
            'amount'      => $amount,
            'channel'     => 'admin_moto',
            'created_by_admin_id' => (int)$adminUser['id'],
        ]);
        if ($customerId) q("UPDATE tm_payments SET customer_id=? WHERE id=?", [$customerId, $id]);
        $pay  = row("SELECT * FROM tm_payments WHERE id=?", [$id]);
        $form = qnb_build_form($pay);
        if (function_exists('log_activity')) {
            log_activity('create', 'payment', $id, 'Telefonla sipariş (MOTO) başlatıldı: ' . $full . ' — ' . qnb_money($amount) . ' (' . $adminUser['username'] . ')');
        }
    } catch (Throwable $e) {
        $fail('Ödeme başlatılamadı. Lütfen tekrar deneyin.', 500);
    }

    echo json_encode(['ok' => true, 'action' => $form['action'], 'fields' => $form['fields']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* ============================================================
 * SAYFA
 * ============================================================ */
$adminTitle = 'Telefonla Sipariş (MOTO)';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

if (!in_array($adminUser['role'] ?? '', ['superadmin', 'admin'], true)) {
    adm_back_with('error', 'Bu sayfaya erişim yetkiniz yok.', 'admin/index.php');
}

qnb_ensure_schema();
cust_ensure_schema();
$payOn     = qnb_enabled();
$payPaused = $payOn && qnb_is_paused();
$cfg       = qnb_cfg();
$customers = all("SELECT id, username, full_name, company, email, phone FROM tm_customers WHERE is_active=1 ORDER BY full_name");

$js = [
    'name' => 'Lütfen ad ve soyadınızı birlikte yazın.',
    'email' => 'Geçerli bir e-posta adresi girin.',
    'phone' => 'Geçerli bir telefon numarası girin.',
    'amount' => 'Geçerli bir tutar girin.',
    'range' => 'Tutar izin verilen aralığın dışında.',
    'holder' => 'Kart üzerindeki adı girin.',
    'card' => 'Geçerli bir kart numarası girin.',
    'expiry' => 'Son kullanma tarihini kontrol edin.',
    'cvv' => 'CVV kodunu kontrol edin.',
    'confirm' => 'Kart bilgisinin kart sahibinden bizzat alındığını onaylayın.',
    'processing' => 'İşleniyor…',
    'btn' => 'Ödemeyi Başlat',
    'generic' => 'Bir hata oluştu. Lütfen tekrar deneyin.',
];
?>
<style>
.moto-warn{background:#fff7e6;border-left:4px solid #c8102e;padding:16px 20px;margin-bottom:20px;font-size:13.5px;line-height:1.6;color:#3a3a3a}
.moto-warn strong{color:#c8102e}
.moto-card-fields{background:#f7f8fa;border:1px solid #e1e4ea;border-radius:8px;padding:20px;margin-top:6px}
.moto-card-fields h3{margin:0 0 14px;font-size:14px;font-weight:700;color:#1a2540}
.moto-preview{display:flex;align-items:center;justify-content:space-between;gap:14px;background:linear-gradient(135deg,#0a1730,#152b57);color:#fff;border-radius:10px;padding:16px 20px;margin-bottom:16px;font-family:monospace;font-size:15px;letter-spacing:1px}
.moto-preview .mp-net{font-family:var(--sans,system-ui);letter-spacing:1px;font-size:11px;font-weight:700;opacity:.7}
.moto-errbox{background:#fdecec;border-left:4px solid #c8102e;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#8a0f24;display:none}
.moto-errbox ul{margin:0;padding-left:18px}
.moto-confirm{display:flex;gap:10px;align-items:flex-start;font-size:13px;line-height:1.55;margin:16px 0;color:#3a3a3a}
.moto-confirm input{margin-top:3px;flex-shrink:0}
</style>

<?php if (!$payOn): ?>
  <div class="adm-panel">
    <div class="adm-panel-body"><div class="adm-empty"><div class="ico">💳</div>Online ödeme sistemi kapalı. Önce <a href="<?= h(admin_url('sanal-pos.php?tab=settings')) ?>">Sanal POS ayarlarından</a> etkinleştirin.</div></div>
  </div>
<?php elseif ($payPaused): ?>
  <div class="adm-panel">
    <div class="adm-panel-body"><div class="adm-empty"><div class="ico">⏸</div>Ödeme sistemi geçici olarak duraklatıldı. <a href="<?= h(admin_url('sanal-pos.php?tab=settings')) ?>">Sanal POS ayarlarından</a> kaldırabilirsiniz.</div></div>
  </div>
<?php else: ?>

  <div class="moto-warn">
    <strong>Nasıl çalışır:</strong> Bu ekran, müşteri hesabına "sizin yerinize giriş" değildir — kimse müşterinin
    şifresini bilmenize gerek yoktur. Siz kartı burada girersiniz ama işlemi banka yine <strong>3D Secure</strong>
    ile doğrular: onay kodu (SMS) doğrudan kart sahibinin telefonuna gider ve işlemi tamamlamak için müşterinin
    o kodu bankanın açacağı sayfada onaylaması gerekir. Kart bilgilerini yalnızca <strong>bizzat kart sahibiyle
    telefondayken</strong>, onun ağzından alın; kart verisi bu sitede hiçbir yerde saklanmaz.
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>📞 Yeni Telefon Siparişi</h2></div>
    <div class="adm-panel-body">

      <div class="moto-errbox" id="motoErr"></div>

      <form id="motoForm" class="adm-form" novalidate data-init="<?= h(url('admin/moto-odeme.php')) ?>" data-min="<?= h((string)$cfg['min']) ?>" data-max="<?= h((string)$cfg['max']) ?>">
        <?= csrf_field() ?>

        <?php if ($customers): ?>
        <div class="row">
          <label>Kayıtlı Müşteri (opsiyonel)</label>
          <select id="motoCustomer" name="customer_id">
            <option value="0">— Seçilmedi (bilgileri elle gir) —</option>
            <?php foreach ($customers as $cu): ?>
              <option value="<?= (int)$cu['id'] ?>"
                data-name="<?= h($cu['full_name']) ?>" data-company="<?= h($cu['company'] ?? '') ?>"
                data-email="<?= h($cu['email'] ?? '') ?>" data-phone="<?= h($cu['phone'] ?? '') ?>">
                <?= h($cu['full_name']) ?> (@<?= h($cu['username']) ?>)<?= $cu['company'] ? ' — ' . h($cu['company']) : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="help">Seçilirse ödeme, o müşterinin "Geçmiş Ödemelerim" listesinde de görünür. Boş bırakılırsa bilgiler elle girilir.</p>
        </div>
        <?php endif; ?>

        <div class="row-2">
          <div class="row"><label>Ad Soyad *</label><input type="text" name="full_name" id="motoName" required></div>
          <div class="row"><label>Firma</label><input type="text" name="company" id="motoCompany"></div>
        </div>
        <div class="row-2">
          <div class="row"><label>E-posta *</label><input type="email" name="email" id="motoEmail" required></div>
          <div class="row"><label>Telefon *</label><input type="tel" name="phone" id="motoPhone" required></div>
        </div>
        <div class="row-2">
          <div class="row"><label>Tutar (TL) *</label><input type="text" name="amount" id="motoAmount" inputmode="decimal" placeholder="0,00" required>
            <p class="help">İzin verilen aralık: <?= h(qnb_money($cfg['min'])) ?> – <?= h(qnb_money($cfg['max'])) ?></p></div>
          <div class="row"><label>Açıklama</label><input type="text" name="description" id="motoDesc" placeholder="Fatura / sipariş numarası"></div>
        </div>

        <div class="moto-card-fields">
          <h3>💳 Kart Bilgileri (sunucuya kaydedilmez, doğrudan bankaya gönderilir)</h3>
          <div class="moto-preview">
            <span id="motoCardNum">•••• •••• •••• ••••</span>
            <span class="mp-net" id="motoCardNet"></span>
          </div>
          <div class="row-2">
            <div class="row"><label>Kart Üzerindeki Ad Soyad *</label><input type="text" id="mc_holder" autocomplete="off"></div>
            <div class="row"><label>Kart Numarası *</label><input type="text" id="mc_no" inputmode="numeric" autocomplete="off" maxlength="23" placeholder="0000 0000 0000 0000"></div>
          </div>
          <div class="row-2">
            <div class="row"><label>Son Kullanma *</label><input type="text" id="mc_exp" inputmode="numeric" autocomplete="off" maxlength="7" placeholder="AA / YY"></div>
            <div class="row"><label>CVV *</label><input type="text" id="mc_cvv" inputmode="numeric" autocomplete="off" maxlength="4" placeholder="•••"></div>
          </div>
        </div>

        <label class="moto-confirm">
          <input type="checkbox" id="motoConfirm" required>
          <span>Kart bilgilerini <strong>bizzat kart sahibinden</strong>, telefonda kendisiyle konuşarak aldığımı onaylıyorum. Ödeme onayı (3D Secure SMS) yine kart sahibine gidecek ve tamamlanması için onun onayı gerekecek.</span>
        </label>

        <div class="form-actions">
          <button type="submit" class="adm-btn adm-btn-primary" id="motoBtn">Ödemeyi Başlat →</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  (function () {
    var I = <?= json_encode($js, JSON_UNESCAPED_UNICODE) ?>;
    var form = document.getElementById('motoForm'), btn = document.getElementById('motoBtn'), box = document.getElementById('motoErr');
    var holder = document.getElementById('mc_holder'), no = document.getElementById('mc_no'),
        exp = document.getElementById('mc_exp'), cvv = document.getElementById('mc_cvv');
    var MIN = parseFloat(form.getAttribute('data-min')) || 1, MAX = parseFloat(form.getAttribute('data-max')) || 250000;

    var sel = document.getElementById('motoCustomer');
    if (sel) {
      sel.addEventListener('change', function () {
        var o = sel.options[sel.selectedIndex];
        document.getElementById('motoName').value = o.getAttribute('data-name') || '';
        document.getElementById('motoCompany').value = o.getAttribute('data-company') || '';
        document.getElementById('motoEmail').value = o.getAttribute('data-email') || '';
        document.getElementById('motoPhone').value = o.getAttribute('data-phone') || '';
      });
    }

    function digits(s) { return (s || '').replace(/\D/g, ''); }
    function luhn(n) { var s = 0, alt = false; for (var i = n.length - 1; i >= 0; i--) { var d = +n.charAt(i); if (alt) { d *= 2; if (d > 9) d -= 9; } s += d; alt = !alt; } return n.length > 0 && s % 10 === 0; }
    function parseAmount(s) {
      s = (s || '').replace(/[\s₺]|TL/gi, ''); if (!/^[0-9.,]+$/.test(s)) return null;
      var d = s.indexOf('.') > -1, c = s.indexOf(',') > -1;
      if (d && c) { s = s.lastIndexOf(',') > s.lastIndexOf('.') ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, ''); }
      else if (c) { s = (s.split(',').length === 2) ? s.replace(',', '.') : s.replace(/,/g, ''); }
      else if (d) { if (/^[1-9]\d{0,2}(\.\d{3})+$/.test(s)) { s = s.replace(/\./g, ''); } else if (s.split('.').length > 2) { return null; } }
      var v = parseFloat(s); return isNaN(v) ? null : Math.round(v * 100) / 100;
    }
    function cardNetwork(n) {
      if (/^4/.test(n)) return 'VISA';
      if (/^(5[1-5]|2[2-7])/.test(n)) return 'MASTERCARD';
      if (/^9792/.test(n)) return 'TROY';
      if (/^3[47]/.test(n)) return 'AMEX';
      return '';
    }
    var cardNumEl = document.getElementById('motoCardNum'), cardNetEl = document.getElementById('motoCardNet');
    function updatePreview() {
      var n = digits(no.value);
      var grouped = (n + '•••••••••••••••••'.slice(0, Math.max(0, 16 - n.length))).slice(0, 16).replace(/(.{4})/g, '$1 ').trim();
      cardNumEl.textContent = n.length ? grouped : '•••• •••• •••• ••••';
      cardNetEl.textContent = cardNetwork(n);
    }
    no.addEventListener('input', function () { var v = digits(no.value).slice(0, 19); no.value = v.replace(/(.{4})/g, '$1 ').trim(); updatePreview(); });
    exp.addEventListener('input', function () { var v = digits(exp.value).slice(0, 4); exp.value = v.length >= 3 ? v.slice(0, 2) + ' / ' + v.slice(2) : v; });
    cvv.addEventListener('input', function () { cvv.value = digits(cvv.value).slice(0, 4); });

    function show(list) {
      if (!list.length) { box.style.display = 'none'; box.innerHTML = ''; return; }
      var ul = document.createElement('ul');
      list.forEach(function (m) { var li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
      box.innerHTML = ''; box.appendChild(ul); box.style.display = 'block';
      box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function validate() {
      var e = [];
      if (form.elements.full_name.value.trim().split(/\s+/).length < 2) e.push(I.name);
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.elements.email.value.trim())) e.push(I.email);
      if (digits(form.elements.phone.value).length < 10) e.push(I.phone);
      var a = parseAmount(form.elements.amount.value);
      if (a === null || a <= 0) e.push(I.amount); else if (a < MIN || a > MAX) e.push(I.range);
      if (!holder.value.trim()) e.push(I.holder);
      var n = digits(no.value); if (n.length < 13 || !luhn(n)) e.push(I.card);
      var ed = digits(exp.value), mm = parseInt(ed.slice(0, 2), 10), yy = parseInt(ed.slice(2, 4), 10), now = new Date();
      var okExp = ed.length === 4 && mm >= 1 && mm <= 12 && (2000 + yy > now.getFullYear() || (2000 + yy === now.getFullYear() && mm >= now.getMonth() + 1));
      if (!okExp) e.push(I.expiry);
      if (digits(cvv.value).length < 3) e.push(I.cvv);
      if (!document.getElementById('motoConfirm').checked) e.push(I.confirm);
      return e;
    }

    function toGateway(j) {
      var f = document.createElement('form'); f.method = 'POST'; f.action = j.action; f.acceptCharset = 'UTF-8';
      function add(k, v) { var i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v; f.appendChild(i); }
      Object.keys(j.fields).forEach(function (k) { add(k, j.fields[k]); });
      var ed = digits(exp.value);
      add('cc_holder_name', holder.value.trim());
      add('cc_no', digits(no.value));
      add('expiry_month', ed.slice(0, 2));
      add('expiry_year', '20' + ed.slice(2, 4));
      add('cvv', digits(cvv.value));
      document.body.appendChild(f); f.submit();
    }

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var errs = validate(); show(errs); if (errs.length) return;
      btn.disabled = true; btn.textContent = I.processing;

      var fd = new FormData(form); fd.append('action', 'init');
      if (document.getElementById('motoConfirm').checked) fd.append('confirm_phone', '1');
      fetch(form.getAttribute('data-init'), { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json().catch(function () { return { ok: false, error: I.generic }; }); })
        .then(function (j) { if (!j.ok) throw new Error(j.error || I.generic); toGateway(j); })
        .catch(function (err) { show([err.message || I.generic]); btn.disabled = false; btn.textContent = I.btn + ' →'; });
    });

    window.addEventListener('pageshow', function (e) { if (e.persisted) { btn.disabled = false; btn.textContent = I.btn + ' →'; } });
  })();
  </script>

<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
