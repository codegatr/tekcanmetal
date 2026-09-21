<?php
/**
 * Ödeme dekontu — yalnızca ÖDENMİŞ kayıtlar için, tahmin edilemez public_ref ile açılır.
 * Bağımsız (site başlığı/altlığı yok) yazdırma dostu sayfa; "Yazdır / PDF" tarayıcıdan.
 * Bilgilendirme amaçlıdır, fatura yerine geçmez.
 */
require __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/qnbpay.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$ref = (string)($_GET['ref'] ?? '');
$pay = preg_match('/^[a-f0-9]{32}$/', $ref) ? row("SELECT * FROM tm_payments WHERE public_ref=?", [$ref]) : false;
if (!$pay) redirect('odeme.php');
if ($pay['status'] !== 'paid') redirect('odeme-sonuc.php?ref=' . $pay['public_ref']);   // dekont yalnızca başarılı ödeme için

$isTest = $pay['pos_mode'] === 'test';
$siteName = (string)(settings('site_name', '') ?: settings('site_short_name', 'Tekcan Metal'));
$addr  = (string)settings('site_address', '');
$phone = (string)settings('site_phone', '');
$mail  = (string)settings('site_email', '');
$last4 = qnb_card_last4($pay['card_mask']);
$when  = $pay['paid_at'] ? (new DateTime($pay['paid_at']))->format('d.m.Y H:i') : '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Ödeme Dekontu — <?= h($pay['invoice_id']) ?></title>
<style>
  :root{--navy:#050d24;--gold:#c9a86b;--line:#e3e0d8}
  *{box-sizing:border-box}
  body{margin:0;background:#f3f1ec;font-family:'Inter',system-ui,-apple-system,'Segoe UI',Arial,sans-serif;color:#1a1a1a}
  .sheet{max-width:760px;margin:28px auto;background:#fff;border:1px solid var(--line);border-top:5px solid var(--gold);padding:44px 48px;position:relative;overflow:hidden}
  .head{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;border-bottom:2px solid var(--navy);padding-bottom:18px;margin-bottom:24px}
  .head h1{margin:0;font-size:22px;letter-spacing:2px;color:var(--navy)}
  .head .co{font-size:12.5px;line-height:1.6;color:#555;text-align:right}
  .head .co strong{display:block;font-size:15px;color:var(--navy)}
  .badge{display:inline-block;background:#047857;color:#fff;font-weight:700;font-size:12px;letter-spacing:2px;padding:5px 12px;margin-bottom:6px}
  table{width:100%;border-collapse:collapse;margin:14px 0 6px}
  td{padding:11px 4px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:top}
  td:first-child{width:38%;color:#777}
  td:last-child{font-weight:600;color:var(--navy);word-break:break-word}
  .amount td{font-size:20px;border-bottom:2px solid var(--navy)}
  .note{margin-top:24px;font-size:12px;line-height:1.65;color:#666;border-top:1px dashed var(--line);padding-top:14px}
  .actions{max-width:760px;margin:0 auto 40px;display:flex;gap:10px;flex-wrap:wrap}
  .btn{background:var(--navy);color:#fff;border:0;padding:13px 24px;font-size:13px;font-weight:700;letter-spacing:1px;cursor:pointer;text-decoration:none;font-family:inherit}
  .btn.alt{background:#fff;color:var(--navy);border:1px solid var(--navy)}
  .wm{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;font-size:74px;font-weight:800;color:rgba(200,16,46,.10);transform:rotate(-24deg);letter-spacing:6px;text-align:center;line-height:1.1}
  @media (max-width:600px){.sheet{padding:26px 18px;margin:10px}.head{flex-direction:column}.head .co{text-align:left}}
  @media print{body{background:#fff}.sheet{margin:0;border:0;border-top:0;padding:0 4px;max-width:none}.actions{display:none}}
</style>
</head>
<body>
  <div class="sheet">
    <?php if ($isTest): ?><div class="wm">TEST<br>GEÇERLİ DEĞİLDİR</div><?php endif; ?>
    <div class="head">
      <div>
        <div class="badge">ÖDENDİ</div>
        <h1>ÖDEME DEKONTU</h1>
      </div>
      <div class="co">
        <strong><?= h($siteName) ?></strong>
        <?php if ($addr): ?><?= h($addr) ?><br><?php endif; ?>
        <?php if ($phone): ?><?= h($phone) ?><br><?php endif; ?>
        <?php if ($mail): ?><?= h($mail) ?><?php endif; ?>
      </div>
    </div>

    <table>
      <tr><td>Referans No</td><td><?= h($pay['invoice_id']) ?></td></tr>
      <tr><td>Banka İşlem No</td><td><?= h($pay['order_no'] ?: '—') ?></td></tr>
      <tr><td>Ödeme Tarihi</td><td><?= h($when) ?></td></tr>
      <tr><td>Ödeme Yapan</td><td><?= h($pay['full_name']) ?></td></tr>
      <?php if (!empty($pay['company'])): ?><tr><td>Firma</td><td><?= h($pay['company']) ?></td></tr><?php endif; ?>
      <tr><td>Ödeme Yöntemi</td><td>Kredi / Banka Kartı (3D Secure)<?= $last4 ? ' — **** **** **** ' . h($last4) : '' ?></td></tr>
      <?php if (!empty($pay['description'])): ?><tr><td>Açıklama</td><td><?= h($pay['description']) ?></td></tr><?php endif; ?>
      <tr class="amount"><td>Ödenen Tutar</td><td><?= h(qnb_money((float)$pay['amount'])) ?></td></tr>
    </table>

    <div class="note">
      Bu belge, yukarıdaki ödemenin alındığını gösteren bilgilendirme amaçlı dekonttur ve <strong>fatura yerine geçmez</strong>.
      Kart bilgileriniz saklanmaz; yalnızca kartın son dört hanesi gösterilir.
      Sorularınız için <?= h($siteName) ?> ile iletişime geçebilirsiniz.
    </div>
  </div>

  <div class="actions">
    <button class="btn" onclick="window.print()">🖨 Yazdır / PDF Olarak Kaydet</button>
    <a class="btn alt" href="<?= h(url('odeme-sonuc.php?ref=' . $pay['public_ref'])) ?>">← Sonuç sayfası</a>
  </div>
</body>
</html>
