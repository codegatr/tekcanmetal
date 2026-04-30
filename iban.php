<?php
require __DIR__ . '/includes/db.php';
$banks = all("SELECT * FROM tm_banks WHERE is_active=1 ORDER BY sort_order");
$pageTitle = t('iban.title', 'IBAN Bilgilerimiz');
$metaDesc  = t('iban.meta_desc', 'Tekcan Metal banka hesap ve IBAN bilgileri. Şirketimize ait güncel hesap numaraları.');
require __DIR__ . '/includes/header.php';
?>
<section class="page-header">
  <div class="container">
    <nav class="breadcrumb"><a href="<?= h(url_lang('')) ?>"><?= h(t('bc.home', 'Anasayfa')) ?></a> <span>›</span> <span>IBAN</span></nav>
    <h1><?= h(t('iban.title', 'Banka Hesap Bilgilerimiz')) ?></h1>
    <p class="lead"><?= t('iban.lead_prefix', 'Aşağıdaki hesap numaraları') ?> <strong><?= h(settings('site_name', 'Tekcan Metal Sanayi ve Ticaret Ltd. Şti.')) ?></strong> <?= t('iban.lead_suffix', 'adına kayıtlıdır.') ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$banks): ?>
      <p class="empty-state"><?= h(t('iban.no_banks', 'Henüz banka hesabı eklenmedi.')) ?></p>
    <?php else: ?>
    <div class="iban-grid">
      <?php foreach ($banks as $b): ?>
      <div class="iban-card">
        <div class="iban-head">
          <?php if (!empty($b['logo'])): ?>
            <img src="<?= h(img_url($b['logo'])) ?>" alt="<?= h($b['bank_name']) ?>" class="iban-logo">
          <?php else: ?>
            <div class="iban-bank-name"><?= h($b['bank_name']) ?></div>
          <?php endif; ?>
          <?php if (!empty($b['branch'])): ?>
            <span class="iban-branch"><?= h($b['branch']) ?></span>
          <?php endif; ?>
        </div>
        <div class="iban-body">
          <div class="iban-row">
            <span class="lbl"><?= h(t('iban.account_holder', 'Hesap Sahibi')) ?></span>
            <span class="val"><?= h($b['account_holder']) ?></span>
          </div>
          <?php if (!empty($b['account_number'])): ?>
          <div class="iban-row">
            <span class="lbl"><?= h(t('iban.account_number', 'Hesap No')) ?></span>
            <span class="val"><?= h($b['account_number']) ?></span>
          </div>
          <?php endif; ?>
          <div class="iban-row iban-main">
            <span class="lbl"><?= h(t('iban.iban_number', 'IBAN')) ?></span>
            <code class="iban-num" data-iban="<?= h($b['iban']) ?>"><?= h($b['iban']) ?></code>
            <button type="button" class="iban-copy" data-copy="<?= h($b['iban']) ?>" aria-label="<?= h(t('iban.copy', 'IBAN\'ı kopyala')) ?>"><?= h(t('iban.copy_btn', 'Kopyala')) ?></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="iban-note">⚠ <?= t('iban.note', 'Para gönderirken açıklama kısmına lütfen <strong>fatura veya cari numaranızı</strong> yazınız.') ?></p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
