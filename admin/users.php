<?php
define('TM_ADMIN', true);
$adminTitle = 'Kullanıcılar';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$me = (int)($_SESSION['admin_id'] ?? 0);

/* ========== POST SAVE ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId   = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role     = $_POST['role'] ?? 'admin';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password_confirm'] ?? '';

    if (!in_array($role, ['superadmin', 'admin', 'editor'], true)) $role = 'admin';

    if ($username === '' || $email === '' || $fullName === '') {
        adm_back_with('error', 'Kullanıcı adı, e-posta ve ad soyad zorunlu.', 'admin/users.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        adm_back_with('error', 'Geçerli bir e-posta girin.', 'admin/users.php');
    }

    // Unique check
    $dup = row("SELECT id FROM tm_users WHERE (username=? OR email=?) AND id<>?",
        [$username, $email, $editId]);
    if ($dup) adm_back_with('error', 'Kullanıcı adı veya e-posta zaten kayıtlı.', 'admin/users.php');

    // Self-protection: cannot deactivate or demote yourself
    if ($editId && $editId === $me) {
        $isActive = 1;
        $existingRole = val("SELECT role FROM tm_users WHERE id=?", [$me]);
        $role = $existingRole ?: $role;
    }

    // Password handling
    if (!$editId) {
        // Yeni kullanıcı: şifre zorunlu
        if (strlen($pass) < 6) adm_back_with('error', 'Şifre en az 6 karakter olmalı.', 'admin/users.php');
        if ($pass !== $pass2) adm_back_with('error', 'Şifreler eşleşmiyor.', 'admin/users.php');
    } elseif ($pass !== '') {
        // Düzenleme: şifre boş ise eski şifre kalır
        if (strlen($pass) < 6) adm_back_with('error', 'Yeni şifre en az 6 karakter olmalı.', 'admin/users.php');
        if ($pass !== $pass2) adm_back_with('error', 'Şifreler eşleşmiyor.', 'admin/users.php');
    }

    $data = [
        'username'  => $username,
        'email'     => $email,
        'full_name' => $fullName,
        'role'      => $role,
        'is_active' => $isActive,
    ];
    if ($pass !== '') {
        $data['password'] = password_hash($pass, PASSWORD_BCRYPT);
    }

    $newId = adm_save('tm_users', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'user', $newId);
    adm_back_with('success', 'Kullanıcı kaydedildi.', 'admin/users.php');
}

/* ========== ACTIONS ========== */
if ($action === 'delete' && $id) {
    if ($id === $me) adm_back_with('error', 'Kendi hesabınızı silemezsiniz.', 'admin/users.php');
    // Son superadmin'i silme koruması
    $target = row("SELECT role FROM tm_users WHERE id=?", [$id]);
    if ($target && $target['role'] === 'superadmin') {
        $cnt = (int)val("SELECT COUNT(*) FROM tm_users WHERE role='superadmin' AND is_active=1 AND id<>?", [$id]);
        if ($cnt < 1) adm_back_with('error', 'Son superadmin silinemez.', 'admin/users.php');
    }
    adm_delete('tm_users', $id);
    log_activity('delete', 'user', $id);
    adm_back_with('success', 'Kullanıcı silindi.', 'admin/users.php');
}

