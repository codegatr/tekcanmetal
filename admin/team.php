<?php
define('TM_ADMIN', true);
$adminTitle = 'Ekip';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $fn = trim($_POST['full_name'] ?? '');
    $pos = trim($_POST['position'] ?? '');
    if ($fn === '' || $pos === '') adm_back_with('error', 'Ad ve pozisyon zorunlu.', 'admin/team.php');

    $data = [
        'full_name'  => $fn,
        'position'   => $pos,
        'bio'        => trim($_POST['bio'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'linkedin'   => trim($_POST['linkedin'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
    ];
    $data['photo'] = adm_handle_image_upload('photo', 'uploads/team', $_POST['existing_photo'] ?? null);
    $newId = adm_save('tm_team', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'team', $newId, "Ekip: $fn");
    adm_back_with('success', 'Ekip üyesi kaydedildi.', 'admin/team.php');
}

if ($action === 'delete' && $id) {
    adm_delete('tm_team', $id);
    log_activity('delete', 'team', $id);
    adm_back_with('success', 'Ekip üyesi silindi.', 'admin/team.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_team', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/team.php');
}

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_team WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Ekip üyesi bulunamadı.', 'admin/team.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Ekip Üyesi Düzenle' : 'Yeni Ekip Üyesi' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Ad Soyad *</label><input type="text" name="full_name" value="<?= h($row['full_name'] ?? '') ?>" required></div>
        <div class="row"><label>Pozisyon *</label><input type="text" name="position" value="<?= h($row['position'] ?? '') ?>" required></div>
      </div>
      <div class="row"><label>Biyografi / Kısa Tanıtım</label><textarea name="bio" rows="4"><?= h($row['bio'] ?? '') ?></textarea></div>
      <div class="row-2">
        <div class="row"><label>E-posta</label><input type="email" name="email" value="<?= h($row['email'] ?? '') ?>"></div>
        <div class="row"><label>Telefon</label><input type="text" name="phone" value="<?= h($row['phone'] ?? '') ?>"></div>
      </div>
      <div class="row-2">
        <div class="row"><label>LinkedIn URL</label><input type="text" name="linkedin" value="<?= h($row['linkedin'] ?? '') ?>"></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
      </div>
      <div class="row">
        <label>Fotoğraf</label>
        <?php if (!empty($row['photo'])): ?>
          <div style="margin-bottom:8px"><img src="<?= h(img_url($row['photo'])) ?>" style="max-height:160px;border-radius:6px"></div>
        <?php endif; ?>
        <input type="file" name="photo" accept="image/*">
        <input type="hidden" name="existing_photo" value="<?= h($row['photo'] ?? '') ?>">
      </div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('team.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_team ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Tüm Ekip Üyeleri (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Üye</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">👥</div>Ekip üyesi eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Foto</th><th>Ad</th><th>Pozisyon</th><th>İletişim</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['photo'])): ?><img src="<?= h(img_url($r['photo'])) ?>" class="thumb"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td><strong><?= h($r['full_name']) ?></strong></td>
          <td><?= h($r['position']) ?></td>
          <td style="font-size:11px;color:var(--text-muted)"><?= h($r['email'] ?: '') ?><?= $r['phone'] ? '<br>' . h($r['phone']) : '' ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?></a>
            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
            <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Silmek istediğinize emin misiniz?">Sil</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
