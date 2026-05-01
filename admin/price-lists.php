<?php
define('TM_ADMIN', true);
$adminTitle = 'Fiyat Listeleri Rehberi';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action  = $_GET['action'] ?? 'list';
$id      = (int)($_GET['id'] ?? 0);

/* ========== POST: SAVE ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $form = $_POST['_form'] ?? '';

    // SAVE (create/update)
    if ($form === 'save') {
        $editId = (int)($_POST['id'] ?? 0);
        $brand_name = trim($_POST['brand_name'] ?? '');
        $list_url   = trim($_POST['list_url'] ?? '');
        if ($brand_name === '') adm_back_with('error', 'Firma adı zorunlu.', 'admin/price-lists.php');
        if ($list_url === '')   adm_back_with('error', 'URL zorunlu.', 'admin/price-lists.php');

        $slug = slugify(trim($_POST['brand_slug'] ?? '') ?: $brand_name);

        // Logo upload (varsa)
        $existing = $editId ? row("SELECT brand_logo FROM tm_price_lists WHERE id=?", [$editId]) : null;
        $brand_logo = $existing['brand_logo'] ?? null;
        if (!empty($_FILES['brand_logo_file']['name'])) {
            $up = upload_image($_FILES['brand_logo_file'], 'uploads/price-lists');
            if ($up) $brand_logo = $up['path'];
        }

        $data = [
            'brand_name'   => $brand_name,
            'brand_slug'   => $slug,
            'brand_logo'   => $brand_logo,
            'category'     => $_POST['category'] ?? 'celik',
            'city'         => trim($_POST['city'] ?? '') ?: null,
            'region'       => trim($_POST['region'] ?? '') ?: null,
            'description'  => trim($_POST['description'] ?? '') ?: null,
            'list_url'     => $list_url,
            'list_type'    => $_POST['list_type'] ?? 'web',
            'last_updated' => $_POST['last_updated'] ?: null,
            'is_featured'  => !empty($_POST['is_featured']) ? 1 : 0,
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
            'is_active'    => !empty($_POST['is_active']) ? 1 : 0,
        ];

        $newId = adm_save('tm_price_lists', $data, $editId ?: null);
        log_activity($editId ? 'update' : 'create', 'price_lists', $newId, $brand_name);
        adm_back_with('success', $editId ? 'Güncellendi: ' . $brand_name : 'Eklendi: ' . $brand_name, 'admin/price-lists.php');
    }

    // DELETE
    if ($form === 'delete') {
        $delId = (int)($_POST['id'] ?? 0);
        if ($delId) {
            $row = row("SELECT brand_name FROM tm_price_lists WHERE id=?", [$delId]);
            adm_delete('tm_price_lists', $delId);
            log_activity('delete', 'price_lists', $delId, $row['brand_name'] ?? '');
            adm_back_with('success', 'Silindi: ' . ($row['brand_name'] ?? ''), 'admin/price-lists.php');
        }
    }

    // TOGGLE active
    if ($form === 'toggle_active') {
        $tid = (int)($_POST['id'] ?? 0);
        if ($tid) {
            adm_toggle('tm_price_lists', $tid, 'is_active');
            adm_back_with('success', 'Durum değişti.', 'admin/price-lists.php');
        }
    }
}

/* ========== EDIT / NEW FORM ========== */
if ($action === 'edit' || $action === 'new') {
    $row = $id ? row("SELECT * FROM tm_price_lists WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/price-lists.php');

    $categories = [
        'celik'     => '🔥 Çelik',
        'sac'       => '📐 Sac',
        'boru'      => '⭕ Boru',
        'profil'    => '▭ Profil',
        'paslanmaz' => '✨ Paslanmaz',
    ];
    $types = [
        'web'     => '🌐 Web (resmi site)',
        'pdf'     => '📄 PDF (indirilebilir)',
        'login'   => '🔐 Bayi Portal (login)',
        'request' => '📩 Talep formu',
    ];
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="_form" value="save">
    <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

    <div class="adm-panel">
        <div class="adm-panel-head">
            <h2><?= $action === 'edit' ? '✏ Düzenle: ' . h($row['brand_name']) : '➕ Yeni Fabrika Ekle' ?></h2>
        </div>
        <div class="adm-panel-body">
            <div class="row-2">
                <div class="row">
                    <label>Firma Adı *</label>
                    <input type="text" name="brand_name" value="<?= h($row['brand_name'] ?? '') ?>" required placeholder="Örn: Erdemir">
                </div>
                <div class="row">
                    <label>URL Slug</label>
                    <input type="text" name="brand_slug" value="<?= h($row['brand_slug'] ?? '') ?>" placeholder="otomatik üretilir">
                </div>
            </div>

            <div class="row-3">
                <div class="row">
                    <label>Kategori *</label>
                    <select name="category" required>
                        <?php foreach ($categories as $k => $v): ?>
                            <option value="<?= h($k) ?>" <?= ($row['category'] ?? 'celik') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <label>Şehir</label>
                    <input type="text" name="city" value="<?= h($row['city'] ?? '') ?>" placeholder="Örn: Ereğli">
                </div>
                <div class="row">
                    <label>Bölge</label>
                    <input type="text" name="region" value="<?= h($row['region'] ?? '') ?>" placeholder="Örn: Karadeniz">
                </div>
            </div>

            <div class="row">
                <label>Açıklama</label>
                <textarea name="description" rows="3" placeholder="Firma hakkında kısa bilgi"><?= h($row['description'] ?? '') ?></textarea>
            </div>

            <div class="row-2">
                <div class="row">
                    <label>Fiyat Listesi URL *</label>
                    <input type="url" name="list_url" value="<?= h($row['list_url'] ?? '') ?>" required placeholder="https://...">
                </div>
                <div class="row">
                    <label>Liste Tipi</label>
                    <select name="list_type">
                        <?php foreach ($types as $k => $v): ?>
                            <option value="<?= h($k) ?>" <?= ($row['list_type'] ?? 'web') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row-3">
                <div class="row">
                    <label>Son Güncelleme</label>
                    <input type="date" name="last_updated" value="<?= h($row['last_updated'] ?? '') ?>">
                </div>
                <div class="row">
                    <label>Sıralama (küçük = önde)</label>
                    <input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 99) ?>">
                </div>
                <div class="row">
                    <label>Logo (opsiyonel)</label>
                    <input type="file" name="brand_logo_file" accept="image/*">
                    <?php if (!empty($row['brand_logo'])): ?>
                        <small>Mevcut: <?= h($row['brand_logo']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row-2">
                <div class="row">
                    <label class="checkbox">
                        <input type="checkbox" name="is_featured" value="1" <?= !empty($row['is_featured']) ? 'checked' : '' ?>>
                        ⭐ Öne Çıkar (Featured — listenin başında)
                    </label>
                </div>
                <div class="row">
                    <label class="checkbox">
                        <input type="checkbox" name="is_active" value="1" <?= !isset($row['is_active']) || !empty($row['is_active']) ? 'checked' : '' ?>>
                        ✓ Aktif (Sayfada görünür)
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?= h(admin_url('price-lists.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
        <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
    </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

/* ========== LIST ========== */
$rows = all("SELECT * FROM tm_price_lists ORDER BY is_featured DESC, sort_order, brand_name");

$cats = [
    'celik'     => ['label' => '🔥 Çelik',     'color' => '#c8102e'],
    'sac'       => ['label' => '📐 Sac',       'color' => '#0c1e44'],
    'boru'      => ['label' => '⭕ Boru',       'color' => '#143672'],
    'profil'    => ['label' => '▭ Profil',     'color' => '#a88a4a'],
    'paslanmaz' => ['label' => '✨ Paslanmaz', 'color' => '#6b7280'],
];

$catCounts = [];
foreach ($rows as $r) {
    $catCounts[$r['category']] = ($catCounts[$r['category']] ?? 0) + 1;
}
?>
<style>
.pl-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
.pl-stats { display:flex; gap:16px; flex-wrap:wrap; font-size:13px; color:#6b7280; }
.pl-stats strong { color:#0c1e44; font-size:18px; font-family:'Cormorant Garamond', Georgia, serif; }
.pl-table { width:100%; background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
.pl-table th { background:#f8fafc; padding:10px 12px; text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:0.3px; color:#374151; border-bottom:1px solid #e5e7eb; }
.pl-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; font-size:13px; vertical-align:middle; }
.pl-table tr:last-child td { border-bottom:none; }
.pl-table tr:hover { background:#fafbfc; }
.pl-name { font-weight:600; color:#0c1e44; }
.pl-name small { display:block; color:#9ca3af; font-weight:400; font-size:11px; font-family:'JetBrains Mono', monospace; margin-top:2px; }
.pl-cat-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; background:#f3f4f6; }
.pl-type { font-size:11px; padding:2px 6px; border-radius:4px; background:#eff6ff; color:#1e40af; font-family:'JetBrains Mono', monospace; }
.pl-actions { display:flex; gap:6px; justify-content:flex-end; }
.pl-actions form { display:inline; }
.pl-actions button, .pl-actions a { padding:5px 10px; font-size:12px; border-radius:6px; border:1px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer; text-decoration:none; }
.pl-actions a:hover { background:#f3f4f6; }
.pl-actions .btn-edit:hover { color:#0c1e44; border-color:#0c1e44; }
.pl-actions .btn-delete { color:#dc2626; border-color:#fecaca; }
.pl-actions .btn-delete:hover { background:#fee2e2; }
.pl-featured { color:#c9a86b; font-size:14px; }
.pl-inactive { opacity:0.45; }
.pl-url { font-family:'JetBrains Mono', monospace; font-size:11px; color:#6b7280; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle; }
.pl-url a { color:#143672; text-decoration:none; }
.pl-url a:hover { text-decoration:underline; }
</style>

<div class="pl-toolbar">
    <div class="pl-stats">
        <span><strong><?= count($rows) ?></strong> kayıt</span>
        <?php foreach ($cats as $key => $cat): ?>
            <?php if (!empty($catCounts[$key])): ?>
                <span style="color:<?= $cat['color'] ?>"><?= $cat['label'] ?> <strong><?= $catCounts[$key] ?></strong></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div>
        <a href="<?= h(url('fiyat-listeleri.php')) ?>" target="_blank" class="adm-btn adm-btn-ghost">👁 Frontend</a>
        <a href="<?= h(admin_url('price-lists.php?action=new')) ?>" class="adm-btn adm-btn-primary">➕ Yeni Fabrika</a>
    </div>
</div>

<table class="pl-table">
    <thead>
        <tr>
            <th style="width:50px">#</th>
            <th>Firma</th>
            <th>Kategori</th>
            <th>Şehir</th>
            <th>URL</th>
            <th>Tip</th>
            <th>Tarih</th>
            <th style="width:160px;text-align:right">İşlem</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $i => $r):
            $cat = $cats[$r['category']] ?? ['label' => $r['category'], 'color' => '#6b7280'];
            $typeIcons = ['pdf' => '📄', 'login' => '🔐', 'request' => '📩', 'web' => '🌐'];
        ?>
            <tr class="<?= empty($r['is_active']) ? 'pl-inactive' : '' ?>">
                <td>
                    <?= $i + 1 ?>
                    <?php if (!empty($r['is_featured'])): ?>
                        <span class="pl-featured" title="Öne çıkarıldı">⭐</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="pl-name"><?= h($r['brand_name']) ?>
                        <small><?= h($r['brand_slug']) ?></small>
                    </span>
                </td>
                <td>
                    <span class="pl-cat-badge" style="color:<?= $cat['color'] ?>"><?= h($cat['label']) ?></span>
                </td>
                <td><?= h($r['city'] ?? '—') ?></td>
                <td>
                    <span class="pl-url"><a href="<?= h($r['list_url']) ?>" target="_blank" rel="noopener"><?= h($r['list_url']) ?></a></span>
                </td>
                <td><span class="pl-type"><?= $typeIcons[$r['list_type']] ?? '?' ?> <?= h($r['list_type']) ?></span></td>
                <td><?= !empty($r['last_updated']) ? date('d.m.Y', strtotime($r['last_updated'])) : '<span style="color:#9ca3af">—</span>' ?></td>
                <td>
                    <div class="pl-actions">
                        <form method="post" onsubmit="return confirm('Aktif/pasif değiştir?')">
                            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="_form" value="toggle_active">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" title="<?= empty($r['is_active']) ? 'Aktif et' : 'Pasif et' ?>"><?= empty($r['is_active']) ? '⭕' : '✓' ?></button>
                        </form>
                        <a href="<?= h(admin_url('price-lists.php?action=edit&id=' . (int)$r['id'])) ?>" class="btn-edit" title="Düzenle">✏</a>
                        <form method="post" onsubmit="return confirm('<?= h($r['brand_name']) ?> silinsin mi?')">
                            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="_form" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn-delete" title="Sil">🗑</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#9ca3af">
                Henüz kayıt yok. <a href="<?= h(admin_url('price-lists.php?action=new')) ?>">İlk fabrikayı ekle →</a>
            </td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/_footer.php'; ?>
