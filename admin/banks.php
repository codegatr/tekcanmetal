<?php
define('TM_ADMIN', true);
$adminTitle = 'Bankalar / IBAN';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $editId = (int)($_POST['id'] ?? 0);
    $bank = trim($_POST['bank_name'] ?? '');
    $iban = trim(strtoupper(str_replace(' ', '', $_POST['iban'] ?? '')));
    if ($bank === '' || $iban === '') adm_back_with('error', 'Banka adı ve IBAN zorunlu.', 'admin/banks.php');

    // IBAN'ı 4'lü gruplar halinde formatla
    $iban_fmt = trim(chunk_split($iban, 4, ' '));

    $data = [
        'bank_name'      => $bank,
        'branch'         => trim($_POST['branch'] ?? ''),
        'account_holder' => trim($_POST['account_holder'] ?? '') ?: 'TEKCAN METAL SAN. VE TİC. LTD. ŞTİ.',
        'iban'           => $iban_fmt,
        'account_no'     => trim($_POST['account_no'] ?? ''),
        'currency'       => trim($_POST['currency'] ?? 'TRY'),
        'sort_order'     => (int)($_POST['sort_order'] ?? 0),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];
    $data['logo'] = adm_handle_image_upload('logo', 'uploads/banks', $_POST['existing_logo'] ?? null);
    $newId = adm_save('tm_banks', $data, $editId ?: null);
    log_activity($editId ? 'update' : 'create', 'bank', $newId, "Banka: $bank");
    adm_back_with('success', 'Banka kaydedildi.', 'admin/banks.php');
}

if ($action === 'delete' && $id) { adm_delete('tm_banks', $id); adm_back_with('success', 'Silindi.', 'admin/banks.php'); }
if ($action === 'toggle' && $id) { adm_toggle('tm_banks', $id, 'is_active'); adm_back_with('success', 'Durum güncellendi.', 'admin/banks.php'); }

if (in_array($action, ['edit','new'], true)) {
    $row = $action === 'edit' ? row("SELECT * FROM tm_banks WHERE id=?", [$id]) : [];
    if ($action === 'edit' && !$row) adm_back_with('error', 'Bulunamadı.', 'admin/banks.php');
?>
<form method="post" enctype="multipart/form-data" class="adm-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)($row['id'] ?? 0) ?>">

  <div class="adm-panel">
    <div class="adm-panel-head"><h2><?= $action === 'edit' ? 'Banka Düzenle' : 'Yeni Banka' ?></h2></div>
    <div class="adm-panel-body">
      <div class="row-2">
        <div class="row"><label>Banka Adı *</label><input type="text" name="bank_name" value="<?= h($row['bank_name'] ?? '') ?>" required></div>
        <div class="row"><label>Şube</label><input type="text" name="branch" value="<?= h($row['branch'] ?? '') ?>"></div>
      </div>
      <div class="row"><label>Hesap Sahibi</label><input type="text" name="account_holder" value="<?= h($row['account_holder'] ?? 'TEKCAN METAL SAN. VE TİC. LTD. ŞTİ.') ?>"></div>
      <div class="row"><label>IBAN *</label><input type="text" name="iban" value="<?= h($row['iban'] ?? '') ?>" required placeholder="TR12 0000 0000 0000 0000 0000 00"></div>
      <div class="row-2">
        <div class="row"><label>Hesap No</label><input type="text" name="account_no" value="<?= h($row['account_no'] ?? '') ?>"></div>
        <div class="row">
          <label>Para Birimi</label>
          <select name="currency">
            <?php foreach (['TRY' => '₺ Türk Lirası','USD' => '$ Dolar','EUR' => '€ Euro'] as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($row['currency'] ?? 'TRY') === $k ? 'selected' : '' ?>><?= h($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row"><label>Sıra</label><input type="number" name="sort_order" value="<?= (int)($row['sort_order'] ?? 0) ?>" style="max-width:120px"></div>
      <div class="row">
        <label>Banka Logosu</label>
        <?php if (!empty($row['logo'])): ?>
          <div style="margin-bottom:8px;background:#fff;padding:8px;border-radius:6px;display:inline-block"><img src="<?= h(img_url($row['logo'])) ?>" style="max-height:60px"></div>
        <?php endif; ?>
        <input type="file" name="logo" accept="image/*">
        <input type="hidden" name="existing_logo" value="<?= h($row['logo'] ?? '') ?>">
      </div>
      <div class="row"><label class="checkbox"><input type="checkbox" name="is_active" <?= ($row['is_active'] ?? 1) ? 'checked' : '' ?>> Aktif</label></div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= h(admin_url('banks.php')) ?>" class="adm-btn adm-btn-ghost">Vazgeç</a>
    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
  </div>
</form>
<?php require __DIR__ . '/_footer.php'; exit; }

$rows = all("SELECT * FROM tm_banks ORDER BY sort_order, id");
?>

<div class="adm-panel">
  <div class="adm-panel-head">
    <h2>Banka Hesapları (<?= count($rows) ?>)</h2>
    <a href="?action=new" class="adm-btn adm-btn-primary">+ Yeni Banka</a>
  </div>
  <div class="adm-panel-body" style="padding:0">
    <?php if (!$rows): ?>
      <div class="adm-empty"><div class="ico">🏦</div>Banka hesabı eklenmedi.</div>
    <?php else: ?>
    <table class="adm-table">
      <thead><tr><th>#</th><th>Logo</th><th>Banka</th><th>Şube</th><th>IBAN</th><th>Para</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?php if (!empty($r['logo'])): ?><img src="<?= h(img_url($r['logo'])) ?>" class="thumb" style="background:#fff;padding:3px"><?php else: ?><span style="opacity:.4">—</span><?php endif; ?></td>
          <td><strong><?= h($r['bank_name']) ?></strong></td>
          <td><?= h($r['branch'] ?: '—') ?></td>
          <td><code style="font-size:11px"><?= h($r['iban']) ?></code></td>
          <td><?= h($r['currency']) ?></td>
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
