<?php
define('TM_ADMIN', true);
$adminTitle = 'Sayfalar';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') adm_back_with('error', 'Başlık zorunlu.', 'admin/pages.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);

    $data = [
        'slug'       => $slug,
        'title'      => $title,
        'subtitle'   => trim($_POST['subtitle'] ?? ''),
        'content'    => $_POST['content'] ?? '',
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_desc'  => trim($_POST['meta_desc'] ?? ''),
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
    ];
    $data['hero_image'] = adm_handle_image_upload('hero_image', 'uploads/pages', $_POST['existing_hero'] ?? null);

    $newId = adm_save('tm_pages', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'page', $newId, "Sayfa: $title");
    adm_back_with('success', 'Sayfa kaydedildi.', 'admin/pages.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_pages', $id); adm_back_with('success', 'Silindi.', 'admin/pages.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_pages', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/pages.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_pages WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/pages.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-grid-2">
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Sayfa Düzenle' : 'Yeni Sayfa' ?></h2></div>
        <div class="adm-panel-body">
          <div class="row"><label>Başlık *</label><input type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required></div>
          <div class="row"><label>Alt Başlık</label><input type="text" name="subtitle" value="<?= h($row['subtitle'] ?? '') ?>"></div>
          <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="örn: kvkk, hakkimizda, cerez"></div>
          <div class="row">
            <label>İçerik (HTML kabul edilir)</label>
            <textarea name="content" rows="22" id="pageContent"><?= h($row['content'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Yayın</h2></div>
        <div class="adm-panel-body">
          <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
          <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
        </div>
      </div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>SEO</h2></div>
        <div class="adm-panel-body">
          <div class="row"><label>Meta Başlık</label><input type="text" name="meta_title" value="<?= h($row['meta_title'] ?? '') ?>"></div>
          <div class="row"><label>Meta Açıklama</label><textarea name="meta_desc" rows="3" maxlength="300"><?= h($row['meta_desc'] ?? '') ?></textarea></div>
        </div>
      </div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Hero Görsel</h2></div>
        <div class="adm-panel-body">
          <?php if (!empty($row['hero_image'])): ?>
            <div style="margin-bottom:8px"><img src="<?= h(img_url($row['hero_image'])) ?>" style="max-width:100%;border-radius:6px"></div>
          <?php endif; ?>
          <input type="file" name="hero_image" accept="image/*">
          <input type="hidden" name="existing_hero" value="<?= h($row['hero_image'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('pages.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.getElementById('pageContent'), {
  toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','blockQuote','insertTable','|','undo','redo']
}).catch(e=>console.error(e));
</script>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_pages ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Sayfalar (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Sayfa</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">📄</div>Sayfa eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Başlık</th><th>Slug</th><th>Güncelleme</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><strong><?= h($r['title']) ?></strong><br><small style="color:var(--text-muted)"><?= h($r['subtitle'] ?: '') ?></small></td>
          <td><code><?= h($r['slug']) ?></code></td>
          <td style="font-size:11px"><?= h(tr_date($r['updated_at'], true)) ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a href="<?= h(url('sayfa.php?slug=' . urlencode($r['slug']))) ?>" target="_blank" class="adm-btn adm-btn-sm adm-btn-ghost">↗ Gör</a>
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
