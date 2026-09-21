<?php
require __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/qnbpay.php';

header('Cache-Control: no-store');
qnb_ensure_schema();

/* ============================================================
 * 1) QNBpay dönüşü (return_url / cancel_url)
 *    Banka → tarayıcı → buraya POST gelir. Çapraz-site POST'ta oturum çerezi
 *    gönderilmeyebilir; bu yüzden kimlik doğrulama OTURUMA DEĞİL hash_key'e dayanır.
 * ============================================================ */
$in = array_merge($_GET, $_POST);   // QNBpay POST veya GET ile dönebilir
$isGatewayReturn = isset($in['invoice_id']) && (isset($in['sipay_status']) || isset($in['hash_key']));

if ($isGatewayReturn) {
    $invoiceId = mb_substr((string)$in['invoice_id'], 0, 40, 'UTF-8');
    $pay = row("SELECT * FROM tm_payments WHERE invoice_id=?", [$invoiceId]);

    if ($pay) {
        try {
            $res = qnb_apply_return($pay, $in);
            if ($res['changed'] && in_array($res['status'], ['paid', 'review'], true)) {
                qnb_notify((int)$pay['id']);
            }
        } catch (Throwable $e) {
            // Sessiz — sonuç sayfası kaydın mevcut durumunu gösterir
        }
        redirect('odeme-sonuc.php?ref=' . $pay['public_ref']);   // PRG: yenilemede tekrar POST olmasın
    }
    redirect('odeme.php');
}

/* ============================================================
 * 2) Sonuç sayfası (?ref=<public_ref>)
 * ============================================================ */
$ref = (string)($_GET['ref'] ?? '');
$pay = preg_match('/^[a-f0-9]{32}$/', $ref) ? row("SELECT * FROM tm_payments WHERE public_ref=?", [$ref]) : false;
if (!$pay) redirect('odeme.php');

$pageTitle  = t('payres.title', 'Ödeme Sonucu');
$metaDesc   = t('payres.meta_desc', 'Tekcan Metal online ödeme sonucu.');
$metaRobots = 'noindex, nofollow';
$status     = (string)$pay['status'];
$isTest     = $pay['pos_mode'] === 'test';
$phone      = (string)settings('site_phone', '');

require __DIR__ . '/includes/header.php';
?>

