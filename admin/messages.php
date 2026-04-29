<?php
define('TM_ADMIN', true);
$adminTitle = 'Mesajlar';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$tab    = $_GET['tab']    ?? 'contact';
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if (!in_array($tab, ['contact', 'mail', 'loyalty'], true)) $tab = 'contact';

$tabConfig = [
    'contact' => [
        'table' => 'tm_contact_messages',
        'label' => 'İletişim Mesajları',
        'icon'  => '📨',
        'flag'  => 'is_read',
    ],
    'mail' => [
        'table' => 'tm_mail_orders',
        'label' => 'Mail Order Talepleri',
        'icon'  => '💳',
        'flag'  => null,
    ],
    'loyalty' => [
        'table' => 'tm_loyalty_members',
        'label' => 'Sadakat Üyeleri',
        'icon'  => '⭐',
        'flag'  => 'is_approved',
    ],
];

$cfg   = $tabConfig[$tab];
$table = $cfg['table'];

/* ========== POST ACTIONS ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $rid = (int)($_POST['id'] ?? 0);
    if (!$rid) adm_back_with('error', 'Geçersiz kayıt.', "admin/messages.php?tab=$tab");

    $note = trim($_POST['admin_note'] ?? '');

    if ($tab === 'contact') {
        $isRead    = isset($_POST['is_read'])    ? 1 : 0;
        $isReplied = isset($_POST['is_replied']) ? 1 : 0;
        q("UPDATE $table SET admin_note=?, is_read=?, is_replied=? WHERE id=?",
            [$note, $isRead, $isReplied, $rid]);
    } elseif ($tab === 'mail') {
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending','approved','rejected','completed'], true)) $status = 'pending';
        q("UPDATE $table SET admin_note=?, status=? WHERE id=?", [$note, $status, $rid]);
    } else { // loyalty
        $isApproved = isset($_POST['is_approved']) ? 1 : 0;
        q("UPDATE $table SET is_approved=? WHERE id=?", [$isApproved, $rid]);
    }
    log_activity('update', $tab . '_message', $rid);
    adm_back_with('success', 'Kayıt güncellendi.', "admin/messages.php?tab=$tab&action=view&id=$rid");
}

if ($action === 'delete' && $id) {
    adm_delete($table, $id);
    log_activity('delete', $tab . '_message', $id);
    adm_back_with('success', 'Kayıt silindi.', "admin/messages.php?tab=$tab");
}

if ($action === 'mark_read' && $id && $tab === 'contact') {
    q("UPDATE $table SET is_read=1 WHERE id=?", [$id]);
    adm_back_with('success', 'Okundu işaretlendi.', "admin/messages.php?tab=$tab");
}

/* ========== DETAIL VIEW ========== */
if ($action === 'view' && $id) {
    $row = row("SELECT * FROM $table WHERE id=?", [$id]);
    if (!$row) adm_back_with('error', 'Kayıt bulunamadı.', "admin/messages.php?tab=$tab");

    // Auto-mark as read on view
    if ($tab === 'contact' && empty($row['is_read'])) {
        q("UPDATE $table SET is_read=1 WHERE id=?", [$id]);
        $row['is_read'] = 1;
    }
    ?>
    <div class="adm-tabs">
        <?php foreach ($tabConfig as $tk => $tc): ?>
            <a href="?tab=<?= $tk ?>" class="<?= $tk === $tab ? 'active' : '' ?>"><?= $tc['icon'] ?> <?= h($tc['label']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="adm-panel">
        <div class="adm-panel-head">
            <h2><?= $cfg['icon'] ?> <?= h($cfg['label']) ?> #<?= (int)$row['id'] ?></h2>
            <a href="<?= h(admin_url('messages.php?tab=' . $tab)) ?>" class="adm-btn adm-btn-ghost">← Listeye Dön</a>
        </div>
        <div class="adm-panel-body">
            <div class="adm-detail-grid">
                <?php if ($tab === 'contact'): ?>
                    <div><label>Ad Soyad</label><div><?= h($row['full_name']) ?></div></div>
                    <div><label>E-posta</label><div><a href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></div></div>
                    <div><label>Telefon</label><div><?= $row['phone'] ? '<a href="tel:' . h($row['phone']) . '">' . h($row['phone']) . '</a>' : '—' ?></div></div>
                    <div><label>Firma</label><div><?= h($row['company'] ?: '—') ?></div></div>
                    <div><label>Konu</label><div><?= h($row['subject'] ?: '—') ?></div></div>
                    <div><label>Kaynak</label><div><?= h($row['source'] ?: 'iletisim') ?></div></div>
                    <div><label>IP</label><div><code><?= h($row['ip_address'] ?: '—') ?></code></div></div>
                    <div><label>Tarih</label><div><?= h(tr_date($row['created_at'])) ?></div></div>
                    <div class="full"><label>Mesaj</label><div class="adm-message-body"><?= nl2br(h($row['message'])) ?></div></div>

                <?php elseif ($tab === 'mail'): ?>
                    <div><label>Ad Soyad</label><div><?= h($row['full_name']) ?></div></div>
                    <div><label>Firma</label><div><?= h($row['company'] ?: '—') ?></div></div>
                    <div><label>E-posta</label><div><a href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></div></div>
                    <div><label>Telefon</label><div><a href="tel:<?= h($row['phone']) ?>"><?= h($row['phone']) ?></a></div></div>
                    <div><label>Kart Sahibi</label><div><?= h($row['card_holder']) ?></div></div>
                    <div><label>Kart Son 4 Hane</label><div><code>**** **** **** <?= h($row['card_last4'] ?: '----') ?></code></div></div>
                    <div><label>Tutar</label><div><strong><?= number_format((float)$row['amount'], 2, ',', '.') ?> ₺</strong></div></div>
                    <div><label>IP</label><div><code><?= h($row['ip_address'] ?: '—') ?></code></div></div>
                    <div><label>Tarih</label><div><?= h(tr_date($row['created_at'])) ?></div></div>
                    <div class="full"><label>Açıklama</label><div class="adm-message-body"><?= nl2br(h($row['description'] ?: '—')) ?></div></div>

                <?php else: // loyalty ?>
                    <div><label>Ad Soyad</label><div><?= h($row['full_name']) ?></div></div>
                    <div><label>Firma</label><div><?= h($row['company'] ?: '—') ?></div></div>
                    <div><label>E-posta</label><div><a href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a></div></div>
                    <div><label>Telefon</label><div><a href="tel:<?= h($row['phone']) ?>"><?= h($row['phone']) ?></a></div></div>
                    <div><label>Şehir</label><div><?= h($row['city'] ?: '—') ?></div></div>
                    <div><label>IP</label><div><code><?= h($row['ip_address'] ?: '—') ?></code></div></div>
                    <div><label>Tarih</label><div><?= h(tr_date($row['created_at'])) ?></div></div>
                    <div class="full"><label>İlgilendiği Ürünler</label><div class="adm-message-body"><?= nl2br(h($row['preferred_products'] ?: '—')) ?></div></div>
                <?php endif; ?>
            </div>

            <hr style="margin:20px 0;border:0;border-top:1px solid #2a3552">

            <form method="post" class="adm-form" style="margin:0">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

                <?php if ($tab === 'contact'): ?>
                    <div class="row-2">
                        <div class="row"><label class="checkbox"><input type="checkbox" name="is_read"    <?= $row['is_read']    ? 'checked' : '' ?>> Okundu</label></div>
                        <div class="row"><label class="checkbox"><input type="checkbox" name="is_replied" <?= $row['is_replied'] ? 'checked' : '' ?>> Cevaplandı</label></div>
                    </div>
                    <div class="row"><label>Yönetici Notu</label><textarea name="admin_note" rows="4"><?= h($row['admin_note'] ?? '') ?></textarea></div>
                <?php elseif ($tab === 'mail'): ?>
                    <div class="row">
                        <label>Durum</label>
                        <select name="status">
                            <?php foreach (['pending'=>'Beklemede','approved'=>'Onaylandı','rejected'=>'Reddedildi','completed'=>'Tamamlandı'] as $sk => $sv): ?>
                                <option value="<?= $sk ?>" <?= $row['status'] === $sk ? 'selected' : '' ?>><?= $sv ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row"><label>Yönetici Notu</label><textarea name="admin_note" rows="4"><?= h($row['admin_note'] ?? '') ?></textarea></div>
                <?php else: ?>
                    <div class="row"><label class="checkbox"><input type="checkbox" name="is_approved" <?= $row['is_approved'] ? 'checked' : '' ?>> Onaylandı</label></div>
                <?php endif; ?>

                <div class="form-actions">
                    <a href="?tab=<?= $tab ?>&action=delete&id=<?= (int)$row['id'] ?>" class="adm-btn adm-btn-danger" data-confirm="Kaydı kalıcı olarak sil?">🗑 Sil</a>
                    <button type="submit" class="adm-btn adm-btn-primary">💾 Kaydet</button>
                </div>
            </form>
        </div>
    </div>
    <?php require __DIR__ . '/_footer.php'; exit;
}

/* ========== LIST VIEW ========== */
$f = $_GET['f'] ?? '';
$where  = "WHERE 1=1";
$params = [];

if ($tab === 'contact' && $f === 'unread') { $where .= " AND is_read=0"; }
if ($tab === 'mail'    && in_array($f, ['pending','approved','rejected','completed'], true)) { $where .= " AND status=?"; $params[] = $f; }
if ($tab === 'loyalty' && $f === 'pending') { $where .= " AND is_approved=0"; }

$rows = all("SELECT * FROM $table $where ORDER BY id DESC LIMIT 200", $params);
$totals = [
    'contact_unread'  => (int)val("SELECT COUNT(*) FROM tm_contact_messages WHERE is_read=0"),
    'mail_pending'    => (int)val("SELECT COUNT(*) FROM tm_mail_orders WHERE status='pending'"),
    'loyalty_pending' => (int)val("SELECT COUNT(*) FROM tm_loyalty_members WHERE is_approved=0"),
];
?>

<div class="adm-tabs">
    <?php foreach ($tabConfig as $tk => $tc): ?>
        <?php
        $bdg = '';
        if ($tk === 'contact' && $totals['contact_unread'])   $bdg = $totals['contact_unread'];
        if ($tk === 'mail'    && $totals['mail_pending'])     $bdg = $totals['mail_pending'];
        if ($tk === 'loyalty' && $totals['loyalty_pending'])  $bdg = $totals['loyalty_pending'];
        ?>
        <a href="?tab=<?= $tk ?>" class="<?= $tk === $tab ? 'active' : '' ?>">
            <?= $tc['icon'] ?> <?= h($tc['label']) ?>
            <?php if ($bdg): ?><span class="adm-tab-badge"><?= (int)$bdg ?></span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="adm-panel">
    <div class="adm-panel-head">
        <h2><?= $cfg['icon'] ?> <?= h($cfg['label']) ?> (<?= count($rows) ?>)</h2>
        <div class="adm-filters">
            <?php if ($tab === 'contact'): ?>
                <a href="?tab=contact"             class="adm-btn adm-btn-sm <?= $f === ''       ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Tümü</a>
                <a href="?tab=contact&f=unread"    class="adm-btn adm-btn-sm <?= $f === 'unread' ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Okunmamış</a>
            <?php elseif ($tab === 'mail'): ?>
                <a href="?tab=mail"                class="adm-btn adm-btn-sm <?= $f === ''         ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Tümü</a>
                <a href="?tab=mail&f=pending"      class="adm-btn adm-btn-sm <?= $f === 'pending'  ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Bekleyen</a>
                <a href="?tab=mail&f=approved"     class="adm-btn adm-btn-sm <?= $f === 'approved' ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Onaylı</a>
                <a href="?tab=mail&f=completed"    class="adm-btn adm-btn-sm <?= $f === 'completed'? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Tamamlanan</a>
            <?php else: ?>
                <a href="?tab=loyalty"             class="adm-btn adm-btn-sm <?= $f === ''        ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Tümü</a>
                <a href="?tab=loyalty&f=pending"   class="adm-btn adm-btn-sm <?= $f === 'pending' ? 'adm-btn-primary' : 'adm-btn-ghost' ?>">Bekleyen</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="adm-panel-body" style="padding:0">
        <?php if (!$rows): ?>
            <div class="adm-empty"><div class="ico"><?= $cfg['icon'] ?></div>Kayıt yok.</div>
        <?php else: ?>
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ad Soyad</th>
                        <th>E-posta / Telefon</th>
                        <?php if ($tab === 'contact'): ?>
                            <th>Konu</th><th>Durum</th>
                        <?php elseif ($tab === 'mail'): ?>
                            <th>Tutar</th><th>Durum</th>
                        <?php else: ?>
                            <th>Şehir</th><th>Durum</th>
                        <?php endif; ?>
                        <th>Tarih</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $isUnread = false;
                    if ($tab === 'contact')  $isUnread = empty($r['is_read']);
                    if ($tab === 'mail')     $isUnread = ($r['status'] ?? '') === 'pending';
                    if ($tab === 'loyalty')  $isUnread = empty($r['is_approved']);
                    ?>
                    <tr<?= $isUnread ? ' class="adm-row-unread"' : '' ?>>
                        <td><?= (int)$r['id'] ?></td>
                        <td><strong><?= h($r['full_name']) ?></strong><?php if (!empty($r['company'])): ?><br><small style="color:#aaa"><?= h($r['company']) ?></small><?php endif; ?></td>
                        <td>
                            <?php if (!empty($r['email'])): ?><a href="mailto:<?= h($r['email']) ?>"><?= h($r['email']) ?></a><br><?php endif; ?>
                            <?php if (!empty($r['phone'])): ?><small><?= h($r['phone']) ?></small><?php endif; ?>
                        </td>
                        <?php if ($tab === 'contact'): ?>
                            <td><?= h(excerpt($r['subject'] ?: $r['message'], 50)) ?></td>
                            <td>
                                <?php if (!empty($r['is_replied'])): ?><span class="badge badge-on">✓ Yanıtlandı</span>
                                <?php elseif (!empty($r['is_read'])): ?><span class="badge">Okundu</span>
                                <?php else: ?><span class="badge badge-warn">Yeni</span><?php endif; ?>
                            </td>
                        <?php elseif ($tab === 'mail'): ?>
                            <td><strong><?= number_format((float)$r['amount'], 2, ',', '.') ?> ₺</strong></td>
                            <td>
                                <?php
                                $statusLabels = ['pending'=>['Beklemede','warn'],'approved'=>['Onaylandı','on'],'rejected'=>['Reddedildi','off'],'completed'=>['Tamamlandı','on']];
                                $sl = $statusLabels[$r['status']] ?? ['?', ''];
                                ?>
                                <span class="badge badge-<?= $sl[1] ?>"><?= $sl[0] ?></span>
                            </td>
                        <?php else: ?>
                            <td><?= h($r['city'] ?: '—') ?></td>
                            <td><span class="badge badge-<?= $r['is_approved'] ? 'on' : 'warn' ?>"><?= $r['is_approved'] ? 'Onaylı' : 'Bekleyen' ?></span></td>
                        <?php endif; ?>
                        <td><small><?= h(tr_date($r['created_at'])) ?></small></td>
                        <td class="actions">
                            <a href="?tab=<?= $tab ?>&action=view&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-primary">Detay</a>
                            <a href="?tab=<?= $tab ?>&action=delete&id=<?= (int)$r['id'] ?>" class="adm-btn adm-btn-sm adm-btn-danger" data-confirm="Sil?">×</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
