<?php
define('TM_ADMIN', true);
$adminTitle = 'Çözüm Ortakları';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name === '') adm_back_with('error', 'Firma adı zorunlu.', 'admin/partners.php');

    $data = [
        'name'        => $name,
        'website'     => trim($_POST['website'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];
    $data['logo'] = adm_handle_image_upload('logo', 'uploads/partners', $_POST['existing_logo'] ?? null);
    $data = i18n_post_merge($data, ['name', 'description']);
    $newId = adm_save('tm_partners', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'partner', $newId, "Partner: $name");
    adm_back_with('success', 'Çözüm ortağı kaydedildi.', 'admin/partners.php');
}

if ($action === 'delete' && $id) {
    adm_delete('tm_partners', $id);
    adm_back_with('success', 'Silindi.', 'admin/partners.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_partners', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/partners.php');
}

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_partners WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/partners.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Çözüm Ortağı Düzenle' : 'Yeni Çözüm Ortağı' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Firma Adı *</label><input type="text" name="name" value="<?= h($row['name'] ?? '') ?>" required><?= i18n_inputs($row, 'name') ?></div>
        <div class="row"><label>Website</label><input type="text" name="website" value="<?= h($row['website'] ?? '') ?>" placeholder="https://"></div>
      </div>
      <div class="row"><label>Kısa Açıklama</label><textarea name="description" rows="2" maxlength="400"><?= h($row['description'] ?? '') ?></textarea><?= i18n_inputs($row, 'description', true, 3) ?></div>
      <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>" style="max-width:120px"></div>
      <div class="row">
        <label>Logo</label>
        <?php if (!empty($row['logo'])): ?>
          <div style="margin-bottom:8px;background:#fff;padding:10px;border-radius:6px;display:inline-block"><img src="<?= h(img_url($row['logo'])) ?>" style="max-height:80px"></div>
        <?php endif; ?>
        <input type="file" name="logo" accept="image/*">
        <input type="hidden" name="existing_logo" value="<?= h($row['logo'] ?? '') ?>">
      </div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('partners.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?= i18n_tabs_js() ?>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_partners ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Çözüm Ortakları (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Ortak</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">🤝</div>Henüz çözüm ortağı eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Logo</th><th>Firma</th><th>Web</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['logo'])): ?><img src="<?= h(img_url($r['logo'])) ?>" class="thumb" style="background:#fff;padding:4px"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td><strong><?= h($r['name']) ?></strong></td>
          <td><?php if ($r['website']): ?><a href="<?= h($r['website']) ?>" target="_blank" style="color:var(--accent)">↗</a><?php endif; ?></td>
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
