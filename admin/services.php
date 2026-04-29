<?php
define('TM_ADMIN', true);
$adminTitle = 'Hizmetler';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') adm_back_with('error', 'Başlık zorunlu.', 'admin/services.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);

    // features: satır satır liste → JSON array
    $featRaw = trim($_POST['features_raw'] ?? '');
    $features = [];
    foreach (preg_split('/\r?\n/', $featRaw) as $line) {
        $line = trim($line);
        if ($line !== '') $features[] = $line;
    }
    $featJson = $features ? json_encode($features, JSON_UNESCAPED_UNICODE) : null;

    $data = [
        'slug'        => $slug,
        'title'       => $title,
        'short_desc'  => trim($_POST['short_desc'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'icon'        => trim($_POST['icon'] ?? ''),
        'features'    => $featJson,
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];
    $data['image'] = adm_handle_image_upload('image', 'uploads/services', $_POST['existing_image'] ?? null);
    $newId = adm_save('tm_services', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'service', $newId, "Hizmet: $title");
    adm_back_with('success', 'Hizmet kaydedildi.', 'admin/services.php');
}

if ($action === 'delete' && $id) {
    adm_delete('tm_services', $id);
    log_activity('delete', 'service', $id);
    adm_back_with('success', 'Hizmet silindi.', 'admin/services.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_services', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/services.php');
}

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_services WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Hizmet bulunamadı.', 'admin/services.php');

    $featTxt = '';
    if (!empty($row['features'])) {
        $arr = json_decode($row['features'], true);
        if (is_array($arr)) $featTxt = implode("\n", $arr);
    }
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Hizmet Düzenle' : 'Yeni Hizmet' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Başlık *</label><input type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required></div>
        <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir"></div>
      </div>
      <div class="row-2">
        <div class="row"><label>İkon (emoji veya SVG sınıfı)</label><input type="text" name="icon" value="<?= h($row['icon'] ?? '') ?>" placeholder="örn: ⚙️ veya bir karakter"></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
      </div>
      <div class="row"><label>Kısa Açıklama</label><textarea name="short_desc" rows="2" maxlength="400"><?= h($row['short_desc'] ?? '') ?></textarea></div>
      <div class="row"><label>Detaylı Açıklama</label><textarea name="description" rows="8"><?= h($row['description'] ?? '') ?></textarea></div>
      <div class="row">
        <label>Özellikler / Avantajlar (her satıra bir madde)</label>
        <textarea name="features_raw" rows="6" placeholder="Hassas ölçü tolerans&#10;CNC kontrollü kesim&#10;En geniş kalınlık aralığı"><?= h($featTxt) ?></textarea>
      </div>
      <div class="row">
        <label>Görsel</label>
        <?php if (!empty($row['image'])): ?>
          <div style="margin-bottom:8px"><img src="<?= h(img_url($row['image'])) ?>" style="max-height:160px;border-radius:6px"></div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
        <input type="hidden" name="existing_image" value="<?= h($row['image'] ?? '') ?>">
      </div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('services.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_services ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Tüm Hizmetler (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Hizmet</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">🛠</div>Hizmet eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Görsel</th><th>İkon</th><th>Başlık</th><th>Slug</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['image'])): ?><img src="<?= h(img_url($r['image'])) ?>" class="thumb"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td style="font-size:18px"><?= h($r['icon'] ?? '') ?></td>
          <td><strong><?= h($r['title']) ?></strong></td>
          <td><code><?= h($r['slug']) ?></code></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?></a>
            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
            <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Hizmeti silmek istediğinize emin misiniz?">Sil</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
