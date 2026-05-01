<?php
define('TM_ADMIN', true);
$adminTitle = 'Slider';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') adm_back_with('error', 'Başlık zorunlu.', 'admin/sliders.php');

    $data = [
        'title'       => $title,
        'subtitle'    => trim($_POST['subtitle'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'link_text'   => trim($_POST['link_text'] ?? ''),
        'link_url'    => trim($_POST['link_url'] ?? ''),
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];

    $img = adm_handle_image_upload('image', 'uploads/sliders', $_POST['existing_image'] ?? null);
    if (!$img && !$editId) adm_back_with('error', 'Görsel zorunlu.', 'admin/sliders.php');
    if ($img) $data['image'] = $img;

    $newId = adm_save('tm_sliders', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'slider', $newId, "Slider: $title");
    adm_back_with('success', 'Slider kaydedildi.', 'admin/sliders.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_sliders', $id); adm_back_with('success', 'Silindi.', 'admin/sliders.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_sliders', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/sliders.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_sliders WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/sliders.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Slider Düzenle' : 'Yeni Slider' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row"><label>Başlık *</label><input type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required></div>
      <div class="row"><label>Alt Başlık</label><input type="text" name="subtitle" value="<?= h($row['subtitle'] ?? '') ?>"></div>
      <div class="row"><label>Açıklama</label><textarea name="description" rows="3"><?= h($row['description'] ?? '') ?></textarea></div>
      <div class="row-2">
        <div class="row"><label>Buton Metni</label><input type="text" name="link_text" value="<?= h($row['link_text'] ?? '') ?>" placeholder="örn: Detayları Gör"></div>
        <div class="row"><label>Buton URL</label><input type="text" name="link_url" value="<?= h($row['link_url'] ?? '') ?>" placeholder="örn: urunler.php"></div>
      </div>
      <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>" style="max-width:120px"></div>
      <div class="row">
        <label>Görsel <?= $action === 'new' ? '*' : '' ?> (önerilen 1920×800px)</label>
        <?php if (!empty($row['image'])): ?>
          <div style="margin-bottom:8px"><img src="<?= h(img_url($row['image'])) ?>" style="max-width:100%;max-height:200px;border-radius:6px"></div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*" <?= $action === 'new' ? 'required' : '' ?>>
        <input type="hidden" name="existing_image" value="<?= h($row['image'] ?? '') ?>">
      </div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('sliders.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_sliders ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Anasayfa Slider'ları (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Slider</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">🎬</div>Slider eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Görsel</th><th>Başlık</th><th>Buton</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['image'])): ?><img src="<?= h(img_url($r['image'])) ?>" style="width:120px;height:50px;object-fit:cover;border-radius:4px"><?php endif; ?></td>
          <td><strong><?= h($r['title']) ?></strong><br><small style="color:var(--text-muted)"><?= h($r['subtitle'] ?: '') ?></small></td>
          <td><?= h($r['link_text'] ?: '—') ?></td>
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
