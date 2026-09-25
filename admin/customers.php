<?php
define('TM_ADMIN', true);
$adminTitle = 'Müşteri Hesapları (Sanal POS)';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';
require_once __DIR__ . '/../includes/qnbpay.php';
require_once __DIR__ . '/../includes/customer_auth.php';

// Müşteri hesapları hassas veri (şifre atama) içerir: yalnızca süper yönetici / yönetici
if (!in_array($adminUser['role'] ?? '', ['superadmin', 'admin'], true)) {
    adm_back_with('error', 'Bu sayfaya erişim yetkiniz yok.', 'admin/index.php');
}

cust_ensure_schema();

$self = 'admin/customers.php';
$revealedPassword = null;   // yalnızca oluşturma/sıfırlama sonrası bir kez, bu istek içinde tutulur

/* ============================================================
 * POST işlemleri
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $do = $_POST['do'] ?? '';

    /* ---- Yeni müşteri oluştur ---- */
    if ($do === 'create') {
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $company  = trim((string)($_POST['company'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $phone    = trim((string)($_POST['phone'] ?? ''));
        $username = mb_strtolower(trim((string)($_POST['username'] ?? '')), 'UTF-8');
        $customPw = (string)($_POST['password'] ?? '');

        $errors = [];
        if ($fullName === '') $errors[] = 'Ad soyad zorunludur.';
        if ($username === '') $username = cust_suggest_username($fullName);
        if (!cust_valid_username($username)) $errors[] = 'Kullanıcı adı yalnızca küçük harf, rakam, nokta, tire ve alt çizgi içerebilir (3-60 karakter).';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-posta adresi geçersiz.';
        if ($customPw !== '' && mb_strlen($customPw, 'UTF-8') < 8) $errors[] = 'Şifre en az 8 karakter olmalıdır.';
        if (!$errors && val("SELECT COUNT(*) FROM tm_customers WHERE username=?", [$username]) > 0) {
            $errors[] = 'Bu kullanıcı adı zaten kullanılıyor: ' . $username;
        }
        if ($errors) adm_back_with('error', implode(' ', $errors), $self . '?open=new');

        $plainPw = $customPw !== '' ? $customPw : cust_generate_password();
        q("INSERT INTO tm_customers (username, password, full_name, company, email, phone, must_change_password, created_by)
           VALUES (?,?,?,?,?,?,1,?)",
          [$username, password_hash($plainPw, PASSWORD_BCRYPT), $fullName, $company ?: null, $email ?: null, $phone ?: null, $adminUser['id']]);
        $id = (int)db()->lastInsertId();
        log_activity('create', 'customer', $id, 'Müşteri hesabı oluşturuldu: ' . $username);

        // Şifre yalnızca bu tek yönlendirme sonrası, oturumda BİR KEZ gösterilir.
        $_SESSION['_reveal_pw'] = ['id' => $id, 'username' => $username, 'password' => $plainPw, 'until' => time() + 120];
        adm_back_with('success', 'Müşteri hesabı oluşturuldu.', $self . '?reveal=' . $id);
    }

    /* ---- Şifre sıfırla ---- */
    if ($do === 'reset_password') {
        $id = (int)($_POST['id'] ?? 0);
        $c = row("SELECT * FROM tm_customers WHERE id=?", [$id]);
        if (!$c) adm_back_with('error', 'Müşteri bulunamadı.', $self);
        $customPw = (string)($_POST['password'] ?? '');
        if ($customPw !== '' && mb_strlen($customPw, 'UTF-8') < 8) {
            adm_back_with('error', 'Şifre en az 8 karakter olmalıdır.', $self . '?view=' . $id);
        }
        $plainPw = $customPw !== '' ? $customPw : cust_generate_password();
        q("UPDATE tm_customers SET password=?, must_change_password=1, failed_attempts=0, locked_until=NULL WHERE id=?",
          [password_hash($plainPw, PASSWORD_BCRYPT), $id]);
        log_activity('update', 'customer', $id, 'Müşteri şifresi sıfırlandı: ' . $c['username']);
        $_SESSION['_reveal_pw'] = ['id' => $id, 'username' => $c['username'], 'password' => $plainPw, 'until' => time() + 120];
        adm_back_with('success', 'Şifre sıfırlandı.', $self . '?reveal=' . $id);
    }

    /* ---- Bilgi güncelle ---- */
    if ($do === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $c = row("SELECT * FROM tm_customers WHERE id=?", [$id]);
        if (!$c) adm_back_with('error', 'Müşteri bulunamadı.', $self);
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $company  = trim((string)($_POST['company'] ?? ''));
        $email    = trim((string)($_POST['email'] ?? ''));
        $phone    = trim((string)($_POST['phone'] ?? ''));
        if ($fullName === '') adm_back_with('error', 'Ad soyad zorunludur.', $self . '?view=' . $id);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) adm_back_with('error', 'E-posta adresi geçersiz.', $self . '?view=' . $id);
        q("UPDATE tm_customers SET full_name=?, company=?, email=?, phone=? WHERE id=?",
          [$fullName, $company ?: null, $email ?: null, $phone ?: null, $id]);
        log_activity('update', 'customer', $id, 'Müşteri bilgileri güncellendi: ' . $c['username']);
        adm_back_with('success', 'Bilgiler güncellendi.', $self . '?view=' . $id);
    }

    /* ---- Aktif / pasif ---- */
    if ($do === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        $c = row("SELECT * FROM tm_customers WHERE id=?", [$id]);
        if (!$c) adm_back_with('error', 'Müşteri bulunamadı.', $self);
        $new = $c['is_active'] ? 0 : 1;
        q("UPDATE tm_customers SET is_active=? WHERE id=?", [$new, $id]);
        log_activity('update', 'customer', $id, ($new ? 'Müşteri aktifleştirildi: ' : 'Müşteri pasifleştirildi: ') . $c['username']);
        adm_back_with('success', $new ? 'Müşteri aktifleştirildi.' : 'Müşteri pasifleştirildi.', $self);
    }

    /* ---- Kilidi kaldır ---- */
    if ($do === 'unlock') {
        $id = (int)($_POST['id'] ?? 0);
        q("UPDATE tm_customers SET locked_until=NULL, failed_attempts=0 WHERE id=?", [$id]);
        log_activity('update', 'customer', $id, 'Müşteri hesap kilidi kaldırıldı');
        adm_back_with('success', 'Hesap kilidi kaldırıldı.', $self . '?view=' . $id);
    }
}

// Bir kerelik şifre gösterimi: yalnızca doğru oturumdan, tek yönlendirme sonrası, 2 dakika geçerli
if (!empty($_GET['reveal']) && !empty($_SESSION['_reveal_pw']) && (int)$_GET['reveal'] === (int)$_SESSION['_reveal_pw']['id']
    && $_SESSION['_reveal_pw']['until'] >= time()) {
    $revealedPassword = $_SESSION['_reveal_pw'];
}
unset($_SESSION['_reveal_pw']);
?>

<?php
$view = (int)($_GET['view'] ?? 0);

/* ---------------- DETAY ---------------- */
if ($view) {
    $c = row("SELECT * FROM tm_customers WHERE id=?", [$view]);
    if (!$c) adm_back_with('error', 'Müşteri bulunamadı.', $self);
    $payments = all("SELECT * FROM tm_payments WHERE customer_id=? ORDER BY id DESC LIMIT 20", [$c['id']]);
    $isLocked = !empty($c['locked_until']) && strtotime($c['locked_until']) > time();
?>
  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>@<?= h($c['username']) ?> <?= $c['is_active'] ? '<span class="badge badge-on">Aktif</span>' : '<span class="badge badge-off">Pasif</span>' ?><?= $isLocked ? ' <span class="badge badge-danger">Kilitli</span>' : '' ?></h2>
      <a href="<?= h(admin_url('customers.php')) ?>" class="adm-btn adm-btn-ghost">← Listeye dön</a>
    </div>
  </div>

  <?php if ($revealedPassword && (int)$revealedPassword['id'] === $c['id']): ?>
  <div class="adm-panel">
    <div class="adm-panel-head"><h2>🔑 Yeni Şifre</h2></div>
    <div class="adm-panel-body">
      <p class="help" style="margin:0 0 10px">Bu şifre yalnızca <strong>şimdi</strong> gösteriliyor, sayfayı yenilerseniz tekrar göremezsiniz. Müşteriye WhatsApp/telefon ile iletin.</p>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <code style="font-size:18px;font-weight:700;background:#f3f7f4;border:1px solid #cfe8d8;padding:10px 16px;letter-spacing:1px"><?= h($revealedPassword['password']) ?></code>
        <button type="button" class="adm-btn adm-btn-ghost" onclick="navigator.clipboard.writeText('<?= h($revealedPassword['password']) ?>');this.textContent='✓ Kopyalandı'">📋 Kopyala</button>
      </div>
      <p class="help" style="margin:10px 0 0">Kullanıcı adı: <strong><?= h($revealedPassword['username']) ?></strong> · Müşteri ilk girişte şifresini değiştirmeye yönlendirilecek.</p>
    </div>
  </div>
  <?php endif; ?>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Bilgiler</h2></div>
    <div class="adm-panel-body">
      <form method="post" class="adm-form">
        <?= csrf_field() ?><input type="hidden" name="do" value="update"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <div class="row-2">
          <div class="row"><label>Ad Soyad</label><input type="text" name="full_name" value="<?= h($c['full_name']) ?>" required></div>
          <div class="row"><label>Firma</label><input type="text" name="company" value="<?= h($c['company'] ?? '') ?>"></div>
        </div>
        <div class="row-2">
          <div class="row"><label>E-posta</label><input type="email" name="email" value="<?= h($c['email'] ?? '') ?>"></div>
          <div class="row"><label>Telefon</label><input type="text" name="phone" value="<?= h($c['phone'] ?? '') ?>"></div>
        </div>
        <div class="row"><label>Kullanıcı adı</label><input type="text" value="<?= h($c['username']) ?>" disabled style="opacity:.6">
          <p class="help">Kullanıcı adı değiştirilemez.</p></div>
        <div class="form-actions"><button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button></div>
      </form>
    </div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Hesap İşlemleri</h2></div>
    <div class="adm-panel-body" style="display:flex;gap:10px;flex-wrap:wrap">
      <form method="post" onsubmit="return confirm('Yeni bir rastgele şifre oluşturulacak, mevcut şifre geçersiz olacak. Devam edilsin mi?')">
        <?= csrf_field() ?><input type="hidden" name="do" value="reset_password"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <button type="submit" class="adm-btn adm-btn-primary">🔑 Şifreyi Sıfırla (rastgele)</button>
      </form>
      <?php if ($isLocked): ?>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="do" value="unlock"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <button type="submit" class="adm-btn adm-btn-ghost">🔓 Kilidi Kaldır</button>
      </form>
      <?php endif; ?>
      <form method="post" onsubmit="return confirm('<?= $c['is_active'] ? 'Bu hesabı pasifleştirmek istediğinize emin misiniz? Müşteri giriş yapamaz hale gelir.' : 'Bu hesabı aktifleştirmek istiyor musunuz?' ?>')">
        <?= csrf_field() ?><input type="hidden" name="do" value="toggle_active"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <button type="submit" class="adm-btn <?= $c['is_active'] ? 'adm-btn-danger' : 'adm-btn-ghost' ?>"><?= $c['is_active'] ? '⏸ Pasifleştir' : '▶ Aktifleştir' ?></button>
      </form>
    </div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head"><h2>Ödeme Geçmişi (<?= count($payments) ?>)</h2></div>
    <div class="adm-panel-body" style="padding:0">
      <?php if (!$payments): ?>
        <div class="adm-empty"><div class="ico">💳</div>Bu müşteriye bağlı ödeme yok.</div>
      <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Tarih</th><th>Referans</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td style="white-space:nowrap"><?= h(tr_date($p['created_at'], true)) ?></td>
            <td><code style="font-size:11px"><?= h($p['invoice_id']) ?></code></td>
            <td><strong><?= h(qnb_money((float)$p['amount'])) ?></strong></td>
            <td><?= qnb_status_badge((string)$p['status']) ?></td>
            <td class="actions"><a href="<?= h(admin_url('sanal-pos.php?view=' . (int)$p['id'])) ?>" class="adm-btn adm-btn-sm adm-btn-primary">Detay</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

<?php } else {
/* ---------------- LİSTE ---------------- */
$q = trim((string)($_GET['q'] ?? ''));
$where = []; $params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(username LIKE ? OR full_name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like);
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$customers = all("SELECT c.*, (SELECT COUNT(*) FROM tm_payments p WHERE p.customer_id=c.id AND p.status='paid') AS paid_count
                  FROM tm_customers c $w ORDER BY c.created_at DESC", $params);
$openNew = isset($_GET['open']) && $_GET['open'] === 'new';
?>

  <?php if ($revealedPassword): ?>
  <div class="adm-panel">
    <div class="adm-panel-head"><h2>🔑 Yeni Şifre</h2></div>
    <div class="adm-panel-body">
      <p class="help" style="margin:0 0 10px">Bu şifre yalnızca <strong>şimdi</strong> gösteriliyor. Müşteriye WhatsApp/telefon ile iletin.</p>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <span class="help">Kullanıcı adı: <strong><?= h($revealedPassword['username']) ?></strong></span>
        <code style="font-size:18px;font-weight:700;background:#f3f7f4;border:1px solid #cfe8d8;padding:10px 16px;letter-spacing:1px"><?= h($revealedPassword['password']) ?></code>
        <button type="button" class="adm-btn adm-btn-ghost" onclick="navigator.clipboard.writeText('<?= h($revealedPassword['password']) ?>');this.textContent='✓ Kopyalandı'">📋 Kopyala</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>Yeni Müşteri Hesabı</h2>
      <span class="help">Şifreyi boş bırakırsanız rastgele oluşturulur</span>
    </div>
    <div class="adm-panel-body">
      <form method="post" class="adm-form">
        <?= csrf_field() ?><input type="hidden" name="do" value="create">
        <div class="row-2">
          <div class="row"><label>Ad Soyad *</label><input type="text" name="full_name" required></div>
          <div class="row"><label>Firma</label><input type="text" name="company"></div>
        </div>
        <div class="row-2">
          <div class="row"><label>E-posta</label><input type="email" name="email"></div>
          <div class="row"><label>Telefon</label><input type="text" name="phone"></div>
        </div>
        <div class="row-2">
          <div class="row"><label>Kullanıcı Adı</label><input type="text" name="username" placeholder="Boşsa ad-soyaddan otomatik oluşturulur" pattern="[a-z0-9._-]{3,60}"></div>
          <div class="row"><label>Şifre</label><input type="text" name="password" placeholder="Boşsa rastgele oluşturulur" minlength="8"></div>
        </div>
        <div class="form-actions"><button type="submit" class="adm-btn adm-btn-primary">➕ Hesap Oluştur</button></div>
      </form>
    </div>
  </div>

  <div class="adm-panel">
    <div class="adm-panel-head">
      <h2>Müşteriler (<?= count($customers) ?>)</h2>
      <form method="get"><input type="text" name="q" value="<?= h($q) ?>" placeholder="Kullanıcı adı, ad, firma, e-posta…"> <button type="submit" class="adm-btn adm-btn-ghost">Ara</button></form>
    </div>
    <div class="adm-panel-body" style="padding:0">
      <?php if (!$customers): ?>
        <div class="adm-empty"><div class="ico">👤</div>Henüz müşteri hesabı yok.</div>
      <?php else: ?>
      <table class="adm-table">
        <thead><tr><th>Kullanıcı Adı</th><th>Ad Soyad</th><th>Firma</th><th>Durum</th><th>Ödeme</th><th>Son Giriş</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($customers as $c): $locked = !empty($c['locked_until']) && strtotime($c['locked_until']) > time(); ?>
          <tr>
            <td><code style="font-size:12px">@<?= h($c['username']) ?></code></td>
            <td><strong><?= h($c['full_name']) ?></strong></td>
            <td><?= h($c['company'] ?: '—') ?></td>
            <td><?= $c['is_active'] ? '<span class="badge badge-on">Aktif</span>' : '<span class="badge badge-off">Pasif</span>' ?><?= $locked ? ' <span class="badge badge-danger">Kilitli</span>' : '' ?></td>
            <td><?= (int)$c['paid_count'] ?></td>
            <td style="white-space:nowrap"><?= $c['last_login_at'] ? h(tr_date($c['last_login_at'], true)) : '—' ?></td>
            <td class="actions"><a href="?view=<?= (int)$c['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Detay</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
<?php } ?>

<?php require __DIR__ . '/_footer.php'; ?>
