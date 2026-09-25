<?php
/**
 * Geçmiş Ödemelerim — giriş yapmış müşterinin KENDİ ödemelerini listelediği,
 * odeme.php ile aynı bağımsız kabuğu (menü/footer yok) kullanan sayfa.
 * Arama, durum ve tarih aralığı filtresi + CSV dışa aktarma içerir.
 *
 * Referans: kullanıcının paylaştığı Peker Profil Online Tahsilat Sistemi'nin
 * "Geçmiş Ödemeler" ekranı (arama/durum/tarih filtresi + CSV) incelenip
 * Tekcan Metal'in müşteri hesabı yapısına (yalnızca kendi kayıtları) uyarlandı.
 */
require __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/qnbpay.php';
require_once __DIR__ . '/includes/customer_auth.php';
cust_ensure_schema();

$cust = customer();
if (!$cust || $cust['must_change_password']) {
    redirect('odeme.php');
}

$statusOptions = ['' => 'Tüm Durumlar', 'paid' => 'Ödendi', 'pending' => 'Bekliyor', 'failed' => 'Başarısız', 'review' => 'İnceleniyor'];
$fStatusRaw = (string)($_GET['status'] ?? '');
$fStatus = array_key_exists($fStatusRaw, $statusOptions) ? $fStatusRaw : '';
$fQ      = trim((string)($_GET['q'] ?? ''));
$fFrom   = trim((string)($_GET['date_from'] ?? ''));
$fTo     = trim((string)($_GET['date_to'] ?? ''));
$validDate = fn(string $d) => $d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);

$where = ['customer_id = ?'];
$params = [$cust['id']];
if ($fStatus !== '') { $where[] = 'status = ?'; $params[] = $fStatus; }
if ($fQ !== '') {
    $like = '%' . addcslashes($fQ, '%_\\') . '%';
    $cond = '(invoice_id LIKE ? OR description LIKE ? OR order_no LIKE ?)';
    $qp = [$like, $like, $like];
    $num = trim(str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $fQ)));
    if ($num !== '' && is_numeric($num)) { $cond = '(' . $cond . ' OR amount = ?)'; $qp[] = (float)$num; }
    $where[] = $cond;
    array_push($params, ...$qp);
}
if ($validDate($fFrom)) { $where[] = 'created_at >= ?'; $params[] = $fFrom . ' 00:00:00'; }
if ($validDate($fTo))   { $where[] = 'created_at <= ?'; $params[] = $fTo . ' 23:59:59'; }
$whereSql = 'WHERE ' . implode(' AND ', $where);

/* ============================================================
 * CSV dışa aktarma — aynı filtrelerle, TÜM eşleşen kayıtlar (sayfalama yok)
 * ============================================================ */
if (($_GET['action'] ?? '') === 'export') {
    $rows = all("SELECT invoice_id, created_at, paid_at, amount, status, order_no, description
                 FROM tm_payments $whereSql ORDER BY created_at DESC", $params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="odemelerim-' . date('Ymd-His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // Excel'in UTF-8'i doğru algılaması için BOM
    fputcsv($out, ['Referans No', 'Tarih', 'Ödeme Tarihi', 'Tutar', 'Durum', 'Banka İşlem No', 'Açıklama']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['invoice_id'],
            $r['created_at'],
            $r['paid_at'] ?: '',
            number_format((float)$r['amount'], 2, ',', '.'),
            qnb_status_label((string)$r['status']),
            $r['order_no'] ?: '',
            $r['description'] ?: '',
        ]);
    }
    fclose($out);
    exit;
}

/* ============================================================
 * Sayfalanmış liste
 * ============================================================ */
$perPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = (int)val("SELECT COUNT(*) FROM tm_payments $whereSql", $params);
$lastPage = max(1, (int)ceil($total / $perPage));
$page = min($page, $lastPage);
$offset = ($page - 1) * $perPage;
$rows = all("SELECT invoice_id, public_ref, created_at, paid_at, amount, status, order_no, description
             FROM tm_payments $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset", $params);

$qs = function (array $extra = []) use ($fStatus, $fQ, $fFrom, $fTo) {
    return '?' . http_build_query(array_filter(array_merge(
        ['status' => $fStatus, 'q' => $fQ, 'date_from' => $fFrom, 'date_to' => $fTo], $extra
    ), fn($v) => $v !== '' && $v !== null));
};
$hasFilter = $fStatus !== '' || $fQ !== '' || $fFrom !== '' || $fTo !== '';

$pageTitle  = t('hist.title', 'Geçmiş Ödemelerim');
$metaDesc   = t('hist.meta_desc', 'Tekcan Metal online ödeme geçmişiniz.');
$metaRobots = 'noindex, nofollow';
$siteShort  = settings('site_short_name', 'Tekcan Metal');
$logoPath   = settings('logo', 'assets/img/logo.png');
$logoWhitePath = preg_replace('/\.(png|svg|jpe?g)$/i', '-white.$1', $logoPath) ?: $logoPath;
$logoOnDark = file_exists(__DIR__ . '/' . $logoWhitePath) ? $logoWhitePath : (file_exists(__DIR__ . '/' . $logoPath) ? $logoPath : null);
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= h($pageTitle) ?> — <?= h($siteShort) ?></title>
<meta name="description" content="<?= h($metaDesc) ?>">
<meta name="robots" content="<?= h($metaRobots) ?>">
<link rel="icon" href="<?= h(url(settings('favicon', 'assets/img/favicon.png'))) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--navy:#050d24;--navy-2:#0c1e44;--gold:#c9a86b;--gold-dark:#a88a4a;--red:#c8102e;--red-dark:#a00d24;
  --paper:#fafaf7;--ink:#1a1a1a;--line:#e7e4dc;--muted:#767268;--serif:'Cormorant Garamond',Georgia,serif;--sans:'Inter',system-ui,sans-serif}
*,*::before,*::after{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;overflow-x:hidden;font-family:var(--sans);color:var(--ink);background:linear-gradient(180deg,#f3f1ec 0%,#eceae3 100%);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
a{color:inherit}
.ck-top{background:var(--navy);border-bottom:3px solid var(--red)}
.ck-top-inner{max-width:1080px;margin:0 auto;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.ck-brand{display:flex;align-items:center;gap:10px}
.ck-brand-logo{height:38px;width:auto;max-width:220px;display:block;object-fit:contain}
@media (max-width:480px){.ck-brand-logo{height:30px;max-width:160px}}
.ck-brand-mark{width:34px;height:34px;border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--serif);font-size:18px;font-weight:600;color:var(--gold);flex-shrink:0}
.ck-brand-text{font-family:var(--serif);font-size:16.5px;font-weight:600;color:#fff;letter-spacing:.3px}
.ck-brand-text em{font-style:italic;color:var(--gold)}
.hist-back{display:flex;align-items:center;gap:6px;font-family:var(--sans);font-size:12.5px;font-weight:600;color:rgba(255,255,255,.8);text-decoration:none}
.hist-back:hover{color:#fff}

.hist-main{flex:1;padding:32px 18px 46px;display:flex;justify-content:center}
.hist-shell{width:100%;max-width:1000px}
.hist-head{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:18px}
.hist-head h1{font-family:var(--serif);font-size:27px;font-weight:600;color:var(--navy);margin:0 0 4px}
.hist-head .sub{font-family:var(--sans);font-size:12.5px;color:var(--muted)}
.hist-export{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid var(--line);border-radius:8px;padding:10px 16px;
  font-family:var(--sans);font-size:12.5px;font-weight:700;color:var(--navy);text-decoration:none;white-space:nowrap}
.hist-export:hover{border-color:var(--gold);background:var(--paper)}

.hist-card{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(15,13,8,.04),0 18px 44px rgba(15,13,8,.06);margin-bottom:18px;overflow:hidden}
.hist-filters{padding:20px 22px;display:grid;grid-template-columns:2fr 1.2fr 1fr 1fr auto;gap:12px;align-items:end}
@media (max-width:820px){.hist-filters{grid-template-columns:1fr 1fr}}
.hist-field{display:flex;flex-direction:column;gap:5px}
.hist-field label{font-family:var(--sans);font-size:10.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--navy)}
.hist-field input,.hist-field select{padding:10px 12px;font-family:var(--sans);font-size:13.5px;border:1px solid #d8d5cc;border-radius:8px;background:var(--paper);width:100%}
.hist-field input:focus,.hist-field select:focus{outline:0;border-color:var(--gold);background:#fff}
.hist-filters button{background:var(--navy);color:#fff;border:0;border-radius:8px;padding:10px 20px;font-family:var(--sans);font-size:12.5px;font-weight:700;cursor:pointer;white-space:nowrap}
.hist-filters button:hover{background:var(--navy-2)}
.hist-clear{grid-column:1/-1;font-family:var(--sans);font-size:12px;color:var(--muted);text-decoration:underline;justify-self:start}

.hist-empty{padding:60px 20px;text-align:center;font-family:var(--sans);color:var(--muted)}
.hist-empty svg{color:#c9c5b8;margin-bottom:14px}
.hist-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.hist-table{width:100%;border-collapse:collapse;font-family:var(--sans);font-size:13px;min-width:640px}
.hist-table th{text-align:left;padding:12px 18px;background:var(--paper);font-size:10.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--line)}
.hist-table td{padding:13px 18px;border-bottom:1px solid var(--line);color:#333;white-space:nowrap}
.hist-table tr:last-child td{border-bottom:0}
.hist-table a{color:var(--navy);font-weight:600;text-decoration:underline}
.hist-amount{font-weight:700;color:var(--navy)}
.hist-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.hist-badge-paid{background:#e9f8ef;color:#16a34a}
.hist-badge-pending{background:#fff7e6;color:#b45309}
.hist-badge-failed{background:#fdecec;color:#dc2626}
.hist-badge-review{background:#fff3eb;color:#c8102e}
.hist-pager{display:flex;justify-content:center;align-items:center;gap:14px;padding:16px;font-family:var(--sans);font-size:12.5px;color:var(--muted)}
.hist-pager a{color:var(--navy);text-decoration:underline}

.ck-footer{text-align:center;padding:22px 18px 28px;font-family:var(--sans);font-size:11.5px;color:#9a9689}
.ck-footer a{color:#9a9689;text-decoration:underline}
.ck-footer a:hover{color:var(--navy)}
.ck-footer .sep{margin:0 7px;opacity:.6}
</style>
</head>
<body>

<header class="ck-top">
  <div class="ck-top-inner">
    <div class="ck-brand">
      <?php if ($logoOnDark): ?>
        <img src="<?= h(url($logoOnDark)) ?>" alt="<?= h($siteShort) ?>" class="ck-brand-logo">
      <?php else: ?>
        <span class="ck-brand-mark">T</span>
        <span class="ck-brand-text">TEKCAN <em>METAL</em></span>
      <?php endif; ?>
    </div>
    <a href="<?= h(url('odeme.php')) ?>" class="hist-back">← <?= h(t('hist.back', 'Ödeme Sayfasına Dön')) ?></a>
  </div>
</header>

<main class="hist-main">
  <div class="hist-shell">

    <div class="hist-head">
      <div>
        <h1><?= h(t('hist.title', 'Geçmiş Ödemelerim')) ?></h1>
        <div class="sub"><?= h($cust['full_name']) ?> · <?= (int)$total ?> <?= h(t('hist.records', 'kayıt')) ?></div>
      </div>
      <a class="hist-export" href="<?= h($qs(['action' => 'export'])) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <?= h(t('hist.csv', 'CSV İndir')) ?>
      </a>
    </div>

    <div class="hist-card">
      <form method="get" class="hist-filters">
        <div class="hist-field"><label for="hq"><?= h(t('hist.f_search', 'Ara')) ?></label>
          <input type="text" id="hq" name="q" value="<?= h($fQ) ?>" placeholder="<?= h(t('hist.f_search_ph', 'Referans, işlem no, açıklama veya tutar')) ?>"></div>
        <div class="hist-field"><label for="hstatus"><?= h(t('hist.f_status', 'Durum')) ?></label>
          <select id="hstatus" name="status">
            <?php foreach ($statusOptions as $k => $lbl): ?><option value="<?= h($k) ?>" <?= $fStatus === $k ? 'selected' : '' ?>><?= h($lbl) ?></option><?php endforeach; ?>
          </select></div>
        <div class="hist-field"><label for="hfrom"><?= h(t('hist.f_from', 'Başlangıç')) ?></label>
          <input type="date" id="hfrom" name="date_from" value="<?= h($fFrom) ?>"></div>
        <div class="hist-field"><label for="hto"><?= h(t('hist.f_to', 'Bitiş')) ?></label>
          <input type="date" id="hto" name="date_to" value="<?= h($fTo) ?>"></div>
        <button type="submit"><?= h(t('hist.filter', 'Filtrele')) ?></button>
        <?php if ($hasFilter): ?><a class="hist-clear" href="<?= h(url('odeme-gecmisim.php')) ?>"><?= h(t('hist.clear', 'Filtreleri temizle')) ?></a><?php endif; ?>
      </form>
    </div>

    <div class="hist-card">
      <?php if (!$rows): ?>
        <div class="hist-empty">
          <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <p><?= h($hasFilter ? t('hist.empty_filtered', 'Eşleşen işlem bulunamadı.') : t('hist.empty', 'Henüz ödeme işleminiz yok.')) ?></p>
        </div>
      <?php else: ?>
        <div class="hist-table-wrap">
          <table class="hist-table">
            <thead><tr>
              <th><?= h(t('hist.t_ref', 'Referans No')) ?></th>
              <th><?= h(t('hist.t_date', 'Tarih')) ?></th>
              <th><?= h(t('hist.t_amount', 'Tutar')) ?></th>
              <th><?= h(t('hist.t_status', 'Durum')) ?></th>
              <th><?= h(t('hist.t_desc', 'Açıklama')) ?></th>
              <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r):
              $badgeCls = ['paid' => 'hist-badge-paid', 'pending' => 'hist-badge-pending', 'failed' => 'hist-badge-failed', 'review' => 'hist-badge-review'][$r['status']] ?? 'hist-badge-pending';
            ?>
              <tr>
                <td><code><?= h($r['invoice_id']) ?></code></td>
                <td><?= h(tr_date($r['created_at'], true)) ?></td>
                <td class="hist-amount"><?= h(qnb_money((float)$r['amount'])) ?></td>
                <td><span class="hist-badge <?= $badgeCls ?>"><?= h(qnb_status_label((string)$r['status'])) ?></span></td>
                <td><?= h($r['description'] ?: '—') ?></td>
                <td><?php if ($r['status'] === 'paid'): ?><a href="<?= h(url('odeme-dekont.php?ref=' . $r['public_ref'])) ?>" target="_blank"><?= h(t('hist.receipt', 'Dekont')) ?></a><?php endif; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if ($lastPage > 1): ?>
        <div class="hist-pager">
          <?php if ($page > 1): ?><a href="<?= h($qs(['page' => $page - 1])) ?>">‹ <?= h(t('hist.prev', 'Önceki')) ?></a><?php endif; ?>
          <span><?= h(t('hist.page', 'Sayfa')) ?> <?= $page ?> / <?= $lastPage ?></span>
          <?php if ($page < $lastPage): ?><a href="<?= h($qs(['page' => $page + 1])) ?>"><?= h(t('hist.next', 'Sonraki')) ?> ›</a><?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

  </div>
</main>

<footer class="ck-footer">
  <p>© <?= h(date('Y')) ?> <?= h($siteShort) ?>
    <span class="sep">·</span><a href="<?= h(url('sayfa.php?slug=kvkk')) ?>">KVKK</a>
    <span class="sep">·</span><a href="<?= h(url('iletisim.php')) ?>">İletişim</a>
  </p>
</footer>

</body>
</html>
