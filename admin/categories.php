<?php
define('TM_ADMIN', true);
$adminTitle = 'Kategoriler';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// ═══════════════════════════════════════════════════
// FORM POST — Kaydet
// ═══════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    if ($name === '') adm_back_with('error', 'Kategori adı zorunlu.', 'admin/categories.php');

    $slug = trim($_POST['slug'] ?? '') ?: slugify($name);

    $data = [
        'name'        => $name,
        'slug'        => $slug,
        'short_desc'  => trim($_POST['short_desc'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'icon'        => trim($_POST['icon'] ?? ''),
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        'meta_title'  => trim($_POST['meta_title'] ?? ''),
        'meta_desc'   => trim($_POST['meta_desc'] ?? ''),
    ];

    // parent_id (alt kategori desteği)
    $parentId = (int)($_POST['parent_id'] ?? 0);
    $data['parent_id'] = $parentId > 0 ? $parentId : null;

    // Görsel yükleme
    $data['image'] = adm_handle_image_upload('image', 'uploads/categories', $_POST['existing_image'] ?? null);

    try {
        $data = i18n_post_merge($data, ['name', 'meta_title', 'short_desc', 'description', 'meta_desc']);
        $newId = adm_save('tm_categories', $data, $editId ?: null);
        log_activity($editId ? 'update' : 'create', 'category', $newId, "Kategori: $name");
        adm_back_with('success', 'Kategori başarıyla kaydedildi.', 'admin/categories.php');
    } catch (Throwable $e) {
        adm_back_with('error', 'Kayıt hatası: ' . $e->getMessage(), 'admin/categories.php');
    }
}

// ═══════════════════════════════════════════════════
// SİL / TOGGLE
// ═══════════════════════════════════════════════════
if ($action === 'delete' && $id) {
    // Bağlı ürün var mı kontrolü
    $productCount = (int) val("SELECT COUNT(*) FROM tm_products WHERE category_id=?", [$id]);
    if ($productCount > 0) {
        adm_back_with('error', "Bu kategoride $productCount ürün var. Önce ürünleri taşıyın veya silin.", 'admin/categories.php');
    }
    adm_delete('tm_categories', $id);
    log_activity('delete', 'category', $id);
    adm_back_with('success', 'Kategori silindi.', 'admin/categories.php');
}

if ($action === 'toggle' && $id) {
    adm_toggle('tm_categories', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/categories.php');
}

// ═══════════════════════════════════════════════════
// EKLE / DÜZENLE FORMU
// ═══════════════════════════════════════════════════
if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_categories WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) {
        adm_back_with('error', 'Kategori bulunamadı.', 'admin/categories.php');
    }
    // Alt kategori için ana kategori seçimi
    $parentCats = all("SELECT id, name FROM tm_categories WHERE parent_id IS NULL " .
                      ($action === 'edit' && $id ? "AND id <> $id " : "") .
                      "ORDER BY sort_order, name");
?>
<style>
.cat-form-grid{
  display:grid;
  grid-template-columns:1.5fr 1fr;
  gap:20px;
  align-items:start;
}
@media (max-width:900px){.cat-form-grid{grid-template-columns:1fr}}

.cat-form-tip{
  font-size:11.5px;color:#666;
  margin-top:5px;line-height:1.5;
}
.cat-form-tip code{
  background:#f1f5f9;padding:2px 6px;border-radius:3px;
  font-size:11px;color:#0c1e44;
}

.cat-img-preview{
  position:relative;
  display:inline-block;
  margin-bottom:10px;
}
.cat-img-preview img{
  max-height:140px;border:2px solid #e3e8ef;
  border-radius:4px;display:block;
}
.cat-img-preview .cat-img-name{
  position:absolute;bottom:6px;left:6px;
  background:rgba(5,13,36,.85);color:#fff;
  padding:3px 9px;font-size:11px;font-family:monospace;
  border-radius:3px;
}

.cat-charcounter{
  font-size:11px;color:#888;text-align:right;
  font-family:monospace;
  margin-top:3px;
}
.cat-charcounter.warn{color:#dc2626}
</style>

<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="cat-form-grid">

    <!-- SOL: Ana içerik -->
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head">
          <h2><?= $action === 'edit' ? '✏ Kategori Düzenle' : '➕ Yeni Kategori' ?></h2>
        </div>
        <div class="adm-panel-body">

          <div class="row">
            <label>Kategori Adı <span style="color:#dc2626">*</span></label>
            <input type="text" name="name" value="<?= h($row['name'] ?? '') ?>" required placeholder="Örn: Sac Ürünleri"><?= i18n_inputs($row, 'name') ?>
            <div class="cat-form-tip">Müşterinin gördüğü kategori başlığı</div>
          </div>

          <div class="row-2">
            <div class="row">
              <label>Slug (URL)</label>
              <input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" placeholder="otomatik üretilir">
              <div class="cat-form-tip">URL'de görünür: <code>/kategori/<strong id="slugPreview"><?= h($row['slug'] ?? 'sac-urunleri') ?></strong></code></div>
            </div>
            <div class="row">
              <label>Üst Kategori</label>
              <select name="parent_id">
                <option value="0">— Yok (Ana Kategori) —</option>
                <?php foreach ($parentCats as $pc): ?>
                  <option value="<?= (int)$pc['id'] ?>" <?= ($row['parent_id'] ?? 0) == $pc['id'] ? 'selected' : '' ?>>
                    <?= h($pc['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <label>Kısa Açıklama</label>
            <textarea name="short_desc" rows="2" maxlength="300" placeholder="Kategori kart görünümünde çıkacak kısa metin (max 300 karakter)"><?= h($row['short_desc'] ?? '') ?></textarea><?= i18n_inputs($row, 'short_desc', true, 2) ?>
            <div class="cat-charcounter" id="shortDescCount">0 / 300</div>
          </div>

          <div class="row">
            <label>Detaylı Açıklama</label>
            <textarea name="description" rows="6" placeholder="Kategori detay sayfasında çıkacak uzun metin (HTML kullanabilirsiniz)"><?= h($row['description'] ?? '') ?></textarea><?= i18n_inputs($row, 'description', true, 6) ?>
            <div class="cat-form-tip">İsterseniz HTML kullanabilirsiniz. Örn: <code>&lt;p&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;strong&gt;</code></div>
          </div>

        </div>
      </div>

      <!-- SEO PANEL -->
      <div class="adm-panel" style="margin-top:18px">
        <div class="adm-panel-head">
          <h2>🌐 SEO Ayarları</h2>
        </div>
        <div class="adm-panel-body">
          <div class="row">
            <label>SEO Meta Başlık</label>
            <input type="text" name="meta_title" value="<?= h($row['meta_title'] ?? '') ?>" maxlength="200" placeholder="Boş bırakırsanız kategori adı kullanılır"><?= i18n_inputs($row, 'meta_title') ?>
            <div class="cat-form-tip">Tarayıcı sekmesinde ve Google sonuçlarında görünür</div>
          </div>
          <div class="row">
            <label>SEO Meta Açıklama</label>
            <textarea name="meta_desc" rows="3" maxlength="300" placeholder="Google arama sonuçlarında görünecek açıklama (155-160 karakter ideal)"><?= h($row['meta_desc'] ?? '') ?></textarea><?= i18n_inputs($row, 'meta_desc', true, 2) ?>
            <div class="cat-charcounter" id="metaDescCount">0 / 300</div>
          </div>
        </div>
      </div>
    </div>

    <!-- SAĞ: Ayarlar + Görsel -->
    <div>
      <div class="adm-panel">
        <div class="adm-panel-head">
          <h2>⚙ Ayarlar</h2>
        </div>
        <div class="adm-panel-body">
          <div class="row">
            <label class="checkbox" style="font-size:14px">
              <input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>>
              <strong>Aktif</strong> (sitede görünür)
            </label>
          </div>
          <div class="row">
            <label>Sıra</label>
            <input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>" min="0">
            <div class="cat-form-tip">Düşük sayı önce görünür (0 = ilk sırada)</div>
          </div>
          <div class="row">
            <label>İkon</label>
            <input type="text" name="icon" value="<?= h($row['icon'] ?? '') ?>" placeholder="Örn: 📦 veya emoji">
            <div class="cat-form-tip">Emoji veya HTML ikon</div>
          </div>
        </div>
      </div>

      <div class="adm-panel" style="margin-top:18px">
        <div class="adm-panel-head">
          <h2>🖼 Kategori Görseli</h2>
        </div>
        <div class="adm-panel-body">
          <?php if (!empty($row['image'])): ?>
            <div class="cat-img-preview">
              <img src="<?= h(img_url($row['image'])) ?>" alt="">
              <span class="cat-img-name"><?= h(basename($row['image'])) ?></span>
            </div>
          <?php endif; ?>

          <div class="row" style="margin-top:0">
            <label>Yeni Görsel Yükle</label>
            <input type="file" name="image" accept="image/*">
            <input type="hidden" name="existing_image" value="<?= h($row['image'] ?? '') ?>">
            <div class="cat-form-tip">Önerilen boyut: <code>1200×800px</code> · Format: JPG, PNG, WebP</div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="form-actions" style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
    <a href="<?= h(admin_url('categories.php')) ?>" class="adm-btn adm-btn-ghost">↩ Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 <?= $action === 'edit' ? 'Değişiklikleri Kaydet' : 'Kategori Oluştur' ?></button>
  </div>
</form>

<script>
// Char counter
function setupCounter(textareaName, counterId, maxChars) {
  const ta = document.querySelector(`[name="${textareaName}"]`);
  const counter = document.getElementById(counterId);
  if (!ta || !counter) return;
  function update() {
    const len = ta.value.length;
    counter.textContent = `${len} / ${maxChars}`;
    counter.classList.toggle('warn', len > maxChars * 0.9);
  }
  ta.addEventListener('input', update);
  update();
}
setupCounter('short_desc', 'shortDescCount', 300);
setupCounter('meta_desc', 'metaDescCount', 300);

// Slug preview canlı güncelleme
const nameInput = document.querySelector('[name="name"]');
const slugInput = document.querySelector('[name="slug"]');
const slugPreview = document.getElementById('slugPreview');

function slugify(s) {
  const trMap = {'ç':'c','Ç':'c','ğ':'g','Ğ':'g','ı':'i','İ':'i','ö':'o','Ö':'o','ş':'s','Ş':'s','ü':'u','Ü':'u'};
  return s.toLowerCase()
    .replace(/[çğıİöşüÇĞÖŞÜ]/g, c => trMap[c] || c)
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

if (nameInput && slugInput && slugPreview) {
  nameInput.addEventListener('input', () => {
    if (!slugInput.value || slugInput.dataset.auto !== 'false') {
      slugInput.value = slugify(nameInput.value);
      slugInput.dataset.auto = 'true';
    }
    slugPreview.textContent = slugInput.value || 'kategori';
  });
  slugInput.addEventListener('input', () => {
    slugInput.dataset.auto = 'false';
    slugPreview.textContent = slugInput.value || 'kategori';
  });
}
</script>

<?= i18n_tabs_js() ?>
<?php require __DIR__ . '/_footer.php'; exit; }

// ═══════════════════════════════════════════════════
// LİSTE GÖRÜNÜMÜ
// ═══════════════════════════════════════════════════
$rows = all("SELECT c.*,
                    (SELECT COUNT(*) FROM tm_products p WHERE p.category_id=c.id) AS product_count,
                    (SELECT name FROM tm_categories pc WHERE pc.id = c.parent_id) AS parent_name
             FROM tm_categories c
             ORDER BY c.parent_id IS NULL DESC, c.sort_order, c.name");

$activeCount = 0;
$totalProducts = 0;
foreach ($rows as $r) {
    if ($r['is_active']) $activeCount++;
    $totalProducts += (int)$r['product_count'];
}
?>

<style>
.cat-stats{
  display:grid;
  grid-template-columns:repeat(4, 1fr);
  gap:12px;
  margin-bottom:18px;
}
@media (max-width:700px){.cat-stats{grid-template-columns:repeat(2,1fr)}}
.cat-stat{
  background:#fff;
  border:1px solid #e3e8ef;
  border-left:4px solid #1e4a9e;
  padding:14px 18px;
}
.cat-stat .lbl{
  font-size:10px;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;
  color:#666;margin-bottom:4px;
}
.cat-stat .val{
  font-size:24px;font-weight:700;
  color:#0c1e44;
  font-family:'JetBrains Mono', monospace;
  letter-spacing:-.5px;
}
.cat-stat:nth-child(2){border-color:#10803a}
.cat-stat:nth-child(3){border-color:#c9a86b}
.cat-stat:nth-child(4){border-color:#c8102e}

.cat-table-row.has-parent td:nth-child(3){
  padding-left:32px;position:relative;
}
.cat-table-row.has-parent td:nth-child(3)::before{
  content:'└';
  position:absolute;left:14px;top:50%;
  transform:translateY(-50%);
  color:#999;font-size:14px;
}
.cat-parent-tag{
  display:inline-block;font-size:10px;
  background:#eff6ff;color:#1e4a9e;
  padding:2px 8px;border-radius:3px;margin-left:6px;
  font-weight:600;
}
.cat-product-count{
  display:inline-block;
  font-family:'JetBrains Mono', monospace;
  font-size:12px;font-weight:600;
  background:#f1f5f9;color:#0c1e44;
  padding:3px 10px;border-radius:3px;
}
.cat-product-count.zero{background:#fef2f2;color:#991b1b}
</style>

<!-- İSTATİSTİK ÖZET -->
<div class="cat-stats">
  <div class="cat-stat">
    <div class="lbl">Toplam Kategori</div>
    <div class="val"><?= count($rows) ?></div>
  </div>
  <div class="cat-stat">
    <div class="lbl">Aktif</div>
    <div class="val"><?= $activeCount ?></div>
  </div>
  <div class="cat-stat">
    <div class="lbl">Pasif</div>
    <div class="val"><?= count($rows) - $activeCount ?></div>
  </div>
  <div class="cat-stat">
    <div class="lbl">Toplam Ürün</div>
    <div class="val"><?= $totalProducts ?></div>
  </div>
</div>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>🗂 Tüm Kategoriler (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Kategori</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty">
        <div class="ico">🗂</div>
        <p>Henüz kategori eklenmedi.</p>
        <a href="?action=new" class="adm-btn adm-btn-primary" style="margin-top:14px">İlk Kategoriyi Ekle</a>
      </div>
    <?php else: ?>
    <table class="adm-table">
      <thead>
        <tr>
          <th style="width:50px">#</th>
          <th style="width:80px">Görsel</th>
          <th>Ad</th>
          <th>Slug</th>
          <th style="width:90px;text-align:center">Ürün</th>
          <th style="width:60px;text-align:center">Sıra</th>
          <th style="width:90px">Durum</th>
          <th style="width:280px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr class="cat-table-row <?= $r['parent_id'] ? 'has-parent' : '' ?>">
          <td><?= (int)$r['id'] ?></td>
          <td>
            <?php if (!empty($r['image'])): ?>
              <img src="<?= h(img_url($r['image'])) ?>" class="thumb" style="width:50px;height:50px;object-fit:cover;border-radius:4px">
            <?php else: ?>
              <span style="opacity:.4;font-size:24px">🗂</span>
            <?php endif; ?>
          </td>
          <td>
            <strong><?= h($r['name']) ?></strong>
            <?php if (!empty($r['parent_name'])): ?>
              <span class="cat-parent-tag">↑ <?= h($r['parent_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($r['icon'])): ?>
              <span style="opacity:.7;margin-left:6px"><?= h($r['icon']) ?></span>
            <?php endif; ?>
          </td>
          <td><code style="font-size:11.5px"><?= h($r['slug']) ?></code></td>
          <td style="text-align:center">
            <span class="cat-product-count <?= $r['product_count'] == 0 ? 'zero' : '' ?>">
              <?= (int)$r['product_count'] ?>
            </span>
          </td>
          <td style="text-align:center;font-family:monospace"><?= (int)$r['sort_order'] ?></td>
          <td>
            <span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>">
              <?= $r['is_active'] ? '✓ Aktif' : '✕ Pasif' ?>
            </span>
          </td>
          <td class="actions">
            <a href="<?= h(url('kategori.php?slug=' . urlencode($r['slug']))) ?>" target="_blank" class="adm-btn adm-btn-sm adm-btn-ghost" title="Sitede görüntüle">👁</a>
            <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost">
              <?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?>
            </a>
            <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
            <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger"
               data-confirm="<?= h($r['name']) ?> kategorisini silmek istediğinize emin misiniz? <?= $r['product_count'] > 0 ? "Bu kategoride " . $r['product_count'] . " ürün var, önce taşınmalı." : "" ?>">Sil</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
