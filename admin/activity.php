<?php
define('TM_ADMIN', true);
$adminTitle = 'Aktivite Logları';
require __DIR__ . '/_layout.php';
require __DIR__ . '/_helpers.php';

$action = $_GET['action'] ?? 'list';

/* ========== DELETE / CLEAR ========== */
if ($action === 'clear' && csrf_check()) {
    q("DELETE FROM tm_activity_logs");
    log_activity('clear', 'activity_logs', 0);
    adm_back_with('success', 'Tüm loglar silindi.', 'admin/activity.php');
}

if ($action === 'clear_old' && csrf_check()) {
    q("DELETE FROM tm_activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    adm_back_with('success', '90 günden eski loglar silindi.', 'admin/activity.php');
}

/* ========== LIST ========== */
$page = max(1, (int)($_GET['s'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$filterAction = trim($_GET['fa'] ?? '');
$filterTarget = trim($_GET['ft'] ?? '');
$filterUser   = (int)($_GET['fu'] ?? 0);

$where = "WHERE 1=1";
$params = [];
if ($filterAction !== '') { $where .= " AND a.action = ?";      $params[] = $filterAction; }
if ($filterTarget !== '') { $where .= " AND a.target_type = ?"; $params[] = $filterTarget; }
if ($filterUser   !== 0)  { $where .= " AND a.user_id = ?";     $params[] = $filterUser; }

$total = (int)val("SELECT COUNT(*) FROM tm_activity_logs a $where", $params);
$pages = (int)max(1, ceil($total / $perPage));

$paramsList = $params;
$paramsList[] = $perPage;
$paramsList[] = $offset;
$rows = all("
    SELECT a.*, u.username, u.full_name
    FROM tm_activity_logs a
    LEFT JOIN tm_users u ON u.id = a.user_id
    $where
    ORDER BY a.id DESC
    LIMIT ? OFFSET ?
", $paramsList);

// Filter dropdowns
$actions     = all("SELECT DISTINCT action      FROM tm_activity_logs WHERE action      <> '' ORDER BY action");
$targetTypes = all("SELECT DISTINCT target_type FROM tm_activity_logs WHERE target_type <> '' ORDER BY target_type");
$users       = all("SELECT id, username, full_name FROM tm_users ORDER BY username");

function action_color(string $a): string {
    return match (true) {
        str_contains($a, 'delete') || str_contains($a, 'clear') => 'off',
        str_contains($a, 'create') || str_contains($a, 'add')   => 'on',
        str_contains($a, 'update') || str_contains($a, 'edit')  => 'warn',
        str_contains($a, 'login')                                => 'on',
        default                                                  => '',
    };
}

function build_qs(array $extra = []): string {
    $params = array_merge($_GET, $extra);
    unset($params['s']);
    return http_build_query($params);
}
?>

<div class="adm-panel">
    <div class="adm-panel-head">
        <h2>Aktivite Logları (<?= number_format($total, 0, ',', '.') ?>)</h2>
        <div class="adm-filters">
            <form method="post" action="?action=clear_old" style="display:inline" onsubmit="return confirm('90 günden eski loglar silinecek. Devam?')">
                <?= csrf_field() ?>
                <button type="submit" class="adm-btn adm-btn-sm adm-btn-ghost">90+ Gün Sil</button>
            </form>
            <form method="post" action="?action=clear" style="display:inline" onsubmit="return confirm('TÜM LOGLAR silinecek. EMİN MİSİNİZ?')">
                <?= csrf_field() ?>
                <button type="submit" class="adm-btn adm-btn-sm adm-btn-danger">Tümünü Temizle</button>
            </form>
        </div>
    </div>

    <div class="adm-panel-body" style="padding:14px 20px;border-bottom:1px solid #2a3552">
        <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">
            <div>
                <label style="font-size:12px;color:#aaa">İşlem</label>
                <select name="fa">
                    <option value="">Tümü</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= h($a['action']) ?>" <?= $filterAction === $a['action'] ? 'selected' : '' ?>><?= h($a['action']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:12px;color:#aaa">Hedef Tipi</label>
                <select name="ft">
                    <option value="">Tümü</option>
                    <?php foreach ($targetTypes as $t): ?>
                        <option value="<?= h($t['target_type']) ?>" <?= $filterTarget === $t['target_type'] ? 'selected' : '' ?>><?= h($t['target_type']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:12px;color:#aaa">Kullanıcı</label>
                <select name="fu">
                    <option value="0">Tümü</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= (int)$u['id'] ?>" <?= $filterUser === (int)$u['id'] ? 'selected' : '' ?>><?= h($u['full_name'] ?: $u['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="adm-btn adm-btn-sm adm-btn-primary">Filtrele</button>
            <a href="<?= h(admin_url('activity.php')) ?>" class="adm-btn adm-btn-sm adm-btn-ghost">Temizle</a>
        </form>
    </div>

    <div class="adm-panel-body" style="padding:0">
        <?php if (!$rows): ?>
            <div class="adm-empty"><div class="ico">📜</div>Log kaydı bulunmuyor.</div>
        <?php else: ?>
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th><th>Kullanıcı</th><th>İşlem</th><th>Hedef</th><th>Açıklama</th><th>IP</th><th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td>
                                <?php if ($r['user_id']): ?>
                                    <strong><?= h($r['full_name'] ?: $r['username'] ?: 'Kullanıcı #' . $r['user_id']) ?></strong>
                                <?php else: ?>
                                    <span style="color:#777">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?= action_color($r['action']) ?>"><?= h($r['action']) ?></span></td>
                            <td>
                                <?php if ($r['target_type']): ?>
                                    <code><?= h($r['target_type']) ?><?= $r['target_id'] ? '#' . (int)$r['target_id'] : '' ?></code>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><small><?= h(excerpt($r['description'] ?: '', 80)) ?></small></td>
                            <td><small style="color:#aaa"><code><?= h($r['ip_address'] ?: '—') ?></code></small></td>
                            <td><small><?= h(tr_date($r['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pages > 1): ?>
                <?php $qs = build_qs(); $qsPrefix = $qs ? $qs . '&' : ''; ?>
                <div class="adm-pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= $qsPrefix ?>s=<?= $page - 1 ?>" class="adm-btn adm-btn-sm adm-btn-ghost">‹ Önceki</a>
                    <?php endif; ?>
                    <span>Sayfa <?= $page ?> / <?= $pages ?></span>
                    <?php if ($page < $pages): ?>
                        <a href="?<?= $qsPrefix ?>s=<?= $page + 1 ?>" class="adm-btn adm-btn-sm adm-btn-ghost">Sonraki ›</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
