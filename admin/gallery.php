<?php
define('TM_ADMIN', true);
$adminTitle = 'Galeri';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

/* ========== ALBUM CRUD ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $form = $_POST['_form'] ?? '';

    // Album save
    if ($form === 'album') {
        $editId = (int)($_POST['id'] ?? 0);
        $title  = trim($_POST['title'] ?? '');
        if ($title === '') adm_back_with('error', 'Başlık zorunlu.', 'admin/gallery.php');

        $existing = $editId ? row("SELECT cover_image FROM tm_gallery_albums WHERE id=?", [$editId]) : null;
        $cover = adm_handle_image_upload('cover_image', 'gallery', $existing['cover_image'] ?? null);

        $data = [
            'title'        => $title,
            'slug'         => slugify(trim($_POST['slug'] ?? '') ?: $title),
            'description'  => trim($_POST['description'] ?? ''),
            'cover_image'  => $cover,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ];
        // i18n çevirileri ekle (title_en/ar/ru, description_en/ar/ru)
        $data = i18n_post_merge($data, ['title', 'description']);
        $newId = adm_save('tm_gallery_albums', $data, $editId ?: null);
        log_activity($editId ? 'update' : 'create', 'gallery_album', $newId);
        adm_back_with('success', 'Albüm kaydedildi.', 'admin/gallery.php');
    }

    // Image upload (multi)
    if ($form === 'image_upload') {
        $albumId = (int)($_POST['album_id'] ?? 0);
        if (!$albumId) adm_back_with('error', 'Albüm seçilmedi.', 'admin/gallery.php');

        $cnt = 0;
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                $f = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                if ($f['error'] !== UPLOAD_ERR_OK) continue;
                $up = upload_image($f, 'gallery');
                if ($up) {
                    q("INSERT INTO tm_gallery_images (album_id, image_path, caption, sort_order) VALUES (?,?,?,?)",
                        [$albumId, $up['path'], '', 0]);
                    $cnt++;
                }
            }
        }
        log_activity('upload', 'gallery_images', $albumId, "$cnt resim yüklendi");
        adm_back_with($cnt ? 'success' : 'error',
            $cnt ? "$cnt resim yüklendi." : 'Hiç resim yüklenmedi.',
            'admin/gallery.php?action=view&id=' . $albumId);
    }

    // Image caption update
    if ($form === 'image_caption') {
        $imgId   = (int)($_POST['img_id'] ?? 0);
        $caption = trim($_POST['caption'] ?? '');
        $sort    = (int)($_POST['sort_order'] ?? 0);
        $albumId = (int)($_POST['album_id'] ?? 0);
        if ($imgId) {
            q("UPDATE tm_gallery_images SET caption=?, sort_order=? WHERE id=?", [$caption, $sort, $imgId]);
        }
        adm_back_with('success', 'Resim güncellendi.', 'admin/gallery.php?action=view&id=' . $albumId);
    }
}

if ($action === 'delete' && $id) {
    // Delete cover and images? Keep files; just delete records
    q("DELETE FROM tm_gallery_images WHERE album_id=?", [$id]);
    adm_delete('tm_gallery_albums', $id);
    log_activity('delete', 'gallery_album', $id);
    adm_back_with('success', 'Albüm silindi.', 'admin/gallery.php');
}
if ($action === 'toggle' && $id) {
    adm_toggle('tm_gallery_albums', $id, 'is_active');
    adm_back_with('success', 'Durum güncellendi.', 'admin/gallery.php');
}
if ($action === 'delete_image' && $id) {
    $img = row("SELECT album_id FROM tm_gallery_images WHERE id=?", [$id]);
    q("DELETE FROM tm_gallery_images WHERE id=?", [$id]);
    adm_back_with('success', 'Resim silindi.', 'admin/gallery.php?action=view&id=' . (int)($img['album_id'] ?? 0));
}

/* ========== ALBUM EDIT/NEW FORM ========== */
if (in_array($action, ['edit', 'new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_gallery_albums WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Albüm bulunamadı.', 'admin/gallery.php');
    ?>
    <form method="post" enctype="multipart/form-data" class="adm-form">
        <?= csrf_field() ?>
        <input type="hidden" name="_form" value="album">
        <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

        <div class="adm-panel">
            <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Albüm Düzenle' : 'Yeni Albüm' ?></h2></div>
            <div class="adm-panel-body">
                <div class="row-2">
                    <div class="row"><label>Başlık *</label><input type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required></div>
                    <div class="row"><label>Slug (boş bırak otomatik)</label><input type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>"></div>
                </div>
                <?= i18n_inputs($row, 'title') ?>
                <div class="row"><label>Açıklama</label><textarea name="description" rows="3"><?= h($row['description'] ?? '') ?></textarea></div>
                <?= i18n_inputs($row, 'description', true, 3) ?>
                <div class="row-2">
                    <div class="row">
                        <label>Kapak Görseli</label>
                        <input type="file" name="cover_image" accept="image/*">
                        <?php if (!empty($row['cover_image'])): ?>
                            <div style="margin-top:8px"><img src="<?= h(url($row['cover_image'])) ?>" style="max-width:160px;border-radius:6px"></div>
                        <?php endif; ?>
                    </div>
                    <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
                </div>
                <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= h(admin_url('gallery.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
            <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
        </div>
    </form>
    <?= i18n_tabs_js() ?>
    <?php require __DIR__ . '/_footer.php'; exit;
}

/* ========== ALBUM DETAIL (IMAGE MANAGEMENT) ========== */
if ($action === 'view' && $id) {
    $album = row("SELECT * FROM tm_gallery_albums WHERE id=?", [$id]);
    if (!$album) adm_back_with('error', 'Albüm bulunamadı.', 'admin/gallery.php');
    $images = all("SELECT * FROM tm_gallery_images WHERE album_id=? ORDER BY sort_order, id", [$id]);
    ?>
    <div class="adm-panel">
        <div class="adm-panel-head">
            <h2><?= h($album['title']) ?> — Resimler (<?= count($images) ?>)</h2>
            <a href="<?= h(admin_url('gallery.php')) ?>" class="adm-btn adm-btn-ghost">← Albüm Listesi</a>
        </div>
        <div class="adm-panel-body">
            <form method="post" enctype="multipart/form-data" style="margin-bottom:16px">
                <?= csrf_field() ?>
                <input type="hidden" name="_form" value="image_upload">
                <input type="hidden" name="album_id" value="<?= (int)$album['id'] ?>">
                <div class="row" style="display:flex;gap:8px;align-items:flex-end">
                    <div style="flex:1">
                        <label>Resim Yükle (çoklu seçim)</label>
                        <input type="file" name="images[]" multiple accept="image/*" required>
                    </div>
                    <button type="submit" class="adm-btn adm-btn-primary">⬆ Yükle</button>
                </div>
            </form>

            <?php if (!$images): ?>
                <div class="adm-empty"><div class="ico">🖼</div>Henüz resim yüklenmedi.</div>
            <?php else: ?>
                <div class="adm-grid-thumbs">
                    <?php foreach ($images as $im): ?>
                        <div class="adm-thumb">
                            <a href="<?= h(url($im['image_path'])) ?>" target="_blank">
                                <img src="<?= h(url($im['image_path'])) ?>" alt="">
                            </a>
                            <form method="post" class="adm-thumb-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_form" value="image_caption">
                                <input type="hidden" name="img_id" value="<?= (int)$im['id'] ?>">
                                <input type="hidden" name="album_id" value="<?= (int)$album['id'] ?>">
                                <input type="text" name="caption" value="<?= h($im['caption']) ?>" placeholder="Açıklama">
                                <div style="display:flex;gap:6px;align-items:center">
                                    <input type="number" name="sort_order" value="<?= (int)$im['sort_order'] ?>" style="width:70px" title="Sıra">
                                    <button type="submit" class="adm-btn adm-btn-sm adm-btn-primary">✓</button>
                                    <a href="?action=delete_image&id=<?= (int)$im['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Resmi sil?">×</a>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php require __DIR__ . '/_footer.php'; exit;
}

/* ========== ALBUM LIST ========== */
$rows = all("
    SELECT a.*, (SELECT COUNT(*) FROM tm_gallery_images WHERE album_id=a.id) AS img_count
    FROM tm_gallery_albums a
    ORDER BY a.sort_order, a.id
");
?>

<div class="adm-panel">
    <div class="adm-panel-head">
        <h2>Galeri Albümleri (<?= count($rows) ?>)</h2>
        <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Albüm</a>
    </div>
    <div class="adm-panel-body" style="padding:0">
        <?php if (!$rows): ?>
            <div class="adm-empty"><div class="ico">🖼</div>Henüz albüm yok.</div>
        <?php else: ?>
            <table class="adm-table">
                <thead>
                    <tr><th style="width:80px">Kapak</th><th>Albüm</th><th>Slug</th><th>Resim</th><th>Sıra</th><th>Durum</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td>
                                <?php if (!empty($r['cover_image'])): ?>
                                    <img src="<?= h(url($r['cover_image'])) ?>" alt="" style="width:60px;height:42px;object-fit:cover;border-radius:4px">
                                <?php else: ?>
                                    <span class="adm-no-img">—</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= h($r['title']) ?></strong><?php if ($r['description']): ?><br><small style="color:#aaa"><?= h(excerpt($r['description'], 60)) ?></small><?php endif; ?></td>
                            <td><code><?= h($r['slug']) ?></code></td>
                            <td><span class="badge"><?= (int)$r['img_count'] ?></span></td>
                            <td><?= (int)$r['sort_order'] ?></td>
                            <td><span class="badge badge-<?= $r['is_active'] ? 'on' : 'off' ?>"><?= $r['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                            <td class="actions">
                                <a href="?action=view&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost">🖼 Resimler</a>
                                <a href="?action=toggle&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-ghost"><?= $r['is_active'] ? 'Gizle' : 'Yayınla' ?></a>
                                <a href="?action=edit&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Düzenle</a>
                                <a href="?action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Albüm ve tüm resimleri silinecek. Emin misiniz?">Sil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
