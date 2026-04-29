<?php
define('TM_ADMIN', true);
$adminTitle = 'Kategoriler';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    if ($name === '') adm_back_with('error', 'Ad zorunlu.', 'admin/categories.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
    $data = [
        'name'             => $name,
        'slug'             => $slug,
        'description'      => trim($_POST['description'] ?? ''),
        'icon'             => trim($_POST['icon'] ?? ''),
        'sort_order'       => (int)($_POST['sort_order'] ?? 0),
        'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    $data['image'] = adm_handle_image_upload('image', 'uploads/categories', $_POST['existing_image'] ?? null);
    $newId = adm_save('tm_categories', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'category', $newId, "Kategori: $name");
    adm_back_with('success', 'Kategori kaydedildi.', 'admin/categories.php');
}

if ($action === 'delete' && $id) {
    adm_delete('tm_categories', $id);
    log_activity('delete', 'category', $id);
    adm_back_with('success', 'Kategori silindi.', 'admin/categories.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_categories', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/categories.php');
}

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_categories WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Kategori bulunamadı.', 'admin/categories.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Kategori Düzenle' : 'Yeni Kategori' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Ad *</label><input type="text" name="name" value="<?= h($row['name'] ?? '') ?>" required></div>
        <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir"></div>
      </div>
      <div class="row"><label>Açıklama</label><textarea name="description" rows="3"><?= h($row['description'] ?? '') ?></textarea></div>
      <div class="row-2">
        <div class="row"><label>İkon (emoji veya HTML)</label><input type="text" name="icon" value="<?= h($row['icon'] ?? '') ?>"></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
      </div>
      <div class="row">
        <label>Görsel</label>
        <?php if (!empty($row['image'])): ?>
          <div style="margin-bottom:8px"><img src="<?= h(img_url($row['image'])) ?>" style="max-height:120px;border-radius:6px"></div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
        <input type="hidden" name="existing_image" value="<?= h($row['image'] ?? '') ?>">
      </div>
      <div class="row"><label>SEO Meta Açıklama</label><textarea name="meta_description" rows="2"><?= h($row['meta_description'] ?? '') ?></textarea></div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('categories.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT c.*, (SELECT COUNT(*) FROM tm_products p WHERE p.category_id=c.id) AS cnt FROM tm_categories c ORDER BY c.sort_order, c.name");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Tüm Kategoriler (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Kategori</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">🗂</div>Henüz kategori eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Görsel</th><th>Ad</th><th>Slug</th><th>Ürün</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td>
            <?php if (!empty($r['image'])): ?>
              <img src="<?= h(img_url($r['image'])) ?>" class="thumb">
            <?php else: ?><span style="opacity:.4">—</span><?php endif; ?>
          </td>
          <td><strong><?= h($r['name']) ?></strong></td>
          <td><code><?= h($r['slug']) ?></code></td>
          <td><?= (int)$r['cnt'] ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?></a>
            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
            <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Bu kategoriyi silmek istediğinize emin misiniz? Bağlı ürünler etkilenebilir.">Sil</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
