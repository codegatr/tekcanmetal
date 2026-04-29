<?php
define('TM_ADMIN', true);
$adminTitle = 'SSS';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $qq = trim($_POST['question'] ?? '');
    $aa = trim($_POST['answer'] ?? '');
    if ($qq === '' || $aa === '') adm_back_with('error', 'Soru ve cevap zorunlu.', 'admin/faq.php');

    $data = [
        'category'   => trim($_POST['category'] ?? 'genel') ?: 'genel',
        'question'   => $qq,
        'answer'     => $aa,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
    ];
    $newId = adm_save('tm_faq', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'faq', $newId);
    adm_back_with('success', 'Soru kaydedildi.', 'admin/faq.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_faq', $id); adm_back_with('success', 'Silindi.', 'admin/faq.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_faq', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/faq.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_faq WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/faq.php');
?>
<form method="post" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Soru Düzenle' : 'Yeni Soru' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Kategori</label><input type="text" name="category" value="<?= h($row['category'] ?? 'genel') ?>" placeholder="genel, teslimat, ödeme..."></div>
        <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>"></div>
      </div>
      <div class="row"><label>Soru *</label><input type="text" name="question" value="<?= h($row['question'] ?? '') ?>" required></div>
      <div class="row"><label>Cevap *</label><textarea name="answer" rows="6" required><?= h($row['answer'] ?? '') ?></textarea></div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('faq.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_faq ORDER BY category, sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Sıkça Sorulan Sorular (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Soru</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">❓</div>Soru eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Kategori</th><th>Soru</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><span class="badge"><?= h($r['category']) ?></span></td>
          <td><strong><?= h($r['question']) ?></strong></td>
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
