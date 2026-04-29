<?php
define('TM_ADMIN', true);
$adminTitle = 'Ürünler';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Galeri görseli silme
if ($action === 'delete-img' && $id) {
    $img = row("SELECT * FROM tm_product_images WHERE id=?", [$id]);
    if ($img) {
        $pid = (int)$img['product_id'];
        @unlink(__DIR__ . '/../' . $img['image']);
        adm_delete('tm_product_images', $id);
        adm_back_with('success', 'Galeri görseli silindi.', "admin/products.php?action=edit&id=$pid");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $cat  = (int)($_POST['category_id'] ?? 0);
    if ($name === '' || $cat === 0) adm_back_with('error', 'Ad ve kategori zorunlu.', 'admin/products.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($name);

    // specs: textarea satır satır "anahtar:değer" → JSON
    $specsRaw = trim($_POST['specs_raw'] ?? '');
    $specsArr = [];
    foreach (preg_split('/\r?\n/', $specsRaw) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, ':') === false) continue;
        [$k, $v] = array_map('trim', explode(':', $line, 2));
        if ($k !== '') $specsArr[] = ['k' => $k, 'v' => $v];
    }
    $specsJson = $specsArr ? json_encode($specsArr, JSON_UNESCAPED_UNICODE) : null;

    $data = [
        'category_id' => $cat,
        'slug'        => $slug,
        'name'        => $name,
        'short_desc'  => trim($_POST['short_desc'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'specs'       => $specsJson,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
    ];
    $data['image'] = adm_handle_image_upload('image', 'uploads/products', $_POST['existing_image'] ?? null);

    $newId = adm_save('tm_products', $data, $editId ?: null);

    // Galeri görselleri (çoklu yükleme)
    if (!empty($_FILES['gallery']['name'][0])) {
        $cnt = count($_FILES['gallery']['name']);
        for ($i = 0; $i < $cnt; $i++) {
            if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $f = [
                'name'     => $_FILES['gallery']['name'][$i],
                'type'     => $_FILES['gallery']['type'][$i],
                'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
                'error'    => $_FILES['gallery']['error'][$i],
                'size'     => $_FILES['gallery']['size'][$i],
            ];
            $up = upload_image($f, 'uploads/products');
            if ($up) {
                q("INSERT INTO tm_product_images (product_id,image,sort_order) VALUES (?,?,?)",
                  [$newId, $up['path'], $i]);
            }
        }
    }

    log_activity($editId ? 'update' : 'create', 'product', $newId, "Ürün: $name");
    adm_back_with('success', 'Ürün kaydedildi.', "admin/products.php?action=edit&id=$newId");
}

if ($action === 'delete' && $id) {
    // Önce galeri görsellerini sil (dosya + DB)
    foreach (all("SELECT image FROM tm_product_images WHERE product_id=?", [$id]) as $img) {
        @unlink(__DIR__ . '/../' . $img['image']);
    }
    q("DELETE FROM tm_product_images WHERE product_id=?", [$id]);
    adm_delete('tm_products', $id);
    log_activity('delete', 'product', $id);
    adm_back_with('success', 'Ürün silindi.', 'admin/products.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_products', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/products.php');
}
if ($action === 'feature' && $id) {
    adm_toggle('tm_products', $id, 'is_featured');
    adm_back_with('success', 'Öne çıkarıldı/kaldırıldı.', 'admin/products.php');
}

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_products WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Ürün bulunamadı.', 'admin/products.php');
    $cats = all("SELECT id, name FROM tm_categories WHERE is_active=1 ORDER BY sort_order, name");
    $gallery = $action === 'edit' ? all("SELECT * FROM tm_product_images WHERE product_id=? ORDER BY sort_order", [$id]) : [];

    // specs: JSON → satır satır
    $specsTxt = '';
    if (!empty($row['specs'])) {
        $arr = json_decode($row['specs'], true);
        if (is_array($arr)) {
            foreach ($arr as $sp) $specsTxt .= ($sp['k'] ?? '') . ': ' . ($sp['v'] ?? '') . "\n";
        }
    }
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-grid-2">
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Ürün Düzenle' : 'Yeni Ürün' ?></h2></div>
        <div class="adm-panel-body">
          <div class="row"><label>Ürün Adı *</label><input type="text" name="name" value="<?= h($row['name'] ?? '') ?>" required></div>
          <div class="row-2">
            <div class="row">
              <label>Kategori *</label>
              <select name="category_id" required>
                <option value="">— Seçin —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= (int)$c['id'] ?>" <?= ((int)($row['category_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row"><label>Slug</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir"></div>
          </div>
          <div class="row"><label>Kısa Açıklama</label><textarea name="short_desc" rows="2" maxlength="400"><?= h($row['short_desc'] ?? '') ?></textarea></div>
          <div class="row"><label>Detaylı Açıklama</label><textarea name="description" rows="8"><?= h($row['description'] ?? '') ?></textarea></div>
          <div class="row">
            <label>Teknik Özellikler (her satıra <code>anahtar: değer</code>)</label>
            <textarea name="specs_raw" rows="6" placeholder="Standart: TS EN 10025-2&#10;Kalite: S235JR&#10;Kalınlık: 2-30 mm"><?= h($specsTxt) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Yayın</h2></div>
        <div class="adm-panel-body">
          <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
          <div class="row"><label class="checkbox"><input type="checkbox" name="is_featured" <?= !empty($row['is_featured']) ? 'checked' : '' ?>> Öne Çıkan</label></div>
          <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
        </div>
      </div>

      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Ana Görsel</h2></div>
        <div class="adm-panel-body">
          <?php if (!empty($row['image'])): ?>
            <div style="margin-bottom:8px"><img src="<?= h(img_url($row['image'])) ?>" style="max-width:100%;border-radius:6px"></div>
          <?php endif; ?>
          <input type="file" name="image" accept="image/*">
          <input type="hidden" name="existing_image" value="<?= h($row['image'] ?? '') ?>">
        </div>
      </div>

      <?php if ($action === 'edit'): ?>
      <div class="adm-panel">
        <div class="adm-panel-head"><h2>Galeri Görselleri</h2></div>
        <div class="adm-panel-body">
          <input type="file" name="gallery[]" accept="image/*" multiple>
          <small style="display:block;margin-top:6px;color:var(--text-muted)">Birden fazla seçebilirsiniz.</small>
          <?php if ($gallery): ?>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px">
              <?php foreach ($gallery as $g): ?>
                <div style="position:relative">
                  <img src="<?= h(img_url($g['image'])) ?>" style="width:100%;height:80px;object-fit:cover;border-radius:4px">
                  <a href="?action=delete-img&id=<?= (int)$g['id'] ?>" data-confirm="Görseli silmek istediğinize emin misiniz?" style="position:absolute;top:4px;right:4px;background:#dc2626;color:#fff;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;text-decoration:none">×</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('products.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

// Liste
$catFilter = (int)($_GET['cat'] ?? 0);
$qstr = trim($_GET['q'] ?? '');
$where = ['1=1']; $params = [];
if ($catFilter) { $where[] = 'p.category_id=?'; $params[] = $catFilter; }
if ($qstr !== '') { $where[] = '(p.name LIKE ? OR p.short_desc LIKE ?)'; $params[] = "%$qstr%"; $params[] = "%$qstr%"; }
$rows = all("SELECT p.*, c.name AS cat_name FROM tm_products p LEFT JOIN tm_categories c ON c.id=p.category_id WHERE " . implode(' AND ', $where) . " ORDER BY p.sort_order, p.id DESC", $params);
$cats = all("SELECT id, name FROM tm_categories ORDER BY sort_order, name");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Tüm Ürünler (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Ürün</a>
  </div>
  <div class="adm-panel-body">
    <form method="get" style="display:flex;gap:8px;margin-bottom:14px">
      <select name="cat" onchange="this.form.submit()" style="max-width:240px">
        <option value="0">Tüm kategoriler</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $catFilter === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="q" value="<?= h($qstr) ?>" placeholder="Ürün ara…" style="flex:1">
      <button class="adm-btn adm-btn-ghost">Ara</button>
    </form>

    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">📦</div>Ürün bulunamadı.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Görsel</th><th>Ad</th><th>Kategori</th><th>Görüntülenme</th><th>Sıra</th><th>Öne</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['image'])): ?><img src="<?= h(img_url($r['image'])) ?>" class="thumb"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td><strong><?= h($r['name']) ?></strong><br><small style="color:var(--text-muted)"><?= h($r['slug']) ?></small></td>
          <td><?= h($r['cat_name'] ?? '—') ?></td>
          <td><?= (int)$r['view_count'] ?></td>
          <td><?= (int)$r['sort_order'] ?></td>
          <td><a href="?action=feature&id=<?= (int)$r['id'] ?>" style="font-size:18px;text-decoration:none;color:<?= $r['is_featured'] ? 'var(--accent)' : 'var(--text-muted)' ?>"><?= $r['is_featured'] ? '★' : '☆' ?></a></td>
          <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
          <td class="actions">
            <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?></a>
            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
            <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Bu ürünü ve galerideki tüm görsellerini silmek istediğinize emin misiniz?">Sil</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
