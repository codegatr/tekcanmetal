<?php
define('TM_ADMIN', true);
$adminTitle = 'Blog Kategorileri';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name === '') adm_back_with('error', 'Ad zorunlu.', 'admin/blog-categories.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($name);

    $data = [
        'slug'        => $slug,
        'name'        => $name,
        'description' => trim($_POST['description'] ?? ''),
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ];
    $newId = adm_save('tm_blog_categories', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'blog_category', $newId);
    adm_back_with('success', 'Blog kategorisi kaydedildi.', 'admin/blog-categories.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_blog_categories', $id); adm_back_with('success', 'Silindi.', 'admin/blog-categories.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_blog_categories', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/blog-categories.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_blog_categories WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/blog-categories.php');
?>
<form method="post" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Blog Kategorisi Düzenle' : 'Yeni Blog Kategorisi' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Ad *</label><input type="text" name="name" value="<?= h($row['name'] ?? '') ?>" required></div>
        <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir"></div>
      </div>
      <div class="row"><label>Açıklama</label><textarea name="description" rows="3" maxlength="400"><?= h($row['description'] ?? '') ?></textarea></div>
      <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>" style="max-width:120px"></div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('blog-categories.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT c.*, (SELECT COUNT(*) FROM tm_blog_posts p WHERE p.category_id=c.id) AS cnt FROM tm_blog_categories c ORDER BY sort_order, name");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Blog Kategorileri (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Kategori</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">📁</div>Blog kategorisi eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Ad</th><th>Slug</th><th>Yazı</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><strong><?= h($r['name']) ?></strong></td>
          <td><code><?= h($r['slug']) ?></code></td>
          <td><?= (int)$r['cnt'] ?></td>
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