if ($action === 'toggle' && $id) {
    if ($id === $me) adm_back_with('error', 'Kendi hesabınızı pasif yapamazsınız.', 'admin/users.php');
    $target = row("SELECT role, is_active FROM tm_users WHERE id=?", [$id]);
    if ($target && $target['role'] === 'superadmin' && $target['is_active']) {
        $cnt = (int)val("SELECT COUNT(*) FROM tm_users WHERE role='superadmin' AND is_active=1 AND id<>?", [$id]);
        if ($cnt < 1) adm_back_with('error', 'Son aktif superadmin pasifleştirilemez.', 'admin/users.php');
    }
    adm_toggle('tm_users', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/users.php');
}

/* ========== EDIT/NEW FORM ========== */
if (in_array($action, ['edit', 'new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_users WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Kullanıcı bulunamadı.', 'admin/users.php');
    $isSelf = !empty($row['id']) && (int)$row['id'] === $me;
    ?>
    <form method="post" class="adm-form" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

        <div class="adm-panel">
            <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı' ?></h2></div>
            <div class="adm-panel-body">
                <div class="row-2">
                    <div class="row"><label>Ad Soyad *</label><input type="text" name="full_name" value="<?= h($row['full_name'] ?? '') ?>" required></div>
                    <div class="row"><label>Kullanıcı Adı *</label><input type="text" name="username" value="<?= h($row['username'] ?? '') ?>" required pattern="[a-zA-Z0-9_]{3,80}"></div>
                </div>
                <div class="row-2">
                    <div class="row"><label>E-posta *</label><input type="email" name="email" value="<?= h($row['email'] ?? '') ?>" required></div>
                    <div class="row">
                        <label>Rol</label>
                        <select name="role" <?= $isSelf ? 'disabled' : '' ?>>
                            <option value="editor"     <?= ($row['role'] ?? '') === 'editor'     ? 'selected' : '' ?>>Editör</option>
                            <option value="admin"      <?= ($row['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>Yönetici</option>
                            <option value="superadmin" <?= ($row['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Süper Yönetici</option>
                        </select>
                        <?php if ($isSelf): ?><input type="hidden" name="role" value="<?= h($row['role']) ?>"><small>Kendi rolünüzü değiştiremezsiniz.</small><?php endif; ?>
                    </div>
                </div>

                <hr style="margin:12px 0;border:0;border-top:1px solid #2a3552">

                <div class="row-2">
                    <div class="row"><label>Şifre <?= $action === 'edit' ? '(boş bırak değişmez)' : '*' ?></label><input type="password" name="password" autocomplete="new-password" <?= $action === 'new' ? 'required' : '' ?>></div>
                    <div class="row"><label>Şifre (Tekrar)</label><input type="password" name="password_confirm" autocomplete="new-password" <?= $action === 'new' ? 'required' : '' ?>></div>
                </div>

                <div class="row">
                    <label class="checkbox">
                        <input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?> <?= $isSelf ? 'disabled checked' : '' ?>>
                        Aktif
                    </label>
                    <?php if ($isSelf): ?><input type="hidden" name="is_active" value="1"><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= h(admin_url('users.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
            <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
        </div>
    </form>
    <?php require __DIR__ . '/_footer.php'; exit;
}

/* ========== LIST ========== */
$rows = all("SELECT id, username, email, full_name, role, is_active, last_login, last_ip, created_at FROM tm_users ORDER BY id");
$roleLabels = ['superadmin' => 'Süper Yönetici', 'admin' => 'Yönetici', 'editor' => 'Editör'];
?>

<div class="adm-panel">
    <div class="adm-panel-head">
        <h2>Kullanıcılar (<?= count($rows) ?>)</h2>
        <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Kullanıcı</a>
    </div>
    <div class="adm-panel-body" style="padding:0">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th><th>Ad Soyad / Kullanıcı Adı</th><th>E-posta</th><th>Rol</th>
                    <th>Son Giriş</th><th>Durum</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): $isSelf = (int)$r['id'] === $me; ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td>
                            <strong><?= h($r['full_name']) ?></strong>
                            <?php if ($isSelf): ?> <span class="badge">SİZ</span><?php endif; ?>
                            <br><small style="color:#aaa"><code><?= h($r['username']) ?></code></small>
                        </td>
                        <td><?= h($r['email']) ?></td>
                        <td><span class="badge"><?= h($roleLabels[$r['role']] ?? $r['role']) ?></span></td>
                        <td>
                            <?php if ($r['last_login']): ?>
                                <small><?= h(tr_date($r['last_login'])) ?></small>
                                <?php if ($r['last_ip']): ?><br><small style="color:#777"><code><?= h($r['last_ip']) ?></code></small><?php endif; ?>
                            <?php else: ?>
                                <small style="color:#777">—</small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                        <td class="actions">
                            <?php if (!$isSelf): ?>
                                <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Pasifleştir' : 'Etkinleştir' ?></a>
                            <?php endif; ?>
                            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
                            <?php if (!$isSelf): ?>
                                <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Kullanıcı silinecek. Emin misiniz?">Sil</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
