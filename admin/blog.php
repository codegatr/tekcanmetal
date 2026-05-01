<?php
define('TM_ADMIN', true);
$adminTitle = 'Blog Yazıları';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') adm_back_with('error', 'Başlık zorunlu.', 'admin/blog.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($title);

    // published_at kontrolü
    $publishedAt = trim($_POST['published_at'] ?? '');
    if ($publishedAt) {
        $ts = strtotime($publishedAt);
        $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : null;
    } else {
        $publishedAt = isset($_POST['publish_now']) ? date('Y-m-d H:i:s') : null;
    }

    $data = [
        'category_id'  => (int)($_POST['category_id'] ?? 0) ?: null,
        'slug'         => $slug,
        'title'        => $title,
        'excerpt'      => trim($_POST['excerpt'] ?? ''),
        'content'      => $_POST['content'] ?? '',
        'author'       => trim($_POST['author'] ?? '') ?: 'Tekcan Metal',
        'meta_title'   => trim($_POST['meta_title'] ?? ''),
        'meta_desc'    => trim($_POST['meta_desc'] ?? ''),
        'published_at' => $publishedAt,
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
    ];
    $data['cover_image'] = adm_handle_image_upload('cover_image', 'uploads/blog', $_POST['existing_cover'] ?? null);

    $newId = adm_save('tm_blog_posts', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'blog', $newId, "Blog: $title");
    adm_back_with('success', 'Blog yazısı kaydedildi.', 'admin/blog.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_blog_posts', $id); adm_back_with('success', 'Silindi.', 'admin/blog.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_blog_posts', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/blog.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_blog_posts WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/blog.php');
    $cats = all("SELECT id, name FROM tm_blog_categories WHERE is_active=1 ORDER BY sort_order, name");
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-grid-2">
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Blog Yazısı Düzenle' : 'Yeni Blog Yazısı' ?></h2></div>
        <div class="adm-panel-body">
          <div class="row"><label>Başlık *</label><input type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required></div>
          <div class="row-2">
            <div class="row">
              <label>Kategori</label>
              <select name="category_id">
                <option value="">— Kategori yok —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= ((int)($row['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir"></div>
          </div>
          <div class="row"><label>Özet</label><textarea name="excerpt" rows="2" maxlength="500"><?= h($row['excerpt'] ?? '') ?></textarea></div>
          <div class="row">
            <label>İçerik</label>
            <textarea name="content" rows="20" id="postContent"><?= h($row['content'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Yayın</h2></div>
        <div class="adm-panel-body">
          <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
          <div class="row">
            <label>Yayın Tarihi</label>
            <input type="datetime-local" name="published_at" value="<?= h($row['published_at'] ? date('Y-m-d\TH:i', strtotime($row['published_at'])) : '') ?>">
          </div>
          <?php if (empty($row['published_at'])): ?>
          <div class="row"><label class="checkbox"><input type="checkbox" name="publish_now"> Hemen Yayınla</label></div>
          <?php endif; ?>
          <div class="row"><label>Yazar</label><input type="text" name="author" value="<?= h($row['author'] ?? 'Tekcan Metal') ?>"></div>
        </div>
      </div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Kapak Görseli</h2></div>
        <div class="adm-panel-body">
          <?php if (!empty($row['cover_image'])): ?>
            <div style="margin-bottom:8px"><img src="<?= h(img_url($row['cover_image'])) ?>" style="max-width:100%;border-radius:6px"></div>
          <?php endif; ?>
          <input type="file" name="cover_image" accept="image/*">
          <input type="hidden" name="existing_cover" value="<?= h($row['cover_image'] ?? '') ?>">
        </div>
      </div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>SEO</h2></div>
        <div class="adm-panel-body">
          <div class="row"><label>Meta Başlık</label><input type="text" name="meta_title" value="<?= h($row['meta_title'] ?? '') ?>"></div>
          <div class="row"><label>Meta Açıklama</label><textarea name="meta_desc" rows="3" maxlength="300"><?= h($row['meta_desc'] ?? '') ?></textarea></div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('blog.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.getElementById('postContent'), {
  toolbar: ['heading','|','bold','italic','link','bulletedList','numberedList','|','blockQuote','insertTable','|','undo','redo']
}).catch(e=>console.error(e));
</script>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT p.*, c.name AS cat_name FROM tm_blog_posts p LEFT JOIN tm_blog_categories c ON c.id=p.category_id ORDER BY p.published_at DESC, p.id DESC");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Blog Yazıları (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Yazı</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">✍</div>Yazı eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Kapak</th><th>Başlık</th><th>Kategori</th><th>Yayın</th><th>Görüntüleme</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['cover_image'])): ?><img src="<?= h(img_url($r['cover_image'])) ?>" class="thumb"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td><strong><?= h($r['title']) ?></strong></td>
          <td><?= h($r['cat_name'] ?: '—') ?></td>
          <td style="font-size:11px"><?= h($r['published_at'] ? tr_date($r['published_at']) : '— taslak —') ?></td>
          <td><?= (int)$r['view_count'] ?></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <?php if ($r['published_at'] && $r['is_active']): ?>
              <a href="<?= h(url('blog-detay.php?slug=' . urlencode($r['slug']))) ?>" target="_blank" class="adm-btn adm-btn-sm adm-btn-ghost">↗</a>
            <?php endif; ?>
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