<style>.pr-page{--navy:#050d24;--gold:#c9a86b;--red:#c8102e;--paper:#fafaf7;--serif:'Cormorant Garamond',Georgia,serif;--sans:'Inter',system-ui,sans-serif;background:var(--paper);padding:70px 0 100px;font-family:var(--sans)}
.pr-card{max-width:640px;margin:0 auto;background:#fff;border:1px solid #e3e0d8;border-top:4px solid var(--gold);padding:44px 40px;text-align:center}
@media (max-width:600px){.pr-card{padding:32px 20px}}
.pr-icon{width:68px;height:68px;border-radius:50%;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;font-size:34px;color:#fff}
.pr-ok .pr-icon{background:#047857}.pr-fail .pr-icon{background:var(--red)}.pr-review .pr-icon,.pr-wait .pr-icon{background:#b45309}
.pr-card h1{font-family:var(--serif);font-size:32px;font-weight:600;color:var(--navy);margin:0 0 10px}
.pr-card p{font-size:14.5px;line-height:1.65;color:#3a3a3a;margin:0 0 14px}
.pr-table{width:100%;border-collapse:collapse;margin:22px 0;text-align:left;font-size:13.5px}
.pr-table td{padding:10px 4px;border-bottom:1px solid #eee}
.pr-table td:first-child{color:#777;width:42%}
.pr-table td:last-child{color:var(--navy);font-weight:600;word-break:break-all}
.pr-btn{display:inline-block;margin-top:10px;padding:14px 30px;background:var(--navy);color:#fff;font-size:12.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;text-decoration:none}
.pr-btn:hover{background:var(--gold);color:var(--navy)}
.pr-test{display:inline-block;background:#fff7e6;border:1px solid #f0d9a8;color:#8a5a00;font-size:11px;font-weight:700;letter-spacing:1px;padding:4px 10px;margin-bottom:16px}
</style>

<div class="pr-page">
  <div class="container">
    <div class="pr-card pr-<?= $status === 'paid' ? 'ok' : ($status === 'failed' ? 'fail' : ($status === 'review' ? 'review' : 'wait')) ?>">

      <?php if ($isTest): ?><div class="pr-test">TEST ORTAMI</div><?php endif; ?>

      <?php if ($status === 'paid'): ?>
        <div class="pr-icon">✓</div>
        <h1><?= h(t('payres.ok_h', 'Ödemeniz Alındı')) ?></h1>
        <p><?= h(t('payres.ok_p', 'Teşekkür ederiz. Ödemeniz başarıyla tamamlandı; onay bilgisi e-posta adresinize gönderildi.')) ?></p>

      <?php elseif ($status === 'failed'): ?>
        <div class="pr-icon">✕</div>
        <h1><?= h(t('payres.fail_h', 'Ödeme Tamamlanamadı')) ?></h1>
        <p><?= h(t('payres.fail_p', 'İşleminiz gerçekleştirilemedi. Kartınızdan tutar çekilmedi.')) ?></p>
        <?php if (!empty($pay['gateway_message'])): ?><p><strong><?= h($pay['gateway_message']) ?></strong></p><?php endif; ?>

      <?php elseif ($status === 'review'): ?>
        <div class="pr-icon">!</div>
        <h1><?= h(t('payres.review_h', 'Ödemeniz İnceleniyor')) ?></h1>
        <p><?= h(t('payres.review_p', 'İşleminiz bankadan onay almış görünüyor ancak doğrulama tamamlanamadı. Ekibimiz ödemenizi elle kontrol edecektir. Kartınızdan tutar çekildiyse aşağıdaki referans numarasıyla bizimle iletişime geçin.')) ?></p>
        <?php if ($phone): ?><p><a href="<?= h(phone_link($phone)) ?>"><?= h($phone) ?></a></p><?php endif; ?>

      <?php else: ?>
        <div class="pr-icon">…</div>
        <h1><?= h(t('payres.wait_h', 'İşlem Henüz Sonuçlanmadı')) ?></h1>
        <p><?= h(t('payres.wait_p', 'Banka doğrulaması tamamlanmamış görünüyor. Birkaç saniye sonra sayfayı yenileyin. İşlemi tamamlamadıysanız yeniden deneyebilirsiniz.')) ?></p>
      <?php endif; ?>

      <table class="pr-table">
        <tr><td><?= h(t('payres.t_ref', 'Referans No')) ?></td><td><?= h($pay['invoice_id']) ?></td></tr>
        <tr><td><?= h(t('payres.t_amount', 'Tutar')) ?></td><td><?= h(qnb_money((float)$pay['amount'])) ?></td></tr>
        <?php if (!empty($pay['order_no'])): ?><tr><td><?= h(t('payres.t_order', 'Banka İşlem No')) ?></td><td><?= h($pay['order_no']) ?></td></tr><?php endif; ?>
        <?php if ($status === 'paid' && !empty($pay['paid_at'])): ?><tr><td><?= h(t('payres.t_date', 'Tarih')) ?></td><td><?= h(tr_date($pay['paid_at'], true)) ?></td></tr><?php endif; ?>
        <?php if (!empty($pay['description'])): ?><tr><td><?= h(t('payres.t_desc', 'Açıklama')) ?></td><td><?= h($pay['description']) ?></td></tr><?php endif; ?>
      </table>

      <?php if ($status === 'paid'): ?>
        <a class="pr-btn" href="<?= h(url('odeme-dekont.php?ref=' . $pay['public_ref'])) ?>" target="_blank" rel="noopener" style="margin-right:8px">🖨 <?= h(t('payres.receipt', 'Dekontu Görüntüle / Yazdır')) ?></a>
      <?php endif; ?>
      <?php if ($status === 'failed' || $status === 'pending'): ?>
        <a class="pr-btn" href="<?= h(url_lang('odeme.php')) ?>"><?= h(t('payres.retry', 'Tekrar Dene')) ?></a>
      <?php else: ?>
        <a class="pr-btn" href="<?= h(url_lang('')) ?>"><?= h(t('payres.home', 'Anasayfaya Dön')) ?></a>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
